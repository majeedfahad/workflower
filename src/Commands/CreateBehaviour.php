<?php

namespace Majeedfahad\Workflower\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class CreateBehaviour extends Command
{
    protected $signature = 'workflow:create-behaviour {name}';
    protected $description = 'Create a new workflow behaviour';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $name = $this->argument('name');

        //check if has directory format in name
        if(Str::contains($name, '/')) {
            $parts = explode('/', $name);
            $name = array_pop($parts);
            $subDirectoryParts = $parts;
        } else {
            $subDirectoryParts = null;
        }

        $className = Str::studly($name);
        $fileName = $className . '.php';

        //check if the workflow directory exists
        $directory = $this->getDirectory();

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        // Define the file path for the behaviour
        if($subDirectoryParts) {
            $subDirectoryPath = implode('/', $subDirectoryParts);
            $directory .= '/' . $subDirectoryPath;

            // Create sub-directory if it doesn't exist
            if (! $this->files->isDirectory($directory)) {
                $this->files->makeDirectory($directory, 0755, true);
            }
        }
        $path = $directory . '/' . $fileName;

        // Check if the file already exists
        if ($this->files->exists($path)) {
            $this->error("Workflow Behaviour {$className} already exists!");
            return;
        }

        // Generate the stub content
        $stub = $this->getStubContent($className, $subDirectoryParts);

        // Create the new workflow behaviour file
        $this->files->put($path, $stub);
        $this->info("Workflow behaviour file created: {$path}");
    }

    public function getDirectory()
    {
        return app_path('Workflows/Behaviours');
    }

    protected function getStubContent(string $className, ?array $subDirectoryParts)
    {
        $subNamespace = $subDirectoryParts ? '\\' . implode('\\', $subDirectoryParts) : '';

        return <<<EOT
        <?php

        namespace App\Workflows\Behaviours$subNamespace;

        use Majeedfahad\Workflower\Contracts\Workflowable;
        use Majeedfahad\Workflower\Models\Behaviour;
        use Majeedfahad\Workflower\Models\Transition;

        class $className extends Behaviour
        {
            public function handle(Workflowable \$workflowable, Transition \$transition, array \$meta, array \$parameters): ?array
            {
                // Your behaviour logic here

                return null;
            }
        }

        EOT;
    }
}
