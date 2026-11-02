<?php

namespace App\Livewire\Contents;

use App\Models\Contents\Topic as TopicModel;
use Livewire\Component;

class Topic extends Component
{
    public TopicModel $topic;

    public function mount(TopicModel $topic): void
    {
        $this->topic = $topic;
    }

    public function render()
    {
        return view('livewire.contents.topic', [
            'contents' => $this->topic->allContents()->load('topic'),
            'children' => $this->topic->children,
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => $this->topic->name, 'back' => route('contents.index')]);
    }
}
