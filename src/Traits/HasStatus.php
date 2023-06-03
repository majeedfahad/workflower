<?php

namespace Majeedfahad\Workflower\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Majeedfahad\Workflower\Models\Status;
use Exception;

trait HasStatus {

    use HasWorkflow;
    use StatusChecker;

    public function status(): MorphOne
    {
        return $this->morphOne(Status::class, 'model');
    }

    public function getStatusAttribute()
    {
        return $this->status->status;
    }

    public function setStatusAttribute($status)
    {
        if ($this->status) {
            $this->status->update(['status' => $status]);
        } else {
            $this->status()->create(['status' => $status]);
        }
    }
}
