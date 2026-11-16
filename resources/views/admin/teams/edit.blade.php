{{-- resources/views/admin/teams/edit.blade.php --}}
<x-admin-layout title="{{ __('teams.page_header.edit') }}">
    @section('page_header', __('teams.page_header.edit'))

    <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ __('teams.descriptions.edit_notice') }}</p>
        <a href="{{ route('teams.index') }}"
            class="ui-back">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>

            {{ __('teams.buttons.back_to_list') }}
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="ui-card p-6">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('teams.headings.details') }}</h2>
            <form method="POST" action="{{ route('teams.update', $team) }}" class="mt-6 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('teams.labels.name') }}</label>
                    <input name="name" value="{{ old('name', $team->name) }}"
                        class="ui-input mt-1"
                        required>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('teams.labels.parent') }}</label>
                    <select name="parent_id"
                        class="ui-select mt-1">
                        <option value="">{{ __('teams.labels.none') }}</option>
                        @foreach($teams as $parent)
                        <option value="{{ $parent->id }}"
                            @selected(old('parent_id', $team->parent_id ?? '') == $parent->id)>
                            {{ $parent->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('teams.labels.description') }}</label>
                    <textarea name="description"
                        class="ui-textarea mt-1"
                        rows="4">{{ old('description', $team->description) }}</textarea>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('teams.labels.team_leader') }}</label>
                    <select name="team_leader_id"
                        class="ui-select mt-1">
                        <option value="">{{ __('teams.labels.none') }}</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}"
                            @selected(old('team_leader_id', $team->team_leader_id) == $user->id)>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">{{ __('teams.descriptions.leader_optional') }}</p>
                </div>

                <button type="submit"
                    class="btn btn-success w-full">
                    {{ __('teams.buttons.save_changes') }}
                </button>
            </form>
        </div>

        <div class="ui-card p-6">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('teams.headings.members') }}</h2>
            <p class="text-sm text-gray-500">{{ __('teams.descriptions.members_edit_notice') }}</p>
            <p class="text-xs text-gray-500">{{ __('teams.descriptions.members_leader_notice') }}</p>
            <form method="POST" action="{{ route('teams.users.update', $team) }}" class="mt-6 space-y-4">
                @csrf
                @method('PATCH')
                @php
                $selectedUsers = old('users', $team->users->pluck('id')->toArray());
                @endphp
                <div>
                    <div class="mt-1 grid gap-2 sm:grid-cols-2 max-h-60 overflow-y-auto rounded-sm border border-gray-200 bg-white p-2 text-sm">
                        @foreach($users as $user)
                        <label class="flex items-center gap-2 rounded-sm px-2 py-1 hover:bg-gray-50">
                            <input type="checkbox" name="users[]" value="{{ $user->id }}"
                                class="ui-checkbox h-4 w-4"
                                @checked(in_array($user->id, $selectedUsers))>
                            <span>{{ $user->name }} ({{ $user->email }})</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <button type="submit"
                    class="btn btn-primary w-full">
                    {{ __('teams.buttons.save_members') }}
                </button>
            </form>
        </div>
    </div>
</x-admin-layout>
