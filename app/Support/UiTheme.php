<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\SystemSetting;
use Throwable;

/**
 * Interface styles (visual themes) and how they resolve for a request.
 *
 * A style sets `data-ui-style` on <html>; resources/css/themes.css maps it to
 * colour, radius, shadow, spacing and focus tokens. Styles are independent of
 * the light/dark mode and of the accent palette (except where a style fixes
 * its own brand colour).
 *
 * The five non-classic styles come from the UI/UX Pro Max style catalogue
 * (styles.csv): each was chosen because it supports light and dark mode, has a
 * low performance cost and low accessibility risk, and suits a public-service
 * product (government / legal / enterprise).
 */
final class UiTheme
{
    public const DEFAULT = 'classic';

    /**
     * @return array<string, array{label: string, description: string, source: string|null, swatches: array<int, string>, radius: string}>
     */
    public static function catalog(): array
    {
        return [
            'classic' => [
                'label' => __('settings.ui_style_classic'),
                'description' => __('settings.ui_style_classic_desc'),
                'source' => null,
                'swatches' => ['#f4f7fb', '#ffffff', '#2563eb', '#0f172a'],
                'radius' => '10px',
            ],
            'authority' => [
                'label' => __('settings.ui_style_authority'),
                'description' => __('settings.ui_style_authority_desc'),
                'source' => 'Accessible & Ethical',
                'swatches' => ['#f8fafc', '#ffffff', '#1e3a8a', '#b45309'],
                'radius' => '8px',
            ],
            'swiss' => [
                'label' => __('settings.ui_style_swiss'),
                'description' => __('settings.ui_style_swiss_desc'),
                'source' => 'Minimalism & Swiss Style',
                'swatches' => ['#ffffff', '#f4f4f5', '#111827', '#dc2626'],
                'radius' => '0',
            ],
            'compact' => [
                'label' => __('settings.ui_style_compact'),
                'description' => __('settings.ui_style_compact_desc'),
                'source' => 'Data-Dense Dashboard',
                'swatches' => ['#f5f5f5', '#ffffff', '#2563eb', '#333333'],
                'radius' => '6px',
            ],
            'inclusive' => [
                'label' => __('settings.ui_style_inclusive'),
                'description' => __('settings.ui_style_inclusive_desc'),
                'source' => 'Inclusive Design',
                'swatches' => ['#ffffff', '#ffffff', '#1d4ed8', '#000000'],
                'radius' => '8px',
            ],
            'bento' => [
                'label' => __('settings.ui_style_bento'),
                'description' => __('settings.ui_style_bento_desc'),
                'source' => 'Bento Box Grid',
                'swatches' => ['#f5f5f7', '#ffffff', '#2563eb', '#1d1d1f'],
                'radius' => '20px',
            ],
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::catalog());
    }

    /** Settings columns for the customisable page chrome, keyed by area. */
    public const CHROME = [
        'sidebar' => 'chrome_sidebar_bg',
        'topbar' => 'chrome_topbar_bg',
        'footer' => 'chrome_footer_bg',
    ];

    /** Text colours used on custom chrome backgrounds. */
    private const INK = '#0f172a';

    private const PAPER = '#ffffff';

    /** Minimum contrast (WCAG AA, normal text) between a chrome colour and its text. */
    public const MIN_CONTRAST = 4.5;

    public static function isHexColor(mixed $value): bool
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1;
    }

    /**
     * 'dark' when white text reads best on the colour, 'light' when dark text does.
     */
    public static function tone(string $hex): string
    {
        return self::contrast($hex, self::PAPER) >= self::contrast($hex, self::INK) ? 'dark' : 'light';
    }

    /** Best achievable text contrast on this background (white or dark ink). */
    public static function bestTextContrast(string $hex): float
    {
        return max(self::contrast($hex, self::PAPER), self::contrast($hex, self::INK));
    }

    /** WCAG 2.x contrast ratio between two #rrggbb colours. */
    public static function contrast(string $a, string $b): float
    {
        [$l1, $l2] = [self::luminance($a), self::luminance($b)];

        return (max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05);
    }

    private static function luminance(string $hex): float
    {
        $channels = array_map(
            static fn (string $pair): float => hexdec($pair) / 255,
            str_split(ltrim($hex, '#'), 2)
        );
        $linear = array_map(
            static fn (float $c): float => $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4,
            $channels
        );

        return 0.2126 * $linear[0] + 0.7152 * $linear[1] + 0.0722 * $linear[2];
    }

    /**
     * Resolved settings for the page boot script and the theme picker.
     *
     * `chrome` holds the administrator's custom colours (null = style default)
     * and the tone of each ('dark' = white text, 'light' = dark text).
     *
     * @return array{style: string, enabled: list<string>, userChoice: bool, mode: string, accent: string, chrome: array<string, array{color: string|null, tone: string}>}
     */
    public static function config(?SystemSetting $settings = null): array
    {
        if ($settings === null) {
            try {
                $settings = SystemSetting::cached();
            } catch (Throwable) {
                $settings = null; // settings table unavailable (e.g. during install)
            }
        }

        $keys = self::keys();
        $enabled = array_values(array_intersect($keys, (array) ($settings?->ui_styles_enabled ?? $keys)));
        $style = in_array($settings?->ui_style, $keys, true) ? $settings->ui_style : self::DEFAULT;

        // The default style must always be available to everyone.
        if (! in_array($style, $enabled, true)) {
            $enabled[] = $style;
        }

        return [
            'style' => $style,
            'enabled' => $enabled,
            'userChoice' => (bool) ($settings?->ui_style_user_choice ?? true),
            'mode' => in_array($settings?->default_theme, ['light', 'dark', 'system'], true) ? $settings->default_theme : 'system',
            'accent' => in_array($settings?->accent_palette, ['blue', 'teal', 'violet', 'emerald', 'rose'], true) ? $settings->accent_palette : 'blue',
            'chrome' => self::chrome($settings),
        ];
    }

    /**
     * @return array<string, array{color: string|null, tone: string}>
     */
    private static function chrome(?SystemSetting $settings): array
    {
        $chrome = [];
        foreach (self::CHROME as $area => $column) {
            $color = $settings?->{$column};
            $color = self::isHexColor($color) ? strtolower($color) : null;
            // Without a custom colour the sidebar is dark in every style; the top
            // bar and footer follow the page surface.
            $chrome[$area] = [
                'color' => $color,
                'tone' => $color !== null ? self::tone($color) : ($area === 'sidebar' ? 'dark' : 'light'),
            ];
        }

        return $chrome;
    }
}
