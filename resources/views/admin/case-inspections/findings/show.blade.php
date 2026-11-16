<x-admin-layout title="{{ __('case_inspections.findings.show_title') }}">
    @section('page_header', __('case_inspections.findings.show_title'))
    @php $isAdmin = auth()->user()?->hasRole('admin') ?? false; @endphp

    <div class="ui-card p-6 space-y-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $finding->title }}</h1>
                <p class="text-sm text-gray-600">{{ $finding->request?->case?->case_number }} - {{ $finding->request?->subject }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if(!$finding->accepted_at)
                <a href="{{ route('case-inspection-findings.edit', $finding) }}" class="btn btn-secondary">{{ __('case_inspections.common.edit') }}</a>
                <form method="POST" action="{{ route('case-inspection-findings.destroy', $finding) }}" class="inline" onsubmit="return confirm('{{ __('case_inspections.findings.confirm_delete') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('case_inspections.common.delete') }}</button>
                </form>
                @endif
                <a href="{{ route('case-inspection-findings.index') }}" class="ui-back"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
{{ __('case_inspections.common.back') }}</a>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4 text-sm">
            <div><span class="text-gray-500">{{ __('case_inspections.findings.labels.finding_date') }}:</span> <span class="text-gray-900">{{ \App\Support\EthiopianDate::format($finding->finding_date) }}</span></div>
            <div><span class="text-gray-500">{{ __('case_inspections.findings.labels.severity') }}:</span> <span class="text-gray-900">{{ __('case_inspections.severity.' . $finding->severity) }}</span></div>
            <div><span class="text-gray-500">{{ __('case_inspections.findings.labels.recorded_by') }}:</span> <span class="text-gray-900">{{ $finding->recordedBy?->name ?? __('case_inspections.common.no_data') }}</span></div>
            <div>
                <span class="text-gray-500">{{ __('case_inspections.findings.labels.attachment_pdf') }}:</span>
                <span class="text-gray-900">
                    @if($finding->attachment_path)
                    <a href="{{ route('case-inspection-findings.attachment', $finding) }}" target="_blank" rel="noopener noreferrer" class="text-blue-700 hover:text-blue-800 underline">
                        {{ $finding->attachment_original_name ?? __('case_inspections.findings.labels.download_attachment') }}
                    </a>
                    @else
                    {{ __('case_inspections.common.no_data') }}
                    @endif
                </span>
            </div>
            <div>
                <span class="text-gray-500">{{ __('case_inspections.findings.labels.accepted') }}:</span>
                <span class="text-gray-900">
                    @if($finding->accepted_at)
                    {{ __('case_inspections.findings.accepted') }} ({{ $finding->acceptedBy?->name ?? __('case_inspections.common.no_data') }})
                    @else
                    {{ __('case_inspections.findings.not_accepted') }}
                    @endif
                </span>
            </div>
        </div>

        <div>
            <h2 class="font-semibold text-gray-900 mb-2">{{ __('case_inspections.findings.labels.details') }}</h2>
            <div class="prose max-w-none bg-gray-50 border border-gray-200 rounded-lg p-4">{!! nl2br(e($finding->details)) !!}</div>
        </div>

    </div>
</x-admin-layout>
