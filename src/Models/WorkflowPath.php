<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowPath extends Model
{
    protected $table = 'workflow_paths';

    protected $guarded = [];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function fromState(): BelongsTo
    {
        return $this->belongsTo(State::class, 'from_state_id');
    }

    public function toState(): BelongsTo
    {
        return $this->belongsTo(State::class, 'to_state_id');
    }

    public static function initiate(Workflow $workflow): self
    {
        $path = new self();
        $path->workflow()->associate($workflow);

        return $path;
    }

    public function from(State|string $state): self
    {
        if (is_string($state)) {
            $state = $this->workflow->states()->where('name', $state)->firstOrFail();
        }

        $this->fromState()->associate($state);

        return $this;
    }

    public function to(State|string $state): self
    {
        if (is_string($state)) {
            $state = $this->workflow->states()->where('name', $state)->firstOrFail();
        }

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
