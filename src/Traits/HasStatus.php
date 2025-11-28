<?php

namespace Majeedfahad\Workflower\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Majeedfahad\Workflower\Events\TransitionApplied;
use Majeedfahad\Workflower\Models\Status;
use Exception;
use Illuminate\Support\Facades\DB;
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

    public function applyTransition(string $transition, array $meta = [], array $parameters = []): void
    {
        DB::beginTransaction();

        $transition = $this->validatedTransition($transition);

        if ($transition->hasBehaviours()) {
            $behaviourMeta = $transition->runBehaviours($this, $meta, $parameters);
            $meta = array_merge($meta, $behaviourMeta);
        }

        $this->status()->updateOrCreate(
            ['model_id' => $this->id,'model_type' => self::class],
            ['state_id' => $transition->toState->id]
        );

        event(new TransitionApplied($transition, $this, request()->user(), $meta));

        DB::commit();
    }

    private function validatedTransition(string $transition): Transition
    {
        $t = Transition::whereName($transition)->firstOr(function () use ($transition) {
            throw new Exception("Transition {$transition} not found on workflow {$this->workflow->name}");
        });

        if(!$this->status || $this->status->state->transitions()->count() === 0 || $t->doesntHaveFromState()) {
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
