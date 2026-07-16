<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'book_id' => $this->book_id,
            'book_number' => $this->whenLoaded('book', fn () => $this->book->number),
            'category_id' => $this->category_id,
            'category_name' => $this->whenLoaded('category', fn () => $this->category?->name),
            'page_number' => $this->page_number,
            'type' => $this->type,
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'running_balance' => $this->when(
                array_key_exists('running_balance', $this->resource->getAttributes()),
                fn () => (float) $this->running_balance
            ),
            'movement_date' => $this->movement_date->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
