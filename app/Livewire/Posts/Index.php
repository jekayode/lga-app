<?php

namespace App\Livewire\Posts;

use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.public')]
#[Title('News')]
class Index extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.posts.index', [
            'posts' => Post::query()->published()->with('category')->paginate(8),
        ]);
    }
}
