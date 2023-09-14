<?php

namespace Majeedfahad\Workflower\Traits;

use Majeedfahad\Workflower\Models\Workflow;
use Majeedfahad\Workflower\Models\State;

trait HasWorkflow {
    public function getWorkflowAttribute()
    {
        return Workflow::with('states')
            ->whereName(self::class)
            ->where('owner_id', $this->workflowOwner())
            ->first();
    }

    public static function workflow(array $owner = [0])
    {
        return Workflow::with('states')
            ->whereName(self::class)
            ->whereIn('owner_id', $owner)
            ->get();
    }

    public static function workflowStates($workflows)
    {
        return State::whereIn('workflow_id', $workflows->pluck('id'))
            ->get();
    }
}
