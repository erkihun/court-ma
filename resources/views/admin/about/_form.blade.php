@csrf
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('about.form_title_label') }}</label>
        <input type="text" name="title" value="{{ old('title', $about->title ?? '') }}"
            class="ui-input mt-1">
        @error('title')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('about.form_slug_label') }}</label>
        <input type="text" name="slug" value="{{ old('slug', $about->slug ?? '') }}"
            class="ui-input mt-1">
        @error('slug')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('about.form_body_label') }}</label>
        <textarea id="about-body" name="body" rows="10"
            class="ui-textarea mt-1">{{ old('body', $about->body ?? '') }}</textarea>
        @error('body')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="flex items-center space-x-2">
        <input type="checkbox" name="is_published" value="1" id="is_published"
            {{ old('is_published', $about->is_published ?? false) ? 'checked' : '' }}
            class="ui-checkbox">
        <label for="is_published" class="text-sm text-gray-700">{{ __('about.form_published_label') }}</label>
    </div>
</div>
