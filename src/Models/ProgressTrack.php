<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Majeedfahad\Workflower\Enums\ProgressRole;

/**
 * A step progress bar drawn from the states of one or more workflows: a
 * record's progress is where its current states (and those of the records
 * that follow it, like a contract after its project) are mapped.
 *
 * The bar has steps, in order. Each state of the linked workflows is either
 * one of the steps (several states may be the same step), between a step and
 * the next, hiding the bar (the record has no progress, like a canceled one),
 * or unmapped, and ignored.
 *
 * Without an owner the track is the shared one; an owner's own track is used
 * instead where there is one (see for()).
 */
class ProgressTrack extends Model
{
    protected $table = 'progress_tracks';

    protected $guarded = [];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function workflows(): BelongsToMany
    {
        return $this->belongsToMany(Workflow::class, 'progress_track_workflow');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProgressStep::class, 'progress_track_id')->orderBy('order')->orderBy('id');
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(ProgressTrackState::class);
    }

    /** The states that hide the bar. */
    public function hiddenStates(): HasMany
    {
        return $this->mappings()->where('role', ProgressRole::HIDDEN->value);
    }

    /** The owner's own track, or the shared one. */
    public static function for(?Model $owner): ?self
    {
        if ($owner) {
            $own = static::query()->whereMorphedTo('owner', $owner)->first();

            if ($own) {
                return $own;
            }
        }

        return static::query()->whereNull('owner_type')->first();
    }

    public static function initiate(string $name, ?Model $owner = null): self
    {
        $track = new self(['name' => $name]);
        $track->owner()->associate($owner);

        return $track;
    }

    /**
     * Sets the track and saves it; null leaves a value as it is.
     *
     * Unlinking a workflow unmaps its states.
     *
     * @param  array<int, Workflow|int>|null  $workflows  synced
     */
    public function define(?string $name = null, ?array $workflows = null): self
    {
        if (! is_null($name)) {
            $this->name = $name;
        }

        DB::transaction(function () use ($workflows) {
            $this->save();

            if (! is_null($workflows)) {
                $ids = array_map(fn ($workflow) => $workflow instanceof Workflow ? $workflow->getKey() : (int) $workflow, $workflows);

                $this->workflows()->sync($ids);

                $this->mappings()
                    ->whereNotIn('state_id', State::query()->whereIn('workflow_id', $ids)->select('id'))
                    ->delete();
            }
        });

        $this->unsetRelation('workflows');
        $this->unsetRelation('mappings');

        return $this;
    }

    /**
     * Adds a step for the state, after the others unless an order is given.
     * See ProgressStep::define() for the other states.
     */
    public function addStep(State|int $state, ?int $order = null, array $states = [], array $between = []): ProgressStep
    {
        $step = new ProgressStep;
        $step->track()->associate($this);

        return $step->define(state: $state, order: $order, states: $states, between: $between);
    }

    /**
     * Numbers the steps in the order of the ids given.
     *
     * @param  array<int, int>  $stepIds
     */
    public function reorderSteps(array $stepIds): self
    {
        DB::transaction(function () use ($stepIds) {
            foreach (array_values($stepIds) as $index => $id) {
                $this->steps()->whereKey($id)->update(['order' => $index + 1]);
            }
        });

        $this->unsetRelation('steps');

        return $this;
    }

    /**
     * The states that hide the bar, synced.
     *
     * @param  array<int, State|int>  $states
     */
    public function hide(array $states): self
    {
        $this->syncRole(ProgressRole::HIDDEN, $states);

        return $this;
    }

    /**
     * Gives the states the role (on the step, unless hidden) and takes it
     * from the states that had it there and are not listed. A listed state
     * leaves whatever it was mapped to on the track before.
     *
     * @param  array<int, State|int>  $states
     */
    public function syncRole(ProgressRole $role, array $states, ?ProgressStep $step = null): void
    {
        if (($role === ProgressRole::HIDDEN) !== is_null($step)) {
            throw new InvalidArgumentException('A hidden state has no step; a step or between state needs one.');
        }

        if ($step && (int) $step->progress_track_id !== (int) $this->getKey()) {
            throw new InvalidArgumentException("Step [{$step->label}] is not on the track.");
        }

        $ids = array_values(array_unique(array_map(fn ($state) => $state instanceof State ? $state->getKey() : (int) $state, $states)));

        $foreign = array_diff($ids, $this->availableStates()->pluck('id')->all());

        if ($foreign !== []) {
            throw new InvalidArgumentException('States ['.implode(', ', $foreign).'] are not in the track\'s workflows.');
        }

        // A step's own state stays on it.
        $owned = $this->steps()
            ->whereIn('state_id', $ids)
            ->when($role === ProgressRole::STEP && $step, fn ($query) => $query->whereKeyNot($step->getKey()))
            ->pluck('state_id')
            ->all();

        if ($owned !== []) {
            throw new InvalidArgumentException('States ['.implode(', ', $owned).'] are steps of their own.');
        }

        DB::transaction(function () use ($role, $ids, $step) {
            foreach ($ids as $id) {
                $this->mappings()->updateOrCreate(
                    ['state_id' => $id],
                    ['role' => $role, 'progress_step_id' => $step?->getKey()],
                );
            }

            $this->mappings()
                ->where('role', $role->value)
                ->where('progress_step_id', $step?->getKey())
                ->whereNotIn('state_id', $ids)
                ->delete();
        });

        $this->unsetRelation('mappings');
    }

    /**
     * The states of the linked workflows: the ones that can be mapped.
     */
    public function availableStates(): Builder
    {
        return State::query()->whereIn('workflow_id', $this->workflows()->select('workflows.id'));
    }

    /**
     * @return Collection<int, State>
     */
    public function unmappedStates(): Collection
    {
        return $this->availableStates()
            ->whereNotIn('id', $this->mappings()->select('state_id'))
            ->orderBy('workflow_id')
            ->orderBy('id')
            ->get();
    }

    /**
     * Where the records are on the bar: see progressOf(), for their current
     * states. Nulls, and records without a status, are skipped.
     *
     * @param  iterable<int, Model|null>  $records  models with a workflower status
     * @return array{steps: array<int, array{id: int, number: int, label: string, status: string}>, between: array{label: string, after: int}|null}|null
     */
    public function progress(iterable $records): ?array
    {
        $stateIds = [];

        foreach ($records as $record) {
            if ($record?->status?->state_id) {
                $stateIds[] = $record->status->state_id;
            }
        }

        return $this->progressOf($stateIds);
    }

    /**
     * Where states put the bar: the furthest of the mapped ones (a between
     * state just after its step). Null when one of them hides the bar, or
     * none is mapped; unmapped states are ignored.
     *
     * @param  array<int, int>  $stateIds
     * @return array{steps: array<int, array{id: int, number: int, label: string, status: string}>, between: array{label: string, after: int}|null}|null
     */
    public function progressOf(array $stateIds): ?array
    {
        $this->loadMissing(['steps.state', 'mappings.state']);

        $mappings = $this->mappings->keyBy('state_id');
        $positions = $this->steps->pluck('id')->flip();

        $current = null;
        $rank = null;

        foreach ($stateIds as $stateId) {
            $mapping = $mappings->get($stateId);

            if (! $mapping) {
                continue;
            }

            if ($mapping->role === ProgressRole::HIDDEN) {
                return null;
            }

            $candidate = [$positions[$mapping->progress_step_id], $mapping->role === ProgressRole::BETWEEN ? 1 : 0];

            if ($rank === null || $candidate > $rank) {
                [$current, $rank] = [$mapping, $candidate];
            }
        }

        if (! $current) {
            return null;
        }

        [$position, $between] = $rank;

        return [
            'steps' => $this->steps->values()->map(fn (ProgressStep $step, int $index) => [
                'id' => $step->id,
                'number' => $index + 1,
                'label' => $step->label,
                'status' => match (true) {
                    $index < $position, $index === $position && $between => 'done',
                    $index === $position => 'current',
                    default => 'upcoming',
                },
            ])->all(),
            'between' => $between ? [
                'label' => $current->state->label ?: $current->state->name,
                'after' => $position + 1,
            ] : null,
        ];
    }
}
