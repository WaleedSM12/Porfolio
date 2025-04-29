<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalDealService
{
    /**
     * Fetch deals from external API(s)
     * 
     * @param string $source The source to fetch deals from (all, skyscanner, amadeus)
     * @return array The fetched deals
     */
    public function fetchDeals(string $source = 'all'): array
    {
        $allDeals = [];
        
        if ($source === 'all' || $source === 'skyscanner') {
            $skyscannerDeals = $this->fetchSkyscannerDeals();
            $allDeals = array_merge($allDeals, $skyscannerDeals);
        }
        
        if ($source === 'all' || $source === 'amadeus') {
            $amadeusDeals = $this->fetchAmadeusDeals();
            $allDeals = array_merge($allDeals, $amadeusDeals);
        }
        
        return $allDeals;
    }
    
    /**
     * Fetch deals from Skyscanner API
     */
    protected function fetchSkyscannerDeals(): array
    {
        try {
            // Check if we've already fetched recently to avoid hitting rate limits
            $cacheKey = 'skyscanner_api_last_fetch';
            if (Cache::has($cacheKey) && now()->diffInMinutes(Cache::get($cacheKey)) < 30) {
                Log::warning('Skipping Skyscanner API fetch due to rate limiting');
                return [];
            }
            
            // Update the last fetch time
            Cache::put($cacheKey, now(), 60); // Store for 60 minutes
            
            // This is a placeholder for the real API implementation
            // You would replace this with actual Skyscanner API integration
            $httpClient = $this->getSkyscannerClient();
            
            // Example endpoint - you'll need to replace this with the actual endpoint
            $response = $httpClient->get('/v3/flights/live/search/create', [
                'query' => [
                    'market' => 'US',
                    'locale' => 'en-US',
                    'currency' => 'USD',
                    'adults' => 1,
                ]
            ]);
            
            if ($response->failed()) {
                Log::error('Failed to fetch from Skyscanner API: ' . $response->body());
                return [];
            }
            
            // Process the response - this is just a placeholder
            // You'll need to adapt this to the actual API response format
            $processedDeals = [];
            
            // Example response processing
            $flights = $response->json('data.flights') ?? [];
            foreach ($flights as $flight) {
                $processedDeals[] = [
                    'title' => 'Flight: ' . ($flight['origin'] ?? 'Unknown') . ' to ' . ($flight['destination'] ?? 'Unknown'),
                    'description' => 'Fly with ' . ($flight['carrier'] ?? 'Unknown carrier'),
                    'type' => 'flight',
                    'price' => $flight['price'] ?? 0,
                    'currency' => 'USD',
                    'source_id' => $flight['id'] ?? uniqid(),
                    'source' => 'Skyscanner',
                    'details' => $flight,
                    'valid_until' => now()->addDays(7),
                    'url' => $flight['deepLink'] ?? null,
                ];
            }
            
            return $processedDeals;
        } catch (\Exception $e) {
            Log::error('Error fetching Skyscanner deals: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return [];
        }
    }
    
    /**
     * Fetch deals from Amadeus API
     */
    protected function fetchAmadeusDeals(): array
    {
        try {
            // Check if we've already fetched recently to avoid hitting rate limits
            $cacheKey = 'amadeus_api_last_fetch';
            // if (Cache::has($cacheKey) && now()->diffInMinutes(Cache::get($cacheKey)) < 30) {
            //     Log::warning('Skipping Amadeus API fetch due to rate limiting');
            //     return [];
            // }
            
            // Update the last fetch time
            Cache::put($cacheKey, now(), 60); // Store for 60 minutes
            
            // This is a placeholder for the real API implementation
            // You would replace this with actual Amadeus API integration
            $httpClient = $this->getAmadeusClient();
            
            // First, get an access token (this is usually required for Amadeus)
            $tokenResponse = Http::asForm()->post('https://test.api.amadeus.com/v1/security/oauth2/token', [
                'grant_type' => 'client_credentials',
                'client_id' => config('services.amadeus.key'),
                'client_secret' => config('services.amadeus.secret')
            ]);
            
            if ($tokenResponse->failed()) {
                Log::error('Failed to get Amadeus token: ' . $tokenResponse->body());
                return [];
            }
            
            $token = $tokenResponse->json('access_token');
            
            // Make a request to the hotel offers endpoint
            $response = $httpClient->withToken($token)->get('/v3/shopping/hotel-offers', [
                'hotelIds' => 'MCLONGHM', //HNPARKGU, HNPARSPC, HNPARNUJ, BWPAR599
                'roomQuantity' => 1,
                'adults' => 1,
                'bestRateOnly' => true
            ]);
            
            if ($response->failed()) {
                Log::error('Failed to fetch from Amadeus API: ' . $response->body());
                return [];
            }
            
            // Process the response - this is just a placeholder
            // You'll need to adapt this to the actual API response format
            $processedDeals = [];
            
            // Example response processing
            $hotels = $response->json('data') ?? [];
            foreach ($hotels as $hotel) {
                $offer = $hotel['offers'][0] ?? null;
                if ($offer) {
                    $processedDeals[] = [
                        'title' => 'Hotel: ' . ($hotel['hotel']['name'] ?? 'Unknown Hotel'),
                        'description' => 'Stay at ' . ($hotel['hotel']['name'] ?? 'Unknown Hotel') . ' in ' . ($hotel['hotel']['cityCode'] ?? 'Unknown Location'),
                        'type' => 'hotel',
                        'price' => $offer['price']['total'] ?? 0,
                        'currency' => $offer['price']['currency'] ?? 'USD',
                        'source_id' => $hotel['hotel']['hotelId'] ?? uniqid(),
                        'source' => 'Amadeus',
                        'details' => [
                            'hotel' => $hotel['hotel'],
                            'offer' => $offer,
                        ],
                        'valid_until' => now()->addDays(30),
                        'url' => null,
                    ];
                }
            }
            
            return $processedDeals;
        } catch (\Exception $e) {
            Log::error('Error fetching Amadeus deals: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return [];
        }
    }
    
    /**
     * Get a configured HTTP client for Skyscanner API
     */
    protected function getSkyscannerClient(): PendingRequest
    {
        return Http::withHeaders([
            'X-RapidAPI-Key' => config('services.skyscanner.key'),
            'X-RapidAPI-Host' => 'skyscanner50.p.rapidapi.com'
        ])->baseUrl('https://skyscanner50.p.rapidapi.com');
    }
    
    /**
     * Get a configured HTTP client for Amadeus API
     */
    protected function getAmadeusClient(): PendingRequest
    {
        return Http::baseUrl('https://test.api.amadeus.com');
    }
} 