<?php

namespace Majeedfahad\Workflower\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class CreateWorkflowUpdate extends Command
{
    protected $signature = 'workflow:create-update {name} {--workflow=}';
    protected $description = 'Create a new workflow update file';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        if($this->option('workflow')) {
            $workflowClass = $this->getWorkflowClass();
            if (!class_exists($workflowClass)) {
                $this->error("{$workflowClass} class does not exist!");
                return;
            }
        }

        $fileName = $this->getFileName();

        //check if the workflow directory exists
        $directory = $this->getDirectory();
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        // Define the file path for the update
        $path = $directory . '/' . $fileName;

        // Generate the stub content
        $stub = $this->getStubContent();

        // Check if the file already exists
        if ($this->files->exists($path)) {
            $this->error("Workflow update file already exists!");
            return;
        }

        // Create the new workflow update file
        $this->files->put($path, $stub);
        $this->info("Workflow update file created: {$path}");
    }

    protected function getStubContent()
    {
        $namespace = $this->getNamespace();

        return <<<EOT
        <?php

        namespace $namespace;

        use Majeedfahad\Workflower\Workflows\WorkflowFeed;
        use Illuminate\Support\Facades\DB;

        return new class extends WorkflowFeed
        {
            public function feed()
            {
                DB::beginTransaction();

                // Example transition update, modify as needed

                DB::commit();
            }
        };
        EOT;
    }

    protected function getWorkflowClass(): string
    {
        return 'App\\Workflows\\'.$this->option('workflow');
    }

    protected function getDirectory(): string
    {
        if($this->option('workflow')) {
            return base_path("app/Workflows/Updates/{$this->option('workflow')}");
        }

        return base_path("app/Workflows/Updates");
    }

    protected function getFileName(): string
    {
        $timestamp = now()->format('Y_m_d_His');
        $name = Str::snake($this->argument('name'));
        return "{$timestamp}_{$name}.php";
    }

    protected function getNamespace(): string
    {
        if($this->option('workflow')) {
            return 'App\\Workflows\\Updates\\' . $this->option('workflow');
        }

        return 'App\\Workflows\\Updates';
    }
}
