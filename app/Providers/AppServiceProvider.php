<?php

namespace App\Providers;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

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
        // Sends a welcome email to new users
        User::created(function ($user) {
            // Send verification email to the new user
            Mail::to($user->email)->send(new WelcomeMail($user));
        });
    }
}
