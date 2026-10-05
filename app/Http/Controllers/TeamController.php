<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Team;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $query = Team::with(['department', 'teamLeader'])
            ->withCount('employees');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $teams = $query->orderBy('name')->get();
        $departments = Department::where('status', 'active')->orderBy('name')->get();

        return view('teams.index', compact('teams', 'departments'));
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
        $team->load(['employees.department']);

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
        $employeeCount = $team->employees()->count();

        if ($employeeCount > 0) {
            return redirect()->route('teams.index')->with('error', "Cannot delete team. It has {$employeeCount} employee(s) assigned. Please remove all employees first.");
        }

        try {
            $team->delete();
            return redirect()->route('teams.index')->with('success', 'Team deleted successfully.');
        } catch (QueryException $e) {
            return redirect()->route('teams.index')->with('error', 'Cannot delete team. It has employees assigned. Please remove all employees first.');
        }
    }

    private function validatedData(Request $request, ?Team $team = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('teams', 'name')->ignore($team?->id),
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('teams', 'code')->ignore($team?->id),
            ],
            'description' => 'nullable|string|max:500',
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
            'name.unique' => 'A team with this name already exists.',
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

    public function assignEmployee(Request $request, Team $team)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        if ($employee->team_id) {
            if ($employee->team_id === $team->id) {
                return back()->with('error', 'Employee is already a member of this team.');
            }
            $otherTeam = $employee->team;
            return back()->with('error', "Employee is already assigned to team: {$otherTeam->name}. Please remove them from that team first.");
        }

        $employee->update(['team_id' => $team->id]);

        // Redirect to edit page if coming from there
        $referer = request()->headers->get('referer');
        if ($referer && strpos($referer, '/edit') !== false) {
            return redirect()->route('teams.edit', $team->id)->with('success', 'Employee assigned to team successfully.');
        }

        return back()->with('success', 'Employee assigned to team successfully.');
    }

    public function removeEmployee(Team $team, Employee $employee)
    {
        if ($employee->team_id !== $team->id) {
            return back()->with('error', 'Employee is not a member of this team.');
        }

        if ($team->team_leader_id === $employee->id) {
            return back()->with('error', 'Cannot remove team leader. Please change the team leader first, then remove this employee.');
        }

        $employee->update(['team_id' => null]);

        return back()->with('success', 'Employee removed from team successfully.');
    }
}
