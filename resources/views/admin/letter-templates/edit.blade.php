{{-- resources/views/letter-templates/edit.blade.php --}}
<x-admin-layout title="{{ __('letters.templates.edit') }}">
    @section('page_header', __('letters.templates.edit'))

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="ui-card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">{{ __('letters.templates.edit') }}</h2>
            <p class="text-sm text-gray-500 mb-4">{{ __('letters.templates.edit_help') }}</p>

            <form method="POST" action="{{ route('letter-templates.update', $template) }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                @include('admin.letter-templates._form', [
                    'template' => $template,
                    'categories' => $categories,
                ])
                <div class="flex items-center justify-between">
                    <a href="{{ route('letter-templates.index') }}"
                        class="ui-back"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
{{ __('letters.templates.actions.back') }}</a>
                    <button type="submit"
                        class="btn btn-primary">{{ __('letters.templates.actions.update_template') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
