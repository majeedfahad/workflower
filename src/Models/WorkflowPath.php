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

    public static function initiate(Workflow $workflow): self
    {
        $path = new self();
        $path->workflow()->associate($workflow);

        return $path;
    }

    public function from(State $state): self
    {
        $this->fromState()->associate($state);

        return $this;
    }

    public function to(State $state): self
    {
        $this->toState()->associate($state);

        return $this;
    }

    public function start(): self
    {
        $this->start = true;

        return $this;
    }

    public function end(): self
    {
        $this->end = true;

        return $this;
    }
}
