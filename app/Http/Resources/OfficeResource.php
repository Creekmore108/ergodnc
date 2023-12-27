<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class OfficeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'user' => UserResource::make($this->whenLoaded('user')),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'featured_image' => ImageResource::make($this->whenLoaded('featuredImage')),
            'reservations_count' => $this->resource->reservations_count ?? 0,

            $this->merge(Arr::except(parent::toArray($request), [
                'user_id', 'created_at', 'updated_at',
                'deleted_at'
            ]))
        ];
        // return Arr::except(parent::toArray($request), [
        //     'user_id', 'created_at', 'updated_at',
        //     'deleted_at'
        // ]);
    }
}
