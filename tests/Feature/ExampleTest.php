<?php

test('guests are redirected to login from the root url', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
