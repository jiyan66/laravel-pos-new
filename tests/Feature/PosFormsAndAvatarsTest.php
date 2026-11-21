<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use App\Support\AvatarPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PosFormsAndAvatarsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->signInOperator();
    }

    private function makeUser(string $username = 'sample.user'): User
    {
        return User::query()->create(['username' => $username, 'full_name' => 'Sample User', 'email' => $username.'@example.com', 'password' => 'Testing123!']);
    }

    private function oldAvatar(User $user): string
    {
        $path = 'avatars/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, 'old avatar bytes');
        $user->avatar = $path;
        $user->save();

        return $path;
    }

    public function test_create_forms_and_prefilled_edit_forms_are_available(): void
    {
        $customer = Customer::query()->create(['full_name' => 'Existing Customer', 'email' => 'existing@example.com', 'phone' => '09171234567']);
        $user = $this->makeUser();

        $this->get('/customers/create')->assertOk()->assertSee('name="_token"', false);
        $this->get('/users/create')->assertOk()->assertSee('name="_token"', false);
        $this->get('/customers/'.$customer->id.'/edit')->assertOk()
            ->assertSee('value="Existing Customer"', false)->assertSee('existing@example.com');
        $this->get('/users/'.$user->id.'/edit')->assertOk()
            ->assertSee('value="sample.user"', false)
            ->assertSee('multipart/form-data', false)->assertSee('name="avatar"', false);
    }

    public function test_valid_customer_is_saved_and_redirected(): void
    {
        $this->post('/customers', ['full_name' => 'New Customer', 'email' => 'new@example.com', 'phone' => '09175555555'])
            ->assertRedirect(route('customers.index'))->assertSessionHas('success');
        $this->assertDatabaseHas('customers', ['full_name' => 'New Customer', 'email' => 'new@example.com']);
    }

    public function test_customer_requires_a_name_and_valid_email_and_preserves_old_input(): void
    {
        $this->from('/customers/create')->post('/customers', ['full_name' => 'Kept Name', 'email' => 'invalid-email'])
            ->assertRedirect('/customers/create')->assertSessionHasErrors('email')
            ->assertSessionHasInput('full_name', 'Kept Name')->assertSessionHasInput('email', 'invalid-email');
        $this->assertDatabaseCount('customers', 0);

        $this->post('/customers', [])->assertSessionHasErrors(['full_name', 'email']);
    }

    public function test_customer_edit_changes_the_existing_record(): void
    {
        $customer = Customer::query()->create(['full_name' => 'Before', 'email' => 'before@example.com', 'phone' => '123']);
        $this->put('/customers/'.$customer->id, ['full_name' => 'After', 'email' => 'after@example.com', 'phone' => '456'])
            ->assertRedirect(route('customers.index'));
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'full_name' => 'After', 'phone' => '456']);
    }

    public function test_invalid_customer_edit_does_not_change_the_database(): void
    {
        $customer = Customer::query()->create(['full_name' => 'Original', 'email' => 'original@example.com']);
        $this->put('/customers/'.$customer->id, ['full_name' => 'Changed', 'email' => 'invalid'])->assertSessionHasErrors('email');
        $this->assertSame('Original', $customer->fresh()->full_name);
    }

    public function test_user_create_requires_name_and_username(): void
    {
        $this->post('/users', [])->assertSessionHasErrors(['username', 'full_name', 'email', 'password']);
        $this->assertDatabaseCount('users', 1);
        $this->post('/users', ['username' => 'new.user', 'full_name' => 'New User', 'email' => 'new@example.com', 'password' => 'Testing123!', 'password_confirmation' => 'Testing123!'])
            ->assertRedirect(route('users.index'))->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['username' => 'new.user', 'full_name' => 'New User', 'avatar' => null]);
    }

    public function test_duplicate_username_is_rejected_and_old_name_is_kept(): void
    {
        $this->makeUser('taken');
        $this->from('/users/create')->post('/users', ['username' => 'taken', 'full_name' => 'Keep This Name', 'email' => 'other@example.com', 'password' => 'Testing123!', 'password_confirmation' => 'Testing123!'])
            ->assertSessionHasErrors('username')->assertSessionHasInput('full_name', 'Keep This Name');
        $this->assertDatabaseCount('users', 2);
    }

    public function test_user_can_keep_their_own_username_on_update(): void
    {
        $user = $this->makeUser();
        $this->put('/users/'.$user->id, ['username' => $user->username, 'email' => $user->email, 'full_name' => 'Updated Name'])
            ->assertRedirect(route('users.index'))->assertSessionHasNoErrors();
        $this->assertSame('Updated Name', $user->fresh()->full_name);
    }

    public function test_form_id_cannot_bypass_another_users_username_uniqueness(): void
    {
        $first = $this->makeUser('first');
        $second = $this->makeUser('second');
        $this->put('/users/'.$first->id, ['id' => $second->id, 'username' => 'second', 'email' => $first->email, 'full_name' => 'Attempt'])
            ->assertSessionHasErrors('username');
        $this->assertSame('first', $first->fresh()->username);
    }

    public function test_uploaded_image_is_prepared_as_a_256_pixel_jpeg(): void
    {
        $user = $this->makeUser();
        $this->put('/users/'.$user->id, [
            'username' => $user->username, 'email' => $user->email, 'full_name' => $user->full_name,
            'avatar' => UploadedFile::fake()->image('portrait.png', 600, 400),
        ])->assertRedirect(route('users.index'))->assertSessionHasNoErrors();

        $path = $user->fresh()->avatar;
        $this->assertTrue(AvatarPath::isManaged($path));
        Storage::disk('public')->assertExists($path);
        $info = getimagesize(Storage::disk('public')->path($path));
        $this->assertSame(256, $info[0]);
        $this->assertSame(256, $info[1]);
        $this->assertSame('image/jpeg', $info['mime']);
        $this->assertCount(1, Storage::disk('public')->allFiles('avatars'));
        $this->get('/users')->assertOk()->assertSee(route('avatars.show', ['filename' => basename($path)]));
    }

    public function test_text_only_update_preserves_the_existing_avatar(): void
    {
        $user = $this->makeUser();
        $old = $this->oldAvatar($user);
        $this->put('/users/'.$user->id, ['username' => $user->username, 'email' => $user->email, 'full_name' => 'Text Changed'])
            ->assertSessionHasNoErrors();
        $this->assertSame($old, $user->fresh()->avatar);
        Storage::disk('public')->assertExists($old);
    }

    public function test_replacement_removes_old_avatar_after_successful_save(): void
    {
        $user = $this->makeUser();
        $old = $this->oldAvatar($user);
        $this->put('/users/'.$user->id, [
            'username' => $user->username, 'email' => $user->email, 'full_name' => 'New Picture',
            'avatar' => UploadedFile::fake()->image('replacement.jpg', 400, 400),
        ])->assertSessionHasNoErrors();
        $this->assertNotSame($old, $user->fresh()->avatar);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($user->fresh()->avatar);
    }

    public function test_oversized_upload_does_not_change_user_or_existing_avatar(): void
    {
        $user = $this->makeUser();
        $old = $this->oldAvatar($user);
        $this->put('/users/'.$user->id, [
            'username' => $user->username, 'email' => $user->email, 'full_name' => 'Must Not Save',
            'avatar' => UploadedFile::fake()->image('large.png', 50, 50)->size(2049),
        ])->assertSessionHasErrors('avatar');
        $this->assertSame('Sample User', $user->fresh()->full_name);
        $this->assertSame($old, $user->fresh()->avatar);
        Storage::disk('public')->assertExists($old);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_non_image_and_excessive_dimensions_are_rejected(): void
    {
        $user = $this->makeUser();
        foreach ([UploadedFile::fake()->create('document.pdf', 10, 'application/pdf'), UploadedFile::fake()->image('wide.png', 3001, 10)] as $file) {
            $this->put('/users/'.$user->id, ['username' => $user->username, 'email' => $user->email, 'full_name' => $user->full_name, 'avatar' => $file])
                ->assertSessionHasErrors('avatar');
        }
        $this->assertCount(0, Storage::disk('public')->allFiles());
    }

    public function test_script_extension_cannot_be_uploaded_as_an_avatar(): void
    {
        $user = $this->makeUser();
        $png = UploadedFile::fake()->image('source.png', 20, 20);
        $file = UploadedFile::fake()->createWithContent('picture.php', file_get_contents($png->getPathname()));
        $this->put('/users/'.$user->id, ['username' => $user->username, 'email' => $user->email, 'full_name' => $user->full_name, 'avatar' => $file])
            ->assertSessionHasErrors('avatar');
        $this->assertCount(0, Storage::disk('public')->allFiles());
    }

    public function test_avatar_upload_is_on_edit_only(): void
    {
        $this->post('/users', ['username' => 'new', 'full_name' => 'New User', 'email' => 'new@example.com', 'password' => 'Testing123!', 'password_confirmation' => 'Testing123!', 'avatar' => UploadedFile::fake()->image('photo.jpg')])
            ->assertSessionHasErrors('avatar');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_a_browser_cannot_supply_an_arbitrary_avatar_path(): void
    {
        $user = $this->makeUser();
        $this->put('/users/'.$user->id, ['username' => $user->username, 'email' => $user->email, 'full_name' => $user->full_name, 'avatar' => '../../.env'])
            ->assertSessionHasErrors('avatar');
        $this->assertNull($user->fresh()->avatar);
        $this->assertFalse(AvatarPath::isManaged('avatars/../../.env'));
    }

    public function test_placeholder_is_used_for_missing_or_unset_images(): void
    {
        $user = $this->makeUser();
        $this->get('/users')->assertOk()->assertSee('images/avatar-placeholder.svg');
        $user->avatar = 'avatars/'.Str::uuid().'.jpg';
        $user->save();
        $this->assertSame(asset('images/avatar-placeholder.svg'), $user->fresh()->avatar_url);
    }

    public function test_avatar_route_streams_only_managed_jpeg_files(): void
    {
        $name = Str::uuid().'.jpg';
        Storage::disk('public')->put('avatars/'.$name, 'jpeg fixture');
        $this->get('/avatars/'.$name)->assertOk()->assertHeader('Content-Type', 'image/jpeg')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/avatars/not-an-avatar.php')->assertNotFound();
        $this->get('/avatars/'.Str::uuid().'.jpg')->assertNotFound();
    }

    public function test_missing_records_return_404(): void
    {
        $this->get('/customers/99999/edit')->assertNotFound();
        $this->get('/users/99999/edit')->assertNotFound();
    }

    public function test_failed_database_update_removes_new_file_and_keeps_old_avatar(): void
    {
        $user = $this->makeUser();
        $old = $this->oldAvatar($user);
        $caught = null;
        User::updating(function (): void { throw new \RuntimeException('Simulated database failure'); });
        $this->withoutExceptionHandling();

        try {
            $this->put('/users/'.$user->id, [
                'username' => $user->username, 'email' => $user->email, 'full_name' => 'Cannot Save',
                'avatar' => UploadedFile::fake()->image('replacement.png', 300, 200),
            ]);
        } catch (\RuntimeException $exception) {
            $caught = $exception;
        } finally {
            User::flushEventListeners();
        }

        $this->assertNotNull($caught);
        $this->assertSame('Simulated database failure', $caught->getMessage());
        $this->assertSame('Sample User', $user->fresh()->full_name);
        $this->assertSame($old, $user->fresh()->avatar);
        Storage::disk('public')->assertExists($old);
        $this->assertCount(1, Storage::disk('public')->allFiles('avatars'));
    }
}
