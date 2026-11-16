@props(['announcement' => null])

<div class="space-y-6">
    <div>
        <label class="block text-sm font-semibold text-gray-700">{{ __('announcements.form_title_label') }}</label>
        <input type="text"
            name="title"
            value="{{ old('title', $announcement?->title) }}"
            class="ui-input mt-1"
            maxlength="255"
        >
        @error('title')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700">{{ __('announcements.form_content_label') }}</label>
        <textarea id="announcement-content" name="content"
            rows="8"
            class="ui-textarea mt-1"
        >{{ old('content', $announcement?->content) }}</textarea>
        @error('content')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700">{{ __('announcements.form_status_label') }}</label>
        <select name="status"
            class="ui-select mt-1">
            @foreach(['active' => __('announcements.status_active'), 'inactive' => __('announcements.status_inactive')] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $announcement?->status ?? 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

