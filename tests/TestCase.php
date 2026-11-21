<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Backend tests do not need a running Vite server. VERIFY separately checks built assets.
        $this->withoutVite();
    }

    protected function signInOperator(): \App\Models\User
    {
        $operator = \App\Models\User::query()->create([
            'username' => 'test.operator', 'full_name' => 'Test Operator',
            'email' => 'operator@example.com', 'password' => 'Testing123!',
        ]);
        $this->actingAs($operator);

        return $operator;
    }

    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}
