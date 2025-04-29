<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the user's bookmarked deals.
     */
    public function bookmarks(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min($request->input('per_page', 20), 100);
        
        $bookmarks = $user->bookmarkedDeals()
            ->latest('bookmarks.created_at')
            ->paginate($perPage);
            
        return response()->json($bookmarks);
    }
} 