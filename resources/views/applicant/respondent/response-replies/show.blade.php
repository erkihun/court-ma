@php
    $applicantName = trim((($reply->applicant_first_name ?? '') . ' ' . ($reply->applicant_middle_name ?? '') . ' ' . ($reply->applicant_last_name ?? '')));
@endphp

<x-applicant-layout :title="__('respondent.response_of_response')" :as-respondent-nav="true">
    <div class="w-full max-w-[1600px] mx-auto space-y-6">
        <div class="ui-card p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('respondent.response_of_response') }}</p>
                    <h1 class="mt-1 text-2xl font-semibold text-slate-900">{{ __('respondent.response_of_response') }}</h1>
                </div>
                <a href="{{ route('respondent.response-replies.index') }}"
                    class="ui-back">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>

                    {{ __('respondent.back_to_responses') }}
                </a>
            </div>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('cases.case_number') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $reply->case_number }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('respondent.response_number_label') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $reply->response_number ?: __('cases.labels.not_available') }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('cases.applicant_full_name') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $applicantName ?: __('cases.labels.not_available') }}</dd>
                </div>
            </dl>
        </div>

        <div class="ui-card p-6">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('respondent.response_of_response_description') }}</h2>
            <div class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $reply->description }}</div>
        </div>

        <div class="ui-card p-6">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('cases.attachment') }}</h2>
                <a href="{{ route('respondent.response-replies.download', $reply->id) }}"
                    class="text-sm font-semibold text-blue-700 hover:underline">
                    {{ __('respondent.download') }}
                </a>
            </div>
            <iframe
                src="{{ route('respondent.response-replies.download', [$reply->id, 'inline' => 1]) }}"
                class="mt-4 min-h-[80vh] w-full rounded-xl border border-slate-200"
                title="{{ __('respondent.response_of_response') }}">
            </iframe>
        </div>
    </div>
</x-applicant-layout>
