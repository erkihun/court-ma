<?php

declare(strict_types=1);

use App\Models\Applicant;
use App\Models\CaseType;
use App\Models\CourtCase;
use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\Permission;
use App\Models\Respondent;
use App\Models\RespondentResponse;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
 * Regression coverage for authorization defects fixed during the 2026-11 audit.
 */

function accessStaff(array $permissions = [], ?Team $team = null): User
{
    $role = Role::query()->create(['name' => 'access-'.Str::uuid()]);
    $role->permissions()->sync(collect($permissions)->map(
        fn (string $name): int => Permission::query()->firstOrCreate(['name' => $name], ['label' => $name])->id
    ));

    $user = User::factory()->create(['status' => 'active']);
    $user->roles()->attach($role);
    $team?->users()->attach($user);

    return $user;
}

function accessAdminRole(): Role
{
    return Role::query()->firstOrCreate(['name' => 'admin']);
}

function accessAdmin(): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->roles()->attach(accessAdminRole());

    return $user;
}

function accessCase(array $attributes = []): CourtCase
{
    $type = CaseType::query()->firstOrCreate(['name' => 'Access control test type']);

    return CourtCase::query()->create(array_merge([
        'case_number' => 'AC/'.random_int(10000, 99999).'/'.Str::upper(Str::random(4)),
        'title' => 'Access control case',
        'case_type_id' => $type->id,
        'filing_date' => now()->toDateString(),
        'status' => 'pending',
        'review_status' => 'accepted',
    ], $attributes));
}

function accessApplicant(string $label): Applicant
{
    $suffix = Str::lower(Str::random(8));

    return Applicant::create([
        'first_name' => $label,
        'middle_name' => 'T',
        'last_name' => 'User',
        'gender' => 'female',
        'position' => 'Officer',
        'organization_name' => 'Org',
        'phone' => '09'.random_int(10000000, 99999999),
        'email' => "{$label}-{$suffix}@example.com",
        'address' => 'Addis Ababa',
        'national_id_number' => (string) random_int(1000000000000000, 9999999999999999),
        'password' => Hash::make('password'),
        'is_active' => true,
        'is_lawyer' => false,
    ]);
}

function staffProfilePayload(User $user, array $overrides = []): array
{
    return array_merge([
        'first_name' => 'Self',
        'last_name' => 'Editor',
        'email' => $user->email,
        'status' => 'active',
    ], $overrides);
}

// ── User management: privilege escalation ───────────────────────────────────

test('staff editing their own profile cannot grant themselves roles or change status', function (): void {
    $adminRole = accessAdminRole();
    $user = accessStaff(['cases.view']);
    $originalRoleIds = $user->roles()->pluck('roles.id')->all();

    $this->actingAs($user)
        ->patch(route('users.update', $user), staffProfilePayload($user, [
            'status' => 'inactive',
            'roles' => [$adminRole->id],
        ]))
        ->assertRedirect();

    $user->refresh();
    expect($user->status)->toBe('active')
        ->and($user->roles()->pluck('roles.id')->all())->toEqual($originalRoleIds)
        ->and($user->name)->toBe('Self Editor');
});

test('a non-admin user manager cannot grant the admin role', function (): void {
    $adminRole = accessAdminRole();
    $manager = accessStaff(['users.manage']);
    $target = accessStaff(['cases.view']);

    $this->actingAs($manager)
        ->patch(route('users.update', $target), staffProfilePayload($target, ['roles' => [$adminRole->id]]))
        ->assertSessionHasErrors('roles');

    expect($target->roles()->whereKey($adminRole->id)->exists())->toBeFalse();

    $this->actingAs($manager)
        ->post(route('users.store'), [
            'first_name' => 'New',
            'last_name' => 'Admin',
            'email' => 'new-admin-'.Str::lower(Str::random(6)).'@example.com',
            'status' => 'active',
            'roles' => [$adminRole->id],
        ])
        ->assertSessionHasErrors('roles');

    expect(User::query()->where('name', 'New Admin')->exists())->toBeFalse();
});

test('a non-admin user manager can still assign ordinary roles', function (): void {
    $manager = accessStaff(['users.manage']);
    $target = accessStaff(['cases.view']);
    $clerkRole = Role::query()->create(['name' => 'clerk-'.Str::uuid()]);

    $this->actingAs($manager)
        ->patch(route('users.update', $target), staffProfilePayload($target, [
            'status' => 'inactive',
            'roles' => [$clerkRole->id],
        ]))
        ->assertSessionHasNoErrors();

    $target->refresh();
    expect($target->status)->toBe('inactive')
        ->and($target->roles()->pluck('roles.id')->all())->toEqual([$clerkRole->id]);
});

test('an admin can grant the admin role', function (): void {
    $adminRole = accessAdminRole();
    $target = accessStaff(['cases.view']);

    $this->actingAs(accessAdmin())
        ->patch(route('users.update', $target), staffProfilePayload($target, ['roles' => [$adminRole->id]]))
        ->assertSessionHasNoErrors();

    expect($target->roles()->whereKey($adminRole->id)->exists())->toBeTrue();
});

// ── Landing page CMS ────────────────────────────────────────────────────────

test('landing page manager requires settings.manage', function (): void {
    $clerk = accessStaff(['cases.view']);

    $this->actingAs($clerk)
        ->get(route('admin.landing.index'))
        ->assertRedirect(route('dashboard'));

    $this->actingAs($clerk)
        ->put(route('admin.landing.footer.update'), ['about' => 'defaced'])
        ->assertRedirect(route('dashboard'));

    $this->actingAs(accessStaff(['settings.manage']))
        ->get(route('admin.landing.index'))
        ->assertOk();
});

// ── Team / assignment scope on single-case actions ──────────────────────────

test('an unassigned team member cannot change a case status or download its files by id', function (): void {
    $team = Team::query()->create(['name' => 'Bench A']);
    $member = accessStaff(['cases.view', 'cases.edit'], $team);
    $colleague = accessStaff(['cases.view'], $team);
    $case = accessCase([
        'assigned_team_id' => $team->id,
        'assigned_user_id' => $colleague->id,
        'assigned_member_user_id' => $colleague->id,
    ]);

    Storage::fake('private');
    Storage::disk('private')->put('case_files/secret.pdf', 'secret');
    $fileId = DB::table('case_files')->insertGetId([
        'case_id' => $case->id,
        'label' => 'Secret',
        'path' => 'case_files/secret.pdf',
        'mime' => 'application/pdf',
        'size' => 6,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($member)->get(route('cases.show', $case->id))->assertForbidden();

    $this->actingAs($member)
        ->patch(route('cases.status.update', $case->id), ['status' => 'active'])
        ->assertForbidden();
    expect($case->fresh()->status)->toBe('pending');

    $this->actingAs($member)
        ->get(route('cases.files.download', [$case->id, $fileId]))
        ->assertForbidden();

    $this->actingAs($member)
        ->get(route('recordes.show', $case->id))
        ->assertForbidden();

    // The assigned colleague keeps access.
    $this->actingAs($colleague)
        ->get(route('cases.files.download', [$case->id, $fileId]))
        ->assertOk();
});

// ── Respondent responses lock once accepted ─────────────────────────────────

test('an accepted respondent response can no longer be edited or deleted', function (): void {
    $applicant = accessApplicant('respondent');
    $respondent = Respondent::create([
        'first_name' => 'Resp',
        'middle_name' => 'T',
        'last_name' => 'User',
        'gender' => 'male',
        'position' => 'Officer',
        'organization_name' => 'Org',
        'address' => 'Addis Ababa',
        'phone' => '09'.random_int(10000000, 99999999),
        'email' => $applicant->email,
        'password' => Hash::make('password'),
    ]);
    $response = RespondentResponse::create([
        'respondent_id' => $respondent->id,
        'case_number' => null,
        'title' => 'Accepted response',
        'description' => 'Body',
        'pdf_path' => 'respondent/responses/accepted.pdf',
        'review_status' => 'accepted',
    ]);

    $this->actingAs($applicant, 'applicant')
        ->get(route('respondent.responses.edit', $response))
        ->assertForbidden();

    $this->actingAs($applicant, 'applicant')
        ->patch(route('respondent.responses.update', $response), ['title' => 'Swapped'])
        ->assertForbidden();

    $this->actingAs($applicant, 'applicant')
        ->delete(route('respondent.responses.destroy', $response))
        ->assertForbidden();

    expect($response->fresh())->not->toBeNull()
        ->and($response->fresh()->title)->toBe('Accepted response');
});

// ── Letter previews show parties approved letters only ──────────────────────

test('a case applicant can preview approved letters but not drafts', function (): void {
    $owner = accessApplicant('owner');
    $case = accessCase(['applicant_id' => $owner->id]);
    $template = LetterTemplate::create(['title' => 'Notice', 'body' => '<p>Notice</p>']);
    $author = accessAdmin();

    $makeLetter = fn (string $status): Letter => Letter::create([
        'letter_template_id' => $template->id,
        'user_id' => $author->id,
        'recipient_name' => 'Recipient',
        'subject' => 'Notice',
        'reference_number' => 'REF-'.Str::upper(Str::random(8)),
        'case_number' => $case->case_number,
        'body' => '<p>Letter body</p>',
        'send_to_applicant' => true,
        'approval_status' => $status,
    ]);

    $this->actingAs($owner, 'applicant')
        ->get(route('letters.case-preview', $makeLetter('pending')))
        ->assertForbidden();

    $this->actingAs($owner, 'applicant')
        ->get(route('letters.case-preview', $makeLetter('approved')))
        ->assertOk();
});

// ── Respondent case-code brute force ────────────────────────────────────────

test('repeated wrong case codes are locked out', function (): void {
    $owner = accessApplicant('owner');
    $case = accessCase(['applicant_id' => $owner->id, 'code' => '48213']);
    $prober = accessApplicant('prober');

    for ($i = 0; $i < 10; $i++) {
        $this->actingAs($prober, 'applicant')
            ->get(route('respondent.cases.show', ['caseNumber' => $case->case_number, 'case_code' => sprintf('%05d', $i)]))
            ->assertNotFound();
    }

    // Even the correct code is refused once the account is locked out.
    $this->actingAs($prober, 'applicant')
        ->get(route('respondent.cases.show', ['caseNumber' => $case->case_number, 'case_code' => '48213']))
        ->assertStatus(429);
});
