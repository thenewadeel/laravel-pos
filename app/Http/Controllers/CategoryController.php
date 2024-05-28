<?php

namespace App\Http\Controllers;

// use App\Models\Category;
use AliBayat\LaravelCategorizable\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
// use App\Models\CategoryProducts;
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
    public function index(Request $request)
    {
        $products = Product::query();
        $categories = Category::query();
        if ($request->has('cat_ids')) {
            logger($request->cat_ids);
            $categories =    $categories->whereIn('id', $request->cat_ids);
            // $products = $products->whereIn('category_id', $request->cat_ids);
            // ->delete();
            // foreach ($request->cat_ids as $cat_id) {
            //     foreach ($request->input('product_ids', []) as $product_id) {
            //         $categoryProduct = new CategoryProducts();
            //         $categoryProduct->category_id = $cat_id;
            //         $categoryProduct->product_id = $product_id;
            //         $categoryProduct->save();
            //     }
            // }
        }
        $categories = $categories->where('type', 'product');
        $categories = $categories->get();
        $categories = $categories->map(function ($cat) {
            $cat['items'] = Category::find($cat->id)->entries(Product::class)->get();
            return $cat;
        });



        if (request()->wantsJson()) {
            return $categories;
        }
        // dd($categories);
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
            'description' => 'nullable',
            'image' => 'nullable|image',
            'kitchen_printer_ip' => 'nullable',
            'type' => 'nullable|in:product,default',
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->description = $request->description;
        if ($request->hasFile('image')) {
            $category->image = $request->file('image')->store('public/categories');
        }
        $category->kitchen_printer_ip = $request->kitchen_printer_ip;
        $category->type = $request->type;
        $category->save();
        // dd($category);
        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $products = Category::find($category->id)->entries(Product::class)->get();
        // $products = Product::all();
        // dd($products);
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
        // dd($request->all());
        $request->validate([
            'name' => 'nullable',
            'description' => 'nullable',
            'image' => 'nullable|image',
            'kitchen_printer_ip' => 'nullable'
        ]);
        $category->name = $request->name;
        $category->description = $request->description;
        $category->kitchen_printer_ip = $request->kitchen_printer_ip;
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
        if ($category->photo) {
            Storage::delete($category->photo);
        }

        $category->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true
            ]);
        }
        return back()->with('message', "Category deleted");
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
            Product::find($product_id)->attachCategory($category);
            // $categoryProduct = new CategoryProducts();
            // $categoryProduct->category_id = $request->category_id;
            // $categoryProduct->product_id = $product_id;
            // $categoryProduct->save();
            // $category->products()->save(Product::findOrFail($product_id), $categoryProduct->toArray());
        }

        return redirect()->back();
    }

    /**
     * Delete the specified category-product relation
     */
    public function catproddelete(Category $category_id, $product_id)
    {
        // Log the arguments
        // logger('Deleting category-product relation:', [
        //     'category_id' => $category_id,
        //     'product_id' => $product_id,
        // ]);
        Product::find($product_id)->detachCategory($category_id);
        // CategoryProducts::where('category_id', $category_id)
        //     ->where('product_id', $product_id)
        //     ->delete();
        return redirect()->back();
    }
}
