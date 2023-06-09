<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class State extends Model
{
    protected $table = 'workflow_states';

    protected $guarded = [];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
}
