<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Majeedfahad\Workflower\Enums\ProgressRole;

/**
 * One circle of a progress track: a state, whose label it shows. Other states
 * may be on the step too (stepStates); its own state always is.
 */
class ProgressStep extends Model
{
    protected $table = 'progress_steps';

    protected $guarded = [];

    protected $casts = [
        'order' => 'integer',
    ];

    public function track(): BelongsTo
    {
        return $this->belongsTo(ProgressTrack::class, 'progress_track_id');
    }

    /** The state the step is, and takes its label from. */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function getLabelAttribute(): string
    {
        return $this->state?->label ?: (string) $this->state?->name;
    }

    /** Every mapping that points at the step, whatever its role. */
    public function mappings(): HasMany
    {
        return $this->hasMany(ProgressTrackState::class, 'progress_step_id');
    }

    /** The states that are this step. */
    public function stepStates(): HasMany
    {
        return $this->mappings()->where('role', ProgressRole::STEP->value);
    }

    /** The states drawn after this step, before the next one. */
    public function betweenStates(): HasMany
    {
        return $this->mappings()->where('role', ProgressRole::BETWEEN->value);
    }

    /**
     * Sets the step and saves it; what is passed as null is left as it is.
     * The state lists are synced: a state listed here leaves whatever it was
     * mapped to on the track before, and one no longer listed is unmapped.
     * The step's own state is always on it.
     *
     * @param  array<int, State|int>|null  $states  other states that are the step
     * @param  array<int, State|int>|null  $between  the states after it, before the next
     */
    public function define(
        State|int|null $state = null,
        ?int $order = null,
        ?array $states = null,
        ?array $between = null,
    ): self {
        if (! is_null($state)) {
            $this->state()->associate($state instanceof State ? $state : State::query()->findOrFail($state));
        }

        if (! is_null($order)) {
            $this->order = $order;
        } elseif (! $this->exists) {
            $this->order = (int) $this->track->steps()->max('order') + 1;
        }

        DB::transaction(function () use ($states, $between) {
            $this->save();

            // A state that becomes the step's own leaves the between list.
            $between = is_null($between) ? null : array_values(array_filter(
                $between,
                fn ($id) => (int) ($id instanceof State ? $id->getKey() : $id) !== (int) $this->state_id,
            ));

            $this->track->syncRole(ProgressRole::STEP, [
                $this->state_id,
                ...($states ?? $this->stepStates()->pluck('state_id')->all()),
            ], $this);

            if (! is_null($between)) {
                $this->track->syncRole(ProgressRole::BETWEEN, $between, $this);
            }
        });

        $this->unsetRelation('state');

        return $this;
    }
}
