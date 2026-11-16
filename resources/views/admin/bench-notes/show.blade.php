@php
$safeTitle = clean($benchNote->title ?? '', 'cases');
$safeNote = clean($benchNote->note ?? '', 'default');
@endphp

<x-admin-layout title="{{ __('bench.page_header.show') }}">
    @section('page_header', __('bench.page_header.show'))

    <div class="max-w-5xl mx-auto space-y-10">
        <div>
            <a href="{{ route('bench-notes.index', ['case_id' => $benchNote->case_id]) }}"
                class="ui-back">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </a>
        </div>
        <div class="text-center space-y-2 text-sm text-gray-800">
            <div>{{ $benchNote->judgeOne?->name ?? __('bench.meta.unknown') }}</div>
            <div>{{ $benchNote->judgeTwo?->name ?? __('bench.meta.unknown') }}</div>
            <div>{{ $benchNote->judgeThree?->name ?? __('bench.meta.unknown') }}</div>
        </div>

        <div class="space-y-4">
            <h2 class="text-base font-semibold text-gray-900">Note Content</h2>
            <div class="ui-card p-4">
                @if($benchNote->note)
                {!! $safeNote !!}
                @else
                <p class="text-gray-500 italic">{{ __('bench.helpers.empty_content') }}</p>
                @endif
            </div>
        </div>

        <div class="text-center text-sm text-gray-800">
            <div class="font-medium">{{ $benchNote->judgeOne?->name ?? __('bench.meta.unknown') }}</div>
            @if(!empty($benchNote->judgeOne?->signature_url))
            <img src="{{ $benchNote->judgeOne?->signature_url }}" alt="{{ $benchNote->judgeOne?->name }}" class="mx-auto mt-2 max-h-16 w-auto">
            @else
            <div class="mt-2 text-gray-500">Judge 1 signature</div>
            @endif
        </div>
    </div>
</x-admin-layout>
