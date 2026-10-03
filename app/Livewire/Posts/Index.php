<?php

namespace App\Livewire\Posts;

use App\Models\Post;
use App\Support\Seo;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.public')]
#[Title('News')]
class Index extends Component
{
    use WithPagination;

    public function render(Seo $seo)
    {
        $posts = Post::query()->published()->with('category')->paginate(8);
        $page = $posts->currentPage();

        $seo->title($page > 1 ? "News — Page {$page}" : 'News')
            ->description('Latest news, events and updates from the Lagos Grassroots Alliance across all 20 Local Governments of Lagos State.')
            ->canonical($page > 1 ? $posts->url($page) : route('posts.index'))
            ->indexable()
            ->schema([
                '@type' => 'CollectionPage',
                'name' => 'Lagos Grassroots Alliance News',
                'url' => route('posts.index'),
                'isPartOf' => ['@id' => url('/').'/#website'],
            ]);

        return view('livewire.posts.index', [
            'posts' => $posts,
        ]);
    }
}
