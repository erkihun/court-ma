<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SetSessionCookieForGuard
{
    /**
     * Paths served by the staff (admin) portal. Anything else that is not an
     * applicant/respondent path uses the plain base cookie.
     */
    private const ADMIN_PATTERNS = [
        'login',
        'logout',
        'register',
        'forgot-password',
        'password-otp',
        'password-otp/*',
        'new-password',
        'new-password/*',
        'verify-email',
        'verify-email/*',
        'confirm-password',
        'force-password',
        'password',
        'email/verification-notification',
        'dashboard',
        'profile',
        'profile/*',
        'mfa/*',
        'mfa-challenge',
        'mfa-challenge/*',
        'users',
        'users/*',
        'roles',
        'roles/*',
        'admin',
        'admin/*',
    ];

    private const APPLICANT_PATTERNS = [
        'applicant',
        'applicant/*',
        'respondent',
        'respondent/*',
    ];

    /**
     * Set a distinct session cookie name per guard context to avoid collisions.
     *
     * This runs before StartSession so the correct cookie is read/written.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $base = self::base();
        $portal = $this->portalFor($request);

        config(['session.cookie' => $portal === null ? $base : $base.'-'.$portal]);

        return $next($request);
    }

    /**
     * The configured session cookie base, normalised to a name browsers and
     * proxies will keep.
     *
     * Never build a cookie name off an empty or dash-leading base: browsers
     * and proxies may drop it, which silently destroys the session that holds
     * pending OTP codes between the send and verify requests.
     */
    public static function base(): string
    {
        $base = (string) config('session.cookie_base', config('session.cookie'));

        $base = trim($base, '-');

        if ($base === '') {
            $base = 'app-session';
        }

        return $base;
    }

    /**
     * Every cookie name the application currently issues.
     *
     * @return list<string>
     */
    public static function liveNames(): array
    {
        $base = self::base();

        return [$base, $base.'-admin', $base.'-applicant'];
    }

    /**
     * Which portal a request path belongs to: 'admin', 'applicant' or null for
     * the public site. The respondent portal uses the applicant guard, so both
     * portal modes intentionally share the applicant session cookie.
     */
    public static function portalForPath(string $path): ?string
    {
        $path = trim($path, '/');

        if ($path === '') {
            return null;
        }

        if (Str::is(self::ADMIN_PATTERNS, $path)) {
            return 'admin';
        }

        if (Str::is(self::APPLICANT_PATTERNS, $path)) {
            return 'applicant';
        }

        return null;
    }

    private function portalFor(Request $request): ?string
    {
        // The language switch lives outside every portal, but it must run in
        // the session of the portal the visitor came from. Otherwise the chosen
        // locale lands in a throw-away public session and, worse, the live
        // admin/applicant cookie is foreign to this request, so any cookie
        // clean-up on the response would discard it and sign the visitor out.
        if ($request->is('language/*')) {
            return self::portalForPath($this->originPath($request));
        }

        return self::portalForPath($request->path());
    }

    /** The path the visitor will be sent back to after switching language. */
    private function originPath(Request $request): string
    {
        $origin = (string) ($request->query('return') ?: $request->headers->get('referer', ''));

        if ($origin === '') {
            return '';
        }

        $path = parse_url($origin, PHP_URL_PATH);

        return is_string($path) ? $path : '';
    }
}
