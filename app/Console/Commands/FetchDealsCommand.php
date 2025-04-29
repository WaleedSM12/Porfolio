<?php

namespace App\Console\Commands;

use App\Models\Deal;
use App\Services\ExternalDealService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchDealsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deals:fetch {--source=all : The source to fetch deals from (all, skyscanner, amadeus)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch deals from external APIs and store them in the database';

    /**
     * Execute the console command.
     */
    public function handle(ExternalDealService $dealService): int
    {
        $source = $this->option('source');
        
        $this->info("Fetching deals from {$source} source...");
        
        try {
            // Dispatch the job to fetch deals to the queue
            dispatch(function () use ($dealService, $source) {
                $deals = $dealService->fetchDeals($source);
                
                foreach ($deals as $dealData) {
                    // Check if the deal already exists by source_id
                    $existingDeal = Deal::where('source_id', $dealData['source_id'])
                                   ->where('source', $dealData['source'])
                                   ->first();
                                   
                    if ($existingDeal) {
                        // Update the existing deal
                        $existingDeal->update($dealData);
                    } else {
                        // Create a new deal
                        Deal::create($dealData);
                    }
                }
                
                Log::info("Successfully fetched and processed " . count($deals) . " deals from {$source}");
            });
            
            $this->info("Deals fetching job dispatched to the queue.");
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error fetching deals: {$e->getMessage()}");
            Log::error("Error fetching deals: {$e->getMessage()}", [
                'exception' => $e,
                'source' => $source
            ]);
            
            return self::FAILURE;
        }
    }
} 