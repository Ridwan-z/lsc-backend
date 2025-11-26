<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Get all categories for user
     */
    public function index(Request $request)
    {
        $categories = Category::where('user_id', $request->user()->id)
            ->withCount('lectures')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ], 200);
    }

    /**
     * Create new category
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = Category::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'color' => $request->color ?? '#87CEEB',
            'icon' => $request->icon,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        $category = Category::where('category_id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update($request->only(['name', 'color', 'icon', 'description']));

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category
        ], 200);
    }

    /**
     * Delete category
     */
    public function destroy(Request $request, $id)
    {
        $category = Category::where('category_id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Set lectures' category_id to null (handled by foreign key ON DELETE SET NULL)
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ], 200);
    }
}
