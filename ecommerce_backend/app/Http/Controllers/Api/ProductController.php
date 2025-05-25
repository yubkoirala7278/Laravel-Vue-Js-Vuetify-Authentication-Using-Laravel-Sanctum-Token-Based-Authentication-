<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search', '');
        $status = $request->query('status', '');
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');

        $query = Product::with(['category', 'subCategory', 'brand', 'color']);

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhere('price', 'like', '%' . $search . '%');
            });
        }

        // Status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Define allowed sort fields, including related table fields
        $allowedSorts = [
            'name' => 'products.name',
            'status' => 'products.status',
            'updated_at' => 'products.updated_at',
            'category' => 'categories.name',
            'sub_category' => 'sub_categories.name',
            'brand' => 'brands.name',
            'color' => 'colors.name',
            'price'=>'products.price',
            'compare_price'=>'products.compare_price',
            'is_featured'=>'products.is_featured',
        ];

        // Validate sortBy and map to table-qualified column
        $sortColumn = $allowedSorts[$sortBy] ?? 'products.updated_at';
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'desc';

        // Join related tables for sorting
        if (in_array($sortBy, ['category', 'sub_category', 'brand', 'color'])) {
            if ($sortBy === 'category') {
                $query->join('categories', 'products.category_id', '=', 'categories.id');
            } elseif ($sortBy === 'sub_category') {
                $query->leftJoin('sub_categories', 'products.sub_category_id', '=', 'sub_categories.id');
            } elseif ($sortBy === 'brand') {
                $query->leftJoin('brands', 'products.brand_id', '=', 'brands.id');
            } elseif ($sortBy === 'color') {
                $query->leftJoin('colors', 'products.color_id', '=', 'colors.id');
            }
        }

        // Apply sorting
        $query->orderBy($sortColumn, $sortDirection);

        // Select only products table columns to avoid ambiguity
        $query->select('products.*');

        // Paginate results
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
     public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|gt:price',
            'is_featured' => 'required|in:Yes,No',
            'status' => 'required|in:active,inactive',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'color_id' => 'nullable|exists:colors,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'name.required' => 'The product name is required.',
            'price.numeric' => 'The price must be a number.',
            'compare_price.gt' => 'The compare price must be greater than the actual price.',
            'image.image' => 'The uploaded file must be an image.',
            'image.max' => 'The image must not be larger than 2MB.',
            'category_id.exists' => 'The selected category does not exist.',
            'category_id.required' => 'The category is required.',
            'sub_category_id.exists' => 'The selected subcategory does not exist.',
            'brand_id.exists' => 'The selected brand does not exist.',
            'color_id.exists' => 'The selected color does not exist.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        // Generate a unique slug
        $data['slug'] = \Illuminate\Support\Str::slug($request->name) . '-' . uniqid();

        $product = Product::create($data);

        return new ProductResource($product);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|gt:price',
             'is_featured' => 'required|in:Yes,No',
            'status' => 'required|in:active,inactive',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'color_id' => 'nullable|exists:colors,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'name.required' => 'The product name is required.',
            'price.numeric' => 'The price must be a number.',
            'compare_price.gt' => 'The compare price must be greater than the actual price.',
            'image.image' => 'The uploaded file must be an image.',
            'image.max' => 'The image must not be larger than 2MB.',
            'category_id.exists' => 'The selected category does not exist.',
            'category_id.required' => 'The category is required.',
            'sub_category_id.exists' => 'The selected subcategory does not exist.',
            'brand_id.exists' => 'The selected brand does not exist.',
            'color_id.exists' => 'The selected color does not exist.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prepare data for update, including all validated fields
        $data = $request->all();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            // Store new image
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        // Update the product with all data
        $product->update($data);

        return (new ProductResource($product))->response()->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            // Delete image if exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            // Delete product
            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete the product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
