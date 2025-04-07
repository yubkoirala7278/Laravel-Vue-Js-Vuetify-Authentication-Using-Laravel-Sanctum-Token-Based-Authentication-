<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search', '');
        $status = $request->query('status', ''); // New status filter
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');

        $query = Category::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        if ($status) {
            $query->where('status', $status); // Exact match for status
        }

        $allowedSorts = ['name', 'status', 'updated_at']; // Updated to include updated_at
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'updated_at';
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'desc';

        $query->orderBy($sortBy, $sortDirection);
        $categories = $query->paginate($perPage);

        return CategoryResource::collection($categories);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name',
            'status' => 'required|in:active,inactive',
            'image' => 'required|image|max:2048', // Max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);

        return new CategoryResource($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255|unique:categories,name,' . $category->id,
            'status' => 'required|in:active,inactive',
            'image'  => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'status']);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            // Store new image
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return (new CategoryResource($category))->response()->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            // Delete image if exists
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            // Delete category
            $category->delete();

            return response()->json([
                'message' => 'Category deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete the category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete multiple categories
     */
    public function deleteMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slugs' => 'required|array',
            'slugs.*' => 'required|string|exists:categories,slug',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $slugs = $request->input('slugs');

        DB::transaction(function () use ($slugs) {
            $categories = Category::whereIn('slug', $slugs)->get();

            foreach ($categories as $category) {
                if ($category->image) {
                    Storage::disk('public')->delete($category->image);
                }
                $category->delete();
            }
        });

        return response()->json(null, 204);
    }
}
