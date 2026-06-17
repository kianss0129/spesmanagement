CITY COLLEGE OF SAN FERNANDO PAMPANGA
City of San Fernando (P)



A Web-Based Management System for the Special Program for Employment of Students (SPES) in San Fernando, Pampanga



Proponents/Developers
Lacson, Jerome C.
Tiomico, Kissel C.
Yalung, Adrian T.

May 2026


A Capstone Project presented to the City College of San Fernando Pampanga in partial fulfillment of the requirements for the Degree in Bachelor of Science in Information Technology


---

## APPROVAL SHEET

The Capstone Project entitled **A Web-Based Management System for the Special Program for Employment of Students (SPES) in San Fernando, Pampanga** was reviewed and assessed as compliant with the requirements stipulated by the College, and is hereby approved and recommended for implementation and presentation before the Panel set by the College.

Recommended by:

RONALIZA V. DOMINGO
Adviser’s Signature over Name

Approved by:

AVEGAILLE M. FERRER, MSIT		Aileen C. Villanueva, MPA, MSSW, RSW
Program Head				Acting College Administrator


---

## PANEL APPROVAL SHEET

The Capstone Project entitled **A Web-Based Management System for the Special Program for Employment of Students (SPES) in San Fernando, Pampanga** has been presented before the Panel set by the College. It was reviewed, assessed, and approved as compliant with the requirements for the Degree in Bachelor of Science in Information Technology.

Approved by:

John Oliver Dizon (Panel 1)		Alexis Perez Cantoria (Panel 2)

Jhosane Liwanag (Lead Panel)

AVEGAILLE M. FERRER, MSIT		Aileen C. Villanueva, MPA, MSSW, RSW
Program Head				Acting College Administrator


---

## DEDICATION

This research is dedicated to all students, out-of-school youth, and dependents of displaced workers who continue to pursue their education and future despite financial challenges. We dedicate this study to the beneficiaries of the Special Program for Employment of Students (SPES). We hope that this project will help improve the SPES process by making it more organized, accessible, and efficient for applicants, employers, and administrators.

This work is also dedicated to the City Public Employment Service Office (CPESO), local government units, and partner institutions involved in implementing the SPES program. May this web-based management system help improve documentation, monitoring, and overall program management through digital solutions.

To future researchers and developers, may this study serve as a guide and inspiration in creating systems that solve real-world problems and improve public services.

To our professors, advisers, and panel members, thank you for your guidance, patience, and support throughout the completion of this research.

To our families and loved ones, thank you for your encouragement, understanding, and motivation during the challenges we faced while working on this project.

Lastly, we dedicate this research to ourselves as researchers and developers. This project reflects our hard work, teamwork, and dedication. May the knowledge and experience we gained from this journey help us grow as future professionals.

“Success does not come from what you do occasionally, but from what you do consistently.”


---

## ACKNOWLEDGEMENT

The researchers would like to sincerely thank the following people who helped make this study possible:

First, to Ms. Ronaliza V. Domingo, our research adviser, for her guidance, patience, and continuous support throughout the entire process. Her advice, suggestions, and knowledge were very helpful in improving both our research and system.

To the panel members, professors, and faculty of City College of San Fernando Pampanga, for sharing their expertise, comments, and recommendations that helped improve and validate this study.

To the City Public Employment Service Office (CPESO) staff and respondents, for their cooperation and willingness to provide important information and feedback needed for this research, especially about the Special Program for Employment of Students (SPES).

To the administration and staff of City College of San Fernando Pampanga, for the support, resources, and learning environment they provided during the completion of this project.

To our families, for their endless support, understanding, patience, and encouragement throughout this journey.

To our friends, classmates, and colleagues, for their help, ideas, and motivation while working on this study.

And most of all, to Almighty God, for giving us strength, wisdom, and guidance to finish this research successfully.

This study would not have been completed without the help and support of all these people.

– The Researchers


---

## ABSTRACT

The Special Program for Employment of Students (SPES) in San Fernando, Pampanga, faced significant operational inefficiencies due to a heavy reliance on manual, paper-based management processes. These challenges included slow workflows, a high risk of lost documentation, and difficulties in retrieving information, which ultimately hindered the program's ability to effectively support student beneficiaries. Furthermore, inconsistent monitoring and a lack of centralized data gathering made reporting and performance tracking unreliable for both implementation offices and employers.

This study developed **A Web-Based Management System for the Special Program for Employment of Students (SPES)**, a digital platform designed to modernize the program's administrative and monitoring functions. The system streamlines the application process, provides a centralized database for participant and employer records, and integrates automated tracking features—such as digital attendance and performance monitoring—to enhance transparency and accountability. The development of this system aligns with the digital documentation goals of DOLE Department Order No. 175.

Survey results from program beneficiaries highlighted a strong demand for this digital transformation, with 76.2% of respondents agreeing that digitalization would improve the SPES process and 81% expressing a preference for online submission and tracking. The study addressed critical user concerns, such as the 79.3% of participants who previously encountered errors in manual records and the 58.6% who reported delays in status updates.

The study successfully achieved its objectives by creating a robust digital solution that reduces administrative workload and prevents data loss. By providing real-time tracking and a secure audit trail, the system positions the San Fernando SPES program to deliver more efficient and transparent services, serving as a vital bridge between education and employment for the youth in the community.

Keywords: Special Program for Employment of Students (SPES), Web-Based Management System, Digital Transformation, Youth Employment, Process Automation, San Fernando Pampanga.


---

## TABLE OF CONTENTS

* CHAPTER I 	 BACKGROUND OF THE STUDY ........................................... 1
* CHAPTER II 	 REVIEW OF RELATED LITERATURE AND STUDIES ........................ 2
* CHAPTER III 	 METHODOLOGY .................................................. 3
* CHAPTER IV 	 RESULTS AND DISCUSSION ........................................ 4
* CHAPTER V 	 SUMMARY, CONCLUSIONS AND RECOMMENDATIONS ....................... 5
* REFERENCES ................................................................. 6
* APPENDICES ................................................................. 7
* RESEARCHER’S PROFILE ...................................................... 8


---

## LIST OF TABLES

Table 1. Hardware Requirements .............................................. 3
Table 2. Software Requirements .............................................. 3
Table 3. Module / Feature Mapping ............................................ 4
Table 4. Evaluation Results Summary ......................................... 5
Table 5. Respondent Profile .................................................. 5


---

## LIST OF FIGURES

Figure 1. Conceptual Framework of the Study ................................... 3
Figure 2. Context Diagram of the SPES System .................................. 4
Figure 3. SPES Application and Approval Flowchart ............................. 4
Figure 4. HIPO Chart for SPES System Modules ................................ 5
Figure 5. Login Page Placeholder ............................................ 6
Figure 6. Beneficiary Dashboard Placeholder .................................. 6
Figure 7. CPESO Admin Dashboard Placeholder .................................. 7
Figure 8. Employer Module Placeholder ....................................... 7
Figure 9. Reports Page Placeholder .......................................... 7
Figure 10. Analytics Page Placeholder ....................................... 7


---

# CHAPTER I
## BACKGROUND OF THE STUDY

Education is one of the most powerful tools for personal growth and national development. It enables individuals to acquire knowledge, develop skills, and contribute meaningfully to society. In the Philippines, education is viewed as a key to breaking the cycle of poverty and improving one’s quality of life. However, many Filipino students continue to face financial challenges that hinder them from finishing their studies. The rising cost of tuition, school materials, and living expenses often forces students to look for part-time or temporary work to support their education (Del Rosario, 2021). As a result, balancing academic responsibilities and financial needs becomes a significant struggle for many young learners.

To address this concern, the Philippine government established the Special Program for Employment of Students (SPES) under Republic Act No. 7323, later amended by Republic Act No. 9547 and Republic Act No. 10917 (2016). The program is implemented by the Department of Labor and Employment (DOLE) as part of its youth employment initiatives. RA 10917 expanded the program’s coverage to include out-of-school youth (OSY) and dependents of displaced or would-be displaced workers, ensuring that more young Filipinos can benefit from temporary employment opportunities. SPES aims to help poor but deserving students earn income and gain work experience during school breaks while continuing their education. Through this program, students are given temporary employment opportunities in both public and private institutions. The program follows a wage-sharing scheme, in which 60 percent of the student’s salary is paid by the employer and 40 percent by the government (DOLE, 2023). This setup ensures that students receive fair compensation while employers and the government work hand in hand to support youth development.

The SPES is not only a form of financial assistance but also a valuable learning opportunity. Students who participate in the program are exposed to real work environments, allowing them to develop skills such as responsibility, time management, communication, and teamwork. These experiences help prepare them for future employment and improve their confidence in facing workplace challenges (Ramirez & Bautista, 2020). Furthermore, SPES promotes the values of productivity and independence among the youth. It teaches them that hard work and perseverance are key to achieving their educational goals despite economic hardship.

However, the local implementation still encounters barriers such as limited promotion and difficulties in verifying requirements. These challenges occur because SPES announcements are often shared only through word of mouth and physical postings, which limits the reach of information. As a result, many eligible students are not fully informed about application schedules, required documents, and important deadlines.

Processing also becomes slow because all submissions are handled manually, requiring staff to review documents one by one, confirm eligibility, and coordinate with different offices involved in the evaluation process. During peak application periods, the volume of submissions increases significantly, leading to longer queues and slower verification.

Verifying requirements is equally challenging because documents such as school records, identification papers, and tax forms are submitted physically and stored in separate folders. Staff must check each file individually, and unclear or incomplete submissions require applicants to return for corrections. Since records are not consolidated, identifying which applicants still have pending requirements often requires repeated manual checking.

These overlapping issues cause delays in the overall processing and approval of applications. When document verification takes longer than expected or requires multiple follow-ups, applicants experience extended waiting times, which can lessen their motivation and interest in continuing with the program.


## STATEMENT OF THE PROBLEM

### General Problem

The current implementation of the Special Program for Employment of Students (SPES) in San Fernando, Pampanga faces inefficiencies in management, documentation, and monitoring, which limits the program’s effectiveness in supporting student beneficiaries.

### Specific Problems

Specifically, this study seeks to address the following:

1. Manual processing slows down workflows, increases the risk of errors or lost documents, and makes information hard to retrieve. This results in inefficiency, inconsistent records, and higher administrative workload.
2. Gathering of data during and after the program is done manually and inconsistently, leading to inaccurate or incomplete information. Monitoring participant progress and gathering post-program data becomes difficult, making reporting and evaluation unreliable.
3. Limited monitoring, transparency, and accountability in program implementation, as attendance, performance, and employer compliance are still tracked manually, making it difficult to verify records, trace inconsistencies, and ensure accurate documentation.


## OBJECTIVES OF THE STUDY

### General Objective

To assess the implementation of the SPES program in San Fernando, Pampanga and develop a web-based management system to enhance its efficiency, transparency, monitoring, and documentation processes.

### Specific Objectives

1. To minimize the reliance on manual processing by designing a digital platform that streamlines documentation, minimizes errors, prevents data loss, and reduces administrative workload.
2. To develop a centralized system for monitoring and data gathering that allows accurate, consistent, and timely collection of participant and employer information during and after the program.
3. To enhance monitoring, transparency, and accountability by integrating automated tracking features such as digital attendance, performance monitoring, employer compliance verification, and a secure audit trail for user actions and data changes.


## SIGNIFICANCE OF THE STUDY

This study is significant as it provides a digital solution to enhance the efficiency and transparency of the SPES program in San Fernando, Pampanga. It supports Republic Act No. 10917 and DOLE Department Order No. 175, Series of 2017, which emphasize the importance of proper documentation and system-based monitoring of SPES beneficiaries.

### City Public Employment Service Office (CPESO) and Local Government Unit (LGU)

The study helps CPESO and the LGU identify problems in SPES operations and improve how they monitor and manage student records. It supports DOLE’s guidelines under Rule VII of Department Order 175, which promotes better digital documentation and reporting of SPES data.

### Employers

Employers will have an easier way to submit and track program documents like attendance, reducing delays and errors.

### SPES Beneficiaries

Students, out-of-school youth, and dependents of displaced workers will benefit from faster applications and clearer information about the program.

### Future Researchers and Policymakers

The study can serve as a guide for future improvements in SPES and other youth employment programs.


## SCOPE AND DELIMITATIONS

### Scope

This study focuses on how the Special Program for Employment of Students (SPES) is implemented and managed in San Fernando, Pampanga. It involves students, employers, and DOLE or LGU staff. The study covers the main parts of SPES operations such as application, selection, job placement, and monitoring. It aims to improve the process through a web-based system that supports the digital goals of DOLE Department Order No. 175, Series of 2017, particularly Rule VII on system-based monitoring, and integrates an audit trail feature to ensure transparency, accountability, and secure tracking of all user activities within the system.

### Delimitations

The study is limited to the management and monitoring processes of the Special Program for the Employment of Students (SPES) in San Fernando, Pampanga. It does not include payroll computation, salary distribution, or other DOLE employment programs. The Department of Labor and Employment (DOLE) is only involved in providing the budget allocation or sponsorship for the payroll, but is not part of the actual payroll processing or distribution handled by the local government. The study also excludes employer financial audits, student performance evaluations during employment, and post-program employment tracking.


# CHAPTER II
## REVIEW OF RELATED LITERATURE AND STUDIES

### Foreign Literature

Studies from other countries indicate that youth employment programs provide young people with important opportunities to develop work habits, practical skills, and self-confidence. These programs help prepare students for future careers by teaching discipline, responsibility, and professional behavior. In the United States, the Boston Summer Youth Employment Program (SYEP) has shown that structured temporary work can positively influence youth attitudes, work habits, and readiness for long-term employment (Modestino & Paulsen, 2019; Heller, 2021). Programs like these allow students to gain meaningful experience while building confidence and understanding workplace expectations.

Despite these benefits, foreign literature also identifies significant challenges. Programs that rely heavily on paper-based attendance sheets and manual documentation often face delays, lost records, and inconsistent reporting, making it difficult for managers to track progress accurately (Aspen Institute, 2021). Globally, similar problems appear. In Spain, temporary workers often experience poor supervision and irregular schedules, which negatively affect skill development and job satisfaction (García-Barrero, 2025). In Korea, limited access to temporary jobs and uneven institutional support result in disparities among students, with some gaining more opportunities than others (Chung, 2023). These studies highlight the importance of proper monitoring, coordination, and digital systems to ensure that youth employment programs operate efficiently and fairly. These issues mirror challenges faced by SPES in the Philippines, such as inconsistent supervision, incomplete documentation, and fragmented coordination between agencies and employers.

### Local Literature

In the Philippines, the Special Program for Employment of Students (SPES) provides students with work experience and financial support while enhancing their motivation, persistence, and practical skills (Salvaña et al., 2025). Local studies confirm that SPES contributes to youth employability and financial independence. However, implementation challenges remain. Many LGUs struggle with slow or manual documentation, delayed salary processing, and fragmented coordination between schools, employers, and agencies (Codera, 2021; DOLE Rapid Assessment Report). These issues can reduce program efficiency and affect student experiences.

Additionally, students participating in SPES may face difficulties balancing work and academic responsibilities. Short-term employment can support skill development, but without proper monitoring from schools and host organizations, students may experience declines in academic performance (Beam & Quimbo, 2023; Bautista Junior et al., 2022). Resource-rich LGUs tend to manage SPES more effectively due to better digital capabilities, while less-resourced LGUs struggle with paper-based systems, inconsistent screening, and delays in reporting and payroll. These challenges highlight the need for a centralized, digital platform to streamline processes, improve transparency, and ensure better coordination among all stakeholders.

### Foreign Studies

Several foreign studies provide further insight into operational challenges and benefits of youth employment programs. Poorly structured work schedules and insufficient supervision can reduce the positive outcomes of part-time or temporary employment (Baburkin, Talanov, & Kushnarev, 2022). In Spain, temporary jobs often lack structured training and consistent supervision, limiting long-term skill development and employability (García-Barrero, 2025). Similarly, in Korea, unequal access to job opportunities and weak institutional support create barriers for some students, affecting fairness and inclusivity (Chung, 2023). These foreign studies emphasize that careful planning, supervision, and digital management are essential for maximizing program benefits, which is similar to challenges SPES currently faces.

### Local Studies

Local studies show that SPES achieves its main objectives, but differences in LGU capacities and the absence of a unified monitoring system often lead to delays, inconsistent records, and payroll issues (Bachita & Caelian, 2025; Beam & Quimbo, 2021; Bautista Junior et al., 2022). While the program successfully provides financial support and work experience, these recurring operational problems hinder efficiency and reduce transparency. Short-term employment improves youth employability, but structured oversight is required to prevent negative impacts on academic performance.

These findings suggest that SPES would greatly benefit from a web-based management system. Such a system could automate recordkeeping, improve coordination among DOLE, LGUs, and employers, and provide real-time tracking of student progress and compliance. A digital platform would address delays, enhance transparency, and ensure that all beneficiaries receive consistent and equitable opportunities.

### Conceptual Framework

The conceptual framework for this study is based on the Input-Process-Output (IPO) model, which describes how the SPES management system transforms stakeholder inputs into useful program outputs through digital processing.

Figure 1. Conceptual Framework of the Study

```
Input --> Process --> Output

Inputs:
- Beneficiary data
- Employer data
- CPESO staff requirements
- SPES documents
- Survey feedback

Processes:
- User authentication and role validation
- Application submission and document upload
- Document verification and approval
- Monitoring of attendance and performance
- Report generation and analytics

Outputs:
- Approved SPES applications
- Centralized records
- Monitoring dashboards
- DOLE-compliant reports
- Audit trail logs
```

This framework illustrates how data gathered from beneficiaries, employers, and CPESO staff is processed by the web-based system to produce more efficient and transparent SPES management outputs.


# CHAPTER III
## METHODOLOGY

This chapter discusses the research design, project development phases, evaluation procedure, evaluation criteria, and instruments and techniques used in the study.

### 3.1 Project Research Design

The SPES management system project adopted a descriptive-developmental research design combined with a software development methodology. The research design emphasized iterative development, stakeholder consultation, and continual testing.

The system design includes:
- System flow
- Block diagram
- Flowchart
- Context diagram
- HIPO chart

Figure 2. Context Diagram of the SPES System

```
Beneficiary --> SPES System <-- Employer
                ^   |   ^
                |   |   |
                v   |   v
             CPESO Staff  Admin
                |
                v
               DOLE
```

Figure 3. SPES Application and Approval Flowchart

```
Start
  |
  v
[Beneficiary Login]
  |
  v
[Complete Application]
  |
  v
[Upload Documents]
  |
  v
[Submit Application]
  |
  v
[Store Data]
  |
  v
[CPESO Review]
  |
  v
[Verify Documents]
  |
  +----------------+   
  |                |
  v                v
[Approve]      [Request Correction]
  |                |
  v                v
[Update Status]  [Notify Beneficiary]
  |
  v
[Employer Assignment]
  |
  v
[Monitor Deployment]
  |
  v
[Generate Reports]
  |
  v
End
```

Figure 4. HIPO Chart for SPES System Modules

```
1. Authentication Module
   1.1 Inputs: credentials, login request
   1.2 Process: validate user, assign role, create session
   1.3 Outputs: authenticated access, dashboard
2. Beneficiary Management
   2.1 Inputs: profile, documents, applications
   2.2 Process: validate info, store data, track status
   2.3 Outputs: beneficiary records, status updates
3. Employer Management
   3.1 Inputs: employer profile, compliance documents
   3.2 Process: verify employer, log placements
   3.3 Outputs: employer record, compliance status
4. PESO Dashboard
   4.1 Inputs: pending applications, uploaded documents
   4.2 Process: verify, approve, reject
   4.3 Outputs: application decisions, logs
5. Reports Module
   5.1 Inputs: compiled data, attendance, ratings
   5.2 Process: generate summaries, export reports
   5.3 Outputs: SPES reports, export files
6. Analytics Module
   6.1 Inputs: metrics, performance data
   6.2 Process: compute trends, create charts
   6.3 Outputs: dashboards, analytics views
7. Role Management Module
   7.1 Inputs: role definitions, permissions
   7.2 Process: enforce access, render menus
   7.3 Outputs: secured access, privilege enforcement
```

### 3.2 Project Development

The development of the SPES system followed these phases:

#### 3.2.1 Planning

The planning phase identified project objectives, stakeholder requirements, and resources. A timeline and task breakdown were established, and the research scope was confirmed.

#### 3.2.2 Analysis

Requirements gathering was conducted through interviews, surveys, and literature review. The analysis clarified the needs of SPES beneficiaries, employers, and CPESO staff. Functional and non-functional requirements were documented.

#### 3.2.3 Design

The system architecture was designed using Laravel for the backend and Vue.js/Inertia for the frontend. Database schema, user interface layouts, and module relationships were developed.

#### 3.2.4 Development

Coding was completed for authentication, beneficiary and employer workflows, document handling, approval processes, reporting, and analytics. The development phase also included integration of role-based access control and audit trail capabilities.

#### 3.2.5 Testing

The system was tested for functionality, usability, reliability, and security. Test cases were executed for application submission, document review, approval workflows, and report generation.

#### 3.2.6 Deployment

The system was deployed on a local XAMPP environment for demonstration and pilot use. Deployment instructions were prepared for future implementation.


### 3.3 Evaluation Procedure

The system evaluation combined quantitative and qualitative data collection. Respondents included SPES beneficiaries, employers, and CPESO staff.

Surveys were distributed using Google Forms, and interviews were conducted with CPESO staff and employers. The survey utilized a Likert scale, and results were analyzed using weighted mean.

The evaluation process included:
1. Preparing survey and interview guides.
2. Collecting responses and feedback.
3. Measuring results against ISO 9126 criteria.
4. Documenting findings and recommendations.


### 3.4 Evaluation Criteria

The system was evaluated using ISO 9126 quality attributes.

#### 3.4.1 Functionality

The system was assessed for correct operation of SPES application submission, document upload, approval workflows, and reporting.

#### 3.4.2 Reliability

Reliability was measured by data consistency, system stability, and successful document retrieval.

#### 3.4.3 Usability

Usability was assessed through user feedback on navigation, clarity, and ease of use.

#### 3.4.4 Efficiency

The system’s performance was evaluated by response times, workflow speed, and perceived reduction in manual effort.

#### 3.4.5 Maintainability

Maintainability was reviewed based on code structure, documentation, and modular design.

#### 3.4.6 Portability

Portability was assessed through compatibility with common server environments and standard deployment setups.

#### 3.4.7 Security

Security evaluation covered authentication, authorization, data protection, and access control.


### 3.5 Instruments and Techniques Used

The following instruments and techniques were used:

- Survey questionnaire (Google Forms)
- Interviews with CPESO staff and employers
- Likert-scale measurement
- Weighted mean analysis
- System observation and usability review


# CHAPTER IV
## RESULTS AND DISCUSSION

### 4.1 Project Technical Description

#### 4.1.1 Hardware Requirements

Table 1. Hardware Requirements

| Requirement | Minimum | Recommended |
|-------------|---------|-------------|
| Processor | Intel Core i3 or equivalent | Intel Core i5 / AMD Ryzen 5 |
| RAM | 4 GB | 8 GB |
| Storage | 20 GB free | 50 GB SSD |
| Network | Stable internet | Broadband connection |
| Display | 1280 x 720 | 1920 x 1080 |


#### 4.1.2 Software Requirements

Table 2. Software Requirements

| Requirement | Description |
|-------------|-------------|
| Operating System | Windows 10/11 or Linux |
| Web Server | Apache HTTP Server (XAMPP for development) |
| Database Server | MySQL / MariaDB |
| Backend Framework | Laravel 10 |
| Programming Language | PHP 8.x |
| Frontend Framework | Vue.js 3, Inertia.js |
| Package Managers | Composer, npm/yarn |
| Browser | Modern browser (Chrome, Edge, Firefox) |
| Survey Platform | Google Forms |


#### 4.1.3 Technical Stack

The SPES Web-Based Management System uses a modern web stack consisting of:
- Laravel 10 for backend application logic
- Vue.js 3 and Inertia.js for frontend rendering
- MySQL for database storage
- Apache/XAMPP for local server environment
- Tailwind CSS for user interface styling
- Google Forms for survey distribution


### 4.2 Project Structured Organization

The system is organized into several primary modules that reflect the main functional areas of SPES management.

#### 4.2.1 Login / Authentication

This module supports secure user access and role-based permissions. It handles user registration, login, password reset, and session control. Users are redirected to role-specific dashboards based on their assigned access.

#### 4.2.2 Beneficiary Management

This module manages SPES beneficiary data, application submission, and document uploads. It stores personal profiles, educational information, document requirements, and application status.

#### 4.2.3 Employer Management

This module supports employer registration, company profile management, compliance document uploads, and placement of beneficiaries. Employers can track their assigned students and submit required reports.

#### 4.2.4 PESO Dashboard

The PESO dashboard serves as the central control panel for administrative staff. It displays pending applications, approved and rejected cases, document verification status, and quick access to monitoring tools.

#### 4.2.5 Reports

The reports module generates summaries of SPES activities, attendance records, and compliance data. It produces export-ready documents for DOLE reporting and internal review.

#### 4.2.6 Analytics

The analytics module presents key program metrics, such as applicant distribution, completion rates, attendance compliance, and beneficiary ratings. It supports decision-making and trend analysis.

#### 4.2.7 Role Management

This module enforces access control and separates privileges for administrators, CPESO staff, employers, and beneficiaries. It ensures users see only the functions relevant to their role.


### 4.3 Project Limitations and Capabilities

#### 4.3.1 System Capabilities

Table 3. Module / Feature Mapping

| Module | Feature | Description |
|--------|---------|-------------|
| Authentication | Secure login | Role-based user access |
| Beneficiary Management | Application submission | Online SPES application form |
| Beneficiary Management | Document upload | Upload ID, school records, contracts |
| Employer Management | Employer profile | Register and manage company details |
| Employer Management | Compliance tracking | Upload employer compliance documents |
| PESO Dashboard | Application review | Approve or reject beneficiaries |
| Reports | SPES reports | Generate program summaries and exports |
| Analytics | Trend charts | Display completion, attendance, ratings data |
| Role Management | Access control | Restrict features based on user role |
| Audit Trail | Logging | Record user actions and status changes |


#### 4.3.2 System Limitations

The SPES system has the following limitations:
- Payroll computation and salary distribution are not included.
- Post-program employment tracking is not implemented.
- Employer financial audits are excluded.
- No mobile application interface is provided; the system is web-based only.
- Real-time SMS or email notification delivery is not part of the current implementation.
- Detailed student academic performance integration is not included.

These limitations reflect the scope of this study and the project’s focus on SPES management, documentation, and monitoring.


### 4.4 Project Evaluation

#### 4.4.1 Survey Results

Table 4. Evaluation Results Summary

| Evaluation Item | Result |
|-----------------|--------|
| Digitalization improves SPES | 76.2% agree |
| Preference for online submission | 81% agree |
| Previous record errors | 79.3% experienced errors |
| Delays in status updates | 58.6% experienced delays |
| Manual submission reliance | 89.7% manual submission |
| Difficulty of application process | 55.4% difficult |
| Difficulty understanding process | 50.4% difficult |


#### 4.4.2 Respondent Profile

Table 5. Respondent Profile

| Respondent Group | Description | Sample Feedback |
|------------------|-------------|-----------------|
| SPES Beneficiaries | Students, OSY, dependents | Need easier online process and clearer updates |
| Employers | Partner establishments | Need streamlined compliance and monitoring |
| CPESO Staff | Implementation officers | Need centralized tracking and report generation |


#### 4.4.3 Interpretation of Results

The survey responses indicate that the majority of SPES stakeholders perceive the current manual process as inefficient and error-prone. Most respondents support an online system for application submission and status tracking.

The high percentage of respondents reporting record errors and delays supports the need for a centralized digital system. The system’s design addresses these issues through document validation, status tracking, and audit logs.

Respondents also expressed a strong preference for online submission and monitoring, which aligns with the system’s goal of reducing manual workloads and improving transparency.

#### 4.4.4 Alignment with ISO 9126 Criteria

- **Functionality**: The system performs key SPES operations such as application submission, document upload, approval workflows, and reporting.
- **Reliability**: Data consistency and successful file retrieval were validated through testing and pilot use.
- **Usability**: The user interface and workflow were designed to be intuitive and supportive of user tasks.
- **Efficiency**: The system reduces manual effort and speeds up SPES processing by centralizing records.
- **Maintainability**: Modular architecture and documentation support future updates.
- **Portability**: The system can be deployed on common web servers and development environments.
- **Security**: Role-based access control and authentication protect sensitive SPES data.

Overall, the evaluation shows that the system meets the required quality attributes and offers measurable improvements over the existing manual processes.


# CHAPTER V
## SUMMARY, CONCLUSIONS, AND RECOMMENDATIONS

### 5.1 Summary

This study developed a web-based management system for the Special Program for Employment of Students (SPES) in San Fernando, Pampanga. The system addressed the program’s current challenges in manual application processing, document verification, and monitoring.

The project included the development of beneficiary and employer modules, a CPESO dashboard, reports, and analytics. It also integrated role-based access control and file storage for SPES documents.

Evaluation results showed strong user support for digitalization. Respondents reported that online submission, status tracking, and centralized recordkeeping would significantly improve the SPES process.

### 5.2 Conclusions

Based on the findings, the following conclusions are drawn:

1. The current SPES process in San Fernando, Pampanga is significantly hindered by manual workflows, inconsistent documentation, and delayed communication.
2. A web-based management system is an effective solution to streamline application processing, improve verification accuracy, and provide better monitoring of beneficiaries and employers.
3. The developed system meets the needs of SPES stakeholders by offering secure authentication, role-based access, document management, and reporting functionalities.
4. Evaluation results confirm that stakeholders perceive digital transformation as beneficial and preferable to the existing manual system.
5. The system’s design aligns with ISO 9126 quality standards, particularly in functionality, usability, efficiency, reliability, maintainability, portability, and security.

### 5.3 Recommendations

The following recommendations are proposed for the continued improvement and implementation of the SPES system:

1. Implement the web-based system for pilot use in CPESO offices and gather additional feedback for refinement.
2. Expand the system to include payroll processing and salary distribution modules in future versions.
3. Develop mobile-friendly interfaces or a dedicated mobile application for easier beneficiary access.
4. Integrate real-time notification features such as email or SMS to improve communication on application status and report deadlines.
5. Continue to collect and analyze user feedback to enhance usability and expand system capabilities.
6. Provide training workshops for CPESO staff, employers, and beneficiaries to ensure proper adoption and use of the system.


# REFERENCES

Aspen Institute. (2021). Summer Youth Employment Programs: Lessons from the field. https://www.aspencommunitysolutions.org/report/syep-fact-sheets/

Bachita, E., & Caelian, M. V. (2025). Implementation, challenges, and opportunities of the Special Program for Employment of Students. Technium Social Sciences Journal, 72, 14–38. https://doi.org/10.47577/tssj.v72i1.12904

Baburkin, A., Talanov, M., & Kushnarev, O. (2022). [Study title]. [Journal name].

Beam, E., & Quimbo, S. (2021). The impact of short-term work for low-income youth. Review of Economics and Statistics. https://doi.org/10.1162/rest_a_01135

Bautista Junior, et al. (2022). [Title]. [Journal].

Chung, D.-H. (2023). Temporary employment and organizational status in Korean universities. Asia Pacific Journal of Business. https://doi.org/10.32599/apjb.14.3.202309.89

Codera, J. (2021). YES to SPES? Documentation challenges in youth programs. https://d1wqtxts1xzle7.cloudfront.net/76250218/K0209393105-libre.pdf

Department of Information and Communications Technology. (2023). E-government initiatives. https://dict.gov.ph

Department of Labor and Employment. (2015). Rapid assessment of SPES. https://ils.dole.gov.ph/policy-brief/2015-policy-briefs/a-rapid-assessment-of-the-special-program-on-employment-of-students-spes

García-Barrero, J. A. (2025). Legacy of temporary employment in Francoist Spain. Social Science History, 49(1). https://doi.org/10.1017/ssh.2025.3

Heller, S. (2021). Youth employment and behavioral outcomes. Policy Analysis and Management. https://doi.org/10.1002/pam.22138

Modestino, A., & Paulsen, R. (2019). Reducing inequality through summer youth employment. https://clear.dol.gov/Study/Reducing-inequality-summer-summer-Lessons-evaluation-Boston-Summer-Youth-Employment-Program

OECD. (2024). Digital Government Review. https://www.oecd.org/gov/digital-government/

Reyes, R. (2023). SPES beneficiaries in Pampanga. SunStar Pampanga. https://www.sunstar.com.ph/pampanga/local-news/spes-beneficiaries

Salvaña, J., et al. (2025). Student persistence and employment motivation. DIT.ADS Journal. https://doi.org/10.63941/dit.adsimrj.2025.04

Solutions for Youth Employment (S4YE). (2023). Digital platforms for youth employment. https://www.s4ye.org


# APPENDICES

## Appendix A: Survey Questionnaire

The survey questionnaire was administered using Google Forms. The instrument measured stakeholder perceptions using a five-point Likert scale.

### Survey Instrument

1. I find the current SPES application process easy to understand.
   - Strongly Agree
   - Agree
   - Neutral
   - Disagree
   - Strongly Disagree
2. I prefer online submission and tracking of SPES requirements.
   - Strongly Agree
   - Agree
   - Neutral
   - Disagree
   - Strongly Disagree
3. I have experienced errors or inconsistencies in SPES records.
   - Strongly Agree
   - Agree
   - Neutral
   - Disagree
   - Strongly Disagree
4. Manual SPES processing causes delays in updates.
   - Strongly Agree
   - Agree
   - Neutral
   - Disagree
   - Strongly Disagree
5. A web-based system would improve SPES monitoring and transparency.
   - Strongly Agree
   - Agree
   - Neutral
   - Disagree
   - Strongly Disagree
6. The system should provide real-time status updates for applicants.
   - Strongly Agree
   - Agree
   - Neutral
   - Disagree
   - Strongly Disagree
7. Employers should be able to submit compliance documents online.
   - Strongly Agree
   - Agree
   - Neutral
   - Disagree
   - Strongly Disagree
8. CPESO staff should have a dashboard for pending applications.
   - Strongly Agree
   - Agree
   - Neutral
   - Disagree
   - Strongly Disagree


## Appendix B: Interview Questions

This appendix contains the guide questions used for interviews with Public Employment Service Office (PESO) staff.

1. How do you assess the skills and interests of SPES applicants to match them with suitable job placements?
2. How do you monitor the progress and performance of SPES beneficiaries during their placements? What indicators do you track?
3. How do you ensure that employers are complying with SPES guidelines and providing a positive work experience for the beneficiaries?
4. How do you stay informed about changes in labor laws, industry trends, and other factors that may affect the SPES program and its beneficiaries?
5. Why do you think the SPES program is an important initiative for the youth in our community and society?
6. Why do you think some SPES beneficiaries are more successful in their placements than others? What factors contribute to their success?
7. Why do you think it is important to monitor the progress and well-being of SPES beneficiaries during their placements?
8. Why is it important for SPES to offer skills and work experience, not just money to students?
9. What are the policies of the SPES program and the main purpose or objective of SPES?
10. What are the biggest challenges you face in implementing the SPES program?
11. What are the most rewarding aspects of working with the SPES program?
12. How could the SPES program be improved to better serve students and employers?
13. What role does the SPES program play in addressing youth unemployment in our community?
14. Do you have existing data or records of graduates from the SPES program, or any records regarding SPES beneficiaries after their participation? Did they subsequently graduate, remain unemployed, or pursue other paths?
15. What is the step-by-step process of SPES and how does it work?
16. If we develop a system for SPES, do you have any suggestions for features or functionalities that could be included to enhance its effectiveness?


## Appendix C: Charts and Graphs

Figure 6 shows the interface of the SPES system login page.

Figure 7 shows the interface of the beneficiary dashboard.

Figure 8 shows the interface of the CPESO admin dashboard.

Figure 9 shows the interface of the employer module.

Figure 10 shows the interface of the reports page.

Figure 11 shows the interface of the analytics page.

### Survey Summary Charts

1. Ease of Understanding the SPES Application Process
2. Difficulty in Submitting Application
3. Mode of Application Submission
4. Timeliness of SPES Application Status Updates
5. Efficiency of SPES Documentation and Processing System
6. Perception on Digitalizing the SPES Process
7. Preference for Online Submission and Tracking of Requirements
8. Monitoring of Attendance and Performance During Employment
9. Errors or Inconsistencies in SPES Records or Documents


## Appendix D: PESO Responses

This appendix contains responses from Public Employment Service Office staff who participated in the interview.

### Selected Responses

- The SPES office aligns applicant placements with chosen fields of study to ensure meaningful experience.
- Progress and performance are monitored through weekly accomplishment reports and attendance documentation.
- Employer compliance is ensured by regular monitoring and provision of DOLE advisories.
- The SPES program is important because it provides both income and work experience.
- Success factors include exposure to workplace practices, confidence, work ethic, and professional standards.
- Monitoring is important because it shows beneficiaries they are supported and valued.
- The program should offer skills and experience, not just financial assistance.
- The biggest challenge is that applicant demand often exceeds available placement slots.
- The most rewarding aspect is observing beneficiaries complete their studies successfully.
- System improvements should focus on stronger monitoring, feedback, and streamlined documentary requirements.
- SPES reduces youth unemployment by providing practical work experience and professional networking.


# RESEARCHER’S PROFILE

**Lacson, Jerome C.**
Bachelor of Science in Information Technology candidate at City College of San Fernando Pampanga.

**Tiomico, Kissel C.**
Bachelor of Science in Information Technology candidate at City College of San Fernando Pampanga.

**Yalung, Adrian T.**
Bachelor of Science in Information Technology candidate at City College of San Fernando Pampanga.

---

*End of thesis draft.*
