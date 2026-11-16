<x-admin-layout title="{{ __('terms.create_heading') }}">
    <div class="ui-card max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-semibold text-gray-900 mb-4">{{ __('terms.create_heading') }}</h1>
        <form method="POST" action="{{ route('terms.store') }}" class="space-y-6">
            @include('admin.terms._form', ['term' => null])
            <div class="flex justify-end gap-3">
                <a href="{{ route('terms.index') }}" class="btn btn-secondary">
                    {{ __('terms.form_cancel') }}
                </a>
                <button class="btn btn-primary">
                    {{ __('terms.form_save') }}
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
