<x-admin-layout title="{{ __('Edit Case Type') }}">
    @section('page_header', __('Edit Case Type'))

    {{-- Modern Card Container --}}
    <div class="ui-card max-w-7xl p-6">
        <form method="POST" action="{{ route('case-types.update', $type->id) }}" class="space-y-6">
            @csrf @method('PATCH')

            {{-- Name Field --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Name') }} <span class="text-rose-600">*</span></label>
                <input id="name" name="name" value="{{ old('name', $type->name) }}" required
                    {{-- Updated Input Styling: rounded-lg, shadow-sm, cleaner focus ring --}}
                    class="ui-input mt-1">
                @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            {{-- Prefix Field --}}
            <div>
                <label for="prefix" class="block text-sm font-medium text-gray-700">{{ __('Prefix') }}</label>
                <input id="prefix" name="prefix" value="{{ old('prefix', $type->prefix) }}" maxlength="16"
                    class="ui-input mt-1">
                @error('prefix') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-500 mt-1">Used as the case number prefix (e.g., PREFIX/00001/YY).</p>
            </div>

            {{-- Action Buttons --}}
            <div class="pt-2 flex gap-3">
                {{-- Cancel Button (Secondary style) --}}
                <a href="{{ route('case-types.index') }}"
                    class="inline-flex items-center h-10 px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium transition shadow-xs">
                    {{ __('Cancel') }}
                </a>
                {{-- Update Button (Primary style) --}}
                <button type="submit"
                    class="btn btn-primary h-10">
                    {{ __('Update') }}
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>

