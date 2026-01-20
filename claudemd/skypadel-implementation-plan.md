# Sky Padel India - Implementation Plan

**Chapter 2: Project Lifecycle Orchestration**
**Created:** 2025-12-29
**Last Updated:** 2026-01-20

---

## Current Status: 98% Complete ✅

### Completed Features
- ✅ Lead Management (`sky-padel-lead/`)
- ✅ Site Visit Scheduling (`sky-padel-site-visit/`)
- ✅ Site Report with assessments (`sky-padel-site-report/`)
- ✅ Lead to Quotation conversion
- ✅ Version-controlled quotations with revision tracking (`sky-padel-quotation/`)
- ✅ Proforma invoice generation with PDF (`sky-padel-proforma/`)
- ✅ **Digital Signature Contract** (`sky-padel-contract/`) - DONE
  - Auto-generate on quotation approval
  - Indian Legal PDF template with professional formatting
  - OTP-verified contract signing
  - Contract view and signing in client portal
- ✅ Project Management (`sky-padel-project/`)
- ✅ **Project Gantt Chart** (`x-sky-padel-project-gantt.php`) - DONE
- ✅ Payment tracking (`sky-padel-payment/`)
- ✅ Expense tracking (`sky-padel-expense/`)
- ✅ Reports (`sky-padel-report/`)
- ✅ Dashboard with analytics (`sky-padel-dashboard/`)
- ✅ **Stock Allocation per project** (`stock-allocation/`) - DONE
- ✅ **Site GRN (Goods Receipt)** (`site-grn/`) - DONE
- ✅ **Client Portal** (`/skypadel/`) - DONE
  - Homepage with lead form (`index.php`)
  - Email + OTP authentication (`login.php`, `api/send-otp.php`, `api/verify-otp.php`)
  - Dashboard with project overview (`dashboard.php`)
  - Project list and detail with milestones (`projects.php`, `project-detail.php`)
  - Quotation view with approval/signature (`quotations.php`, `quotation-view.php`)
  - **Contracts list and signing** (`contracts.php`, `contract-sign.php`) - NEW
  - Invoices and payments tracking (`invoices.php`, `payments.php`)
  - Consistent design across all pages (Bebas Neue + Outfit fonts)

### Pending Features (Minor)
- ⏳ Bill of Materials (BOM) - dedicated module (currently handled via quotation items)
- ⏳ Auto procurement trigger (logic may exist, needs verification)
- ⏳ Material reconciliation report (end-of-project stock audit)
- ⏳ Profitability report (may be in sky-padel-report, needs verification)

---

## Implementation Phases

### Phase 1: Client Portal (Priority)
**Location:** `localhost/bes/skypadel/`
**Timeline:** Immediate

#### 1.1 Database Setup
```sql
-- Client portal authentication table
CREATE TABLE mx_sky_padel_client_auth (
  authID INT AUTO_INCREMENT PRIMARY KEY,
  clientEmail VARCHAR(150) NOT NULL UNIQUE,
  passwordHash VARCHAR(255),
  otpCode VARCHAR(6),
  otpExpiry DATETIME,
  lastLogin DATETIME,
  loginAttempts INT DEFAULT 0,
  isLocked TINYINT DEFAULT 0,
  authToken VARCHAR(255),
  tokenExpiry DATETIME,
  created DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified DATETIME ON UPDATE CURRENT_TIMESTAMP,
  status TINYINT DEFAULT 1,
  INDEX idx_email (clientEmail),
  INDEX idx_token (authToken)
);

-- Client activity log
CREATE TABLE mx_sky_padel_client_activity (
  activityID INT AUTO_INCREMENT PRIMARY KEY,
  clientEmail VARCHAR(150),
  activityType ENUM('Login','Logout','ViewProject','ViewQuotation','ViewInvoice','ViewPayment','ApproveQuotation') NOT NULL,
  entityType VARCHAR(50),
  entityID INT,
  ipAddress VARCHAR(45),
  userAgent TEXT,
  created DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

#### 1.2 Portal Structure
```
E:\xampp\htdocs\bes\skypadel\
├── index.php              # Homepage with lead form & 3D court
├── login.php              # Email + OTP login
├── dashboard.php          # Client dashboard
├── projects.php           # Project list
├── project-detail.php     # Single project view
├── quotations.php         # Quotation history
├── quotation-view.php     # View/approve quotation
├── contracts.php          # Contract list (NEW)
├── contract-sign.php      # OTP-verified contract signing (NEW)
├── invoices.php           # Invoice list
├── payments.php           # Payment history
├── logout.php             # Logout handler
├── core/
│   ├── config.php         # Database & settings
│   ├── layout.php         # Shared header/sidebar/footer (NEW)
│   └── functions.php      # Helper functions
├── api/
│   ├── login.php          # Login API
│   ├── send-otp.php       # OTP sender
│   └── verify-otp.php     # OTP verification
├── templates/
│   ├── contract-pdf-template.php  # Indian legal PDF template (NEW)
│   └── contract-preview.php       # Template preview (NEW)
├── css/
│   └── portal.css         # Portal styles (external CSS)
└── js/
    └── portal.js          # Portal scripts
```

#### 1.3 Portal Features
| Feature | Description |
|---------|-------------|
| Login | Email + OTP based authentication |
| Dashboard | Overview of all projects, pending payments |
| Projects | List of all projects with status |
| Project Detail | Milestones, progress %, timeline |
| Quotations | View & approve quotations |
| Invoices | Proforma & final invoices |
| Payments | Payment history & upcoming dues |

---

### Phase 2: Enhanced BOM & Stock Allocation ✅ IMPLEMENTED
**Status:** Stock Allocation module exists at `xadmin/mod/stock-allocation/`

#### 2.1 Database Changes
```sql
-- Project BOM table
CREATE TABLE mx_sky_padel_project_bom (
  bomID INT AUTO_INCREMENT PRIMARY KEY,
  projectID INT NOT NULL,
  productID INT,
  productSKU VARCHAR(50),
  productName VARCHAR(255),
  quantity DECIMAL(10,2) DEFAULT 1,
  uom VARCHAR(20) DEFAULT 'Pcs',
  estimatedCost DECIMAL(15,2),
  actualCost DECIMAL(15,2),
  allocatedQty DECIMAL(10,2) DEFAULT 0,
  consumedQty DECIMAL(10,2) DEFAULT 0,
  warehouseID INT,
  allocationStatus ENUM('Pending','Partial','Allocated','Consumed') DEFAULT 'Pending',
  created DATETIME DEFAULT CURRENT_TIMESTAMP,
  status TINYINT DEFAULT 1,
  FOREIGN KEY (projectID) REFERENCES mx_sky_padel_project(projectID)
);

-- Stock allocation log
CREATE TABLE mx_sky_padel_stock_allocation (
  allocationID INT AUTO_INCREMENT PRIMARY KEY,
  projectID INT NOT NULL,
  bomID INT NOT NULL,
  productID INT NOT NULL,
  warehouseID INT NOT NULL,
  quantity DECIMAL(10,2) NOT NULL,
  allocationType ENUM('Reserve','Release','Consume') NOT NULL,
  allocationDate DATETIME DEFAULT CURRENT_TIMESTAMP,
  allocatedBy INT,
  notes TEXT,
  status TINYINT DEFAULT 1
);
```

#### 2.2 Workflow
1. When project is created → Auto-generate BOM from quotation items
2. Check stock availability per warehouse
3. Reserve stock for project (reduce available qty)
4. Track consumption as materials are dispatched
5. Reconcile on project completion

---

### Phase 3: Auto Procurement Trigger ⏳ PARTIAL
**Status:** Vendor RFQ and Quote modules exist. Auto-trigger logic may need verification.

#### 3.1 Logic Flow
```
Project BOM Created
    ↓
Check Stock Availability
    ↓
If Stock < Required
    ↓
Auto-generate Purchase Requisition
    ↓
Route to Procurement Team
    ↓
Convert to PO when approved
```

#### 3.2 Implementation
- Add `minStockLevel` field to inventory
- Create `mx_purchase_requisition` table
- Add notification trigger for low stock
- Link requisitions to specific projects

---

### Phase 4: Site GRN (Goods Receipt at Site) ✅ IMPLEMENTED
**Status:** Site GRN module exists at `xadmin/mod/site-grn/`

#### 4.1 Database
```sql
CREATE TABLE mx_sky_padel_site_grn (
  grnID INT AUTO_INCREMENT PRIMARY KEY,
  grnNo VARCHAR(50) UNIQUE,
  projectID INT NOT NULL,
  dispatchID INT,
  poID INT,
  receivedDate DATE NOT NULL,
  receivedBy VARCHAR(200),
  receivedByPhone VARCHAR(20),
  deliveryNotes TEXT,
  receiverSignature VARCHAR(255),
  photos TEXT,
  grnStatus ENUM('Pending','Partial','Complete','Discrepancy') DEFAULT 'Pending',
  created DATETIME DEFAULT CURRENT_TIMESTAMP,
  status TINYINT DEFAULT 1
);

CREATE TABLE mx_sky_padel_site_grn_item (
  itemID INT AUTO_INCREMENT PRIMARY KEY,
  grnID INT NOT NULL,
  bomID INT,
  productID INT,
  productName VARCHAR(255),
  expectedQty DECIMAL(10,2),
  receivedQty DECIMAL(10,2),
  damagedQty DECIMAL(10,2) DEFAULT 0,
  shortQty DECIMAL(10,2) DEFAULT 0,
  condition ENUM('Good','Damaged','Mixed') DEFAULT 'Good',
  notes TEXT,
  status TINYINT DEFAULT 1
);
```

#### 4.2 Mobile Interface
- Simple form for site supervisor
- Photo upload for delivery proof
- Signature capture
- Auto-update inventory on confirmation

---

### Phase 5: Gantt Chart Dashboard ✅ IMPLEMENTED
**Status:** Gantt chart exists at `xadmin/mod/sky-padel-project/x-sky-padel-project-gantt.php`

#### 5.1 Implementation
- Use Frappe Gantt or similar JS library
- Data source: Project milestones with dates
- Features:
  - Drag to reschedule
  - Color by status (on-track/delayed/completed)
  - Milestone dependencies
  - Critical path highlighting

#### 5.2 Dashboard Widgets
- Active Projects count
- Overdue Milestones
- Pending Payments
- Material Status
- Project Timeline (Gantt)

---

### Phase 6: Material Reconciliation ⏳ PENDING
**Status:** Needs dedicated report module or integration with sky-padel-report

#### 6.1 Process
1. At project completion, run reconciliation report
2. Compare: Allocated vs Consumed vs Returned
3. Calculate variance and cost impact
4. Credit returned materials back to inventory
5. Write-off damaged/lost items

#### 6.2 Report Fields
- Project ID & Name
- Material | Allocated | Consumed | Returned | Variance
- Cost Analysis
- Wastage %

---

### Phase 7: Profitability Report ⏳ PARTIAL
**Status:** Report module exists at `xadmin/mod/sky-padel-report/` - needs verification of profitability features

#### 7.1 Report Structure
```
PROJECT PROFITABILITY REPORT
============================
Project: PRJ-20251229-0001
Client: ABC Sports Pvt Ltd

REVENUE
-------
Contract Amount:     ₹25,00,000
Additional Works:    ₹1,50,000
TOTAL REVENUE:       ₹26,50,000

COSTS
-----
Materials:           ₹12,00,000
Labor:               ₹4,50,000
Logistics:           ₹80,000
Vendor Services:     ₹2,00,000
Overheads (10%):     ₹1,93,000
TOTAL COSTS:         ₹21,23,000

PROFITABILITY
-------------
Gross Profit:        ₹5,27,000
Profit Margin:       19.89%
```

#### 7.2 Data Sources
- Revenue: sky_padel_project.contractAmount + additional invoices
- Materials: Sum of PO costs for project
- Labor: Allocated labor costs
- Logistics: Dispatch costs
- Overheads: Configurable % allocation

---

## File Structure for New Modules

```
xadmin/mod/sky-padel-bom/
├── x-sky-padel-bom.inc.php
├── x-sky-padel-bom-list.php
└── x-sky-padel-bom-add-edit.php

xadmin/mod/sky-padel-site-grn/
├── x-sky-padel-site-grn.inc.php
├── x-sky-padel-site-grn-list.php
└── x-sky-padel-site-grn-add-edit.php

xadmin/mod/sky-padel-reconciliation/
├── x-sky-padel-reconciliation.inc.php
└── x-sky-padel-reconciliation-report.php

xadmin/mod/sky-padel-profitability/
├── x-sky-padel-profitability.inc.php
└── x-sky-padel-profitability-report.php
```

---

## Priority Order

1. ✅ **Client Portal** - COMPLETED 2025-12-29
2. **Digital Signature Contract** - Next (auto-generate on quotation approval)
3. **Quotation Revision Tracking** - Connect revisions to parent for audit trail
4. **BOM Module** - Create new module (not enhancement)
5. **Stock Allocation** - Per project, per ppf.docx specification
6. **Gantt Dashboard** - Use xAdmin-compatible styling only
7. **Procurement Trigger** - Auto-generate requisitions on low stock
8. **Site GRN** - Goods receipt at project site
9. **Material Reconciliation** - End-of-project stock audit
10. **Profitability Report** - Financial analysis per project

---

## Client Portal (COMPLETED)

**Location:** `http://localhost/bes/skypadel/`
**Design:** Premium Athletic Modernism theme with Bebas Neue + Outfit fonts

### Pages Built:
| Page | File | Features |
|------|------|----------|
| Homepage | `index.php` | Lead form, 3D court model, navigation |
| Login | `login.php` | Email + OTP authentication |
| Dashboard | `dashboard.php` | Stats, project cards, pending payments |
| Projects | `projects.php` | Project cards with progress |
| Project Detail | `project-detail.php` | Milestones timeline, payments |
| Quotations | `quotations.php` | Quotation list with status |
| Quotation View | `quotation-view.php` | Detail view with approval/signature |
| **Contracts** | `contracts.php` | Contract list with status badges |
| **Contract Sign** | `contract-sign.php` | OTP-verified digital signing |
| Invoices | `invoices.php` | Invoice list with download |
| Payments | `payments.php` | Payment schedule with totals |

### Shared Layout System
- `core/layout.php` - Shared header, sidebar, footer functions
- `css/portal.css` - External CSS with CSS variables
- Consistent design across all pages
- Responsive sidebar navigation

---

## Digital Signature Contract System

### Workflow:
1. Client approves quotation in portal (signature)
2. System auto-generates contract document
3. Contract includes:
   - All quotation details
   - Payment milestones
   - Terms and conditions (20 sections of Indian legal terms)
   - Client signature (OTP-verified)
   - Date stamp & IP address
4. Contract stored in document vault
5. Project can only start after contract signed

### Contract PDF Template (Indian Legal Style)
**Location:** `xadmin/mod/sky-padel-contract/x-sky-padel-contract-pdf.php`
**Template:** `skypadel/templates/contract-pdf-template.php`

**Design Features:**
- Professional Indian legal document formatting
- Ornate border frame with corner ornaments
- Color scheme: Deep green (#1a5c3a), parchment (#faf6ed), gold accents (#b8860b)
- Typography: DejaVu Serif (body), DejaVu Sans Mono (numbers)

**Document Sections:**
1. Contract Agreement header with contract number
2. Parties Section (First Party / Second Party with full details)
3. Recitals (WHEREAS clauses)
4. Contract Value box (total amount with words in Indian Lakh/Crore system)
5. Scope of Work
6. Payment Schedule table (milestones with percentages, amounts, due dates)
7. Terms & Conditions (preserves original numbering: 1., 1.1, 2., etc.)
8. Payment Terms
9. Signature Section (dual blocks for both parties)
10. Company Seal area
11. Witness Section (2 witnesses)
12. Jurisdiction footer (Maharashtra courts)

### Database Addition:
```sql
CREATE TABLE mx_sky_padel_contract (
  contractID INT AUTO_INCREMENT PRIMARY KEY,
  contractNo VARCHAR(50) UNIQUE,
  quotationID INT NOT NULL,
  leadID INT NOT NULL,
  projectID INT,
  contractDate DATE,
  contractAmount DECIMAL(15,2),
  advanceAmount DECIMAL(15,2),
  advancePercentage DECIMAL(5,2),
  scopeOfWork TEXT,
  termsAndConditions TEXT,
  paymentTerms TEXT,
  contractStatus ENUM('Pending Signature','Signed','Active','Completed','Cancelled') DEFAULT 'Pending Signature',
  signedBy VARCHAR(200),
  signedAt DATETIME,
  signatureIP VARCHAR(45),
  signatureMethod VARCHAR(50),
  otpCode VARCHAR(10),
  otpExpiry DATETIME,
  otpVerifiedAt DATETIME,
  created DATETIME DEFAULT CURRENT_TIMESTAMP,
  status TINYINT DEFAULT 1
);

CREATE TABLE mx_sky_padel_contract_milestone (
  milestoneID INT AUTO_INCREMENT PRIMARY KEY,
  contractID INT NOT NULL,
  milestoneName VARCHAR(200),
  milestoneDescription TEXT,
  paymentPercentage DECIMAL(5,2),
  paymentAmount DECIMAL(15,2),
  dueAfterDays INT DEFAULT 0,
  sortOrder INT DEFAULT 0
);
```

---

## Quotation Revision Tracking

### Current State:
- `parentQuotationID` field exists
- `revisionNumber` field exists
- `isLatestRevision` field exists

### Enhancement Needed:
1. Show revision history in quotation view
2. Link all revisions to parent quotation
3. Track changes between revisions
4. Audit trail: who revised, when, why

---

## BOM Module (New)

### Tables Required:
```sql
CREATE TABLE mx_sky_padel_bom (
  bomID INT AUTO_INCREMENT PRIMARY KEY,
  projectID INT NOT NULL,
  bomNo VARCHAR(50) UNIQUE,
  bomDate DATE,
  totalItems INT DEFAULT 0,
  estimatedCost DECIMAL(15,2) DEFAULT 0,
  actualCost DECIMAL(15,2) DEFAULT 0,
  bomStatus ENUM('Draft','Approved','InProgress','Completed') DEFAULT 'Draft',
  createdBy INT,
  created DATETIME DEFAULT CURRENT_TIMESTAMP,
  status TINYINT DEFAULT 1
);

CREATE TABLE mx_sky_padel_bom_item (
  itemID INT AUTO_INCREMENT PRIMARY KEY,
  bomID INT NOT NULL,
  productID INT,
  productSKU VARCHAR(50),
  productName VARCHAR(255),
  quantity DECIMAL(10,2) DEFAULT 1,
  uom VARCHAR(20) DEFAULT 'Pcs',
  estimatedRate DECIMAL(15,2) DEFAULT 0,
  estimatedAmount DECIMAL(15,2) DEFAULT 0,
  allocatedQty DECIMAL(10,2) DEFAULT 0,
  consumedQty DECIMAL(10,2) DEFAULT 0,
  warehouseID INT,
  allocationStatus ENUM('Pending','Partial','Allocated','Consumed') DEFAULT 'Pending',
  status TINYINT DEFAULT 1,
  FOREIGN KEY (bomID) REFERENCES mx_sky_padel_bom(bomID)
);
```

### Module Files:
```
xadmin/mod/sky-padel-bom/
├── x-sky-padel-bom.inc.php
├── x-sky-padel-bom-list.php
├── x-sky-padel-bom-add-edit.php
└── inc/js/x-sky-padel-bom.inc.js
```

---

## Gantt Dashboard Notes

**IMPORTANT:** Must use xAdmin-compatible styling
- Use existing xAdmin CSS classes
- Do not break existing layout
- Reference other dashboards before customizing
- Recommended library: Frappe Gantt (lightweight)
