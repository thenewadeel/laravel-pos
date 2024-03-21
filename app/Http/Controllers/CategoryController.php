<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\CategoryProducts;
use App\Traits\ListOf;

class CategoryController extends Controller
{
    use ListOf;

    protected function getModel(): string
    {
        return Category::class;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::with('products')->get();
        return view('category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => '',
            'image' => 'image',
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->description = $request->description;
        if ($request->hasFile('image')) {
            $category->image = $request->file('image')->store('public/categories');
        }
        $category->save();

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $products = Product::all();
        return view('category.show', compact('category', 'products'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $category->name = $request->name;
        $category->description = $request->description;

        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image) {
                Storage::delete($category->image);
            }
            // Store image
            $category->image = $request->file('image')->store('public/categories');
        }

        if (!$category->save()) {
            return redirect()->back()->with('error', __('category.error_updating'));
        }

        return redirect()->route('categories.index')->with('success', __('category.success_updating'));
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
    }
    public function catprodstore(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'category_id' => 'required',
            'product_ids' => 'required|array|min:1',
        ]);
        $category = Category::findOrFail($request->category_id);

        foreach ($request->product_ids as $product_id) {
            $categoryProduct = new CategoryProducts();
            $categoryProduct->category_id = $request->category_id;
            $categoryProduct->product_id = $product_id;
            $categoryProduct->save();
            // $category->products()->save(Product::findOrFail($product_id), $categoryProduct->toArray());
        }
        return redirect()->back();
    }

    /**
     * Delete the specified category-product relation
     */
    public function catproddelete(Category $category, $product_id)
    {
        CategoryProducts::where('category_id', $category->id)
            ->where('product_id', $product_id)
            ->delete();
        return redirect()->back();
    }
}
