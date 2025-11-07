<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGeneratorPNG;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BarcodeGenerationService
{
    /**
     * Generate barcode number
     */
    public function generateBarcodeNumber($itemCode, $prefix = 'BC')
    {
        // Format: PREFIX-ITEMCODE-TIMESTAMP-RANDOM
        $timestamp = date('Ymd');
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        return strtoupper($prefix . '-' . str_replace([' ', '-'], '', $itemCode) . '-' . $timestamp . '-' . $random);
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
     * Generate QR code number
     */
    public function generateQRCodeNumber($itemCode, $inventoryId = null)
    {
        // Format: QR-ITEMCODE-INVENTORYID
        $inventoryPart = $inventoryId ? str_pad(substr($inventoryId, -6), 6, '0', STR_PAD_LEFT) : date('His');
        return strtoupper('QR-' . str_replace([' ', '-'], '', $itemCode) . '-' . $inventoryPart);
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
     * Generate RFID tag number
     */
    public function generateRFIDTag($itemCode, $sequence = null)
    {
        // Format: RFID-ITEMCODE-SEQUENCE
        $sequence = $sequence ?? str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        return strtoupper('RFID-' . str_replace([' ', '-'], '', substr($itemCode, 0, 10)) . '-' . $sequence);
    }

    /**
     * Generate all three codes for an inventory record
     */
    public function generateAllCodes($itemCode, $inventoryId = null, $sequence = null)
    {
        $barcode = $this->generateBarcodeNumber($itemCode);
        $qrCode = $this->generateQRCodeNumber($itemCode, $inventoryId);
        $rfidTag = $this->generateRFIDTag($itemCode, $sequence);

        return [
            'barcode' => $barcode,
            'qr_code' => $qrCode,
            'rfid_tag' => $rfidTag,
            'barcode_image' => $this->generateBarcodeImage($barcode),
            'qr_code_image' => $this->generateQRCodeImage($qrCode),
        ];
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

