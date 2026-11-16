<x-admin-layout title="{{ __('case_inspections.findings.index_title') }}">
    @section('page_header', __('case_inspections.findings.index_title'))
    @php $isAdmin = auth()->user()?->hasRole('admin') ?? false; @endphp

    <div class="ui-card p-6 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ __('case_inspections.findings.index_title') }}</h1>
                <p class="text-sm text-gray-600">{{ __('case_inspections.findings.index_description') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('case-inspection-findings.create') }}" class="btn btn-primary">{{ __('case_inspections.findings.new') }}</a>
            </div>
        </div>

        <form method="GET" class="grid md:grid-cols-3 gap-3">
            @if(!empty($caseId))
            <input type="hidden" name="case_id" value="{{ $caseId }}">
            @endif
            <select name="request_id" class="ui-select">
                <option value="">{{ __('case_inspections.findings.all_requests') }}</option>
                @foreach($requests as $req)
                <option value="{{ $req->id }}" @selected((string) $requestId === (string) $req->id)>{{ $req->case?->case_number }} - {{ $req->subject }}</option>
                @endforeach
            </select>
            <select name="severity" class="ui-select">
                <option value="">{{ __('case_inspections.findings.all_severities') }}</option>
                @foreach(['low', 'medium', 'high', 'critical'] as $k)
                <option value="{{ $k }}" @selected($severity === $k)>{{ __('case_inspections.severity.' . $k) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">{{ __('case_inspections.common.filter') }}</button>
        </form>

        <div class="overflow-x-auto">
            <table class="ui-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.findings.table_case') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.findings.table_request') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.findings.table_title') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.findings.table_date') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.findings.table_severity') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($findings as $finding)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $finding->request?->case?->case_number ?? __('case_inspections.common.no_data') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $finding->request?->subject ?? __('case_inspections.common.no_data') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ $finding->title }}
                            @if($finding->accepted_at)
                            <span class="ui-badge ui-badge-emerald ml-2 inline-flex items-center">{{ __('case_inspections.findings.accepted') }}</span>
                            @endif
                            @if($finding->attachment_path)
                            <div class="mt-1">
                                <a href="{{ route('case-inspection-findings.attachment', $finding) }}" target="_blank" rel="noopener noreferrer" class="text-xs text-blue-700 hover:text-blue-800 underline">
                                    {{ __('case_inspections.findings.labels.download_attachment') }}
                                </a>
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700" data-sort-value="{{ $finding->finding_date }}">{{ \App\Support\EthiopianDate::format($finding->finding_date) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ __('case_inspections.severity.' . $finding->severity) }}</td>
                        <td class="px-4 py-3 text-sm text-right space-x-2">
                            @if(!$finding->accepted_at)
                            <a href="{{ route('case-inspection-findings.edit', $finding) }}" class="text-amber-700 hover:text-amber-800">{{ __('case_inspections.common.edit') }}</a>
                            @endif
                            <a href="{{ route('case-inspection-findings.show', $finding) }}" class="text-blue-700 hover:text-blue-800">{{ __('case_inspections.common.view') }}</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('case_inspections.findings.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $findings->links() }}
    </div>
</x-admin-layout>
