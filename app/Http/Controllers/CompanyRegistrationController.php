<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyRegistrationController extends Controller
{
    public function create()
    {
        return view('auth.company-register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Personal
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'mobile' => 'required|digits:10|unique:users,mobile',
            'password' => 'required|min:8|confirmed',

            // Company
            'company_name' => 'required|string|max:255',
            'company_type' => 'nullable|string|max:255',
            'employee_count' => 'nullable|integer|min:0',
            'state' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'address' => 'nullable|string|max:500',

            // Employee Types
            'employee_types' => 'nullable|array',
            'employee_types.*' => 'nullable|string|max:100',

            // Branches
            'branches' => 'nullable|array',
            'branches.*' => 'nullable|string|max:150',
        ]);

        DB::transaction(function () use ($validated) {

            /*
            |--------------------------------------------------------------------------
            | Create Company
            |--------------------------------------------------------------------------
            */

            $company = Company::create([
                'name' => $validated['company_name'],
                'email' => $validated['email'],
                'mobile' => $validated['mobile'],
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'],
                'state' => $validated['state'],
                'country' => 'India',
                'status' => true,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create Company Admin
            |--------------------------------------------------------------------------
            */

            User::create([
                'name' => trim(
                    $validated['first_name'].' '.$validated['last_name']
                ),
                'email' => $validated['email'],
                'mobile' => $validated['mobile'],
                'password' => $validated['password'],
                'company_id' => $company->id,
                'role_title' => 'Company Admin',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create Employee Types
            |--------------------------------------------------------------------------
            */

            if (! empty($validated['employee_types'])) {

                foreach ($validated['employee_types'] as $type) {

                    $type = trim($type);

                    if ($type !== '') {
                        EmployeeType::create([
                            'company_id' => $company->id,
                            'name' => $type,
                            'status' => true,
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Create Branches
            |--------------------------------------------------------------------------
            */

            if (! empty($validated['branches'])) {

                foreach ($validated['branches'] as $branch) {

                    $branch = trim($branch);

                    if ($branch !== '') {
                        Branch::create([
                            'company_id' => $company->id,
                            'name' => $branch,
                            'city' => $validated['city'],
                            'state' => $validated['state'],
                            'country' => 'India',
                            'status' => true,
                        ]);
                    }
                }
            }
        });

        /*
        |--------------------------------------------------------------------------
        | After Registration → Login
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Registration successful. Please login with your email and password.'
            );
    }
}
