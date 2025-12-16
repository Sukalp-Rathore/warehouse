<?php

namespace App\Services;

use App\Models\Client\Item;
use App\Models\Client\Vendor;
use App\Models\Client\StorageType;
use App\Models\Client\ItemCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ItemImportExportService
{
    /**
     * Export items to CSV
     */
    public function exportToCSV($items, $filePath = null)
    {
        if (!$filePath) {
            $filePath = storage_path('app/exports/items_' . date('Y-m-d_His') . '.csv');
        }

        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = fopen($filePath, 'w');

        // Add CSV headers
        fputcsv($file, [
            'Item Code',
            'Description',
            'Short Description',
            'Category',
            'Subcategory',
            'UOM',
            'Weight',
            'Length',
            'Width',
            'Height',
            'Volume',
            'Vendor Code',
            'Preferred Storage Type',
            'Minimum Order Qty',
            'Reorder Level',
            'Reorder Quantity',
            'Unit Price',
            'Currency',
            'Barcode',
            'Hazmat',
            'Hazmat Class',
            'Temperature Controlled'
        ]);

        // Add data rows
        foreach ($items as $item) {
            $vendor = $item->vendor;
            $storageType = $item->preferredStorageType;
            $dimensions = $item->dimensions ?? [];

            fputcsv($file, [
                $item->item_code,
                $item->description,
                $item->short_description ?? '',
                $item->category ?? '',
                $item->subcategory ?? '',
                $item->uom ?? '',
                $item->weight ?? 0,
                $dimensions['length'] ?? '',
                $dimensions['width'] ?? '',
                $dimensions['height'] ?? '',
                $item->volume ?? 0,
                $vendor ? $vendor->vendor_code : '',
                $storageType ? $storageType->code : '',
                $item->minimum_order_quantity ?? 0,
                $item->reorder_level ?? 0,
                $item->reorder_quantity ?? 0,
                $item->unit_price ?? 0,
                $item->currency ?? 'USD',
                $item->barcode ?? '',
                $item->is_hazmat ? 'Yes' : 'No',
                $item->hazmat_class ?? '',
                $item->temperature_controlled ? 'Yes' : 'No'
            ]);
        }

        fclose($file);

        return $filePath;
    }

    /**
     * Export items to Excel (using simple CSV for now, can be enhanced with PhpSpreadsheet)
     */
    public function exportToExcel($items)
    {
        // For now, export as CSV (can be enhanced with maatwebsite/excel package)
        return $this->exportToCSV($items);
    }

    /**
     * Import items from CSV
     */
    public function importFromCSV($filePath, $options = [])
    {
        $errors = [];
        $success = [];
        $lineNumber = 1;

        $skipFirstRow = $options['skip_first_row'] ?? true;
        $updateExisting = $options['update_existing'] ?? false;

        if (!file_exists($filePath)) {
            throw new \Exception('File not found: ' . $filePath);
        }

        $file = fopen($filePath, 'r');

        if ($skipFirstRow) {
            fgetcsv($file); // Skip header
            $lineNumber++;
        }

        while (($row = fgetcsv($file)) !== false) {
            $lineNumber++;
            
            try {
                $itemData = $this->parseCSVRow($row);
                $validation = $this->validateItemData($itemData);

                if ($validation->fails()) {
                    $errors[] = [
                        'line' => $lineNumber,
                        'item_code' => $itemData['item_code'] ?? 'Unknown',
                        'errors' => $validation->errors()->all()
                    ];
                    continue;
                }

                // Check if item exists
                $existingItem = Item::where('item_code', $itemData['item_code'])->first();

                if ($existingItem && !$updateExisting) {
                    $errors[] = [
                        'line' => $lineNumber,
                        'item_code' => $itemData['item_code'],
                        'errors' => ['Item code already exists']
                    ];
                    continue;
                }

                // Resolve relationships
                $this->resolveItemRelationships($itemData);

                // Create or update item
                if ($existingItem && $updateExisting) {
                    $existingItem->update($itemData);
                    $item = $existingItem;
                } else {
                    $itemData['created_by'] = session('user._id');
                    $item = Item::create($itemData);
                }

                $success[] = [
                    'line' => $lineNumber,
                    'item_code' => $item->item_code,
                    'action' => $existingItem ? 'updated' : 'created'
                ];

            } catch (\Exception $e) {
                $errors[] = [
                    'line' => $lineNumber,
                    'item_code' => $row[0] ?? 'Unknown',
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        fclose($file);

        Log::info('ItemImportExportService: Import completed', [
            'success' => count($success),
            'errors' => count($errors)
        ]);

        return [
            'success' => count($success),
            'errors' => count($errors),
            'details' => [
                'successful' => $success,
                'failed' => $errors
            ]
        ];
    }

    /**
     * Parse CSV row into item data array
     */
    private function parseCSVRow($row)
    {
        return [
            'item_code' => $row[0] ?? '',
            'description' => $row[1] ?? '',
            'short_description' => $row[2] ?? '',
            'category' => $row[3] ?? '',
            'subcategory' => $row[4] ?? '',
            'uom' => $row[5] ?? 'PCS',
            'weight' => floatval($row[6] ?? 0),
            'dimensions' => [
                'length' => floatval($row[7] ?? 0),
                'width' => floatval($row[8] ?? 0),
                'height' => floatval($row[9] ?? 0),
                'unit' => 'cm'
            ],
            'volume' => floatval($row[10] ?? 0),
            'vendor_code' => $row[11] ?? '',
            'preferred_storage_type_code' => $row[12] ?? '',
            'minimum_order_quantity' => intval($row[13] ?? 0),
            'reorder_level' => intval($row[14] ?? 0),
            'reorder_quantity' => intval($row[15] ?? 0),
            'unit_price' => floatval($row[16] ?? 0),
            'currency' => $row[17] ?? 'USD',
            'barcode' => $row[18] ?? '',
            'is_hazmat' => strtoupper($row[19] ?? 'No') === 'YES',
            'hazmat_class' => $row[20] ?? null,
            'temperature_controlled' => strtoupper($row[21] ?? 'No') === 'YES'
        ];
    }

    /**
     * Validate item data
     */
    private function validateItemData($data)
    {
        return Validator::make($data, [
            'item_code' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'uom' => 'required|string|max:20',
            'weight' => 'nullable|numeric|min:0',
            'volume' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
        ]);
    }

    /**
     * Resolve relationships (vendor, storage type, category)
     */
    private function resolveItemRelationships(&$data)
    {
        // Resolve vendor
        if (!empty($data['vendor_code'])) {
            $vendor = Vendor::where('vendor_code', $data['vendor_code'])->first();
            if ($vendor) {
                $data['vendor_id'] = $vendor->_id;
            }
            unset($data['vendor_code']);
        }

        // Resolve storage type
        if (!empty($data['preferred_storage_type_code'])) {
            $storageType = StorageType::where('code', $data['preferred_storage_type_code'])->first();
            if ($storageType) {
                $data['preferred_storage_type_id'] = $storageType->_id;
            }
            unset($data['preferred_storage_type_code']);
        }

        // Resolve category (find or create)
        if (!empty($data['category'])) {
            $category = ItemCategory::where('category_name', $data['category'])
                                   ->orWhere('category_code', Str::upper($data['category']))
                                   ->first();
            
            if ($category) {
                $data['category_id'] = $category->_id;
            } else {
                // Create category if doesn't exist
                $category = ItemCategory::create([
                    'category_code' => Str::upper($data['category']),
                    'category_name' => $data['category'],
                    'is_active' => true,
                    'created_by' => session('user._id')
                ]);
                $data['category_id'] = $category->_id;
            }
        }
    }

    /**
     * Generate import template
     */
    public function generateTemplate($filePath = null)
    {
        if (!$filePath) {
            $filePath = storage_path('app/templates/item_import_template.csv');
        }

        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = fopen($filePath, 'w');

        // Add headers with sample data row
        fputcsv($file, [
            'Item Code',
            'Description',
            'Short Description',
            'Category',
            'Subcategory',
            'UOM',
            'Weight',
            'Length',
            'Width',
            'Height',
            'Volume',
            'Vendor Code',
            'Preferred Storage Type',
            'Minimum Order Qty',
            'Reorder Level',
            'Reorder Quantity',
            'Unit Price',
            'Currency',
            'Barcode',
            'Hazmat',
            'Hazmat Class',
            'Temperature Controlled'
        ]);

        // Add sample row
        fputcsv($file, [
            'ITEM-001',
            'Sample Product Description',
            'Sample Product',
            'Electronics',
            'Components',
            'PCS',
            '0.5',
            '10',
            '5',
            '3',
            '0.15',
            'VEND-001',
            'RACK',
            '100',
            '50',
            '200',
            '10.50',
            'USD',
            '',
            'No',
            '',
            'No'
        ]);

        fclose($file);

        return $filePath;
    }
}

