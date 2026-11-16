<x-admin-layout title="{{ __('announcements.edit_heading') }}">
    @section('page_header', __('announcements.edit_heading'))

    <div class="ui-card p-6 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('announcements.edit_heading') }}</h1>
            <p class="text-sm text-gray-600">{{ __('announcements.edit_description') }}</p>
        </div>

        <form action="{{ route('announcements.update', $announcement) }}" method="POST" class="space-y-6">
            @csrf
            @method('PATCH')

            @include('admin.announcements._form', ['announcement' => $announcement])

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('announcements.index') }}" class="text-sm text-gray-600 hover:underline">
                    {{ __('announcements.cancel') }}
                </a>
                <button type="submit"
                    class="btn btn-primary">
                    {{ __('announcements.form_update') }}
                </button>
            </div>
        </form>
    </div>
    @include('admin.announcements._tinymce')
</x-admin-layout>
