<?php

namespace Majeedfahad\Workflower\Providers;

use Illuminate\Support\ServiceProvider;
use Majeedfahad\Workflower\Events\TransitionApplied;
use Majeedfahad\Workflower\Listeners\TransitionListener;

class WorkflowerServiceProvider extends ServiceProvider
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
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->app['events']->listen(TransitionApplied::class, TransitionListener::class);
    }
}
