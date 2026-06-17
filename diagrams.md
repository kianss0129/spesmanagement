# SPES Management System Diagrams

This document contains professional system diagrams for the SPES (Special Program for Employment of Students) web-based system, based on the existing Laravel + Vue.js (Inertia) implementation.

## 1. System Flow Diagram

```mermaid
flowchart LR
    subgraph Actors[User Roles]
        B[Beneficiary]
        E[Employer]
        P[CPESO Staff]
        A[Admin]
        D[DOLE / External Agency]
    end

    subgraph Frontend[Vue.js + Inertia Frontend]
        FE1[Authentication Pages]
        FE2[Application & Submission Pages]
        FE3[Document Upload Pages]
        FE4[Dashboard / Reports / Analytics Pages]
    end

    subgraph Backend[Laravel 10 Backend]
        BE1[Auth & Role Middleware]
        BE2[Application Controllers]
        BE3[Approval Workflow Engine]
        BE4[Reports & Analytics Services]
        BE5[Notification / Announcement Service]
        DB[(MySQL Database)]
    end

    B -->|Interact via browser| FE1
    B --> FE2
    B --> FE3
    B --> FE4

    E -->|Interact via browser| FE1
    E --> FE2
    E --> FE4

    P -->|Interact via browser| FE1
    P --> FE4

    A -->|Interact via browser| FE1
    A --> FE4

    FE1 -->|Inertia request| BE1
    FE2 -->|Inertia request| BE2
    FE3 -->|Inertia request| BE3
    FE4 -->|Inertia request| BE4

    BE1 -->|validate user, role, approval| DB
    BE2 -->|store/retrieve application data| DB
    BE3 -->|manage approval workflow| DB
    BE4 -->|generate reports| DB
    BE5 -->|send announcements/notifications| DB

    BE4 -->|report export / DOLE data| D
    D -->|regulatory feedback| BE4
```

## 2. Block Diagram

```mermaid
flowchart TB
    subgraph Client[Client Layer]
        Browser[Browser / Vue.js SPA]
        Mobile[Mobile Browser]
    end

    subgraph Server[Server Layer]
        WebServer[Laravel 10 HTTP Server]
        AuthModule[Authentication Module]
        Onboarding[Onboarding & Registration Module]
        ApplicationModule[Application Submission Module]
        DocumentsModule[Document Upload Module]
        ApprovalModule[Approval Workflow Module]
        ReportsModule[Reports & Analytics Module]
        NotificationModule[Notification / Announcement Module]
        DataStore[(MySQL Database)]
    end

    Browser --> WebServer
    Mobile --> WebServer

    WebServer --> AuthModule
    WebServer --> Onboarding
    WebServer --> ApplicationModule
    WebServer --> DocumentsModule
    WebServer --> ApprovalModule
    WebServer --> ReportsModule
    WebServer --> NotificationModule

    AuthModule --> DataStore
    Onboarding --> DataStore
    ApplicationModule --> DataStore
    DocumentsModule --> DataStore
    ApprovalModule --> DataStore
    ReportsModule --> DataStore
    NotificationModule --> DataStore

    subgraph Roles[Role-Based Access]
        Beneficiary[Beneficiary]
        Employer[Employer]
        CPESO[CPESO Staff]
        Admin[Admin]
    end

    Beneficiary -->|uses| Browser
    Employer -->|uses| Browser
    CPESO -->|uses| Browser
    Admin -->|uses| Browser
```

## 3. Context Diagram

```mermaid
flowchart LR
    Beneficiary[Beneficiary]
    Employer[Employer]
    CPESO[CPESO Staff]
    Admin[Admin]
    DOLE[DOLE / External Agency]

    subgraph System[SPES Management System]
        Authentication[Authentication & Role Verification]
        Application[Application Submission]
        Upload[Document Upload]
        Workflow[Approval Workflow]
        Reporting[Reports & Analytics]
        Database[(MySQL Database)]
    end

    Beneficiary -->|submit application, upload documents, view status| Application
    Beneficiary -->|upload documents| Upload
    Beneficiary -->|view dashboard & reports| Reporting

    Employer -->|post jobs, review applicants, schedule interviews| Application
    Employer -->|view ratings, monitor beneficiaries| Reporting

    CPESO -->|review applications, approve/reject, manage applicants| Workflow
    CPESO -->|generate reports, compliance dashboards| Reporting

    Admin -->|manage roles, users, system settings| Authentication
    Admin -->|monitor system activity| Reporting

    System -->|store and retrieve data| Database
    Reporting -->|export regulatory reports| DOLE
    DOLE -->|receive compliance reports| System
```

## 4. Flowchart (Application to Approval Process)

```mermaid
flowchart TD
    Start((Start))
    Register[Register / Login]
    EmailVerify{Email Verified?}
    Onboarding[Complete Onboarding]
    SubmitApp[Submit SPES Application]
    UploadDocs[Upload Required Documents]
    ValidateDocs[Validate Uploaded Documents]
    ReviewApp[PESO Review Application]
    Decision{Approve or Reject}
    Approved[Approve Beneficiary / Employer]
    Rejected[Reject / Request Revision]
    AssignJob[Assign Beneficiary to Employer]
    Monitor[Monitor Attendance & Progress]
    GenerateReport[Generate Reports / DOLE Export]
    Completed((Completed))

    Start --> Register
    Register --> EmailVerify
    EmailVerify -->|yes| Onboarding
    EmailVerify -->|no| EmailVerify
    Onboarding --> SubmitApp
    SubmitApp --> UploadDocs
    UploadDocs --> ValidateDocs
    ValidateDocs --> ReviewApp
    ReviewApp --> Decision
    Decision -->|approved| Approved
    Decision -->|rejected| Rejected
    Rejected --> UploadDocs
    Approved --> AssignJob
    AssignJob --> Monitor
    Monitor --> GenerateReport
    GenerateReport --> Completed
```

## 5. HIPO Diagram (Hierarchical Input-Process-Output)

```mermaid
flowchart TB
    SPES[SPES Management System]

    SPES --> Input[Input]
    SPES --> Process[Process]
    SPES --> Output[Output]

    subgraph I[Input]
        I1[User Authentication Data]
        I2[Registration & Application Data]
        I3[Document Uploads]
        I4[Review Decisions]
        I5[Report Filters]
    end

    subgraph P[Process]
        P1[Authenticate & Authorize Users]
        P2[Collect Application Data]
        P3[Verify & Store Documents]
        P4[Execute Approval Workflow]
        P5[Assign Beneficiaries to Employers]
        P6[Compute Reports & Analytics]
        P7[Send Notifications & Announcements]
    end

    subgraph O[Output]
        O1[Application Status Updates]
        O2[Approved Beneficiary / Employer Records]
        O3[Dashboard Metrics]
        O4[Regulatory Reports for DOLE]
        O5[Notification Messages]
    end

    Input --> P
    Process --> Output
    I1 --> P1
    I2 --> P2
    I3 --> P3
    I4 --> P4
    I5 --> P6
    P1 --> O1
    P2 --> O2
    P3 --> O1
    P4 --> O2
    P6 --> O3
    P6 --> O4
    P7 --> O5
```

---

### Notes
- All diagrams reflect the actual role-based Laravel route structure and Inertia/Vue frontend flow.
- The system architecture is consistent across the diagrams: Browser → Vue/Inertia frontend → Laravel backend → MySQL database.
- The approval flowchart uses the real onboarding, document upload, PESO review, and approval modules present in the codebase.
- `DOLE` is represented as the external regulatory consumer of report exports.
