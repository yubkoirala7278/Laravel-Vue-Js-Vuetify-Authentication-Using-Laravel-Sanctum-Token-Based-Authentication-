<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'status' => $this->status,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'description' => $this->description,
            'price' => $this->price,
            'compare_price' => $this->compare_price ?? 'N/A',
            'is_featured' => $this->is_featured,
            'category' => $this->category->name,
            'sub_category' => $this->subCategory->name ?? 'N/A',
            'brand' => $this->brand->name ?? 'N/A',
            'color' => $this->color->name ?? 'N/A',
            'category_id' => $this->category_id,
            'sub_category_id' => $this->sub_category_id,
            'brand_id' => $this->brand_id,
            'color_id' => $this->color_id,
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
