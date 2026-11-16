<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\UiTheme;
use Illuminate\Support\Facades\Cache;

function uiThemeAdmin(): User
{
    $admin = User::factory()->create(['status' => 'active']);
    $admin->roles()->attach(Role::query()->firstOrCreate(['name' => 'admin']));

    return $admin;
}

test('the catalogue offers classic plus five interface styles', function (): void {
    expect(UiTheme::keys())->toBe(['classic', 'authority', 'swiss', 'compact', 'inclusive', 'bento']);
});

test('without settings every style is available and classic is the default', function (): void {
    $config = UiTheme::config(new SystemSetting);

    expect($config['style'])->toBe('classic')
        ->and($config['enabled'])->toBe(UiTheme::keys())
        ->and($config['userChoice'])->toBeTrue()
        ->and($config['mode'])->toBe('system')
        ->and($config['accent'])->toBe('blue');
});

test('an administrator can set the default style and the styles users may pick', function (): void {
    $this->actingAs(uiThemeAdmin())
        ->post(route('settings.system.update'), [
            // The settings form always submits these; the controller stores them as sent.
            'app_name' => 'Court MS',
            'default_locale' => 'en',
            'timezone' => 'Africa/Addis_Ababa',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'session_lifetime' => 120,
            'session_warning_minutes' => 5,
            'password_min_length' => 8,
            'audit_retention_days' => 365,
            'login_max_attempts' => 5,
            'lockout_minutes' => 15,
            'api_rate_limit' => 60,
            'mail_mailer' => 'smtp',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'ui_style' => 'authority',
            'ui_styles_enabled' => ['swiss', 'inclusive'],
            'default_theme' => 'dark',
            'accent_palette' => 'teal',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    Cache::forget('system_settings');
    $config = UiTheme::config();

    // The default style is always kept available, even when not ticked.
    expect($config['style'])->toBe('authority')
        ->and($config['enabled'])->toEqualCanonicalizing(['authority', 'swiss', 'inclusive'])
        ->and($config['userChoice'])->toBeFalse()
        ->and($config['mode'])->toBe('dark')
        ->and($config['accent'])->toBe('teal');
});

test('unknown styles are rejected', function (): void {
    $this->actingAs(uiThemeAdmin())
        ->post(route('settings.system.update'), [
            'ui_style' => 'neon',
            'ui_styles_enabled' => ['classic', 'neon'],
        ])
        ->assertSessionHasErrors(['ui_style', 'ui_styles_enabled.1']);
});

test('pages apply the configured style before first paint', function (): void {
    SystemSetting::query()->create([
        'app_name' => 'Court MS',
        'ui_style' => 'bento',
        'ui_styles_enabled' => ['bento', 'swiss'],
        'ui_style_user_choice' => true,
    ]);
    Cache::forget('system_settings');

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('window.__uiTheme', false)
        ->assertSee('"style":"bento"', false)
        ->assertSee('"enabled":["swiss","bento"]', false);
});

/** The fields the System Settings form always submits. */
function uiThemePayload(array $overrides = []): array
{
    return array_merge([
        'app_name' => 'Court MS',
        'default_locale' => 'en',
        'timezone' => 'Africa/Addis_Ababa',
        'date_format' => 'Y-m-d',
        'time_format' => 'H:i',
        'session_lifetime' => 120,
        'session_warning_minutes' => 5,
        'password_min_length' => 8,
        'audit_retention_days' => 365,
        'login_max_attempts' => 5,
        'lockout_minutes' => 15,
        'api_rate_limit' => 60,
        'mail_mailer' => 'smtp',
        'mail_port' => 587,
        'mail_encryption' => 'tls',
        'ui_style' => 'classic',
    ], $overrides);
}

test('chrome colours pick a readable text tone', function (): void {
    expect(UiTheme::tone('#0c1527'))->toBe('dark')
        ->and(UiTheme::tone('#f8fafc'))->toBe('light')
        ->and(UiTheme::bestTextContrast('#0c1527'))->toBeGreaterThan(UiTheme::MIN_CONTRAST)
        // Mid grey reads poorly with both white and dark text.
        ->and(UiTheme::bestTextContrast('#777777'))->toBeLessThan(UiTheme::MIN_CONTRAST);
});

test('without custom colours the sidebar keeps its dark default', function (): void {
    $chrome = UiTheme::config(new SystemSetting)['chrome'];

    expect($chrome['sidebar'])->toBe(['color' => null, 'tone' => 'dark'])
        ->and($chrome['topbar']['color'])->toBeNull()
        ->and($chrome['footer']['color'])->toBeNull();
});

test('an administrator can set and clear sidebar, top bar and footer colours', function (): void {
    $admin = uiThemeAdmin();

    $this->actingAs($admin)
        ->post(route('settings.system.update'), uiThemePayload([
            'chrome_sidebar_bg' => '#F1F5F9',
            'chrome_topbar_bg' => '#0b1f4d',
            'chrome_footer_bg' => '',
        ]))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    Cache::forget('system_settings');
    $chrome = UiTheme::config()['chrome'];

    expect($chrome['sidebar'])->toBe(['color' => '#f1f5f9', 'tone' => 'light'])
        ->and($chrome['topbar'])->toBe(['color' => '#0b1f4d', 'tone' => 'dark'])
        ->and($chrome['footer']['color'])->toBeNull();

    // An empty value returns that part to the style default.
    $this->actingAs($admin)
        ->post(route('settings.system.update'), uiThemePayload(['chrome_sidebar_bg' => '']))
        ->assertSessionHasNoErrors();

    Cache::forget('system_settings');
    expect(UiTheme::config()['chrome']['sidebar']['color'])->toBeNull();
});

test('unreadable or malformed chrome colours are rejected', function (): void {
    $this->actingAs(uiThemeAdmin())
        ->post(route('settings.system.update'), uiThemePayload([
            'chrome_sidebar_bg' => '#777777',
            'chrome_topbar_bg' => 'red',
            'chrome_footer_bg' => '#12345',
        ]))
        ->assertSessionHasErrors(['chrome_sidebar_bg', 'chrome_topbar_bg', 'chrome_footer_bg']);
});

test('pages apply custom chrome colours before first paint', function (): void {
    SystemSetting::query()->create([
        'app_name' => 'Court MS',
        'chrome_sidebar_bg' => '#f1f5f9',
        'chrome_topbar_bg' => '#0b1f4d',
    ]);
    Cache::forget('system_settings');

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('--chrome-sidebar-bg:#f1f5f9;--chrome-topbar-bg:#0b1f4d', false)
        ->assertSee('"sidebar":{"color":"#f1f5f9","tone":"light"}', false)
        ->assertDontSee('--chrome-footer-bg', false);
});

test('the appearance tab shows the chrome colour controls', function (): void {
    SystemSetting::query()->create(['app_name' => 'Court MS', 'chrome_topbar_bg' => '#0b1f4d']);
    Cache::forget('system_settings');

    $this->actingAs(uiThemeAdmin())
        ->get(route('settings.system.edit'))
        ->assertOk()
        ->assertSee('name="chrome_sidebar_bg"', false)
        ->assertSee('name="chrome_topbar_bg"', false)
        ->assertSee('name="chrome_footer_bg"', false)
        ->assertSee("chromeColor('topbar', '#0b1f4d'", false);
});

test('emptied inputs for required columns keep their value instead of failing the save', function (): void {
    $this->actingAs(uiThemeAdmin())
        ->post(route('settings.system.update'), uiThemePayload([
            'sms_provider' => '',
            'mail_mailer' => '',
            'chrome_sidebar_bg' => '#f1f5f9',
        ]))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $settings = SystemSetting::query()->firstOrFail();

    expect($settings->sms_provider)->toBe('infobip')
        ->and($settings->mail_mailer)->toBe('smtp')
        ->and($settings->chrome_sidebar_bg)->toBe('#f1f5f9');
});
