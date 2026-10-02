<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\Leave;
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
        View::composer('*', function ($view) {
            try {
                if (Schema::hasTable('leaves') && Schema::hasTable('activity_logs')) {
                    $headerPendingLeavesCount = Leave::where('status', 'pending')->count();
                    $headerNotifications = ActivityLog::latest()->take(5)->get();
                } else {
                    $headerPendingLeavesCount = 0;
                    $headerNotifications = collect([]);
                }

                $headerUser = Schema::hasTable('users') ? \App\Models\User::first() : null;
            } catch (\Exception $e) {
                $headerPendingLeavesCount = 0;
                $headerNotifications = collect([]);
                $headerUser = null;
            }

            $view->with([
                'headerPendingLeavesCount' => $headerPendingLeavesCount,
                'headerNotifications' => $headerNotifications,
                'headerUser' => $headerUser,
            ]);
        });
    }
}
