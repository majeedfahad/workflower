<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function behaviours(): BelongsToMany
    {
        return $this->belongsToMany(Behaviour::class);
    }

    public static function initiate(Workflow $workflow, string $name): self
    {
        $transition = new self();
        $transition->name = $name;
        $transition->workflow()->associate($workflow);

        return $transition;
    }

    public function setLabel(string $label)
    {
        $this->label = $label;

        return $this;
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

    public function addBehaviour(Behaviour $behaviour): self
    {
        $this->behaviours()->save($behaviour);

        return $this;
    }

    public function hasBehaviours(): bool
    {
        return $this->behaviours()->exists();
    }

    public function runBehaviours($model, $transition)
    {
        $this->behaviours->each(function ($behaviour) use ($model, $transition) {
            $class = $behaviour->class::getInstance();
            $class->handle($model, $transition);
        });
    }
}
