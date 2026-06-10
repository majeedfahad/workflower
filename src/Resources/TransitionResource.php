<?php

namespace Majeedfahad\Workflower\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'requirements' => $this->behaviours->mapWithKeys(fn ($behaviour) => $behaviour->parameters ?? []),
        ];
    }
}
