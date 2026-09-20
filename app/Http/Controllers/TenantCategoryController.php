<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TenantCategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Tenant/Categories/Index', [
            'categories' => Category::query()->withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $name = $request->string('name')->toString();
        Category::query()->create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
        ]);

        return back()->with('success', 'Category added successfully.');
    }
}