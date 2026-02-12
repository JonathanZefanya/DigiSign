<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // ──────────────────────────────────────────────
    //  USER-FACING (own categories)
    // ──────────────────────────────────────────────

    /**
     * List current user's categories.
     */
    public function index()
    {
        $categories = Category::forUser(auth()->id())
            ->withCount('documents')
            ->latest()
            ->paginate(20);

        return view('categories.index', compact('categories'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('categories.form', ['category' => null]);
    }

    /**
     * Store new category for current user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string|max:500',
        ]);

        // Unique name per user
        $exists = Category::forUser(auth()->id())->where('name', $request->name)->exists();
        if ($exists) {
            return back()->withErrors(['name' => 'You already have a category with this name.'])->withInput();
        }

        Category::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'color' => $request->color,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully!');
    }

    /**
     * Show edit form (only own category).
     */
    public function edit(Category $category)
    {
        $this->authorizeCategory($category);
        return view('categories.form', compact('category'));
    }

    /**
     * Update category (only own).
     */
    public function update(Request $request, Category $category)
    {
        $this->authorizeCategory($category);

        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        // Unique name per user (excluding self)
        $exists = Category::forUser(auth()->id())
            ->where('name', $request->name)
            ->where('id', '!=', $category->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'You already have a category with this name.'])->withInput();
        }

        $category->update([
            'name' => $request->name,
            'color' => $request->color,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Delete category (only own).
     */
    public function destroy(Category $category)
    {
        $this->authorizeCategory($category);

        $category->documents()->update(['category_id' => null]);
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted. Documents have been unassigned.');
    }

    /**
     * Ensure user owns the category (or is admin).
     */
    private function authorizeCategory(Category $category)
    {
        if ($category->user_id != auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You do not own this category.');
        }
    }

    // ──────────────────────────────────────────────
    //  ADMIN (all categories)
    // ──────────────────────────────────────────────

    /**
     * Admin: list ALL categories.
     */
    public function adminIndex()
    {
        $categories = Category::with('user')
            ->withCount('documents')
            ->latest()
            ->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Admin: create form.
     */
    public function adminCreate()
    {
        return view('admin.categories.form', ['category' => null]);
    }

    /**
     * Admin: store (admin-level, no user_id = global).
     */
    public function adminStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        // Check unique global category
        if (Category::whereNull('user_id')->where('name', $request->name)->exists()) {
             return back()->withErrors(['name' => 'A global category with this name already exists.'])->withInput();
        }

        Category::create([
            'user_id' => null,
            'name' => $request->name,
            'color' => $request->color,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully!');
    }

    /**
     * Admin: edit any category.
     */
    public function adminEdit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    /**
     * Admin: update any category.
     */
    public function adminUpdate(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        // Check uniqueness within the same scope (User or Global)
        $query = Category::where('name', $request->name)
                         ->where('id', '!=', $category->id);
                         
        if ($category->user_id) {
            $query->where('user_id', $category->user_id);
        } else {
            $query->whereNull('user_id');
        }

        if ($query->exists()) {
             return back()->withErrors(['name' => 'A category with this name already exists for this owner.'])->withInput();
        }

        $category->update([
            'name' => $request->name,
            'color' => $request->color,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Admin: delete any category.
     */
    public function adminDestroy(Category $category)
    {
        $category->documents()->update(['category_id' => null]);
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted. Documents have been unassigned.');
    }
}
