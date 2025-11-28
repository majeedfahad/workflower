<?php

namespace Majeedfahad\Workflower\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Majeedfahad\Workflower\Models\Behaviour;

class UpdateBehaviours extends Command
{
    protected $signature = 'workflow:update-behaviours';
    protected $description = 'Update existing workflow behaviours';

    protected Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    public function handle()
    {
        $directory = $this->getDirectory();

        if (! $this->filesystem->isDirectory($directory)) {
            $this->info("Workflow behaviours found!");
            DB::table('behaviours')->delete();
            return;
        }

        $files = $this->filesystem->allFiles($directory);

        $behaviour_ids = [];
        foreach ($files as $file) {
            $relativePath = $file->getRelativePath();
            if ($relativePath) {
                $relativeNamespace = str_replace('/', '\\', $relativePath);
                $subNamespace = "App\\Workflows\\Behaviours\\{$relativeNamespace}\\";
            } else {
                $subNamespace = "App\\Workflows\\Behaviours\\";
            }
            
            $class = $subNamespace . $file->getFilenameWithoutExtension();

            if (! is_subclass_of($class, Behaviour::class)) {
                throw new \Exception("Class {$class} must extend Behaviour model.");
            }

            $behaviour = $class::updateOrCreate(
                ['class' => $class],
                [
                    'name' => $class::getName(),
                    'description' => $class::getDescription(),
                    'parameters' => $class::getParameters(),
                ]
            );
            
            $behaviour_ids[] = $behaviour->id;
        }

        DB::table('behaviours')->whereNotIn('id', $behaviour_ids)->delete();

        $this->info("Workflow behaviours updated successfully!");
    }

    public function getDirectory()
    {
        return app_path('Workflows/Behaviours');
    }
}
