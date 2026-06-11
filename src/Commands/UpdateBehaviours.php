<?php

namespace Majeedfahad\Workflower\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Majeedfahad\Workflower\Enums\BehaviourParamType;
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
                    'parameters' => $this->validatedParams($class::getParameters()),
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

    public function validatedParams(array $params): array
    {
        foreach ($params as $key => $param) {
            if (!isset($param['type']) || !($param['type'] instanceof BehaviourParamType)) {
                throw new \InvalidArgumentException(trans('workflower::workflower.behaviour.invalid_type', ['key' => $key]));
            }

            if (!isset($param['label'])) {
                throw new \InvalidArgumentException(trans('workflower::workflower.behaviour.missing_label', ['key' => $key]));
            }

            if(!isset($param['name'])) {
                throw new \InvalidArgumentException(trans('workflower::workflower.behaviour.missing_name', ['key' => $key]));
            }

            if(!isset($param['required'])) {
                $params[$key]['required'] = config('workflower.behaviour.validation.required_by_default', true);
            }else {
                $params[$key]['required'] = (bool) $param['required'];
            }

            if(!isset($param['rules'])) {
                $params[$key]['rules'] = match ($params[$key]['type']) {
                    BehaviourParamType::File => config('workflower.behaviour.validation.rules_by_type.file'),
                    BehaviourParamType::Text => config('workflower.behaviour.validation.rules_by_type.text'),
                    BehaviourParamType::Select => config('workflower.behaviour.validation.rules_by_type.select'),
                    BehaviourParamType::Textarea => config('workflower.behaviour.validation.rules_by_type.textarea'),
                    BehaviourParamType::Checkbox => config('workflower.behaviour.validation.rules_by_type.checkbox'),
                    BehaviourParamType::Date => config('workflower.behaviour.validation.rules_by_type.date'),
                    BehaviourParamType::Number => config('workflower.behaviour.validation.rules_by_type.number'),
                };
            }

            if(!isset($param['default'])) {
                $params[$key]['default'] = null;
            }

            $params[$key]['type'] = $param['type']->value;
        }

        return $params;
    }
}
