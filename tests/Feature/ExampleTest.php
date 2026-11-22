<?php

it('redirects visitors from the site root to applicant login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('applicant.login'));
});
