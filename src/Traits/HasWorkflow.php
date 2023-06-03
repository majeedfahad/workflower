<?php

namespace Majeedfahad\Workflower\Traits;

use Majeedfahad\Workflower\Models\Workflow;
use Majeedfahad\Workflower\Models\State;

trait HasWorkflow {

    public function states()
    {
        return $this->belongsTo(State::class, 'status', 'id');
    }

    public function getWorkflowAttribute()
    {
        return Workflow::with('states')->whereName(self::class)->first();
    }

    public static function workflow()
    {
        return Workflow::with('states')->whereName(self::class)->first();
    }
}
