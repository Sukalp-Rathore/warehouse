<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGeneratorPNG;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Client\Inventory;
use Illuminate\Support\Facades\Log;

class BarcodeGenerationService
{
    /**
     * Generate unique barcode number
     * Format: PREFIX-ITEMCODE-WAREHOUSE-BIN-TIMESTAMP-RANDOM
     */
    public function generateBarcodeNumber($itemCode, $warehouseId = null, $binId = null, $prefix = 'BC', $maxAttempts = 10)
    {
        $attempt = 0;
        $timestamp = date('Ymd');
        $warehousePart = $warehouseId ? substr((string)$warehouseId, -4) : '';
        $binPart = $binId ? substr((string)$binId, -4) : '';
        
        do {
            $random = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $barcode = strtoupper($prefix . '-' . 
                str_replace([' ', '-'], '', substr($itemCode, 0, 10)) . '-' . 
                ($warehousePart ? $warehousePart . '-' : '') . 
                ($binPart ? $binPart . '-' : '') . 
                $timestamp . '-' . $random);
            
            // Check for uniqueness
            $exists = Inventory::where('barcode', $barcode)->exists();
            $attempt++;
            
            if ($attempt >= $maxAttempts) {
                Log::error('BarcodeGenerationService: Failed to generate unique barcode after ' . $maxAttempts . ' attempts');
                // Fallback: use microtime for more uniqueness
                $barcode = strtoupper($prefix . '-' . 
                    str_replace([' ', '-'], '', substr($itemCode, 0, 10)) . '-' . 
                    ($warehousePart ? $warehousePart . '-' : '') . 
                    ($binPart ? $binPart . '-' : '') . 
                    $timestamp . '-' . substr(str_replace('.', '', microtime(true)), -8));
            }
        } while ($exists && $attempt < $maxAttempts);
        
        return $barcode;
    }

    /**
     * Generate barcode image (PNG)
     */
    public function generateBarcodeImage($barcodeNumber, $type = BarcodeGeneratorPNG::TYPE_CODE_128)
    {
        $generator = new BarcodeGeneratorPNG();
        return base64_encode($generator->getBarcode($barcodeNumber, $type));
    }

    /**
     * Generate unique QR code number
     * Format: QR-ITEMCODE-WAREHOUSE-BIN-TIMESTAMP-RANDOM
     */
    public function generateQRCodeNumber($itemCode, $warehouseId = null, $binId = null, $maxAttempts = 10)
    {
        $attempt = 0;
        $timestamp = date('YmdHis');
        $warehousePart = $warehouseId ? substr((string)$warehouseId, -4) : '';
        $binPart = $binId ? substr((string)$binId, -4) : '';
        
        do {
            $random = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $qrCode = strtoupper('QR-' . 
                str_replace([' ', '-'], '', substr($itemCode, 0, 10)) . '-' . 
                ($warehousePart ? $warehousePart . '-' : '') . 
                ($binPart ? $binPart . '-' : '') . 
                $timestamp . '-' . $random);
            
            // Check for uniqueness
            $exists = Inventory::where('qr_code', $qrCode)->exists();
            $attempt++;
            
            if ($attempt >= $maxAttempts) {
                Log::error('BarcodeGenerationService: Failed to generate unique QR code after ' . $maxAttempts . ' attempts');
                // Fallback: use microtime for more uniqueness
                $qrCode = strtoupper('QR-' . 
                    str_replace([' ', '-'], '', substr($itemCode, 0, 10)) . '-' . 
                    ($warehousePart ? $warehousePart . '-' : '') . 
                    ($binPart ? $binPart . '-' : '') . 
                    $timestamp . '-' . substr(str_replace('.', '', microtime(true)), -8));
            }
        } while ($exists && $attempt < $maxAttempts);
        
        return $qrCode;
    }

    /**
     * Generate QR code image (SVG)
     */
    public function generateQRCodeImage($qrCodeNumber)
    {
        return QrCode::size(200)
                    ->generate($qrCodeNumber);
    }

    /**
     * Generate unique RFID tag number
     * Format: RFID-ITEMCODE-WAREHOUSE-BIN-TIMESTAMP-RANDOM
     */
    public function generateRFIDTag($itemCode, $warehouseId = null, $binId = null, $maxAttempts = 10)
    {
        $attempt = 0;
        $timestamp = date('YmdHis');
        $warehousePart = $warehouseId ? substr((string)$warehouseId, -4) : '';
        $binPart = $binId ? substr((string)$binId, -4) : '';
        
        do {
            $random = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $rfidTag = strtoupper('RFID-' . 
                str_replace([' ', '-'], '', substr($itemCode, 0, 10)) . '-' . 
                ($warehousePart ? $warehousePart . '-' : '') . 
                ($binPart ? $binPart . '-' : '') . 
                $timestamp . '-' . $random);
            
            // Check for uniqueness
            $exists = Inventory::where('rfid_tag', $rfidTag)->exists();
            $attempt++;
            
            if ($attempt >= $maxAttempts) {
                Log::error('BarcodeGenerationService: Failed to generate unique RFID tag after ' . $maxAttempts . ' attempts');
                // Fallback: use microtime for more uniqueness
                $rfidTag = strtoupper('RFID-' . 
                    str_replace([' ', '-'], '', substr($itemCode, 0, 10)) . '-' . 
                    ($warehousePart ? $warehousePart . '-' : '') . 
                    ($binPart ? $binPart . '-' : '') . 
                    $timestamp . '-' . substr(str_replace('.', '', microtime(true)), -8));
            }
        } while ($exists && $attempt < $maxAttempts);
        
        return $rfidTag;
    }

    /**
     * Generate all three codes for an inventory record with uniqueness guarantee
     * 
     * @param string $itemCode
     * @param string|null $warehouseId
     * @param string|null $binId
     * @return array
     */
    public function generateAllCodes($itemCode, $warehouseId = null, $binId = null)
    {
        $barcode = $this->generateBarcodeNumber($itemCode, $warehouseId, $binId);
        $qrCode = $this->generateQRCodeNumber($itemCode, $warehouseId, $binId);
        $rfidTag = $this->generateRFIDTag($itemCode, $warehouseId, $binId);

        // Final uniqueness check - ensure all three are unique
        $this->ensureAllCodesUnique($barcode, $qrCode, $rfidTag, $itemCode, $warehouseId, $binId);

        return [
            'barcode' => $barcode,
            'qr_code' => $qrCode,
            'rfid_tag' => $rfidTag,
            'barcode_image' => $this->generateBarcodeImage($barcode),
            'qr_code_image' => $this->generateQRCodeImage($qrCode),
        ];
    }

    /**
     * Ensure all codes are unique, regenerate if needed
     */
    private function ensureAllCodesUnique(&$barcode, &$qrCode, &$rfidTag, $itemCode, $warehouseId = null, $binId = null, $maxAttempts = 20)
    {
        $attempt = 0;
        
        do {
            $barcodeExists = Inventory::where('barcode', $barcode)->exists();
            $qrCodeExists = Inventory::where('qr_code', $qrCode)->exists();
            $rfidTagExists = Inventory::where('rfid_tag', $rfidTag)->exists();
            
            if ($barcodeExists) {
                $barcode = $this->generateBarcodeNumber($itemCode, $warehouseId, $binId);
            }
            if ($qrCodeExists) {
                $qrCode = $this->generateQRCodeNumber($itemCode, $warehouseId, $binId);
            }
            if ($rfidTagExists) {
                $rfidTag = $this->generateRFIDTag($itemCode, $warehouseId, $binId);
            }
            
            $attempt++;
            
            if ($attempt >= $maxAttempts) {
                Log::error('BarcodeGenerationService: Failed to ensure all codes unique after ' . $maxAttempts . ' attempts', [
                    'item_code' => $itemCode,
                    'warehouse_id' => $warehouseId,
                    'bin_id' => $binId
                ]);
                break;
            }
        } while (($barcodeExists || $qrCodeExists || $rfidTagExists) && $attempt < $maxAttempts);
    }

    /**
     * Get barcode format from tenant config
     */
    public function getBarcodeFormat()
    {
        // TODO: Get from tenant settings
        return 'CODE128'; // or 'EAN13', 'CODE39', etc.
    }
}

