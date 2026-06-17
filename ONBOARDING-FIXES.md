# SPES Onboarding Form Fixes

## Issue
Beneficiaries registering as OSY (Out-of-School Youth) or Dependent of Displaced Worker were seeing student form fields instead of their category-specific fields.

## Root Causes
1. **Missing Dependent Fields**: The form was missing "Former Employer" and "Date of Job Displacement" fields for dependent applicants
2. **Category Override in Draft Loading**: When loading saved drafts, the category was being overridden with the preferred category, losing the user's registered category
3. **Category Priority**: The category initialization wasn't properly prioritizing the user's registered category from the beneficiary record

## Changes Made

### 1. Added Missing Dependent Fields (Step 3)
**File**: `resources/js/Pages/Beneficiary/SpesOnboarding.vue`

Added two new fields to the dependent section:
- **Former Employer / Company** (optional, date field)
- **Date of Job Displacement** (optional, text field)

These fields now display when the applicant selects "Dependent of Displaced Worker" category.

### 2. Fixed Category Preservation in Draft Loading
**Function**: `loadDraft()`

Changed from:
```javascript
category: preferredCategory,
```

To:
```javascript
// Preserve the draft's category if it exists, otherwise use preferred category
category: parsed.category || preferredCategory,
```

This ensures that if a user has saved a draft as OSY, their category choice is preserved when they reload the form.

### 3. Improved Category Initialization from Beneficiary Data
**Function**: `hydrateForm()`

Changed from:
```javascript
category: getPreferredCategory(),
```

To:
```javascript
// Use beneficiary's category if available, otherwise use preferred category
category: beneficiary.category || beneficiary.beneficiary_type || getPreferredCategory(),
```

This ensures the user's registered category is loaded from the beneficiary record when the form first loads.

### 4. Updated Field Reset Logic
**Function**: `resetCategorySpecificFields()`

Added the new dependent fields to the reset list:
```javascript
form.value.former_employer = '';
form.value.displacement_date = '';
```

This ensures proper cleanup when users switch between categories.

## How It Works

### Form Initialization Flow
1. **Backend** passes the registered category via `page.props.category` (set from `$user->beneficiary_type` or `$beneficiary->category`)
2. **hydrateForm()** loads the beneficiary data and sets the category from the registered type
3. **loadDraft()** loads any saved progress but preserves the category
4. **Form displays** the correct category-specific fields based on the selected category

### Category-Specific Fields

**Student Category (Step 3)**
- School Name
- School Address
- Education Level
- School Year
- Year Level
- Course / Strand

**OSY Category (Step 3)**
- Last School Attended
- Highest Educational Attainment
- Year Last Attended School

**Dependent of Displaced Worker Category (Step 3)**
- Parent / Guardian Name
- Relationship to Applicant
- Reason for Displacement
- Former Employer / Company (NEW)
- Date of Job Displacement (NEW)

## Validation
The validation logic in `validateStep()` now properly requires:
- **Student**: school_name, school_address, education_level, school_year
- **OSY**: last_school_attended, highest_attainment, year_last_attended
- **Dependent**: parent_guardian_name, relationship, displacement_reason

Optional fields (former_employer, displacement_date) are not required but will be saved if provided.

## Testing Recommendations
1. Register as OSY → Verify only OSY fields show in Step 3
2. Register as Dependent → Verify all dependent fields including new ones show
3. Switch between categories → Verify appropriate fields show
4. Save and reload → Verify category is preserved
5. Submit form → Verify all dependent data is saved correctly
