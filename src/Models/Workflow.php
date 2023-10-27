<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Workflow extends Model
{
    protected $guarded = [];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(Transition::class);
    }

    public function initialTransition(): Transition
    {
        return $this->transitions()->where('start', true)->first();
    }
}
