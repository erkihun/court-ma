<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

/**
 * Team/assignment visibility for staff working on a single court case.
 *
 * Staff permissions (`cases.view`, `cases.edit`, …) say *what* a user may do;
 * this class says *which* cases they may do it on:
 *
 *  - Team leaders (`cases.assign.member` without `cases.assign.team`) see cases
 *    assigned to themselves, to an active member of the team they lead, or to
 *    that team as a whole.
 *  - Plain team members (no assignment permission) see only cases assigned to
 *    them personally.
 *  - Everyone else (dispatchers with `cases.assign.team`, registrars, staff who
 *    belong to no team) is not narrowed further.
 *
 * Listing pages apply their own query filters built on teamLeaderMemberIds();
 * every action that reaches one case by ID must call authorize().
 */
final class CaseAccessScope
{
    /**
     * User IDs whose cases a team leader may work on. Empty when the user is
     * not scoped as a team leader (non-leaders and team-level dispatchers).
     *
     * @return array<int, int>
     */
    public function teamLeaderMemberIds(?Authenticatable $user): array
    {
        if (! $user instanceof User) {
            return [];
        }

        if (! $user->hasPermission('cases.assign.member') || $user->hasPermission('cases.assign.team')) {
            return [];
        }

        $leaderTeam = Team::with(['users' => fn ($query) => $query->where('status', 'active')])
            ->where('team_leader_id', $user->id)
            ->first();

        $ids = collect([$user->id]);

        if ($leaderTeam) {
            $ids = $ids->merge($leaderTeam->users->pluck('id'));
        }

        return $ids->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    /**
     * Whether the user may work on this case. $case is any row/model exposing
     * assigned_user_id, assigned_team_id and assigned_member_user_id.
     */
    public function allows(?Authenticatable $user, object $case): bool
    {
        // Only staff accounts carry team scope; applicants/respondents never pass here.
        if (! $user instanceof User) {
            return false;
        }

        $memberIds = $this->teamLeaderMemberIds($user);

        if ($memberIds !== []) {
            if (in_array((int) ($case->assigned_user_id ?? 0), $memberIds, true)) {
                return true;
            }

            $leaderTeamId = Team::where('team_leader_id', $user->id)->value('id');

            return $leaderTeamId && (int) ($case->assigned_team_id ?? 0) === (int) $leaderTeamId;
        }

        $isTeamMember = DB::table('team_user')->where('user_id', $user->id)->exists();

        if (! $isTeamMember
            || $user->hasPermission('cases.assign.member')
            || $user->hasPermission('cases.assign.team')) {
            return true;
        }

        // Older cases carry only assigned_user_id, so fall back to it.
        $assignedTo = (int) ($case->assigned_member_user_id ?? $case->assigned_user_id ?? 0);

        return $assignedTo === (int) $user->id;
    }

    public function authorize(?Authenticatable $user, object $case): void
    {
        abort_unless($this->allows($user, $case), 403, 'You do not have access to this case.');
    }

    /** Looks the case up by ID (404 when missing) and authorizes it. */
    public function authorizeCaseId(?Authenticatable $user, int $caseId): object
    {
        $case = DB::table('court_cases')
            ->select('id', 'assigned_user_id', 'assigned_team_id', 'assigned_member_user_id')
            ->where('id', $caseId)
            ->first();

        abort_if(! $case, 404, 'Case not found.');

        $this->authorize($user, $case);

        return $case;
    }
}
