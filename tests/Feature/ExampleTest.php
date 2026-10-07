<?php

test('the application returns a successful response', function () {
    \Tests\Support\BuyerDatabase::migrate();
    $response = $this->get('/');

    $response->assertStatus(200);
});
