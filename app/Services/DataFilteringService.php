<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use MongoDB\Laravel\Eloquent\Builder as MongoBuilder;

class DataFilteringService
{
    /**
     * Get the selected plant ID from session
     */
    public static function getSelectedPlantId()
    {
        return session('selected_plant_id');
    }

    /**
     * Get the selected warehouse ID from session
     */
    public static function getSelectedWarehouseId()
    {
        return session('selected_warehouse_id');
    }

    /**
     * Get the selected plant name from session
     */
    public static function getSelectedPlantName()
    {
        return session('selected_plant_name');
    }

    /**
     * Get the selected warehouse name from session
     */
    public static function getSelectedWarehouseName()
    {
        return session('selected_warehouse_name');
    }

    /**
     * Check if plant and warehouse are selected
     */
    public static function hasPlantWarehouseSelected()
    {
        return !empty(session('selected_plant_id')) && !empty(session('selected_warehouse_id'));
    }

    /**
     * Apply warehouse filter to a query
     * 
     * @param Builder|MongoBuilder $query
     * @param string $warehouseField The field name for warehouse_id (default: 'warehouse_id')
     * @return Builder|MongoBuilder
     */
    public static function filterByWarehouse($query, $warehouseField = 'warehouse_id')
    {
        $warehouseId = self::getSelectedWarehouseId();
        
        if ($warehouseId) {
            return $query->where($warehouseField, $warehouseId);
        }
        
        return $query;
    }

    /**
     * Apply plant filter to a query
     * 
     * @param Builder|MongoBuilder $query
     * @param string $plantField The field name for plant_id (default: 'plant_id')
     * @return Builder|MongoBuilder
     */
    public static function filterByPlant($query, $plantField = 'plant_id')
    {
        $plantId = self::getSelectedPlantId();
        
        if ($plantId) {
            return $query->where($plantField, $plantId);
        }
        
        return $query;
    }

    /**
     * Apply both plant and warehouse filters to a query
     * 
     * @param Builder|MongoBuilder $query
     * @param string $plantField The field name for plant_id (default: 'plant_id')
     * @param string $warehouseField The field name for warehouse_id (default: 'warehouse_id')
     * @return Builder|MongoBuilder
     */
    public static function filterByPlantAndWarehouse($query, $plantField = 'plant_id', $warehouseField = 'warehouse_id')
    {
        $query = self::filterByPlant($query, $plantField);
        $query = self::filterByWarehouse($query, $warehouseField);
        
        return $query;
    }

    /**
     * Filter warehouses by user's warehouse access
     * Only show warehouses that user has access to
     * 
     * @param Builder|MongoBuilder $query
     * @param array $userWarehouseAccess Array of warehouse IDs user has access to
     * @return Builder|MongoBuilder
     */
    public static function filterWarehousesByUserAccess($query, $userWarehouseAccess)
    {
        if (!empty($userWarehouseAccess)) {
            return $query->whereIn('_id', $userWarehouseAccess);
        }
        
        // If user has no warehouse access, return empty result
        return $query->where('_id', 'nonexistent_id_that_will_never_match');
    }

    /**
     * Filter plants by user's plant access
     * Only show plants that user has access to
     * 
     * @param Builder|MongoBuilder $query
     * @param array $userPlantAccess Array of plant IDs user has access to
     * @return Builder|MongoBuilder
     */
    public static function filterPlantsByUserAccess($query, $userPlantAccess)
    {
        if (!empty($userPlantAccess)) {
            return $query->whereIn('_id', $userPlantAccess);
        }
        
        // If user has no plant access, return empty result
        return $query->where('_id', 'nonexistent_id_that_will_never_match');
    }

    /**
     * Get filter context for display purposes
     * 
     * @return array
     */
    public static function getFilterContext()
    {
        return [
            'plant_id' => self::getSelectedPlantId(),
            'plant_name' => self::getSelectedPlantName(),
            'warehouse_id' => self::getSelectedWarehouseId(),
            'warehouse_name' => self::getSelectedWarehouseName(),
            'has_selection' => self::hasPlantWarehouseSelected(),
        ];
    }
}

