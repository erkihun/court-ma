<x-admin-layout title="{{ __('cases.assign.assign_case') }}">
    @section('page_header', __('cases.assign.assign_case'))

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Case summary --}}
        <div class="ui-card lg:col-span-1 p-6">
            <h3 class="text-sm text-gray-700 mb-3 font-medium">{{ __('cases.assign.summary_title') }}</h3>
            <div class="space-y-2 text-sm">
                <div><span class="text-gray-600">{{ __('cases.assign.case_number') }}</span> <span
                        class="font-mono text-gray-900">{{ $case->case_number }}</span></div>
                <div><span class="text-gray-600">{{ __('cases.assign.title') }}</span> <span class="text-gray-900">{{ $case->title }}</span>
                </div>
                <div><span class="text-gray-600">{{ __('cases.assign.status') }}</span> <span
                        class="capitalize text-gray-900">{{ $case->status }}</span></div>
                <div><span class="text-gray-600">{{ __('cases.assign.current_assignee') }}</span>
                    @if($case->assignee_name)
                    <span class="text-gray-900">{{ $case->assignee_name }}</span>
                    <span class="text-gray-500 text-xs">({{ $case->assignee_email }})</span>
                    @else
                    <span class="text-gray-500">{{ __('cases.assign.unassigned') }}</span>
                    @endif
                </div>
                @if($case->assigned_at)
                <div><span class="text-gray-600">{{ __('cases.assign.assigned_at') }}</span>
                    <span
                        class="text-gray-900">{{ \App\Support\EthiopianDate::format($case->assigned_at, withTime: true, timeFormat: 'h:i A') }}</span>
                </div>
                @endif
            </div>
            <div class="mt-4">
                <a href="{{ route('cases.index') }}"
                    class="ui-back"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
{{ __('cases.assign.back') }}</a>
            </div>
        </div>

        {{-- Assign form --}}
        <div class="ui-card lg:col-span-2 p-6">
            <form method="POST" action="{{ route('cases.assign.update',$case->id) }}" class="space-y-4">
                @csrf @method('PATCH')

                @php
                $mode = $assignmentMode ?? 'admin';
                $canAssignLeaders = auth()->user()?->hasPermission('cases.assign.team');
                @endphp
                @if(!$canAssignLeaders)
                <div class="mb-4 rounded-md border border-yellow-300 bg-yellow-50 px-4 py-2 text-sm text-yellow-800">
                    {{ __('cases.assign.permission_warning') }}
                </div>
                @endif
                @if($mode === 'leader' && $leaderTeam)
                <div class="rounded-md border border-blue-200 bg-blue-50 px-4 py-2 text-sm text-blue-700">
                    {!! __('cases.assign.leader_context', ['team' => '<span class="font-semibold text-blue-900">'.e($leaderTeam->name).'</span>']) !!}
                </div>
                @else
                <div class="rounded-md border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-700">
                    {{ __('cases.assign.admin_context') }}
                </div>
                @endif

                <div>
                    <label class="block text-sm text-gray-700 mb-1 font-medium">{{ __('cases.assign.assign_to') }}</label>
                    <select name="assigned_user_id"
                        class="ui-select">
                        <option value="">{{ __('cases.assign.select_user') }}</option>
                        @if($mode === 'leader' && $leaderTeam)
                        <optgroup label="{{ __('cases.assign.team_members') }}">
                            @foreach($leaderTeam->users as $user)
                            <option value="{{ $user->id }}" @selected(old('assigned_user_id', $case->
                                assigned_user_id)==$user->id)>
                                {{ $user->name }} - {{ $user->email }}
                            </option>
                            @endforeach
                        </optgroup>
                        @else
                        @foreach(($teams ?? collect()) as $team)
                        @if($team->leader)
                        <optgroup label="{{ __('cases.assign.team_leader_label', ['team' => $team->name]) }}">
                            <option value="{{ $team->leader->id }}" @selected(old('assigned_user_id', $case->
                                assigned_user_id)==$team->leader->id)>
                                {{ $team->leader->name }} - {{ $team->leader->email }}
                            </option>
                        </optgroup>
                        @endif
                        @endforeach
                        @endif
                    </select>
                    @error('assigned_user_id')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Unassign toggle --}}
                @if($case->assigned_user_id)
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="unassign" value="1" class="ui-checkbox">
                    {{ __('cases.assign.unassign') }}
                </label>
                @endif

                <div class="pt-2">
                    <button class="btn btn-primary">{{ __('cases.general.save') }}</button>
                    <a href="{{ route('cases.index') }}"
                        class="ml-2 px-4 py-2 rounded-sm bg-gray-200 hover:bg-gray-300 text-gray-800">{{ __('cases.general.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
