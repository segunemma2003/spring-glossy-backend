<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'price' => (float) $this->price,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                    'description' => $this->category->description,
                    'image' => $this->category->image,
                ];
            }),
            'colors' => $this->colors,
            'images' => $this->image_urls,
            'image_url' => $this->image_url,
            'is_new' => $this->is_new,
            'is_best_seller' => $this->is_best_seller,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
