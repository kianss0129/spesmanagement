# SPES SYSTEM — FINAL DEFENSE DEMO SCRIPT

## 📋 PRE-DEMO SETUP

### Start Services (30 minutes before defense)
```bash
# Terminal 1: Start Laravel
cd c:\xampp\htdocs\SPES-SYSTEM-1
php artisan serve

# Terminal 2: Start Vite (frontend)
cd c:\xampp\htdocs\SPES-SYSTEM-1
npm run dev

# Verify: Open http://127.0.0.1:8000 → Welcome page loads
```

### Test Accounts
| Role | Email | Password | Notes |
|------|-------|----------|-------|
| Admin | admin@spes.com | (your password) | Full access |
| PESO Admin | peso1@spes.com | (your password) | Can approve/schedule |
| PESO User | peso2@spes.com | (your password) | Interview evaluation only |
| Employer | employer1@spes.com | (your password) | Job posting + review |
| Beneficiary | adrianyalung76@gmail.com | (your password) | Full beneficiary flow |

> ⚠️ If you forgot passwords, reset via: `php artisan tinker` → `User::find(1)->update(['password' => bcrypt('password123')])`

### Browser Setup
- Use **Chrome** (Incognito for multi-role testing)
- Open 2-3 browser windows for different roles
- Clear cache before demo: `Ctrl+Shift+Delete`

---

## 🎬 DEMO FLOW (15-20 minutes)

---

### SCENE 1: SYSTEM OVERVIEW (1 minute)
**Show**: Welcome page at `http://127.0.0.1:8000`

**Say**: 
> "This is the SPES Management System — a web-based platform for the Special Program for Employment of Students. It digitizes the entire SPES workflow from beneficiary application to completion, connecting PESO offices, beneficiaries, and employers."

**Click**: Show the About page briefly if needed.

---

### SCENE 2: BENEFICIARY REGISTRATION (2 minutes)
**URL**: `http://127.0.0.1:8000/register/beneficiary`

**Say**: 
> "Beneficiaries can register in 3 categories: Student, Out-of-School Youth, or Displaced/Dependent Worker. Each category has specific requirements."

**Action**: 
1. Show the registration form (don't submit — use existing account)
2. Point out: email field, category selection, password strength meter, reCAPTCHA, Terms & Conditions

**Expected**: Form renders with all fields, no errors.

---

### SCENE 3: BENEFICIARY ONBOARDING (2 minutes)
**Login as**: Beneficiary (adrianyalung76@gmail.com)
**URL**: `http://127.0.0.1:8000/onboarding`

**Say**:
> "After email verification, beneficiaries complete a comprehensive SPES onboarding form — personal information, family background, education details, skills selection, and document upload. The system supports draft saving so applicants can return later."

**Action**:
1. Show the SpesOnboarding page (multi-step wizard)
2. Point out: step indicator, form fields per category, document upload section
3. Show the beneficiary dashboard showing "Pending Approval" status

**Expected**: Dashboard loads with pending status banner.

---

### SCENE 4: ADMIN/CPESO DASHBOARD (2 minutes)
**Login as**: Admin (admin@spes.com)
**URL**: `http://127.0.0.1:8000/dashboard`

**Say**:
> "The CPESO Admin dashboard provides a real-time overview — pending applications, approved beneficiaries, job listings, interview schedules, and analytics charts."

**Action**:
1. Show dashboard stats cards (users, beneficiaries, employers, pending)
2. Show the sidebar navigation: Applications, Records, SPES Operations, Reports, Settings
3. Click "Beneficiary Applications" → show pending list

**Expected**: Dashboard loads with stats, charts, and beneficiary list.

---

### SCENE 5: CPESO REVIEWS & APPROVES BENEFICIARY (2 minutes)
**URL**: Click a beneficiary from pending list

**Say**:
> "CPESO staff reviews submitted documents, personal information, and can approve, reject, or request corrections. On approval, the system generates a temporary password and sends an email notification."

**Action**:
1. Click on a beneficiary → show their application/documents page
2. Point out: document viewer, approval buttons, correction request option
3. If demo needs it: click Approve (or show the approve button and explain)

**Expected**: Beneficiary detail page loads with documents and action buttons.

---

### SCENE 6: EXAM SCHEDULING (1 minute)
**Say**:
> "After approval, CPESO can schedule examinations. The system supports batch scheduling — multiple beneficiaries in one exam slot — with automatic email notifications."

**Action**:
1. Navigate to SPES Operations → Schedule tab
2. Show the exam scheduling interface
3. Point out: batch selection, date/time picker, location, instructions field

**Expected**: Schedule section loads with exam form and upcoming exams list.

---

### SCENE 7: INTERVIEW SCHEDULING (2 minutes)
**Say**:
> "For interviews, CPESO assigns a PESO staff member as interviewer and provides a Google Meet link. The system validates that only PESO-role users can be assigned."

**Action**:
1. Show the interview scheduling form
2. Point out: interviewer dropdown (PESO users only), Google Meet link field, batch support
3. Show the upcoming interviews list

**Expected**: Interview form loads, interviewer dropdown shows PESO users.

---

### SCENE 8: PESO USER EVALUATES INTERVIEW (2 minutes)
**Login as**: PESO User (peso2@spes.com) — use incognito window
**URL**: `http://127.0.0.1:8000/peso-user/dashboard`

**Say**:
> "The assigned PESO interviewer sees only their assigned interviews. They can view the beneficiary's profile, join the Google Meet link, and submit their evaluation — passed, failed, or needs review — with detailed remarks."

**Action**:
1. Show the PESO User dashboard (limited view)
2. Show assigned interviews with beneficiary profiles
3. Point out: evaluation form (result dropdown + remarks), Meet link button
4. Show the strict access control: no scheduling, no approvals, read-only records

**Expected**: Dashboard shows assigned interviews only. No admin functions visible.

---

### SCENE 9: EMPLOYER DASHBOARD & JOB POSTING (2 minutes)
**Login as**: Employer (employer1@spes.com) — another window
**URL**: `http://127.0.0.1:8000/employer`

**Say**:
> "Approved employers can post job opportunities with required skills. The system uses skills-based matching to suggest suitable beneficiaries."

**Action**:
1. Show employer dashboard with onboarding progress
2. Navigate to Jobs → show posted jobs list
3. Click "Post Job" → show the form with skills selection
4. Point out: title, description, slots, closing date, required skills (with categories)

**Expected**: Employer dashboard loads, job list shows, post job form works.

---

### SCENE 10: JOB ASSIGNMENT (1 minute)
**Back to Admin window**

**Say**:
> "CPESO uses skills-based matching to assign qualified beneficiaries to appropriate jobs. The system calculates match scores based on overlapping skills."

**Action**:
1. Navigate to SPES Operations → Placement
2. Show the BeneficiaryAssignment page
3. Point out: beneficiary list, job list, matching suggestions with scores

**Expected**: Assignment page loads with beneficiaries and jobs.

---

### SCENE 11: EMPLOYER ACKNOWLEDGES ASSIGNMENT (1 minute)
**Employer window**

**Say**:
> "When a beneficiary is assigned, the employer must acknowledge the assignment before work supervision begins. This triggers the deployment status."

**Action**:
1. Show the Applicants page → assigned beneficiaries
2. Point out the "Acknowledge" button and status indicators

**Expected**: Assigned beneficiaries visible with acknowledge option.

---

### SCENE 12: BENEFICIARY SUBMITS DTR (1 minute)
**Beneficiary window**
**URL**: `http://127.0.0.1:8000/beneficiary/attendance`

**Say**:
> "Deployed beneficiaries submit their Daily Time Record with proof photos. The system tracks time in/out and calculates hours worked."

**Action**:
1. Show the Attendance page
2. Point out: date selection, time in/out fields, proof upload
3. Show existing attendance records

**Expected**: Attendance page loads with submission form and history.

---

### SCENE 13: DAILY ACCOMPLISHMENT REPORT (1 minute)
**URL**: `http://127.0.0.1:8000/beneficiary/work-outputs`

**Say**:
> "Beneficiaries also submit Daily Accomplishment Reports describing their work activities, hours, and can attach supporting files."

**Action**:
1. Show Work Outputs page
2. Point out: work date, accomplishments text area, hours worked, file attachment

**Expected**: Work outputs page loads with form and history.

---

### SCENE 14: EMPLOYER REVIEWS SUBMISSIONS (1 minute)
**Employer window**

**Say**:
> "Employers review DTR records and daily reports, with options to approve, request corrections, or reject with remarks."

**Action**:
1. Navigate to Attendance → show DTR review interface
2. Navigate to Work Output → show report review with approve/reject buttons
3. Point out: review status, remarks field

**Expected**: Review pages load with action buttons.

---

### SCENE 15: COMPLETION & CERTIFICATE (1 minute)
**Say**:
> "When the employment period ends, the employer submits completion for CPESO review. CPESO verifies that DTR, daily reports, and ratings are in place before approving. The beneficiary then receives their completion certificate."

**Action**:
1. Admin window: show the monitoring section with completion readiness indicators
2. Point out: has_dtr, has_approved_daily_reports, has_employer_rating, has_certificate checkmarks
3. Beneficiary window: show Certificate page

**Expected**: Monitoring shows readiness status, certificate page accessible.

---

### SCENE 16: ANALYTICS & REPORTS (1 minute)
**Admin window**

**Say**:
> "The system provides comprehensive analytics — applicants by school, top hiring employers, performance trends, completion rates, and attendance compliance. Reports can be exported for DOLE submission."

**Action**:
1. Show the dashboard charts
2. Navigate to Reports section
3. Show DOLE export option

**Expected**: Charts render, reports page loads.

---

## ✅ EXPECTED RESULTS PER STEP

| Step | Expected Result |
|------|----------------|
| Welcome page | Loads in <2 seconds, no console errors |
| Registration | Form renders all fields, validations fire on submit |
| Onboarding | Multi-step wizard navigates, draft saves work |
| Admin Dashboard | Stats load, charts render, no 500 errors |
| Beneficiary Review | Documents display, action buttons functional |
| Exam Scheduling | Form submits, creates exam record, notification sent |
| Interview Scheduling | Interviewer dropdown populates, schedule saves |
| PESO Evaluation | Evaluation form submits, status updates |
| Job Posting | Skills load, job creates successfully |
| Assignment | Match scores calculate, assignment saves |
| DTR Submission | Attendance record created with proof |
| Report Submission | Work output saved with file attachment |
| Completion | Status transitions correctly to completed |

---

## ⚠️ COMMON ERRORS TO WATCH FOR

| Error | Cause | Quick Fix |
|-------|-------|-----------|
| Blank page | Vite not running | Run `npm run dev` in separate terminal |
| 419 Session Expired | CSRF token stale | Refresh the page (F5) |
| 500 Internal Server Error | Check `storage/logs/laravel.log` | Restart `php artisan serve` |
| Images not loading | Storage not linked | Run `php artisan storage:link` |
| Login redirect loop | Session corrupted | Clear browser cookies for localhost |
| Charts not rendering | Empty database | Ensure seed data exists |
| Email not sending | SMTP not configured | This is OK for demo — just explain it's configured for production |

---

## 🚨 EMERGENCY FALLBACK PLAN

### If the server crashes:
```bash
# Kill existing processes
Ctrl+C in both terminals

# Restart
php artisan serve
npm run dev
```

### If database is corrupted:
```bash
# Restore from backup
mysql -u root spes_db < backup/spes_backup.sql
```

### If a specific page 500s:
1. Check `storage/logs/laravel.log` (last 20 lines)
2. Explain: "This specific feature has a data dependency that we can demonstrate in the other flow"
3. Continue with next scene

### If login doesn't work:
```bash
php artisan tinker
# Reset admin password:
# App\Models\User::find(1)->update(['password' => bcrypt('password123')]);
```

### If Vite/frontend breaks:
```bash
# Kill Vite, rebuild
npm run build
# Now serve without Vite (uses compiled assets)
php artisan serve
```

### Panel questions fallback:
- "How does security work?" → Explain Spatie roles, middleware, route protection
- "Can you show the database?" → Open phpMyAdmin at localhost/phpmyadmin
- "What if beneficiary is rejected?" → Show the needs_correction flow, resubmission
- "How does matching work?" → Explain skills-based matching algorithm in AdminAssignmentController

---

## 💾 FINAL BACKUP CHECKLIST

### Before Defense Day:
- [ ] Export MySQL database: `mysqldump -u root spes_db > backup/spes_backup_FINAL.sql`
- [ ] Zip entire project folder: `SPES-SYSTEM-1_BACKUP.zip`
- [ ] Copy backup to USB drive
- [ ] Copy backup to Google Drive
- [ ] Screenshot all key pages (dashboard, onboarding, scheduling, completion)
- [ ] Test on the actual demo machine (laptop you'll present with)
- [ ] Verify XAMPP/MySQL starts automatically
- [ ] Pre-login all accounts in separate browser profiles
- [ ] Print this demo script

### Day of Defense:
- [ ] Arrive 30 minutes early
- [ ] Start XAMPP (Apache + MySQL)
- [ ] Run `php artisan serve` + `npm run dev`
- [ ] Open all browser windows with accounts pre-logged-in
- [ ] Verify http://127.0.0.1:8000 loads
- [ ] Keep this script open on phone/tablet as reference
- [ ] Have `storage/logs/laravel.log` open in notepad (for emergency debugging)

---

## 🗣️ KEY TALKING POINTS

### When explaining the system:
- "Role-based access control ensures each user only sees what they're authorized for"
- "The workflow is fully digital — from application to certificate generation"
- "Batch scheduling reduces admin workload by processing multiple beneficiaries simultaneously"
- "Skills-based matching provides intelligent job-beneficiary pairing"
- "Real-time notifications keep all stakeholders informed at every status change"

### When explaining tech stack:
- "Laravel 12 backend with Inertia.js for seamless single-page experience"
- "Vue 3 Composition API for reactive, component-based frontend"
- "Spatie Permission package for granular role-based access"
- "MySQL database with proper relationships and status tracking"

### If asked about future improvements:
- "Mobile-responsive PWA version for beneficiaries"
- "SMS notifications via Twilio (already integrated, needs production config)"
- "Google Calendar integration for scheduling sync"
- "DOLE reporting automation with CSV/PDF exports"
- "AI-powered skills matching enhancement"

---

## ⏱️ TIMING GUIDE

| Section | Duration | Cumulative |
|---------|----------|------------|
| System Overview | 1 min | 1 min |
| Registration + Onboarding | 2 min | 3 min |
| Admin Dashboard | 2 min | 5 min |
| Review & Approval | 2 min | 7 min |
| Exam + Interview Scheduling | 3 min | 10 min |
| PESO Evaluation | 2 min | 12 min |
| Employer (Jobs + Acknowledge) | 2 min | 14 min |
| DTR + Reports | 2 min | 16 min |
| Completion + Analytics | 2 min | 18 min |
| Q&A Buffer | 2 min | 20 min |

**Total demo: ~18-20 minutes** (adjust based on your defense time allocation)
