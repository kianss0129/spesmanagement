# Quick Test Guide - Document Upload Fix

**Objective:** Verify that documents upload successfully and appear in PESO verification pages.

---

## 🧪 End-to-End Test (5 minutes)

### Prerequisites
- Laravel server running: `php artisan serve` (port 8000)
- Vite dev server running: `npm run dev` (port 5175)
- Test file ready (PDF or image, <5MB)

### Test Steps

#### 1. **Open Onboarding Form** (1 min)
```
URL: http://127.0.0.1:8000/onboarding
```
- You should see Step 1: Personal Information
- Verify the page loads without errors

#### 2. **Complete Personal Info** (1 min)
- Fill in all fields:
  - Full Name
  - Email
  - Phone Number
- Click "Next" to go to Step 2

#### 3. **Complete Step 2** (1 min)
- Step 2 varies by user type:
  - **Student:** Enter school name
  - **OSY:** Enter skills
  - **Employer:** Enter company pledge
- Click "Next" to go to Step 3

#### 4. **Upload Document** (2 min)
- You are now on **Step 3: Upload Documents**
- You should see:
  - List of required documents
  - File input field
  - "Upload Document" button (disabled)

**Upload Process:**
1. Click the file input (or drag-drop)
2. Select a test file (.pdf, .jpg, .png, .doc, .docx)
   - **File must be < 5MB**
3. File name appears in the input
4. "Upload Document" button becomes **enabled**
5. Click "Upload Document"
6. See "Uploading..." while uploading
7. Should see ✓ **"Document uploaded successfully!"** message (green)
8. Document appears in **"Uploaded Documents"** list below:
   - Shows filename
   - Shows upload timestamp (e.g., "2/1/2026, 12:00:00 PM")
9. Upload more documents if desired

#### 5. **Review Documents** (1 min)
- Click "Next" to go to Step 4: Review
- You should see all your information
- **Verify documents section shows:**
  - Each document with its name
  - "No documents uploaded yet" should NOT appear

#### 6. **Submit Onboarding** (30 sec)
- Go to Step 5: Submit
- Click "Submit" button
- Wait for page to navigate
- Should redirect to `/onboarding/pending` with success message

#### 7. **Login as PESO Admin** (30 sec)
- Open new tab or logout first
- Navigate to login page
- Login with PESO Admin credentials

#### 8. **View Pending Beneficiaries** (1 min)
- After login, click "Beneficiaries" or "Pending Beneficiaries"
- Find the beneficiary you just submitted
- Should see them in the pending list with:
  - Name: ADRIAN T YALUNG (or whoever you used)
  - Status: Pending Review
  - Click "View Onboarding" button

#### 9. **Verify Documents Appear** (2 min)
- You are now viewing the beneficiary's onboarding
- Scroll to **"Submitted Documents"** section
- **Before the fix:** Shows "No documents submitted"
- **After the fix:** Shows document list with:
  - Document name (e.g., "resume.pdf")
  - Upload timestamp
  - ✓ **Blue "View" button** (if file exists)
  - No red "❌ File missing" warning

#### 10. **Download Document** (1 min)
- Click the blue "View" button next to a document
- File should:
  - Open in new tab (PDF viewer), OR
  - Download to your computer (image/doc)
- Verify file content matches original upload

---

## ✅ Success Criteria

| Item | Status |
|------|--------|
| Step 3 has file input | ✓ |
| File selection works | ✓ |
| Upload button appears | ✓ |
| Upload completes with success message | ✓ |
| Document appears in uploaded list | ✓ |
| Document shows in review step | ✓ |
| Submit works without errors | ✓ |
| PESO sees document in verification page | ✓ |
| View button is visible and blue | ✓ |
| File opens/downloads when clicked | ✓ |

---

## ❌ Troubleshooting

### Upload button doesn't appear
- **Fix:** Select a file first - button only shows when file selected

### Upload fails with error
- **Check:** File size < 5MB
- **Check:** File type is .pdf, .doc, .docx, .jpg, .jpeg, or .png
- **Check:** Laravel server running (http://127.0.0.1:8000 accessible)
- **Check:** Browser console (F12) for network errors

### Document not in uploaded list after upload
- **Check:** Scroll down - list might be below the upload button
- **Check:** Success message appeared - if not, check error message
- **Fix:** Try uploading again

### Document in list but not showing in PESO
- **Check:** Completed onboarding submit (not just review)
- **Check:** Logged in as PESO Admin (right role)
- **Check:** Viewing correct beneficiary
- **Fix:** Hard refresh PESO page (Ctrl+F5)

### View button missing in PESO
- **Fix:** File was deleted or moved after upload
- **Fix:** Check storage exists: `ls -la storage/app/public/documents/users/`
- **Fix:** Check symlink: `ls -la public/storage`

### File returns 404 when clicking View
- **Check:** Symlink exists: `php artisan storage:link`
- **Check:** File exists in storage directory
- **Fix:** Rerun: `php artisan storage:link`

---

## 📊 Database Verification

### Check if documents were saved:
```bash
mysql> SELECT id, name, documents FROM beneficiaries WHERE id = 20;
```

Should show:
```
id   name              documents
20   ADRIAN T YALUNG   [{"path":"documents/users/20/...","name":"...","uploaded_at":"..."}]
```

### Check if files exist:
```bash
dir storage\app\public\documents\users\20
```

Should show your uploaded files.

---

## 📸 Expected UI Flow

```
STEP 3: UPLOAD DOCUMENTS
├─ Required Documents (info box)
├─ File Input Section
│  ├─ Input field: [Select document...]
│  ├─ File type info: "Max 5MB. PDF, DOC, DOCX, JPG, JPEG, PNG"
│  └─ Upload button: [Upload Document] (disabled until file selected)
├─ Success Message (green, auto-dismiss)
│  └─ "✓ Document uploaded successfully!"
├─ Error Message (red, if any)
│  └─ "✗ File size exceeds 5MB limit"
└─ Uploaded Documents List (green box)
   ├─ "Uploaded Documents (2)" [count updates]
   ├─ Document Item
   │  ├─ Name: "resume.pdf"
   │  ├─ Timestamp: "2/1/2026, 12:00:00 PM"
   │  └─ Remove button
   └─ Document Item
      ├─ Name: "transcript.pdf"
      ├─ Timestamp: "2/1/2026, 12:05:00 PM"
      └─ Remove button

(User continues to Step 4 Review, Step 5 Submit)

PESO VIEW: SUBMITTED DOCUMENTS
├─ Documents section header
├─ Document Item
│  ├─ Name: "resume.pdf"
│  ├─ Timestamp: "Uploaded: 2/1/2026, 12:00:00 PM"
│  ├─ File status: (no warning)
│  └─ [View] button (blue, clickable)
└─ Document Item
   ├─ Name: "transcript.pdf"
   ├─ Timestamp: "Uploaded: 2/1/2026, 12:05:00 PM"
   ├─ File status: (no warning)
   └─ [View] button (blue, clickable)
```

---

## 🚀 What Changed

**Before Fix:**
- User filled out onboarding form
- Clicked Submit
- Form sent to server WITHOUT any uploaded documents
- PESO verification page showed "No documents submitted"

**After Fix:**
- User fills out form and reaches Step 3
- Selects file and clicks "Upload Document"
- File immediately uploaded to server via `POST /onboarding/upload`
- File stored in `storage/app/public/documents/users/{id}/`
- File metadata saved to database
- Document appears in form's document list
- User can upload more documents
- User reviews and submits form
- Submitted documents appear in PESO verification

---

## 📋 Files Modified

Only **one file** was modified:
- `resources/js/Pages/Beneficiary/Onboarding.vue`

Changes:
- Added upload state variables (selectedFile, uploading, uploadError, uploadSuccess)
- Added `uploadDocument()` async function
- Updated `handleFileUpload()` to track selected file
- Updated `submitForm()` to not send documents (already on server)
- Enhanced Step 3 template with file input, upload button, success/error messages
- Added uploaded documents list with remove button

**No changes to:**
- Backend code (already implemented)
- Database schema (already correct)
- Routes (already configured)
- PESO pages (already correct)

---

**Estimated Time:** 5-10 minutes to complete full test

**Expected Result:** Documents upload, persist, and appear in PESO verification pages ✓

