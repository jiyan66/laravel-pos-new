<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosListingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_provides_five_records_in_each_table_without_duplicates(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('customers', 5);
        $this->assertDatabaseCount('users', 5);
        $this->assertSame(5, User::query()->distinct()->count('username'));
    }

    public function test_home_and_about_pages_work(): void
    {
        $this->get('/')->assertOk()->assertSee('Customer Accounts')->assertSee('User Accounts');
        $this->get('/about')->assertOk()->assertSee('Joseph Gian Carlo Mistica');
    }

    public function test_customers_listing_uses_database_records(): void
    {
        Customer::query()->create([
            'full_name' => 'A Database Customer',
            'email' => 'database-only@example.com',
            'phone' => '09171234567',
        ]);

        $this->signInOperator();
        $this->get('/customers')->assertOk()
            ->assertSee('A Database Customer')
            ->assertSee('database-only@example.com')
            ->assertSee('09171234567');
    }

    public function test_users_listing_uses_database_records(): void
    {
        User::query()->create(['username' => 'database.user', 'full_name' => 'Database User']);

        $this->actingAs(User::query()->first());
        $this->get('/users')->assertOk()->assertSee('database.user')->assertSee('Database User');
    }

    public function test_both_listings_display_the_full_seeded_collections(): void
    {
        $this->seed();

        $this->actingAs(User::query()->first());
        $this->get('/customers')->assertOk()
            ->assertViewHas('customers', fn ($records) => $records->count() === 5);
        $this->get('/users')->assertOk()
            ->assertViewHas('users', fn ($records) => $records->count() === 5);
    }

    public function test_empty_customers_have_a_readable_empty_state(): void
    {
        $this->signInOperator();
        $this->get('/customers')->assertOk()->assertSee('No customer records are available.');
    }

    public function test_database_text_is_html_escaped(): void
    {
        $payload = '<script>alert("test")</script>';
        Customer::query()->create(['full_name' => $payload, 'email' => 'safe@example.com']);
        User::query()->create(['username' => 'escaped', 'full_name' => $payload]);

        $this->actingAs(User::query()->first());
        foreach (['/customers', '/users'] as $url) {
            $this->get($url)->assertOk()->assertSee($payload)->assertDontSee($payload, false);
        }
    }

    public function test_username_is_unique_at_database_level(): void
    {
        User::query()->create(['username' => 'duplicate', 'full_name' => 'First User']);
        $this->expectException(QueryException::class);
        User::query()->create(['username' => 'duplicate', 'full_name' => 'Second User']);
    }
}
