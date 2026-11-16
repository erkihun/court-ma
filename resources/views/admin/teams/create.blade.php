{{-- resources/views/admin/teams/create.blade.php --}}
<x-admin-layout title="{{ __('teams.buttons.add_new') }}">
    @section('page_header', __('teams.page_header.create'))

    <div class="ui-card p-6">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('teams.headings.details') }}</h2>
        <p class="text-sm text-gray-600">{{ __('teams.descriptions.details') }}</p>

        <form method="POST" action="{{ route('teams.store') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-gray-700">{{ __('teams.labels.name') }}</label>
                <input name="name" value="{{ old('name') }}"
                    class="ui-input mt-1"
                    required>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">{{ __('teams.labels.parent') }}</label>
                <select name="parent_id"
                    class="ui-select mt-1">
                    <option value="">{{ __('teams.labels.none') }}</option>
                    @foreach($teams as $team)
                    <option value="{{ $team->id }}" @selected(old('parent_id') == $team->id)>{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">{{ __('teams.labels.team_leader') }}</label>
                <select name="team_leader_id"
                    class="ui-select mt-1">
                    <option value="">{{ __('teams.labels.none') }}</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected(old('team_leader_id') == $user->id)>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">{{ __('teams.descriptions.leader_optional') }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">{{ __('teams.labels.description') }}</label>
                <textarea name="description"
                    class="ui-textarea mt-1"
                    rows="4">{{ old('description') }}</textarea>
            </div>

            <button type="submit"
                class="btn btn-primary w-full">
                {{ __('teams.buttons.create') }}
            </button>
        </form>
    </div>
</x-admin-layout>
