<?php

declare(strict_types=1);

use App\Models\CaseType;
use App\Models\CourtCase;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

function caseShowPageUser(array $permissions): User
{
    $role = Role::query()->create(['name' => 'case-show-page-'.Str::uuid()]);
    $role->permissions()->sync(collect($permissions)->map(
        fn (string $name): int => Permission::query()->firstOrCreate(
            ['name' => $name],
            ['label' => Str::headline($name)],
        )->id,
    ));

    $user = User::factory()->create(['status' => 'active']);
    $user->roles()->attach($role);

    return $user;
}

function caseShowPageRecord(string $status = 'active'): CourtCase
{
    return CourtCase::query()->create([
        'case_number' => 'CS-'.Str::upper(Str::random(8)),
        'title' => 'Case page applicant',
        'case_type_id' => CaseType::query()->create(['name' => 'Case page type'])->id,
        'filing_date' => now()->toDateString(),
        'status' => $status,
    ]);
}

test('the case page shows the case header, facts and section navigation', function (): void {
    $case = caseShowPageRecord();

    $this->actingAs(caseShowPageUser(['cases.view', 'cases.edit', 'hearing.view']))
        ->get(route('cases.show', $case->id))
        ->assertOk()
        ->assertSee('<span id="case-no">'.$case->case_number.'</span>', false)
        ->assertSee('class="cs-nav"', false)
        ->assertSee("openSection('hearings')", false)
        ->assertSeeText('Case page applicant')
        ->assertSeeText(__('cases.summary.unassigned'))
        // The status form keeps its route and field names.
        ->assertSee(route('cases.status.update', $case->id), false)
        ->assertSee('name="status"', false)
        // The old duplicate quick-access bar is gone.
        ->assertDontSee('cs-quick-btn', false);
});

test('a case without hearings says so instead of showing an empty list', function (): void {
    $case = caseShowPageRecord();

    $this->actingAs(caseShowPageUser(['cases.view']))
        ->get(route('cases.show', $case->id))
        ->assertOk()
        ->assertSeeText(__('cases.no_hearings_scheduled'));
});

test('sections the user may not use are left out of the navigation', function (): void {
    $case = caseShowPageRecord();

    $this->actingAs(caseShowPageUser(['cases.view']))
        ->get(route('cases.show', $case->id))
        ->assertOk()
        ->assertDontSee("openSection('inspection-requests')", false)
        ->assertDontSee("openSection('response-of-response')", false);
});

test('the hearing form has visible labels and keeps the fields its scripts use', function (): void {
    $case = caseShowPageRecord();

    $this->actingAs(caseShowPageUser(['cases.view', 'hearing.view', 'hearing.create']))
        ->get(route('cases.show', $case->id))
        ->assertOk()
        ->assertSee('data-hearing-create-form', false)
        ->assertSee('id="hearings-calendar"', false)
        ->assertSee('for="hearing_date_new"', false)
        ->assertSee('for="hearing_type_new"', false)
        ->assertSee('name="hearing_at" id="hearing_at_new"', false)
        ->assertDontSee('Locked (today/past)');
});

test('the section navigation follows the agreed order', function (): void {
    $case = caseShowPageRecord();

    $html = $this->actingAs(caseShowPageUser([
        'cases.view', 'hearing.view', 'file.view', 'cases.response-replies.manage',
        'inspection-requests.manage', 'inspection-findings.manage',
    ]))->get(route('cases.show', $case->id))->assertOk()->getContent();

    $nav = substr($html, strpos($html, 'class="cs-nav"'));
    $nav = substr($nav, 0, strpos($nav, '</nav>'));
    preg_match_all("/openSection\('([a-z-]+)'\)/", $nav, $matches);

    expect($matches[1])->toBe([
        'case-summary', 'case-details', 'letters', 'hearings', 'respondent-responses',
        'response-of-response', 'messages', 'inspection-requests', 'inspection-findings',
        'uploaded-files', 'audits',
    ]);
});
