<x-admin-layout title="{{ __('about.title') }}">
    @section('page_header', __('about.title'))

    <div class="ui-card p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $about->title }}</h1>
                <p class="text-sm text-gray-500">/{{ $about->slug }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('about.edit', $about) }}"
                    class="btn btn-secondary">
                    {{ __('about.action_edit') }}
                </a>
                <a href="{{ route('about.index') }}"
                    class="btn btn-secondary">
                    {{ __('about.form_cancel') }}
                </a>
            </div>
        </div>

        <div class="text-sm">
            @if($about->is_published)
            <span class="ui-badge ui-badge-emerald inline-flex items-center">
                {{ __('about.status_published') }}
            </span>
            @else
            <span class="ui-badge ui-badge-slate inline-flex items-center">
                {{ __('about.status_draft') }}
            </span>
            @endif
        </div>

        <div class="prose max-w-none">
            {!! nl2br(e($about->body)) !!}
        </div>
    </div>
</x-admin-layout>
