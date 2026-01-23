# Padel Park Platform - Complete Module Documentation

**Created:** 2026-01-21
**Last Updated:** 2026-01-22
**Purpose:** Comprehensive documentation of all Padel Park platform modules

**Recent Updates:** See [RECENT_UPDATES_JAN_2026.md](./RECENT_UPDATES_JAN_2026.md) for latest changes, bug fixes, and enhancements.

---

## Table of Contents

1. [Platform Overview](#platform-overview)
2. [Sky Padel (B2B Court Construction)](#1-sky-padel-b2b-court-construction)
3. [Pay n Play (Court Booking & Retail)](#2-pay-n-play-pnp-court-booking--retail)
4. [IPT (Indian Padel Tournament)](#3-ipt-indian-padel-tournament)
5. [IPA (Indian Padel Academy)](#4-ipa-indian-padel-academy)
6. [Warehouse & Inventory Management](#5-warehouse--inventory-management)
7. [Vendor Management](#6-vendor-management)
8. [Architecture & Standards](#7-architecture--standards)
9. [Portal URLs & Access](#8-portal-urls--access)

---

## Platform Overview

The Padel Park platform is a comprehensive business management system for padel sports operations, consisting of multiple integrated modules:

| Module | Purpose | Admin Location | Frontend Portal |
|--------|---------|----------------|-----------------|
| Sky Padel | B2B Court Construction Projects | `/xadmin/mod/sky-padel*/` | `/skypadel/` |
| Pay n Play | Court Booking & Retail POS | `/xadmin/mod/pnp-*/` | `/paynplay/` |
| IPT | Tournament Management | `/xadmin/mod/ipt-*/` | `/ipt/` |
| IPA | Academy Management | `/xadmin/mod/ipa-*/` | - |
| Warehouse | Inventory Management | `/xadmin/mod/warehouse*/` | - |
| Vendor | Procurement & Vendors | `/xadmin/mod/vendor-*/` | `/vendorportal/` |

---

## 1. Sky Padel (B2B Court Construction)

### Overview
Complete project lifecycle management for padel court construction - from lead capture to project completion.

### Module Locations
- **Admin Modules:** `/xadmin/mod/sky-padel*/`
- **Client Portal:** `/skypadel/`

### Admin Modules

| Module | Directory | Purpose |
|--------|-----------|---------|
| Lead Management | `sky-padel-lead/` | Capture and track leads |
| Site Visit | `sky-padel-site-visit/` | Schedule and log site visits |
| Site Report | `sky-padel-site-report/` | Create site assessment reports |
| Quotation | `sky-padel-quotation/` | Create quotes with milestones |
| Contract | `sky-padel-contract/` | Digital contracts with OTP signing |
| Proforma | `sky-padel-proforma/` | Proforma invoice generation |
| Project | `sky-padel-project/` | Project tracking with Gantt chart |
| Payment | `sky-padel-payment/` | Payment milestone tracking |
| Expense | `sky-padel-expense/` | Project expense tracking |
| Report | `sky-padel-report/` | Financial & project reports |
| Dashboard | `sky-padel-dashboard/` | Admin analytics dashboard |

### Client Portal Pages

| Page | File | Features |
|------|------|----------|
| Homepage | `index.php` | Lead form, 3D court display |
| Login | `login.php` | Email + OTP authentication |
| Dashboard | `dashboard.php` | Project stats, pending payments |
| Projects | `projects.php` | List of client projects |
| Project Detail | `project-detail.php` | Milestones, progress, timeline |
| Quotations | `quotations.php` | View and approve quotations |
| Quotation View | `quotation-view.php` | Detailed quotation with approval |
| Contracts | `contracts.php` | Contract list with status |
| Contract Sign | `contract-sign.php` | OTP-verified digital signing |
| Invoices | `invoices.php` | Proforma invoices |
| Payments | `payments.php` | Payment schedule and history |

### Database Tables

```sql
-- Core Tables
mx_sky_padel_lead              -- Lead information
mx_sky_padel_site_visit        -- Site visit records
mx_sky_padel_site_report       -- Site assessment reports
mx_sky_padel_quotation         -- Quotation master
mx_sky_padel_quotation_milestone -- Quotation payment milestones
mx_sky_padel_contract          -- Signed contracts
mx_sky_padel_contract_milestone -- Contract milestones
mx_sky_padel_proforma_invoice  -- Proforma invoices
mx_sky_padel_proforma_item     -- Invoice line items
mx_sky_padel_proforma_milestone -- Invoice milestones
mx_sky_padel_project           -- Active projects
mx_sky_padel_payment           -- Payment records
mx_sky_padel_project_expense   -- Project expenses

-- Client Portal Auth
mx_sky_padel_client_auth       -- OTP authentication
mx_sky_padel_client_activity   -- Activity audit log
```

### Key Workflows

#### Lead to Project Flow
```
Lead Created (Website/Manual)
    ↓
Site Visit Scheduled → Completed
    ↓
Site Report Created
    ↓
Quotation Created → Sent to Client
    ↓
Client Reviews → Approves/Rejects
    ↓
Contract Auto-Generated
    ↓
Client Signs (OTP Verified)
    ↓
Proforma Invoice Generated
    ↓
Project Created → Active
    ↓
Milestones Tracked → Payments Recorded
    ↓
Project Completed
```

#### Contract Signing (OTP Flow)
1. Client views contract in portal
2. Clicks "Sign Contract"
3. OTP sent to registered email
4. Client enters OTP (10-minute expiry, max 5 attempts)
5. Client types name + accepts terms
6. Contract status → "Signed"
7. Signature IP, timestamp, method recorded

### Key Features
- Version-controlled quotations with revision tracking
- 20-section Indian legal contract template
- OTP-verified digital signatures
- Automatic proforma generation on contract signing
- Milestone-based payment tracking
- Project Gantt chart visualization
- Real-time client portal synchronization

---

## 2. Pay n Play (PNP) - Court Booking & Retail

### Overview
Court booking management, equipment rental, and point-of-sale system for padel centers.

### Module Locations
- **Admin Modules:** `/xadmin/mod/pnp-*/`
- **POS Portal:** `/paynplay/`

### Admin Modules

| Module | Directory | Purpose |
|--------|-----------|---------|
| Location | `pnp-location/` | Manage padel center locations |
| Court | `pnp-court/` | Court configuration and rates |
| Booking | `pnp-booking/` | Reservation management |
| Equipment | `pnp-equipment/` | Rental inventory |
| Rental | `pnp-rental/` | Equipment rental tracking |
| Invoice | `pnp-invoice/` | Invoice generation |
| Dashboard | `pnp-dashboard/` | Analytics dashboard |

### POS Terminal Pages

| Page | File | Features |
|------|------|----------|
| Login | `index.php` | Staff PIN-based login |
| Dashboard | `dashboard.php` | Full POS terminal |

### API Endpoints

| Endpoint | File | Purpose |
|----------|------|---------|
| Login | `api/login.php` | Staff authentication |
| Billing | `api/billing.php` | Process sales transactions |
| Pine Labs | `api/pinelabs.php` | Card payment integration |
| Stats | `api/stats.php` | Real-time sales statistics |
| Stock | `api/stock.php` | Inventory lookup |

### Database Tables

```sql
-- Core Tables
pnp_location              -- Padel center locations
pnp_court                 -- Courts per location
pnp_booking               -- Court reservations
pnp_equipment             -- Rental equipment inventory
pnp_rental                -- Equipment rental transactions
pnp_rental_item           -- Rental line items
pnp_invoice               -- All invoices (booking/rental/sale)
pnp_retail_sale           -- POS sale transactions
pnp_retail_sale_item      -- Sale line items
pnp_staff                 -- Terminal staff
pnp_feedback              -- Customer feedback
pnp_consumption           -- Court resource tracking
pnp_pinelabs_transaction  -- Card payment logs
```

### Key Workflows

#### Court Booking Flow
```
Customer Arrives/Calls
    ↓
Staff Selects Location → Court → Time Slot
    ↓
Enter Customer Details
    ↓
Calculate Duration & Amount
    ↓
Check-In at Arrival
    ↓
Check-Out at Session End
    ↓
Auto-Generate Invoice
```

#### POS Retail Flow
```
Staff Logs In (PIN)
    ↓
Search/Scan Products
    ↓
Add to Cart
    ↓
System Calculates GST (18%)
    ↓
Select Payment: Cash/UPI/Card
    ↓
For Card: Pine Labs Terminal Integration
    ↓
Complete Sale → Update Inventory
    ↓
Generate Receipt
```

### Key Features
- Multi-location support with auto-warehouse linking
- Court rate management (hourly, peak, weekend, member)
- Equipment rental with deposit and damage tracking
- Barcode scanning support
- Pine Labs card terminal integration
- Real-time inventory updates
- GST compliant invoicing (CGST 9% + SGST 9%)
- Hudle integration placeholder for booking sync

---

## 3. IPT (Indian Padel Tournament)

### Overview
Complete tournament management system with bracket generation, live scoring, and prize disbursement.

### Module Locations
- **Admin Modules:** `/xadmin/mod/ipt-*/`
- **Public Portal:** `/ipt/`

### Admin Modules

| Module | Directory | Purpose |
|--------|-----------|---------|
| Tournament | `ipt-tournament/` | Create and manage tournaments |
| Category | `ipt-category/` | Tournament category templates |
| Participant | `ipt-participant/` | Team registration management |
| Fixture | `ipt-fixture/` | Bracket and match management |
| Prize | `ipt-prize/` | Prize distribution with TDS |
| Sponsor | `ipt-sponsor/` | Sponsor management |
| Dashboard | `ipt-dashboard/` | Tournament analytics |

### Public Portal Pages

| Page | File | Features |
|------|------|----------|
| Homepage | `index.php` | Tournament listings by status |
| Tournament | `tournament.php` | Details, categories, draws, participants |
| Register | `register.php` | Multi-step team registration |

### API Endpoints

| Endpoint | File | Purpose |
|----------|------|---------|
| Live Scores | `api/scores.php` | Real-time match scores |

### Database Tables

```sql
-- Core Tables
mx_ipt_tournament           -- Tournament master
mx_ipt_category             -- Category templates (skill/age/gender)
mx_ipt_tournament_category  -- Tournament-category linking
mx_ipt_participant          -- Team registrations
mx_ipt_fixture              -- Match brackets and scores
mx_ipt_prize                -- Prize distribution
mx_ipt_sponsor              -- Tournament sponsors
```

### Key Workflows

#### Tournament Lifecycle
```
Create Tournament (Draft)
    ↓
Link Categories with Entry Fees
    ↓
Publish Tournament (Open for Registration)
    ↓
Teams Register Online
    ↓
Admin Confirms Participants
    ↓
Close Registration
    ↓
Generate Brackets (Smart Seeding)
    ↓
Tournament In-Progress
    ↓
Live Score Updates
    ↓
Winner Advances Automatically
    ↓
Tournament Completed
    ↓
Prize Disbursement (TDS Calculated)
    ↓
IPA Rankings Updated
```

#### Bracket Generation
- Supports 4, 8, 16, 32, 64 team draws
- Standard seeding algorithm (top seeds spread)
- BYE handling for incomplete brackets
- Auto-advancement when matches complete

### Key Features
- Multi-category tournaments (Singles/Doubles/Mixed)
- Age and skill level restrictions
- Online team registration with payment tracking
- Intelligent bracket generation with seeding
- Live scoring API with real-time updates
- TDS calculation (31.24% for prizes > ₹10,000)
- Sponsor management with deliverable tracking
- IPA ranking integration

### Prize TDS Calculation
```
If prizeAmount > 10000:
    tdsRate = 31.24%
    tdsDeducted = prizeAmount × 0.3124
    netAmount = prizeAmount - tdsDeducted
```

---

## 4. IPA (Indian Padel Academy)

### Overview
Academy management system for coaches, players, training programs, and assessments.

### Module Locations
- **Admin Modules:** `/xadmin/mod/ipa-*/`

### Admin Modules

| Module | Directory | Purpose |
|--------|-----------|---------|
| Coach | `ipa-coach/` | Coach profiles and metrics |
| Player | `ipa-player/` | Player profiles and membership |
| Program | `ipa-program/` | Training program definitions |
| Session | `ipa-session/` | Session scheduling and attendance |
| Coach Assessment | `ipa-coach-assessment/` | Formal player assessments |
| Coach Feedback | `ipa-coach-feedback/` | Per-session quick feedback |
| Coach Review | `ipa-coach-review/` | Coach performance reviews |
| Student Feedback | `ipa-student-feedback/` | Player feedback on coaches |
| Player Progress | `ipa-player-progress/` | Progress tracking dashboard |
| Commission | `ipa-commission/` | Coach retail commissions |
| Requisition | `ipa-requisition/` | Equipment requisitions |
| Dashboard | `ipa-dashboard/` | Academy analytics |

### Database Tables

```sql
-- Core Tables
mx_ipa_coach                    -- Coach profiles
mx_ipa_player                   -- Player profiles
mx_ipa_program                  -- Training programs
mx_ipa_session                  -- Training sessions
mx_ipa_session_participant      -- Session enrollments

-- Assessment & Feedback
mx_ipa_coach_assessment         -- Formal assessments (6 dimensions)
mx_ipa_coach_session_feedback   -- Per-session quick feedback
mx_ipa_coach_review             -- Coach performance reviews (8 metrics)
mx_ipa_student_feedback         -- Player feedback with tokens

-- Operations
mx_ipa_coach_commission         -- Retail commission tracking
mx_ipa_requisition              -- Equipment requisitions
mx_ipa_requisition_item         -- Requisition line items
```

### Assessment Dimensions

#### Player Assessment (6 Dimensions)
| Dimension | Scale |
|-----------|-------|
| Technical Skills | 0-5 |
| Tactical Awareness | 0-5 |
| Physical Fitness | 0-5 |
| Mental Strength | 0-5 |
| Game Strategy | 0-5 |
| Consistency | 0-5 |

#### Coach Review (8 Metrics)
| Metric | Scale |
|--------|-------|
| Session Quality | 1-5 |
| Student Engagement | 1-5 |
| Punctuality | 1-5 |
| Professionalism | 1-5 |
| Technical Knowledge | 1-5 |
| Communication Skills | 1-5 |
| Student Progress | 1-5 |
| Teamwork | 1-5 |

### Key Workflows

#### Session Completion → Feedback
```
Session Marked "Completed"
    ↓
System Auto-Generates Feedback Tokens
    ↓
Email Sent to All Participants
    ↓
Token Valid for 7 Days
    ↓
Player Submits Feedback
    ↓
Coach Average Rating Updated
```

#### Commission Workflow
```
Coach Generates Retail Sale
    ↓
Admin Creates Commission Record
    ↓
Status: Pending
    ↓
Admin Approves
    ↓
Status: Approved
    ↓
Admin Marks Paid (with reference)
    ↓
Coach Total Commission Updated
```

#### Requisition Workflow
```
Coach Creates Draft Requisition
    ↓
Adds Items (Products from Inventory)
    ↓
Submits for Approval
    ↓
Admin Reviews
    ↓
Approves (adjusts quantities) or Rejects
    ↓
Warehouse Issues Items
```

### Key Features
- Coach certification and employment tracking
- Player membership management
- Multiple program types (Clinic, Private, Group, Camp, Masterclass)
- Automated feedback emails on session completion
- Secure token-based feedback (7-day expiry)
- Comprehensive skill assessment framework
- Coach performance review system
- Commission tracking with approval workflow
- Equipment requisition with stock availability check
- Player progress visualization

---

## 5. Warehouse & Inventory Management

### Overview
Multi-warehouse inventory management with stock allocation, transfers, and goods receipt.

### Module Locations
- **Admin Modules:** `/xadmin/mod/warehouse*/`, `inventory-stock/`, `stock-*/`, `site-grn/`, `product*/`

### Admin Modules

| Module | Directory | Purpose |
|--------|-----------|---------|
| Warehouse | `warehouse/` | Warehouse master data |
| Warehouse Dashboard | `warehouse-dashboard/` | Inventory analytics |
| Inventory Stock | `inventory-stock/` | Stock levels and adjustments |
| Stock Allocation | `stock-allocation/` | Project stock reservation |
| Stock Transfer | `stock-transfer/` | Inter-warehouse transfers |
| Stock Ledger | `stock-ledger/` | Transaction audit trail |
| Site GRN | `site-grn/` | Goods Receipt at project sites |
| Product | `product/` | Product master data |
| Product Brand | `product-brand/` | Brand master |
| Product Category | `product-category/` | Category hierarchy |

### Database Tables

```sql
-- Warehouse
mx_warehouse              -- Warehouse master

-- Inventory
mx_inventory_stock        -- Stock levels per warehouse
mx_stock_ledger           -- Transaction audit trail

-- Allocations & Transfers
mx_stock_allocation       -- Project stock allocations
mx_stock_allocation_item  -- Allocation line items

-- Goods Receipt
mx_site_grn               -- GRN headers
mx_site_grn_item          -- GRN line items

-- Products
mx_product                -- Product master
mx_product_brand          -- Brand master
mx_product_category       -- Category hierarchy
```

### Inventory Quantity States

```
mx_inventory_stock:
├── quantity        -- Total stock
├── availableQty    -- Free for use (qty - reserved - inTransit)
├── reservedQty     -- Allocated to projects
├── inTransitQty    -- Dispatched, not yet received
└── damagedQty      -- Non-usable inventory
```

### Warehouse Types

| Type | Purpose |
|------|---------|
| Main | Central distribution hub |
| Sub-Warehouse | Regional storage |
| In-Transit | Temporary staging |
| Project-Site | Final delivery location |

### Stock Ledger Transaction Types

| Type | Direction | Description |
|------|-----------|-------------|
| GRN | IN | Purchase receipt |
| Sale | OUT | Sale transaction |
| Adjustment | IN/OUT | Manual adjustment |
| Transfer-In | IN | Received from warehouse |
| Transfer-Out | OUT | Sent to warehouse |
| Consumption | OUT | Production use |
| Return | IN | Customer return |
| Damage | OUT | Damaged write-off |
| Reserved | - | Allocation reservation |
| Unreserved | - | Allocation cancellation |
| Opening | IN | Initial stock entry |

### Key Workflows

#### Stock Allocation → Dispatch → GRN
```
Project Created
    ↓
Admin Creates Stock Allocation
    ↓
Items Reserved (availableQty ↓, reservedQty ↑)
    ↓
Admin Dispatches Allocation
    ↓
Items In-Transit (reservedQty ↓, inTransitQty ↑)
    ↓
Site Creates GRN
    ↓
Records Received Quantities
    ↓
Admin Accepts GRN
    ↓
Items Cleared (inTransitQty ↓)
```

#### Stock Transfer
```
Select Source Warehouse
    ↓
Select Destination Warehouse
    ↓
Select Product & Quantity
    ↓
System Validates Available Stock
    ↓
Transfer Executed
    ↓
Source: Transfer-Out Ledger Entry
    ↓
Destination: Transfer-In Ledger Entry
```

### Key Features
- Multi-warehouse with location tracking
- 4-tier quantity tracking (available, reserved, in-transit, damaged)
- Project-based stock allocation
- Inter-warehouse transfers with validation
- Site GRN with shortage/excess tracking
- Complete audit trail via stock ledger
- Low stock alerts based on reorder level
- Hierarchical product categories

---

## 6. Vendor Management

### Overview
Complete B2B procurement workflow from vendor registration to quote award.

### Module Locations
- **Admin Modules:** `/xadmin/mod/vendor-*/`
- **Vendor Portal:** `/xsite/mod/vendorportal/` (accessed via `/vendorportal/`)

### Admin Modules

| Module | Directory | Purpose |
|--------|-----------|---------|
| Vendor Onboarding | `vendor-onboarding/` | Vendor registration & approval |
| Vendor Approval | `vendor-approval/` | Approval workflow dashboard |
| Vendor Approved | `vendor-approved/` | Approved vendors list |
| Vendor Category | `vendor-category/` | Vendor categorization |
| Vendor Document | `vendor-document/` | Document verification |
| Vendor RFQ | `vendor-rfq/` | Request for Quotations |
| Vendor Quote | `vendor-quote/` | Quote evaluation |

### Vendor Portal Pages

| Page | File | Features |
|------|------|----------|
| Login | `x-login.php` | Email/password authentication |
| Registration | `x-registration.php` | Self-service vendor signup |
| Dashboard | `x-dashboard.php` | Stats, recent RFQs, quotes |
| RFQ List | `x-rfq-list.php` | Browse available RFQs |
| Quote Submit | `x-quote-submit.php` | Submit bids on RFQs |
| My Quotes | `x-quotes.php` | Track submitted quotes |
| Quote View | `x-quote-view.php` | Quote details |
| Orders | `x-orders.php` | Awarded quotes |
| Profile | `x-profile.php` | Company profile management |
| Documents | `x-documents.php` | Document upload & management |

### Database Tables

```sql
-- Vendor Master
mx_vendor_onboarding        -- Vendor company information
mx_vendor_portal_user       -- Portal login accounts
mx_vendor_document          -- Uploaded documents
mx_vendor_category          -- Vendor categories

-- Procurement
mx_vendor_rfq               -- Request for Quotations
mx_vendor_rfq_item          -- RFQ line items
mx_vendor_quote             -- Vendor quotes
mx_vendor_quote_item        -- Quote line items
```

### Vendor Status Workflow

```
Pending → Approved → (Active Vendor)
       ↘ Disapproved
       ↘ Blocked
```

### RFQ Status Workflow

```
Draft → Published → Closed → Awarded
                          ↘ Cancelled
```

### Quote Status Workflow

```
Draft → Submitted → Under Review → Shortlisted → Accepted
                              ↘ Rejected
                              ↘ Expired
```

### Key Workflows

#### Vendor Onboarding
```
Vendor Fills Registration Form
    ↓
System Generates Vendor Code (VND-YYYYMMDD-XXXX)
    ↓
Creates Record (Status: Pending)
    ↓
Admin Reviews Information
    ↓
Admin Approves/Disapproves
    ↓
If Approved: Portal Auto-Activated
    ↓
Vendor Uploads Required Documents
    ↓
Admin Verifies Documents
    ↓
Vendor Active & Can Submit Quotes
```

#### RFQ to Award Flow
```
Admin Creates RFQ (Draft)
    ↓
Adds Line Items (Products/Services)
    ↓
Publishes RFQ
    ↓
Vendors Submit Quotes
    ↓
Admin Evaluates (Technical + Commercial Scores)
    ↓
Shortlists Best Quotes
    ↓
Awards Winning Quote
    ↓
Other Quotes Auto-Rejected
    ↓
Vendor Sees "Accepted" Status
```

### Document Types

| Type | Description |
|------|-------------|
| Registration Certificate | Company registration |
| GST Certificate | GST registration |
| PAN Card | Tax ID |
| MSME Certificate | MSME registration |
| Cancelled Cheque | Bank verification |
| Bank Statement | Financial verification |
| ISO Certificate | Quality certification |

### Key Features
- Self-service vendor registration
- Multi-step approval workflow
- Document management with verification
- Public and invited-only RFQs
- Quote submission with line items
- Technical and commercial scoring
- Award with auto-rejection of others
- GST-compliant invoicing
- MSME verification support

---

## 7. Architecture & Standards

### File Naming Convention

```
x-{module-name}.inc.php       -- Core functions (CRUD)
x-{module-name}-add-edit.php  -- Form UI
x-{module-name}-list.php      -- List view
x-{module-name}-view.php      -- Detail view (optional)
x-{module-name}-ajax.php      -- AJAX handlers (optional)
inc/js/x-{module-name}.inc.js -- JavaScript (optional)
```

### Database Standards

| Standard | Pattern |
|----------|---------|
| Primary Key | `{entity}ID` (e.g., `projectID`) |
| Auto-increment | All primary keys |
| Soft Delete | `status` field (1=active, 0=deleted) |
| Timestamps | `created`, `modified` |
| Reference Numbers | Auto-generated with date prefix |
| Foreign Keys | `{related_entity}ID` |

### Reference Number Formats

| Entity | Format | Example |
|--------|--------|---------|
| Lead | SPL-YYYY-NNNN | SPL-2026-0001 |
| Quotation | SPQ-YYYYMMDD-XXXX | SPQ-20260121-0001 |
| Contract | CON-YYYYMMDD-XXXX | CON-20260121-0001 |
| Project | PRJ-YYYYMMDD-XXXX | PRJ-20260121-0001 |
| Booking | BK-YYYYMMDD-XXXX | BK-20260121-0001 |
| Rental | RN-YYYYMMDD-XXXX | RN-20260121-0001 |
| Invoice | INV-YYYYMMDD-XXXX | INV-20260121-0001 |
| Tournament | IPT-YYYY-NNN | IPT-2026-001 |
| Registration | IPT-YYYYMMDD-XXXX | IPT-20260121-0001 |
| Vendor | VND-YYYYMMDD-XXXX | VND-20260121-0001 |
| RFQ | RFQ-YYYYMMDD-XXXX | RFQ-20260121-0001 |
| Allocation | SA-YYYYMMDD-XXXX | SA-20260121-0001 |
| GRN | SGRN-YYYYMMDD-XXXX | SGRN-20260121-0001 |

### API Response Format

```json
{
    "err": 0,           // 0 = success, 1 = error
    "msg": "Success",   // User-friendly message
    "data": {}          // Response payload
}
```

### Security Patterns

- `mxCheckRequest()` - Authentication validation
- Prepared statements for all queries
- Type-safe parameter binding
- CSRF token verification (Vendor Portal)
- OTP-based authentication (Sky Padel Client Portal)
- Password hashing with `password_hash()` (bcrypt)

---

## 8. Portal URLs & Access

### Production URLs

| Portal | URL |
|--------|-----|
| xAdmin | `https://pp.paritoshajmera.com/xadmin` |
| Sky Padel Client | `https://pp.paritoshajmera.com/skypadel` |
| Pay & Play POS | `https://pp.paritoshajmera.com/paynplay` |
| IPT Tournament | `https://pp.paritoshajmera.com/ipt` |
| Vendor Portal | `https://pp.paritoshajmera.com/vendorportal` |
| Main Site | `https://pp.paritoshajmera.com/xsite` |

### Authentication Methods

| Portal | Method |
|--------|--------|
| xAdmin | JWT token-based |
| Sky Padel Client | Email + OTP |
| Pay & Play POS | Staff Code + PIN |
| IPT | Public (no auth) |
| Vendor Portal | Email + Password (session) |

### Database Configuration

```php
// Main Admin (config.inc.php)
$DBHOST = "localhost";
$DBUSER = "padelpark";
$DBPASS = "Padel@2024#Secure";
$DBNAME = "padelpark_bombayengg";

// Portals use same database with module-specific tables
```

---

## Quick Reference

### Module Prefixes

| Prefix | Module |
|--------|--------|
| `sky-padel-*` | Sky Padel (Court Construction) |
| `pnp-*` | Pay n Play (Booking/Retail) |
| `ipt-*` | IPT (Tournament) |
| `ipa-*` | IPA (Academy) |
| `warehouse*` | Warehouse Management |
| `stock-*` | Stock Operations |
| `product*` | Product Management |
| `vendor-*` | Vendor Management |

### Table Prefixes

| Prefix | Module |
|--------|--------|
| `mx_sky_padel_*` | Sky Padel |
| `pnp_*` | Pay n Play |
| `mx_ipt_*` | IPT |
| `mx_ipa_*` | IPA |
| `mx_warehouse` | Warehouse |
| `mx_inventory_*` | Inventory |
| `mx_stock_*` | Stock Operations |
| `mx_product*` | Products |
| `mx_vendor_*` | Vendor Management |

---

## Changelog

### Version 1.2 - 2026-01-22
**Major Updates:**
- SSL Certificate: Installed Let's Encrypt SSL (RSA 2048-bit)
- 7Padel Migration: Updated all locations from Sky Padel to 7Padel branding (12 locations, 29 courts)
- Bug Fixes: Fixed CSRF token issues across 11+ admin modules
- Product Catalog: Imported complete Bullpadel range (29 products from SCS Sports)
- Distributor: Added Selection Centre Sports as authorized distributor
- Inventory: Consolidated all rackets/balls to main warehouse (858 units)
- Automation: Implemented auto-complete booking feature
- Categories: Added Beverages, Snacks & Food, Rentals categories
- HSN Codes: Added 8 new HSN codes for product compliance

**See:** [RECENT_UPDATES_JAN_2026.md](./RECENT_UPDATES_JAN_2026.md) for detailed documentation.

### Version 1.1 - 2026-01-21
- Initial comprehensive documentation of all platform modules
- Documented Sky Padel, PNP, IPT, IPA, Warehouse, and Vendor modules
- Architecture and standards documented

### Version 1.0 - 2026-01-15
- Platform development completed
- All core modules operational

---

*Document maintained for development reference. Update as modules evolve.*
