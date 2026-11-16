<x-admin-layout title="{{ __('announcements.show_heading') }}">
    @section('page_header', __('announcements.show_heading'))

    <div class="ui-card p-6 space-y-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-bold text-gray-900">{{ $announcement->title }}</h1>
            <p class="text-sm text-gray-500">{{ \App\Support\EthiopianDate::smartFormat($announcement->created_at, true, '', 'h:i A', 'Y-m-d') }}</p>
        </div>

        <div class="prose max-w-none text-gray-800">
            @php
                $allowedTags = '<p><br><strong><em><u><ol><ul><li><a><span><div><h1><h2><h3><blockquote>';
                $safeContent = strip_tags($announcement->content ?? '', $allowedTags);
            @endphp
            {!! $safeContent !!}
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('announcements.index') }}"
                class="ui-back"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
{{ __('announcements.back_to_list') }}</a>
        </div>
    </div>
</x-admin-layout>
