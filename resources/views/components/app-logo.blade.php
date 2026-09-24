@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="LGA" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md">
            <img src="{{ \App\Support\Media::logoUrl() }}" alt="" class="size-8 object-contain">
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="LGA" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md">
            <img src="{{ \App\Support\Media::logoUrl() }}" alt="" class="size-8 object-contain">
        </x-slot>
    </flux:brand>
@endif
