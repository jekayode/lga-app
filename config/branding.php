<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public media paths on the configured MEDIA_DISK (local or R2)
    |--------------------------------------------------------------------------
    |
    | Suggested R2 layout:
    |   branding/logo.png
    |   branding/logo-white.png
    |   branding/hero.webp
    |   posts/featured/{slug}.webp
    |   posts/gallery/{event-slug}/{n}.webp
    |
    */

    'logo' => env('BRAND_LOGO_PATH', 'branding/logo.png'),
    'logo_white' => env('BRAND_LOGO_WHITE_PATH', 'branding/logo-white.png'),
    'hero' => env('BRAND_HERO_PATH', 'branding/hero.webp'),

];
