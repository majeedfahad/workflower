<?php

namespace Majeedfahad\Workflower\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->state->id,
            'name' => $this->state->name,
            'label' => $this->state->label,
            'transitions' => $this->when(
                $this->relationLoaded('state') && $this->state->relationLoaded('transitions'),
                fn () => TransitionResource::collection($this->state->transitions)
            ),
        ];
    }
}
