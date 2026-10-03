<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $posts = Post::query()->published()->with('images')->get();

        return response()
            ->view('seo.sitemap', [
                'pages' => [
                    ['url' => route('home'), 'lastmod' => $posts->max('updated_at'), 'priority' => '1.0'],
                    ['url' => route('posts.index'), 'lastmod' => $posts->max('updated_at'), 'priority' => '0.8'],
                    ['url' => route('join'), 'lastmod' => null, 'priority' => '0.8'],
                    ['url' => route('become-agent'), 'lastmod' => null, 'priority' => '0.6'],
                ],
                'posts' => $posts,
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
