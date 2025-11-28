<?php

namespace Majeedfahad\Workflower\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RunWorkflowUpdates extends Command
{
    protected $signature = 'workflow:update {--workflow=} {--all}';
    protected $description = 'Run all pending workflow updates';

    public function handle()
    {
        $this->line("Running all pending workflow updates...");
        $this->line('');

        if ($this->option('all') || !$this->option('workflow')) {
            $this->line("Running general updates...");
            $this->updateWokflow(app_path('Workflows/Updates'), 'General');
        }

        if ($this->option('all')) {
            $directories = File::directories(app_path('Workflows/Updates'));

            foreach ($directories as $directory) {
                $workflow = basename($directory);
                $this->line("Running {$workflow} updates...");
                $this->updateWokflow($directory, $workflow);
            }
        }else if ($this->option('workflow')) {
            $name = $this->option('workflow');
            $workflowClass = 'App\\Workflows\\'.$name;
            if (!class_exists($workflowClass)) {
                $this->error("{$workflowClass} class does not exist!");
                return;
            }

            $this->line("Running {$name} updates...");
            $directory = base_path("app/Workflows/Updates/{$name}");
            if (!File::isDirectory($directory)) {
                $this->warn("{$name}: Nothing to update.");
                return;
            }

            $this->updateWokflow($directory, $name);
        }
    }

    protected function alreadyRun($className)
    {
        return DB::table('workflow_updates')->where('class', $className)->exists();
    }

    protected function markAsRun($className)
    {
        DB::table('workflow_updates')->insert([
            'class' => $className,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function updateWokflow($updatesPath, $name)
    {
        // Get all workflow update files sorted by timestamp
        $updateFiles = collect(File::glob("{$updatesPath}/*.php"))
            ->sortBy(fn($file) => basename($file, '.php'));

        $updatesExists = false;
        foreach ($updateFiles as $file) {
            $className = basename($file, '.php');

            if ($this->alreadyRun($className)) {
                continue;
            }

            $this->warn("Running: {$className}");

            // Include the file to get the anonymous class instance and run it
            $workflow = require $file;
            $workflow->feed();

            $this->markAsRun($className);
            $this->info("Updated Successfully: {$className}");
            $updatesExists = true;
        }
        if (!$updatesExists) {
            $this->warn("{$name}: Nothing to update.");
        }

        $this->line('');
    }
}
