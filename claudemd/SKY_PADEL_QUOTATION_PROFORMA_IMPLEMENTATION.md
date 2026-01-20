# Sky Padel Quotation Revision & Proforma Invoice Implementation

**Date:** 2025-12-24
**Last Updated:** 2026-01-20
**Status:** COMPLETED

---

## Overview

Implemented two key features for the Sky Padel quotation workflow:
1. **Quotation Revisions** - When rejected, allow creating revised quotations with version tracking
2. **Proforma Invoice Auto-Generation** - When approved, auto-create proforma invoice with payment milestones

---

## Database Changes Made

### 1. Modified `mx_sky_padel_quotation` Table
```sql
ALTER TABLE mx_sky_padel_quotation
ADD COLUMN parentQuotationID INT DEFAULT NULL,
ADD COLUMN revisionNumber INT DEFAULT 0,
ADD COLUMN isLatestRevision TINYINT DEFAULT 1,
ADD COLUMN revisionNotes TEXT,
ADD COLUMN proformaGenerated TINYINT DEFAULT 0,
ADD COLUMN proformaID INT DEFAULT NULL;
```

### 2. Created `mx_sky_padel_quotation_milestone` Table
```sql
CREATE TABLE mx_sky_padel_quotation_milestone (
  milestoneID INT AUTO_INCREMENT PRIMARY KEY,
  quotationID INT NOT NULL,
  milestoneName VARCHAR(200) NOT NULL,
  milestoneDescription TEXT,
  paymentPercentage DECIMAL(5,2) DEFAULT 0,
  paymentAmount DECIMAL(15,2) DEFAULT 0,
  dueAfterDays INT DEFAULT 0,
  sortOrder INT DEFAULT 0,
  FOREIGN KEY (quotationID) REFERENCES mx_sky_padel_quotation(quotationID) ON DELETE CASCADE
);
```

### 3. Created `mx_sky_padel_proforma_invoice` Table
```sql
CREATE TABLE mx_sky_padel_proforma_invoice (
  proformaID INT AUTO_INCREMENT PRIMARY KEY,
  proformaNo VARCHAR(50) UNIQUE NOT NULL,
  quotationID INT NOT NULL,
  leadID INT NOT NULL,
  projectID INT DEFAULT NULL,
  clientName VARCHAR(200),
  clientEmail VARCHAR(150),
  clientPhone VARCHAR(20),
  clientCompany VARCHAR(200),
  clientAddress TEXT,
  clientCity VARCHAR(100),
  clientState VARCHAR(100),
  clientPincode VARCHAR(20),
  clientGSTIN VARCHAR(50),
  invoiceDate DATE NOT NULL,
  validUntil DATE,
  courtConfiguration VARCHAR(100),
  scopeOfWork TEXT,
  subtotal DECIMAL(15,2) DEFAULT 0,
  discountAmount DECIMAL(15,2) DEFAULT 0,
  taxableAmount DECIMAL(15,2) DEFAULT 0,
  cgstRate DECIMAL(5,2) DEFAULT 9,
  cgstAmount DECIMAL(15,2) DEFAULT 0,
  sgstRate DECIMAL(5,2) DEFAULT 9,
  sgstAmount DECIMAL(15,2) DEFAULT 0,
  totalTaxAmount DECIMAL(15,2) DEFAULT 0,
  totalAmount DECIMAL(15,2) NOT NULL,
  amountInWords VARCHAR(500),
  paymentTerms TEXT,
  bankDetails TEXT,
  termsAndConditions TEXT,
  notes TEXT,
  invoiceStatus ENUM('Generated','Sent','Acknowledged','Partial Payment','Paid','Cancelled') DEFAULT 'Generated',
  sentDate DATETIME,
  acknowledgedDate DATETIME,
  generatedBy INT,
  status TINYINT DEFAULT 1,
  created DATETIME DEFAULT CURRENT_TIMESTAMP,
  modified DATETIME ON UPDATE CURRENT_TIMESTAMP
);
```

### 4. Created `mx_sky_padel_proforma_milestone` Table
```sql
CREATE TABLE mx_sky_padel_proforma_milestone (
  milestoneID INT AUTO_INCREMENT PRIMARY KEY,
  proformaID INT NOT NULL,
  milestoneName VARCHAR(200) NOT NULL,
  milestoneDescription TEXT,
  paymentPercentage DECIMAL(5,2) DEFAULT 0,
  paymentAmount DECIMAL(15,2) DEFAULT 0,
  dueAfterDays INT DEFAULT 0,
  sortOrder INT DEFAULT 0,
  FOREIGN KEY (proformaID) REFERENCES mx_sky_padel_proforma_invoice(proformaID) ON DELETE CASCADE
);
```

### 5. Created `mx_sky_padel_proforma_item` Table
```sql
CREATE TABLE mx_sky_padel_proforma_item (
  itemID INT AUTO_INCREMENT PRIMARY KEY,
  proformaID INT NOT NULL,
  itemDescription VARCHAR(500) NOT NULL,
  hsnCode VARCHAR(20),
  quantity DECIMAL(10,2) DEFAULT 1,
  unitPrice DECIMAL(15,2) DEFAULT 0,
  taxRate DECIMAL(5,2) DEFAULT 18,
  taxAmount DECIMAL(15,2) DEFAULT 0,
  totalPrice DECIMAL(15,2) DEFAULT 0,
  sortOrder INT DEFAULT 0,
  FOREIGN KEY (proformaID) REFERENCES mx_sky_padel_proforma_invoice(proformaID) ON DELETE CASCADE
);
```

### 6. Enhanced `mx_project_milestone` Table
```sql
ALTER TABLE mx_project_milestone
ADD COLUMN paymentPercentage DECIMAL(5,2) DEFAULT 0,
ADD COLUMN paymentAmount DECIMAL(15,2) DEFAULT 0,
ADD COLUMN dueDate DATE,
ADD COLUMN paymentStatus ENUM('Pending','Invoiced','Paid') DEFAULT 'Pending',
ADD COLUMN linkedPaymentID INT DEFAULT NULL;
```

### 7. Updated Lead Status Enum
```sql
ALTER TABLE mx_sky_padel_lead
MODIFY COLUMN leadStatus ENUM('New', 'Contacted', 'Site Visit Scheduled', 'Site Visit Done',
  'Quotation Sent', 'Quotation Approved', 'Quotation Rejected', 'Revision in Progress',
  'Converted', 'Lost') DEFAULT 'New';
```

### 8. Added Menu Entry
```sql
INSERT INTO mx_x_admin_menu (menuType, menuTitle, seoUri, parentID, xOrder, status)
VALUES (1, 'Proforma Invoices', 'sky-padel-proforma', [Sky Padel Parent ID], 10, 1);
```

---

## Files Created

### New Proforma Invoice Module
```
E:\xampp\htdocs\bes\xadmin\mod\sky-padel-proforma\
├── x-sky-padel-proforma.inc.php      # Backend logic
├── x-sky-padel-proforma-list.php     # List view
├── x-sky-padel-proforma-add-edit.php # Form with milestone editor
└── inc\js\
    └── x-sky-padel-proforma.inc.js   # JavaScript
```

---

## Files Modified

### 1. `xadmin/mod/sky-padel-quotation/x-sky-padel-quotation.inc.php`
- Added `saveQuotationMilestones()` function
- Modified `addQuotation()` to handle revisions and save milestones
- Modified `updateQuotation()` to:
  - Save milestones on update
  - Auto-generate proforma invoice when status changes to "Approved"
  - Include `require_once` for proforma module

### 2. `xadmin/mod/sky-padel-quotation/x-sky-padel-quotation-add-edit.php`
- Added milestone loading on edit
- Added revision detection from URL (`?revisionOf=ID`)
- Added revision info banner
- Added payment milestones table section with:
  - Milestone name, description, percentage, amount, due days
  - Add/remove milestone buttons
  - Auto-calculate amounts based on total
  - Total percentage indicator
- Added revision notes field

### 3. `xadmin/mod/sky-padel-quotation/x-sky-padel-quotation-list.php`
- Added quotation number and status search filters
- Added revision badge (R1, R2...) next to quotation numbers
- Added "View Proforma" link for approved quotations with proforma
- Added "+ Project" button for approved quotations without project
- Added "Revise" button for rejected quotations
- Shows "Revision Pending" if revision already in progress

### 4. `xadmin/mod/sky-padel-project/x-sky-padel-project.inc.php`
- Added `copyProformaMilestonesToProject()` function
- Modified `addProject()` to:
  - Copy milestones from proforma/quotation when creating from quotation
  - Calculate due dates based on project start date + dueAfterDays
  - Link proforma to project

---

## Workflow Logic

### Quotation Revision Flow
```
Quotation Rejected
    → User clicks "Revise" button
    → Creates new quotation with:
        - New quotationNo: QT-YYYYMMDD-XXX-R1 (or R2, R3...)
        - parentQuotationID = original quotation ID
        - revisionNumber = previous max + 1
        - status = Draft
        - Copies all data and milestones
    → Marks old revisions as isLatestRevision = 0
    → Updates lead status to "Revision in Progress"
```

### Proforma Generation Flow
```
Quotation Approved (status change)
    → Auto-creates proforma invoice:
        - proformaNo: PI-YYYYMMDD-XXX
        - Copies client details from lead
        - Copies financial details from quotation
        - Copies milestones to proforma_milestone
    → Updates quotation: proformaGenerated=1, proformaID
    → Updates lead status to "Quotation Approved"
```

### Project Creation Flow
```
Create Project from Quotation
    → Fetches proforma milestones (or quotation milestones if no proforma)
    → Creates project_milestone records with:
        - paymentPercentage, paymentAmount from proforma
        - dueDate = projectStartDate + dueAfterDays
        - paymentStatus = 'Pending'
    → Links proforma to project (sets projectID)
```

---

## Key Features

### Payment Milestones
- Fully customizable per quotation
- Fields: Name, Description, Percentage (%), Amount (₹), Due After Days
- Default 3 milestones: Advance (50%), On Delivery (25%), Final (25%)
- Auto-calculates amounts based on total
- Shows total percentage with color indicator (green=100%, red=not 100%)

### Revision Tracking
- Revision number suffix: QT-20251224-001-R1, R2, R3...
- Links to parent quotation via parentQuotationID
- isLatestRevision flag to identify current version
- Revision notes field to document changes
- Full history preserved

### Proforma Invoice
- Auto-generated on quotation approval
- Includes GST calculations (CGST + SGST)
- Amount in words (Indian numbering system)
- Payment milestones copied from quotation
- Status tracking: Generated → Sent → Acknowledged → Paid
- Email functionality

---

## Menu Structure (After Implementation)

```
Sky Padel
├── Dashboard
├── Leads
├── Site Visits
├── Site Reports
├── Quotations (enhanced with milestones & revisions)
├── Projects (enhanced with payment milestones)
├── Vendor Categories
├── Vendors
├── Payments
└── Proforma Invoices (NEW)
```

---

## Testing Checklist

- [ ] Create new quotation with milestones
- [ ] Edit quotation and verify milestones saved
- [ ] Change quotation status to "Approved" - verify proforma auto-created
- [ ] View proforma invoice with copied details
- [ ] Change quotation status to "Rejected" - verify "Revise" button appears
- [ ] Create revision - verify new quotation number with -R1 suffix
- [ ] Create project from approved quotation - verify milestones copied
- [ ] Verify project milestones have payment fields and calculated due dates

---

## Notes

- Project milestones already existed in the system (displayed inside project page)
- Enhanced existing milestone table with payment tracking fields
- Proforma invoice module follows same patterns as other Sky Padel modules
- All database tables use foreign keys with CASCADE delete for data integrity

---

## Contract System (Added 2026-01-20)

### Contract PDF Template - Indian Legal Style

**Files:**
- `xadmin/mod/sky-padel-contract/x-sky-padel-contract-pdf.php` - PDF generator
- `skypadel/templates/contract-pdf-template.php` - Reusable template
- `skypadel/templates/contract-preview.php` - Preview with sample data

### Design Features
| Element | Description |
|---------|-------------|
| Border Frame | Ornate double-line border with corner ornaments |
| Colors | Deep green (#1a5c3a), parchment (#faf6ed), gold (#b8860b) |
| Typography | DejaVu Serif (body), DejaVu Sans Mono (numbers) |
| Value Box | Green gradient with white text, amount in Indian words (Lakh/Crore) |
| Terms | Preserves original numbering (1., 1.1, 2., 2.1, etc.) |

### Document Sections
1. **Contract Header** - Contract number, execution date
2. **Parties Section** - First Party (Sky Padel) & Second Party (Client) with full details
3. **Recitals** - WHEREAS clauses with legal language
4. **Contract Value** - Total amount with Indian number words
5. **Scope of Work** - Project description and configuration
6. **Payment Schedule** - Milestones table with percentages and amounts
7. **Terms & Conditions** - 20 sections covering all legal aspects
8. **Payment Terms** - Banking and GST details
9. **Signature Section** - Dual signature blocks
10. **Company Seal** - Designated seal area
11. **Witnesses** - Two witness blocks
12. **Jurisdiction** - Maharashtra courts, Indian law

### Client Portal Contract Pages
| Page | File | Features |
|------|------|----------|
| Contract List | `contracts.php` | All contracts with status badges |
| Contract Sign | `contract-sign.php` | 3-step OTP signing flow |

### Contract Signing Flow
```
Step 1: Request OTP
    → Send OTP to client email/phone

Step 2: Verify OTP
    → 6-digit input with 10-minute timer
    → Resend option after 30 seconds

Step 3: Sign Contract
    → Enter full name (signature)
    → Agree to Terms checkbox
    → Authorize Project checkbox
    → Submit with OTP verification

→ Contract marked as "Signed"
→ Proforma invoice generated
→ Client notified via email
```

### Terms & Conditions (20 Sections)
1. Definitions and Interpretation
2. Scope of Work
3. Client Obligations
4. Timeline and Delays
5. Variations and Changes
6. Quality and Materials
7. Inspection and Acceptance
8. Warranty (5 years structural, 2 years glass/lighting)
9. Liability and Indemnification
10. Insurance
11. Intellectual Property
12. Confidentiality
13. Termination
14. Dispute Resolution (Arbitration Act 1996)
15. Governing Law (Indian law)
16. Force Majeure
17. Entire Agreement
18. Amendments
19. Severability
20. Notices
