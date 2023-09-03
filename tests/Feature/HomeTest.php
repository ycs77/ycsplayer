<?php

use function Pest\Laravel\{get};

test('show home view', function () {
    get('/')->assertStatus(200);
});
