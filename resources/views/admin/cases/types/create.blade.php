<x-admin-layout title="{{ __('Add Case Type') }}">
    @section('page_header', __('Add Case Type'))

    <div class="ui-card p-6">
        <form method="POST" action="{{ route('case-types.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('Name') }} <span class="text-rose-600">*</span></label>
                <input name="name" value="{{ old('name') }}" required
                    class="ui-input mt-1">
                @error('name') <p class="text-rose-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('Prefix') }}</label>
                <input name="prefix" value="{{ old('prefix') }}" maxlength="16"
                    class="ui-input mt-1">
                @error('prefix') <p class="text-rose-600 text-sm mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-500 mt-1">Used as the case number prefix (e.g., PREFIX/00001/YY).</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('case-types.index') }}"
                    class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800">{{ __('Cancel') }}</a>
                <button class="btn btn-primary">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</x-admin-layout>

