# Document Upload & Retrieval Testing Guide

## System Status: ✅ All Checks Passed

### Verified Components:
1. ✅ `storage/app/public/` directory exists
2. ✅ `public/storage` junction/symlink created
3. ✅ File storage to public disk works
4. ✅ URL generation produces correct paths: `/storage/documents/users/{id}/filename`
5. ✅ File existence checks work
6. ✅ Relative path storage works

---

## Testing Procedure

### Test 1: Student Beneficiary Onboarding with Documents

1. **Start the application:**
   ```bash
   npm run dev  # Terminal 1: Build frontend assets
   php artisan serve  # Terminal 2: Start Laravel server (http://localhost:8000)
   ```

2. **Navigate to beneficiary registration:**
   - Go to `http://localhost:8000/register`
   - Select "Student" role
   - Complete Step 1: Basic Information (including phone field)
   - Proceed to Step 2: School Information
   - Proceed to Step 3: Document Upload

3. **Upload test documents:**
   - Create a few test documents (PDF, JPG, PNG)
   - Upload them in Step 3
   - Verify files are stored to: `storage/app/public/documents/users/{beneficiary_id}/`
   - Verify success message is shown

4. **Complete registration:**
   - Submit the form
   - Verify:
     - `beneficiaries.documents` column contains relative paths like `documents/users/17/filename.pdf`
     - `beneficiaries.phone` is saved
     - `beneficiaries.school_id` is saved

### Test 2: PESO Admin Review

1. **Login as PESO Admin:**
   - Go to `http://localhost:8000/login`
   - Use PESO admin credentials
   - Navigate to "Pending Beneficiaries" or "Applications"

2. **View beneficiary onboarding details:**
   - Click on a pending beneficiary
   - Verify displayed data:
     - ✅ Phone number appears (from Step 1)
     - ✅ School name appears (from Step 2)
     - ✅ Documents section shows all uploaded files
     - ✅ "View" button appears for each document
     - ✅ "View" button is clickable and shows file

3. **Test document viewing:**
   - Click "View" button for a document
   - Should open/download the file
   - Verify URL is `/storage/documents/users/{id}/filename.pdf`
   - Verify file content is correct

4. **Test missing document handling:**
   - Manually delete a file from `storage/app/public/documents/users/{id}/`
   - Refresh PESO review page
   - Verify:
     - ✅ "File missing or deleted" warning appears
     - ✅ "View" button is replaced with "Unavailable"
     - ✅ No error thrown in console

### Test 3: Employer Onboarding with Documents

1. **Register as employer:**
   - Go to `http://localhost:8000/register`
   - Select "Employer" role
   - Complete all steps including document upload

2. **Verify employer documents:**
   - Files should be stored to: `storage/app/public/documents/employers/{employer_id}/`
   - Database should contain relative paths like `documents/employers/5/dti.pdf`

3. **PESO Admin review of employer:**
   - Login as PESO admin
   - Navigate to employer applications
   - Verify all employer data and documents display correctly
   - Test document viewing same as beneficiary test

---

## Troubleshooting

### If documents show "Unavailable":

1. **Check file exists:**
   ```powershell
   Test-Path "C:\xampp\htdocs\SPES-SYSTEM-2\storage\app\public\documents\users\17\filename.pdf"
   ```

2. **Check database content:**
   ```sql
   SELECT id, documents FROM beneficiaries WHERE id = 17;
   ```

3. **Check storage symlink:**
   ```powershell
   Get-Item "C:\xampp\htdocs\SPES-SYSTEM-2\public\storage" -Force
   ```

### If documents show 404:

1. **Verify `public/storage` exists:**
   ```powershell
   Test-Path "C:\xampp\htdocs\SPES-SYSTEM-2\public\storage"
   ```

2. **Regenerate symlink if needed:**
   ```bash
   php artisan storage:link
   ```

3. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

---

## What Gets Tested

| Component | Expected Behavior | Status |
|-----------|------------------|--------|
| File upload to public disk | Files stored in `storage/app/public/documents/` | ✅ Verified |
| Relative path storage | Database contains `documents/users/{id}/filename.pdf` | ✅ Verified |
| URL generation | `/storage/documents/users/{id}/filename.pdf` format | ✅ Verified |
| File existence check | Shows "View" only if file exists | ✅ Verified |
| Missing file handling | Shows "Unavailable" when file deleted | ✅ Ready to test |
| Phone field persistence | Phone saved in beneficiaries.phone | ✅ Code ready |
| School linking | school_id saved in beneficiaries | ✅ Code ready |
| Document merging | New uploads don't delete old documents | ✅ Code ready |

---

## Backend Code Modifications Ready for Testing

### 1. OnboardingController::upload()
- ✅ Stores to `documents/users/{id}` for beneficiaries
- ✅ Stores to `documents/employers/{id}` for employers
- ✅ Uses public disk via Storage facade
- ✅ Merges documents (no overwrite)

### 2. PESOController::viewBeneficiaryApplications()
- ✅ Normalizes mixed document formats
- ✅ Checks file existence with Storage::disk('public')->exists()
- ✅ Generates URLs with Storage::disk('public')->url()
- ✅ Adds `exists` flag to documents

### 3. PESOController::viewEmployerApplications()
- ✅ Same normalization as beneficiary method
- ✅ Handles mixed document formats
- ✅ Existence checks and URL generation

### 4. Vue Components
- ✅ Beneficiaries/Applications.vue: Shows View button only if exists=true
- ✅ Employers/Applications.vue: Shows View button only if exists=true
- ✅ Both show "File missing or deleted" warning when file doesn't exist

---

## Quick Reference

**Key Directories:**
- Source: `storage/app/public/documents/`
- Public access: `public/storage/documents/` (via symlink)
- Web URL: `/storage/documents/{path}`

**Key Files Modified:**
- `app/Http/Controllers/Beneficiary/OnboardingController.php`
- `app/Http/Controllers/PESO/PESOController.php`
- `resources/js/Pages/PESO/Beneficiaries/Applications.vue`
- `resources/js/Pages/PESO/Employers/Applications.vue`

**Database Columns:**
- `beneficiaries.documents` - JSON array of relative paths
- `beneficiaries.phone` - Phone number from Step 1
- `beneficiaries.school_id` - Foreign key to schools
- `employers.documents` - JSON array of relative paths

---

**Ready to proceed with manual testing?** Start with Test 1 above.
