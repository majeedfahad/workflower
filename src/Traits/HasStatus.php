<?php

namespace Majeedfahad\Workflower\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Majeedfahad\Workflower\Models\Status;
use Exception;
use Majeedfahad\Workflower\Models\Transition;

trait HasStatus {

    use HasWorkflow;
    use StatusChecker;

    public function status(): MorphOne
    {
        return $this->morphOne(Status::class, 'model');
    }

    public function applyTransition(string $transition): void
    {
        \DB::transaction(function () use ($transition) {
            $transition = $this->validatedTransition($transition);

            if ($transition->hasBehaviours()) {
                $transition->runBehaviours($this, $transition->name);
            }

            $this->status()->updateOrCreate(
                ['model_id' => $this->id,'model_type' => self::class],
                ['state_id' => $transition->toState->id]
            );
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
