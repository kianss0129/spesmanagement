# Quick Test Guide - Beneficiary Verification Page

## Prerequisites
- ✅ Laravel app running on `http://localhost:8000`
- ✅ Database migrations completed
- ✅ Storage link created: `php artisan storage:link`
- ✅ PESO Admin user account created

## Step 1: Create Test Data

### Option A: Using Tinker (Quick)
```bash
php artisan tinker
```

```php
// Create a test beneficiary
$user = User::create([
    'name' => 'John Student',
    'email' => 'student@test.com',
    'password' => Hash::make('password'),
]);

$user->assignRole('Beneficiary');

$school = School::firstOrCreate(['name' => 'Test High School']);

$beneficiary = Beneficiary::create([
    'user_id' => $user->id,
    'first_name' => 'John',
    'last_name' => 'Student',
    'email' => 'student@test.com',
    'phone' => '+1 555-1234',
    'school_id' => $school->id,
    'beneficiary_type' => 'student',
    'documents' => ['documents/users/1/test.pdf'],
    'onboarding_completed_at' => now(),
    'approval_status' => 'pending',
    'approved' => false,
    'status' => 'pending',
]);

exit;
```

### Option B: Manual Database Insert
```sql
-- Create user
INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at)
VALUES ('John Student', 'student@test.com', '$2y$12$...', NOW(), NOW(), NOW());

-- Create beneficiary (assuming user ID is 5)
INSERT INTO beneficiaries 
(user_id, first_name, last_name, email, phone, school_id, beneficiary_type, onboarding_completed_at, approval_status, approved, status, created_at, updated_at)
VALUES (5, 'John', 'Student', 'student@test.com', '+1 555-1234', 1, 'student', NOW(), 'pending', 0, 'pending', NOW(), NOW());

-- Assign Beneficiary role
INSERT INTO model_has_roles (role_id, model_type, model_id)
VALUES ((SELECT id FROM roles WHERE name = 'Beneficiary'), 'App\\Models\\User', 5);
```

## Step 2: Upload Test Document

Option A: Create dummy file in storage
```powershell
# Create directories
mkdir -p "C:\xampp\htdocs\SPES-SYSTEM-2\storage\app\public\documents\users\1"

# Create test file
"This is a test document" > "C:\xampp\htdocs\SPES-SYSTEM-2\storage\app\public\documents\users\1\test.pdf"
```

Option B: Upload via form (if beneficiary can access onboarding)
1. Login as beneficiary
2. Go to onboarding
3. Upload document
4. Document saved to storage with path stored in database

## Step 3: Access Verification Page

### As PESO Admin
1. Login with PESO Admin account
2. Navigate to: `http://localhost:8000/peso/beneficiaries/1/verify`
3. Should see beneficiary information and documents

### Expected Display
- ✅ Status banner: "Pending Review" (yellow)
- ✅ Name: "John Student"
- ✅ Email: "student@test.com"
- ✅ Phone: "+1 555-1234"
- ✅ School: "Test High School"
- ✅ Documents section with test.pdf
- ✅ View button for document
- ✅ Approve button (green)
- ✅ Reject form with textarea

## Step 4: Test Approve Action

1. Click "Approve Beneficiary Onboarding" button
2. Confirm in dialog
3. Page refreshes with success message
4. Status banner changes to "Approved" (green)
5. Shows approval date/time
6. Approve/Reject buttons hidden

**Verify in Database:**
```sql
SELECT approval_status, approved, approved_at FROM beneficiaries WHERE id = 1;
-- Should show: approved | 1 | 2026-02-01 10:30:00
```

## Step 5: Test Reject Action

1. Create another test beneficiary (ID 2)
2. Go to: `http://localhost:8000/peso/beneficiaries/2/verify`
3. Scroll to "Rejection Reason" textarea
4. Try to click "Reject Onboarding" without entering reason
   - Should get JavaScript alert: "Please provide a rejection reason"
5. Enter rejection reason: "Documents incomplete"
6. Click "Reject Onboarding"
7. Confirm in dialog
8. Page refreshes with success message
9. Status banner changes to "Rejected" (red)
10. Shows rejection reason and date

**Verify in Database:**
```sql
SELECT approval_status, approved, rejection_reason, rejected_at FROM beneficiaries WHERE id = 2;
-- Should show: rejected | 0 | Documents incomplete | 2026-02-01 10:35:00
```

## Step 6: Test Document Display

### File Exists
1. Create test document at: `storage/app/public/documents/users/1/report.pdf`
2. Update beneficiary documents: `['documents/users/1/test.pdf', 'documents/users/1/report.pdf']`
3. Refresh verification page
4. Should show both documents with "View / Download" buttons
5. Click View button
6. Document opens in new tab/downloads

### File Missing
1. Delete test document from storage
2. Refresh verification page
3. Document still appears in list
4. Shows "❌ File missing or deleted" warning
5. "View" button replaced with "Unavailable" (gray, disabled)

## Step 7: Test Conditional Fields

### Student Type
- ✅ Shows "School" field
- ✅ Shows school name from relationship

### OSY Type
1. Create beneficiary with `beneficiary_type = 'osy'`
2. Add `skills = 'Carpentry'`
3. Verify page shows "Skills / Training" field with "Carpentry"

### Dependent Type
1. Create beneficiary with `beneficiary_type = 'dependent'`
2. Add `parent_name = 'Jane Parent'`
3. Add `displacement_reason = 'Economic hardship'`
4. Verify both fields display

## Step 8: Test Date Formatting

Verify dates display in format: "F j, Y at h:i A"
- Example: "February 1, 2026 at 10:30 AM"

Check these dates:
- Submission Date (onboarding_completed_at)
- Approval Date (approved_at)
- Rejection Date (rejected_at)
- Document Upload Date (document['uploaded_at'])

## Troubleshooting

### Page Not Found
- Verify route added to `routes/web.php`
- Confirm beneficiary ID exists in database
- Check user has PESO Admin role

### Method Not Found
- Verify methods added to `BeneficiaryController.php`
- Confirm file saved properly
- Check for typos in method names

### Blade Template Not Found
- Verify file created at: `resources/views/beneficiaries/verify.blade.php`
- Check file path is correct
- Verify using `.blade.php` extension

### Document Shows Unavailable
- Check file exists at: `storage/app/public/documents/users/1/filename`
- Verify `storage/app/public` directory exists
- Verify `public/storage` symlink exists (should be junction on Windows)
- Verify document path in database matches file path

### Buttons Don't Work
- Check CSRF token in form: `@csrf`
- Verify POST route exists in web.php
- Check for JavaScript errors in browser console
- Verify form method is POST

### Conditional Fields Don't Show
- Check `beneficiary_type` is set in database
- Verify column names match Blade template
- Check data is being passed from controller

## Quick Verification Checklist

- [ ] Page loads without errors
- [ ] All beneficiary information displays
- [ ] Status banner shows correct status
- [ ] Documents list displays
- [ ] View buttons work for existing files
- [ ] Unavailable state shows for missing files
- [ ] Approve button updates status and dates
- [ ] Reject requires reason
- [ ] Rejection saves reason and updates status
- [ ] Conditional fields display based on type
- [ ] Dates format correctly
- [ ] CSS styling applied correctly
- [ ] Mobile responsive design works
- [ ] No console errors in browser

## Success Criteria

✅ Full verification page implemented
✅ Can approve beneficiaries
✅ Can reject beneficiaries with reason
✅ Documents display correctly
✅ File existence checked
✅ All data displays properly
✅ Responsive design works
✅ No errors in logs

---

**Ready to test?** Start with Step 1 above.
