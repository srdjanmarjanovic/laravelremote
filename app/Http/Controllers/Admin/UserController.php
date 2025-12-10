<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(Request $request): Response
    {
        $query = User::query()
            ->with(['developerProfile', 'companies'])
            ->withCount(['applications', 'createdPositions']);

        // Filter by role
        if ($request->filled('role')) {
            $role = $request->input('role');
            if ($role === 'hr') {
                $query->where('role', 'hr');
            } elseif ($role === 'developer') {
                $query->where('role', 'developer');
            } elseif ($role === 'admin') {
                $query->where('role', 'admin');
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['role', 'search', 'sort_by', 'sort_order']),
        ]);
    }
}
