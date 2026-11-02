<?php

namespace App\Livewire\Contents;

use App\Models\Contents\Content;
use App\Services\AccessService;
use App\Services\Contents\ShortcodeRenderer;
use Livewire\Component;

class Show extends Component
{
    public Content $content;

    public function mount(Content $content): void
    {
        abort_unless($content->isPublished() || auth()->user()?->is_admin, 404);
        $this->content = $content;
    }

    public function render(AccessService $access, ShortcodeRenderer $renderer)
    {
        $user = auth()->user();
        $locked = false;

        if ($this->content->requires_login && ! $user) {
            $locked = 'login';
        } elseif ($this->content->product_id && (! $user || ! $access->hasAccess($user, $this->content->product->key))) {
            $locked = 'paywall';
        }

        return view('livewire.contents.show', [
            'locked' => $locked,
            'html' => $locked ? '' : $renderer->render($this->content->body),
            'related' => $this->content->topic?->allContents()->where('id', '!=', $this->content->id)->take(4) ?? collect(),
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'back' => route('contents.index')]);
    }
}
