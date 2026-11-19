<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Majeedfahad\Workflower\Enums\Color;

class State extends Model
{
    protected $table = 'workflow_states';

    protected $guarded = [];

    protected $casts = [
        'color' => Color::class,
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(Transition::class, 'from_state_id')->orderBy('order');
    }

    /** Null clears it. Not saved. */
    public function setColor(Color|string|null $color): self
    {
        $this->color = is_string($color) ? Color::from($color) : $color;

        return $this;
    }
}
