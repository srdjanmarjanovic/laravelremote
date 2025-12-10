<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    /**
     * Display a listing of all applications.
     */
    public function index(Request $request): Response
    {
        $query = Application::query()
            ->with([
                'position.company',
                'user' => function ($query) {
                    $query->withTrashed();
                },
                'user.developerProfile',
            ]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by position
        if ($request->filled('position_id')) {
            $query->where('position_id', $request->input('position_id'));
        }

        // Filter by company
        if ($request->filled('company_id')) {
            $query->whereHas('position', function ($q) use ($request) {
                $q->where('company_id', $request->input('company_id'));
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                    ->orWhereHas('position', function ($positionQuery) use ($search) {
                        $positionQuery->where('title', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->input('sort_by', 'applied_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $applications = $query->paginate(20)->withQueryString();

        // Add user_archived flag
        $applications->through(function ($application) {
            $application->user_archived = $application->user->trashed();

            return $application;
        });

        return Inertia::render('Admin/Applications/Index', [
            'applications' => $applications,
            'filters' => $request->only(['status', 'position_id', 'company_id', 'search', 'sort_by', 'sort_order']),
        ]);
    }
}
