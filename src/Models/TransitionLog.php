<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TransitionLog extends Model
{
    protected $table = 'transition_logs';

    protected $guarded = [];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function transition(): BelongsTo
    {
        return $this->belongsTo(Transition::class);
    }

    public function performer(): MorphTo
    {
        return $this->morphTo();
    }
}
