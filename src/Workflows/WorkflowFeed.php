<?php

namespace Majeedfahad\Workflower\Workflows;

use Illuminate\Database\Eloquent\Model;
use Majeedfahad\Workflower\Contracts\Workflowable;
use Majeedfahad\Workflower\Models\Behaviour;
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
    ): Transition
    {
        if(is_string($workflow)) {
            $workflow = Workflow::where('name', $workflow)->firstOrFail();
        }

        return $workflow->path(
            name: $name,
            label: $label,
            from: $state,
            to: $toState,
            start: $start,
            behaviours: array_values(array_filter(is_array($behaviour) ? $behaviour : [$behaviour])),
            roles: $role ? [$role] : null,
        );
    }

}
