<?php

namespace App\Services;

use App\Models\Client\Item;
use App\Models\Client\Bin;
use App\Models\Client\StorageType;
use Illuminate\Support\Collection;

class PutawayService
{
    /**
     * Suggest best bins for item placement
     * 
     * @param string $itemId
     * @param float $quantity
     * @param string $warehouseId
     * @return Collection
     */
    public function suggestBins($itemId, $quantity, $warehouseId)
    {
        $item = Item::find($itemId);
        if (!$item) {
            return collect();
        }

        // Get preferred storage type from item
        $preferredStorageTypeId = $item->preferred_storage_type_id 
            ?? $item->storage_strategy['preferred_storage_type'] 
            ?? null;

        // Calculate required volume and weight
        $volumePerUnit = $item->volume_per_unit ?? $item->volume ?? 0.01;
        $weightPerUnit = $item->weight_per_unit ?? $item->weight ?? 0.01;
        
        $requiredVolume = $quantity * $volumePerUnit;
        $requiredWeight = $quantity * $weightPerUnit;

        // Build query for bins
        $query = Bin::query()
            ->where('warehouse_id', $warehouseId)
            ->where('status', 'available')
            ->where('is_active', true);

        // Filter by preferred storage type if available
        if ($preferredStorageTypeId) {
            $query->where('storage_type_id', $preferredStorageTypeId);
        }

        // Get all candidate bins
        $bins = $query->get();

        // Filter and score bins
        $suggestions = collect();
        
        foreach ($bins as $bin) {
            // Calculate available capacity
            $availableVolume = ($bin->max_volume ?? 0) - ($bin->current_volume ?? 0);
            $availableWeight = ($bin->max_weight ?? 0) - ($bin->current_weight ?? 0);

            // Check if bin can accommodate
            if ($availableVolume >= $requiredVolume && $availableWeight >= $requiredWeight) {
                $score = $this->calculateScore($bin, $item, $availableVolume, $availableWeight, $requiredVolume, $requiredWeight);
                
                $suggestions->push([
                    'bin_id' => (string) $bin->_id,
                    'bin_code' => $bin->bin_code,
                    'bin_name' => $bin->bin_name,
                    'storage_type' => $bin->storageType ? $bin->storageType->name : null,
                    'section' => $bin->section ? $bin->section->section_name : null,
                    'available_volume' => $availableVolume,
                    'available_weight' => $availableWeight,
                    'capacity_utilization' => $bin->getCapacityUtilization(),
                    'score' => $score,
                    'bin' => $bin,
                ]);
            }
        }

        // Sort by score (higher is better) and return top 5
        return $suggestions->sortByDesc('score')->take(5)->values();
    }

    /**
     * Calculate score for bin suggestion
     * Higher score = better fit
     */
    private function calculateScore($bin, $item, $availableVolume, $availableWeight, $requiredVolume, $requiredWeight)
    {
        $score = 100; // Base score

        // Preferred bin bonus
        if ($item->preferred_bin_id && (string) $bin->_id === (string) $item->preferred_bin_id) {
            $score += 50;
        }

        // Storage type match bonus
        if ($item->preferred_storage_type_id && (string) $bin->storage_type_id === (string) $item->preferred_storage_type_id) {
            $score += 30;
        }

        // Capacity utilization bonus (prefer bins with some space but not too empty)
        $volumeUtilization = $bin->current_volume > 0 ? ($bin->current_volume / $bin->max_volume) : 0;
        if ($volumeUtilization >= 0.3 && $volumeUtilization <= 0.7) {
            $score += 20; // Sweet spot for space efficiency
        }

        // Fit quality (how well the item fits in available space)
        $volumeFitRatio = $requiredVolume / max($availableVolume, 0.001);
        $weightFitRatio = $requiredWeight / max($availableWeight, 0.001);
        
        // Prefer bins where item fits well (not too tight, not too loose)
        if ($volumeFitRatio >= 0.5 && $volumeFitRatio <= 0.9) {
            $score += 15;
        }
        if ($weightFitRatio >= 0.5 && $weightFitRatio <= 0.9) {
            $score += 10;
        }

        // Penalize if bin is nearly full
        if ($volumeUtilization > 0.9) {
            $score -= 20;
        }

        // Check if similar items are in same bin (for proximity)
        $similarItemsCount = \App\Models\Client\Inventory::where('bin_id', $bin->_id)
            ->where('item_id', '!=', $item->_id)
            ->where(function($q) use ($item) {
                $q->where('item_id', $item->_id)
                  ->orWhereIn('item_id', function($subQ) use ($item) {
                      // Items from same category
                      if ($item->category_id) {
                          $subQ->select('_id')
                               ->from('items')
                               ->where('category_id', $item->category_id);
                      }
                  });
            })
            ->count();

        if ($similarItemsCount > 0) {
            $score += 10; // Bonus for proximity
        }

        return $score;
    }

    /**
     * Validate if item can be putaway in specified bin
     * 
     * @param string $binId
     * @param string $itemId
     * @param float $quantity
     * @return array
     */
    public function validatePutaway($binId, $itemId, $quantity)
    {
        $bin = Bin::find($binId);
        $item = Item::find($itemId);

        if (!$bin || !$item) {
            return [
                'valid' => false,
                'message' => 'Bin or item not found'
            ];
        }

        // Check bin status
        if ($bin->status !== 'available' || !$bin->is_active) {
            return [
                'valid' => false,
                'message' => 'Bin is not available for putaway'
            ];
        }

        // Calculate required capacity
        $volumePerUnit = $item->volume_per_unit ?? $item->volume ?? 0.01;
        $weightPerUnit = $item->weight_per_unit ?? $item->weight ?? 0.01;
        
        $requiredVolume = $quantity * $volumePerUnit;
        $requiredWeight = $quantity * $weightPerUnit;

        // Check available capacity
        $availableVolume = ($bin->max_volume ?? 0) - ($bin->current_volume ?? 0);
        $availableWeight = ($bin->max_weight ?? 0) - ($bin->current_weight ?? 0);

        if ($availableVolume < $requiredVolume) {
            return [
                'valid' => false,
                'message' => "Insufficient volume capacity. Required: {$requiredVolume}, Available: {$availableVolume}"
            ];
        }

        if ($availableWeight < $requiredWeight) {
            return [
                'valid' => false,
                'message' => "Insufficient weight capacity. Required: {$requiredWeight}, Available: {$availableWeight}"
            ];
        }

        // Check storage type compatibility
        if ($item->preferred_storage_type_id && (string) $bin->storage_type_id !== (string) $item->preferred_storage_type_id) {
            return [
                'valid' => true,
                'warning' => 'Bin storage type does not match item preferred storage type'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Bin is valid for putaway'
        ];
    }

    /**
     * Execute putaway - creates inventory record and transaction
     * This is a helper method, actual execution is done in InventoryMovementService
     */
    public function executePutaway($inventoryData)
    {
        // This method is kept for backward compatibility
        // Actual putaway logic is in InventoryMovementService
        return true;
    }
}

