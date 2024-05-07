<?php

namespace App\Providers;

use App\Models\Timezone;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        LogViewer::auth(function ($request) {
            if ($request->user() && $request->user()['role_name'] == 'Admin') {
                return true;
            } else {
                return false;
            }
        });

        // View::share('allTimezones', Timezone::where('status', 'active')->get());
    }
}
