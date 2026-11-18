<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;
use Majeedfahad\Workflower\Enums\ProgressRole;

/**
 * A state mapped on a track: a step (progress_step_id is the step), between
 * (progress_step_id is the step it comes after) or hidden (no step).
 */
class ProgressTrackState extends Model
{
    protected $table = 'progress_track_states';

    protected $guarded = [];

    protected $casts = [
        'role' => ProgressRole::class,
    ];

    protected static function booted(): void
    {
        static::saving(function (self $mapping) {
            if (($mapping->role === ProgressRole::HIDDEN) !== is_null($mapping->progress_step_id)) {
                throw new InvalidArgumentException('A hidden state has no step; a step or between state needs one.');
            }
        });
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(ProgressTrack::class, 'progress_track_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(ProgressStep::class, 'progress_step_id');
    }
}
