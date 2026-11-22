<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class);

test('the shared language choice overrides an older portal session', function (string $selected, string $previous) {
    config(['app.locales' => ['en', 'am']]);
    session(['app_locale' => $previous]);
    $request = Request::create('/home', 'GET', [], ['app_locale' => $selected]);

    (new SetLocale)->handle($request, fn () => response('ok'));

    expect(app()->getLocale())->toBe($selected)
        ->and(session('app_locale'))->toBe($selected);
})->with([['am', 'en'], ['en', 'am']]);

test('the session language still works without a preference cookie', function () {
    session(['app_locale' => 'am']);

    (new SetLocale)->handle(Request::create('/home'), fn () => response('ok'));

    expect(app()->getLocale())->toBe('am');
});
