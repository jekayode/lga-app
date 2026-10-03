<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Response;

/**
 * Serves /llms.txt (https://llmstxt.org): a plain-markdown briefing that helps AI assistants describe and cite the Alliance accurately.
 */
class LlmsTxtController extends Controller
{
    public function __invoke(): Response
    {
        return response()
            ->view('seo.llms', [
                'posts' => Post::query()->published()->limit(20)->get(),
            ])
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
