<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Arr;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        $allowed = config('app.locales', ['en', 'am']);
        $allowed = array_unique(array_merge($allowed, ['am_ET']));

        if (!in_array($locale, $allowed, true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        if (str_starts_with($locale, 'am')) {
            $locale = 'am';
        }

        session(['app_locale' => $locale]);
        Cookie::queue('app_locale', $locale, 60 * 24 * 365);

        App::setLocale($locale);

        if ($request->expectsJson()) {
            $from = $locale === 'am' ? 'en' : 'am';
            $loader = app('translator')->getLoader();
            $path = parse_url((string) $request->query('return', ''), PHP_URL_PATH) ?? '';
            $preferredGroup = match (true) {
                str_contains($path, '/register'), str_contains($path, '/login') => 'auth',
                str_contains($path, '/cases') => 'cases',
                default => 'app',
            };
            $groups = array_unique([$preferredGroup, 'app', ...array_map(fn ($path) => basename($path, '.php'), glob(lang_path('en/*.php'))), '*']);
            $pairs = [];
            foreach ($groups as $group) {
                $source = Arr::dot($loader->load($from, $group, '*'));
                $target = Arr::dot($loader->load($locale, $group, '*'));
                foreach ($source as $key => $label) {
                    $translated = $target[$key] ?? null;
                    if (! is_string($label) || ! is_string($translated) || $label === '' || $label === $translated) {
                        continue;
                    }
                    $pairs[$label] ??= $translated;
                }
            }

            return response()->json(['locale' => $locale, 'labels' => (object) $pairs]);
        }

        $return = $request->query('return');
        if (!$return) {
            $return = url()->previous() ?: route('root');
        }

        return redirect()->to($return);
    }
}
