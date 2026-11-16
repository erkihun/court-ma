<?php

declare(strict_types=1);

use App\Models\SystemSetting;
use App\Providers\MailConfigServiceProvider;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    SystemSetting::query()->create([
        'app_name' => 'Court MS',
        'mail_enabled' => true,
        'mail_host' => 'smtp.settings.test',
        'mail_password' => 'stored-secret',
    ]);
    Cache::forget('system_settings');
    config(['mail.mailers.smtp.host' => 'smtp.env.test']);
});

test('the app boots without an app key so key:generate can run', function (): void {
    config(['app.key' => '']);
    app()->forgetInstance('encrypter');

    (new MailConfigServiceProvider(app()))->boot();

    expect(config('mail.mailers.smtp.host'))->toBe('smtp.env.test');
});

test('stored SMTP settings still apply when the app key is present', function (): void {
    (new MailConfigServiceProvider(app()))->boot();

    expect(config('mail.mailers.smtp.host'))->toBe('smtp.settings.test')
        ->and(config('mail.mailers.smtp.password'))->toBe('stored-secret');
});
