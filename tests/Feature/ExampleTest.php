<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_renders(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_guests_are_sent_to_login_from_protected_apps(): void
    {
        $this->get('/mdp')->assertRedirect('/login');
        $this->get('/fichas')->assertRedirect('/login');
        $this->get('/tests')->assertRedirect('/login');
    }
}
