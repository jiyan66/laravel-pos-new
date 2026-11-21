<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResourceAccessTest extends TestCase
{
    use RefreshDatabase;

    private function targetUser(): User
    {
        return User::query()->create([
            'username' => 'target.user', 'full_name' => 'Target User',
            'email' => 'target@example.com', 'password' => 'Testing123!',
        ]);
    }

    public function test_all_seven_actions_for_both_resources_have_auth_middleware(): void
    {
        foreach (['customers', 'users'] as $resource) {
            foreach (['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'] as $action) {
                $route = Route::getRoutes()->getByName($resource.'.'.$action);
                $this->assertNotNull($route, $resource.'.'.$action);
                $this->assertContains('auth', $route->gatherMiddleware());
            }
        }
        $this->assertContains('auth', Route::getRoutes()->getByName('avatars.show')->gatherMiddleware());
    }

    public function test_guests_are_redirected_from_list_create_show_and_edit_pages(): void
    {
        $customer = Customer::query()->create(['full_name' => 'Private Customer', 'email' => 'private@example.com']);
        $user = $this->targetUser();
        foreach ([
            '/customers', '/customers/create', '/customers/'.$customer->id, '/customers/'.$customer->id.'/edit',
            '/users', '/users/create', '/users/'.$user->id, '/users/'.$user->id.'/edit', '/dashboard',
            '/avatars/'.Str::uuid().'.jpg',
        ] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
        $this->assertGuest();
    }

    public function test_guest_write_attempts_do_not_create_update_or_delete_records(): void
    {
        $customer = Customer::query()->create(['full_name' => 'Original', 'email' => 'original@example.com']);
        $user = $this->targetUser();
        $this->post('/customers', ['full_name' => 'New', 'email' => 'new@example.com'])->assertRedirect(route('login'));
        $this->put('/customers/'.$customer->id, ['full_name' => 'Changed', 'email' => 'changed@example.com'])->assertRedirect(route('login'));
        $this->delete('/customers/'.$customer->id)->assertRedirect(route('login'));
        $this->post('/users', [])->assertRedirect(route('login'));
        $this->put('/users/'.$user->id, ['full_name' => 'Changed'])->assertRedirect(route('login'));
        $this->delete('/users/'.$user->id)->assertRedirect(route('login'));
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertSame('Original', $customer->fresh()->full_name);
        $this->assertSame('Target User', $user->fresh()->full_name);
    }

    public function test_authenticated_users_can_view_resource_details(): void
    {
        $this->signInOperator();
        $customer = Customer::query()->create(['full_name' => 'Details Customer', 'email' => 'details@example.com']);
        $user = $this->targetUser();
        $this->get('/customers/'.$customer->id)->assertOk()->assertSee('Details Customer');
        $this->get('/users/'.$user->id)->assertOk()->assertSee('target@example.com');
    }

    public function test_authenticated_customer_deletion_works(): void
    {
        $this->signInOperator();
        $customer = Customer::query()->create(['full_name' => 'Delete Customer', 'email' => 'delete@example.com']);
        $this->delete('/customers/'.$customer->id)->assertRedirect(route('customers.index'));
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_user_deletion_cleans_up_the_managed_avatar_and_reset_token(): void
    {
        Storage::fake('public');
        $this->signInOperator();
        $user = $this->targetUser();
        $path = 'avatars/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, 'avatar fixture');
        $user->avatar = $path;
        $user->save();
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => 'test-token', 'created_at' => now()]);
        $this->delete('/users/'.$user->id)->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_you_cannot_delete_the_account_currently_in_use(): void
    {
        $operator = $this->signInOperator();
        $this->delete('/users/'.$operator->id)->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $operator->id]);
    }

    public function test_creating_an_account_supplies_usable_hashed_login_credentials(): void
    {
        $this->signInOperator();
        $this->post('/users', [
            'username' => 'new.account', 'full_name' => 'New Account', 'email' => 'new@example.com',
            'password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!',
        ])->assertRedirect(route('users.index'));
        $user = User::query()->where('username', 'new.account')->firstOrFail();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
        $this->post('/logout');
        $this->post('/login', ['email' => 'new@example.com', 'password' => 'NewPassword123!']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_blank_password_edit_preserves_hash_and_new_password_rotates_remember_token(): void
    {
        $this->signInOperator();
        $user = $this->targetUser();
        $oldHash = $user->password;
        $user->remember_token = 'old-token';
        $user->save();
        $data = ['username' => $user->username, 'full_name' => $user->full_name, 'email' => $user->email, 'password' => '', 'password_confirmation' => ''];
        $this->put('/users/'.$user->id, $data)->assertSessionHasNoErrors();
        $this->assertSame($oldHash, $user->fresh()->password);
        $data['password'] = $data['password_confirmation'] = 'NewPassword123!';
        $this->put('/users/'.$user->id, $data)->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
        $this->assertNotSame('old-token', $user->fresh()->remember_token);
    }

    public function test_duplicate_email_cannot_be_bypassed_with_a_form_supplied_id(): void
    {
        $operator = $this->signInOperator();
        $user = $this->targetUser();
        $this->put('/users/'.$user->id, [
            'id' => $operator->id, 'username' => $user->username,
            'full_name' => $user->full_name, 'email' => $operator->email,
        ])->assertSessionHasErrors('email');
        $this->assertSame('target@example.com', $user->fresh()->email);
    }

    public function test_old_seeded_accounts_can_be_given_credentials_without_recreating_them(): void
    {
        $this->seed();
        $this->signInOperator();
        $user = User::query()->where('username', 'gian.mistica')->firstOrFail();
        $id = $user->id;
        $this->assertNull($user->password);
        $this->put('/users/'.$id, [
            'username' => $user->username, 'full_name' => $user->full_name,
            'email' => 'gian@example.com', 'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertSessionHasNoErrors();
        $this->assertSame($id, $user->fresh()->id);
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
        $this->assertDatabaseCount('users', 6);
    }

    public function test_changing_your_own_password_requires_the_current_password(): void
    {
        $operator = $this->signInOperator();
        $this->put('/users/'.$operator->id, [
            'username' => $operator->username, 'full_name' => $operator->full_name, 'email' => $operator->email,
            'password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!',
        ])->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('Testing123!', $operator->fresh()->password));
    }

    public function test_password_and_email_changes_revoke_pending_reset_tokens(): void
    {
        $this->signInOperator();
        $user = $this->targetUser();
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => 'fixture', 'created_at' => now()]);
        $data = ['username' => $user->username, 'full_name' => $user->full_name, 'email' => $user->email,
            'password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!'];
        $this->put('/users/'.$user->id, $data)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => 'fixture', 'created_at' => now()]);
        $user->email_verified_at = now();
        $user->save();
        $this->put('/users/'.$user->id, ['username' => $user->username, 'full_name' => $user->full_name, 'email' => 'changed@example.com'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'target@example.com']);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertSame('changed@example.com', $user->fresh()->email);
    }

}
