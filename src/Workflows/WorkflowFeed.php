<?php

namespace Majeedfahad\Workflower\Workflows;

use Illuminate\Database\Eloquent\Model;
use Majeedfahad\Workflower\Contracts\Workflowable;
use Majeedfahad\Workflower\Models\Behaviour;
use Majeedfahad\Workflower\Models\Permission;
use Majeedfahad\Workflower\Models\Transition;
use Majeedfahad\Workflower\Models\Workflow;
use Spatie\Permission\Models\Role;

abstract class WorkflowFeed
{

    protected string $apiKey;

    protected array $grants = [];

    protected Workflowable $workflower;
    /**
     * @return Workflow
     */
    protected function workflowGenerator($name, array $states, ?Model $owner = null): Workflow
    {
        $workflow = Workflow::query()->firstOrCreate([
            'name' => $name,
            'owner_id' => $owner?->id ?? null,
            'owner_type' => $owner ? get_class($owner) : null,
        ]);

        foreach ($states as $state) {
            $workflow->states()->firstOrCreate($state);
        }

        return $workflow;
    }

    protected function generatePaths(
        Workflow|string $workflow,
        string $name,
        string $label,
        ?string $state = null,
        ?string $toState = null,
        bool $start = false,
        Behaviour|array|null $behaviour = null,
        ?Role $role = null,
        ?array $mails = null
    ): void
    {
        if(is_string($workflow)) {
            $workflow = Workflow::where('name', $workflow)->firstOrFail();
        }

        $transition = Transition::initiate($workflow, $name)
            ->setLabel($label)
            ->from($state)
            ->to($toState);

        if($start) {
            $transition->start();
        }

        $transition->save();

        if(!is_array($behaviour)) {
            $behaviour = [$behaviour];
        }

        foreach ($behaviour as $b) {
            if ($b) {
                $transition->addBehaviour($b);
            }
        }

        if($role) {
            $permission = Permission::create([
                'name' => $name,
                'guard_name' => $role->guard_name ?? 'web',
            ], $transition);
    
            $role->givePermissionTo($permission);
        }


        if ($mails) {
            if (isset($mails[$name])) {
                $workflow->owner->mails()->create([
                    'transition_name' => $name,
                    ...$mails[$name]
                ]);
            }
        }
    }

}
