<?php

namespace App\Http\Controllers;

use App\Enums\ListingType;
use App\Models\Position;
use App\Models\PositionView;
use App\Models\Technology;
use App\Services\DeviceDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PublicPositionController extends Controller
{
    /**
     * Display a listing of positions.
     */
    public function index(Request $request): View
    {
        $query = Position::query()
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with(['company', 'technologies'])
            ->withCount('applications');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('long_description', 'like', "%{$search}%");
            });
        }

        // Filter by technology
        if ($request->filled('technology')) {
            $query->whereHas('technologies', function ($q) use ($request) {
                $q->where('slug', $request->input('technology'));
            });
        }

        // Filter by seniority
        if ($request->filled('seniority')) {
            $query->where('seniority', $request->input('seniority'));
        }

        // Filter by remote type
        if ($request->filled('remote_type')) {
            $query->where('remote_type', $request->input('remote_type'));
        }

        // Filter by location restriction (when remote_type is country)
        if ($request->filled('location_restriction')) {
            $query->where('location_restriction', $request->input('location_restriction'));
        }

        // Filter by salary range
        if ($request->filled('min_salary')) {
            $query->where('salary_max', '>=', $request->input('min_salary'));
        }

        // Sort - Top positions always appear first, then Featured and Regular mixed by published_at
        $sortBy = $request->input('sort', 'published_at');
        $sortOrder = $request->input('order', 'desc');

        // Always prioritize top positions first (1), then everything else (2)
        $query->orderByRaw('CASE WHEN listing_type = ? THEN 1 ELSE 2 END', [
            ListingType::Top->value,
        ]);

        // Then apply the requested sort (Featured and Regular will be mixed together)
        if ($sortBy === 'featured') {
            $query->orderBy('published_at', 'desc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $positions = $query->paginate(20)->withQueryString();

        $technologies = Technology::orderBy('name')->get();

        return view('positions.index', [
            'positions' => $positions,
            'technologies' => $technologies,
            'filters' => $request->only(['search', 'technology', 'seniority', 'remote_type', 'location_restriction', 'min_salary', 'sort', 'order']),
        ]);
    }

    /**
     * Display the specified position.
     */
    public function show(Request $request, string $slug): View
    {
        $position = Position::where('slug', $slug)
            ->where('status', 'published')
            ->with(['company', 'technologies'])
            ->firstOrFail();

        // Track view (anonymized)
        $this->trackView($request, $position);

        $position->loadCount('applications');

        // Check if authenticated user has already applied
        $hasApplied = false;
        if ($request->user()) {
            $hasApplied = $position->applications()
                ->where('user_id', $request->user()->id)
                ->exists();
        }

        return view('positions.show', [
            'position' => $position,
            'hasApplied' => $hasApplied,
        ]);
    }

    /**
     * Track position view (anonymized).
     */
    protected function trackView(Request $request, Position $position): void
    {
        $ipAddress = $request->ip();
        $ipHash = Hash::make($ipAddress);

        // Only track one view per IP per position per day
        $today = now()->startOfDay();
        $existingView = PositionView::where('position_id', $position->id)
            ->where('ip_address_hash', $ipHash)
            ->where('viewed_at', '>=', $today)
            ->exists();

        if ($existingView) {
            return;
        }

        // Get detailed device information from user agent
        $deviceInfo = DeviceDetector::getDeviceInfo($request->userAgent());

        // Try to get country from various sources
        $countryCode = $this->detectCountry($request);

        PositionView::create([
            'position_id' => $position->id,
            'ip_address_hash' => $ipHash,
            'country_code' => $countryCode,
            'user_agent' => $request->userAgent(),
            'device_type' => $deviceInfo['device_type'],
            'device_name' => $deviceInfo['device_name'],
            'browser' => $deviceInfo['browser'],
            'os' => $deviceInfo['os'],
            'referrer' => $request->header('referer'),
            'viewed_at' => now(),
        ]);
    }

    /**
     * Detect country from request headers.
     */
    protected function detectCountry(Request $request): ?string
    {
        // CloudFlare provides country code in header
        if ($request->header('CF-IPCountry')) {
            return $request->header('CF-IPCountry');
        }

        // Cloudflare also provides this
        if ($request->header('HTTP_CF_IPCOUNTRY')) {
            return $request->header('HTTP_CF_IPCOUNTRY');
        }

        // Accept-Language header can give a hint
        $acceptLanguage = $request->header('Accept-Language');
        if ($acceptLanguage) {
            // Extract first locale (e.g., "en-US" -> "US")
            if (preg_match('/[a-z]{2}-([A-Z]{2})/', $acceptLanguage, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
