<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicCompanyController extends Controller
{
    /**
     * Display a listing of companies.
     */
    public function index(Request $request): View
    {
        $query = Company::query()
            ->whereHas('positions', function ($q) {
                $q->where('status', 'published')
                    ->where(function ($subQ) {
                        $subQ->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    });
            })
            ->withCount([
                'positions' => function ($q) {
                    $q->where('status', 'published')
                        ->where(function ($subQ) {
                            $subQ->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                        });
                },
            ]);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->input('sort', 'name');
        $sortOrder = $request->input('order', 'asc');

        if ($sortBy === 'positions_count') {
            $query->orderBy('positions_count', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $companies = $query->paginate(20)->withQueryString();

        return view('companies.index', [
            'companies' => $companies,
            'filters' => $request->only(['search', 'sort', 'order']),
        ]);
    }

    /**
     * Display the specified company.
     */
    public function show(Company $company): View
    {

        // Get published positions for this company
        $positions = Position::query()
            ->where('company_id', $company->id)
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with(['technologies'])
            ->withCount('applications')
            ->latest('published_at')
            ->get();

        return view('companies.show', [
            'company' => $company,
            'positions' => $positions,
        ]);
    }
}
