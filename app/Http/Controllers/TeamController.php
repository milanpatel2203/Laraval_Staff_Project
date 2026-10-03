<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::with(['department', 'teamLeader'])
            ->withCount('employees')
            ->orderBy('name')
            ->get();

        return view('teams.index', compact('teams'));
    }

    public function create()
    {
        $departments = Department::where('status', 'active')->orderBy('name')->get();
        $leaders = $this->leaderOptions();

        return view('teams.create', compact('departments', 'leaders'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        $team = Team::create($validated);
        $this->syncLeaderMembership($team);

        return redirect()->route('teams.index')->with('success', 'Team created successfully.');
    }

    public function show(Team $team)
    {
        $team->load(['department', 'teamLeader', 'employees.role', 'employees.department']);

        return view('teams.show', compact('team'));
    }

    public function edit(Team $team)
    {
        $departments = Department::where('status', 'active')->orderBy('name')->get();
        $leaders = $this->leaderOptions($team->team_leader_id);

        return view('teams.edit', compact('team', 'departments', 'leaders'));
    }

    public function update(Request $request, Team $team)
    {
        $validated = $this->validatedData($request, $team);
        $team->update($validated);
        $this->syncLeaderMembership($team);

        return redirect()->route('teams.index')->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team)
    {
        if ($team->employees()->exists()) {
            $team->update(['status' => 'inactive']);

            return redirect()->route('teams.index')->with('success', 'Team has members, so it was deactivated instead of deleted.');
        }

        $team->delete();

        return redirect()->route('teams.index')->with('success', 'Team deleted successfully.');
    }

    private function validatedData(Request $request, ?Team $team = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('teams', 'code')->ignore($team?->id),
            ],
            'department_id' => 'nullable|exists:departments,id',
            'team_leader_id' => [
                'nullable',
                Rule::unique('teams', 'team_leader_id')->ignore($team?->id),
                Rule::exists('employees', 'id')->where(function ($query) use ($team) {
                    $query->where('status', 'active');
                    if ($team?->team_leader_id) {
                        $query->orWhere('id', $team->team_leader_id);
                    }
                }),
            ],
            'status' => 'required|in:active,inactive',
        ], [
            'team_leader_id.exists' => 'Please select an active employee as Team Leader.',
            'team_leader_id.unique' => 'This employee is already assigned as Team Leader of another team.',
        ]);
    }

    private function leaderOptions(?int $currentLeaderId = null)
    {
        return Employee::query()
            ->where(function ($query) use ($currentLeaderId) {
                $query->where('status', 'active');
                if ($currentLeaderId) {
                    $query->orWhere('id', $currentLeaderId);
                }
            })
            ->orderBy('first_name')
            ->get();
    }

    private function syncLeaderMembership(Team $team): void
    {
        if (! $team->team_leader_id) {
            return;
        }

        Employee::where('id', $team->team_leader_id)->update(['team_id' => $team->id]);
    }
}
