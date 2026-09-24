<?php

test('legacy fortify registration routes are disabled', function () {
    expect(route('join'))->toContain('/join');

    $this->get('/register')->assertNotFound();
});
