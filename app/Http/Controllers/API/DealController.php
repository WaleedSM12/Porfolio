<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DealController extends Controller
{
    /**
     * Display a listing of the deals.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Deal::query();
        
        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // Sort by price
        if ($request->has('sort_by') && $request->sort_by === 'price') {
            $direction = $request->input('direction', 'asc');
            $query->orderBy('price', $direction);
        } else {
            // Default sort by newest
            $query->latest();
        }
        
        // Paginate results - default to 20 per page, max 100
        $perPage = min($request->input('per_page', 20), 100);
        $deals = $query->paginate($perPage);
        
        return response()->json($deals);
    }

    /**
     * Display the specified deal.
     */
    public function show(Deal $deal): JsonResponse
    {
        return response()->json($deal);
    }

    /**
     * Bookmark a deal for the authenticated user.
     */
    public function bookmark(Request $request, Deal $deal): JsonResponse
    {
        $user = $request->user();
        
        // Check if already bookmarked
        if ($user->bookmarkedDeals()->where('deal_id', $deal->id)->exists()) {
            return response()->json([
                'message' => 'Deal already bookmarked',
            ], 409); // Conflict status code
        }
        
        // Add to queue to process the bookmark
        dispatch(function () use ($user, $deal) {
            $user->bookmarkedDeals()->attach($deal->id);
        });
        
        return response()->json([
            'message' => 'Deal bookmarked successfully',
        ], 201); // Created status code
    }
} 