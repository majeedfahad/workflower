<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class State extends Model
{
    protected $table = 'workflow_states';

    protected $guarded = [];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(Transition::class, 'from_state_id');
    }
}
