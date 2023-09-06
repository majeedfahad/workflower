<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Majeedfahad\Workflower\Contracts\Workflowable;

class Transition extends Model
{
    protected $table = 'transitions';

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

    public static function initiate(Workflow $workflow, string $name): self
    {
        $path = new self();
        $path->name = $name;
        $path->workflow()->associate($workflow);

        return $path;
    }

    public function from(State|string $state = null): self
    {
        if(is_null($state)) {
            return $this;
        }

        if (is_string($state)) {
            $state = $this->workflow->states()->where('name', $state)->firstOrFail();
        }

        $this->fromState()->associate($state);

        return $this;
    }

    public function to(State|string $state = null): self
    {
        if(is_null($state)) {
            return $this;
        }

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
