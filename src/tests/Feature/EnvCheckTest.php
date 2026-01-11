<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EnvCheckTest extends TestCase
{
    public function test_env_testing_is_loaded()
    {
        $this->assertEquals('sqlite', config('database.default'));
    }
}
