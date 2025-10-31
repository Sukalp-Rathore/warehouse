<?php

namespace App\Services;

use App\Models\Client\Bin;
use App\Models\Client\Warehouse;
use App\Models\Client\StorageType;
use App\Models\Client\Section;
use Illuminate\Support\Facades\Log;

class BinGenerationService
{
    /**
     * Generate bins for a warehouse
     */
    public function generateBins($warehouseId, $storageTypeId, $count, $prefix = 'BIN-', $options = [])
    {
        try {
            Log::info('BinGenerationService: Starting bin generation', [
                'warehouse_id' => $warehouseId,
                'storage_type_id' => $storageTypeId,
                'count' => $count,
                'prefix' => $prefix
            ]);

            $warehouse = Warehouse::findOrFail($warehouseId);
            $storageType = StorageType::findOrFail($storageTypeId);

            // Validate storage type belongs to warehouse
            if ($storageType->warehouse_id && $storageType->warehouse_id !== $warehouseId) {
                throw new \Exception('Storage type does not belong to this warehouse');
            }

            $binVolume = $options['bin_volume'] ?? 1.0;
            $binWeight = $options['bin_weight'] ?? 100.0;
            $assignZones = $options['assign_zones'] ?? true;
            $zoneRotation = $options['zone_rotation'] ?? ['A', 'B', 'C', 'D'];
            $assignSections = $options['assign_sections'] ?? false;
            $sectionId = $options['section_id'] ?? null;
            $generateCoordinates = $options['generate_coordinates'] ?? false;
            $startNumber = $options['start_number'] ?? 1;

            $bins = [];
            $existingCount = Bin::where('warehouse_id', $warehouseId)
                               ->where('storage_type_id', $storageTypeId)
                               ->count();

            for ($i = 0; $i < $count; $i++) {
                $binNumber = $startNumber + $i;
                $binCode = $prefix . str_pad($binNumber, 3, '0', STR_PAD_LEFT);

                // Check if bin code already exists
                $existingBin = Bin::where('bin_code', $binCode)
                                 ->where('warehouse_id', $warehouseId)
                                 ->first();

                if ($existingBin) {
                    Log::warning('BinGenerationService: Bin code already exists, skipping', [
                        'bin_code' => $binCode
                    ]);
                    continue;
                }

                // Assign zone
                $zoneCode = null;
                if ($assignZones && !empty($zoneRotation)) {
                    $zoneCode = $zoneRotation[$i % count($zoneRotation)];
                }

                // Generate coordinates if enabled
                $coordinates = null;
                if ($generateCoordinates) {
                    $coordinates = $this->generateCoordinates($i, $options);
                }

                $bin = Bin::create([
                    'bin_code' => $binCode,
                    'bin_name' => 'Bin ' . $binNumber,
                    'warehouse_id' => $warehouseId,
                    'storage_type_id' => $storageTypeId,
                    'section_id' => $assignSections ? $sectionId : null,
                    'zone_code' => $zoneCode,
                    'coordinates' => $coordinates,
                    'max_volume' => $binVolume,
                    'max_weight' => $binWeight,
                    'current_volume' => 0,
                    'current_weight' => 0,
                    'status' => 'available',
                    'is_active' => true,
                    'created_by' => session('user._id'),
                ]);

                $bins[] = $bin;

                // Log progress for large batches
                if (($i + 1) % 50 == 0) {
                    Log::info('BinGenerationService: Generated bins', [
                        'progress' => $i + 1,
                        'total' => $count
                    ]);
                }
            }

            Log::info('BinGenerationService: Bin generation completed', [
                'warehouse_id' => $warehouseId,
                'total_generated' => count($bins),
                'requested' => $count
            ]);

            return [
                'success' => true,
                'generated' => count($bins),
                'requested' => $count,
                'bins' => $bins
            ];

        } catch (\Exception $e) {
            Log::error('BinGenerationService: Generation failed', [
                'error' => $e->getMessage(),
                'warehouse_id' => $warehouseId,
                'storage_type_id' => $storageTypeId
            ]);
            throw $e;
        }
    }

    /**
     * Generate coordinates for a bin based on index
     */
    private function generateCoordinates($index, $options)
    {
        $coordinatePattern = $options['coordinate_pattern'] ?? 'rack'; // rack, shelf, flat
        
        switch ($coordinatePattern) {
            case 'rack':
                // Rack-style: Row A, Rack 1, Level 2, Position 3
                $rows = ['A', 'B', 'C', 'D', 'E', 'F'];
                $rowIndex = floor($index / 100);
                $rackIndex = floor(($index % 100) / 10);
                $levelIndex = ($index % 10) % 5;
                $positionIndex = floor(($index % 10) / 5);

                return [
                    'row' => $rows[$rowIndex % count($rows)],
                    'rack' => (string)($rackIndex + 1),
                    'level' => (string)($levelIndex + 1),
                    'position' => (string)($positionIndex + 1)
                ];

            case 'shelf':
                // Shelf-style: Section A, Shelf 1, Position 3
                $sections = ['A', 'B', 'C', 'D'];
                $sectionIndex = floor($index / 25);
                $shelfIndex = floor(($index % 25) / 5);
                $positionIndex = $index % 5;

                return [
                    'section' => $sections[$sectionIndex % count($sections)],
                    'shelf' => (string)($shelfIndex + 1),
                    'position' => (string)($positionIndex + 1)
                ];

            case 'flat':
                // Flat storage: Zone A, Bay 1, Position 3
                $zones = ['A', 'B', 'C', 'D'];
                $zoneIndex = floor($index / 50);
                $bayIndex = floor(($index % 50) / 10);
                $positionIndex = $index % 10;

                return [
                    'zone' => $zones[$zoneIndex % count($zones)],
                    'bay' => (string)($bayIndex + 1),
                    'position' => (string)($positionIndex + 1)
                ];

            default:
                return null;
        }
    }

    /**
     * Bulk update bin status
     */
    public function bulkUpdateStatus($binIds, $status)
    {
        $updated = Bin::whereIn('_id', $binIds)
                     ->update([
                         'status' => $status,
                         'updated_at' => now()
                     ]);

        Log::info('BinGenerationService: Bulk status update', [
            'updated' => $updated,
            'status' => $status
        ]);

        return $updated;
    }

    /**
     * Bulk assign zones
     */
    public function bulkAssignZones($binIds, $zoneCode)
    {
        $updated = Bin::whereIn('_id', $binIds)
                     ->update([
                         'zone_code' => $zoneCode,
                         'updated_at' => now()
                     ]);

        return $updated;
    }
}

