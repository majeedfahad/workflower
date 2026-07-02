<?php

namespace Majeedfahad\Workflower\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransitionLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transition_name' => $this->transition->name,
            'transition_label' => $this->transition->label,
            'performer_type' => $this->performer_type ? strtolower(class_basename($this->performer_type)) : null,
            'performer' => $this->resolvePerformerName(),
            'meta' => $this->parsedMeta(),
            'created_at' => $this->created_at,
        ];
    }

    private function resolvePerformerName(): ?string
    {
        return match(true) {
            ! $this->performer                                        => null,
            method_exists($this->performer, 'getPerformerName')      => $this->performer->getPerformerName($this),
            default                                                   => $this->performer->name ?? null,
        };
    }

    private function parsedMeta(): array
    {
        if (! $this->meta) {
            return [];
        }

        if (method_exists($this->resource, 'parseMeta')) {
            return $this->resource->parseMeta($this->meta);
        }

        return collect($this->meta)->except(['ip', 'user_agent', 'user_class', 'user_id', 'user_name'])->all();
    }
}
