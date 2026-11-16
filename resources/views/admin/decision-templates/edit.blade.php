{{-- resources/views/admin/decision-templates/edit.blade.php --}}
<x-admin-layout title="{{ __('decision_templates.edit') }}">
    @section('page_header', __('decision_templates.edit'))

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="ui-card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">{{ __('decision_templates.edit') }}</h2>
            <p class="text-sm text-gray-500 mb-4">{{ __('decision_templates.edit_help') }}</p>

            <form method="POST" action="{{ route('decision-templates.update', $template) }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                @include('admin.decision-templates._form', ['template' => $template])
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('decision-templates.index') }}"
                        class="btn btn-secondary">{{ __('decision_templates.actions.cancel') }}</a>
                    <button type="submit"
                        class="btn btn-success">{{ __('decision_templates.actions.save_template') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
