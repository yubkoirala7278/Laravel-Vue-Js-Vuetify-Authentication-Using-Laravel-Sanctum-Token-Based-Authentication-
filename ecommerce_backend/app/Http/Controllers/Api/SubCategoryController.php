<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActiveSubCategoryResource;
use App\Http\Resources\SubCategoryResource;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Retrieve query parameters with defaults
        $perPage = $request->query('per_page', 10); // Number of items per page, default 10
        $search = $request->query('search', ''); // Search term for filtering
        $status = $request->query('status', ''); // Status filter (active/inactive)
        $sortBy = $request->query('sort_by', 'created_at'); // Field to sort by
        $sortDirection = $request->query('sort_direction', 'desc'); // Sort direction (asc/desc)

        // Initialize query with eager-loaded category relationship
        $query = SubCategory::with('category');

        // Apply search filter if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Search in subcategory name (partial match)
                $q->where('sub_categories.name', 'like', '%' . $search . '%')
                    // Search in subcategory status (partial match)
                    ->orWhere('status', 'like', '%' . $search . '%')
                    // Search in related category name (partial match)
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Apply status filter if provided (exact match)
        if ($status) {
            $query->where('status', $status);
        }

        // Define allowed fields for sorting
        $allowedSorts = ['name', 'status', 'updated_at', 'category'];
        // Validate sortBy, fallback to 'updated_at' if invalid
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'updated_at';
        // Validate sortDirection, fallback to 'desc' if invalid
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'desc';

        // Handle sorting logic
        if ($sortBy === 'category') {
            // Join with categories table to sort by category name
            $query->join('categories', 'sub_categories.category_id', '=', 'categories.id')
                // Sort by category name in specified direction
                ->orderBy('categories.name', $sortDirection)
                // Select only subcategory columns to avoid ambiguity
                ->select('sub_categories.*');
        } else {
            // Sort by specified subcategory field (name, status, updated_at)
            $query->orderBy($sortBy, $sortDirection);
        }

        // Handle "All" records request (per_page = -1)
        if ($perPage == -1) {
            // Fetch all records without pagination
            $subCategories = $query->get();
            $total = $subCategories->count();
            // Construct response with metadata for frontend compatibility
            $response = [
                'data' => SubCategoryResource::collection($subCategories),
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $total,
                    'total' => $total,
                ],
            ];
            return response()->json($response);
        }

        // Paginate results for standard requests
        $subCategories = $query->paginate($perPage);
        // Return paginated results as resource collection
        return SubCategoryResource::collection($subCategories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'category_id' => 'required|exists:categories,id',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $sub_category = SubCategory::create($data);
        return new SubCategoryResource($sub_category);
    }

    /**
     * Display the specified resource.
     */
    public function show(SubCategory $subCategory)
    {
        return new SubCategoryResource($subCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubCategory $subCategory)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        $subCategory->update($data);

        return (new SubCategoryResource($subCategory))->response()->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubCategory $subCategory)
    {
        try {
            // Delete sub category
            $subCategory->delete();

            return response()->json([
                'message' => 'Sub Category deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete the sub category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Fetch all active sub categories.
     */
    public function activeSubCategories(Request $request)
    {
        $subCategories = SubCategory::where('status', 'active')->orderBy('name', 'asc')->get();
        return ActiveSubCategoryResource::collection($subCategories);
    }
}
