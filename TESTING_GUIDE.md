# Item Management Testing Guide

## Prerequisites
Before testing, ensure you have:
- ✅ Logged in as Tenant Admin
- ✅ Created at least one Plant
- ✅ Created at least one Warehouse
- ✅ Created at least one Storage Type
- ✅ Created at least one Section (if using High Rack storage)
- ✅ Created at least one Bin
- ✅ Created at least one Vendor
- ✅ Created at least one Item Category

## Complete Testing Flow

### **Step 1: Create an Item (Item Master)**

1. Navigate to: **Item Master** → **Add New Item**
2. Fill in the form:
   - Item Code: `TEST-ITEM-001`
   - Description: `Test Item Description`
   - SKU: `SKU-001`
   - UOM: `PCS` or `KG` or `L`
   - Type: `RM` (Raw Material), `FG` (Finished Goods), or `WIP`
   - Weight per Unit: `0.5` (kg)
   - Volume per Unit: `0.01` (cubic meters)
   - Category: Select from dropdown
   - Vendor: Select a vendor
   - Preferred Storage Type: Select a storage type (optional)
   - Reorder Level: `10`
   - Barcode Format: `AUTO`
   - Check "Batch Tracking" if required
3. Click **Save**
4. ✅ **Expected Result**: Item created successfully, redirected to item list

---

### **Step 2: Create a GRN (Goods Receipt Note)**

1. Navigate to: **GRN Management** → **Create GRN**
2. Fill in the form:
   - Vendor: Select the vendor you created
   - Warehouse: Select your warehouse
   - PO Reference: `PO-2025-001` (optional)
   - Items: Click "Add Item" button
     - Select Item: Choose the item created in Step 1
     - Quantity: `100` (or any number)
     - Repeat for more items if needed
3. Click **Create GRN**
4. ✅ **Expected Result**: GRN created with status "Pending"

---

### **Step 3: Approve GRN**

1. From GRN list, find the GRN you just created
2. Click **View** (eye icon)
3. Click **Approve GRN** button
4. ✅ **Expected Result**: 
   - GRN status changes to "In Progress"
   - Putaway status becomes "Pending"
   - "Start Putaway" button appears

---

### **Step 4: Putaway Process (Place Items in Bins)**

1. Click **Putaway** button (or navigate from GRN details)
2. For each item in the GRN:
   - Click **"Get Bin Suggestions"** button
   - ✅ **Expected**: System suggests best bins based on item properties
   - Select a suggested bin OR manually select a bin from dropdown
   - Enter quantity (must be ≤ remaining quantity)
   - Batch Information:
     - Select "Create New Batch"
     - Batch ID: `BATCH-2025-001`
     - Manufacturing Date: Today's date
     - Expiry Date: Future date (optional)
   - Click **Putaway Item**
3. ✅ **Expected Result**: 
   - Item placed in selected bin
   - Success message displayed
   - Inventory record created
   - Barcode, QR Code, RFID Tag auto-generated
   - Progress bar updates

---

### **Step 5: Manual Item Placement (Alternative)**

1. Navigate to: **Inventory Management** → **Manual Placement**
2. Fill in:
   - Item: Select an item
   - Warehouse: Select warehouse
   - Bin: Select a bin
   - Quantity: `50`
   - Batch: Create new or use existing
3. Click **Place Item**
4. ✅ **Expected Result**: Item placed in bin with auto-generated codes

---

### **Step 6: View Inventory**

1. Navigate to: **Inventory Management**
2. You should see all inventory records
3. Click on any inventory record (eye icon)
4. ✅ **Expected Result**: 
   - See full details:
     - Item information
     - Location path (Plant → Warehouse → Storage → Section → Bin)
     - Stock quantities (Total, Available, Blocked, Reserved)
     - Batch information
     - Barcode/QR/RFID codes
     - Transaction history

---

### **Step 7: Transfer Items Between Bins**

1. From Inventory details page, click **Transfer** button
2. Fill in:
   - Destination Bin: Select a different bin
   - Quantity: Enter quantity (≤ available quantity)
   - Notes: "Testing transfer" (optional)
3. Click **Transfer Items**
4. ✅ **Expected Result**: 
   - Quantity deducted from source bin
   - Quantity added to destination bin
   - Transaction recorded
   - Bin capacity updated
   - Source inventory updated or removed

---

### **Step 8: Stock Adjustment**

1. From Inventory details, click **Adjust Stock**
2. Fill in:
   - Adjustment Type: **Increase** or **Decrease**
   - Quantity: `10`
   - Reason: "Stock correction for testing"
3. Click **Apply Adjustment**
4. ✅ **Expected Result**: 
   - Stock quantity updated
   - Transaction recorded
   - Bin capacity recalculated

---

### **Step 9: QC Hold/Release**

**QC Hold:**
1. From Inventory details, click **QC Hold**
2. Fill in:
   - Hold Bin: Optional (different bin for QC)
   - Notes: "Quality check required"
3. Click **Move to QC Hold**
4. ✅ **Expected Result**: 
   - Status changes to "QC Hold"
   - Available quantity → Blocked quantity

**QC Release:**
1. Click **QC Release** button
2. Fill in:
   - Destination Bin: Select bin for release
   - Notes: "QC passed"
3. Click **Release from QC**
4. ✅ **Expected Result**: 
   - Status changes to "Available"
   - Blocked quantity → Available quantity

---

### **Step 10: Locate Item**

1. Navigate to: **Locate Item**
2. Enter one of:
   - Item Code: `TEST-ITEM-001`
   - Barcode: Copy from inventory details
   - QR Code: Copy from inventory details
   - RFID Tag: Copy from inventory details
3. Click **Search**
4. ✅ **Expected Result**: 
   - Results show all locations where item exists
   - Shows quantity, status, batch info for each location
   - Clickable links to view inventory details

---

### **Step 11: Batch Management**

1. Navigate to: **Batch Management**
2. View batch list:
   - See all batches
   - Filter by item, status, expiring soon
3. Click on any batch (eye icon)
4. ✅ **Expected Result**: 
   - Batch details:
     - Batch ID, Item, Manufacturing/Expiry dates
     - Days until expiry
     - Remaining quantity
     - All inventory locations for this batch

---

## Testing Checklist

### ✅ Functional Tests

- [ ] Item creation works
- [ ] GRN creation with multiple items works
- [ ] GRN approval works
- [ ] Putaway with bin suggestions works
- [ ] Putaway with manual bin selection works
- [ ] Batch creation during putaway works
- [ ] Manual item placement works
- [ ] Inventory listing and filtering works
- [ ] Inventory detail view shows all information
- [ ] Transfer between bins works
- [ ] Stock adjustment (increase) works
- [ ] Stock adjustment (decrease) works
- [ ] QC Hold works
- [ ] QC Release works
- [ ] Item location search by item code works
- [ ] Item location search by barcode works
- [ ] Item location search by QR code works
- [ ] Item location search by RFID works
- [ ] Batch listing and filtering works
- [ ] Batch detail view shows all locations
- [ ] Transaction history displays correctly

### ✅ Validation Tests

- [ ] Cannot putaway more quantity than in GRN
- [ ] Cannot transfer more than available quantity
- [ ] Cannot decrease stock below zero
- [ ] Bin capacity validation works (weight/volume)
- [ ] Required fields validation works
- [ ] Batch expiry date must be after manufacturing date
- [ ] Warehouse must belong to selected plant

### ✅ Data Integrity Tests

- [ ] Inventory transactions are recorded
- [ ] Bin capacity updates correctly on movements
- [ ] Batch quantities match inventory totals
- [ ] Location paths display correctly
- [ ] Codes (barcode/QR/RFID) are unique and generated
- [ ] Status changes reflect correctly

### ✅ UI/UX Tests

- [ ] All navigation links work
- [ ] Flash messages display correctly
- [ ] Error messages are clear
- [ ] Forms are responsive
- [ ] AJAX calls work (bin suggestions, batch loading)
- [ ] Tables paginate correctly
- [ ] Filters work correctly

---

## Common Issues & Solutions

### Issue: "Bin suggestions not showing"
**Solution**: 
- Ensure warehouse has bins created
- Check bin status is "available"
- Verify item has weight/volume defined
- Check browser console for JavaScript errors

### Issue: "Batch not loading in dropdown"
**Solution**:
- Ensure route `/tenant/items/{id}/batches` is accessible
- Check item has batches created
- Verify AJAX call in browser network tab

### Issue: "Transfer fails with capacity error"
**Solution**:
- Check destination bin has enough capacity
- Verify item weight/volume are set correctly
- Ensure bin max_volume/max_weight are set

### Issue: "Location search returns no results"
**Solution**:
- Ensure inventory records exist
- Check search code matches exactly
- Verify barcode/QR/RFID codes were generated

### Issue: "Transaction history not showing"
**Solution**:
- Verify transactions are being created
- Check transaction timestamp is set
- Ensure user_id is in session

---

## Quick Test Scenario (Fast Path)

If you want a quick test:

1. **Create Item**: `TEST-001` with basic info
2. **Create GRN**: Add TEST-001, quantity 100
3. **Approve GRN**
4. **Putaway**: Use suggestions, place 100 units
5. **View Inventory**: Check details
6. **Locate**: Search by item code
7. ✅ **Done!**

---

## Next Steps After Testing

1. Document any bugs found
2. Test edge cases (large quantities, multiple locations, etc.)
3. Test with real data volumes
4. Performance testing with large datasets
5. User acceptance testing with end users

