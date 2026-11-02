<?php

namespace App\Livewire\Contents;

use App\Models\Contents\Content;
use App\Models\Contents\Topic as TopicModel;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.contents.index', [
            'topics' => TopicModel::query()->whereNull('parent_id')->with('children')->orderBy('position')->get(),
            'latest' => Content::query()->published()->with('topic')->orderByDesc('published_at')->take(6)->get(),
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => __('Materiales'), 'back' => route('home')]);
    }
}
