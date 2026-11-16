<?php

declare(strict_types=1);

use App\Models\Applicant;
use Illuminate\Support\Facades\Hash;

function respondentLoginAccount(): Applicant
{
    $applicant = Applicant::query()->create([
        'first_name' => 'Respondent',
        'middle_name' => 'Login',
        'last_name' => 'Test',
        'gender' => 'female',
        'position' => 'Officer',
        'organization_name' => 'Test Org',
        'phone' => '0911000222',
        'email' => 'respondent-login@example.test',
        'address' => 'Addis Ababa',
        'national_id_number' => '1111222233335555',
        'password' => Hash::make('correct-password'),
        'is_active' => true,
    ]);
    $applicant->forceFill(['email_verified_at' => now()])->save();

    return $applicant;
}

test('respondent sign-in does not create an account for an unknown email', function (): void {
    $this->post(route('applicant.login.submit'), [
        'email' => 'nobody@example.test',
        'password' => 'whatever-password',
        'login_as' => 'respondent',
    ])->assertSessionHasErrors('email');

    expect(Applicant::query()->where('email', 'nobody@example.test')->exists())->toBeFalse();
    $this->assertGuest('applicant');
});

test('an existing account can sign in as respondent', function (): void {
    $applicant = respondentLoginAccount();

    $this->post(route('applicant.login.submit'), [
        'email' => $applicant->email,
        'password' => 'correct-password',
        'login_as' => 'respondent',
    ])->assertRedirect(route('respondent.dashboard'));

    $this->assertAuthenticatedAs($applicant, 'applicant');
    expect(session('acting_as_respondent'))->toBeTrue();
});

test('a wrong password in respondent mode is rejected', function (): void {
    $applicant = respondentLoginAccount();

    $this->post(route('applicant.login.submit'), [
        'email' => $applicant->email,
        'password' => 'wrong-password',
        'login_as' => 'respondent',
    ])->assertSessionHasErrors('email');

    $this->assertGuest('applicant');
});

test('respondent registration links open the registration form', function (): void {
    $this->get(route('respondent.register'))->assertRedirect(route('applicant.register'));
});
