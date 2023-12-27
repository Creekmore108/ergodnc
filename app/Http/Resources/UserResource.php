<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            $this->merge(Arr::except(parent::toArray($request), [
                'created_at', 'updated_at', 'email', 'email_verified_at'
            ]))
        ];
    }
}
