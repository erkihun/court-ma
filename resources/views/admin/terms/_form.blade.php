@csrf
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('terms.form_title_label') }}</label>
        <input type="text" name="title" value="{{ old('title', $term->title ?? '') }}"
            class="ui-input mt-1">
        @error('title')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('terms.form_body_label') }}</label>
        <textarea name="body" rows="10"
            class="ui-textarea mt-1">{{ old('body', $term->body ?? '') }}</textarea>
        @error('body')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="flex items-center space-x-2">
        <input type="checkbox" name="is_published" value="1" id="is_published"
            {{ old('is_published', $term->is_published ?? false) ? 'checked' : '' }}
            class="ui-checkbox">
        <label for="is_published" class="text-sm text-gray-700">{{ __('terms.form_published_label') }}</label>
    </div>
</div>
