{{-- resources/views/admin/teams/index.blade.php --}}
<x-admin-layout title="{{ __('teams.title') }}">
    @section('page_header', __('teams.page_header.index'))

    <div class="ui-card p-6">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ __('teams.headings.current') }}</h2>
                <p class="text-sm text-gray-600">{{ __('teams.descriptions.current') }}</p>
            </div>
            <a href="{{ route('teams.create') }}"
                class="btn btn-primary">
                {{ __('teams.buttons.add_new') }}
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="ui-table min-w-[640px] text-left">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-600">
                    <tr>
                        <th class="px-3 py-2 font-medium">{{ __('teams.labels.team') }}</th>
                        <th class="px-3 py-2 font-medium">{{ __('teams.labels.members') }}</th>
                        <th class="px-3 py-2 font-medium">{{ __('teams.labels.leader') }}</th>
                        <th class="px-3 py-2 font-medium">{{ __('teams.labels.description') }}</th>
                        <th class="px-3 py-2 text-right font-medium">{{ __('teams.labels.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($teams as $team)
                    <tr>
                        <td class="px-3 py-4 align-top">
                            <div class="font-semibold text-gray-900">{{ $team->name }}</div>
                            <p class="text-xs text-gray-500">
                                {{ $team->parent?->name ? __('teams.labels.reports_to', ['team' => $team->parent->name]) : __('teams.labels.top_level') }}
                            </p>
                        </td>
                        <td class="px-3 py-4 align-top">
                            <div class="text-sm font-semibold text-gray-900">{{ $team->users->count() }}</div>
                            <p class="text-xs text-gray-500">{{ __('teams.labels.member_count') }}</p>
                        </td>
                        <td class="px-3 py-4 align-top">
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $team->leader?->name ?? __('teams.meta.unassigned') }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $team->leader?->email ?? __('teams.meta.unknown_email') }}</p>
                        </td>
                        <td class="px-3 py-4 align-top">
                            <p class="text-sm text-gray-700">
                                {{ $team->description ? \Illuminate\Support\Str::limit($team->description, 120) : __('teams.descriptions.description_optional') }}
                            </p>
                        </td>
                        <td class="px-3 py-4 align-top">
                            <div class="flex flex-wrap justify-end gap-2">
                                <a href="{{ route('teams.show', $team) }}"
                                    class="btn btn-secondary btn-sm">
                                    {{ __('teams.buttons.view') }}
                                </a>
                                <a href="{{ route('teams.edit', $team) }}"
                                    class="btn btn-secondary btn-sm">
                                    {{ __('teams.buttons.edit') }}
                                </a>
                                @if($team->users->isNotEmpty())
                                <div class="flex flex-col items-end gap-1">
                                    <button type="button"
                                        class="btn btn-secondary btn-sm"
                                        disabled>
                                        {{ __('teams.buttons.delete') }}
                                    </button>
                                </div>
                                @else
                                <form method="POST" action="{{ route('teams.destroy', $team) }}"
                                    onsubmit="return confirm('{{ __('teams.confirmations.delete') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-secondary btn-sm">
                                        {{ __('teams.buttons.delete') }}
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-3 py-10 text-center text-sm text-gray-500">
                            {{ __('teams.empty') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
