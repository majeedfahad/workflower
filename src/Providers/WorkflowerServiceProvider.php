<?php

namespace Majeedfahad\Workflower\Providers;

use Illuminate\Support\ServiceProvider;
use Majeedfahad\Workflower\Commands\CreateBehaviour;
use Majeedfahad\Workflower\Commands\CreateWorkflowUpdate;
use Majeedfahad\Workflower\Commands\UpdateBehaviours;
use Majeedfahad\Workflower\Commands\RunWorkflowUpdates;
use Majeedfahad\Workflower\Commands\WorkflowFeeder;
use Majeedfahad\Workflower\Events\TransitionApplied;
use Majeedfahad\Workflower\Listeners\TransitionListener;

class WorkflowerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->commands([
            WorkflowFeeder::class,
            CreateWorkflowUpdate::class,
            RunWorkflowUpdates::class,
            CreateBehaviour::class,
            UpdateBehaviours::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->app['events']->listen(TransitionApplied::class, TransitionListener::class);

        $this->publishes([
            __DIR__.'/../../database/migrations/create_workflows_table.php.stub'
                => database_path("migrations/".date('Y_m_d_His', time())."_create_workflows_table.php"),

            __DIR__.'/../../database/migrations/create_states_table.php.stub'
                => database_path("migrations/".date('Y_m_d_His', time() + 1)."_create_states_table.php"),

            __DIR__.'/../../database/migrations/create_statuses_table.php.stub'
                => database_path("migrations/".date('Y_m_d_His', time() + 2)."_create_statuses_table.php"),

            __DIR__.'/../../database/migrations/create_transitions_table.php.stub'
                => database_path("migrations/".date('Y_m_d_His', time() + 3)."_create_transitions_table.php"),

            __DIR__.'/../../database/migrations/create_behaviours_table.php.stub'
                => database_path("migrations/".date('Y_m_d_His', time() + 4)."_create_behaviours_table.php"),

            __DIR__.'/../../database/migrations/create_transition_logs_table.php.stub'
                => database_path("migrations/".date('Y_m_d_His', time() + 5)."_create_transition_logs_table.php"),

            __DIR__.'/../../database/migrations/add_transition_id_to_permission_table.php.stub'
                => database_path("migrations/".date('Y_m_d_His', time() + 6)."_add_transition_id_to_permission_table.php"),
        ], 'workflower-migrations');
    }
}
