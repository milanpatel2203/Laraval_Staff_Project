<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\Leave;
use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use App\Services\LeaveApprovalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Task::class, TaskPolicy::class);

        View::composer('*', function ($view) {
            $headerPendingLeavesCount = 0;
            $headerNotifications = collect([]);
            $headerUser = auth()->user();

            try {
                if (Schema::hasTable('leaves') && Schema::hasTable('activity_logs')) {
                    if (auth()->check() && Schema::hasColumn('leaves', 'current_approver_id')) {
                        $headerPendingLeavesCount = app(LeaveApprovalService::class)
                            ->pendingAssignedCount(auth()->user());
                    } elseif (auth()->check()) {
                        $headerPendingLeavesCount = Leave::where('status', 'pending')->count();
                    }
                    $headerNotifications = ActivityLog::latest()->take(5)->get();
                }

                $headerUser = Auth::user();
            } catch (\Exception $e) {
                $headerPendingLeavesCount = 0;
                $headerNotifications = collect([]);
            }

            $view->with([
                'headerPendingLeavesCount' => $headerPendingLeavesCount,
                'headerNotifications' => $headerNotifications,
                'headerUser' => $headerUser,
            ]);
        });
    }
}