<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Admin dashboard.
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_documents' => Document::count(),
            'signed_documents' => Document::where('status', 'signed')->count(),
            'draft_documents' => Document::where('status', 'draft')->count(),
        ];

        $recentDocuments = Document::with('user')
            ->latest()
            ->take(10)
            ->get();

        $recentUsers = User::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentDocuments', 'recentUsers'));
    }

    /**
     * Show settings form.
     */
    public function settings()
    {
        $settings = Setting::allSettings();
        return view('admin.settings', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'app_name' => 'nullable|string|max:255',
            'app_timezone' => 'nullable|string|max:100',
            'app_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'app_favicon' => 'nullable|image|mimes:png,ico|max:1024',
            'sso_api_url' => 'nullable|url|max:500',
            'sso_api_key' => 'nullable|string|max:500',
            'max_upload_size' => 'nullable|integer|min:1|max:100',
            'provider_api_key' => 'nullable|string|max:500',
        ]);

        // Text settings
        if ($request->filled('app_name')) {
            Setting::set('app_name', $request->app_name);
        }
        if ($request->filled('app_timezone')) {
            Setting::set('app_timezone', $request->app_timezone);
        }
        if ($request->has('sso_api_url')) {
            Setting::set('sso_api_url', $request->sso_api_url);
        }
        if ($request->has('sso_api_key')) {
            Setting::set('sso_api_key', $request->sso_api_key);
        }
        if ($request->filled('max_upload_size')) {
            Setting::set('max_upload_size', $request->max_upload_size);
        }
        if ($request->has('provider_api_key')) {
            Setting::set('provider_api_key', $request->provider_api_key);
        }

        // Toggle settings (checkbox sends value only when checked)
        Setting::set('registration_enabled', $request->boolean('registration_enabled') ? '1' : '0');

        // File settings
        if ($request->hasFile('app_logo')) {
            // Delete old logo
            $oldLogo = Setting::get('app_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('app_logo')->store('settings', 'public');
            Setting::set('app_logo', $path);
        }

        if ($request->hasFile('app_favicon')) {
            $oldFavicon = Setting::get('app_favicon');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
            $path = $request->file('app_favicon')->store('settings', 'public');
            Setting::set('app_favicon', $path);
        }

        Setting::clearCache();

        return back()->with('success', 'Settings updated successfully!');
    }

    /**
     * List all users.
     */
    public function users(Request $request)
    {
        $query = User::withCount('documents');

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status (is_active)
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
            // 'all' => no filter
        }

        // Sort by documents count
        if ($request->filled('sort')) {
            $sort = $request->sort;
            if ($sort === 'docs_desc') {
                $query->orderBy('documents_count', 'desc');
            } elseif ($sort === 'docs_asc') {
                $query->orderBy('documents_count', 'asc');
            } else {
                $query->latest(); // Default: newest first
            }
        } else {
            $query->latest();
        }

        $users = $query->paginate(20);

        // If AJAX request, return only the table partial
        if ($request->ajax()) {
            return view('admin.partials.user-list', compact('users'))->render();
        }

        return view('admin.users', compact('users'));
    }

    /**
     * Create user form.
     */
    public function createUser()
    {
        return view('admin.user-form', ['user' => null]);
    }

    /**
     * Store new user.
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,user',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
        ]);

        return redirect()->route('admin.users')
            ->with('success', 'User created successfully!');
    }

    /**
     * Edit user form.
     */
    public function editUser(User $user)
    {
        return view('admin.user-form', compact('user'));
    }

    /**
     * Update user.
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,user',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Toggle user active status.
     */
    public function toggleUser(User $user)
    {
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot deactivate your own account.']);
        }

        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', 'User status updated.');
    }

    /**
     * Delete a user and all their documents and categories.
     */
    public function destroyUser(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $userName = $user->name;
        $documentsCount = $user->documents()->count();
        $categoriesCount = $user->categories()->count();

        // Delete user's documents (files will be deleted via model events if configured)
        foreach ($user->documents as $document) {
            // Delete document files from storage
            if ($document->filepath && Storage::disk('public')->exists($document->filepath)) {
                Storage::disk('public')->delete($document->filepath);
            }
            if ($document->signed_filepath && Storage::disk('public')->exists($document->signed_filepath)) {
                Storage::disk('public')->delete($document->signed_filepath);
            }
            $document->delete();
        }

        // Delete user's categories
        $user->categories()->delete();

        // Delete the user
        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', "User '{$userName}' deleted successfully with {$documentsCount} documents and {$categoriesCount} categories.");
    }

    /**
     * Show all documents (admin view).
     */
    public function documents(Request $request)
    {
        $query = Document::with(['user', 'category']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('original_filename', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter Category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $documents = $query->latest()->paginate(20);

        if ($request->ajax()) {
            return view('admin.partials.document-list', compact('documents'))->render();
        }

        $categories = \App\Models\Category::active()->orderBy('name')->get();

        return view('admin.documents', compact('documents', 'categories'));
    }
}
