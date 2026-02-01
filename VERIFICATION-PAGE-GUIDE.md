# Beneficiary Onboarding Verification Page - Implementation Guide

## Overview
A comprehensive Laravel Blade template that PESO admins use to review and verify beneficiary onboarding submissions. The page displays all beneficiary information, submitted documents, and provides approve/reject actions with optional rejection reasons.

## Files Created/Modified

### 1. Blade Template
**File:** `resources/views/beneficiaries/verify.blade.php`

**Features:**
- Status banner showing current approval status (Pending Review, Approved, Rejected)
- Beneficiary information section with:
  - Always displayed: Name, Email, Phone, Submission Date
  - Conditional fields based on beneficiary type:
    - **Student:** School name
    - **OSY (Out-of-School Youth):** Skills/Training info
    - **Dependent/Displaced Worker:** Parent/Guardian name, Displacement reason
- Documents section showing uploaded files with:
  - File name and upload date
  - View/Download button (only if file exists)
  - File missing/deleted warning
  - Unavailable state for missing files
- Verification actions:
  - Approve button (green) with confirmation
  - Reject form with required rejection reason
  - Client-side validation for rejection reason

**Styling:**
- TailwindCSS with responsive grid layout
- Card-based design with shadows and rounded corners
- Status-specific color schemes (green for approved, red for rejected, yellow for pending)
- Hover effects on buttons and document links

### 2. Controller Methods
**File:** `app/Http/Controllers/Beneficiary/BeneficiaryController.php`

**Added Methods:**

#### `showVerification(Beneficiary $beneficiary)`
- Load beneficiary with related data (user, school)
- Normalize documents array from multiple formats (strings, arrays, objects)
- Check file existence using Storage facade
- Generate proper storage URLs
- Pass data to Blade template

**Returns:**
```php
[
    'beneficiary' => $beneficiary,
    'documents' => [
        [
            'path' => 'documents/users/17/filename.pdf',
            'url' => 'http://localhost:8000/storage/documents/users/17/filename.pdf',
            'name' => 'Final Report',
            'uploaded_at' => '2026-02-01T10:30:00Z',
            'exists' => true,
        ],
        // ... more documents
    ],
]
```

#### `verify(Request $request, Beneficiary $beneficiary)`
- Validates action (approve/reject)
- Validates rejection reason if rejecting (required)
- Updates beneficiary approval_status and timestamps
- Logs verification action
- Redirects back to verification page with success message

**Request Validation:**
```php
[
    'action' => 'required|in:approve,reject',
    'rejection_reason' => 'required_if:action,reject|nullable|string|max:1000',
]
```

### 3. Routes
**File:** `routes/web.php`

**Added Routes (in PESO Admin middleware group):**
```php
// Verification page - display
Route::get('beneficiaries/{beneficiary}/verify', [BeneficiaryController::class, 'showVerification'])
    ->name('beneficiaries.verify.show');

// Verification action - approve/reject
Route::post('beneficiaries/{beneficiary}/verify', [BeneficiaryController::class, 'verify'])
    ->name('beneficiaries.verify');
```

**Access Control:**
- Only PESO Admin role can access
- Requires authentication
- Model binding for automatic beneficiary lookup

## Database Fields Required

The `beneficiaries` table must have these columns:

```sql
- id (PK)
- user_id (FK)
- first_name (string)
- last_name (string)
- email (string)
- phone (nullable string)
- school_id (nullable FK)
- beneficiary_type (nullable string) - 'student', 'osy', 'dependent', 'displaced_worker'
- skills (nullable text) - for OSY beneficiaries
- parent_name (nullable string) - for dependent/displaced worker
- displacement_reason (nullable text) - for displaced workers
- documents (nullable JSON) - array of document paths
- approval_status (nullable string) - 'pending', 'approved', 'rejected'
- approved (boolean)
- approved_at (nullable timestamp)
- rejected_at (nullable timestamp)
- rejection_reason (nullable text)
- onboarding_completed_at (nullable timestamp)
- status (string) - 'draft', 'pending', etc.
```

## Usage

### Accessing the Verification Page

**URL:** `GET /peso/beneficiaries/{beneficiary}/verify`

**Example:**
```
http://localhost:8000/peso/beneficiaries/17/verify
```

### Approving a Beneficiary

**Form Submission:**
```html
<form action="{{ route('beneficiaries.verify', $beneficiary->id) }}" method="POST">
    @csrf
    <input type="hidden" name="action" value="approve">
    <button type="submit">Approve Beneficiary</button>
</form>
```

**Result:**
- Sets `approval_status` = 'approved'
- Sets `approved` = true
- Sets `approved_at` = current timestamp
- Clears rejection fields

### Rejecting a Beneficiary

**Form Submission:**
```html
<form action="{{ route('beneficiaries.verify', $beneficiary->id) }}" method="POST">
    @csrf
    <input type="hidden" name="action" value="reject">
    <textarea name="rejection_reason" required>
        Reason for rejection...
    </textarea>
    <button type="submit">Reject</button>
</form>
```

**Result:**
- Sets `approval_status` = 'rejected'
- Sets `approved` = false
- Sets `rejected_at` = current timestamp
- Stores rejection reason
- Clears approved_at

## Features in Detail

### 1. Status Banner
- **Pending:** Yellow background with "Pending Review" label
- **Approved:** Green background with "Approved" label and approval date
- **Rejected:** Red background with "Rejected" label, rejection reason, and date

### 2. Conditional Beneficiary Information
The page automatically displays different information based on `beneficiary_type`:

**Student**
```blade
School: {{ $beneficiary->school?->name }}
```

**OSY (Out-of-School Youth)**
```blade
Skills / Training: {{ $beneficiary->skills }}
```

**Dependent / Displaced Worker**
```blade
Parent / Guardian Name: {{ $beneficiary->parent_name }}
Displacement Reason: {{ $beneficiary->displacement_reason }}
```

### 3. Document Display
- Iterates through normalized documents array
- Shows file icon, name, and upload date
- Displays file existence status
- View/Download link only if file exists
- Shows "Unavailable" state for missing files

### 4. Verification Actions
- **Approve Button:** Submits hidden form with action=approve
- **Reject Form:** Textarea for rejection reason with client-side validation
- Both forms include CSRF protection
- JavaScript confirmation before submission

## Document Storage Integration

The page works with the document storage system:

1. **Document Upload**
   - Files uploaded via OnboardingController::upload()
   - Stored to `storage/app/public/documents/{users|employers}/{id}/`
   - Relative paths saved to database JSON column

2. **Document Retrieval**
   - showVerification() normalizes mixed formats
   - Checks file existence with Storage::disk('public')->exists()
   - Generates URLs with Storage::disk('public')->url()
   - Adds exists flag to each document

3. **Document Display**
   - Vue components check exists flag
   - Shows View button only if file exists
   - Displays warning if file missing or deleted

## Security Considerations

1. **Authentication:** Requires 'auth' middleware
2. **Authorization:** Requires 'PESO Admin' role
3. **CSRF Protection:** All forms include @csrf token
4. **Validation:** Server-side validation of action and rejection_reason
5. **Mass Assignment:** Only specific fields updated via Eloquent

## Customization

### Change Rejection Reason Label
Edit line in `verify.blade.php`:
```blade
<label class="block text-sm font-semibold text-gray-700 mb-3">
    Rejection Reason (Required if rejecting)
</label>
```

### Add Additional Conditional Fields
Add new elseif block:
```blade
@elseif($beneficiary->beneficiary_type === 'your_type')
    <div>
        <label>Your Field:</label>
        <p>{{ $beneficiary->your_field }}</p>
    </div>
@endif
```

### Modify Button Colors
Edit TailwindCSS classes:
- Approve: `bg-green-600 hover:bg-green-700`
- Reject: `bg-red-600 hover:bg-red-700`

## Testing Checklist

- [ ] Access verification page as PESO Admin
- [ ] View pending beneficiary with all fields
- [ ] View approved beneficiary shows status
- [ ] View rejected beneficiary shows rejection reason
- [ ] Documents display with correct links
- [ ] View button works for existing files
- [ ] "File missing" warning shows for deleted files
- [ ] Approve button updates status to approved
- [ ] Reject button requires rejection reason
- [ ] Rejection saves reason and status
- [ ] Student conditionally shows school
- [ ] OSY conditionally shows skills
- [ ] Dependent/DW shows parent name and reason
- [ ] Phone field shows "Not provided" if empty
- [ ] All dates format correctly (F j, Y at h:i A)

## Error Handling

The page gracefully handles:
- Missing documents: Shows "No documents submitted"
- Deleted files: Shows "File missing or deleted" warning
- Empty conditional fields: Shows "Not provided" in italics
- Missing phone: Shows "Not provided" placeholder
- Missing submission date: Shows "Not yet submitted"

## Related Files

- `OnboardingController.php` - Handles document uploads
- `PESOController.php` - Lists pending beneficiaries
- `verify.blade.php` - This verification template
- `BeneficiaryController.php` - Verification logic
- `web.php` - Route definitions

## Notes

- Documents are stored with relative paths in JSON format
- Storage facade used for all file operations
- Logging included for audit trail
- Timestamps use Carbon date formatting
- Responsive design works on mobile, tablet, desktop
