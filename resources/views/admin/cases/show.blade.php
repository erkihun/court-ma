{{-- resources/views/cases/show.blade.php --}}
<x-admin-layout title="{{ __('cases.case_details') }}">
    @section('page_header', __('cases.case_details'))

    @php
    // Safer permission checks (works even if user() is null or helper exists)
    $canEditStatus = function_exists('userHasPermission')
    ? userHasPermission('cases.edit')
    : (auth()->user()?->hasPermission('cases.edit') ?? false);

    $canAssign = function_exists('userHasPermission')
    ? (
    userHasPermission('cases.assign.team')
    || userHasPermission('cases.assign.member')
    || userHasPermission('cases.assign')
    )
    : (
    (auth()->user()?->hasPermission('cases.assign.team') ?? false)
    || (auth()->user()?->hasPermission('cases.assign.member') ?? false)
    || (auth()->user()?->hasPermission('cases.assign') ?? false)
    );

    $currentStatus = $case->status ?? 'pending';
    $reviewStatus = $case->review_status ?? 'accepted';
    $reviewNote = $case->review_note ?? null;

    $canReview = function_exists('userHasPermission')
    ? userHasPermission('cases.review')
    : (auth()->user()?->can('cases.review') ?? false);
    $canManageResponseReplies = function_exists('userHasPermission')
    ? userHasPermission('cases.response-replies.manage')
    : (auth()->user()?->hasPermission('cases.response-replies.manage') ?? false);

    $statuses = [
    'pending' => __('cases.status.pending'),
    'active' => __('cases.status.active'),
    'dismissed' => __('cases.status.dismissed'),
    'closed' => __('cases.status.closed'),
    ];
    $selectedStatus = old('status', $reviewStatus === 'awaiting_review' ? 'pending' : $currentStatus);

    $statusChip = fn (string $s) => match ($s) {
    'pending' => 'bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-500/15 dark:border-amber-400/30',
    'active' => 'bg-blue-100 text-blue-800 border border-blue-300 dark:bg-blue-500/15 dark:border-blue-400/30',
    'adjourned' => 'bg-purple-100 text-purple-800 border border-purple-300 dark:bg-purple-500/15 dark:border-purple-400/30',
    'dismissed' => 'bg-rose-100 text-rose-800 border border-rose-300 dark:bg-rose-500/15 dark:border-rose-400/30',
    'closed' => 'bg-emerald-100 text-emerald-800 border border-emerald-300 dark:bg-emerald-500/15 dark:border-emerald-400/30',
    default => 'bg-gray-100 text-gray-800 border border-gray-300 dark:bg-white/10 dark:border-white/20',
    };

    $reviewChip = fn (string $s) => match ($s) {
    'awaiting_review' => 'bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-500/15 dark:border-amber-400/30',
    'returned' => 'bg-yellow-100 text-yellow-800 border border-yellow-300 dark:bg-yellow-500/15 dark:border-yellow-400/30',
    'rejected' => 'bg-rose-100 text-rose-800 border border-rose-300 dark:bg-rose-500/15 dark:border-rose-400/30',
    default => 'bg-emerald-100 text-emerald-800 border border-emerald-300 dark:bg-emerald-500/15 dark:border-emerald-400/30',
    };
    $reviewLabel = fn (string $s) => match ($s) {
    'awaiting_review' => __('cases.review_status.awaiting_review'),
    'returned' => __('cases.review_status.returned'),
    'rejected' => __('cases.review_status.rejected'),
    default => __('cases.review_status.accepted'),
    };
    $headerPrimaryAction = 'btn btn-primary';
    $headerSecondaryAction = 'btn btn-secondary';
    $headerChip = 'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold capitalize';

    $letterPanelOpen = $errors->has('template_id') || $errors->has('recipient_name') || $errors->has('body');
    $defaultSection = $letterPanelOpen ? 'letters-compose' : 'case-summary';
    $canWriteLetter = function_exists('userHasPermission')
    ? userHasPermission('letters.create')
    : (auth()->user()?->hasPermission('letters.create') ?? false);
    $canManageBench = function_exists('userHasPermission')
    ? userHasPermission('bench-notes.manage')
    : (auth()->user()?->hasPermission('bench-notes.manage') ?? false);
    $canViewHearings = function_exists('userHasPermission')
    ? userHasPermission('hearing.view')
    : (auth()->user()?->hasPermission('hearing.view') ?? true);
    $canCreateHearings = function_exists('userHasPermission')
    ? userHasPermission('hearing.create')
    : (auth()->user()?->hasPermission('hearing.create') ?? false);
    $canUpdateHearings = function_exists('userHasPermission')
    ? userHasPermission('hearing.update')
    : (auth()->user()?->hasPermission('hearing.update') ?? false);
    $canDeleteHearings = function_exists('userHasPermission')
    ? userHasPermission('hearing.delete')
    : (auth()->user()?->hasPermission('hearing.delete') ?? false);
    $canViewFiles = function_exists('userHasPermission')
    ? userHasPermission('file.view')
    : (auth()->user()?->hasPermission('file.view') ?? true);
    $canCreateFiles = function_exists('userHasPermission')
    ? userHasPermission('file.create')
    : (auth()->user()?->hasPermission('file.create') ?? false);
    $canUpdateFiles = function_exists('userHasPermission')
    ? userHasPermission('file.update')
    : (auth()->user()?->hasPermission('file.update') ?? false);
    $canDeleteFiles = function_exists('userHasPermission')
    ? userHasPermission('file.delete')
    : (auth()->user()?->hasPermission('file.delete') ?? false);
    $canManageInspectionRequests = function_exists('userHasPermission')
    ? userHasPermission('inspection-requests.manage')
    : (auth()->user()?->hasPermission('inspection-requests.manage') ?? false);
    $canManageInspectionFindings = function_exists('userHasPermission')
    ? userHasPermission('inspection-findings.manage')
    : (auth()->user()?->hasPermission('inspection-findings.manage') ?? false);
    $canCreateMessage = function_exists('userHasPermission')
    ? userHasPermission('message.create')
    : (auth()->user()?->hasPermission('message.create') ?? false);
    $canViewLetters = function_exists('userHasPermission')
    ? userHasPermission('letters.view')
    : (auth()->user()?->hasPermission('letters.view') ?? false);
    $selectedInlineTemplate = null;
    if(old('template_id')) {
    $selectedInlineTemplate = $letterTemplates->firstWhere('id', old('template_id'));
    }

    $letterFieldsDisabled = false;
    $inlineTemplatesData = $letterTemplates->mapWithKeys(function($tpl) {
    $placeholders = $tpl->placeholders ?? [];
    if (is_string($placeholders)) {
    $decoded = json_decode($placeholders, true);
    $placeholders = $decoded ?: [];
    }
    return [
    $tpl->id => [
    'id' => $tpl->id,
    'title' => $tpl->title,
    'body' => $tpl->body,
    'subject' => $tpl->title,
    'placeholders' => $placeholders,
    'category' => $tpl->category,
    ],
    ];
    });

    // Lock actions if case is closed AND has an active decision
    $hasActiveDecision = false;
    try {
    $hasActiveDecision = \App\Models\Decision::where('court_case_id', $case->id)->where('status', 'published')->exists();
    } catch (\Throwable $e) {
    $hasActiveDecision = false;
    }
    $caseLocked = (($case->status ?? '') === 'closed') && $hasActiveDecision;

    $hearingDateKeys = ($hearings ?? collect())
    ->map(function ($h) {
    try {
    return \Illuminate\Support\Carbon::parse($h->hearing_at)->format('Y-m-d');
    } catch (\Throwable $e) {
    return $h->hearing_at ? substr((string) $h->hearing_at, 0, 10) : null;
    }
    })
    ->filter()
    ->unique()
    ->values()
    ->toArray();

    $applicantDisplayName = trim((string) ($case->title ?? ''));

    $lawyerName = collect([
    $case->applicant_first_name ?? null,
    $case->applicant_middle_name ?? null,
    $case->applicant_last_name ?? null,
    ])->map(fn($part) => trim((string) $part))
    ->filter()
    ->implode(' ');
    if ($lawyerName === '') {
    $lawyerName = trim((string) ($case->applicant_name ?? $case->applicant_full_name ?? ''));
    }
    $submittedByLawyer = !empty($case->applicant_is_lawyer) && $lawyerName !== '';

    // Section navigation: one list drives the desktop side nav and the mobile strip.
    $sectionCount = fn ($items) => $items instanceof \Countable ? count($items) : null;
    $canSeeFiles = $canViewFiles || $canCreateFiles || $canUpdateFiles || $canDeleteFiles;
    $canSeeHearings = $canViewHearings || $canCreateHearings || $canUpdateHearings || $canDeleteHearings;
    $sectionNav = collect([
        ['id' => 'case-summary', 'group' => 'main', 'label' => __('cases.navigation.case_summary'), 'icon' => 'M4 6h16M4 12h16M4 18h16', 'show' => true],
        ['id' => 'case-details', 'group' => 'main', 'label' => __('cases.navigation.case_details'), 'icon' => 'M9 12h6m-6 4h6M7 8h10M5 5h14v14H5z', 'show' => true],
        ['id' => 'letters', 'group' => 'main', 'label' => __('cases.navigation.letters'), 'icon' => 'M3 7l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z', 'show' => true, 'count' => $sectionCount($letters ?? null)],
        ['id' => 'hearings', 'group' => 'main', 'label' => __('cases.navigation.hearings'), 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'show' => $canSeeHearings, 'count' => $sectionCount($hearings ?? null)],
        ['id' => 'respondent-responses', 'group' => 'main', 'label' => __('cases.navigation.respondent_responses'), 'icon' => 'M5 7h14M5 11h10M5 15h14', 'show' => true, 'count' => $sectionCount($respondentResponses ?? null)],
        ['id' => 'response-of-response', 'group' => 'main', 'label' => __('cases.navigation.response_of_response'), 'icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6', 'show' => $canManageResponseReplies, 'count' => $sectionCount($applicantResponseReplies ?? null)],
        ['id' => 'messages', 'group' => 'main', 'label' => __('cases.navigation.messages'), 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', 'show' => true, 'count' => $sectionCount($messages ?? null)],
        ['id' => 'inspection-requests', 'group' => 'more', 'label' => __('case_inspections.requests.index_title'), 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'show' => $canManageInspectionRequests, 'count' => $sectionCount($inspectionRequests ?? null)],
        ['id' => 'inspection-findings', 'group' => 'more', 'label' => __('case_inspections.findings.index_title'), 'icon' => 'M21 21l-5.2-5.2M10 17a7 7 0 100-14 7 7 0 000 14z', 'show' => $canManageInspectionFindings, 'count' => $sectionCount($inspectionFindings ?? null)],
        ['id' => 'uploaded-files', 'group' => 'more', 'label' => __('cases.navigation.uploaded_files'), 'icon' => 'M15.2 7l-6.6 6.6a2 2 0 102.8 2.8l6.4-6.6a4 4 0 00-5.6-5.6l-6.5 6.5a6 6 0 108.5 8.5L20.5 13', 'show' => $canSeeFiles, 'count' => $sectionCount($files ?? null)],
        ['id' => 'audits', 'group' => 'more', 'label' => __('cases.navigation.case_audits'), 'icon' => 'M9 17v-2m0 0V9m0 6h6m-6-4h6m2 8H7a2 2 0 01-2-2V7a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2z', 'show' => true],
    ])->where('show', true)->values();
    @endphp
    @push('styles')
    <style>
    /* ── Case show page ─────────────────────────────────────────── */

    [x-cloak] { display: none !important; }

    /* Section cards */
    .main-content-section {
        background: var(--surface-strong);
        border: 1px solid var(--border);
        border-radius: 1rem;
        animation: csFadeUp .25s ease-out both;
    }
    @keyframes csFadeUp {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Section header divider */
    .cs-section-header {
        display: flex;
        align-items: center;
        gap: .625rem;
        padding-bottom: .875rem;
        margin-bottom: 1.25rem;
        border-bottom: 1px solid var(--border);
    }
    .cs-section-header h3 {
        font-size: .9375rem;
        font-weight: 700;
        color: var(--text);
        margin: 0;
    }
    .cs-section-icon {
        display: grid;
        place-items: center;
        width: 1.875rem;
        height: 1.875rem;
        border-radius: .5rem;
        background: rgb(var(--ac) / .1);
        color: rgb(var(--ac));
        flex-shrink: 0;
    }
    .cs-count-badge {
        margin-left: auto;
        font-size: .6875rem;
        font-weight: 600;
        color: var(--text-subtle);
        background: var(--surface-soft);
        border: 1px solid var(--border);
        border-radius: 999px;
        padding: .1875rem .625rem;
    }

    /* Meta label + value pairs */
    .cs-meta-label {
        font-size: .6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--text-subtle);
        margin-bottom: .25rem;
    }
    .cs-meta-value {
        font-size: .875rem;
        font-weight: 500;
        color: var(--text);
    }

    /* CMS rich-text output */
    .cms-output {
        background: var(--surface-soft);
        border: 1px solid var(--border);
        border-radius: .75rem;
        padding: 1rem 1.125rem;
        color: var(--text);
        font-size: .9rem;
        line-height: 1.7;
    }
    .cms-output p  { margin: 0 0 .6rem; text-align: justify; }
    .cms-output ul { list-style: disc;    margin: .5rem 0 .75rem 1.25rem; padding-left: 1rem; }
    .cms-output ol { list-style: decimal; margin: .5rem 0 .75rem 1.25rem; padding-left: 1rem; }
    .cms-output li { margin: .15rem 0; }
    .cms-output blockquote { border-left: 3px solid rgb(var(--ac)/.4); padding-left: 1rem; color: var(--text-muted); margin: .75rem 0; }
    .cms-output h1,.cms-output h2,.cms-output h3,.cms-output h4,.cms-output h5,.cms-output h6 { font-weight: 700; margin: .75rem 0 .4rem; color: var(--text); }
    .cms-output a  { text-decoration: underline; color: rgb(var(--ac)); }
    .cms-output table { width: 100%; border-collapse: collapse; margin: .75rem 0; }
    .cms-output th,.cms-output td { border: 1px solid var(--border); padding: .35rem .55rem; }
    .case-details-copy { font-size: 16px; line-height: 1.7; }

    /* Header */
    .cs-hero {
        background: var(--surface-strong);
        border: 1px solid var(--border);
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 1px 2px rgb(15 23 42 / .04);
    }
    @media (min-width: 640px) { .cs-hero { padding: 1.5rem; } }
    .cs-back {
        display: inline-flex;
        align-items: center;
        gap: .375rem;
        min-height: 2rem;
        font-size: .8125rem;
        font-weight: 500;
        color: var(--text-muted);
        transition: color .15s;
    }
    .cs-back:hover { color: var(--text); }
    .cs-case-no {
        margin: 0;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: clamp(1.25rem, 1rem + 1vw, 1.625rem);
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: -.01em;
        white-space: nowrap;
        color: var(--text);
    }
    .cs-icon-btn {
        display: inline-grid;
        place-items: center;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: .625rem;
        border: 1px solid var(--border);
        background: var(--surface-strong);
        color: var(--text-muted);
        transition: background .15s, color .15s, border-color .15s;
    }
    .cs-icon-btn:hover { background: var(--surface-soft); color: var(--text); border-color: var(--border-strong); }
    .cs-facts {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(8.5rem, 1fr));
        gap: .875rem 1.5rem;
        margin: 1.125rem 0 0;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }
    .cs-facts dt, .cs-fact-grid dt {
        font-size: .6875rem;
        font-weight: 600;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--text-subtle);
    }
    .cs-facts dd, .cs-fact-grid dd {
        margin: .25rem 0 0;
        font-size: .875rem;
        font-weight: 500;
        color: var(--text);
        overflow-wrap: anywhere;
    }
    .cs-facts dd.cs-fact-warn { color: #b45309; }
    .dark .cs-facts dd.cs-fact-warn { color: #fcd34d; }
    .cs-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .5rem;
    }
    @media (min-width: 1280px) { .cs-actions { justify-content: flex-end; max-width: 36rem; } }
    .cs-menu-item {
        display: flex;
        width: 100%;
        align-items: center;
        gap: .625rem;
        padding: .5rem .875rem;
        font-size: .875rem;
        font-weight: 500;
        text-align: left;
        transition: background .15s;
    }
    .cs-menu-item:hover, .cs-menu-item:focus-visible { background: var(--surface-soft); }

    /* Buttons: one accent action per area, everything else neutral */
    .cs-btn-primary,
    .cs-btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        min-height: 2.5rem;
        padding: .5rem 1rem;
        border-radius: .625rem;
        font-size: .8125rem;
        font-weight: 600;
        white-space: nowrap;
        transition: background .15s, color .15s, border-color .15s, filter .15s, transform .1s;
    }
    .cs-btn-primary {
        color: #fff;
        background: rgb(var(--ac));
        border: 1px solid rgb(var(--ac));
        box-shadow: 0 1px 2px rgb(15 23 42 / .12);
    }
    .cs-btn-primary:hover { filter: brightness(1.08); }
    .cs-btn-secondary {
        color: var(--text);
        background: var(--surface-strong);
        border: 1px solid var(--border-strong);
    }
    .cs-btn-secondary:hover { background: var(--surface-soft); border-color: rgb(var(--ac) / .45); }
    .cs-btn-primary:active, .cs-btn-secondary:active { transform: scale(.98); }

    /* Panels, notices and form fields */
    .cs-panel {
        background: var(--surface-strong);
        border: 1px solid var(--border);
        border-radius: 1rem;
        padding: 1.25rem;
    }
    .cs-panel-title {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin: 0 0 1rem;
        font-size: .9375rem;
        font-weight: 700;
        color: var(--text);
    }
    .cs-panel-title svg { color: rgb(var(--ac)); }
    .cs-label {
        display: block;
        margin-bottom: .375rem;
        font-size: .8125rem;
        font-weight: 600;
        color: var(--text-muted);
    }
    .cs-field {
        width: 100%;
        min-height: 2.5rem;
        padding: .5rem .75rem;
        border-radius: .625rem;
        border: 1px solid var(--border-strong);
        background: var(--surface-strong);
        color: var(--text);
        font-size: .875rem;
        transition: border-color .15s, box-shadow .15s;
    }
    .cs-field:focus { outline: none; border-color: rgb(var(--ac)); box-shadow: 0 0 0 3px rgb(var(--ac) / .18); }
    .cs-error { margin-top: .375rem; font-size: .8125rem; color: #b91c1c; }
    .dark .cs-error { color: #fca5a5; }
    .cs-notice {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .625rem;
        padding: .75rem 1rem;
        border-radius: .75rem;
        border: 1px solid;
        font-size: .875rem;
    }
    .cs-notice-warn { background: #fffbeb; border-color: #fde68a; color: #92400e; }
    .cs-notice-ok { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
    .dark .cs-notice-warn { background: rgb(245 158 11 / .12); border-color: rgb(245 158 11 / .3); color: #fcd34d; }
    .dark .cs-notice-ok { background: rgb(16 185 129 / .12); border-color: rgb(16 185 129 / .3); color: #6ee7b7; }

    /* Section navigation */
    .cs-nav {
        display: flex;
        gap: .25rem;
        padding: .375rem;
        overflow-x: auto;
        scrollbar-width: thin;
        background: var(--surface-strong);
        border: 1px solid var(--border);
        border-radius: .875rem;
    }
    .cs-nav-group { display: none; }
    .cs-nav-btn {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        gap: .625rem;
        min-height: 2.5rem;
        padding: .5rem .75rem;
        border-radius: .625rem;
        font-size: .8125rem;
        font-weight: 500;
        text-align: left;
        white-space: nowrap;
        color: var(--text-muted);
        transition: background .15s, color .15s;
    }
    .cs-nav-btn svg { color: var(--text-subtle); transition: color .15s; }
    .cs-nav-btn:hover { background: var(--surface-soft); color: var(--text); }
    .cs-nav-btn[aria-current='true'] { background: rgb(var(--ac) / .1); color: rgb(var(--ac)); font-weight: 600; }
    .cs-nav-btn[aria-current='true'] svg { color: currentColor; }
    .dark .cs-nav-btn[aria-current='true'] { color: color-mix(in srgb, rgb(var(--ac)) 40%, #ffffff); background: rgb(var(--ac) / .2); }
    .cs-nav-count {
        margin-left: auto;
        min-width: 1.375rem;
        padding: .0625rem .4375rem;
        border-radius: 999px;
        background: var(--surface-soft);
        color: var(--text-subtle);
        font-size: .6875rem;
        font-weight: 600;
        text-align: center;
    }
    @media (min-width: 1024px) {
        .cs-nav {
            position: sticky;
            top: 5rem;
            flex-direction: column;
            gap: .125rem;
            padding: .5rem;
            overflow: visible;
        }
        .cs-nav-group {
            display: block;
            margin: .5rem .75rem .25rem;
            font-size: .6875rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--text-subtle);
        }
        .cs-nav-group:first-child { margin-top: .25rem; }
        .cs-nav-btn { flex-shrink: 1; width: 100%; }
    }

    /* Summary content */
    .cs-party {
        padding: 1rem;
        border-radius: .875rem;
        border: 1px solid var(--border);
        background: var(--surface-soft);
    }
    .cs-party-label {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: .625rem;
        font-size: .6875rem;
        font-weight: 600;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--text-subtle);
    }
    .cs-party-icon {
        display: inline-grid;
        place-items: center;
        flex-shrink: 0;
        width: 1.875rem;
        height: 1.875rem;
        border-radius: .5rem;
    }
    .cs-party-name { margin: 0; font-size: .9375rem; font-weight: 600; color: var(--text); }
    .cs-party-meta { margin: .125rem 0 0; font-size: .8125rem; color: var(--text-muted); white-space: pre-line; }
    .cs-fact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(8.5rem, 1fr));
        gap: 1rem 1.5rem;
        margin: 0;
        padding: 1rem 0;
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
    }
    .cs-subheading {
        margin: 0 0 .625rem;
        font-size: .6875rem;
        font-weight: 600;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--text-subtle);
    }
    .cs-empty {
        margin: 0;
        padding: .875rem 1rem;
        border: 1px dashed var(--border-strong);
        border-radius: .75rem;
        font-size: .875rem;
        color: var(--text-muted);
        text-align: center;
    }

    /* Inner sections: shared building blocks */
    .main-content-section > .cs-section-header,
    .main-content-section .cs-section-header { margin-bottom: 0; }
    .cs-section-header h3 { flex: 1 1 auto; min-width: 0; }
    .cs-table-wrap {
        overflow-x: auto;
        border: 1px solid var(--border);
        border-radius: .75rem;
        background: var(--surface-strong);
    }
    .cs-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: .875rem; }
    .cs-table thead th {
        padding: .625rem .875rem;
        background: var(--surface-soft);
        border-bottom: 1px solid var(--border);
        color: var(--text-subtle);
        font-size: .6875rem;
        font-weight: 600;
        letter-spacing: .05em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .cs-table tbody td {
        padding: .75rem .875rem;
        border-bottom: 1px solid var(--border);
        color: var(--text);
        vertical-align: top;
    }
    .cs-table tbody tr:last-child td { border-bottom: 0; }
    .cs-table tbody tr:hover { background: var(--surface-soft); }
    .cs-table pre {
        background: var(--surface-soft) !important;
        border-color: var(--border) !important;
        color: var(--text-muted);
    }
    .cs-field-readonly { background: var(--surface-soft); color: var(--text-muted); }
    textarea.cs-field { min-height: 4.5rem; line-height: 1.5; }
    .cs-check { width: 1rem; height: 1rem; border-radius: .25rem; accent-color: rgb(var(--ac)); }
    .cs-file {
        width: 100%;
        font-size: .875rem;
        color: var(--text-muted);
    }
    .cs-file::file-selector-button {
        margin-right: .75rem;
        padding: .5rem 1rem;
        border: 1px solid var(--border-strong);
        border-radius: .625rem;
        background: var(--surface-strong);
        color: var(--text);
        font-weight: 600;
        cursor: pointer;
    }
    .cs-file::file-selector-button:hover { background: var(--surface-soft); }
    .cs-btn-sm { min-height: 2rem; padding: .3125rem .75rem; font-size: .75rem; border-radius: .5rem; }
    .cs-btn-danger,
    .cs-btn-success,
    .cs-btn-warn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .375rem;
        min-height: 2.5rem;
        padding: .5rem 1rem;
        border-radius: .625rem;
        border: 1px solid;
        font-size: .8125rem;
        font-weight: 600;
        white-space: nowrap;
        transition: background .15s, color .15s, border-color .15s;
    }
    .cs-btn-danger { color: #b91c1c; background: var(--surface-strong); border-color: #fca5a5; }
    .cs-btn-danger:hover { background: #fef2f2; border-color: #f87171; }
    .cs-btn-success { color: #fff; background: #047857; border-color: #047857; }
    .cs-btn-success:hover { background: #065f46; }
    .cs-btn-warn { color: #92400e; background: var(--surface-strong); border-color: #fcd34d; }
    .cs-btn-warn:hover { background: #fffbeb; }
    .dark .cs-btn-danger { color: #fca5a5; border-color: rgb(248 113 113 / .45); }
    .dark .cs-btn-danger:hover { background: rgb(239 68 68 / .12); }
    .dark .cs-btn-warn { color: #fcd34d; border-color: rgb(252 211 77 / .4); }
    .dark .cs-btn-warn:hover { background: rgb(245 158 11 / .12); }
    .cs-btn-danger.cs-btn-sm, .cs-btn-success.cs-btn-sm, .cs-btn-warn.cs-btn-sm {
        min-height: 2rem; padding: .3125rem .75rem; font-size: .75rem; border-radius: .5rem;
    }
    summary.list-none::-webkit-details-marker { display: none; }
    .cs-subpanel {
        padding: 1.125rem;
        border: 1px solid var(--border);
        border-radius: .875rem;
        background: var(--surface-soft);
    }

    /* Other sections: follow the theme accent for header icons */
    .main-content-section > div:first-child h3 > svg.text-blue-600 { color: rgb(var(--ac)); }

    @media (prefers-reduced-motion: reduce) {
        .main-content-section { animation: none; }
        .cs-btn-primary:active, .cs-btn-secondary:active { transform: none; }
    }

    /* Calendar */
    .datepicker-popup { z-index: 9999 !important; }
    .modern-calendar .calendar-header .calendar-title,
    .modern-calendar .selected-date-display { display: none !important; }
    #hearings-calendar .day.has-event { position: relative; }
    #hearings-calendar .day.has-event .event-dot {
        position: absolute; bottom: 5px; left: 50%; transform: translateX(-50%);
        width: 6px; height: 6px; border-radius: 9999px;
        background: rgb(var(--ac)); box-shadow: 0 0 0 2px var(--surface-strong);
    }
    #hearings-calendar .modern-calendar { width: 100%; max-width: 420px; margin: 0 auto; }
    /* Calendar follows the interface accent instead of its own purple gradient */
    #hearings-calendar {
        --primary-color: rgb(var(--ac));
        --secondary-color: rgb(var(--ac));
        --accent-color: rgb(var(--ac));
        --success-color: rgb(var(--ac) / .75);
        --border-radius: .875rem;
        --shadow: none;
    }
    #hearings-calendar .modern-calendar { font-family: inherit; border: 1px solid var(--border); }
    #hearings-calendar { --danger-color: #be123c; }
    .dark #hearings-calendar { --danger-color: #fda4af; }
    .dark #hearings-calendar .calendar-body { background: var(--surface-strong); }
    .dark #hearings-calendar .weekday { color: var(--text-subtle); }
    #hearings-calendar .day.other-month { opacity: .55; }
    #hearings .cs-table td:last-child > div { flex-wrap: nowrap; }
    #hearings .cs-table td:nth-child(4) { white-space: nowrap; }
    #hearings .cs-table td:nth-child(2) { min-width: 10.5rem; }
    .cs-table td:first-child { color: var(--text-subtle); }

    /* Print */
    @media print {
        .no-print, .no-print * { display: none !important; }
        button, form, input, textarea, select, details, summary { display: none !important; }
        [x-cloak], #review-modal, #review-quick-form { display: none !important; }
        .main-content-section { box-shadow: none !important; border: 1px solid #e5e7eb !important; }
        *, *::before, *::after { box-shadow: none !important; }
    }
    </style>
    @php
    $assetVersion = function (string $path): string {
        try {
            return (string) filemtime(public_path($path));
        } catch (\Throwable $e) {
            return (string) now()->timestamp;
        }
    };
    $modernCalendarCssV = $assetVersion('vendor/modern-ethiopian-calendar/css/modern-calendar.css');
    $modernDatepickerCssV = $assetVersion('vendor/modern-ethiopian-calendar/css/datepicker.css');
    @endphp
    {{-- Modern Ethiopian calendar assets --}}
    <link rel="stylesheet"
        href="{{ asset('vendor/modern-ethiopian-calendar/css/modern-calendar.css') }}?v={{ $modernCalendarCssV }}">
    <link rel="stylesheet"
        href="{{ asset('vendor/modern-ethiopian-calendar/css/datepicker.css') }}?v={{ $modernDatepickerCssV }}">

    @endpush

    @push('scripts')
    <script>
    (function() {
        const templates = {{ \Illuminate\Support\Js::from($inlineTemplatesData ?? []) }};
        const selectEl = document.getElementById('inline-template-select');
        const loadBtn = document.getElementById('inline-template-load');
        const hiddenTemplate = document.getElementById('inline-template-hidden');
        const subjectInput = document.querySelector('#write-letter-panel input[name="subject"]');
        const refBlock = document.getElementById('inline-reference-block');
        const refValue = document.getElementById('inline-reference-value');
        const placeholderBlock = document.getElementById('inline-placeholders');
        const placeholderText = document.getElementById('inline-placeholders-text');
        const summaryBlock = document.getElementById('inline-template-summary');
        const summaryTitle = document.getElementById('inline-template-title');
        const summaryCategory = document.getElementById('inline-template-category');
        const summaryExcerpt = document.getElementById('inline-template-excerpt');
        const categoryFallback = {{ \Illuminate\Support\Js::from(__('letters.form.category_fallback')) }};

        const getTpl = (id) => templates[id] || templates[String(id)] || templates[Number(id)];

        const stripHtml = (html) => {
            const div = document.createElement('div');
            div.innerHTML = html || '';
            return div.textContent || div.innerText || '';
        };

        const buildReference = (tpl) => {
            if (!tpl || tpl.reference_sequence === null || tpl.reference_sequence === undefined) return null;
            const next = (parseInt(tpl.reference_sequence, 10) || 0) + 1;
            const seq = String(next).padStart(4, '0');
            return [tpl.subject_prefix || '', seq].filter(Boolean).join('/');
        };

        const renderMeta = (tpl) => {
            if (!tpl) {
                placeholderBlock?.classList.add('hidden');
                summaryBlock?.classList.add('hidden');
                refBlock?.classList.add('hidden');
                return;
            }
            const placeholders = Array.isArray(tpl.placeholders) ? tpl.placeholders : [];
            if (placeholderBlock) {
                if (placeholders.length) {
                    placeholderText.textContent = placeholders.join(', ');
                    placeholderBlock.classList.remove('hidden');
                } else {
                    placeholderBlock.classList.add('hidden');
                }
            }
            if (summaryBlock) {
                summaryBlock.classList.remove('hidden');
                if (summaryTitle) summaryTitle.textContent = tpl.title || '';
                if (summaryCategory) summaryCategory.textContent = tpl.category || categoryFallback;
                if (summaryExcerpt) {
                    const text = stripHtml(tpl.body || '');
                    summaryExcerpt.textContent = text.length > 120 ? text.slice(0, 120) + '...' : text;
                }
            }
            if (refBlock && refValue) {
                const ref = buildReference(tpl);
                if (ref) {
                    refValue.value = ref;
                    refBlock.classList.remove('hidden');
                } else {
                    refBlock.classList.add('hidden');
                }
            }
        };

        const applyTemplate = (tpl) => {
            if (!tpl) return;
            if (subjectInput) subjectInput.value = tpl.subject || tpl.title || '';
            if (hiddenTemplate) hiddenTemplate.value = tpl.id || '';

            const body = tpl.body || '';
            const editor = tinymce.get('letter-body-editor');
            if (editor) {
                editor.setContent(body);
                editor.focus();
            } else {
                const textarea = document.getElementById('letter-body-editor');
                if (textarea) textarea.value = body;
            }
            renderMeta(tpl);
        };

        const handleLoad = () => {
            const id = selectEl?.value;
            if (!id) {
                alert({{ \Illuminate\Support\Js::from(__('letters.form.select_placeholder')) }});
                return;
            }
            const tpl = getTpl(id);
            applyTemplate(tpl);
        };

        selectEl?.addEventListener('change', handleLoad);
        loadBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            handleLoad();
        });

        if (selectEl?.value) {
            applyTemplate(getTpl(selectEl.value));
        }
    })();
    </script>
    @endpush

    <div class="enterprise-page case-typography" x-data="{
            activeSection: {{ json_encode($defaultSection) }},
            selectedFindingId: null,
            init() {
                const hashSection = window.location.hash ? window.location.hash.substring(1) : null;
                if (hashSection) {
                    this.activeSection = hashSection;
                }
                const syncSectionFromHash = () => {
                    if (window.location.hash) {
                        this.activeSection = window.location.hash.substring(1);
                    }
                };
                window.addEventListener('popstate', syncSectionFromHash);
                window.addEventListener('hashchange', syncSectionFromHash);
            },
            openSection(section) {
                this.activeSection = section;
                history.pushState(null, null, '#' + section);
                this.$nextTick(() => {
                    const target = document.getElementById(section);
                    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            },
            toggleFindingDetails(id) {
                this.activeSection = 'inspection-findings';
                this.selectedFindingId = this.selectedFindingId === id ? null : id;
            },
        }" x-on:open-section.window="openSection($event.detail.section)" x-init="init()">

        {{-- Header: identity, key facts and the actions for this case --}}
        <header class="cs-hero mb-5">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0 flex-1">
                    <a href="{{ route('cases.index') }}" class="cs-back no-print">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        {{ __('cases.back') }}
                    </a>

                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-2">
                        <h1 class="cs-case-no" aria-label="{{ __('cases.case_number') }} {{ $case->case_number }}">
                            <span id="case-no">{{ $case->case_number }}</span>
                        </h1>
                        <button type="button" class="cs-icon-btn no-print"
                            x-data="{ copied: false }"
                            x-on:click="navigator.clipboard?.writeText(document.querySelector('#case-no').textContent.trim()); copied = true; setTimeout(() => copied = false, 1400)"
                            :aria-label="copied ? @js(__('cases.copied')) : @js(__('cases.copy').' '.$case->case_number)"
                            title="{{ __('cases.copy') }}">
                            <svg x-show="!copied" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <svg x-show="copied" x-cloak class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <span class="{{ $headerChip }} {{ $statusChip($currentStatus) }}">{{ __('cases.status.'.$currentStatus) }}</span>
                        <span class="{{ $headerChip }} {{ $reviewChip($reviewStatus) }}">{{ $reviewLabel($reviewStatus) }}</span>
                    </div>
                </div>

                {{-- Actions: the next step for this case is the one accent button --}}
                <div class="cs-actions no-print">
                    @if(in_array($reviewStatus, ['awaiting_review','returned']) && $canReview)
                    <div class="relative" x-data="{ reviewMenuOpen: false }" @click.outside="reviewMenuOpen = false" @keydown.escape="reviewMenuOpen = false">
                        <button type="button" class="{{ $headerPrimaryAction }}" @click="reviewMenuOpen = !reviewMenuOpen"
                            aria-haspopup="menu" :aria-expanded="reviewMenuOpen.toString()">
                            {{ __('cases.show.review_actions') }}
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="reviewMenuOpen" x-cloak role="menu"
                            class="absolute right-0 top-full mt-1.5 w-48 rounded-xl border border-[var(--border)] bg-[var(--surface-strong)] shadow-xl z-30 py-1.5 overflow-hidden">
                            <button type="button" role="menuitem" class="cs-menu-item text-emerald-700 dark:text-emerald-300"
                                @click="reviewMenuOpen=false; submitReviewDecision('accept')">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                {{ __('cases.show.accept') }}
                            </button>
                            <button type="button" role="menuitem" class="cs-menu-item text-amber-700 dark:text-amber-300"
                                @click="reviewMenuOpen=false; openReviewModal('return')">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                {{ __('cases.show.return') }}
                            </button>
                            <button type="button" role="menuitem" class="cs-menu-item text-red-700 dark:text-red-300"
                                @click="reviewMenuOpen=false; openReviewModal('reject')">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                {{ __('cases.show.reject') }}
                            </button>
                        </div>
                    </div>
                    @endif

                    @if(!$caseLocked && ($case->status ?? '') === 'closed')
                    <a href="{{ route('decisions.create', ['case_id' => $case->id]) }}" class="{{ $headerPrimaryAction }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('cases.show.give_decision') }}
                    </a>
                    @endif

                    @if(!$caseLocked && $canAssign)
                    <a href="{{ route('cases.assign.form', $case->id) }}" class="{{ $headerSecondaryAction }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        {{ __('cases.assign_change') }}
                    </a>
                    @endif

                    @if(!$caseLocked && $canWriteLetter)
                    <a href="#letters-compose" @click.prevent="openSection('letters-compose')" class="{{ $headerSecondaryAction }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ __('cases.show.write_letter') }}
                    </a>
                    @endif

                    @if(!$caseLocked && $canManageBench)
                    <a href="{{ route('bench-notes.index', ['case_id' => $case->id]) }}" class="{{ $headerSecondaryAction }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        {{ __('cases.show.bench_note') }}
                    </a>
                    @endif

                    @if($canManageInspectionRequests)
                    <a href="{{ route('case-inspection-requests.create', ['case_id' => $case->id]) }}" class="{{ $headerSecondaryAction }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        {{ __('cases.show.inspection_request') }}
                    </a>
                    @endif
                </div>
            </div>

            <dl class="cs-facts">
                <div>
                    <dt>{{ __('cases.summary.filing_date') }}</dt>
                    <dd>{{ $case->filing_date ? \App\Support\EthiopianDate::format($case->filing_date) : '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('cases.summary.applicant') }}</dt>
                    <dd>{{ $applicantDisplayName !== '' ? $applicantDisplayName : '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('cases.table.type') }}</dt>
                    <dd>{{ $case->case_type ?: '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('cases.summary.assignee') }}</dt>
                    @if(!empty($case->assignee_name))
                    <dd>{{ $case->assignee_name }}</dd>
                    @else
                    <dd class="cs-fact-warn">{{ __('cases.summary.unassigned') }}</dd>
                    @endif
                </div>
            </dl>

            @if($caseLocked)
            <div class="cs-notice cs-notice-warn mt-4" role="status">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>{{ __('cases.show.actions_locked') }}</span>
            </div>
            @endif
        </header>

        {{-- Modal for return/reject note --}}
        <div id="review-modal" class="ui-modal-backdrop fixed inset-0 hidden items-center justify-center z-30 p-4 no-print"
            role="dialog" aria-modal="true" aria-labelledby="review-modal-title">
            <div class="ui-modal-panel max-w-lg w-full p-6">
                <h3 class="text-lg font-semibold text-[var(--text)] mb-3" id="review-modal-title">{{ __('cases.show.review_decision') }}</h3>
                <form method="POST" action="{{ route('cases.review.update', $case->id) }}" id="review-form"
                    class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="decision" id="review-decision" value="">
                    <div>
                        <label for="review-note" class="cs-label">{{ __('cases.show.reason_note') }}</label>
                        <textarea name="note" id="review-note" rows="3" required
                            class="ui-textarea">{{ old('note', $reviewNote ?? '') }}</textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeReviewModal()"
                            class="btn btn-secondary">{{ __('cases.general.cancel') }}</button>
                        <button type="submit"
                            class="btn btn-primary">{{ __('cases.show.submit') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <form id="review-quick-form" method="POST" action="{{ route('cases.review.update', $case->id) }}"
            class="hidden">
            @csrf
            @method('PATCH')
            <input type="hidden" name="decision" id="review-quick-decision" value="accept">
            <input type="hidden" name="note" value="">
        </form>

        {{-- Status change (admins) --}}
        @if($canEditStatus && $currentStatus !== 'closed')
        <section class="cs-panel mb-5 no-print" aria-labelledby="cs-status-title">
            <h2 id="cs-status-title" class="cs-panel-title">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ __('cases.status.change_case_status') }}
            </h2>
            <form method="POST" action="{{ route('cases.status.update', $case->id) }}"
                class="grid gap-4 md:grid-cols-[minmax(0,14rem)_minmax(0,1fr)_auto] md:items-end">
                @csrf @method('PATCH')
                <div>
                    <label for="cs-status-select" class="cs-label">{{ __('cases.status.new_status') }}</label>
                    <select id="cs-status-select" name="status" class="cs-field">
                        @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus===$value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="cs-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="cs-status-note" class="cs-label">{{ __('cases.status.note_to_timeline') }}</label>
                    <input id="cs-status-note" name="note" placeholder="{{ __('cases.status.add_note_placeholder') }}" class="cs-field">
                    @error('note') <p class="cs-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <button type="submit" class="btn btn-primary w-full md:w-auto">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('cases.status.update_status') }}
                    </button>
                </div>
            </form>
        </section>
        @elseif($canEditStatus)
        <div class="cs-notice cs-notice-ok mb-5 no-print" role="status">
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <span><strong class="font-semibold">{{ __('cases.status.closed_locked_title') }}.</strong> {{ __('cases.status.closed_locked') }}</span>
        </div>
        @endif

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-6">
            {{-- Section navigation: vertical on desktop, a scrollable strip on small screens --}}
            <aside class="no-print min-w-0">
                <nav class="cs-nav" aria-label="{{ __('cases.navigation.title') }}">
                    @foreach($sectionNav as $item)
                        @if($loop->first || $sectionNav[$loop->index - 1]['group'] !== $item['group'])
                        <p class="cs-nav-group">{{ $item['group'] === 'main' ? __('cases.navigation.title') : __('cases.show.quick_sections') }}</p>
                        @endif
                        <button type="button" class="cs-nav-btn" @click="openSection(@js($item['id']))"
                            :aria-current="(activeSection === @js($item['id']) || (@js($item['id']) === 'letters' && activeSection === 'letters-compose')) ? 'true' : 'false'">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                            <span class="truncate">{{ $item['label'] }}</span>
                            @if(!empty($item['count']))
                            <span class="cs-nav-count">{{ $item['count'] }}</span>
                            @endif
                        </button>
                    @endforeach
                </nav>
            </aside>
            {{-- Main Content --}}
            <div class="min-w-0 space-y-6">
                {{-- Case Summary --}}
                <section id="case-summary" x-show="activeSection === 'case-summary'"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="main-content-section p-5 sm:p-6 space-y-6" aria-labelledby="cs-summary-title">
                    <div class="cs-section-header">
                        <span class="cs-section-icon">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <h3 id="cs-summary-title">{{ __('cases.navigation.case_summary') }}</h3>
                    </div>

                    {{-- Submitted by lawyer --}}
                    @if($submittedByLawyer)
                    @php $hasLawyerDoc = !empty($case->applicant_lawyer_document_path); @endphp
                    <div x-data="{ pdfOpen: false }"
                        class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-indigo-200 bg-indigo-50 p-4">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <p class="font-semibold text-indigo-900">
                                {{ __('dashboard.submitted_by_lawyer', ['name' => $lawyerName]) }}
                            </p>
                        </div>

                        @if($hasLawyerDoc)
                        <button type="button" @click="pdfOpen = true"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-300 bg-white px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-50 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            {{ __('cases.show.view_lawyer_document') }}
                        </button>

                        {{-- PDF preview modal --}}
                        <div x-cloak x-show="pdfOpen" @keydown.escape.window="pdfOpen = false"
                            class="fixed inset-0 z-50 flex items-center justify-center p-4">
                            <div class="absolute inset-0 bg-black/50" @click="pdfOpen = false"></div>
                            <div class="relative z-10 flex h-[80vh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
                                <div class="flex items-center justify-between bg-orange-500 px-4 py-3">
                                    <h4 class="text-sm font-semibold text-white">{{ __('cases.show.lawyer_document') }}</h4>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('cases.lawyer-document', $case->id) }}" target="_blank"
                                            class="inline-flex items-center gap-1 rounded-lg border border-white/40 bg-white/10 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-white/20">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            {{ __('cases.show.open_new_tab') }}
                                        </a>
                                        <button type="button" @click="pdfOpen = false"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-white hover:bg-white/20">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <iframe x-show="pdfOpen" :src="pdfOpen ? '{{ route('cases.lawyer-document', $case->id) }}' : ''"
                                    class="w-full flex-1 min-h-0 border-0" title="{{ __('cases.show.lawyer_document') }}"></iframe>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Parties --}}
                    @php
                    $applicantAddress = trim((string) ($case->applicant_address ?? ''));
                    if ($applicantAddress === '') {
                        $applicantAddress = trim((string) ($case->applicant_profile_address ?? ''));
                    }
                    @endphp
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="cs-party">
                            <div class="cs-party-label">
                                <span class="cs-party-icon bg-[rgb(var(--ac)/0.1)] text-[rgb(var(--ac))]">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                {{ __('cases.summary.applicant') }}
                            </div>
                            <p class="cs-party-name">{{ $applicantDisplayName !== '' ? $applicantDisplayName : '—' }}</p>
                            <p class="cs-party-meta">{{ $applicantAddress !== '' ? $applicantAddress : '—' }}</p>
                        </div>
                        <div class="cs-party">
                            <div class="cs-party-label">
                                <span class="cs-party-icon bg-rose-500/10 text-rose-600 dark:text-rose-300">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </span>
                                {{ __('cases.respondent_defendant') }}
                            </div>
                            <p class="cs-party-name">{{ $case->respondent_name ?: '—' }}</p>
                            <p class="cs-party-meta">{{ $case->respondent_address ?: '—' }}</p>
                        </div>
                    </div>

                    {{-- Record dates (type and filing date are in the header) --}}
                    <dl class="cs-fact-grid">
                        <div>
                            <dt>{{ __('cases.summary.created') }}</dt>
                            <dd>{{ $case->created_at ? \App\Support\EthiopianDate::format($case->created_at, withTime: true, timeFormat: 'h:i A') : '—' }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('cases.summary.updated') }}</dt>
                            <dd>{{ $case->updated_at ? \App\Support\EthiopianDate::format($case->updated_at, withTime: true, timeFormat: 'h:i A') : '—' }}</dd>
                        </div>
                    </dl>

                    {{-- Assignment --}}
                    <div>
                        <h4 class="cs-subheading">{{ __('cases.summary.assignee') }}</h4>
                        @if($case->assignee_name)
                        <div class="cs-party flex flex-wrap items-center gap-x-6 gap-y-2">
                            <div class="flex items-center gap-2.5">
                                <span class="cs-party-icon bg-emerald-500/10 text-emerald-700 dark:text-emerald-300">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                <span class="cs-party-name">{{ $case->assignee_name }}</span>
                            </div>
                            @if(!empty($case->assignee_team_name))
                            <span class="cs-party-meta">{{ __('cases.summary.team') }}: <span class="font-medium text-[var(--text)]">{{ $case->assignee_team_name }}</span></span>
                            @endif
                            @if($case->assigned_at)
                            <span class="cs-party-meta">{{ __('cases.summary.assigned_at') }}: <span class="font-medium text-[var(--text)]">{{ \App\Support\EthiopianDate::format($case->assigned_at, withTime: true, timeFormat: 'h:i A') }}</span></span>
                            @endif
                        </div>
                        @else
                        <div class="cs-notice cs-notice-warn">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span class="font-medium">{{ __('cases.summary.unassigned') }}</span>
                            @if(!$caseLocked && $canAssign)
                            <a href="{{ route('cases.assign.form', $case->id) }}" class="ml-auto font-semibold underline underline-offset-2">{{ __('cases.assign_change') }}</a>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Hearings --}}
                    @php $summaryHearings = collect($hearings ?? []); @endphp
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <h4 class="cs-subheading">{{ __('cases.summary.hearings') }}</h4>
                            @if($summaryHearings->count() > 3 && $canSeeHearings)
                            <button type="button" class="text-xs font-semibold text-[rgb(var(--ac))] hover:underline no-print" @click="openSection('hearings')">{{ __('cases.navigation.hearings') }} ({{ $summaryHearings->count() }})</button>
                            @endif
                        </div>
                        @if($summaryHearings->isNotEmpty())
                        <ul class="space-y-2">
                            @foreach($summaryHearings->take(3) as $hearing)
                            <li class="cs-party flex items-start gap-3 !p-3">
                                <span class="cs-party-icon bg-[rgb(var(--ac)/0.1)] text-[rgb(var(--ac))]">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="cs-party-name">{{ \App\Support\EthiopianDate::smartFormat($hearing->hearing_at, true, '—', 'h:i A', 'F j, Y') }}</p>
                                    @if(!empty($hearing->note))
                                    <p class="cs-party-meta">{{ \Illuminate\Support\Str::limit(strip_tags($hearing->note), 90) }}</p>
                                    @endif
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <p class="cs-empty">{{ __('cases.no_hearings_scheduled') }}</p>
                        @endif
                    </div>
                </section>

                {{-- Case Audits --}}
                <section id="audits" x-show="activeSection === 'audits'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section p-5 sm:p-6 space-y-3">
                    <div class="cs-section-header">
                        <span class="cs-section-icon"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" aria-hidden="true" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg></span>
                        <h3>
                            {{ __('cases.case_audit_trail') }}
                        </h3>
                        <span class="cs-count-badge">{{ ($audits ?? collect())->count() }} {{ __('cases.show.entries') }}</span>
                    </div>
                    @if(($audits ?? collect())->isEmpty())
                    <div
                        class="cs-empty">
                        {{ __('cases.no_audit_records') }}
                    </div>
                    @else
                    <div class="cs-table-wrap">
                        <table class="cs-table">
                            <thead class="bg-gray-50 text-gray-700">
                                <tr>
                                    <th class="p-3 text-left font-medium border-b border-gray-200">{{ __('cases.show.when') }}</th>
                                    <th class="p-3 text-left font-medium border-b border-gray-200">{{ __('cases.show.action') }}</th>
                                    <th class="p-3 text-left font-medium border-b border-gray-200">{{ __('cases.show.actor') }}</th>
                                    <th class="p-3 text-left font-medium border-b border-gray-200">{{ __('cases.show.details') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($audits as $a)
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 text-gray-700 whitespace-nowrap">
                                        {{ \App\Support\EthiopianDate::format($a->created_at, withTime: true) }}
                                    </td>
                                    <td class="p-3 text-gray-900 font-medium">
                                        {{ str_replace('_',' ', ucfirst($a->action)) }}</td>
                                    <td class="p-3 text-gray-700 text-xs">
                                        @if(!empty($a->actor_name))
                                        {{ $a->actor_name }} @if($a->actor_id)(#{{ $a->actor_id }})@endif
                                        @elseif(!empty($a->actor_id))
                                        {{ $a->actor_type ?? 'system' }} (#{{ $a->actor_id }})
                                        @else
                                        {{ $a->actor_type ?? 'system' }}
                                        @endif
                                    </td>
                                    <td class="p-3 text-gray-700 text-xs">
                                        @php $meta = $a->meta ? json_decode($a->meta, true) : []; @endphp
                                        @if($meta)
                                        <pre
                                            class="bg-gray-100 border border-gray-200 rounded-sm px-2 py-1 whitespace-pre-wrap text-[11px]">{{ json_encode($meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
                                        @else
                                        <span class="text-[var(--text-subtle)]">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </section>

                @if($canManageInspectionRequests)
                <section id="inspection-requests" x-show="activeSection === 'inspection-requests'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section p-5 sm:p-6 space-y-4">
                    <div class="cs-section-header">
                        <h3>{{ __('case_inspections.requests.index_title') }}</h3>
                        <span class="cs-count-badge">
                            {{ ($inspectionRequests ?? collect())->count() }} {{ __('cases.show.entries') }}
                        </span>
                    </div>

                    @if(($inspectionRequests ?? collect())->isEmpty())
                    <div class="cs-empty">
                        {{ __('case_inspections.requests.empty') }}
                    </div>
                    @else
                    <div class="cs-table-wrap">
                        <table class="cs-table">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('case_inspections.requests.table_date') }}</th>
                                    <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('case_inspections.requests.table_subject') }}</th>
                                    <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('case_inspections.requests.table_status') }}</th>
                                    <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('case_inspections.requests.table_inspector') }}</th>
                                    <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('case_inspections.requests.table_created_by') }}</th>
                                    <th class="px-3 py-2 text-right font-medium border-b border-gray-200">{{ __('cases.labels.actions') ?? 'Actions' }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach(($inspectionRequests ?? collect()) as $req)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-gray-700">{{ \App\Support\EthiopianDate::smartFormat($req->request_date) }}</td>
                                    <td class="px-3 py-2 text-gray-900">{{ $req->subject }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ __('case_inspections.status.' . $req->status) }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $req->assigned_inspector_name ?? __('case_inspections.common.no_data') }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $req->created_by_name ?? __('case_inspections.common.no_data') }}</td>
                                    <td class="px-3 py-2 text-right space-x-2">
                                        <a href="{{ route('case-inspection-requests.show', $req->id) }}" class="text-blue-700 hover:text-blue-800 text-sm">{{ __('case_inspections.common.view') }}</a>
                                        @if(($req->status ?? '') !== 'completed')
                                        <a href="{{ route('case-inspection-requests.edit', $req->id) }}" class="text-amber-700 hover:text-amber-800 text-sm">{{ __('case_inspections.common.edit') }}</a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </section>
                @endif

                @if($canManageInspectionFindings)
                <section id="inspection-findings" x-show="activeSection === 'inspection-findings'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section p-5 sm:p-6 space-y-4">
                    <div class="cs-section-header">
                        <h3>{{ __('case_inspections.findings.index_title') }}</h3>
                        <span class="cs-count-badge">
                            {{ ($inspectionFindings ?? collect())->count() }} {{ __('cases.show.entries') }}
                        </span>
                    </div>

                    @if(($inspectionFindings ?? collect())->isEmpty())
                    <div class="cs-empty">
                        {{ __('case_inspections.findings.empty') }}
                    </div>
                    @else
                    <div class="cs-table-wrap">
                        <table class="cs-table">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('case_inspections.findings.table_date') }}</th>
                                    <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('case_inspections.findings.table_title') }}</th>
                                    <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('case_inspections.findings.table_severity') }}</th>
                                    <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('case_inspections.findings.table_request') }}</th>
                                    <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('case_inspections.requests.table_inspector') }}</th>
                                    <th class="px-3 py-2 text-right font-medium border-b border-gray-200">{{ __('cases.labels.actions') ?? 'Actions' }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach(($inspectionFindings ?? collect()) as $finding)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-gray-700">{{ \App\Support\EthiopianDate::smartFormat($finding->finding_date) }}</td>
                                    <td class="px-3 py-2 text-gray-900">
                                        {{ $finding->title }}
                                        @if(!empty($finding->accepted_at))
                                        <span class="ml-2 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">{{ __('case_inspections.findings.accepted') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-gray-700">{{ __('case_inspections.severity.' . $finding->severity) }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $finding->request_subject ?? __('case_inspections.common.no_data') }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $finding->assigned_inspector_name ?? __('case_inspections.common.no_data') }}</td>
                                    <td class="px-3 py-2 text-right space-x-2">
                                        @if(($canManageInspectionFindings ?? false) && (auth()->user()?->hasRole('admin') ?? false) && empty($finding->accepted_at))
                                        <form method="POST" action="{{ route('case-inspection-findings.accept', $finding->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-emerald-700 hover:text-emerald-800 text-sm">{{ __('case_inspections.findings.accept') }}</button>
                                        </form>
                                        @endif
                                        <button type="button" @click="toggleFindingDetails({{ (int) $finding->id }})" class="text-blue-700 hover:text-blue-800 text-sm">{{ __('case_inspections.common.view') }}</button>
                                    </td>
                                </tr>
                                <tr x-show="selectedFindingId === {{ (int) $finding->id }}" x-cloak class="bg-gray-50">
                                    <td colspan="6" class="px-4 py-3">
                                        <div class="rounded-lg border border-gray-200 bg-white p-4 space-y-3">
                                            <div class="grid md:grid-cols-3 gap-3 text-sm">
                                                <div><span class="text-gray-500">{{ __('case_inspections.findings.labels.finding_date') }}:</span> <span class="text-gray-900">{{ \App\Support\EthiopianDate::smartFormat($finding->finding_date) }}</span></div>
                                                <div><span class="text-gray-500">{{ __('case_inspections.findings.labels.severity') }}:</span> <span class="text-gray-900">{{ __('case_inspections.severity.' . $finding->severity) }}</span></div>
                                                <div><span class="text-gray-500">{{ __('case_inspections.findings.labels.recorded_by') }}:</span> <span class="text-gray-900">{{ $finding->recorded_by_name ?? __('case_inspections.common.no_data') }}</span></div>
                                                <div>
                                                    <span class="text-gray-500">{{ __('case_inspections.findings.labels.attachment_pdf') }}:</span>
                                                    @if(!empty($finding->attachment_path))
                                                    <a href="{{ route('case-inspection-findings.attachment', $finding->id) }}" target="_blank" rel="noopener noreferrer" class="text-blue-700 hover:text-blue-800 underline">
                                                        {{ $finding->attachment_original_name ?? __('case_inspections.findings.labels.download_attachment') }}
                                                    </a>
                                                    @else
                                                    <span class="text-gray-900">{{ __('case_inspections.common.no_data') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ __('case_inspections.findings.labels.details') }}</p>
                                                <div class="text-sm text-gray-800 whitespace-pre-wrap">{{ $finding->details }}</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </section>
                @endif

                {{-- Case Details --}}
                @php
                $reliefHtmlOut = null;
                if (!empty($case->relief_requested_html ?? null)) {
                $reliefHtmlOut = clean($case->relief_requested_html, 'cases');
                } elseif (!empty($case->relief_requested)) {
                $reliefHtmlOut = clean($case->relief_requested, 'cases');
                }

                // Active Terms & Conditions, printed as a list under the respondent section.
                $printTerms = \App\Models\TermsAndCondition::published()
                    ->orderByDesc('published_at')
                    ->first();

                $termsItems = [];
                if ($printTerms && !empty($printTerms->body)) {
                    $termsHtml = clean($printTerms->body, 'cases');

                    // Prefer existing list items / paragraphs as the natural list rows.
                    if (preg_match_all('/<li\b[^>]*>(.*?)<\/li>/is', $termsHtml, $m) && !empty($m[1])) {
                        $rows = $m[1];
                    } elseif (preg_match_all('/<p\b[^>]*>(.*?)<\/p>/is', $termsHtml, $m) && !empty($m[1])) {
                        $rows = $m[1];
                    } else {
                        // Fall back to splitting on line breaks.
                        $rows = preg_split('/<br\s*\/?>|\r\n|\n/i', $termsHtml);
                    }

                    foreach ($rows as $row) {
                        $row = trim(preg_replace('/\s+/', ' ', strip_tags($row, '<strong><b><em><i><u><a>')));
                        if ($row !== '') {
                            $termsItems[] = $row;
                        }
                    }
                }
                @endphp
                <section id="case-details" x-show="activeSection === 'case-details'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section p-5 sm:p-6 space-y-6">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-xl font-semibold text-gray-900">{{ __('cases.show.case_details_overview') }}</h3>
                        <button type="button" onclick="printCaseOverview()"
                            class="btn btn-secondary no-print">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            {{ __('cases.print') }}
                        </button>
                    </div>

                    {{-- Word-style printable document (content only, no UI chrome) --}}
                    <template id="case-overview-print-tpl">
                        <h1>{{ __('cases.show.case_details_overview') }}</h1>
                        <div class="doc-meta-row">
                            <p>{{ __('cases.case_number') }} <strong>{{ $case->case_number }}</strong></p>
                            <p>{{ __('cases.summary.filing_date') }} <strong>{{ $case->filing_date ? \App\Support\EthiopianDate::format($case->filing_date) : '—' }}</strong></p>
                        </div>

                        <h2>{{ __('cases.summary.applicant') }}</h2>
                        @if($submittedByLawyer)
                        <p><strong>{{ __('dashboard.submitted_by_lawyer', ['name' => $lawyerName]) }}</strong></p>
                        @endif
                        <table class="doc-fields">
                            <tr><td>{{ __('cases.name') }}</td><td>{{ $applicantDisplayName !== '' ? $applicantDisplayName : '—' }}</td></tr>
                            <tr><td>{{ __('cases.address') }}</td><td>{{ trim((string) ($case->applicant_address ?? $case->applicant_profile_address ?? '')) ?: '—' }}</td></tr>
                            <tr><td>{{ __('cases.applicant_email') }}</td><td>{{ $case->applicant_email ?? '—' }}</td></tr>
                        </table>

                        <h2>{{ __('cases.respondent_defendant') }}</h2>
                        <table class="doc-fields">
                            <tr><td>{{ __('cases.name') }}</td><td>{{ $case->respondent_name ?? '—' }}</td></tr>
                            <tr><td>{{ __('cases.address') }}</td><td>{{ $case->respondent_address ?? '—' }}</td></tr>
                        </table>

                        @if(!empty($termsItems))
                        <ol class="doc-terms-list">
                            @foreach($termsItems as $item)
                            <li>{!! $item !!}</li>
                            @endforeach
                        </ol>
                        @endif

                        <h2>{{ __('cases.details.case_details') }}</h2>
                        <div class="doc-body">
                            {!! $case->description_html ?? clean($case->description ?? __('cases.details.no_details'), 'cases') !!}
                        </div>

                        <h2>{{ __('cases.details.relief_requested') }}</h2>
                        <div class="doc-body">
                            {!! $reliefHtmlOut ?? __('cases.details.no_relief_specified') !!}
                        </div>

                        @if(($docs ?? collect())->isNotEmpty())
                        <h2>{{ __('cases.documents.submitted_documents') }}</h2>
                        <ol class="doc-list">
                            @foreach(($docs ?? collect()) as $d)
                            <li>{{ trim((string) ($d->title ?? '')) ?: __('cases.documents.document') }}@if(!empty($d->created_at)) — {{ \App\Support\EthiopianDate::format($d->created_at, withTime: true) }}@endif</li>
                            @endforeach
                        </ol>
                        @endif

                        @if(($witnesses ?? collect())->isNotEmpty())
                        <h2>{{ __('cases.witnesses_section.title') }}</h2>
                        <table class="doc-table">
                            <thead>
                                <tr>
                                    <th>{{ __('cases.labels.name') }}</th>
                                    <th>{{ __('cases.labels.phone') }}</th>
                                    <th>{{ __('cases.labels.email') }}</th>
                                    <th>{{ __('cases.labels.address') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($witnesses as $w)
                                <tr>
                                    <td>{{ $w->full_name }}</td>
                                    <td>{{ $w->phone ?? '—' }}</td>
                                    <td>{{ $w->email ?? '—' }}</td>
                                    <td>{{ $w->address ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif

                        <p class="doc-printed">{{ \App\Support\EthiopianDate::format(now(), withTime: true) }}</p>
                    </template>
                    <script>
                        function printCaseOverview() {
                            const tpl = document.getElementById('case-overview-print-tpl');
                            if (!tpl) return;
                            const w = window.open('', '_blank');
                            if (!w) return;
                            w.document.write(`<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<title>{{ __('cases.show.case_details_overview') }} - {{ $case->case_number }}</title>
<style>
    @page { size: A4; margin: 2.54cm; }
    body {
        font-family: 'Times New Roman', 'Nyala', 'Abyssinica SIL', 'Noto Sans Ethiopic', serif;
        font-size: 12pt; line-height: 1.7; color: #000; margin: 0;
    }
    h1 { font-size: 16pt; text-align: center; margin: 0 0 4pt; }
    .doc-meta-row {
        display: flex; justify-content: space-between; gap: 24pt;
        margin: 0 0 18pt; font-size: 11pt;
    }
    .doc-meta-row p { margin: 0; font-weight: bold; }
    .doc-meta-row p:first-child { text-align: left; }
    .doc-meta-row p:last-child { text-align: right; }
    h2 { font-size: 13pt; margin: 16pt 0 6pt; border-bottom: 1px solid #000; padding-bottom: 2pt; }
    .doc-fields { border-collapse: collapse; width: 100%; }
    .doc-fields td { padding: 2pt 6pt 2pt 0; vertical-align: top; }
    .doc-fields td:first-child { width: 30%; font-weight: bold; }
    .doc-terms-list { margin: 6pt 0 12pt; padding-left: 22pt; text-align: justify; }
    .doc-terms-list li { margin: 0 0 5pt; }
    .doc-body { text-align: justify; }
    .doc-body p { margin: 0 0 8pt; }
    .doc-body table { border-collapse: collapse; width: 100%; }
    .doc-body td, .doc-body th { border: 1px solid #000; padding: 3pt 5pt; }
    .doc-list { margin: 0; padding-left: 20pt; }
    .doc-table { border-collapse: collapse; width: 100%; }
    .doc-table th, .doc-table td { border: 1px solid #000; padding: 3pt 6pt; text-align: left; vertical-align: top; }
    .doc-table th { font-weight: bold; }
    .doc-printed { margin-top: 24pt; text-align: right; font-size: 10pt; }
</style>
</head>
<body>
${tpl.innerHTML}
<script>
    const closeAfterPrint = () => {
        setTimeout(() => window.close(), 100);
    };

    window.addEventListener('afterprint', closeAfterPrint);
    window.onafterprint = closeAfterPrint;

    if (window.matchMedia) {
        const mediaQueryList = window.matchMedia('print');
        const handlePrintMediaChange = (event) => {
            if (!event.matches) {
                closeAfterPrint();
            }
        };

        if (mediaQueryList.addEventListener) {
            mediaQueryList.addEventListener('change', handlePrintMediaChange);
        } else if (mediaQueryList.addListener) {
            mediaQueryList.addListener(handlePrintMediaChange);
        }
    }
<\/script>
</body>
</html>`);
                            w.document.close();
                            w.focus();
                            const closePrintWindow = () => {
                                setTimeout(() => {
                                    if (!w.closed) {
                                        w.close();
                                    }
                                }, 100);
                            };

                            w.addEventListener('afterprint', closePrintWindow);

                            if (w.matchMedia) {
                                const mediaQueryList = w.matchMedia('print');
                                const handlePrintMediaChange = (event) => {
                                    if (!event.matches) {
                                        closePrintWindow();
                                    }
                                };

                                if (mediaQueryList.addEventListener) {
                                    mediaQueryList.addEventListener('change', handlePrintMediaChange);
                                } else if (mediaQueryList.addListener) {
                                    mediaQueryList.addListener(handlePrintMediaChange);
                                }
                            }

                            setTimeout(() => {
                                w.print();
                                setTimeout(closePrintWindow, 500);
                            }, 300);
                        }
                    </script>

                    @if($submittedByLawyer)
                    <div class="flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-800">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ __('dashboard.submitted_by_lawyer', ['name' => $lawyerName]) }}
                    </div>
                    @endif
                    <div class="grid md:grid-cols-2 gap-4 border border-gray-100 rounded-xl p-4 bg-gray-50">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('cases.summary.applicant') }}</p>
                            <dl class=" text-gray-700 space-y-1">
                                <div>
                                    <dt class="text-xs text-gray-500">{{ __('cases.name') }}</dt>
                                    <dd class="font-semibold text-gray-900">
                                        {{ $applicantDisplayName !== '' ? $applicantDisplayName : '—' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500">{{ __('cases.address') }}</dt>
                                    @php
                                    $applicantAddress = trim((string) ($case->applicant_address ?? ''));
                                    if ($applicantAddress === '') {
                                        $applicantAddress = trim((string) ($case->applicant_profile_address ?? ''));
                                    }
                                    @endphp
                                    <dd class="whitespace-pre-line">{{ $applicantAddress !== '' ? $applicantAddress : '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500">{{ __('cases.applicant_email') }}</dt>
                                    <dd>{{ $case->applicant_email ?? '&mdash;' }}</dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                {{ __('cases.respondent_defendant') }}</p>
                            <dl class=" text-gray-700 space-y-1">
                                <div>
                                    <dt class="text-xs text-gray-500">{{ __('cases.name') }}</dt>
                                    <dd class="font-semibold text-gray-900">{{ $case->respondent_name ?? '&mdash;' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500">{{ __('cases.address') }}</dt>
                                    <dd>{{ $case->respondent_address ?? '&mdash;' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                {{ __('cases.details.case_details') }}</p>
                            <div class="cms-output case-details-copy mt-2">
                                {!! $case->description_html ?? clean($case->description ??
                                __('cases.details.no_details'), 'cases') !!}
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                {{ __('cases.details.relief_requested') }}</p>
                            <div class="cms-output case-details-copy mt-2">
                                {!! $reliefHtmlOut ?? __('cases.details.no_relief_specified') !!}
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="cs-section-header">
                            <h4 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ __('cases.documents.submitted_documents') }}
                            </h4>
                            <span
                                class="cs-count-badge">{{ ($docs ?? collect())->count() }}
                                {{ __('cases.documents.items') }}</span>
                        </div>
                        @if(($docs ?? collect())->isEmpty())
                        <div
                            class="cs-empty">
                            {{ __('cases.documents.no_documents') }}
                        </div>
                        @else
                        <div class="relative overflow-visible rounded-lg border border-gray-100">
                            <table class="cs-table">
                                <thead class="bg-gray-50 text-gray-600">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('cases.documents.document') }}
                                        </th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('cases.summary.type') }}</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('cases.show.uploaded') }}
                                        </th>
                                        <th class="px-3 py-2 text-right font-medium border-b border-gray-200">{{ __('cases.show.action') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($docs as $d)
                                    @php
                                    $filePath = $d->file_path ?? $d->path ?? null;
                                    $docTitle = $d->title ?? ($d->label ?? ($filePath ? basename($filePath) :
                                    __('cases.documents.document')));
                                    $fileTime = !empty($d->created_at) ?
                                    \App\Support\EthiopianDate::format($d->created_at, withTime: true) : null;
                                    $fileSize = isset($d->size) ? number_format(max(0, (int) $d->size) / 1024, 1) :
                                    null;
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 align-top">
                                            <div class="font-medium text-gray-900">{{ $docTitle }}</div>
                                            @if(!empty($d->description))
                                            <div class="text-xs text-gray-600 mt-1 tiny-content">
                                                {!! clean($d->description, 'cases') !!}
                                            </div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-gray-700">
                                            {{ $d->mime ?? __('cases.documents.document') }}</td>
                                        <td class="px-3 py-2 text-gray-700">
                                            {{ $fileTime ?? '&mdash;' }} @if($fileSize)<span
                                                class="text-gray-500 text-xs">({{ $fileSize }} KB)</span>@endif
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <a @if($filePath)
                                                href="{{ route('cases.documents.view', [$case->id, $d->id]) }}"
                                                target="_blank" @endif
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border text-xs font-medium transition-colors duration-150 {{ $filePath ? 'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100' : 'bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed' }}"
                                                @unless($filePath) aria-disabled="true" @endunless>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                {{ __('cases.documents.view') }}
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>

                    <div class="space-y-3">
                        <div class="cs-section-header">
                            <h4 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0M15 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0zm6 3a2 2 0 1 1-4 0 2 2 0 0 1 4 0z" />
                                </svg>
                                {{ __('cases.witnesses_section.title') }}
                            </h4>
                            <span class="cs-count-badge">
                                {{ ($witnesses ?? collect())->count() }} {{ __('cases.witnesses_section.total') }}
                            </span>
                        </div>
                        @if(($witnesses ?? collect())->isEmpty())
                        <div
                            class="cs-empty">
                            {{ __('cases.witnesses_section.no_witnesses') }}
                        </div>
                        @else
                        <div class="relative overflow-x-auto overflow-y-visible rounded-lg border border-gray-100">
                            <table class="cs-table">
                                <thead class="bg-gray-50 text-gray-600">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">
                                            {{ __('cases.labels.name') }}</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">
                                            {{ __('cases.labels.phone') }}</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">
                                            {{ __('cases.labels.email') }}</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">
                                            {{ __('cases.labels.address') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($witnesses as $w)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 font-medium text-gray-900">{{ $w->full_name }}</td>
                                        <td class="px-3 py-2 text-gray-700">{{ $w->phone ?? '&mdash;' }}</td>
                                        <td class="px-3 py-2">
                                            @if(!empty($w->email))
                                            <a href="mailto:{{ $w->email }}"
                                                class="text-blue-700 hover:underline">{{ $w->email }}</a>
                                            @else
                                            <span class="text-gray-400">&mdash;</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-gray-700">{{ $w->address ?? '&mdash;' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </section>

                {{-- Letters Section --}}
                @php
                $templates = $letterTemplates ?? collect();
                $selectedTemplate = $selectedInlineTemplate ?? null;
                $recipientName = old('recipient_name', '');
                $recipientTitle = old('recipient_title', '');
                $recipientCompany = old('recipient_company', '');
                $cc = old('cc', '');
                $subject = old('subject', '');
                $body = old('body', '');
                $sendToApplicant = filter_var(old('send_to_applicant', '1'), FILTER_VALIDATE_BOOLEAN);
                $sendToRespondent = filter_var(old('send_to_respondent', '1'), FILTER_VALIDATE_BOOLEAN);
                $caseNumber = $case->case_number ?? '';
                $letterCollection = ($letters ?? collect())->map(function ($item) {
                return (object) $item;
                });
                $letterStatusClasses = [
                'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
                'approved' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'rejected' => 'bg-rose-50 text-rose-800 border-rose-200',
                'returned' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
                ];
                @endphp
                <section id="letters" x-cloak x-show="activeSection === 'letters'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section">
                    <div class="p-6 space-y-6">
                        <div class="space-y-3">
                            <div class="cs-section-header">
                                <span class="cs-section-icon"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" aria-hidden="true" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-6M9 17l3-3-3-3M14 7h7" />
                                    </svg></span>
                        <h3>
                                    {{ __('cases.navigation.letters') }}
                                </h3>
                                <span class="cs-count-badge">
                                    {{ $letterCollection->count() }} {{ __('cases.show.entries') }}
                                </span>
                            </div>
                            @if($letterCollection->isEmpty())
                            <div
                                class="cs-empty">
                                {{ __('cases.show.no_letters_logged') }}
                            </div>
                            @else
                            <div class="cs-table-wrap">
                                <table class="cs-table">
                                    <thead class="bg-gray-50 text-gray-600">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-medium border-b border-gray-200">
                                                {{ __('cases.show.letters_table.reference') }}</th>
                                            <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('cases.show.letters_table.subject') }}
                                            </th>
                                            <th class="px-3 py-2 text-left font-medium border-b border-gray-200">
                                                {{ __('cases.show.letters_table.template') }}</th>
                                            <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('cases.show.letters_table.status') }}
                                            </th>
                                            <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('cases.show.letters_table.date') }}
                                            </th>
                                            <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('cases.show.letters_table.author') }}
                                            </th>
                                            <th class="px-3 py-2 text-right font-medium border-b border-gray-200">{{ __('cases.show.letters_table.action') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($letterCollection as $letter)
                                        @php
                                        $status = strtolower($letter->approval_status ?? 'pending');
                                        $statusClass = $letterStatusClasses[$status] ?? 'bg-gray-50 text-gray-600
                                        border-gray-200';
                                        try {
                                        $letterDate = \App\Support\EthiopianDate::format($letter->created_at, withTime:
                                        true);
                                        } catch (\Throwable $e) {
                                        $letterDate = $letter->created_at ?? null;
                                        }
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 font-medium text-gray-900">
                                                {{ $letter->reference_number ?? '—' }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $letter->subject ?? '—' }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $letter->template_title ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2 text-gray-700">
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-semibold {{ $statusClass }}">
                                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-gray-700">{{ $letterDate ?? '—' }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $letter->author_name ?? '—' }}</td>
                                            <td class="px-3 py-2 text-right">
                                                @if($canViewLetters)
                                                <a href="{{ route('letters.show', $letter->id) }}" target="_blank"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-150">
                                                    {{ __('cases.documents.view_letter') }}
                                                </a>
                                                @else
                                                <span class="text-gray-400 text-xs">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                </section>

                {{-- Write Letter (Compose) Section --}}
                <section id="letters-compose" x-cloak x-show="activeSection === 'letters-compose'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section">
                    <div class="p-6 space-y-6">
                        <div class="cs-section-header">
                            <span class="cs-section-icon"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" aria-hidden="true" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 4h10M11 8h10M4 16h16M4 12h16M4 8h4M4 4h4" />
                                </svg></span>
                        <h3>
                                {{ __('cases.show.write_letter') }}
                            </h3>

                            <button type="button" @click="openSection('letters')"
                                class="btn btn-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                                {{ __('cases.show.back_to_list') }}
                            </button>
                        </div>

                        <div id="write-letter-panel" class="max-w-5xl mx-auto space-y-6">
                            <div class="bg-[var(--surface-strong)] border border-[var(--border)] rounded-xl shadow-xs p-6">
                                <h2 class="text-lg font-semibold text-gray-900 mb-1">
                                    {{ __('letters.form.select_template') }}</h2>
                                <p class=" text-gray-500 mb-4">{{ __('letters.description.compose') }}</p>

                                <div class="flex flex-col md:flex-row gap-3">
                                    <div class="flex-1">
                                        <label
                                            class="block  font-medium text-gray-700">{{ __('letters.form.template_label') }}</label>
                                        <select name="template_id" id="inline-template-select"
                                            class="cs-field mt-1">
                                            <option value="">{{ __('letters.form.select_placeholder') }}</option>
                                            @foreach($templates as $template)
                                            <option value="{{ $template->id }}" @selected(optional($selectedTemplate)->
                                                id === $template->id)>
                                                {{ $template->title }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" id="inline-template-load"
                                            class="btn btn-secondary">
                                            {{ __('letters.actions.load') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-[var(--surface-strong)] border border-[var(--border)] rounded-xl shadow-xs p-6">
                                <form method="POST" action="{{ route('letters.store') }}" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="template_id" id="inline-template-hidden"
                                        value="{{ optional($selectedTemplate)->id }}">

                                    @if(!$selectedTemplate)
                                    <div
                                        class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3  text-yellow-800">
                                        {{ __('letters.form.template_notice') }}
                                    </div>
                                    @endif

                                    <div class="grid md:grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block  font-medium text-gray-700">{{ __('letters.form.recipient_name') }}</label>
                                            <input type="text" name="recipient_name" value="{{ $recipientName }}"
                                                class="cs-field mt-1">
                                            @error('recipient_name')
                                            <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label
                                                class="block  font-medium text-gray-700">{{ __('letters.form.recipient_title') }}</label>
                                            <input type="text" name="recipient_title" value="{{ $recipientTitle }}"
                                                class="cs-field mt-1">
                                            @error('recipient_title')
                                            <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="grid md:grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block  font-medium text-gray-700">{{ __('letters.form.recipient_company') }}</label>
                                            <input type="text" name="recipient_company" value="{{ $recipientCompany }}"
                                                class="cs-field mt-1">
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ __('letters.form.recipient_company_hint') }}</p>
                                        </div>
                                        <div>
                                            <label
                                                class="block  font-medium text-gray-700">{{ __('letters.form.case_number') }}</label>
                                            <input type="text" name="case_number"
                                                value="{{ old('case_number', $caseNumber) }}"
                                                class="cs-field cs-field-readonly mt-1"
                                                placeholder="{{ __('letters.form.case_number_placeholder') }}" readonly>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ __('letters.form.case_number_help') }}</p>
                                            @error('case_number')
                                            <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="grid md:grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block  font-medium text-gray-700">{{ __('letters.form.cc') }}</label>
                                            <input type="text" name="cc" value="{{ $cc }}"
                                                class="cs-field mt-1"
                                                placeholder="{{ __('letters.form.cc_placeholder') }}">
                                            <p class="text-xs text-gray-500 mt-1">{{ __('letters.form.cc_hint') }}</p>
                                        </div>
                                        <div>
                                            <label
                                                class="block  font-medium text-gray-700">{{ __('letters.form.delivery_label') }}</label>
                                            <div class="mt-2 flex flex-wrap items-center gap-4">
                                                <input type="hidden" name="send_to_applicant" value="0">
                                                <label class="inline-flex items-center gap-2  text-gray-700">
                                                    <input type="checkbox" name="send_to_applicant" value="1"
                                                        class="cs-check"
                                                        @checked($sendToApplicant)>
                                                    <span>{{ __('letters.form.deliver_applicant') }}</span>
                                                </label>
                                                <input type="hidden" name="send_to_respondent" value="0">
                                                <label class="inline-flex items-center gap-2  text-gray-700">
                                                    <input type="checkbox" name="send_to_respondent" value="1"
                                                        class="cs-check"
                                                        @checked($sendToRespondent)>
                                                    <span>{{ __('letters.form.deliver_respondent') }}</span>
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">{{ __('letters.form.delivery_hint') }}
                                            </p>
                                            @error('send_to_applicant')
                                            <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="block  font-medium text-gray-700">{{ __('letters.form.subject') }}</label>
                                        <input type="text" name="subject" value="{{ $subject ?? '' }}"
                                            class="cs-field mt-1">
                                        @error('subject')
                                        <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    @if($caseNumber)
                                    @php
                                    // Find last reference with the same case number
                                    $last = \Illuminate\Support\Facades\DB::table('letters')
                                    ->where('case_number', $caseNumber)
                                    ->orderBy('id', 'desc')
                                    ->first();

                                    if ($last && preg_match('/\/(\d{2})$/', $last->reference_number, $m)) {
                                    $nextSeq = intval($m[1]) + 1;
                                    } else {
                                    $nextSeq = 1;
                                    }

                                    $seq = str_pad($nextSeq, 2, '0', STR_PAD_LEFT);

                                    $nextReference = "{$caseNumber}/{$seq}";
                                    @endphp

                                    <div>
                                        <label class="block  font-medium text-gray-700">{{ __('cases.show.reference_number_auto') }}</label>
                                        <input type="text" value="{{ $nextReference }}" readonly
                                            class="cs-field cs-field-readonly mt-1">

                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ __('cases.show.reference_number_hint') }}
                                        </p>
                                    </div>
                                    @endif


                                    <div id="inline-placeholders"
                                        class="{{ $selectedTemplate && $selectedTemplate->placeholders ? '' : 'hidden' }} rounded-lg border border-dashed border-blue-300 bg-blue-50 px-4 py-3 text-xs text-blue-800">
                                        <p class="font-semibold mb-1">{{ __('letters.form.placeholders_title') }}</p>
                                        <p>{{ __('letters.form.placeholders_help') }}</p>
                                        <p id="inline-placeholders-text" class="mt-1">
                                            @if($selectedTemplate && $selectedTemplate->placeholders)
                                            {{ implode(', ', $selectedTemplate->placeholders) }}
                                            @endif
                                        </p>
                                    </div>

                                    <div id="inline-template-summary"
                                        class="{{ $selectedTemplate ? '' : 'hidden' }} rounded-lg border border-gray-200 bg-gray-50 px-4 py-3  text-gray-600">
                                        <p class="text-xs uppercase tracking-wide text-gray-500">
                                            {{ __('letters.form.selected_template') }}</p>
                                        <p id="inline-template-title" class="font-semibold text-gray-900">
                                            {{ $selectedTemplate->title ?? '' }}</p>
                                        <p id="inline-template-category" class="text-xs">
                                            {{ $selectedTemplate->category ?? ($selectedTemplate ? __('letters.form.category_fallback') : '') }}
                                        </p>
                                        <p id="inline-template-excerpt" class="mt-1 text-xs text-gray-500">
                                            {{ $selectedTemplate ? \Illuminate\Support\Str::limit($selectedTemplate->body, 100, '...') : '' }}
                                        </p>
                                    </div>

                                    <div>
                                        <label
                                            class="block  font-medium text-gray-700">{{ __('letters.form.body') }}<span
                                                class="text-red-600 dark:text-red-400" aria-hidden="true">*</span></label>
                                        <textarea id="letter-body-editor" name="body" rows="12"
                                            class="cs-field mt-1 font-mono">{{ old('body', $body) }}</textarea>
                                        <p class="text-xs text-gray-500 mt-1">{{ __('letters.form.body_hint') }}</p>
                                        @error('body')
                                        <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="flex items-center justify-end">
                                        <button type="submit"
                                            class="btn btn-primary">
                                            {{ __('letters.actions.save_letter') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Hearings Section --}}
                @if($canViewHearings || $canCreateHearings || $canUpdateHearings || $canDeleteHearings)
                <section id="hearings" x-show="activeSection === 'hearings'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section p-5 sm:p-6 space-y-4">
                    <div class="cs-section-header">
                        <span class="cs-section-icon"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" aria-hidden="true" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg></span>
                        <h3>
                            {{ __('cases.hearings.title') }}
                        </h3>
                        <span
                            class="cs-count-badge">{{ ($hearings ?? collect())->count() }}
                            {{ __('cases.hearings.total') }}</span>
                    </div>

                    <div class="space-y-5">
                        {{-- Saved hearings table --}}
                        @php
                        $orderedHearings = ($hearings ?? collect())
                        ->sortByDesc(function($h) {
                        try {
                        return \Illuminate\Support\Carbon::parse($h->hearing_at)->timestamp;
                        } catch (\Throwable $e) {
                        return 0;
                        }
                        })
                        ->values();
                        @endphp
                        @if($orderedHearings->isEmpty())
                        <div
                            class="cs-empty">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400 mb-2"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ __('cases.hearings.no_hearings') }}
                        </div>
                        @else
                        <div class="cs-table-wrap">
                            <table class="cs-table">
                                <thead class="bg-gray-50 text-gray-600">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200 w-12">#</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('cases.show.when') }}</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">
                                            {{ __('cases.hearings.type_placeholder') }}</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">
                                            {{ __('cases.hearings.location_placeholder') }}</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">
                                            {{ __('cases.hearings.notes_placeholder') }}</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">
                                            {{ __('cases.table.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($orderedHearings as $index => $h)
                                    <tr class="hover:bg-gray-50 align-top">
                                        <td class="px-3 py-2 text-gray-500">{{ $index + 1 }}</td>
                                        <td class="px-3 py-2">
                                            @php
                                            try {
                                            $hearingDisplay = \App\Support\EthiopianDate::format($h->hearing_at,
                                            withTime: true);
                                            } catch (\Throwable $e) {
                                            $hearingDisplay = $h->hearing_at ?? '';
                                            }
                                            try {
                                            $hearingDateLocked = \Illuminate\Support\Carbon::parse($h->hearing_at)
                                            ->startOfDay()
                                            ->lte(\Illuminate\Support\Carbon::today());
                                            } catch (\Throwable $e) {
                                            $hearingDateLocked = false;
                                            }
                                            @endphp
                                            <span class="font-medium" data-hearing-at="{{ $h->hearing_at }}"
                                                data-hearing-display>
                                                {{ $hearingDisplay }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-gray-700">{{ $h->type ?: '-' }}</td>
                                        <td class="px-3 py-2 text-gray-700">{{ $h->location ?: '-' }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ $h->notes ?: '-' }}</td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-wrap gap-2">
                                                @if(!$caseLocked && !$hearingDateLocked && $canUpdateHearings)
                                                <details class="relative z-50">
                                                    <summary
                                                        class="btn btn-secondary btn-sm cursor-pointer list-none">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        {{ __('cases.general.edit') }}
                                                    </summary>
                                                    <div
                                                        class="relative z-[9999] mt-2 w-80 max-h-[75vh] overflow-y-auto overscroll-contain p-4 rounded-lg border border-gray-200 bg-white shadow-xl">
                                                        <form method="POST"
                                                            action="{{ route('cases.hearings.update',$h->id) }}"
                                                            class="space-y-3" data-hearing-edit-form>
                                                            @csrf @method('PATCH')
                                                            <div class="space-y-2">
                                                                <input id="hearing_date_edit_{{ $h->id }}" type="text"
                                                                    data-hearing-date
                                                                    value="{{ \Illuminate\Support\Carbon::parse($h->hearing_at)->format('Y-m-d') }}"
                                                                    placeholder="{{ __('cases.hearings.add_new_hearing') }}"
                                                                    class="cs-field"
                                                                    autocomplete="off">
                                                                @if(app()->getLocale() === 'am')
                                                                <x-eth-time-input id="hearing_time_edit_{{ $h->id }}" data-attr="hearing-time" value="08:00" />
                                                                @else
                                                                <input type="time" id="hearing_time_edit_{{ $h->id }}"
                                                                    data-hearing-time min="00:00" max="11:59" value=""
                                                                    class="cs-field"
                                                                    placeholder="HH:MM (AM)" required>
                                                                @endif
                                                                <input name="type" value="{{ $h->type ?? '' }}"
                                                                    placeholder="{{ __('cases.hearings.type_placeholder') }}"
                                                                    class="cs-field"
                                                                    required>
                                                                <input name="location" value="{{ $h->location ?? '' }}"
                                                                    placeholder="{{ __('cases.hearings.location_placeholder') }}"
                                                                    class="cs-field">
                                                                <input type="hidden" name="hearing_at"
                                                                    id="hearing_at_edit_{{ $h->id }}"
                                                                    data-hearing-target value="{{ $h->hearing_at }}">
                                                            </div>
                                                            <textarea name="notes" rows="2"
                                                                placeholder="{{ __('cases.hearings.notes_placeholder') }}"
                                                                class="cs-field"
                                                                required>{{ $h->notes ?? '' }}</textarea>
                                                            <div class="flex justify-end gap-2 pt-1">
                                                                <button
                                                                    class="btn btn-primary btn-sm">{{ __('cases.general.save') }}</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </details>
                                                @endif

                                                @if(!$caseLocked && !$hearingDateLocked && $canDeleteHearings)
                                                <form method="POST" action="{{ route('cases.hearings.delete',$h->id) }}"
                                                    onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('cases.hearings.remove_confirm')) }})">
                                                    @csrf @method('DELETE')
                                                    <button
                                                        class="btn btn-danger btn-sm">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        {{ __('cases.general.delete') }}
                                                    </button>
                                                </form>
                                                @endif
                                                @if($hearingDateLocked)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium border border-amber-200 bg-amber-50 text-amber-800">
                                                    {{ __('cases.hearings.locked_past') }}
                                                </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif

                        {{-- Create: calendar and form side by side on wide screens --}}
                        @if(!$caseLocked && $canCreateHearings)
                        <div class="cs-subpanel">
                            <h4 class="cs-subheading">{{ __('cases.hearings.add_new_hearing') }}</h4>
                            <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,21rem)_minmax(0,1fr)]">
                                <div class="min-w-0">
                                    <p class="mb-2 text-xs text-[var(--text-muted)]">{{ __('cases.show.hearing_calendar_hint') }}</p>
                                    <div id="hearings-calendar" style="min-height: 340px;"></div>
                                </div>
                                <form method="POST" action="{{ route('cases.hearings.store', $case->id) }}"
                                    class="space-y-3" data-hearing-create-form>
                                    @csrf
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label for="hearing_date_new" class="cs-label">{{ __('cases.show.when') }}</label>
                                            <input id="hearing_date_new" type="text" class="cs-field"
                                                placeholder="{{ __('cases.hearings.add_new_hearing') }}" required autocomplete="off">
                                        </div>
                                        <div>
                                            <label for="hearing_time_new" class="cs-label">{{ __('cases.show.time_placeholder') }}</label>
                                            @if(app()->getLocale() === 'am')
                                            <x-eth-time-input id="hearing_time_new" value="08:00" />
                                            @else
                                            <input id="hearing_time_new" type="time" min="00:00" max="11:59" class="cs-field"
                                                placeholder="{{ __('cases.show.time_placeholder') }}" required>
                                            @endif
                                        </div>
                                        <div>
                                            <label for="hearing_type_new" class="cs-label">{{ __('cases.hearings.type_placeholder') }}</label>
                                            <input id="hearing_type_new" name="type" class="cs-field" required>
                                        </div>
                                        <div>
                                            <label for="hearing_location_new" class="cs-label">{{ __('cases.hearings.location_placeholder') }}</label>
                                            <input id="hearing_location_new" name="location" class="cs-field">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="hearing_notes_new" class="cs-label">{{ __('cases.hearings.notes_placeholder') }}</label>
                                        <textarea id="hearing_notes_new" name="notes" rows="2" class="cs-field" required></textarea>
                                    </div>
                                    <input type="hidden" id="hearing_at_greg_new">
                                    <input type="hidden" name="hearing_at" id="hearing_at_new">
                                    <div class="flex justify-end">
                                        <button type="submit" class="btn btn-primary">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                            {{ __('cases.hearings.add_hearing') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                    </div>
                </section>
                @endif

                {{-- Respondent Responses Section --}}
                <section id="respondent-responses" x-show="activeSection === 'respondent-responses'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section p-5 sm:p-6 space-y-4">
                    <div class="cs-section-header">
                        <span class="cs-section-icon"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" aria-hidden="true" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 7h16M4 12h10M4 17h16M4 7l2-3h12l2 3M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7" />
                            </svg></span>
                        <h3>
                            {{ __('cases.navigation.respondent_responses') }}
                        </h3>
                        <span
                            class="cs-count-badge">{{ ($respondentResponses ?? collect())->count() }}
                            {{ __('cases.respondent_responses') }}</span>
                    </div>

                    <div class="space-y-4 max-h-[85vh] overflow-auto pr-2">
                        @forelse($respondentResponses ?? [] as $resp)
                        @php
                        $respBody = clean($resp->description ?? '', 'cases');
                        $respReviewStatus = $resp->review_status ?? 'awaiting_review';
                        $respReviewNote = $resp->review_note ?? null;
                        $respReviewBase = 'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium
                        border';
                        $respReviewClass = match ($respReviewStatus) {
                        'awaiting_review' => $respReviewBase.' bg-amber-50 text-amber-800 border-amber-200',
                        'returned' => $respReviewBase.' bg-yellow-50 text-yellow-800 border-yellow-200',
                        'rejected' => $respReviewBase.' bg-red-50 text-red-800 border-red-200',
                        default => $respReviewBase.' bg-emerald-50 text-emerald-800 border-emerald-200',
                        };
                        $respReviewLabel = match ($respReviewStatus) {
                        'awaiting_review' => __('cases.review_status.awaiting_review'),
                        'returned' => __('cases.review_status.returned'),
                        'rejected' => __('cases.review_status.rejected'),
                        default => __('cases.review_status.accepted'),
                        };
                        $respPdfInlineUrl = !empty($resp->id)
                            ? route('respondent-responses.download', ['response' => $resp->id, 'inline' => 1])
                            : null;
                        @endphp
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 shadow-xs space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2 text-gray-500">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="font-semibold text-gray-900">{{ $resp->title ?? __('cases.respondent_response') }}</span>
                                    <span class="{{ $respReviewClass }}">
                                        {{ $respReviewLabel }}
                                    </span>
                                </div>
                                <span>{{ \App\Support\EthiopianDate::smartFormat($resp->created_at, true, '') }}</span>
                            </div>
                            <div class="text-xs text-gray-600">
                                {{ __('cases.case_number') }}: {{ $resp->case_number ?? $case->case_number }}
                            </div>
                            @if(!empty($respBody))
                            <div class="cms-output  text-gray-800">
                                {!! $respBody !!}
                            </div>
                            @else
                            <div class="text-xs text-gray-500 italic">
                                {{ __('cases.no_description') }}
                            </div>
                            @endif
                            @if(!empty($respReviewNote))
                            <div class="text-xs text-gray-600">
                                <span class="font-semibold text-gray-700">{{ __('cases.reviewer_note') }}</span>
                                {{ $respReviewNote }}
                            </div>
                            @endif
                            @if(!empty($respPdfInlineUrl) || !empty($resp->pdf_embed['data']))
                            <div class="rounded-xl border border-gray-200 overflow-hidden bg-white">
                                @if(!empty($respPdfInlineUrl))
                                <iframe src="{{ $respPdfInlineUrl }}#toolbar=0&navpanes=0&scrollbar=0"
                                    loading="lazy" class="w-full" style="height:min(80vh,980px); min-height:520px;"
                                    title="{{ $resp->title ?? __('cases.respondent_response') }}">
                                </iframe>
                                @elseif(!empty($resp->pdf_embed['data']))
                                <iframe
                                    src="data:{{ $resp->pdf_embed['mime'] ?? 'application/pdf' }};base64,{{ $resp->pdf_embed['data'] }}#toolbar=0&navpanes=0&scrollbar=0"
                                    loading="lazy" class="w-full" style="height:min(80vh,980px); min-height:520px;"
                                    title="{{ $resp->title ?? __('cases.respondent_response') }}">
                                </iframe>
                                @endif
                            </div>
                            @endif
                            @if(in_array($respReviewStatus, ['awaiting_review', 'returned'], true) && $canReview)
                            <form method="POST" action="{{ route('respondent-responses.review', $resp->id) }}"
                                class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <label
                                    class="text-xs font-semibold text-gray-600">{{ __('cases.reviewer_note') }}</label>
                                <textarea name="review_note" rows="2"
                                    class="cs-field"
                                    placeholder="{{ __('cases.show.add_reason_note') }}">{{ old('review_note', $respReviewNote ?? '') }}</textarea>
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="submit" name="decision" value="accept"
                                        class="btn btn-success btn-sm">
                                        {{ __('cases.show.accept') }}
                                    </button>
                                    <button type="submit" name="decision" value="return"
                                        class="btn btn-warn btn-sm">
                                        {{ __('cases.show.return') }}
                                    </button>
                                </div>
                            </form>
                            @endif
                        </div>
                        @empty
                        <div class="rounded-2xl border border-gray-200 bg-white p-4  text-gray-500">
                            {{ __('recordes.messages.no_responses') }}
                        </div>
                        @endforelse
                    </div>
                </section>
                @if($canManageResponseReplies)
                <section id="response-of-response" x-show="activeSection === 'response-of-response'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section p-5 sm:p-6 space-y-4">
                    <div class="cs-section-header">
                        <span class="cs-section-icon"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 10h8M8 14h5M6 4h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2z" />
                            </svg></span>
                        <h3>
                            {{ __('cases.navigation.response_of_response') }}
                        </h3>
                        <span class="cs-count-badge">
                            {{ ($applicantResponseReplies ?? collect())->count() }}
                        </span>
                    </div>

                    <div class="space-y-4 max-h-[85vh] overflow-auto pr-2">
                        @forelse($applicantResponseReplies ?? [] as $reply)
                        @php
                        $replyReviewStatus = $reply->review_status ?? 'awaiting_review';
                        $replyReviewNote = $reply->review_note ?? null;
                        $replyReviewBase = 'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium border';
                        $replyReviewClass = match ($replyReviewStatus) {
                            'awaiting_review' => $replyReviewBase.' bg-amber-50 text-amber-800 border-amber-200',
                            'returned' => $replyReviewBase.' bg-yellow-50 text-yellow-800 border-yellow-200',
                            'rejected' => $replyReviewBase.' bg-red-50 text-red-800 border-red-200',
                            default => $replyReviewBase.' bg-emerald-50 text-emerald-800 border-emerald-200',
                        };
                        $replyReviewLabel = match ($replyReviewStatus) {
                            'awaiting_review' => __('cases.review_status.awaiting_review'),
                            'returned' => __('cases.review_status.returned'),
                            'rejected' => __('cases.review_status.rejected'),
                            default => __('cases.review_status.accepted'),
                        };
                        $applicantDisplayName = trim(($reply->applicant_first_name ?? '').' '.($reply->applicant_middle_name ?? '').' '.($reply->applicant_last_name ?? ''));
                        @endphp
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 shadow-xs space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2 text-gray-500">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-gray-900">{{ __('respondent.response_of_response') }}</span>
                                    <span class="{{ $replyReviewClass }}">{{ $replyReviewLabel }}</span>
                                </div>
                                <span>{{ \App\Support\EthiopianDate::smartFormat($reply->created_at, true, '') }}</span>
                            </div>
                            <div class="text-xs text-gray-600 grid gap-1 sm:grid-cols-2">
                                <span>{{ __('cases.case_number') }}: {{ $case->case_number }}</span>
                                <span>{{ __('respondent.response_number_label') }}: {{ $reply->response_number ?: __('cases.labels.not_available') }}</span>
                                <span>{{ __('cases.applicant_full_name') }}: {{ $applicantDisplayName ?: __('cases.labels.not_available') }}</span>
                            </div>
                            <div class="cms-output text-gray-800">
                                {{ $reply->description }}
                            </div>
                            @if(!empty($replyReviewNote))
                            <div class="text-xs text-gray-600">
                                <span class="font-semibold text-gray-700">{{ __('cases.reviewer_note') }}</span>
                                {{ $replyReviewNote }}
                            </div>
                            @endif
                            @if(!empty($reply->pdf_path))
                            <div class="rounded-xl border border-gray-200 overflow-hidden bg-white">
                                <iframe
                                    src="{{ route('admin.applicant-response-replies.download', ['case' => $case->id, 'response' => $reply->respondent_response_id, 'reply' => $reply->id, 'inline' => 1]) }}#toolbar=0&navpanes=0&scrollbar=0"
                                    loading="lazy" class="w-full" style="height:min(80vh,980px); min-height:520px;"
                                    title="{{ __('respondent.response_of_response') }}">
                                </iframe>
                            </div>
                            @endif
                            @if(in_array($replyReviewStatus, ['awaiting_review', 'returned'], true))
                            <form method="POST" action="{{ route('applicant-response-replies.review', $reply->id) }}" class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <label class="text-xs font-semibold text-gray-600">{{ __('cases.reviewer_note') }}</label>
                                <textarea name="review_note" rows="2"
                                    class="cs-field"
                                    placeholder="{{ __('cases.show.add_reason_note') }}">{{ old('review_note', $replyReviewNote ?? '') }}</textarea>
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="submit" name="decision" value="accept"
                                        class="btn btn-success btn-sm">
                                        {{ __('cases.show.accept') }}
                                    </button>
                                    <button type="submit" name="decision" value="return"
                                        class="btn btn-warn btn-sm">
                                        {{ __('cases.show.give_correction') }}
                                    </button>
                                </div>
                            </form>
                            @endif
                        </div>
                        @empty
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 text-gray-500">
                            {{ __('respondent.no_applicant_response_replies') }}
                        </div>
                        @endforelse
                    </div>
                </section>
                @endif

                {{-- Messages Section --}}
                <section id="messages" x-show="activeSection === 'messages'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section overflow-hidden">

                    {{-- Chat header --}}
                    <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--border)] bg-[var(--surface-soft)]">
                        <div class="flex items-center gap-3">
                            <div class="cs-section-icon">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-700 font-bold text-[var(--text)]">{{ __('cases.messages_section.title') }}</h3>
                                <p class="text-[11px] text-[var(--text-subtle)] mt-0.5">{{ __('cases.case_number') }}: {{ $case->case_number }}</p>
                            </div>
                        </div>
                        <span class="cs-count-badge">{{ ($messages ?? collect())->count() }} {{ __('cases.messages.total') }}</span>
                    </div>

                    {{-- Message thread --}}
                    <div class="px-5 py-4 space-y-4 overflow-y-auto" style="min-height:220px; max-height:480px;" id="msg-thread">
                        @forelse($messages as $m)
                        @php
                            $fromAdmin     = !is_null($m->sender_user_id);
                            $fromApplicant = !is_null($m->sender_applicant_id);
                            $who = $fromAdmin
                                ? ($m->admin_name ?: __('cases.messages.court_staff'))
                                : ($fromApplicant
                                    ? trim(($m->first_name ?? '').' '.($m->last_name ?? ''))
                                    : __('cases.messages_section.system'));
                            $initial = mb_strtoupper(mb_substr($who, 0, 1));
                        @endphp

                        <div class="flex items-end gap-2.5 {{ $fromAdmin ? 'flex-row-reverse' : '' }}">
                            {{-- Avatar --}}
                            <div class="shrink-0 h-8 w-8 rounded-full grid place-items-center text-xs font-bold shadow-xs
                                {{ $fromAdmin ? 'bg-[rgb(var(--ac))] text-white' : 'bg-[var(--surface-soft)] border border-[var(--border)] text-[var(--text-subtle)]' }}">
                                {{ $initial }}
                            </div>

                            {{-- Bubble --}}
                            <div class="max-w-[72%] space-y-1 {{ $fromAdmin ? 'items-end' : 'items-start' }} flex flex-col">
                                <div class="flex items-center gap-2 {{ $fromAdmin ? 'flex-row-reverse' : '' }}">
                                    <span class="text-[11.5px] font-semibold text-[var(--text)]">{{ $who }}</span>
                                    <span class="text-[10.5px] text-[var(--text-subtle)]">{{ \App\Support\EthiopianDate::format($m->created_at, withTime: true) }}</span>
                                </div>
                                <div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed whitespace-pre-wrap break-words shadow-xs
                                    {{ $fromAdmin
                                        ? 'bg-[rgb(var(--ac))] text-white rounded-br-xs'
                                        : 'bg-[var(--surface-soft)] border border-[var(--border)] text-[var(--text)] rounded-bl-xs' }}">{{ $m->body }}</div>
                            </div>
                        </div>

                        @empty
                        {{-- Empty state --}}
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <div class="h-14 w-14 rounded-2xl bg-[var(--surface-soft)] border border-[var(--border)] grid place-items-center mb-4">
                                <svg class="h-7 w-7 text-[var(--text-subtle)]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            </div>
                            <p class="text-sm font-medium text-[var(--text-muted)]">{{ __('cases.messages_section.no_messages') }}</p>
                            <p class="text-xs text-[var(--text-subtle)] mt-1">{{ __('cases.show.quick_access_hint') }}</p>
                        </div>
                        @endforelse
                    </div>

                    {{-- Compose area --}}
                    @if($canCreateMessage && !$caseLocked)
                    <div class="border-t border-[var(--border)] bg-[var(--surface-soft)] px-5 py-4">
                        <form method="POST" action="{{ route('cases.messages.post', $case->id) }}" x-data="{ body: '{{ old('body') }}', sending: false }" @submit="sending = true">
                            @csrf
                            <div class="flex items-end gap-3">
                                {{-- Current user avatar --}}
                                <div class="shrink-0 h-8 w-8 rounded-full bg-[rgb(var(--ac))] grid place-items-center text-xs font-bold text-white shadow-xs">
                                    {{ mb_strtoupper(mb_substr(auth()->user()->name ?? 'A', 0, 1)) }}
                                </div>
                                {{-- Input + send --}}
                                <div class="flex-1 relative">
                                    <textarea
                                        name="body"
                                        x-model="body"
                                        rows="1"
                                        @input="$el.style.height='auto'; $el.style.height=Math.min($el.scrollHeight,160)+'px'"
                                        @keydown.enter.prevent="if(!$event.shiftKey && body.trim()){ sending=true; $el.closest('form').submit(); }"
                                        class="w-full resize-none rounded-2xl border border-[var(--border)] bg-[var(--surface-strong)] text-[var(--text)] placeholder:text-[var(--text-subtle)] px-4 py-2.5 pr-12 text-sm focus:outline-hidden focus:ring-2 focus:ring-[rgb(var(--ac)/0.4)] focus:border-[rgb(var(--ac)/0.5)] transition-all"
                                        style="min-height:42px; max-height:160px; overflow-y:auto;"
                                        placeholder="{{ __('cases.messages_section.write_message_placeholder') }}">{{ old('body') }}</textarea>
                                    {{-- Send button inside input --}}
                                    <button type="submit"
                                        :disabled="!body.trim() || sending"
                                        :class="body.trim() && !sending ? 'bg-[rgb(var(--ac))] text-white hover:opacity-90' : 'bg-[var(--border)] text-[var(--text-subtle)] cursor-not-allowed'"
                                        class="absolute right-2 bottom-2 h-7 w-7 rounded-xl grid place-items-center transition-all">
                                        <svg x-show="!sending" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                        <svg x-show="sending" x-cloak class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                    </button>
                                </div>
                            </div>
                            @error('body')
                            <p class="mt-2 text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-1.5">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-[10.5px] text-[var(--text-subtle)] pl-11">{{ __('cases.messages_section.reply_to_applicant') }} &middot; Enter {{ __('cases.show.send') ?? 'to send' }}, Shift+Enter for new line</p>
                        </form>
                    </div>
                    @elseif($caseLocked)
                    <div class="border-t border-[var(--border)] px-5 py-3 flex items-center gap-2.5 bg-amber-50/60">
                        <svg class="h-4 w-4 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        <span class="text-xs text-amber-700">{{ __('cases.show.actions_locked') }}</span>
                    </div>
                    @endif

                </section>

                {{-- Uploaded Files Section --}}
                @if($canViewFiles || $canCreateFiles || $canUpdateFiles || $canDeleteFiles)
                <section id="uploaded-files" x-show="activeSection === 'uploaded-files'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section p-5 sm:p-6 space-y-4">
                    <div class="cs-section-header">
                        <span class="cs-section-icon"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" aria-hidden="true" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg></span>
                        <h3>
                            {{ __('cases.files.title') }}
                        </h3>
                        <span
                            class="cs-count-badge">{{ ($files ?? collect())->count() }}
                            {{ __('cases.files.total') }}</span>
                    </div>

                    @if($canCreateFiles)
                    <form method="POST" action="{{ route('cases.files.upload', $case->id) }}"
                        enctype="multipart/form-data"
                        class="mb-2 grid grid-cols-1 sm:grid-cols-[1fr_auto_auto] gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        @csrf
                        <input name="label" placeholder="{{ __('cases.files.label_placeholder') }}"
                            class="cs-field">
                        <input type="file" name="file" required
                            class="cs-file">
                        <button
                            class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            {{ __('cases.files.upload') }}
                        </button>
                    </form>
                    @error('file') <div class="text-red-600  mb-2 p-2 bg-red-50 rounded-lg border border-red-200">
                        {{ $message }}</div> @enderror
                    @endif

                    @if(($files ?? collect())->isEmpty())
                    <div
                        class="cs-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400 mb-2" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('cases.files.no_files') }}
                    </div>
                    @else
                    <ul class="divide-y divide-gray-200">
                        @foreach($files as $f)
                        <li
                            class="py-3 flex items-center justify-between hover:bg-gray-50 px-3 rounded-lg transition-colors duration-150">
                            <div class="">
                                <div class="font-medium text-gray-900 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ $f->label ?? basename($f->path) }}
                                </div>
                                <div class="text-xs text-gray-600 mt-1 flex items-center gap-3 flex-wrap">
                                    <span>{{ $f->mime ?? __('cases.file') }}</span>
                                    <span>• {{ number_format(($f->size ?? 0)/1024,1) }} KB</span>
                                    <span>•
                                        {{ \App\Support\EthiopianDate::format($f->created_at, withTime: true) }}</span>
                                    @php $by = $f->uploader_name ?? trim(($f->first_name ?? '').' '.($f->last_name ??
                                    '')); @endphp
                                    @if($by) <span>• {{ __('cases.files.uploaded_by') }} {{ $by }}</span> @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($canViewFiles)
                                <a href="{{ route('cases.files.download', [$case->id, $f->id]) }}"
                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-gray-50 text-xs text-gray-700 border border-gray-300 transition-colors duration-150 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ __('cases.documents.view') }}
                                </a>
                                @endif
                                @if($canDeleteFiles)
                                <form method="POST" action="{{ route('cases.files.delete', [$case->id, $f->id]) }}"
                                    onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('cases.files.remove_confirm')) }})">
                                    @csrf @method('DELETE')
                                    <button
                                        class="btn btn-danger btn-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        {{ __('cases.general.delete') }}
                                    </button>
                                </form>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </section>
                @endif
            </div>
        </div>

        <script>
        function openReviewModal(decision) {
            const existingReviewNote = {{ \Illuminate\Support\Js::from(old('note', $reviewNote ?? '')) }};
            const modal = document.getElementById('review-modal');
            document.getElementById('review-decision').value = decision;
            document.getElementById('review-note').value = existingReviewNote || '';
            const title = decision === 'return'
                ? {{ \Illuminate\Support\Js::from(__('cases.show.return_for_correction')) }}
                : {{ \Illuminate\Support\Js::from(__('cases.show.reject_case')) }};
            document.getElementById('review-modal-title').textContent = title;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('review-note').focus();
        }

        function closeReviewModal() {
            const modal = document.getElementById('review-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function submitReviewDecision(decision) {
            if (decision === 'accept') {
                document.getElementById('review-quick-decision').value = decision;
                document.getElementById('review-quick-form').submit();
                return;
            }
            openReviewModal(decision);
        }

        window.getHearingTimeHelpers = window.getHearingTimeHelpers || function() {
            if (window.hearingTimeHelpers && typeof window.hearingTimeHelpers.normalizeToAm === 'function') {
                return window.hearingTimeHelpers;
            }
            // Default Gregorian wall-clock time (08:00 = 2:00 ከሰአት በፊት, first slot).
            const defaultTime = '08:00';
            // The Ethiopian time conversion is handled entirely by the custom
            // eth-time-input picker (am locale), which writes the Gregorian
            // time straight into the field. So no extra ±6 shift is applied
            // here — toGregorianTime/toEthiopianTime are pass-throughs.
            // (Do NOT write the component tag with angle brackets in this
            // comment: Blade compiles x- tags even inside script comments.)
            const isEthiopianTime = false;
            const pad = (n) => String(n).padStart(2, '0');
            const shiftHours = (timeStr, delta) => {
                if (!timeStr) return timeStr;
                const parts = String(timeStr).split(':').map((p) => parseInt(p, 10));
                let [h, m] = parts;
                if (Number.isNaN(h)) return timeStr;
                if (Number.isNaN(m)) m = 0;
                h = ((h + delta) % 24 + 24) % 24;
                return `${pad(h)}:${pad(m)}`;
            };
            // Validate/zero-pad a "HH:MM" 24h Gregorian time. (Formerly folded
            // to 12h, but the custom picker now stores true Gregorian 08:00–16:00,
            // so PM hours must be preserved — only normalise format here.)
            const normalizeToAm = (timeStr) => {
                if (!timeStr) return defaultTime;
                const parts = String(timeStr).split(':').map((p) => parseInt(p, 10));
                let [h, m] = parts;
                if (Number.isNaN(h)) return defaultTime;
                if (Number.isNaN(m)) m = 0;
                if (h < 0) h = 0;
                if (h > 23) h = 23;
                return `${pad(h)}:${pad(m)}`;
            };
            // Convert the value typed in the (Ethiopian) picker to the Gregorian
            // wall-clock time stored in hearing_at. No-op outside the am locale.
            const toGregorianTime = (timeStr) => isEthiopianTime ? shiftHours(timeStr, 6) : timeStr;
            // Convert a stored Gregorian time back to what the picker should show.
            const toEthiopianTime = (timeStr) => isEthiopianTime ? shiftHours(timeStr, -6) : timeStr;
            // Set a time field's value AND notify the custom Ethiopian picker
            // (am locale) so its display re-syncs. No-op difference on English.
            const setTimeField = (el, val) => {
                if (!el) return;
                el.value = val;
                el.dispatchEvent(new CustomEvent('eth-time:set', { bubbles: true }));
            };
            window.hearingTimeHelpers = {
                defaultTime,
                normalizeToAm,
                setTimeField,
                isEthiopianTime,
                toGregorianTime,
                toEthiopianTime
            };
            return window.hearingTimeHelpers;
        };
        window.caseHearingDateSet = new Set({{ \Illuminate\Support\Js::from($hearingDateKeys) }});

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-hearing-create-form]');
            const dateField = document.getElementById('hearing_date_new');
            const gregHidden = document.getElementById('hearing_at_greg_new');
            const {
                defaultTime,
                normalizeToAm,
                toGregorianTime,
                toEthiopianTime,
                setTimeField
            } = window.getHearingTimeHelpers();
            const existingHearingDates = window.caseHearingDateSet || new Set();
            const duplicateDateMessage =
                {{ \Illuminate\Support\Js::from(__('cases.show.duplicate_hearing_date')) }};
            const convertToGregorian = (value) => {
                if (typeof window.hearingConvertToGregorian === 'function') {
                    return window.hearingConvertToGregorian(value);
                }
                return value;
            };
            const isDateUnavailable = (value) => {
                if (!value) return false;
                const normalized = value.split('T')[0];
                return existingHearingDates.has(normalized);
            };
            const formatGregDate = (d) => {
                if (!(d instanceof Date)) return '';
                const yyyy = d.getFullYear();
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            };

            window.hearingDateState = window.hearingDateState || {
                lastPickedDate: null,
                setFromDate(date) {
                    this.lastPickedDate = date instanceof Date ? date : null;
                    if (gregHidden) {
                        gregHidden.value = date ? formatGregDate(date) : '';
                    }
                },
                getFormatted() {
                    if (gregHidden?.value) return gregHidden.value;
                    if (this.lastPickedDate) return formatGregDate(this.lastPickedDate);
                    return '';
                }
            };

            if (form && dateField && gregHidden) {
                dateField.addEventListener('input', () => {
                    if (!dateField.value && window.hearingDateState?.setFromDate) {
                        window.hearingDateState.setFromDate(null);
                    }
                });

                form.addEventListener('submit', (e) => {
                    const timeField = document.getElementById('hearing_time_new');
                    const target = document.getElementById('hearing_at_new');
                    let dateVal = window.hearingDateState.getFormatted();
                    if (!dateVal && dateField?.value) {
                        dateVal = convertToGregorian(dateField.value) || '';
                    }
                    if (!dateVal) {
                        e.preventDefault();
                        alert({{ \Illuminate\Support\Js::from(__('cases.show.select_hearing_date')) }});
                        return;
                    }
                    if (isDateUnavailable(dateVal)) {
                        e.preventDefault();
                        alert(duplicateDateMessage);
                        return;
                    }
                    const timeVal = normalizeToAm(timeField?.value || defaultTime);
                    if (timeField && !timeField.value) {
                        setTimeField(timeField, timeVal);
                    }
                    // Picker value is Ethiopian on the am locale → store Gregorian.
                    target.value = `${dateVal}T${toGregorianTime(timeVal)}:00`;
                });
            }

            // Existing hearing inline edit forms
            const editForms = document.querySelectorAll('[data-hearing-edit-form]');
            const buildDateValue = (dateString, timeString) => {
                if (!dateString) return '';
                const t = normalizeToAm(timeString || defaultTime);
                const toGreg = (typeof window.hearingConvertToGregorian === 'function') ?
                    window.hearingConvertToGregorian :
                    (v) => v;
                const gregDate = toGreg(dateString) || dateString;
                // Picker value is Ethiopian on the am locale → store Gregorian.
                return `${gregDate}T${toGregorianTime(t)}:00`;
            };
            const extractTimeValue = (dateTimeValue) => {
                if (!dateTimeValue) return '';
                const parts = String(dateTimeValue).split('T');
                if (parts.length < 2) return '';
                return parts[1].slice(0, 5);
            };

            editForms.forEach((editForm) => {
                const editDate = editForm.querySelector('[data-hearing-date]');
                const editTime = editForm.querySelector('[data-hearing-time]');
                const editTarget = editForm.querySelector('[data-hearing-target]');
                if (!editDate || !editTarget) return;

                const syncHidden = () => {
                    if (!editDate.value) return;
                    editTarget.value = buildDateValue(editDate.value, editTime?.value);
                };

                if (editTime && !editTime.value) {
                    const existingTime = extractTimeValue(editTarget.value);
                    // Stored time is Gregorian → show Ethiopian in the picker (am locale).
                    setTimeField(editTime, toEthiopianTime(normalizeToAm(existingTime || defaultTime)));
                }

                editDate.addEventListener('input', syncHidden);
                editTime?.addEventListener('input', syncHidden);

                editForm.addEventListener('submit', (e) => {
                    if (!editDate.value) {
                        e.preventDefault();
                        alert({{ \Illuminate\Support\Js::from(__('cases.show.select_hearing_date')) }});
                        return;
                    }
                    if (editTime && !editTime.value) {
                        setTimeField(editTime, defaultTime);
                    }
                    syncHidden();
                });
            });
        });
        </script>

        @push('scripts')
        <script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
        <script>
        (function() {
            const TINY_BASE = "{{ asset('vendor/tinymce') }}";
            const categoryFallback = {{ \Illuminate\Support\Js::from(__('letters.form.category_fallback')) }};
            const inlineTemplates = {{ \Illuminate\Support\Js::from($inlineTemplatesData) }} || {};
            let pendingBody = '';

            const common = {
                base_url: TINY_BASE,
                suffix: '.min',
                license_key: 'gpl',
                branding: false,
                promotion: false,
                menubar: true,
                toolbar_mode: 'wrap',
                toolbar_sticky: true,
                plugins: 'lists link table code image advlist charmap fullscreen wordcount',
                toolbar: [
                    'undo redo |  fontfamily fontsize | bold italic underline strikethrough removeformat',
                    '| forecolor backcolor | alignleft aligncenter alignright alignjustify',
                    '| numlist bullist outdent indent  | fullscreen code'
                ].join(' '),
                forced_root_block: 'p',
                forced_root_block_attrs: {
                    style: 'text-align: justify;'
                },
                content_style: `
           body, p, div, li, td, th, blockquote { text-align: justify; text-justify: inter-word; }
          table{width:100%;border-collapse:collapse}
          td,th{border:1px solid #ddd;padding:4px}
          body{font-size:14px;line-height:1.5}
        `,
                paste_postprocess(plugin, args) {
                    const blocks = args.node.querySelectorAll('p,div,li,td,th,blockquote');
                    blocks.forEach(el => el.style.textAlign = 'justify');
                },
                resize: false,
                statusbar: true,
                setup(editor) {
                    editor.on('init', () => editor.execCommand('JustifyFull'));
                }
            };

            tinymce.init({
                ...common,
                selector: '#letter-body-editor',
                height: 800,
                min_height: 800,
                max_height: 800,
                setup(editor) {
                    common.setup(editor);
                    editor.on('init', () => {
                        if (pendingBody) {
                            editor.setContent(pendingBody);
                            pendingBody = '';
                        }
                    });
                }
            });

            const initTemplateLoader = () => {
                const templateSelect = document.getElementById('inline-template-select');
                const loadButton = document.getElementById('inline-template-load');
                const inlineForm = document.querySelector('#write-letter-panel form[action*="letters.store"]');
                const hiddenTemplate = document.getElementById('inline-template-hidden');
                const subjectInput = inlineForm?.querySelector('input[name=\"subject\"]');
                const refBlock = document.getElementById('inline-reference-block');
                const refValue = document.getElementById('inline-reference-value');
                const placeholderBlock = document.getElementById('inline-placeholders');
                const placeholderText = document.getElementById('inline-placeholders-text');
                const summaryBlock = document.getElementById('inline-template-summary');
                const summaryTitle = document.getElementById('inline-template-title');
                const summaryCategory = document.getElementById('inline-template-category');
                const summaryExcerpt = document.getElementById('inline-template-excerpt');

                const stripHtml = (html) => {
                    const div = document.createElement('div');
                    div.innerHTML = html || '';
                    return div.textContent || div.innerText || '';
                };

                const buildReference = (tpl) => {
                    if (!tpl || tpl.reference_sequence === null || tpl.reference_sequence === undefined)
                        return null;
                    const next = (parseInt(tpl.reference_sequence, 10) || 0) + 1;
                    const seq = String(next).padStart(4, '0');
                    return [tpl.subject_prefix || '', seq].filter(Boolean).join('/');
                };

                const renderMeta = (tpl) => {
                    const placeholders = Array.isArray(tpl?.placeholders) ? tpl.placeholders : [];
                    if (placeholderBlock) {
                        if (placeholders.length) {
                            placeholderText.textContent = placeholders.join(', ');
                            placeholderBlock.classList.remove('hidden');
                        } else {
                            placeholderText.textContent = '';
                            placeholderBlock.classList.add('hidden');
                        }
                    }

                    if (summaryBlock) {
                        if (tpl) {
                            summaryBlock.classList.remove('hidden');
                            if (summaryTitle) summaryTitle.textContent = tpl.title || '';
                            if (summaryCategory) summaryCategory.textContent = tpl.category ||
                                categoryFallback;
                            if (summaryExcerpt) {
                                const text = stripHtml(tpl.body || '');
                                summaryExcerpt.textContent = text.length > 120 ? text.slice(0, 120) +
                                    '...' : text;
                            }
                        } else {
                            summaryBlock.classList.add('hidden');
                        }
                    }

                    if (refBlock && refValue) {
                        const ref = buildReference(tpl);
                        if (ref) {
                            refValue.value = ref;
                            refBlock.classList.remove('hidden');
                        } else {
                            refValue.value = '';
                            refBlock.classList.add('hidden');
                        }
                    }
                };

                const toggleFields = (enabled) => {
                    document.querySelectorAll('[data-letter-field]').forEach(el => {
                        el.removeAttribute('disabled');
                    });
                };

                const clearTemplate = () => {
                    if (subjectInput) subjectInput.value = '';
                    if (hiddenTemplate) hiddenTemplate.value = '';
                    const editor = tinymce.get('letter-body-editor');
                    if (editor) {
                        editor.setContent('');
                    } else {
                        const textarea = document.getElementById('letter-body-editor');
                        if (textarea) textarea.value = '';
                    }
                    renderMeta(null);
                };

                const fillFromTemplate = (id) => {
                    const tpl = inlineTemplates?. [id] ?? inlineTemplates?. [String(id)] ?? inlineTemplates
                        ?. [Number(id)];
                    if (!tpl) {
                        clearTemplate();
                        return;
                    }

                    if (subjectInput) {
                        subjectInput.value = tpl.subject || '';
                    }

                    if (hiddenTemplate) hiddenTemplate.value = id;

                    const editor = tinymce.get('letter-body-editor');
                    if (editor) {
                        editor.setContent(tpl.body || '');
                        editor.focus();
                    } else {
                        const textarea = document.getElementById('letter-body-editor');
                        const bodyText = tpl.body || '';
                        if (textarea) textarea.value = bodyText;
                    }

                    renderMeta(tpl);
                };

                templateSelect?.addEventListener('change', (e) => {
                    if (e.target.value) {
                        fillFromTemplate(e.target.value);
                        toggleFields(true);
                    } else {
                        clearTemplate();
                        toggleFields(false);
                    }
                });

                loadButton?.addEventListener('click', (e) => {
                    e.preventDefault();
                    const id = templateSelect?.value;
                    if (!id) {
                        alert({{ \Illuminate\Support\Js::from(__('letters.form.select_placeholder')) }});
                        return;
                    }
                    fillFromTemplate(id);
                    toggleFields(true);
                });

                const ensureEditable = () => {
                    const editor = tinymce.get('letter-body-editor');
                    if (!editor) {
                        setTimeout(ensureEditable, 120);
                    }
                };
                ensureEditable();

                const bodyTextarea = document.getElementById('letter-body-editor');
                const hasBody = bodyTextarea?.value?.trim().length > 0;
                const initialTpl = templateSelect?.value;
                if (initialTpl) {
                    fillFromTemplate(initialTpl);
                    toggleFields(true);
                    if (hiddenTemplate) hiddenTemplate.value = initialTpl;
                } else {
                    toggleFields(hasBody);
                }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initTemplateLoader);
            } else {
                initTemplateLoader();
            }
        })();
        </script>
        @endpush

        @push('scripts')
        @php
        $modernCalendarJsV = $assetVersion('vendor/modern-ethiopian-calendar/js/modern-calendar.js');
        $modernDatepickerJsV = $assetVersion('vendor/modern-ethiopian-calendar/js/datepicker.js');
        @endphp
        <script src="{{ asset('vendor/modern-ethiopian-calendar/js/modern-calendar.js') }}?v={{ $modernCalendarJsV }}"></script>
        <script src="{{ asset('vendor/modern-ethiopian-calendar/js/datepicker.js') }}?v={{ $modernDatepickerJsV }}"></script>
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const hasModernPicker = (typeof ModernDatePicker !== 'undefined');
            const ethHelper = (() => {
                try {
                    return new ModernCalendar(document.createElement('div'), {
                        calendar: 'ethiopian',
                        language: 'am'
                    });
                } catch (e) {
                    return null;
                }
            })();
            const {
                defaultTime,
                normalizeToAm,
                toGregorianTime,
                toEthiopianTime,
                setTimeField
            } = window.getHearingTimeHelpers();
            const existingHearingDates = window.caseHearingDateSet || new Set();
            const toEthiopianDateString = (input) => {
                if (!ethHelper || !input) return input || '';
                const d = (input instanceof Date) ? input : new Date(input);
                if (Number.isNaN(d.getTime())) return input || '';
                const eth = ethHelper.gregorianToEthiopian(d);
                return `${eth.year}-${String(eth.month).padStart(2, '0')}-${String(eth.day).padStart(2, '0')}`;
            };
            const toGregorianDate = (ethString) => {
                if (!ethHelper || !ethString) return null;
                const parts = ethString.split('-').map((p) => parseInt(p, 10));
                if (parts.length !== 3 || parts.some((p) => Number.isNaN(p))) return null;
                const [y, m, d] = parts;
                try {
                    const jd = ethHelper.ethiopianToJD(y, m, d);
                    const g = ethHelper.jdToGregorian(jd);
                    return new Date(g.year, g.month - 1, g.day);
                } catch (e) {
                    return null;
                }
            };
            const toGregorianString = (ethString) => {
                const g = toGregorianDate(ethString);
                if (!g || Number.isNaN(g.getTime())) return '';
                const yyyy = g.getFullYear();
                const mm = String(g.getMonth() + 1).padStart(2, '0');
                const dd = String(g.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            };
            window.hearingConvertToGregorian = toGregorianString;
            const formatTime = (d) => {
                if (!(d instanceof Date)) return '';
                const hh = String(d.getHours()).padStart(2, '0');
                const mi = String(d.getMinutes()).padStart(2, '0');
                return `${hh}:${mi}`;
            };
            const toDateKey = (input) => {
                if (!input) return '';
                const d = (input instanceof Date) ? input : new Date(input);
                if (Number.isNaN(d.getTime())) return '';
                const yyyy = d.getFullYear();
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            };
            const toTimeKey = (input) => {
                if (!input) return '';
                const d = (input instanceof Date) ? input : new Date(input);
                if (Number.isNaN(d.getTime())) return '';
                return formatTime(d);
            };

            const minDate = new Date();
            minDate.setHours(0, 0, 0, 0);
            const formatDate = (d) => {
                if (!(d instanceof Date)) return '';
                const yyyy = d.getFullYear();
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            };

            const attachPicker = (input, onValidPick) => {
                if (!input || !hasModernPicker) return null;
                return new ModernDatePicker(input, {
                    calendar: 'ethiopian',
                    language: 'am',
                    theme: 'modern',
                    format: 'yyyy-mm-dd',
                    closeOnSelect: false,
                    onSelect: (date, formatted) => {
                        const picked = new Date(date);
                        picked.setHours(0, 0, 0, 0);
                        if (picked < minDate) {
                            alert({{ \Illuminate\Support\Js::from(__('cases.show.select_today_or_future')) }});
                            input.value = '';
                            onValidPick?.(null, '');
                            return;
                        }
                        const value = formatted || formatDate(picked);
                        input.value = value;
                        onValidPick?.(picked, value);
                        if (input && input.blur) input.blur();
                    }
                });
            };

            const dateInput = document.getElementById('hearing_date_new');
            const timeInput = document.getElementById('hearing_time_new');
            if (dateInput) {
                attachPicker(dateInput, (picked, value) => {
                    if (!picked || !value) {
                        window.hearingDateState?.setFromDate?.(null);
                        return;
                    }
                    const dateSet = window.caseHearingDateSet || new Set();
                    const gregorianValue = (typeof window.hearingConvertToGregorian === 'function') ?
                        window.hearingConvertToGregorian(value) :
                        value;
                    const normalized = (gregorianValue || '').split('T')[0];
                    if (normalized && dateSet.has(normalized)) {
                        alert({{ \Illuminate\Support\Js::from(__('cases.show.duplicate_hearing_date')) }});
                        window.hearingDateState?.setFromDate?.(null);
                        dateInput.value = '';
                        return;
                    }
                    if (window.hearingDateState?.setFromDate) {
                        window.hearingDateState.setFromDate(picked);
                    }
                });
            }
            if (timeInput && !timeInput.value) {
                setTimeField(timeInput, defaultTime);
            }

            // Inline edit pickers
            if (hasModernPicker) {
                document.querySelectorAll('[data-hearing-edit-form]').forEach((form) => {
                    const dateEl = form.querySelector('[data-hearing-date]');
                    const timeEl = form.querySelector('[data-hearing-time]');
                    const target = form.querySelector('[data-hearing-target]');
                    if (target && dateEl) {
                        const existing = target.value || dateEl.value;
                        const ethVal = toEthiopianDateString(existing);
                        if (ethVal) dateEl.value = ethVal;
                    }
                    if (timeEl) {
                        // Stored time is Gregorian → show Ethiopian in the picker (am locale).
                        const baseTime = target?.value ? toEthiopianTime(normalizeToAm(toTimeKey(target.value))) :
                            defaultTime;
                        if (!timeEl.value) setTimeField(timeEl, baseTime);
                    }
                    attachPicker(dateEl, (picked, value) => {
                        if (!target) return;
                        if (!picked || !value) {
                            target.value = '';
                            return;
                        }
                        const timeVal = normalizeToAm(timeEl?.value || defaultTime);
                        const gregDateStr = toGregorianString(value) || value;
                        // Picker value is Ethiopian on the am locale → store Gregorian.
                        target.value = `${gregDateStr}T${toGregorianTime(timeVal)}:00`;
                        if (timeEl && !timeEl.value) {
                            setTimeField(timeEl, timeVal);
                        }
                    });
                });
            }

            // Hearing calendar using Ethiopian calendar
            const calendarEl = document.getElementById('hearings-calendar');
            if (calendarEl && typeof ModernCalendar !== 'undefined') {
                @php
                $fcEvents = ($hearings ?? collect())
                    ->map(function ($h) {
                        $start = null;
                        try {
                            $start = \Illuminate\Support\Carbon::parse($h->hearing_at)->toIso8601String();
                        } catch (\Throwable $e) {
                            if (is_string($h->hearing_at)) {
                                $clean = trim($h->hearing_at);
                                foreach (['d-m-Y H:i:s', 'd-m-Y H:i'] as $fmt) {
                                    try {
                                        $start = \Illuminate\Support\Carbon::createFromFormat($fmt, $clean)->toIso8601String();
                                        break;
                                    } catch (\Throwable $e2) {
                                        $start = null;
                                    }
                                }
                            }
                        }
                        if (!$start) return null;
                        return [
                            'title' => trim(($h->type ?? '') . ' ' . ($h->location ?? '')),
                            'start' => $start,
                            'allDay' => false,
                            'extendedProps' => [
                                'location' => $h->location ?? null,
                                'notes' => $h->notes ?? null,
                            ],
                        ];
                    })
                    ->filter()
                    ->values()
                    ->toArray();
                @endphp
                const fcEvents = {{ \Illuminate\Support\Js::from($fcEvents) }};
                const eventMetaByDate = {};
                const eventDateSet = new Set(
                    (fcEvents || [])
                    .map((ev) => {
                        const key = toDateKey(ev.start);
                        if (key && !eventMetaByDate[key]) {
                            eventMetaByDate[key] = {
                                location: ev.extendedProps?.location || null,
                                notes: ev.extendedProps?.notes || null,
                                time: normalizeToAm(toTimeKey(ev.start)) || null,
                            };
                        }
                        return key;
                    })
                    .filter(Boolean)
                );

                const dateField = document.getElementById('hearing_date_new');
                const timeField = document.getElementById('hearing_time_new');
                const setFormFromDate = (d, opts = {}) => {
                    if (!(d instanceof Date)) return;
                    const yyyy = d.getFullYear();
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const dd = String(d.getDate()).padStart(2, '0');
                    const dateStr = `${yyyy}-${mm}-${dd}`;
                    if (existingHearingDates.has(dateStr)) {
                        alert({{ \Illuminate\Support\Js::from(__('cases.show.duplicate_hearing_date')) }});
                        if (dateField) {
                            dateField.value = '';
                        }
                        if (window.hearingDateState?.setFromDate) {
                            window.hearingDateState.setFromDate(null);
                        }
                        return;
                    }
                    if (dateField) {
                        const displayVal = toEthiopianDateString(d) || dateStr;
                        dateField.value = displayVal;
                    }
                    if (timeField && opts.setTime) {
                        // meta time is Gregorian → show Ethiopian in the picker (am locale).
                        setTimeField(timeField, toEthiopianTime(normalizeToAm(opts.setTime)));
                    } else if (timeField && !timeField.value) {
                        setTimeField(timeField, defaultTime);
                    }
                    if (window.hearingDateState?.setFromDate) {
                        window.hearingDateState.setFromDate(new Date(d));
                    }
                };

                const highlightEventDays = () => {
                    calendarEl.querySelectorAll('.day.has-event').forEach((el) => {
                        el.classList.remove('has-event');
                        el.querySelector('.event-dot')?.remove();
                    });
                    calendarEl.querySelectorAll('.day[data-date]').forEach((el) => {
                        const dateStr = el.getAttribute('data-date');
                        if (eventDateSet.has(dateStr)) {
                            el.classList.add('has-event');
                            const dot = document.createElement('span');
                            dot.className = 'event-dot';
                            el.appendChild(dot);
                        }
                    });
                };

                const calendar = new ModernCalendar(calendarEl, {
                    calendar: 'ethiopian',
                    language: 'am',
                    theme: 'modern',
                    firstDayOfWeek: 0,
                    onDateSelect: (date) => {
                        if (date instanceof Date) {
                            const dateKey = toDateKey(date);
                            const meta = eventMetaByDate[dateKey];
                            setFormFromDate(date, {
                                setTime: meta?.time || defaultTime
                            });
                            timeField?.focus();

                            if (meta) {
                                const locationInput = document.querySelector('[name="location"]');
                                if (locationInput && !locationInput.value && meta.location) {
                                    locationInput.value = meta.location;
                                }
                                const notesInput = document.querySelector('[name="notes"]');
                                if (notesInput && !notesInput.value && meta.notes) {
                                    notesInput.value = meta.notes;
                                }
                            }
                        }
                        requestAnimationFrame(highlightEventDays);
                    },
                    onMonthChange: () => {
                        requestAnimationFrame(highlightEventDays);
                    }
                });

                // Highlight existing hearings after initial render
                requestAnimationFrame(highlightEventDays);
            } else if (calendarEl) {
                calendarEl.innerHTML = `<div class=" text-red-600">${{{ \Illuminate\Support\Js::from(__('cases.show.calendar_load_failed')) }}}</div>`;
            }

            // Convert displayed hearing dates to Ethiopian calendar (UI only)
            (() => {
                if (!ethHelper) return;
                const fmt = (val) => {
                    const d = val ? new Date(val) : null;
                    if (!d || Number.isNaN(d.getTime())) return val;
                    const eth = ethHelper.gregorianToEthiopian(d);
                    const pad = (n) => String(n).padStart(2, '0');
                    const monthNames = ethHelper.getMonthNames?.() || [];
                    const monthName = monthNames[eth.month - 1] || pad(eth.month);
                    // Ethiopian clock + Amharic meridiem (mirrors EthiopianDate::formatEthiopianTime)
                    const rawHour = d.getHours();
                    let ethHour = (rawHour + 6) % 12;
                    if (ethHour === 0) ethHour = 12;
                    const meridiem = rawHour < 12 ? 'ከሰአት በፊት' : 'ከሰአት በኋላ';
                    const time = `${pad(ethHour)}፡${pad(d.getMinutes())}`;
                    return `${monthName}-${pad(eth.day)}-${eth.year} ዓ.ም ${time} ${meridiem}`;
                };
                document.querySelectorAll('[data-hearing-display]').forEach((el) => {
                    const val = el.getAttribute('data-hearing-at');
                    const out = fmt(val);
                    if (out) el.textContent = out;
                });
            })();
        });
        </script>
        @endpush

    </div>
</x-admin-layout>
