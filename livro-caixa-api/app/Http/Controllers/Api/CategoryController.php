<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Categories are global (shared by every account), matching the legacy
 * lc_cat table, which has no per-account scoping at all.
 */
class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection(Category::query()->orderBy('name')->get());
    }

    public function store(Request $request): CategoryResource
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:credit,debit'],
        ]);

        return new CategoryResource(Category::query()->create($data));
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    public function update(Request $request, Category $category): CategoryResource
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:credit,debit'],
        ]);

        $category->update($data);

        return new CategoryResource($category);
    }

    public function destroy(Category $category): Response
    {
        $category->delete();

        return response()->noContent();
    }
}
