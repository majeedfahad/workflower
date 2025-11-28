<?php

namespace Majeedfahad\Workflower\Traits;

use Illuminate\Database\Eloquent\Model;
use Majeedfahad\Workflower\Models\Workflow;
use Majeedfahad\Workflower\Models\State;

trait HasWorkflow {
    public function getWorkflowAttribute()
    {
        return Workflow::with('states')
            ->whereName(self::class)
            ->when($this->workflowOwner(), function ($query) {
                $query->whereMorphedTo('owner', $this->workflowOwner());
            })
            ->when(!$this->workflowOwner(), function ($query) {
                $query->whereNull('owner_type')
                      ->whereNull('owner_id');
            })
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
    
    public function workflowOwner(): ?Model
    {
        return null;
    }
}
