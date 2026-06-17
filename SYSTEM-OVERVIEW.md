# SPES Web-Based Management System
## System Overview

---

## 1. Introduction

The **SPES (Special Program for Employment of Students) Web-Based Management System** is a comprehensive digital platform designed to streamline and automate the administration of the Special Program for Employment of Students under the Department of Labor and Employment (DOLE). The system serves the City Public Employment Service Office (CPESO) by digitizing the entire SPES workflow — from beneficiary application to program completion — replacing manual paper-based processes with an efficient, transparent, and role-based web application.

The system connects four primary stakeholders: **Beneficiaries** (students, out-of-school youth, and dependents of displaced workers), **CPESO/PESO Administrators**, **PESO Staff Interviewers**, and **Partner Employers**.

---

## 2. System Architecture

The SPES Management System is built on a modern web technology stack:

| Layer | Technology |
|-------|-----------|
| Backend Framework | Laravel 12 (PHP) |
| Frontend Framework | Vue.js 3 (Composition API) |
| SPA Bridge | Inertia.js |
| Styling | Tailwind CSS |
| Database | MySQL |
| Authentication | Laravel Sanctum with email verification |
| Role Management | Spatie Laravel Permission |
| Real-time Notifications | Email (SMTP), SMS (Twilio), Dashboard alerts |

The application follows a role-based access control (RBAC) architecture where each user type has strictly defined permissions, ensuring data security and proper workflow enforcement.

---

## 3. User Roles and Access Levels

| Role | Access Level | Primary Functions |
|------|-------------|-------------------|
| **Admin / CPESO** | Full system access | Application review, scheduling, assignment, approval, analytics, user management |
| **PESO Admin** | Operational management | Same as Admin for day-to-day SPES operations |
| **PESO User (Interviewer)** | Limited access | Conduct assigned interviews, submit evaluations |
| **Employer** | Employer portal | Post jobs, acknowledge assignments, review DTR/reports, submit ratings |
| **Beneficiary** | Applicant portal | Apply, submit documents, view schedules, submit DTR/reports, view certificate |

---

## 4. Main System Workflow

### 4.1 Applicant Registration and Application Submission

The SPES application process begins with beneficiary registration. Applicants create an account and select their beneficiary category:

- **Student** — Currently enrolled in Senior High School, College, or Technical/Vocational programs
- **Out-of-School Youth (OSY)** — Youth not currently enrolled in any educational institution
- **Dependent of Displaced Worker (DDW)** — Dependents of workers who have been terminated or displaced

After email verification, beneficiaries complete a comprehensive SPES onboarding form that collects:
- Personal information (name, birthdate, address, contact details)
- Family background (parents' information, family income bracket)
- Educational background (school, course, year level)
- Skills assessment (selected from a categorized skills database)
- Required documents (valid ID, school enrollment, barangay certificate, supporting documents)

Upon submission, the system creates an application record with status **"Applied"** and the beneficiary's dashboard reflects the current stage in the SPES pipeline.

### 4.2 Admin/CPESO Review and Verification

CPESO administrators access a unified dashboard that displays all pending applications. The review process includes:

1. **Document Verification** — CPESO reviews uploaded documents for completeness and authenticity
2. **Eligibility Check** — Verifying the applicant meets SPES category requirements
3. **Decision Actions:**
   - **Approve** — Marks the beneficiary as verified, generates a temporary password, and sends an approval email notification
   - **Request Correction** — Returns the application to the beneficiary with specific remarks about what needs to be fixed
   - **Reject** — Denies the application with a documented reason and sends a rejection email

The system maintains a complete audit trail of all approval decisions, including timestamps and the responsible administrator.

### 4.3 Document Upload and Requirement Checking

The document management module handles:

- **Structured Document Types** — Each beneficiary category has specific required documents (birth certificate, school records, income proof, displacement certificates)
- **Status Tracking** — Each document is tracked with statuses: uploaded, under review, approved, needs correction
- **File Validation** — The system enforces file type restrictions (PDF, JPG, JPEG, PNG only) and size limits
- **Resubmission Flow** — When CPESO requests corrections, beneficiaries can upload replacement documents without restarting the entire application

Documents are stored securely on the server with organized directory structures per beneficiary, and CPESO can preview documents directly within the review interface.

### 4.4 Interview and Exam Scheduling

The scheduling module supports three types of SPES activities:

**Examination Scheduling:**
- CPESO creates exam schedules with date, time, location, and instructions
- Supports batch scheduling (multiple beneficiaries per exam slot)
- Automatic email notifications sent to scheduled beneficiaries
- Results recorded as passed or failed, triggering status transitions

**Interview Scheduling:**
- CPESO assigns a PESO staff member as the interviewer
- Google Meet integration provides video interview links
- Interviewers view only their assigned interviews (strict access control)
- Evaluation form captures: result (passed/failed/needs review) and detailed remarks

**Contract Signing:**
- Scheduled after qualification is confirmed
- Location, date, and instructions provided
- Result recorded as signed or not signed
- Triggers deployment status upon successful signing

All scheduling supports rescheduling with reason tracking, batch operations for efficiency, and optional beneficiary notifications.

### 4.5 Beneficiary Approval and Job Placement

The qualification and placement pipeline follows a strict sequential workflow:

```
Exam Passed → Interview Passed → Qualified → Approved → Assigned → Deployed → Ongoing
```

**Qualification:**
- After passing the interview, CPESO marks the application as "Qualified"
- Final approval transitions the application to "Approved" status

**Job Matching and Assignment:**
- The system provides skills-based matching between beneficiaries and available job postings
- Match scores are calculated based on overlapping skills between beneficiary profiles and job requirements
- CPESO assigns beneficiaries to specific employer job slots

**Employer Acknowledgment:**
- Employers receive notification of assigned beneficiaries
- Employers must acknowledge the assignment before work supervision begins
- Acknowledgment triggers the "Deployed" status

**Work Period:**
- Once deployed, beneficiaries submit Daily Time Records (DTR) with proof photos
- The DTR system uses server-side Philippine Standard Time to prevent time manipulation
- Beneficiaries submit Daily Accomplishment Reports documenting their work activities
- The first DTR or report submission automatically transitions status from "Deployed" to "Ongoing"

**Completion:**
- Employers submit completion for CPESO review
- CPESO verifies completion readiness: DTR records, approved daily reports, employer rating, and uploaded certificate
- Upon approval, the beneficiary's status becomes "Completed" and the certificate becomes available

### 4.6 Announcement and Notification System

The communication module ensures all stakeholders stay informed:

**Announcements:**
- CPESO creates announcements targeted to specific audiences (all users, beneficiaries only, or employers only)
- Announcements support image attachments
- Read/unread tracking per user
- CRUD operations (create, edit, delete) for administrators

**Notifications:**
- Email notifications for major status changes (approval, rejection, scheduling)
- SMS notifications via Twilio integration for interview schedules
- In-app dashboard notifications with real-time badge counts
- Categorized notification display: Status Updates, Requirement Reminders, Schedule Reminders, Assignment Updates, and General Announcements

**Smart Categorization:**
- The system automatically categorizes notifications based on content analysis
- Broadcast announcements are displayed without action buttons
- Targeted notifications include relevant action buttons (e.g., "View Schedule", "Upload Requirement")

### 4.7 Monitoring, Reports, and Record Management

**Beneficiary Monitoring:**
- Real-time tracking of all approved beneficiaries with their current workflow status
- Attendance compliance monitoring
- Daily accomplishment report tracking
- Completion readiness indicators

**Employer Supervision:**
- Employers review and approve/reject DTR submissions
- Employers review and approve/reject daily accomplishment reports
- Performance rating system (5 categories: punctuality, work quality, attitude, communication, overall)
- Certificate upload for completed beneficiaries

**Analytics Dashboard:**
- Applicants by school distribution
- Top hiring employers
- Performance rating trends over time
- Completion rate tracking
- Attendance compliance metrics
- Applicant trend analysis (monthly/yearly)

**Reports:**
- DOLE report generation and export (CSV format)
- Employer reliability metrics
- Activity audit trail with user, action, date, and module tracking
- Role-based report access (full analytics for Admin, limited for PESO User)

**Record Management:**
- Approved beneficiaries registry with revert capability
- Approved employers registry with compliance tracking
- Application history per beneficiary
- Interview evaluation history
- Rating history and averages

---

## 5. Security Features

| Feature | Implementation |
|---------|---------------|
| Authentication | Email/password with mandatory email verification |
| Role-Based Access | Spatie Permission middleware on all routes |
| CSRF Protection | Laravel web middleware (automatic token validation) |
| DTR Anti-Manipulation | Server-side Philippine time override via WorldTimeAPI with fallback |
| File Upload Security | MIME type validation, size limits, extension filtering |
| Session Management | Configurable session timeout, device tracking |
| Password Security | Bcrypt hashing, password strength validation |
| Route Protection | Middleware gates for beneficiary approval and employer approval |
| Data Isolation | Ownership verification on all employer/beneficiary data access |

---

## 6. Technology Justification

| Choice | Reason |
|--------|--------|
| Laravel 12 | Robust PHP framework with built-in security, ORM, and ecosystem support |
| Vue 3 + Inertia.js | Single-page application experience without separate API layer |
| Tailwind CSS | Rapid UI development with consistent design system |
| MySQL | Reliable relational database for structured SPES workflow data |
| Spatie Permission | Industry-standard role/permission management for Laravel |
| Google Meet Integration | Free, accessible platform for online interviews |
| WorldTimeAPI | Tamper-proof time verification for attendance integrity |

---

## 7. System Benefits

1. **Eliminates paper-based processing** — All applications, documents, and approvals are digital
2. **Reduces processing time** — Batch scheduling and automated notifications speed up workflows
3. **Improves transparency** — Beneficiaries can track their application status in real-time
4. **Ensures data integrity** — Server-side time verification prevents DTR manipulation
5. **Supports remote operations** — Online interviews via Google Meet, remote document submission
6. **Provides decision support** — Skills-based matching and analytics assist CPESO decision-making
7. **Maintains compliance** — Audit trails and DOLE report generation ensure regulatory compliance
8. **Scales efficiently** — Batch operations and role-based access support growing beneficiary volumes

---

## 8. Conclusion

The SPES Web-Based Management System transforms the traditional manual SPES administration into a streamlined digital workflow. By connecting beneficiaries, CPESO administrators, PESO interviewers, and partner employers through a unified platform, the system ensures efficient program delivery, transparent status tracking, and comprehensive record management — ultimately serving more beneficiaries with better oversight and reduced administrative burden.

---

*Document prepared for: Capstone/Thesis Defense Presentation*
*System Version: 1.0*
*Date: June 2026*
