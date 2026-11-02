<?php

namespace App\Livewire\Tests;

use App\Models\Tests\Test;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $takes = auth()->user()->testTakes()->get()->groupBy('test_id');

        return view('livewire.tests.index', [
            'tests' => Test::query()->where('active', true)->orderBy('position')->get(),
            'takes' => $takes,
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => __('Tests'), 'back' => route('home')]);
    }
}
