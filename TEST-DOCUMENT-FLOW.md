# Document Upload & Display Flow - Testing Guide

## System Overview

This document describes the complete document upload, storage, and display flow for the SPES System onboarding verification process.

### Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│ FRONTEND: Onboarding Form (Vue/Inertia)                            │
│ - User selects files                                                │
│ - Sends POST /onboarding/upload                                     │
│ - Receives document array: {path, name, uploaded_at}                │
│ - Displays uploaded documents in form                               │
│ - Submits form with POST /onboarding/submit                         │
└──────────────────────┬──────────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│ BACKEND: OnboardingController                                       │
│                                                                     │
│ upload() method:                                                    │
│ - Validates file (size, type)                                       │
│ - Stores to storage/app/public/documents/{users|employers}/{id}/   │
│ - Creates document object: {path, name, uploaded_at}                │
│ - Merges with existing beneficiary.documents or employer.documents  │
│ - Returns JSON response with document data                          │
│                                                                     │
│ submit() method:                                                    │
│ - Documents already saved via upload()                              │
│ - Sets approval_status = 'pending'                                  │
│ - Sets onboarding_completed_at = now()                              │
│ - Updates beneficiary or employer record                            │
└──────────────────────┬──────────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│ DATABASE: MySQL JSON Column                                         │
│ - beneficiaries.documents JSON array                                │
│ - employers.documents JSON array                                    │
│ - Format: [{path, name, uploaded_at}, ...]                          │
│ - Relative paths stored (e.g., "documents/users/123/file.pdf")     │
└──────────────────────┬──────────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│ PESO WORKFLOW                                                       │
│                                                                     │
│ 1. PESO Admin views PendingBeneficiaries / Pending Employers       │
│ 2. Clicks "View Onboarding" button                                  │
│ 3. Navigates to PESOController::viewBeneficiaryApplications()      │
│    or PESOController::viewEmployerApplications()                   │
└──────────────────────┬──────────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│ PESO CONTROLLER: Document Normalization                             │
│                                                                     │
│ Process:                                                            │
│ - Retrieves documents JSON from database                            │
│ - Normalizes mixed formats (string/array/object)                    │
│ - For each document:                                                │
│   * Extracts path, name, uploaded_at                                │
│   * Checks file existence: Storage::disk('public')->exists($path)  │
│   * Generates URL: Storage::disk('public')->url($path)              │
│   * Sets exists=true/false flag                                     │
│ - Returns normalized array to Inertia: {path, url, name, exists}   │
└──────────────────────┬──────────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│ FRONTEND: PESO Applications.vue (Beneficiary & Employer)            │
│                                                                     │
│ Display Logic:                                                      │
│ - Loops through documents array                                     │
│ - Checks: if (doc.exists && doc.url)                                │
│   * Shows "View" button (enabled, clickable)                        │
│   * Button onclick: viewDocument(doc) opens file in new tab         │
│ - If !doc.exists:                                                   │
│   * Shows red warning: "❌ File missing or deleted"                 │
│   * Button shows "Unavailable" (disabled)                           │
│ - Shows doc.name and doc.uploaded_at timestamp                      │
└──────────────────────────────────────────────────────────────────────┘
```

## File Structure

### Storage Locations

**Beneficiary Documents:**
```
storage/
└── app/
    └── public/
        └── documents/
            └── users/
                ├── 1/
                │   ├── resume.pdf
                │   ├── transcript.pdf
                │   └── diploma.jpg
                ├── 2/
                │   ├── resume.pdf
                │   └── transcript.pdf
                └── 3/
                    └── resume.pdf
```

**Employer Documents:**
```
storage/
└── app/
    └── public/
        └── documents/
            └── employers/
                ├── 1/
                │   ├── dti.pdf
                │   ├── business-permit.pdf
                │   └── mayor-permit.pdf
                ├── 2/
                │   ├── dti.pdf
                │   └── business-permit.pdf
                └── 3/
                    └── dti.pdf
```

### Public Access

Via symlink created by `php artisan storage:link`:
```
public/
└── storage/
    └── documents/
        ├── users/
        │   └── 1/
        │       ├── resume.pdf
        │       ├── transcript.pdf
        │       └── diploma.jpg
        └── employers/
            └── 1/
                ├── dti.pdf
                ├── business-permit.pdf
                └── mayor-permit.pdf
```

### Database Schema

**beneficiaries table - documents column:**
```json
[
  {
    "path": "documents/users/1/resume.pdf",
    "name": "resume.pdf",
    "uploaded_at": "2025-01-02T10:30:00Z"
  },
  {
    "path": "documents/users/1/transcript.pdf",
    "name": "transcript.pdf",
    "uploaded_at": "2025-01-02T10:35:00Z"
  }
]
```

**employers table - documents column:**
```json
[
  {
    "path": "documents/employers/1/dti.pdf",
    "name": "dti.pdf",
    "uploaded_at": "2025-01-02T11:00:00Z"
  },
  {
    "path": "documents/employers/1/business-permit.pdf",
    "name": "business-permit.pdf",
    "uploaded_at": "2025-01-02T11:05:00Z"
  }
]
```

## Code Implementation Details

### 1. OnboardingController::upload() - File Storage & Metadata

**Location:** `app/Http/Controllers/Beneficiary/OnboardingController.php` (Lines 111-155)

**Flow:**
```php
// Validate uploaded file
$validated = $request->validate([
    'file' => 'required|file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
]);

// Get authenticated user
$user = Auth::user();
$file = $validated['file'];

// Determine target folder
// Beneficiaries: documents/users/{id}/
// Employers: documents/employers/{id}/
$folder = $user->hasRole('Employer') 
    ? 'documents/employers/' . $user->id 
    : 'documents/users/' . $user->id;

// Store file to public disk
$storedPath = $file->store($folder, 'public');

// Get existing documents or empty array
$beneficiary = $user->beneficiary ?? $user->employer;
$existingDocs = $beneficiary->documents ?? [];

// Ensure it's an array
if (!is_array($existingDocs)) {
    $existingDocs = [$existingDocs];
}

// Create document object with metadata
$uploadedDocs[] = [
    'path' => $storedPath,
    'name' => $file->getClientOriginalName(),
    'uploaded_at' => now()->toIso8601String(),
];

// Merge new uploads with existing documents
$beneficiary->documents = array_merge($existingDocs, $uploadedDocs);
$beneficiary->save();

// Return success response
return response()->json([
    'success' => true,
    'documents' => $beneficiary->documents,
    'message' => 'Document uploaded successfully',
]);
```

### 2. OnboardingController::submit() - Finalize Onboarding

**Location:** `app/Http/Controllers/Beneficiary/OnboardingController.php` (Lines 162-244)

**Key Points:**
- Documents are already saved via upload() method
- submit() only sets status fields and relationships
- No additional document processing needed

```php
// Update beneficiary record
$beneficiary->update([
    'status' => 'pending',
    'onboarding_completed_at' => now(),
    'approved' => false,
    'approval_status' => 'pending',
    'phone' => $beneficiary->phone ?? $validated['phone'] ?? null,
    'school_id' => $beneficiary->school_id,
]);

// Documents already in $beneficiary->documents from upload()
// No need to handle them again
```

### 3. PESOController::viewBeneficiaryApplications() - Display with Normalization

**Location:** `app/Http/Controllers/PESO/PESOController.php` (Lines 160-235)

**Normalization Process:**
```php
// Load beneficiary with relationships
$beneficiary->load('user', 'school');

// Get documents from database (may be mixed format)
$documents = [];
if ($beneficiary->documents) {
    $raw = is_array($beneficiary->documents) 
        ? $beneficiary->documents 
        : [$beneficiary->documents];
    
    foreach ($raw as $entry) {
        // Extract path from various formats
        if (is_string($entry)) {
            $entryPath = $entry;
        } elseif (is_array($entry)) {
            $entryPath = $entry['path'] ?? $entry['file'] ?? null;
            $entryName = $entry['name'] ?? $entry['filename'] ?? null;
            $entryUploadedAt = $entry['uploaded_at'] ?? null;
        } elseif (is_object($entry)) {
            $entryPath = $entry->path ?? $entry->file ?? null;
            // ... extract other fields
        }
        
        if (!$entryPath) continue; // Skip malformed
        
        // CHECK FILE EXISTS
        $exists = Storage::disk('public')->exists((string) $entryPath);
        
        // GENERATE URL ONLY IF EXISTS
        $url = null;
        if ($exists) {
            try {
                $url = Storage::disk('public')->url((string) $entryPath);
            } catch (\Throwable $e) {
                $url = null;
            }
        }
        
        // Build normalized document object
        $documents[] = [
            'path' => $entryPath,
            'url' => $url,
            'name' => $entryName ?? basename((string) $entryPath),
            'uploaded_at' => $entryUploadedAt,
            'exists' => $exists,  // CRITICAL FLAG
        ];
    }
}

// Return to Inertia
return Inertia::render('PESO/Beneficiaries/Applications', [
    'documents' => $documents,  // Normalized array with exists flag
    // ... other data
]);
```

### 4. Vue Component - Conditional Display

**Location:** `resources/js/Pages/PESO/Beneficiaries/Applications.vue` (Lines 40-70)

**Template:**
```vue
<div v-else class="space-y-3">
  <div v-for="(doc, index) in documents" :key="index" class="border rounded-lg p-4 flex items-center justify-between">
    <div class="flex-1">
      <p class="font-semibold">{{ doc.name || 'Document' }}</p>
      <p class="text-xs text-gray-500 mt-1">{{ doc.uploaded_at ? 'Uploaded: ' + formatDate(doc.uploaded_at) : '' }}</p>
      <p v-if="!doc.exists" class="text-xs text-red-600 mt-1">❌ File missing or deleted</p>
    </div>
    
    <!-- CRITICAL: Only show View button if file exists AND url is available -->
    <button 
      v-if="doc.exists && doc.url"
      class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm"
      @click="viewDocument(doc)"
    >
      View
    </button>
    <span v-else class="text-gray-400 text-sm px-3 py-1">Unavailable</span>
  </div>
</div>
```

## Testing Checklist

### Setup

- [ ] Verify `storage/app/public/` directory exists
- [ ] Verify `public/storage` symlink exists (created by `php artisan storage:link`)
- [ ] Verify `beneficiaries.documents` column is JSON type
- [ ] Verify `employers.documents` column is JSON type

### Beneficiary Onboarding Flow

#### Step 1: Upload Documents
```bash
# Test uploading a document
POST /onboarding/upload
Content-Type: multipart/form-data
{
  "file": <binary PDF or image>
}

# Expected Response:
{
  "success": true,
  "documents": [
    {
      "path": "documents/users/1/filename.pdf",
      "name": "filename.pdf",
      "uploaded_at": "2025-01-02T10:30:00Z"
    }
  ]
}

# Verify:
- File exists at storage/app/public/documents/users/1/filename.pdf
- Database beneficiaries.documents contains the document object
```

#### Step 2: Submit Onboarding
```bash
POST /onboarding/submit
{
  "phone": "09991234567",
  "school": "University of the Philippines"
}

# Expected Response:
- Redirect to /onboarding/pending
- beneficiaries.approval_status = 'pending'
- beneficiaries.onboarding_completed_at = current timestamp
- Documents still in beneficiaries.documents JSON column
```

#### Step 3: PESO Verification
```bash
# View pending beneficiaries
GET /peso/beneficiaries/pending

# Click "View Onboarding" on a beneficiary
# Navigates to:
GET /peso/beneficiaries/{beneficiary_id}/applications

# Expected in Response:
{
  "documents": [
    {
      "path": "documents/users/1/filename.pdf",
      "name": "filename.pdf",
      "uploaded_at": "2025-01-02T10:30:00Z",
      "url": "/storage/documents/users/1/filename.pdf",
      "exists": true
    }
  ]
}

# Verify in Vue:
- Document displays in list
- "View" button is visible and clickable
- No "File missing" warning
```

#### Step 4: Download Document
```bash
# Click "View" button
# Browser makes request:
GET /storage/documents/users/1/filename.pdf

# Expected:
- File downloads or opens in new tab
- Correct file content
- File size matches original upload
```

### Employer Onboarding Flow

Repeat all steps above but:
- Use employer onboarding form
- Files stored in `documents/employers/{id}/`
- Route: GET /peso/employers/{employer_id}/applications
- Component: `Employers/Applications.vue`

### Edge Cases

#### Test: File Deleted After Upload
```bash
# 1. Complete beneficiary onboarding with documents
# 2. Manually delete file from storage/app/public/documents/users/X/
# 3. View in PESO verification page
# Expected:
# - Document still appears in list
# - "View" button shows "Unavailable"
# - Red warning: "❌ File missing or deleted"
# - doc.exists = false
```

#### Test: Multiple Uploads
```bash
# 1. Upload first document (resume.pdf)
# 2. Upload second document (transcript.pdf)
# 3. Upload third document (diploma.jpg)
# 4. Submit onboarding
# 5. View in PESO
# Expected:
# - All three documents display
# - Each with correct name and upload timestamp
# - All have View buttons (assuming files exist)
```

#### Test: Re-submission
```bash
# 1. Complete beneficiary onboarding with 2 documents
# 2. PESO rejects with reason
# 3. Beneficiary submits again with 1 additional document (3 total)
# 4. PESO views application
# Expected:
# - All 3 documents display (not duplicated)
# - Upload timestamps preserved
# - New upload has latest timestamp
```

### Debugging Commands

```bash
# Check if storage symlink exists
ls -la public/ | grep storage

# Check document files exist
ls -la storage/app/public/documents/users/
ls -la storage/app/public/documents/employers/

# Check database documents column
mysql> SELECT id, JSON_EXTRACT(documents, '$[*].name') FROM beneficiaries WHERE documents IS NOT NULL;
mysql> SELECT id, JSON_EXTRACT(documents, '$[*].path') FROM employers WHERE documents IS NOT NULL;

# Manually test Storage facade
php artisan tinker
>>> Storage::disk('public')->exists('documents/users/1/resume.pdf')
>>> Storage::disk('public')->url('documents/users/1/resume.pdf')
>>> Storage::disk('public')->get('documents/users/1/resume.pdf')
```

## Troubleshooting

### Problem: Documents not appearing in PESO view
**Solution:**
1. Verify database: `SELECT documents FROM beneficiaries WHERE id = X;`
2. Check files exist: `ls -la storage/app/public/documents/users/X/`
3. Check symlink: `ls -la public/storage/`
4. Verify Storage::disk('public')->exists() returns true in controller

### Problem: "File missing or deleted" warning
**Solution:**
1. File was deleted after upload - restore from backup or re-upload
2. Path is incorrect in database - check JSON format
3. Storage symlink broken - recreate with `php artisan storage:link`

### Problem: View button not appearing
**Solution:**
1. Check `doc.exists` flag in response - should be `true`
2. Check `doc.url` is not null - should be `/storage/documents/...`
3. Check Vue component condition: `v-if="doc.exists && doc.url"`

### Problem: File downloads but is corrupted
**Solution:**
1. Check file size matches original: `du -h storage/app/public/documents/users/X/`
2. Verify file type: `file storage/app/public/documents/users/X/filename.pdf`
3. Check upload didn't get truncated - compare hash: `md5sum original.pdf && md5sum storage/app/public/documents/users/X/`

## Performance Considerations

- **Document arrays:** JSON columns can store up to 4GB per cell
- **File storage:** Monitor `storage/app/public/documents/` disk space
- **URL generation:** O(n) for each document in PESOController normalization
- **File existence checks:** O(1) for symlinked files in public disk

## Security Notes

- Files stored in `storage/app/public/` are web-accessible
- Use Laravel's authorization (Middleware) to protect beneficiary/employer data
- Consider adding scan for malicious files: use antivirus scanning library
- Set up proper file permissions: `chmod 644` for files, `chmod 755` for directories
- Implement rate limiting on upload endpoint to prevent abuse

## Related Files

- **Upload Controller:** [OnboardingController.php](app/Http/Controllers/Beneficiary/OnboardingController.php#L111-L155)
- **Submit Controller:** [OnboardingController.php](app/Http/Controllers/Beneficiary/OnboardingController.php#L162-L244)
- **PESO Display:** [PESOController.php](app/Http/Controllers/PESO/PESOController.php#L160-L335)
- **Beneficiary Vue:** [Applications.vue](resources/js/Pages/PESO/Beneficiaries/Applications.vue)
- **Employer Vue:** [Applications.vue](resources/js/Pages/PESO/Employers/Applications.vue)
- **Routes:** [web.php](routes/web.php)
- **Models:** [Beneficiary.php](app/Models/Beneficiary.php), [Employer.php](app/Models/Employer.php)
