<?php

use App\Support\Media;
use Illuminate\Support\Facades\Storage;

test('branding falls back to bundled assets when the cache store is unavailable', function () {
    config([
        'cache.default' => 'database',
        'cache.stores.database.table' => 'table_that_does_not_exist',
    ]);

    expect(Media::logoUrl())->toBe(asset('images/lga-logo.png'));
});

test('branding uses the media disk url when the asset exists', function () {
    Storage::fake('public');
    config(['filesystems.media' => 'public']);
    Storage::disk('public')->put(config('branding.logo'), 'logo');

    expect(Media::logoUrl())->toBe(Storage::disk('public')->url(config('branding.logo')));
});
