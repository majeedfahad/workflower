<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowPath extends Model
{
    protected $table = 'workflow_paths';

    protected $guarded = [];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function fromState()
    {
        return $this->belongsTo(State::class, 'from_state_id');
    }

    public function toState()
    {
        return $this->belongsTo(State::class, 'to_state_id');
    }

    public static function addPath(Workflow $workflow, State $fromState, State $toState, bool $start = false, bool $end = false)
    {
        return self::create([
            'workflow_id' => $workflow->id,
            'from_state_id' => $fromState->id,
            'to_state_id' => $toState->id,
            'start' => $start,
            'end' => $end,
        ]);
    }
}
