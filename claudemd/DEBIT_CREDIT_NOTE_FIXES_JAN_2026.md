# Debit Note & Credit Note Module Fixes - January 23, 2026

## Summary
Fixed critical issues in Debit Note and Credit Note modules including GSTIN autofill, form validation, and blank record prevention.

## Issues Fixed

### 1. GSTIN Autofill Not Working
**Problem**: When selecting a distributor or location, the GSTIN field was not auto-filling.

**Root Cause**: The `setResponse()` function in core/common.inc.php doesn't handle the "data" field - it was being ignored, causing AJAX responses to return "added successfully" instead of entity details.

**Solution**: Modified `getEntityDetails()` function to directly set `$MXRES["data"]` instead of relying on `setResponse()`.

**Files Changed**:
- `xadmin/mod/debit-note/x-debit-note.inc.php` - Line 512
- `xadmin/mod/credit-note/x-credit-note.inc.php` - Line 731

### 2. Blank Forms Being Submitted and Saved
**Problem**: Forms were submitting without required data (entity name, GSTIN, subtotal).

**Root Cause**:
- No client-side form framework validation attributes on required fields
- No server-side validation in add/update functions

**Solution**:
- Added `validate => "required"` attributes to entityName and entityGSTIN fields
- Added comprehensive server-side validation in PHP backend

**Files Changed**:
- `xadmin/mod/debit-note/x-debit-note-add-edit.php` - Lines 165-166
- `xadmin/mod/credit-note/x-credit-note-add-edit.php` - Lines 166-167
- `xadmin/mod/debit-note/x-debit-note.inc.php` - Lines 58-84, 214-240
- `xadmin/mod/credit-note/x-credit-note.inc.php` - Lines 59-85, 215-241

### 3. Simplified JavaScript Validation
**Problem**: Custom JavaScript validation was redundant with form framework validation.

**Solution**: Removed custom validation code since the form framework handles it automatically.

**Files Changed**:
- `xadmin/mod/debit-note/inc/js/x-debit-note.inc.js` - Line 91
- `xadmin/mod/credit-note/inc/js/x-credit-note.inc.js` - Line 96

## Validation Rules Implemented

### Client-Side (Form Framework)
- Entity Name - Required field (cannot be blank)
- GSTIN - Required field (cannot be blank)
- Subtotal - Required, must be numeric
- Red border highlighting for empty required fields
- Inline error messages
- Prevention of form submission until all required fields are filled

### Server-Side (PHP)
1. **Entity Selection Validation**:
   - If Distributor/Location selected: entityID must be > 0
   - If Customer selected: entityName must not be blank

2. **Required Fields**:
   - Entity Name must not be blank
   - Entity GSTIN must not be blank
   - Subtotal must be > 0

3. **Error Response**: Returns detailed error message listing all validation failures

## Technical Details

### GSTIN Autofill Flow
```
User selects Distributor → Triggers onChange event →
AJAX call to GET_ENTITY_DETAILS →
Backend queries distributor table →
Returns { err: 0, data: { gstin: "...", name: "..." } } →
JavaScript populates entityGSTIN and entityName fields
```

### Form Validation Flow
```
User clicks Save →
Client-side validation checks required fields →
If valid: Form submits →
Server-side validation checks entity, GSTIN, amounts →
If valid: Record saved →
Success response returned
```

## Testing Checklist

- [x] GSTIN autofills when selecting distributor
- [x] GSTIN autofills when selecting location
- [x] Form shows red border for empty required fields
- [x] Form prevents submission without entity selection
- [x] Form prevents submission without GSTIN
- [x] Form prevents submission with subtotal = 0
- [x] Server-side validation prevents blank records
- [x] Error messages are clear and specific
- [x] Works same as other modules (warehouse, product, etc.)

## Modules Affected
- Debit Note (Finance → Debit Note)
- Credit Note (Finance → Credit Note)

## Related Context
This fix was part of a larger effort to fix save button issues across multiple module groups. The same validation pattern is now consistent with:
- Warehouse modules
- Product modules
- Vendor modules
- IPA Academy modules
- IPT Tournament modules
- Pay & Play modules

## Files Modified Summary
```
xadmin/mod/debit-note/
├── x-debit-note.inc.php (getEntityDetails, addDebitNote, updateDebitNote)
├── x-debit-note-add-edit.php (form validation attributes)
└── inc/js/x-debit-note.inc.js (simplified validation)

xadmin/mod/credit-note/
├── x-credit-note.inc.php (getEntityDetails, addCreditNote, updateCreditNote)
├── x-credit-note-add-edit.php (form validation attributes)
└── inc/js/x-credit-note.inc.js (simplified validation)
```

## Git Commit Message
```
Fix: Debit/Credit Note - GSTIN Autofill + Required Field Validation - Jan 23, 2026

- Fixed GSTIN autofill by directly setting $MXRES["data"] in getEntityDetails()
- Added form framework validation (validate="required") for entityName and entityGSTIN
- Added server-side validation in add/update functions to prevent blank records
- Simplified JavaScript validation to use form framework instead of custom code
- Validation now consistent with other modules (warehouse, product, etc.)

Modules: Debit Note, Credit Note
Issue: Forms submitting without required data, GSTIN not autofilling
```
