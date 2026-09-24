<?php

namespace App\Livewire\Posts;

use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class Show extends Component
{
    public Post $post;

    public function mount(Post $post): void
    {
        abort_unless($post->published_at !== null && $post->published_at->lte(now()), 404);
        $this->post = $post->load(['category', 'images', 'author']);
    }

    public function render()
    {
        return view('livewire.posts.show')
            ->title($this->post->title);
    }
}
