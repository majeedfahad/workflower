<?php

namespace Majeedfahad\Workflower\Listeners;

use Majeedfahad\Workflower\Models\TransitionLog;

class TransitionListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
         TransitionLog::query()->create([
            'model_id' => $event->model->id,
            'model_type' => get_class($event->model),
            'transition_id' => $event->transition->id,
            'performer_id' => $event->performer->id ?? null,
            'performer_type' => $event->performer ? get_class($event->performer) : null,
            'meta' => $event->meta
         ]);
    }
}
