# Testing Plan for WMS Phases 5-10

## Overview

This document provides a comprehensive testing plan for Phases 5-10 of the WMS system, covering Transfer Orders, Outbound Operations, QC Management, Kitting, Cycle Count, and Reporting modules.

---

## Prerequisites

### Test Environment Setup

1. **Database Setup**
   - MongoDB running with master database (`wms_master`)
   - At least one tenant database created and configured
   - Test data seeded (plants, warehouses, bins, items, inventory)

2. **User Accounts**
   - Master admin user
   - Tenant admin user
   - Tenant operator user (for assignment testing)
   - Tenant QA user (for QC testing)

3. **Test Data Requirements**
   - At least 2 plants
   - At least 3 warehouses
   - At least 10 bins (with different storage types)
   - At least 20 items (with various categories)
   - At least 5 vendors
   - Inventory records in multiple bins
   - At least 5 batches with different expiry dates (for FEFO testing)

4. **Configuration**
   - Bin reorder levels configured for replenishment testing
   - QC templates created
   - Hold locations configured

---

## Phase 5: Transfer Orders (TO) + Replenishment Testing

### Test Suite 5.1: Manual Transfer Order Creation

#### Test Case 5.1.1: Create Basic Transfer Order
**Objective**: Verify manual TO creation works correctly

**Steps**:
1. Login as tenant admin
2. Navigate to Transfer Orders → Create Transfer Order
3. Select source bin (Bin A)
4. Select destination bin (Bin B)
5. Select item
6. Enter quantity (50)
7. Set priority: Normal
8. Set reason: Manual
9. Click "Create Transfer Order"

**Expected Results**:
- TO created with status "created"
- TO number generated (format: TO-000001)
- TO visible in transfer orders list
- Source bin has sufficient inventory
- Destination bin has sufficient capacity

**Validation Points**:
- ✅ TO number format correct
- ✅ Status is "created"
- ✅ All fields saved correctly
- ✅ Created by user is logged

---

#### Test Case 5.1.2: Create TO with Insufficient Source Inventory
**Objective**: Verify validation prevents TO creation when source bin lacks inventory

**Steps**:
1. Create TO with quantity > available inventory in source bin
2. Submit form

**Expected Results**:
- Validation error displayed
- TO not created
- Error message: "Insufficient inventory in source bin"

**Validation Points**:
- ✅ Validation error shown
- ✅ No TO record created
- ✅ Form retains input data

---

#### Test Case 5.1.3: Create TO with Insufficient Destination Capacity
**Objective**: Verify validation prevents TO when destination bin lacks capacity

**Steps**:
1. Select destination bin that is nearly full
2. Enter quantity that exceeds available capacity
3. Submit form

**Expected Results**:
- Validation error displayed
- Error message about insufficient capacity
- TO not created

**Validation Points**:
- ✅ Capacity validation works
- ✅ Error message clear

---

### Test Suite 5.2: Transfer Order Approval Workflow

#### Test Case 5.2.1: Approve Transfer Order
**Objective**: Verify approval workflow

**Steps**:
1. Create a TO (status: created)
2. Navigate to TO details page
3. Click "Approve"
4. Confirm approval

**Expected Results**:
- TO status changes to "approved"
- Approved by user logged
- Approved at timestamp recorded
- Approval button no longer visible

**Validation Points**:
- ✅ Status updated correctly
- ✅ Approval metadata saved
- ✅ UI reflects new status

---

#### Test Case 5.2.2: Approve Already Approved TO
**Objective**: Verify system prevents re-approval

**Steps**:
1. Approve a TO
2. Try to approve again

**Expected Results**:
- Error message: "Transfer order cannot be approved in current status"
- Status remains "approved"

**Validation Points**:
- ✅ Workflow validation works
- ✅ Status cannot be changed incorrectly

---

### Test Suite 5.3: Transfer Order Assignment

#### Test Case 5.3.1: Assign TO to Operator
**Objective**: Verify operator assignment

**Steps**:
1. Create/approve a TO
2. Edit TO or use assign action
3. Select operator from dropdown
4. Save

**Expected Results**:
- TO assigned_to field updated
- Operator name displayed in TO details
- Assignment visible in operator's task list

**Validation Points**:
- ✅ Assignment saved
- ✅ Operator can see assigned TOs

---

### Test Suite 5.4: Transfer Order Execution

#### Test Case 5.4.1: Execute Approved Transfer Order
**Objective**: Verify TO execution updates inventory correctly

**Steps**:
1. Create and approve a TO
2. Note source bin inventory quantity
3. Note destination bin current volume/weight
4. Execute TO
5. Verify inventory changes

**Expected Results**:
- TO status changes to "completed"
- Source bin inventory decreased by transfer quantity
- Destination bin inventory increased by transfer quantity
- Bin capacity (volume/weight) updated correctly
- Inventory transaction created
- Transaction type: "TRANSFER"
- Transaction references TO number

**Validation Points**:
- ✅ Inventory updated atomically
- ✅ Bin capacity updated
- ✅ Transaction logged
- ✅ No data loss or corruption

---

#### Test Case 5.4.2: Execute TO with Concurrent Inventory Changes
**Objective**: Verify transaction isolation

**Steps**:
1. Create TO for 50 units
2. Manually reduce source inventory by 30 units (simulate concurrent operation)
3. Try to execute TO

**Expected Results**:
- Execution fails if insufficient inventory
- Error message displayed
- TO status remains "approved" or "in_progress"
- No partial transfer occurred

**Validation Points**:
- ✅ Transaction isolation works
- ✅ No partial updates
- ✅ Error handling correct

---

### Test Suite 5.5: Auto-Generation of Replenishment TOs

#### Test Case 5.5.1: Auto-Generate TOs for Low Stock Bins
**Objective**: Verify automatic TO generation when inventory < reorder_level

**Prerequisites**:
- Bin with reorder_level = 20
- Bin with current inventory = 15 (below reorder level)
- Source bin with sufficient stock

**Steps**:
1. Navigate to Transfer Orders
2. Click "Auto-Generate Replenishment"
3. Confirm action
4. Check generated TOs

**Expected Results**:
- TO automatically created
- TO status: "created"
- TO reason: "replenishment"
- TO auto_generated flag: true
- Source bin selected automatically
- Quantity = bin.reorder_quantity (or reorder_level * 2)
- Notes indicate auto-generation reason

**Validation Points**:
- ✅ TO generated correctly
- ✅ Source bin has sufficient stock
- ✅ Quantity appropriate
- ✅ Metadata correct

---

#### Test Case 5.5.2: Auto-Generate with No Source Stock
**Objective**: Verify handling when no source bin available

**Prerequisites**:
- Bin below reorder level
- No other bins have sufficient stock

**Steps**:
1. Trigger auto-generation
2. Check results

**Expected Results**:
- Error logged for that bin
- No TO created for that bin
- Other eligible bins still processed
- Error summary returned

**Validation Points**:
- ✅ Graceful error handling
- ✅ Partial success handled correctly

---

#### Test Case 5.5.3: Prevent Duplicate Auto-Generated TOs
**Objective**: Verify system doesn't create duplicate TOs

**Steps**:
1. Auto-generate TOs
2. Immediately auto-generate again
3. Check for duplicates

**Expected Results**:
- No duplicate TOs created
- Existing TOs remain unchanged
- System checks for existing pending TOs

**Validation Points**:
- ✅ Duplicate prevention works
- ✅ Existing TOs not modified

---

### Test Suite 5.6: Transfer Order Cancellation

#### Test Case 5.6.1: Cancel Created TO
**Objective**: Verify cancellation of unexecuted TO

**Steps**:
1. Create TO
2. Cancel TO
3. Verify status

**Expected Results**:
- TO status: "cancelled"
- TO cannot be executed
- TO visible in list with cancelled status

**Validation Points**:
- ✅ Cancellation works
- ✅ Cancelled TOs cannot be executed

---

#### Test Case 5.6.2: Cancel Completed TO
**Objective**: Verify completed TOs cannot be cancelled

**Steps**:
1. Execute TO to completion
2. Try to cancel

**Expected Results**:
- Error: "Transfer order cannot be cancelled in current status"
- Status remains "completed"

**Validation Points**:
- ✅ Workflow validation prevents invalid state changes

---

## Phase 6: Outbound Operations Testing

### Test Suite 6.1: Delivery Order Creation

#### Test Case 6.1.1: Create Basic Delivery Order
**Objective**: Verify DO creation with multiple items

**Steps**:
1. Navigate to Delivery Orders → Create
2. Enter customer details
3. Select warehouse
4. Add items (at least 3 items with different quantities)
5. Set delivery date
6. Set priority
7. Set picking strategy: FIFO
8. Create DO

**Expected Results**:
- DO created with status "created"
- DO number generated (DO-000001)
- All items saved correctly
- DO visible in list

**Validation Points**:
- ✅ DO number format correct
- ✅ All items saved
- ✅ Customer details saved
- ✅ Dates saved correctly

---

#### Test Case 6.1.2: Create DO with SAP Reference
**Objective**: Verify SAP integration field

**Steps**:
1. Create DO
2. Enter SAP reference number
3. Save

**Expected Results**:
- SAP reference saved
- Visible in DO details
- Can be used for filtering/searching

**Validation Points**:
- ✅ SAP reference stored
- ✅ Searchable

---

### Test Suite 6.2: Picking Task Generation

#### Test Case 6.2.1: Generate Picking Tasks (FIFO Strategy)
**Objective**: Verify FIFO allocation works correctly

**Prerequisites**:
- DO with items requiring inventory
- Multiple inventory records with different created_at dates
- Sufficient inventory available

**Steps**:
1. Create DO
2. Generate picking tasks
3. Review generated tasks

**Expected Results**:
- Picking tasks created for each item/bin/batch combination
- Tasks allocated using FIFO (oldest inventory first)
- Task quantities sum to required DO quantities
- Each task has correct bin, batch, and item references
- Tasks have sequence numbers

**Validation Points**:
- ✅ FIFO allocation correct (check batch dates)
- ✅ All required quantity allocated
- ✅ No over-allocation
- ✅ Task details correct

---

#### Test Case 6.2.2: Generate Picking Tasks (FEFO Strategy)
**Objective**: Verify FEFO (First Expiry First Out) allocation

**Prerequisites**:
- DO with items
- Multiple batches with different expiry dates
- Sufficient inventory

**Steps**:
1. Create DO with picking_strategy = "fefo"
2. Generate picking tasks
3. Review allocation

**Expected Results**:
- Tasks allocated using FEFO (earliest expiry first)
- Batch with earliest expiry selected first
- Allocation respects expiry dates

**Validation Points**:
- ✅ FEFO allocation correct
- ✅ Expiry dates considered
- ✅ Oldest expiry batches used first

---

#### Test Case 6.2.3: Generate Tasks with Insufficient Inventory
**Objective**: Verify error handling when inventory insufficient

**Prerequisites**:
- DO requiring 100 units
- Only 50 units available in warehouse

**Steps**:
1. Create DO
2. Generate picking tasks

**Expected Results**:
- Error message displayed
- No tasks created
- DO status remains "created"
- Error indicates insufficient inventory

**Validation Points**:
- ✅ Error handling works
- ✅ No partial task creation
- ✅ Clear error message

---

### Test Suite 6.3: Picking Task Execution

#### Test Case 6.3.1: Execute Picking Task
**Objective**: Verify picking execution updates inventory

**Prerequisites**:
- DO with generated picking tasks
- Tasks in "pending" status

**Steps**:
1. Navigate to picking task list
2. Select a task
3. Enter picked quantity (match required quantity)
4. Execute picking
5. Verify inventory changes

**Expected Results**:
- Task status: "completed"
- Picked quantity recorded
- Picked by user logged
- Picked at timestamp recorded
- Source bin inventory decreased
- Bin capacity updated
- Inventory transaction created (type: "PICK")
- DO item picked_qty updated
- DO status changes to "picked" when all items picked

**Validation Points**:
- ✅ Inventory updated correctly
- ✅ Transaction logged
- ✅ DO status updated
- ✅ All metadata saved

---

#### Test Case 6.3.2: Partial Picking
**Objective**: Verify partial picking handling

**Steps**:
1. Execute picking with quantity < required
2. Verify task status

**Expected Results**:
- Task status remains "in_progress" or "pending"
- Picked quantity recorded
- Remaining quantity still required
- DO status remains "created" or "picked" (partial)

**Validation Points**:
- ✅ Partial picking handled
- ✅ Remaining quantity tracked
- ✅ Can complete picking later

---

#### Test Case 6.3.3: Pick Wrong Batch
**Objective**: Verify batch validation

**Steps**:
1. Try to pick from different batch than allocated
2. Execute picking

**Expected Results**:
- Validation error (if batch validation implemented)
- Or system allows but logs discrepancy

**Validation Points**:
- ✅ Batch validation works (if implemented)
- ✅ Discrepancies tracked

---

### Test Suite 6.4: Wave Picking

#### Test Case 6.4.1: Create Picking Wave
**Objective**: Verify wave picking groups multiple DOs

**Prerequisites**:
- At least 3 DOs in "created" status
- All DOs in same warehouse

**Steps**:
1. Navigate to Wave Picking
2. Select multiple DOs
3. Create wave
4. Assign to operator
5. Review wave

**Expected Results**:
- Wave created with wave number
- All selected DOs included
- Picking tasks generated for all DOs
- Tasks grouped by wave
- Wave status: "created"

**Validation Points**:
- ✅ Wave created correctly
- ✅ All DOs included
- ✅ Tasks generated
- ✅ Wave assignment works

---

#### Test Case 6.4.2: Execute Wave Picking
**Objective**: Verify wave execution

**Steps**:
1. Start wave picking
2. Execute tasks in sequence
3. Complete all tasks
4. Verify wave completion

**Expected Results**:
- Wave status: "in_progress" when started
- All tasks can be executed
- Wave status: "completed" when all tasks done
- All DOs updated to "picked"

**Validation Points**:
- ✅ Wave status updates
- ✅ Task execution works within wave
- ✅ Wave completion tracked

---

### Test Suite 6.5: Shipment and Dispatch

#### Test Case 6.5.1: Create Shipment Manifest
**Objective**: Verify manifest generation

**Prerequisites**:
- DO in "picked" status
- All items picked

**Steps**:
1. Navigate to DO details
2. Create shipment
3. Enter vehicle details
4. Enter driver information
5. Generate manifest

**Expected Results**:
- Shipment created with shipment number
- Manifest generated with all items
- Total weight calculated
- Total volume calculated
- DO status: "dispatched"
- Vehicle and driver details saved
- Dispatch date recorded

**Validation Points**:
- ✅ Manifest accurate
- ✅ Weight/volume calculations correct
- ✅ DO status updated
- ✅ All details saved

---

#### Test Case 6.5.2: Confirm Dispatch
**Objective**: Verify dispatch confirmation

**Steps**:
1. Confirm shipment dispatch
2. Verify status updates

**Expected Results**:
- Shipment status: "in_transit"
- Dispatch confirmed timestamp
- Status visible in tracking

**Validation Points**:
- ✅ Status updates correctly
- ✅ Timestamps recorded

---

#### Test Case 6.5.3: Mark as Delivered
**Objective**: Verify delivery confirmation

**Steps**:
1. Mark shipment as delivered
2. Verify DO closure

**Expected Results**:
- Shipment status: "delivered"
- Actual delivery date recorded
- DO status: "closed"
- Delivery confirmation logged

**Validation Points**:
- ✅ Delivery tracked
- ✅ DO closed
- ✅ Dates recorded

---

## Phase 7: Quality Inspection & Hold Management Testing

### Test Suite 7.1: QC Template Management

#### Test Case 7.1.1: Create QC Template
**Objective**: Verify QC template creation

**Steps**:
1. Navigate to QC Templates → Create
2. Enter template name
3. Add inspection parameters (weight, dimensions, etc.)
4. Set expected values and tolerances
5. Save template

**Expected Results**:
- Template created
- All parameters saved
- Template available for use
- Can be assigned to items/vendors

**Validation Points**:
- ✅ Template saved correctly
- ✅ Parameters configurable
- ✅ Tolerances saved

---

#### Test Case 7.1.2: Create Item-Specific Template
**Objective**: Verify item-specific templates

**Steps**:
1. Create template
2. Assign to specific item
3. Verify template used for that item

**Expected Results**:
- Template linked to item
- Template automatically selected for item QC
- Item-specific parameters override global

**Validation Points**:
- ✅ Item linkage works
- ✅ Template selection automatic

---

### Test Suite 7.2: QC Inspection Workflow

#### Test Case 7.2.1: Trigger QC from GRN
**Objective**: Verify QC triggered after GRN

**Prerequisites**:
- GRN completed
- QC template configured

**Steps**:
1. Complete GRN
2. Trigger QC inspection
3. Verify QC created

**Expected Results**:
- QC inspection created
- QC number generated
- Trigger source: "grn"
- Source ID: GRN ID
- Items from GRN included
- Status: "pending"

**Validation Points**:
- ✅ QC auto-created
- ✅ GRN linkage correct
- ✅ Items included

---

#### Test Case 7.2.2: Execute QC Inspection
**Objective**: Verify QC execution and results recording

**Steps**:
1. Open QC inspection
2. Enter actual values for each parameter
3. System calculates pass/fail based on tolerances
4. Enter notes
5. Save results

**Expected Results**:
- Inspection results saved
- Pass/fail calculated automatically
- Passed qty and failed qty calculated
- Inspector logged
- Status: "in_progress" or "completed"

**Validation Points**:
- ✅ Calculations correct
- ✅ Results saved
- ✅ Inspector tracked

---

#### Test Case 7.2.3: QC Approval Workflow
**Objective**: Verify QC approval process

**Prerequisites**:
- QC inspection completed
- Some items failed

**Steps**:
1. QA reviews QC results
2. Approve passed items
3. Reject failed items
4. Manager approves/rejects

**Expected Results**:
- Passed items: moved to release bin
- Failed items: moved to hold bin
- QC status: "approved" or "rejected"
- Approved by logged
- Inventory status updated (available vs qc_hold)

**Validation Points**:
- ✅ Approval workflow works
- ✅ Inventory moved correctly
- ✅ Status updates correct

---

#### Test Case 7.2.4: Release from Hold
**Objective**: Verify release from QC hold

**Prerequisites**:
- Items in QC hold
- Hold location configured

**Steps**:
1. Navigate to hold location
2. Select items to release
3. Select destination bin
4. Release items

**Expected Results**:
- Items moved from hold bin to destination
- Inventory status: "available"
- QC release transaction created
- Blocked quantity cleared

**Validation Points**:
- ✅ Release works correctly
- ✅ Inventory status updated
- ✅ Transaction logged

---

### Test Suite 7.3: QC Analytics

#### Test Case 7.3.1: QC Rejection Rate Report
**Objective**: Verify QC analytics

**Steps**:
1. Navigate to QC Analytics
2. View rejection rate by item
3. View rejection rate by vendor
4. View time period analysis

**Expected Results**:
- Rejection rates calculated correctly
- Data grouped by item/vendor/period
- Charts/graphs display correctly
- Export functionality works

**Validation Points**:
- ✅ Calculations accurate
- ✅ Data visualization works
- ✅ Export functions

---

## Phase 8: Kitting / Assembly Management Testing

### Test Suite 8.1: Kit Template Management

#### Test Case 8.1.1: Create Kit Template with BOM
**Objective**: Verify kit template creation

**Steps**:
1. Navigate to Kit Templates → Create
2. Enter kit code and name
3. Select finished goods item
4. Add BOM components (at least 3 components)
5. Set quantities for each component
6. Save template

**Expected Results**:
- Template created
- BOM saved correctly
- Total cost calculated (if component costs available)
- Total weight calculated
- Template active and usable

**Validation Points**:
- ✅ BOM structure correct
- ✅ Quantities saved
- ✅ Calculations accurate

---

### Test Suite 8.2: Kit Assembly Execution

#### Test Case 8.2.1: Assemble Kit
**Objective**: Verify kit assembly process

**Prerequisites**:
- Kit template created
- All components available in inventory
- Sufficient quantities

**Steps**:
1. Navigate to Kit Assembly → Create
2. Select kit template
3. Enter assembly quantity (10 kits)
4. System allocates components
5. Execute assembly

**Expected Results**:
- Assembly created with assembly number
- Components deducted from inventory
- Finished goods added to inventory
- Component batches tracked
- Traceability data saved
- Assembly status: "completed"
- Cost/weight rolled up

**Validation Points**:
- ✅ Components deducted correctly
- ✅ FG added correctly
- ✅ Traceability maintained
- ✅ Inventory balances correct

---

#### Test Case 8.2.2: Assemble with Insufficient Components
**Objective**: Verify error handling

**Prerequisites**:
- Kit template requiring 2 units of Component A
- Only 1 unit available

**Steps**:
1. Try to assemble kit
2. System checks availability

**Expected Results**:
- Error: "Insufficient components"
- Lists which components are short
- Assembly not created
- No inventory changes

**Validation Points**:
- ✅ Validation works
- ✅ Clear error messages
- ✅ No partial assembly

---

#### Test Case 8.2.3: De-Kitting
**Objective**: Verify reverse assembly process

**Prerequisites**:
- Completed kit assembly
- Finished goods in inventory

**Steps**:
1. Navigate to assembly
2. Initiate de-kitting
3. Confirm de-kit

**Expected Results**:
- Finished goods deducted
- Components added back to inventory
- Traceability maintained
- De-kit transaction logged
- Assembly status updated

**Validation Points**:
- ✅ Reverse process works
- ✅ Inventory restored
- ✅ Traceability maintained

---

#### Test Case 8.2.4: View Traceability
**Objective**: Verify batch traceability

**Steps**:
1. View assembly details
2. Check traceability section
3. Verify component batches listed

**Expected Results**:
- All component batches displayed
- Batch details accessible
- Can trace which batches used in each kit
- Can trace which kits used specific batch

**Validation Points**:
- ✅ Traceability data complete
- ✅ Bidirectional tracing works

---

## Phase 9: Cycle Count & Stock Audit Testing

### Test Suite 9.1: Cycle Count Scheduling

#### Test Case 9.1.1: Schedule Cycle Count
**Objective**: Verify count scheduling

**Steps**:
1. Navigate to Cycle Count → Schedule
2. Select count type (full/partial/random/ABC)
3. Select warehouse/zone/bin/item
4. Set scheduled date
5. Assign counter
6. Save schedule

**Expected Results**:
- Count scheduled
- Count number generated
- Status: "scheduled"
- Assigned to counter
- System quantity pre-populated

**Validation Points**:
- ✅ Schedule saved
- ✅ Count number generated
- ✅ Assignment works

---

### Test Suite 9.2: Cycle Count Execution

#### Test Case 9.2.1: Execute Count (Mobile Interface)
**Objective**: Verify mobile-friendly count entry

**Prerequisites**:
- Scheduled cycle count
- Mobile device or responsive browser

**Steps**:
1. Open count on mobile device
2. Scan barcode/QR code (or manual entry)
3. Enter physical quantity
4. System calculates variance
5. Save count

**Expected Results**:
- Mobile interface responsive
- Barcode scanning works (if implemented)
- Physical quantity saved
- Variance calculated automatically
- Variance percentage calculated
- Count status: "completed"
- Counted by user logged
- Counted at timestamp recorded

**Validation Points**:
- ✅ Mobile UI works
- ✅ Calculations correct
- ✅ Data saved correctly

---

#### Test Case 9.2.2: Count with Variance
**Objective**: Verify variance handling

**Prerequisites**:
- System quantity: 50
- Physical quantity: 48

**Steps**:
1. Enter physical quantity: 48
2. Save count

**Expected Results**:
- Variance: -2
- Variance percentage: -4%
- Discrepancy reason field available
- Count requires approval
- Adjustment transaction prepared (not executed until approval)

**Validation Points**:
- ✅ Variance calculated correctly
- ✅ Approval workflow triggered
- ✅ Adjustment prepared

---

#### Test Case 9.2.3: Approve Count and Generate Adjustment
**Objective**: Verify adjustment generation

**Prerequisites**:
- Count with variance completed
- Count awaiting approval

**Steps**:
1. Manager reviews count
2. Enter discrepancy reason
3. Approve count
4. System generates adjustment

**Expected Results**:
- Count status: "approved"
- Approved by logged
- Adjustment transaction created
- Inventory updated to match physical count
- Adjustment transaction references count
- Audit log entry created

**Validation Points**:
- ✅ Approval works
- ✅ Adjustment created
- ✅ Inventory updated
- ✅ Audit trail maintained

---

### Test Suite 9.3: Stock Audit

#### Test Case 9.3.1: Full Warehouse Audit
**Objective**: Verify complete warehouse audit

**Steps**:
1. Initiate full warehouse audit
2. System generates count schedule for all bins
3. Execute all counts
4. Generate audit report

**Expected Results**:
- All bins scheduled for counting
- Counts can be executed
- Overall accuracy calculated
- Variance summary generated
- Audit report available

**Validation Points**:
- ✅ Full audit works
- ✅ All locations covered
- ✅ Summary accurate

---

#### Test Case 9.3.2: ABC Analysis
**Objective**: Verify ABC classification for prioritization

**Steps**:
1. Run ABC analysis
2. View classification results

**Expected Results**:
- Items classified as A, B, or C
- A items: high value, frequent counts
- B items: medium value
- C items: low value, less frequent counts
- Count schedule prioritized

**Validation Points**:
- ✅ Classification correct
- ✅ Prioritization works

---

## Phase 10: Reporting & Dashboards Testing

### Test Suite 10.1: Dashboard KPIs

#### Test Case 10.1.1: GRN vs Putaway TAT
**Objective**: Verify turnaround time calculation

**Steps**:
1. Navigate to dashboard
2. View GRN TAT widget
3. Verify calculations

**Expected Results**:
- Average TAT displayed
- TAT = Putaway completion time - GRN creation time
- Data grouped by time period
- Chart/graph displays correctly

**Validation Points**:
- ✅ Calculations accurate
- ✅ Time periods correct
- ✅ Visualization works

---

#### Test Case 10.1.2: Bin Utilization %
**Objective**: Verify utilization metrics

**Steps**:
1. View bin utilization widget
2. Check by warehouse
3. Check by zone
4. Check by storage type

**Expected Results**:
- Utilization % calculated correctly
- Grouped by warehouse/zone/type
- Visual indicators (color coding)
- Drill-down available

**Validation Points**:
- ✅ Calculations accurate
- ✅ Grouping works
- ✅ Drill-down functional

---

#### Test Case 10.1.3: QC Rejection Rate
**Objective**: Verify QC metrics

**Steps**:
1. View QC rejection rate widget
2. Check by item
3. Check by vendor
4. Check by time period

**Expected Results**:
- Rejection rate = (Failed qty / Total inspected qty) * 100
- Data grouped correctly
- Trends visible
- Export available

**Validation Points**:
- ✅ Calculations correct
- ✅ Grouping accurate
- ✅ Trends displayed

---

#### Test Case 10.1.4: Replenishment Frequency
**Objective**: Verify replenishment metrics

**Steps**:
1. View replenishment frequency widget
2. Check auto-generated TOs
3. View frequency trends

**Expected Results**:
- Frequency = Number of TOs / Time period
- Trends visible
- High-frequency bins identified
- Low-frequency bins identified

**Validation Points**:
- ✅ Frequency calculated
- ✅ Trends accurate

---

#### Test Case 10.1.5: Inventory Age & Accuracy
**Objective**: Verify inventory metrics

**Steps**:
1. View inventory age widget
2. Check FEFO compliance
3. View expiry tracking
4. Check accuracy metrics

**Expected Results**:
- Age calculated correctly
- Expiry dates tracked
- FEFO compliance shown
- Accuracy % displayed

**Validation Points**:
- ✅ Age calculations correct
- ✅ Expiry tracking works
- ✅ Accuracy metrics accurate

---

#### Test Case 10.1.6: Vendor Performance
**Objective**: Verify vendor metrics

**Steps**:
1. View vendor performance widget
2. Check delivery time
3. Check quality metrics
4. View vendor rankings

**Expected Results**:
- Average delivery time calculated
- Quality scores displayed
- Vendor rankings shown
- Performance trends visible

**Validation Points**:
- ✅ Metrics accurate
- ✅ Rankings correct
- ✅ Trends displayed

---

#### Test Case 10.1.7: DO Lead Time
**Objective**: Verify outbound metrics

**Steps**:
1. View DO lead time widget
2. Check order to dispatch time
3. View trends

**Expected Results**:
- Lead time = Dispatch date - DO creation date
- Average lead time displayed
- Trends visible
- SLA compliance shown

**Validation Points**:
- ✅ Lead time calculated
- ✅ Trends accurate
- ✅ SLA tracking works

---

### Test Suite 10.2: Report Generation

#### Test Case 10.2.1: Generate GRN TAT Report
**Objective**: Verify report generation

**Steps**:
1. Navigate to Reports
2. Select "GRN TAT Report"
3. Set date range
4. Generate report
5. Export to Excel

**Expected Results**:
- Report generated
- Data accurate
- Excel file downloadable
- Format correct
- All required fields included

**Validation Points**:
- ✅ Report generated
- ✅ Data accurate
- ✅ Export works
- ✅ Format correct

---

#### Test Case 10.2.2: Generate PDF Report
**Objective**: Verify PDF export

**Steps**:
1. Generate any report
2. Export to PDF
3. Verify PDF

**Expected Results**:
- PDF generated
- Format correct
- All data included
- Charts/graphs included (if applicable)
- Downloadable

**Validation Points**:
- ✅ PDF generation works
- ✅ Format correct
- ✅ Data complete

---

#### Test Case 10.2.3: API Report Endpoint
**Objective**: Verify API access

**Steps**:
1. Call report API endpoint
2. Verify JSON response
3. Check data format

**Expected Results**:
- API returns JSON
- Data structure correct
- Authentication required
- Rate limiting works (if implemented)

**Validation Points**:
- ✅ API works
- ✅ Format correct
- ✅ Security enforced

---

### Test Suite 10.3: Report Caching

#### Test Case 10.3.1: Cache Daily Metrics
**Objective**: Verify caching works

**Prerequisites**:
- Scheduled job configured

**Steps**:
1. Wait for scheduled job execution
2. Check cached data
3. Verify cache refresh

**Expected Results**:
- Metrics cached daily
- Cache timestamp updated
- Cached data used for dashboard
- Cache refreshed on schedule

**Validation Points**:
- ✅ Caching works
- ✅ Refresh schedule correct
- ✅ Performance improved

---

## Integration Testing

### Test Suite INT.1: End-to-End Workflows

#### Test Case INT.1.1: Complete Inbound to Outbound Flow
**Objective**: Verify complete workflow

**Steps**:
1. Create GRN
2. Approve GRN
3. Execute putaway
4. Create delivery order
5. Generate picking tasks
6. Execute picking
7. Create shipment
8. Dispatch
9. Mark delivered

**Expected Results**:
- All steps execute successfully
- Inventory updated correctly at each step
- All transactions logged
- Status transitions correct
- No data inconsistencies

**Validation Points**:
- ✅ Complete flow works
- ✅ Data integrity maintained
- ✅ All transactions logged

---

#### Test Case INT.1.2: Replenishment to Picking Flow
**Objective**: Verify replenishment supports picking

**Steps**:
1. Bin goes below reorder level
2. Auto-generate replenishment TO
3. Execute TO
4. Create DO requiring items from replenished bin
5. Generate picking tasks
6. Execute picking

**Expected Results**:
- Replenishment works
- Items available for picking
- Picking succeeds
- Flow seamless

**Validation Points**:
- ✅ Integration works
- ✅ No gaps in process

---

## Performance Testing

### Test Suite PERF.1: Load Testing

#### Test Case PERF.1.1: Bulk TO Generation
**Objective**: Verify performance with large volumes

**Steps**:
1. Create 100 bins below reorder level
2. Trigger auto-generation
3. Measure execution time

**Expected Results**:
- All TOs generated within reasonable time (< 30 seconds)
- No timeouts
- System remains responsive

**Validation Points**:
- ✅ Performance acceptable
- ✅ No timeouts
- ✅ System stable

---

#### Test Case PERF.1.2: Concurrent Picking
**Objective**: Verify concurrent operations

**Steps**:
1. Create multiple picking tasks
2. Execute simultaneously (multiple users)
3. Verify data integrity

**Expected Results**:
- All picks execute successfully
- No data corruption
- Inventory balances correct
- No deadlocks

**Validation Points**:
- ✅ Concurrency handled
- ✅ Data integrity maintained
- ✅ No conflicts

---

## Security Testing

### Test Suite SEC.1: Access Control

#### Test Case SEC.1.1: Unauthorized Access Prevention
**Objective**: Verify security

**Steps**:
1. Try to access TO/DO/QC without authentication
2. Try to access with non-admin user
3. Try to access other tenant's data

**Expected Results**:
- Unauthorized access blocked
- Proper error messages
- No data leakage
- Tenant isolation maintained

**Validation Points**:
- ✅ Security enforced
- ✅ Tenant isolation works
- ✅ Access control correct

---

## Test Data Requirements Summary

### Minimum Test Data Needed

1. **Plants**: 2
2. **Warehouses**: 3
3. **Bins**: 20+ (with various storage types, zones, reorder levels)
4. **Items**: 20+ (various categories, with dimensions/weight)
5. **Vendors**: 5
6. **Batches**: 10+ (with different expiry dates for FEFO testing)
7. **Inventory Records**: 50+ (distributed across bins)
8. **Users**: 
   - 1 Master admin
   - 1 Tenant admin
   - 2 Operators
   - 1 QA user
   - 1 Manager

### Test Scenarios by Priority

**High Priority** (Core Functionality):
- TO creation and execution
- DO creation and picking
- QC inspection workflow
- Cycle count execution
- Dashboard KPIs

**Medium Priority** (Advanced Features):
- Auto-replenishment
- Wave picking
- Kit assembly
- Report generation
- Analytics

**Low Priority** (Nice to Have):
- Voice commands (future)
- Advanced analytics
- Custom reports

---

## Test Execution Checklist

### Pre-Testing
- [ ] Test environment set up
- [ ] Test data seeded
- [ ] User accounts created
- [ ] Configuration completed
- [ ] Backup created

### Phase 5 Testing
- [ ] Manual TO creation
- [ ] TO approval workflow
- [ ] TO execution
- [ ] Auto-replenishment
- [ ] TO cancellation

### Phase 6 Testing
- [ ] DO creation
- [ ] Picking task generation (FIFO/FEFO)
- [ ] Picking execution
- [ ] Wave picking
- [ ] Shipment and dispatch

### Phase 7 Testing
- [ ] QC template management
- [ ] QC inspection workflow
- [ ] QC approval
- [ ] Hold management
- [ ] QC analytics

### Phase 8 Testing
- [ ] Kit template creation
- [ ] Kit assembly
- [ ] De-kitting
- [ ] Traceability

### Phase 9 Testing
- [ ] Cycle count scheduling
- [ ] Count execution (mobile)
- [ ] Variance handling
- [ ] Adjustment generation
- [ ] Stock audit

### Phase 10 Testing
- [ ] Dashboard KPIs
- [ ] Report generation
- [ ] Report export (Excel/PDF)
- [ ] API endpoints
- [ ] Caching

### Integration Testing
- [ ] End-to-end workflows
- [ ] Cross-module integration

### Performance Testing
- [ ] Load testing
- [ ] Concurrent operations

### Security Testing
- [ ] Access control
- [ ] Tenant isolation

### Post-Testing
- [ ] Test results documented
- [ ] Bugs logged
- [ ] Performance metrics recorded
- [ ] Test environment restored

---

## Bug Reporting Template

When reporting bugs, include:

1. **Test Case ID**: e.g., 5.1.1
2. **Module**: e.g., Transfer Orders
3. **Severity**: Critical/High/Medium/Low
4. **Steps to Reproduce**: Detailed steps
5. **Expected Result**: What should happen
6. **Actual Result**: What actually happened
7. **Screenshots/Logs**: If applicable
8. **Environment**: Browser, OS, etc.
9. **Data Used**: Test data that caused issue

---

## Success Criteria

### Phase 5
- ✅ All TO workflows functional
- ✅ Auto-replenishment works correctly
- ✅ Inventory updates atomic
- ✅ No data loss

### Phase 6
- ✅ DO creation and management works
- ✅ Picking allocation correct (FIFO/FEFO)
- ✅ Wave picking functional
- ✅ Dispatch workflow complete

### Phase 7
- ✅ QC templates configurable
- ✅ QC workflow complete
- ✅ Hold management works
- ✅ Analytics accurate

### Phase 8
- ✅ Kit templates functional
- ✅ Assembly/de-kitting works
- ✅ Traceability maintained

### Phase 9
- ✅ Cycle count scheduling works
- ✅ Mobile interface functional
- ✅ Adjustments generated correctly
- ✅ Audit trail maintained

### Phase 10
- ✅ All KPIs accurate
- ✅ Reports generate correctly
- ✅ Exports work (Excel/PDF/API)
- ✅ Caching improves performance

---

**Document Version**: 1.0  
**Last Updated**: 2025-01-06  
**Prepared By**: Development Team

