<?php

namespace Majeedfahad\Workflower\Commands;

use Illuminate\Console\Command;

class WorkflowFeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflow:feed {feeder} {--update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Feed the workflow engine with the latest changes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $feeder = $this->argument('feeder');

        $workflowClass = "App\\Workflows\\$feeder";

        if (!class_exists($workflowClass)) {
            $this->error("{$workflowClass} class does not exist!");
            return;
        }

        $workflow = new $workflowClass();

        $apiKey = $workflow->feed();

        if ($this->option('update')) {
            $this->call('workflow:update', ['--workflow' => $feeder]);
        }
    }
}
