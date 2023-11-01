<?php

namespace Majeedfahad\Workflower\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Majeedfahad\Workflower\Events\TransitionApplied;
use Majeedfahad\Workflower\Models\Status;
use Exception;
use Majeedfahad\Workflower\Models\Transition;
use Majeedfahad\Workflower\Models\TransitionLog;

trait HasStatus {

    use HasWorkflow;
    use StatusChecker;

    public function status(): MorphOne
    {
        return $this->morphOne(Status::class, 'model');
    }

    public function transitionLogs(): MorphMany
    {
        return $this->morphMany(TransitionLog::class, 'model');
    }

    public function applyTransition(string $transition, ?string $meta): void
    {
        \DB::transaction(function () use ($transition, $meta) {
            $transition = $this->validatedTransition($transition);

            if ($transition->hasBehaviours()) {
                $transition->runBehaviours($this, $transition->name);
            }

            $this->status()->updateOrCreate(
                ['model_id' => $this->id,'model_type' => self::class],
                ['state_id' => $transition->toState->id]
            );

            event(new TransitionApplied($transition, $this, request()->user(), $meta));
        });
    }

    private function validatedTransition(string $transition): Transition
    {
        if(!$this->status) {
            return $this->workflow->transitions()->where('name', $transition)->firstOr(function () use ($transition) {
                throw new Exception("Transition {$transition} not found on workflow {$this->workflow->name}");
            });
        }

        return $this
            ->status
            ->state->transitions()->where('name', $transition)->firstOr(function () use ($transition) {
            throw new Exception("Transition {$transition} not found on state {$this->status->state->name}");
        });
    }
}
