<?php

namespace App\Livewire\Portal;

use App\Models\Tests\Test;
use App\Services\AccessService;
use Livewire\Component;

class Home extends Component
{
    public function render(AccessService $access)
    {
        $user = auth()->user();

        $modules = [
            [
                'key' => 'mdp',
                'name' => __('Método Devocional Proyectivo'),
                'description' => __('La práctica diaria del libro «Dios como mi terapeuta a través de la Biblia».'),
                'route' => route('mdp.today'),
                'color' => '#7a1f1f',
                'access' => $user ? $access->hasAccess($user, 'mdp') : false,
            ],
            [
                'key' => 'cards',
                'name' => __('El arte de dar forma a tu cerebro'),
                'description' => __('Las 42 fichas del programa de crecimiento, con repasos y ficha del día.'),
                'route' => route('cards.today'),
                'color' => '#2b5d8c',
                'access' => $user ? $access->hasAccess($user, 'cards') : false,
            ],
        ];

        return view('livewire.portal.home', [
            'modules' => $modules,
            'tests' => Test::query()->where('active', true)->orderBy('position')->get(),
        ])->layout('components.layouts.app', ['appKey' => 'portal']);
    }
}
