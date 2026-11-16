<x-admin-layout title="{{ __('case_inspections.findings.create_title') }}">
    @section('page_header', __('case_inspections.findings.create_title'))

    <div class="ui-card p-6">
        <h1 class="text-2xl font-semibold text-gray-900 mb-4">{{ __('case_inspections.findings.create_title') }}</h1>

        <form method="POST" action="{{ route('case-inspection-findings.store') }}" enctype="multipart/form-data" class="space-y-6">
            @include('admin.case-inspections.findings._form', ['finding' => null])

            <div class="flex items-center gap-3">
                <a href="{{ route('case-inspection-findings.index') }}" class="btn btn-secondary">{{ __('case_inspections.common.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('case_inspections.findings.save') }}</button>
            </div>
        </form>
    </div>
</x-admin-layout>
