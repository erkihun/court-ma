{{-- resources/views/admin/teams/show.blade.php --}}
<x-admin-layout title="{{ __('teams.headings.overview') }}">
    @section('page_header', __('teams.page_header.show'))

    <div class="space-y-4">
        <div class="ui-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $team->name }}</h2>
                    <p class="text-sm text-gray-500">
                        {{ $team->parent?->name ? __('teams.labels.reports_to', ['team' => $team->parent->name]) : __('teams.labels.top_level') }}
                    </p>
                </div>
                <a href="{{ route('teams.index') }}"
                    class="ui-back">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>

                    {{ __('teams.buttons.back_to_list') }}
                </a>
            </div>
            <div class="mt-3 text-sm text-gray-600">
                {{ __('teams.labels.leader_prefix') }}
                <span class="font-semibold text-gray-900">
                    {{ $team->leader?->name ?? __('teams.meta.unassigned') }}
                </span>
            </div>
            <p class="mt-4 text-sm text-gray-700">
                {{ $team->description ?? __('teams.descriptions.description_missing') }}
            </p>
        </div>

        <div class="ui-card p-6">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">{{ __('teams.headings.members') }}</h3>
                    <p class="text-xs text-gray-500">{{ $team->users->count() }} {{ __('teams.labels.assigned') }}</p>
                </div>
                <a href="{{ route('teams.edit', $team) }}"
                    class="btn btn-secondary btn-sm">
                    {{ __('teams.buttons.manage_members') }}
                </a>
            </div>
            @if($team->users->isEmpty())
            <p class="text-sm text-gray-500">{{ __('teams.descriptions.members_empty') }}</p>
            @else
            <div class="divide-y divide-gray-100 text-sm text-gray-700">
                @foreach($team->users as $user)
                <div class="flex items-center justify-between py-2">
                    <div>
                        <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</x-admin-layout>
