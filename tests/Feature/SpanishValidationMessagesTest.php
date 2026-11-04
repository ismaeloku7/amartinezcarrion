<?php

namespace Tests\Feature;

use App\Livewire\Mdp\Devotional;
use App\Models\Product;
use App\Models\User;
use App\Services\Mdp\MdpService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;
use Tests\TestCase;

class SpanishValidationMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_messages_are_in_spanish_and_never_show_the_raw_key(): void
    {
        app()->setLocale('es');
        $errors = Validator::make(['a' => '', 'b' => str_repeat('x', 5), 'c' => 'no'], [
            'a' => 'required', 'b' => 'string|max:3', 'c' => 'email',
        ])->errors();

        $this->assertSame('Campo requerido.', $errors->first('a'));
        $this->assertSame('Máximo 3 caracteres.', $errors->first('b'));
        $this->assertSame('No es un correo válido.', $errors->first('c'));
        $this->assertSame('El correo o la contraseña no son correctos.', __('auth.failed'));
    }

    public function test_an_empty_written_prayer_says_campo_requerido(): void
    {
        CarbonImmutable::setTestNow('2026-11-04 08:00:00');
        Product::create(['key' => 'mdp', 'type' => 'practice', 'name' => ['es' => 'MDP'], 'is_free' => true]);
        $user = User::factory()->create(['timezone' => 'UTC', 'locale' => 'es']);
        app(MdpService::class)->enroll($user, ['rest_weekday' => 6]);

        $c = Livewire::actingAs($user)->test(Devotional::class)
            ->call('next')->call('next')
            ->set('newText', 'Nadie puede recibir nada')->call('addText')->call('next')->call('next')
            ->assertSet('step', 'written_prayer')
            ->call('next')
            ->assertHasErrors(['prayer' => 'required'])
            ->assertSee('Campo requerido.')
            ->assertDontSee('validation.required');
    }

    public function test_failed_login_message_is_in_spanish(): void
    {
        User::factory()->create(['email' => 'a@example.com']);

        $this->from('/login')->post('/login', ['email' => 'a@example.com', 'password' => 'mal'])
            ->assertSessionHasErrors(['email' => 'El correo o la contraseña no son correctos.']);
    }
}
