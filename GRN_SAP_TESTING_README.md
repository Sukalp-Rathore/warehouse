## GRN (SAP Integration) – Testing Guide

### 1. Prerequisites

- **Tenant configuration**
  - Tenant is configured and you can log in as a **client user** with access to GRN and Putaway.
  - `TenantSettings.integration_settings` has:
    - `sap_endpoint` (e.g. a mock URL or your SAP gateway)
    - `sap_token` (valid or test token).

- **SAP side / Mock API**
  - The SAP `/api/grns` endpoint (or mock) returns JSON like:
    - `status` values: `PENDING`, `APPROVED`, etc.
    - Each GRN has: `sap_grn_id`/`id`, `grn_number`, `status`, `vendor`, `warehouse_code`, `po_reference`, `items[]`.
  - Make sure at least:
    - 1–2 GRNs with `status = PENDING`.
    - 1–2 GRNs with `status = APPROVED`.

---

### 2. Sync from SAP

#### 2.1 Happy path – sync all GRNs

1. Go to **GRN Management** (`/grns`).
2. Click **“Sync from SAP”**.
3. Verify:
   - Success alert: text like **“Synced X GRN(s) from SAP”**.
   - No PHP exceptions/log errors.
4. In the `grns` collection, confirm for synced GRNs:
   - `sap_grn_id` set.
   - `sap_data` populated.
   - `status` mapped:
     - `PENDING` → `pending`
     - `APPROVED` → `approved`
   - `putaway_status` is `pending`.

#### 2.2 Error handling – SAP unreachable / bad token

1. Temporarily break SAP config:
   - Wrong `sap_endpoint` OR invalid `sap_token`.
2. Click **“Sync from SAP”**.
3. Verify:
   - Error alert with a clear message (“SAP integration not configured…” or “Failed to fetch GRNs from SAP…”).
   - No GRNs are duplicated or corrupted.

#### 2.3 Item / warehouse mapping issues

1. Configure SAP to return a GRN:
   - With **unknown item_code** (does not exist in `items`).
   - With **unknown warehouse_code** (does not exist in `warehouses`).
2. Sync from SAP.
3. Verify:
   - Unknown **warehouse** → that GRN fails sync, is not created, and error is logged.
   - Unknown **item** → GRN is created but that line is skipped; warning logged, no crash.

---

### 3. GRN List Page – Pending vs Approved

#### 3.1 Pending approval section

1. After sync, open **GRN Management**.
2. Check **“Pending Approval GRNs”**:
   - Shows **only** records with `status = pending`.
   - Columns: GRN Number, Vendor, Warehouse, PO, Items, Created Date, Actions.
   - Each row has:
     - **View** button (opens `/grns/{id}`).
     - **Approve** button.
3. Use filters (**search**, **warehouse**, **vendor**) and confirm:
   - The count and list in **Pending Approval GRNs** update correctly.

#### 3.2 Approved section

1. On the same page, check **“Approved GRNs”**:
   - Shows **only** `status = approved`.
   - Columns include **Putaway Status** and **Approved Date**.
   - Each row has:
     - **View** button.
     - **Putaway** button when `putaway_status` is `pending` or `in_progress`.
2. Verify pagination:
   - Each section paginates independently and works as expected.

---

### 4. Approve Flow

#### 4.1 Approve from list

1. From **Pending Approval GRNs**, click **Approve** on one GRN.
2. Verify:
   - You see a success message “GRN approved successfully”.
   - That GRN disappears from **Pending Approval**.
   - The same GRN appears in **Approved GRNs** with:
     - `status = approved`.
     - `approved_by` = current user.
     - `approved_at` set.

#### 4.2 Approve from detail page

1. Open a pending GRN (`/grns/{id}`).
2. Verify header shows **“Approve GRN”** button.
3. Click **Approve GRN**:
   - Status badge becomes **Approved**.
   - `Approved Date` row appears.
   - On index page, GRN moves to **Approved GRNs**.

#### 4.3 Invalid approval attempts

1. Try to approve an already-approved GRN.
2. Verify:
   - Error message: **“GRN cannot be approved in its current status”**.
   - Status and audit fields remain unchanged.

---

### 5. Putaway Button & Flow

#### 5.1 Button visibility

1. In **Approved GRNs** section:
   - `putaway_status = pending` or `in_progress` → **Putaway** button is visible.
   - `putaway_status = completed` → Putaway button is **not** shown.
2. In `/grns/{id}`:
   - For **approved** GRNs, header shows **Putaway** button.
   - For **pending** GRNs, Putaway button is **not** shown, and direct access to `/grns/{id}/putaway` gives “GRN must be approved before putaway”.

#### 5.2 Putaway execution

1. From an approved GRN, click **Putaway**.
2. Confirm:
   - Putaway screen loads with all GRN items and quantities.
3. Perform putaway for one item:
   - Quantity ≤ GRN item quantity.
   - Valid bin with enough capacity.
4. Verify:
   - Inventory record is created/updated in the target bin with correct quantities.
   - GRN `putaway_status` becomes `in_progress`.
   - In **Approved GRNs** list, Putaway Status shows **In Progress**.
5. Fully putaway all items:
   - `putaway_status = completed`.
   - (If implemented) `status = completed`.
   - Detail page progress bar shows 100%.
   - Putaway button is no longer visible.

#### 5.3 Putaway validation failures

- **Quantity > GRN quantity**:
  - Error: **“Quantity exceeds GRN quantity”**.
  - No inventory or transactions created.
- **Bin capacity insufficient**:
  - Error from capacity validation (destination bin capacity message).
  - No inventory moved.

---

### 6. Regression Checks

- **Manual GRN creation removed**
  - No **“Create GRN”** button on GRN index.
  - No usable `/grns/create` or `POST /grns` flow.

- **Existing GRNs**
  - Legacy GRNs (before SAP integration) still:
    - Show in one of the two sections based on `status`.
    - Allow putaway if `status` is `approved`/`in_progress`.

- **Permissions / visibility**
  - Users without GRN/Putaway permissions:
    - Cannot access GRN routes, or see menu (per your RBAC).
  - Tenant admins / warehouse roles:
    - Can use **Sync from SAP**, **Approve**, and **Putaway** as designed.

---

### 7. Suggested Test Data Matrix

Create or simulate these GRNs from SAP and walk through them end‑to‑end:

- **Pending GRNs**
  - P1: single item, normal vendor/warehouse.
  - P2: multiple items, mixed quantities.

- **Approved GRNs**
  - A1: approved, no putaway.
  - A2: approved, partial putaway.
  - A3: approved, fully putaway.

Use this matrix to confirm all flows (sync, approve, putaway, and error handling) behave as expected.



