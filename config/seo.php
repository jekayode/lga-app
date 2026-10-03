<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default page metadata
    |--------------------------------------------------------------------------
    |
    | Used when a page does not set its own description. Keep the description
    | under ~160 characters so search engines show it in full.
    |
    */

    'tagline' => 'One Lagos, Many Voices',

    'description' => 'Lagos Grassroots Alliance is a non-partisan, people-led movement uniting Lagosians across all 20 Local Governments to protect votes, strengthen institutions and be heard.',

    'locale' => 'en_NG',

    /*
    | Official social profile URLs (comma-separated), used for Organization
    | structured data so search engines and AI assistants can link them.
    */

    'same_as' => array_values(array_filter(explode(',', (string) env('SEO_SAME_AS', '')))),

    'twitter_handle' => env('SEO_TWITTER_HANDLE'),

];
