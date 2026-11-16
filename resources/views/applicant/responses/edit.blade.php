@php
    $applicantName = trim((($reply->applicant_first_name ?? '') . ' ' . ($reply->applicant_middle_name ?? '') . ' ' . ($reply->applicant_last_name ?? '')));
    $respondentName = trim((($response->first_name ?? '') . ' ' . ($response->middle_name ?? '') . ' ' . ($response->last_name ?? '')));
@endphp

<x-applicant-layout :title="__('respondent.edit_response_of_response')" :breadcrumbs="[
    ['title' => __('cases.my_cases'), 'url' => route('applicant.cases.index')],
    ['title' => $case->case_number, 'url' => route('applicant.cases.show', $case->id)],
    ['title' => __('cases.respondent_response'), 'url' => route('applicant.cases.respondentResponses.show', [$case->id, $response->id])],
    ['title' => __('respondent.edit_response_of_response')],
]">
    <div class="mx-auto w-full max-w-5xl space-y-6">
        <div class="ui-card p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('respondent.response_of_response') }}</p>
            <h1 class="mt-1 text-2xl font-semibold text-slate-900">{{ __('respondent.edit_response_of_response') }}</h1>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('cases.case_number') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $case->case_number }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('respondent.response_number_label') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $response->response_number ?: __('cases.labels.not_available') }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('cases.applicant_full_name') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $applicantName ?: __('cases.labels.not_available') }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('cases.respondent_defendant') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $respondentName ?: __('cases.labels.not_available') }}</dd>
                </div>
            </dl>
        </div>

        <div class="ui-card p-6">
            <form method="POST" action="{{ route('applicant.cases.respondentResponses.replies.update', [$case->id, $response->id, $reply->id]) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700">
                        {{ __('respondent.response_of_response_description') }}
                    </label>
                    <textarea name="description" id="description" rows="8"
                        class="ui-textarea mt-2"
                        required>{{ old('description', $reply->description) }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="pdf" class="block text-sm font-semibold text-slate-700">
                        {{ __('cases.attachment') }}
                    </label>
                    <p class="mt-1 text-xs text-slate-500">{{ __('respondent.response_reply_attachment_hint') }}</p>
                    <input type="file" name="pdf" id="pdf" accept="application/pdf"
                        class="mt-2 block w-full text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:font-semibold file:text-blue-700 hover:file:bg-blue-100">
                    @error('pdf')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4">
                    <a href="{{ route('applicant.cases.respondentResponses.replies.show', [$case->id, $response->id, $reply->id]) }}"
                        class="btn btn-secondary">
                        {{ __('cases.buttons.cancel') }}
                    </a>
                    <button type="submit"
                        class="btn btn-primary">
                        {{ __('cases.buttons.update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-applicant-layout>
