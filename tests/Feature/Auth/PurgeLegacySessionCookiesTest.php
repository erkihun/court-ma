<?php

declare(strict_types=1);

use App\Http\Middleware\PurgeLegacySessionCookies;
use App\Http\Middleware\SetSessionCookieForGuard;
use Illuminate\Http\Request;

/**
 * Production browsers carry three generations of session cookie
 * ("-session-admin", "pscourt-session-admin", "app-session-admin"), some set at
 * two Domain scopes. The duplicates make the resolved session — and the
 * XSRF-TOKEN paired with it — nondeterministic, which breaks OTP verification.
 */
function purgeWithCookies(array $cookies, string $currentCookie = 'app-session-admin', string $base = 'app-session', string $path = '/forgot-password')
{
    config(['session.cookie_base' => $base, 'session.cookie' => $currentCookie]);

    $request = Request::create($path, 'POST');

    foreach ($cookies as $name => $value) {
        $request->cookies->set($name, $value);
    }

    $response = (new PurgeLegacySessionCookies)->handle($request, fn () => response('ok'));

    // Symfony\Cookie exposes no public $name, so key the lookup on getName().
    return collect($response->headers->getCookies());
}

/** All emitted cookies carrying the given name (one per scope). */
function emittedNamed(iterable $cookies, string $name)
{
    return collect($cookies)->filter(fn ($c) => $c->getName() === $name)->values();
}

test('legacy session cookies present on the request are expired', function () {
    $sent = purgeWithCookies([
        '-session' => 'a',
        '-session-admin' => 'b',
        '-session-applicant' => 'c',
        'pscourt-session-admin' => 'd',
        'app-session-admin' => 'keep',
    ]);

    foreach (['-session', '-session-admin', '-session-applicant', 'pscourt-session-admin'] as $stale) {
        $forgotten = emittedNamed($sent, $stale)->first();

        expect($forgotten)->not->toBeNull("expected {$stale} to be expired")
            // Cookie::forget() clears the value and back-dates the expiry.
            ->and($forgotten->getValue())->toBeEmpty()
            ->and($forgotten->getExpiresTime())->toBeLessThan(time());
    }

    expect(emittedNamed($sent, 'app-session-admin'))->toBeEmpty();
});

test('the cookie currently in use is never expired', function () {
    $sent = purgeWithCookies([
        '-session-admin' => 'stale',
        'app-session-admin' => 'live',
    ]);

    expect(emittedNamed($sent, 'app-session-admin'))->toBeEmpty()
        ->and(emittedNamed($sent, '-session-admin'))->not->toBeEmpty();
});

test('a legacy name is expired at every scope it could have been set on', function () {
    config(['session.domain' => '.pscourt.gov.et']);

    $domains = emittedNamed(purgeWithCookies(['-session-admin' => 'stale']), '-session-admin')
        ->map(fn ($c) => $c->getDomain())
        ->all();

    // Host-only plus the shared parent domain, or the browser keeps sending it.
    expect($domains)->toContain(null)
        ->and($domains)->toContain('.pscourt.gov.et');
});

test('nothing is emitted when no legacy cookies are present', function () {
    expect(purgeWithCookies(['app-session-admin' => 'live']))->toBeEmpty();
});

test('the configured base and its portal variants are never listed as legacy', function () {
    // Production runs SESSION_COOKIE=pscourt-session with a cached config, so
    // env() is empty at runtime; the live names must come from config.
    config(['session.cookie_base' => 'pscourt-session']);

    $legacy = PurgeLegacySessionCookies::legacyNames();

    expect($legacy)->not->toContain('pscourt-session')
        ->not->toContain('pscourt-session-admin')
        ->not->toContain('pscourt-session-applicant')
        // The genuinely dead names are still purged.
        ->toContain('-session-admin')
        ->toContain('app-session-admin');

    expect(SetSessionCookieForGuard::liveNames())
        ->toBe(['pscourt-session', 'pscourt-session-admin', 'pscourt-session-applicant']);
});

test('a public-page request leaves the live admin and applicant cookies alone', function () {
    // Regression: switching language (a public path) signed every admin and
    // applicant out, because the purge ran under the plain base cookie and
    // treated the live portal cookies as retired.
    $sent = purgeWithCookies(
        [
            'pscourt-session-admin' => 'live-admin',
            'pscourt-session-applicant' => 'live-applicant',
            '-session-admin' => 'stale',
        ],
        currentCookie: 'pscourt-session',
        base: 'pscourt-session',
        path: '/language/am',
    );

    expect(emittedNamed($sent, 'pscourt-session-admin'))->toBeEmpty()
        ->and(emittedNamed($sent, 'pscourt-session-applicant'))->toBeEmpty()
        ->and(emittedNamed($sent, '-session-admin'))->not->toBeEmpty();
});

test('deletes survive the encryption layer in the real web stack', function () {
    config(['session.cookie_base' => 'pscourt-session']);

    $response = $this->withUnencryptedCookies([
        '-session-admin' => 'stale',
        'app-session-admin' => 'stale2',
        'pscourt-session-admin' => 'live',
    ])->get('/language/am?return=/dashboard');

    $response->assertRedirect('/dashboard');

    $cookies = $response->baseResponse->headers->getCookies();

    foreach (['-session-admin', 'app-session-admin'] as $stale) {
        $deletes = emittedNamed($cookies, $stale);

        expect($deletes)->not->toBeEmpty("expected {$stale} to be deleted");

        foreach ($deletes as $delete) {
            // EncryptCookies must not re-encrypt these: a non-empty value would
            // hand the browser a fresh cookie instead of removing the old one.
            expect($delete->getValue())->toBe('')
                ->and($delete->getExpiresTime())->toBeLessThan(time());
        }
    }

    // The switch ran in the admin portal's session: its cookie is re-issued
    // (encrypted, in the future), never expired.
    $admin = emittedNamed($cookies, 'pscourt-session-admin');

    expect($admin)->not->toBeEmpty();

    foreach ($admin as $cookie) {
        expect($cookie->getValue())->not->toBe('')
            ->and($cookie->getExpiresTime())->toBeGreaterThan(time());
    }

    // The CSRF cookie must still be encrypted.
    foreach ($cookies as $cookie) {
        if ($cookie->getName() === 'XSRF-TOKEN') {
            expect($cookie->getValue())->not->toBe('');
        }
    }
});
