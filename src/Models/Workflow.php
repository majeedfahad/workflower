<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    protected $guarded = [];
    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(Transition::class);
    }
}
