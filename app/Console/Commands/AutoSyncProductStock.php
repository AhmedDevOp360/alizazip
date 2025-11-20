<?php

namespace App\Console\Commands;

use App\Business;
use App\BusinessLocation;
use App\Utils\ProductUtil;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoSyncProductStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:autoSyncProductStock {--business_id= : Specific business ID to sync} {--location_id= : Specific location ID to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically sync product stock quantities by finding and fixing mismatches across all businesses or specific business/location';

    protected $productUtil;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ProductUtil $productUtil)
    {
        parent::__construct();
        $this->productUtil = $productUtil;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');

            $business_id = $this->option('business_id');
            $location_id = $this->option('location_id');

            $this->info('Starting automatic product stock synchronization...');

            // Get businesses to process
            if ($business_id) {
                $businesses = Business::where('id', $business_id)->get();
                if ($businesses->isEmpty()) {
                    $this->error("Business with ID {$business_id} not found.");
                    return 1;
                }
            } else {
                $businesses = Business::all();
            }

            $total_fixed = 0;
            $total_checked = 0;

            foreach ($businesses as $business) {
                $this->info("Processing business: {$business->name} (ID: {$business->id})");

                // Get locations for this business
                $locations = BusinessLocation::where('business_id', $business->id);
                if ($location_id) {
                    $locations = $locations->where('id', $location_id);
                }
                $locations = $locations->get();

                if ($locations->isEmpty()) {
                    $this->warn("No locations found for business {$business->name}");
                    continue;
                }

                foreach ($locations as $location) {
                    $this->info("  Processing location: {$location->name} (ID: {$location->id})");

                    // Get stock mismatches for this location
                    $stock_mismatches = $this->productUtil->getVariationStockMisMatch(
                        $business->id, 
                        null, // variation_id - null means all variations
                        $location->id
                    );

                    $location_fixed = 0;
                    $location_checked = 0;

                    foreach ($stock_mismatches as $mismatch) {
                        $location_checked++;
                        $total_checked++;

                        // Check if there's a mismatch between calculated stock and actual stock
                        if ($mismatch->total_stock_calculated != $mismatch->stock) {
                            $this->line("    Fixing mismatch for product: {$mismatch->product} - {$mismatch->variation_name}");
                            $this->line("      Calculated stock: {$mismatch->total_stock_calculated}, Actual stock: {$mismatch->stock}");

                            // Fix the mismatch
                            $this->productUtil->fixVariationStockMisMatch(
                                $business->id,
                                $mismatch->variation_id,
                                $location->id,
                                $mismatch->total_stock_calculated
                            );

                            $location_fixed++;
                            $total_fixed++;
                        }
                    }

                    $this->info("    Location {$location->name}: {$location_fixed} mismatches fixed out of {$location_checked} products checked");
                }
            }

            $this->info("Automatic stock synchronization completed!");
            $this->info("Total mismatches fixed: {$total_fixed}");
            $this->info("Total products checked: {$total_checked}");

            // Log the results
            Log::info("AutoSyncProductStock completed", [
                'total_fixed' => $total_fixed,
                'total_checked' => $total_checked,
                'businesses_processed' => $businesses->count(),
                'business_id_filter' => $business_id,
                'location_id_filter' => $location_id
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error("Error during stock synchronization: " . $e->getMessage());
            Log::error("AutoSyncProductStock failed", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return 1;
        }
    }
} 