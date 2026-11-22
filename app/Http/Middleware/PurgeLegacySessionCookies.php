<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Expire session cookies left behind by earlier cookie-naming generations.
 *
 * Production accumulated three generations of admin/applicant session cookie:
 *
 *   -session-admin          (empty Str::slug() of the Amharic APP_NAME)
 *   pscourt-session-admin   (an explicit SESSION_COOKIE)
 *   app-session-admin       (the 'app' fallback)
 *
 * Several were also set at two different Domain scopes (host-only and
 * .pscourt.gov.et), so the browser sends the same name twice. PHP keeps only
 * one of each duplicate, so the session backing an OTP — and the XSRF-TOKEN
 * that must match it — can resolve to different generations on consecutive
 * requests. The OTP written during "send code" is then unreadable during
 * "verify code".
 *
 * This deletes every stale name at every scope it could have been set on,
 * leaving only the cookies the app currently issues. Which names are live is
 * read from the loaded session config on every request — never from env(),
 * which is empty once the config is cached in production. Remove this
 * middleware once traffic no longer carries the legacy names (roughly one
 * session lifetime after deploy).
 *
 * It is prepended to the web group so it runs outside EncryptCookies: the
 * delete cookies carry an empty value and must not be re-encrypted into a
 * fresh payload, and the stale request cookies must still be visible by name
 * after EncryptCookies has failed to decrypt them.
 */
class PurgeLegacySessionCookies
{
    /**
     * Cookie name stems that were issued at some point.
     *
     * The '-admin' / '-applicant' suffixes are applied on top of these.
     */
    private const LEGACY_BASES = [
        '-session',
        'pscourt-session',
        'app-session',
    ];

    /**
     * Every retired cookie name, including the per-portal suffixes.
     *
     * Whatever base the session config currently resolves to is live and is
     * always excluded, in all three of its forms.
     *
     * @return list<string>
     */
    public static function legacyNames(): array
    {
        $live = SetSessionCookieForGuard::liveNames();

        $names = [];

        foreach (self::LEGACY_BASES as $base) {
            foreach ([$base, $base.'-admin', $base.'-applicant'] as $name) {
                if (! in_array($name, $live, true)) {
                    $names[] = $name;
                }
            }
        }

        return $names;
    }

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $current = (string) config('session.cookie');

        $secure = (bool) config('session.secure', false);

        foreach ($this->staleNames($request, $current) as $name) {
            foreach ($this->scopes($request) as $domain) {
                // Built directly rather than via Cookie::forget(): the CookieJar
                // substitutes its configured default domain for a null one,
                // which would lose the host-only scope. ResponseHeaderBag keys
                // cookies by name+path+domain, so each scope survives.
                $response->headers->setCookie(
                    new SymfonyCookie(
                        name: $name,
                        value: '',
                        expire: 1,
                        path: '/',
                        domain: $domain,
                        secure: $secure,
                        httpOnly: true,
                        raw: false,
                        sameSite: null,
                    )
                );
            }
        }

        return $response;
    }

    /**
     * Legacy cookie names actually present on this request.
     *
     * @return list<string>
     */
    private function staleNames(Request $request, string $current): array
    {
        $live = SetSessionCookieForGuard::liveNames();

        $stale = [];

        foreach (self::legacyNames() as $name) {
            // Never delete the cookie this request is using, nor any other
            // cookie the app still issues: a public-page request must leave
            // the admin and applicant sessions alone.
            if ($name === $current || in_array($name, $live, true)) {
                continue;
            }

            if ($request->cookies->has($name)) {
                $stale[] = $name;
            }
        }

        return $stale;
    }

    /**
     * Every domain scope a legacy cookie could have been written at.
     *
     * A delete only removes a cookie when it matches the original scope, so
     * both the host-only and the configured shared domain must be covered.
     *
     * @return list<string|null>
     */
    private function scopes(Request $request): array
    {
        $scopes = [null];                   // host-only (no Domain attribute)

        $configured = (string) config('session.domain', '');

        if ($configured !== '') {
            $scopes[] = $configured;        // e.g. .pscourt.gov.et
        }

        // Guard against the shared domain having been unset at some point.
        $host = $request->getHost();

        if ($host !== '' && $host !== $configured) {
            $scopes[] = $host;
        }

        return $scopes;
    }
}
