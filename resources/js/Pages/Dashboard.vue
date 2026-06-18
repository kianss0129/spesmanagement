<template>
  <div class="flex min-h-screen bg-gradient-to-br from-blue-100 to-blue-200">




    <!-- SIDEBAR -->
    <Sidebar
      :is-open="isSidebarOpen"
      :selected-tab="selectedTab"
      :menu-items="menuItems"
      @toggle="toggleSidebar"
      @select="handleMenuClick"
      @logout="logout"
    />




    <!-- MAIN -->
    <main class="flex-1 overflow-auto">




      <!-- HEADER -->
      <DashboardHeader
        :profile-photo="profilePhoto"
        :menu-open="menuOpen"
        @toggle-menu="toggleMenu"
        @logout="logout"
      />




      <!-- CONTENT -->
      <div
        class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6"
      >




        <!-- EXAM SECTION -->
        <ExamSection
          :selected-tab="selectedTab"
          :sorted-applications="examSchedulingApplications"
          :applications="applications"
          :exams="exams"
          :exam-form="examForm"
          :min-date-time="minDateTime"
          :is-assigned-application="isAssignedApplication"
          :has-scheduled-exam="hasScheduledExam"
          :select-exam-applicant="selectExamApplicant"
          :get-selected-exam-applicant-name="getSelectedExamApplicantName"
          :clear-exam-selection="clearExamSelection"
          :schedule-exam="scheduleExam"
          :update-exam-result="updateExamResult"
          :format-date="formatDate"
          :batch-history="batchHistory"
          :select-batch="selectBatchCustom"
          :select-batch-custom="selectBatchCustom"
          :load-data="loadData"
        />




        <!-- CONTRACT SECTION -->
        <ContractSection
          :selected-tab="selectedTab"
          :sorted-applications="sortedApplications"
          :contracts="contracts"
          :contract-form="contractForm"
          :min-date-time="minDateTime"
          :scheduling-contract="schedulingContract"
          :is-assigned-application="isAssignedApplication"
          :select-applicant="selectApplicant"
          :get-selected-applicant-name="getSelectedApplicantName"
          :clear-applicant-selection="clearApplicantSelection"
          :schedule-contract="scheduleContract"
          :update-contract-result="updateContractResult"
          :format-date="formatDate"
        />

        <ScheduleSection
          :selected-tab="selectedTab"
          :exams="scheduleExams"
          :interviews="scheduleInterviews"
          :contracts="scheduleContracts"
          :users="interviewerUsers"
          :can-create-schedules="canCreateSchedules"
          :can-reschedule-schedules="canCreateSchedules"
          :can-manage-exams-contracts="canCreateSchedules"
          :can-complete-assigned-interviews="canCompleteAssignedInterviews"
          :reschedule-schedule="rescheduleSchedule"
          :update-exam-result="updateExamResult"
          :update-interview-result="updateInterviewResult"
          :update-contract-result="updateContractResult"
          :mark-contract-deployed="markContractDeployed"
          :mark-application-qualified="markApplicationQualified"
          :format-date="formatDate"
          @open-scheduler="openScheduler"
        />




        <!-- HOME -->
        <div
          v-if="selectedTab === 'home'"
          class="space-y-6"
        >
          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">
                  Today's SPES Operations
                </p>
                <h1 class="mt-2 text-3xl font-bold text-slate-900">
                  CPESO Dashboard
                </h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                  Review urgent applications, requirements, assignments, schedules, and announcements that need action today.
                </p>
              </div>

              <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                @click="loadData"
              >
                Refresh
              </button>
            </div>
          </section>

          <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <button
              v-for="card in priorityCards"
              :key="card.label"
              type="button"
              class="rounded-lg border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:border-blue-300 hover:shadow-md"
              @click="handleMenuClick(card.target)"
            >
              <p class="text-sm font-semibold text-slate-600">{{ card.label }}</p>
              <div class="mt-4 flex items-end justify-between gap-3">
                <span class="text-3xl font-bold text-slate-900">{{ card.value }}</span>
                <span
                  class="rounded-full px-3 py-1 text-xs font-semibold"
                  :class="card.value > 0 ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800'"
                >
                  {{ card.value > 0 ? 'Needs action' : 'Clear' }}
                </span>
              </div>
              <p class="mt-3 text-sm text-slate-500">{{ card.description }}</p>
            </button>
          </section>

          <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
            <div
              v-for="card in analyticsCards"
              :key="card.key"
              class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
            >
              <p class="text-sm font-semibold text-slate-600">{{ card.label }}</p>
              <p class="mt-3 text-3xl font-bold text-slate-900">{{ card.value }}</p>
              <p class="mt-2 text-xs text-slate-500">{{ card.description }}</p>
            </div>
          </section>

          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
              <div>
                <h2 class="text-lg font-bold text-slate-900">Automatic Insights</h2>
                <p class="mt-1 text-sm text-slate-500">Highlights based on current SPES records.</p>
              </div>
              <button
                type="button"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                @click="handleMenuClick('reports')"
              >
                Generate Reports
              </button>
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
              <div
                v-for="insight in reporting.insights"
                :key="insight.label"
                class="rounded-lg bg-slate-50 p-4"
              >
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ insight.label }}</p>
                <p class="mt-2 text-lg font-bold text-slate-900">{{ insight.value }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ insight.meta }}</p>
              </div>
              <p
                v-if="!reporting.insights?.length"
                class="rounded-lg bg-slate-50 p-4 text-sm text-slate-500"
              >
                Insights will appear when records are available.
              </p>
            </div>
          </section>

          <section class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
              <div class="border-b border-slate-200 p-5">
                <h2 class="text-lg font-bold text-slate-900">Today's Schedule</h2>
                <p class="mt-1 text-sm text-slate-500">Upcoming exams, interviews, and contract signings.</p>
              </div>

              <div class="grid gap-0 divide-y divide-slate-200 lg:grid-cols-3 lg:divide-x lg:divide-y-0">
                <div
                  v-for="group in scheduleGroups"
                  :key="group.label"
                  class="p-5"
                >
                  <div class="flex items-center justify-between gap-3">
                    <h3 class="font-semibold text-slate-900">{{ group.label }}</h3>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                      {{ group.items.length }}
                    </span>
                  </div>

                  <div class="mt-4 space-y-3">
                    <div
                      v-for="item in group.items"
                      :key="`${group.label}-${item.id}`"
                      class="rounded-lg bg-slate-50 p-3"
                    >
                      <p class="text-sm font-semibold text-slate-900">{{ item.title }}</p>
                      <p class="mt-1 text-xs text-slate-500">{{ formatDate(item.date) }}</p>
                    </div>

                    <p
                      v-if="group.items.length === 0"
                      class="rounded-lg bg-slate-50 p-4 text-sm text-slate-500"
                    >
                      No upcoming {{ group.label.toLowerCase() }}.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
              <h2 class="text-lg font-bold text-slate-900">Quick Actions</h2>
              <div class="mt-4 grid gap-3">
                <button
                  v-for="action in quickActionItems"
                  :key="action.label"
                  type="button"
                  class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3 text-left text-sm font-semibold text-slate-800 transition hover:border-blue-300 hover:bg-blue-50"
                  @click="handleMenuClick(action.target)"
                >
                  <span>{{ action.label }}</span>
                  <span class="text-blue-600">Open</span>
                </button>
              </div>
            </div>
          </section>

          <section class="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
              <div class="border-b border-slate-200 p-5">
                <h2 class="text-lg font-bold text-slate-900">Recent Activity</h2>
                <p class="mt-1 text-sm text-slate-500">Latest approvals, rejections, and assignments.</p>
              </div>

              <div class="divide-y divide-slate-200">
                <div
                  v-for="activity in recentTriageActivity"
                  :key="activity.id"
                  class="p-5"
                >
                  <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                      <p class="text-sm font-semibold text-slate-900">{{ activity.title }}</p>
                      <p class="mt-1 text-sm text-slate-600">{{ activity.description }}</p>
                    </div>
                    <span class="text-xs font-medium text-slate-500">{{ formatDate(activity.date) }}</span>
                  </div>
                </div>

                <p
                  v-if="recentTriageActivity.length === 0"
                  class="p-5 text-sm text-slate-500"
                >
                  No recent triage activity yet.
                </p>
              </div>
            </div>

            <div class="space-y-6">
              <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Urgent Announcements</h2>
                <div class="mt-4 space-y-3">
                  <div
                    v-for="announcement in urgentAnnouncements"
                    :key="announcement.id"
                    class="rounded-lg border border-amber-200 bg-amber-50 p-4"
                  >
                    <p class="text-sm font-semibold text-amber-950">{{ announcement.title }}</p>
                    <p class="mt-1 line-clamp-2 text-sm text-amber-900">
                      {{ announcement.content || announcement.message }}
                    </p>
                  </div>

                  <p
                    v-if="urgentAnnouncements.length === 0"
                    class="rounded-lg bg-slate-50 p-4 text-sm text-slate-500"
                  >
                    No urgent announcements posted.
                  </p>
                </div>
              </div>

              <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Analytics Summary</h2>
                <div class="mt-4 grid grid-cols-2 gap-3">
                  <div
                    v-for="item in analyticsSummary"
                    :key="item.label"
                    class="rounded-lg bg-slate-50 p-4"
                  >
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                      {{ item.label }}
                    </p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">{{ item.value }}</p>
                  </div>
                </div>
              </div>
            </div>
          </section>
        </div>




       <!-- BENEFICIARIES -->
<div
  v-if="selectedTab === 'beneficiaries'"
  class="space-y-6"
>




  <AdminWorkflow
    v-if="isAdmin || isPesoAdmin || isPesoUser"
    :stats="stats"
    :applications="applications"
    :beneficiaries="beneficiaries"
    :exams="exams"
    :interviews="interviews"
    :contracts="contracts"
    :daily-reports="workOutputs"
    :selected-days="selectedDays"
    :can-manage-roles="isAdmin"
    :can-approve="isPesoAdmin"
    :read-only="isPesoUser"
    @update:selectedDays="selectedDays = $event"
    @refresh="loadData"
  />




</div>




       <!-- BENEFICIARY MONITORING -->
<div
  v-if="selectedTab === 'beneficiaryMonitoring'"
  class="space-y-6"
>
<!-- BENEFICIARY MONITORING -->
<beneficiaryMonitoringSection
  :selected-tab="selectedTab"
  :beneficiaries="beneficiaries"
  :is-peso-user="isPesoUser"
  :status-class="statusClass"
  :view-beneficiary-applications="viewBeneficiaryApplications"
/>




  <!-- ATTENDANCE -->
          <AttendanceSection
            :selected-tab="selectedTab"
            :attendance-filters="attendanceFilters"
            :attendance-filter-options="attendanceFilterOptions"
            :attendance-summary="attendanceSummary"
            :filtered-attendance-records="filteredAttendanceRecords"
            :daily-reports="workOutputs"
            :completion-queue="completionReviewQueue"
            :approve-completion="approveCompletionFromQueue"
            :show-attendance-history-modal="showAttendanceHistoryModal"
            :average-ratings="averageRatings"
            :employer-reliability="employerReliability"
            :reset-attendance-filters="resetAttendanceFilters"
            :format-time="formatTime"
          />




</div>

          <AttendanceSection
            :selected-tab="selectedTab"
            :attendance-filters="attendanceFilters"
            :attendance-filter-options="attendanceFilterOptions"
            :attendance-summary="attendanceSummary"
            :filtered-attendance-records="filteredAttendanceRecords"
            :daily-reports="workOutputs"
            :completion-queue="completionReviewQueue"
            :approve-completion="approveCompletionFromQueue"
            :show-attendance-history-modal="showAttendanceHistoryModal"
            :average-ratings="averageRatings"
            :employer-reliability="employerReliability"
            :reset-attendance-filters="resetAttendanceFilters"
            :format-time="formatTime"
          />




        <!-- APPROVED BENEFICIARIES -->
       <ApprovedBeneficiariesSection
  :selected-tab="selectedTab"
  :approved-beneficiaries="approvedBeneficiaries"
  :is-admin-role="isAdminRole"
  :format-date="formatDate"
  @revert="openRevertModal"
/>




        <!-- APPROVED EMPLOYERS -->
      <ApprovedEmployersSection
  :selected-tab="selectedTab"
  :approved-employers="approvedEmployers"
  :is-admin-role="isAdminRole"
  :format-date="formatDate"
  @revert="openRevertModal"
  @manage-jobs="selectedTab = 'jobs'"
/>
      <!-- JOBS -->
<JobListingSection
  :selected-tab="selectedTab"
  :is-admin="isAdmin"
  :is-peso-admin="isPesoAdmin"
  :is-peso-user="isPesoUser"
  :job-listings="jobListings"
  :format-date="formatDate"
  :view-applications="viewApplications"
  :load-data="loadData"
/>




        <!-- APPLICATIONS -->
        <ApplicationsSection
          :selected-tab="selectedTab"
          :applications="applications"
          :format-date="formatDate"
          :update-application-status="updateApplicationStatus"
          :mark-application-qualified="markApplicationQualified"
        />




       <!-- INTERVIEW -->
<InterviewSection
  :selected-tab="selectedTab"
  :applications="interviewApplications"
  :interviews="interviews"
  :schedule-form="scheduleForm"
  :users="interviewerUsers"
  :min-date-time="minDateTime"
  :scheduling-interview="schedulingInterview"
  :is-valid-text="isValidText"
  :is-assigned-application="isAssignedApplication"
  :schedule-interview="scheduleInterview"
  :update-interview-result="updateInterviewResult"
  :format-date="formatDate"
/>




        <!-- REPORTS -->
        <ReportsSection
          :selected-tab="selectedTab"
          :reports="reports"
          :reporting="reporting"
          :filters="reportFilters"
          :loading="isLoadingData"
          :format-date="formatDate"
          :open-document="openDocument"
          @update:filters="updateReportFilters"
          @refresh="loadData"
        />








        <!-- USER MANAGEMENT -->
        <UserManagementSection
  v-if="selectedTab === 'userManagement'"
  :users="users"
  :roles="roles"
  @refresh="loadData"
/>

        <section v-if="selectedTab === 'auditTrail'" class="space-y-6">
          <header class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Settings</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">Audit Trail</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
              Review system activity by user, action, date, and module.
            </p>
          </header>

          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-3 lg:grid-cols-3">
              <input
                v-model="auditFilters.user"
                type="search"
                placeholder="Filter by user"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
              >
              <input
                v-model="auditFilters.action"
                type="search"
                placeholder="Filter by action"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
              >
              <input
                v-model="auditFilters.date"
                type="date"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
              >
            </div>
          </section>

          <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="hidden grid-cols-[0.9fr_1fr_0.9fr_0.8fr_1.4fr] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 xl:grid">
              <span>Date / Time</span>
              <span>User</span>
              <span>Action</span>
              <span>Module</span>
              <span>Details</span>
            </div>

            <div
              v-for="entry in paginatedAuditTrail"
              :key="entry.id || `${entry.action}-${entry.created_at}`"
              class="grid gap-4 border-b border-slate-200 px-5 py-5 text-sm text-slate-700 last:border-b-0 xl:grid-cols-[0.9fr_1fr_0.9fr_0.8fr_1.4fr] xl:items-center"
            >
              <span>{{ formatDate(entry.created_at || entry.date) }}</span>
              <span class="font-semibold text-slate-900">{{ entry.user_name || entry.user || 'System' }}</span>
              <span>{{ entry.action || 'Activity recorded' }}</span>
              <span>{{ entry.module || entry.record || entry.subject || 'System' }}</span>
              <span>{{ formatAuditDetails(entry) }}</span>
            </div>

            <div
              v-if="filteredAuditTrail.length > 0"
              class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between"
            >
              <span>
                Showing {{ ((auditPage - 1) * auditPerPage) + 1 }}-{{ Math.min(auditPage * auditPerPage, filteredAuditTrail.length) }} of {{ filteredAuditTrail.length }} records
              </span>
              <div class="flex gap-2">
                <button
                  type="button"
                  class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="auditPage === 1"
                  @click="auditPage = Math.max(1, auditPage - 1)"
                >
                  Previous
                </button>
                <button
                  type="button"
                  class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="auditPage === auditTotalPages"
                  @click="auditPage = Math.min(auditTotalPages, auditPage + 1)"
                >
                  Next
                </button>
              </div>
            </div>

            <div v-if="filteredAuditTrail.length === 0" class="px-5 py-12 text-center">
              <p class="text-sm font-semibold text-slate-700">No audit records found.</p>
              <p class="mt-1 text-sm text-slate-500">Try adjusting the user, action, or date filters.</p>
            </div>
          </section>
        </section>

        <section v-if="selectedTab === 'systemSettings'" class="space-y-6">
          <header class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Settings</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">System Settings</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
              Manage your profile, security, and account preferences.
            </p>
          </header>

          <section class="grid gap-6 xl:grid-cols-[16rem_1fr]">
            <aside class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
              <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">Settings Menu</p>
              <nav class="mt-5 grid gap-2">
                <button
                  type="button"
                  class="rounded-lg px-4 py-3 text-left text-sm font-semibold transition"
                  :class="settingsTab === 'profile' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-blue-50 hover:text-slate-900'"
                  @click="settingsTab = 'profile'"
                >
                  Profile Information
                </button>
                <button
                  type="button"
                  class="rounded-lg px-4 py-3 text-left text-sm font-semibold transition"
                  :class="settingsTab === 'password' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-blue-50 hover:text-slate-900'"
                  @click="settingsTab = 'password'"
                >
                  Update Password
                </button>
                <button
                  type="button"
                  class="rounded-lg px-4 py-3 text-left text-sm font-semibold transition"
                  :class="settingsTab === 'delete' ? 'bg-red-600 text-white shadow-sm' : 'text-red-600 hover:bg-red-50'"
                  @click="settingsTab = 'delete'"
                >
                  Delete Account
                </button>
              </nav>
            </aside>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
              <div v-if="settingsTab === 'profile'">
                <div class="mb-6">
                  <h2 class="text-xl font-bold text-slate-900">Profile Information</h2>
                  <p class="mt-1 text-sm text-slate-500">Update your personal account details.</p>
                </div>
                <UpdateProfileInformationForm :user="$page.props.auth.user" />
                <SectionBorder />
              </div>

              <div v-if="settingsTab === 'password'">
                <div class="mb-6">
                  <h2 class="text-xl font-bold text-slate-900">Update Password</h2>
                  <p class="mt-1 text-sm text-slate-500">Ensure your account stays secure.</p>
                </div>
                <ChangePassword />
              </div>

              <div v-if="settingsTab === 'delete'">
                <div class="mb-6">
                  <h2 class="text-xl font-bold text-red-600">Delete Account</h2>
                  <p class="mt-1 text-sm text-slate-500">Permanently remove your account and all associated data.</p>
                </div>
                <DeleteUserForm />
              </div>
            </div>
          </section>
        </section>




        <!-- ANNOUNCEMENTS -->
        <AnnouncementSection
          :selected-tab="selectedTab"
          :is-admin-role="isAdminRole"
          :is-peso-user="isPesoUser"
          :filtered-announcements="filteredAnnouncements"
          :new-announcement="newAnnouncement"
          :open-image="openImage"
          :format-date="formatDate"
          @create-announcement="createAnnouncement"
          @image-upload="handleImageUpload"
          @edit-announcement="openEditAnnouncement"
          @delete-announcement="deleteAnnouncement"
        />




      </div>
    </main>




  </div>




  <!-- EDIT ANNOUNCEMENT MODAL -->
  <div
    v-if="editingAnnouncement"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
    @click.self="closeEditAnnouncement"
  >
    <form class="w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl" @submit.prevent="updateAnnouncement">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="text-lg font-bold text-slate-900">Edit Announcement</h2>
          <p class="mt-1 text-sm text-slate-500">Update the title, message, audience, or image.</p>
        </div>
        <button type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="closeEditAnnouncement">
          Close
        </button>
      </div>

      <div class="mt-5 grid gap-4">
        <input
          v-model="editAnnouncementForm.title"
          type="text"
          required
          placeholder="Announcement title"
          class="rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
        >

        <textarea
          v-model="editAnnouncementForm.message"
          rows="5"
          required
          placeholder="Write announcement details..."
          class="rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
        />

        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="text-sm font-semibold text-slate-700">Audience</label>
            <select
              v-model="editAnnouncementForm.targetRole"
              class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
            >
              <option value="beneficiary">Beneficiaries</option>
              <option value="employer">Employers</option>
              <option value="all">All</option>
            </select>
          </div>

          <div>
            <label class="text-sm font-semibold text-slate-700">Replace image</label>
            <input
              type="file"
              accept="image/*"
              class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm"
              @change="handleEditAnnouncementImage"
            >
          </div>
        </div>
      </div>

      <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="closeEditAnnouncement">
          Cancel
        </button>
        <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">
          Save Changes
        </button>
      </div>
    </form>
  </div>




  <!-- DELETE ANNOUNCEMENT MODAL -->
  <div
    v-if="announcementToDelete"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
    @click.self="closeDeleteAnnouncement"
  >
    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-red-600">Confirm Delete</p>
        <h2 class="mt-2 text-lg font-bold text-slate-900">Delete Announcement</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">
          Are you sure you want to delete this announcement? This action cannot be undone.
        </p>
      </div>

      <div class="mt-5 rounded-lg border border-red-100 bg-red-50 p-4">
        <p class="text-sm font-semibold text-red-950">{{ announcementToDelete.title }}</p>
        <p class="mt-1 line-clamp-3 text-sm text-red-800">
          {{ announcementToDelete.message || announcementToDelete.content }}
        </p>
      </div>

      <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <button
          type="button"
          class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-60"
          :disabled="deletingAnnouncement"
          @click="closeDeleteAnnouncement"
        >
          Cancel
        </button>
        <button
          type="button"
          class="rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60"
          :disabled="deletingAnnouncement"
          @click="confirmDeleteAnnouncement"
        >
          {{ deletingAnnouncement ? 'Deleting...' : 'Delete Announcement' }}
        </button>
      </div>
    </div>
  </div>



  <!-- IMAGE MODAL -->
  <div
    v-if="selectedImage"
    class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50"
    @click="closeImage"
  >




    <img
      :src="selectedImage"
      class="max-w-xl max-h-[60vh] rounded shadow-lg object-contain"
      style="width: auto; height: auto;"
    />




  </div>




  <!-- DOCUMENT MODAL -->
  <div
    v-if="selectedDocument"
    class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 px-4 py-6"
    @click.self="closeDocument"
  >




    <div
      class="w-full max-w-5xl h-[90vh] bg-white rounded-lg overflow-hidden shadow-2xl flex flex-col"
    >




      <div class="flex items-center justify-between px-5 py-4 border-b">




        <div>
          <h3 class="text-lg font-semibold text-gray-900">
            {{ selectedDocumentName }}
          </h3>




          <p class="text-xs text-gray-500 break-all">
            {{ selectedDocument }}
          </p>
        </div>




        <button
          @click="closeDocument"
          class="text-gray-500 hover:text-gray-900"
        >
          Close
        </button>




      </div>




      <div class="flex-1 bg-slate-100">




        <iframe
          v-if="isPreviewableDocument(selectedDocument)"
          :src="selectedDocument"
          class="w-full h-full"
          frameborder="0"
        ></iframe>




        <div
          v-else
          class="flex h-full flex-col items-center justify-center gap-3 p-6 text-center"
        >




          <p class="text-gray-700">
            Preview is not available for this document type.
          </p>




          <a
            :href="selectedDocument"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center justify-center rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
          >
            Open in new tab
          </a>




        </div>
      </div>
    </div>
  </div>




  <!-- REVERT MODAL -->
 <ConfirmationModal
  v-if="showRevertModal"
  :show="showRevertModal"
  @close="showRevertModal = false"
>
    <template #title>
      Confirm Revert
    </template>




    <template #content>
      Are you sure you want to revert this
      {{ revertPayload.name }}
      to Pending?
      This action cannot be undone.
    </template>




    <template #footer>




      <button
        type="button"
        @click="showRevertModal = false"
        class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 mr-2"
      >
        Cancel
      </button>




      <button
        type="button"
        @click="confirmRevertToPending"
        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
      >
        Yes, revert
      </button>




    </template>




  </ConfirmationModal>




  <!-- TOAST -->
  <div
    v-if="toast.show"
    :class="[
      'fixed bottom-6 right-6 text-white px-4 py-2 rounded shadow-lg z-50 animate-fade-in',
      toast.color === 'red' ? 'bg-red-600' : 'bg-green-600'
    ]"
  >
    {{ toast.message }}
  </div>




</template>








<script setup>




// =====================================================
// VUE IMPORTS
// =====================================================
import {
  ref,
  computed,
  onMounted,
  onBeforeUnmount,
  watch
} from 'vue'








// =====================================================
// INERTIA IMPORTS
// =====================================================
import { usePage } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'








// =====================================================
// SERVICES
// =====================================================
import axios from 'axios'
import { route } from 'ziggy-js'








// =====================================================
// DASHBOARD COMPONENTS
// =====================================================
import StatsSection from '@/Components/Dashboard/StatsSection.vue'
import ChartsSection from '@/Components/Dashboard/ChartsSection.vue'
import AttendanceSection from '@/Components/Dashboard/AttendanceSection.vue'
import InterviewSection from '@/Components/Dashboard/InterviewSection.vue'
import ExamSection from '@/Components/Dashboard/ExamSection.vue'
import ContractSection from '@/Components/Dashboard/ContractSection.vue'
import ScheduleSection from '@/Components/Dashboard/ScheduleSection.vue'
import ReportsSection from '@/Components/Dashboard/ReportsSection.vue'
import AnnouncementSection from '@/Components/Dashboard/AnnouncementSection.vue'
import ApplicationsSection from '@/Components/Dashboard/ApplicationsSection.vue'
import DashboardHeader from '@/Components/Dashboard/DashboardHeader.vue'
import BeneficiaryMonitoringSection from '@/Components/Dashboard/BeneficiaryMonitoringSection.vue'
import JobListingSection from '@/Components/Dashboard/JobListingSection.vue'
import UserManagementSection from '@/Components/Dashboard/UserManagementSection.vue'
import ApprovedBeneficiariesSection from '@/Components/Dashboard/ApprovedBeneficiariesSection.vue'
import ApprovedEmployersSection from '@/Components/Dashboard/ApprovedEmployersSection.vue'












// =====================================================
// GLOBAL COMPONENTS
// =====================================================
import Sidebar from '@/Components/Sidebar.vue'
import QuickActions from '@/Components/QuickActions.vue'
import AdminWorkflow from '@/Components/AdminWorkflow.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue'
import ChangePassword from '@/Pages/Profile/Partials/ChangePassword.vue'
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue'
import SectionBorder from '@/Components/SectionBorder.vue'








// =====================================================
// COMPOSABLES
// =====================================================
import { useSidebarMenu } from '@/composables/useSidebarMenu'








// =====================================================
// PAGE + PROPS
// =====================================================
const page = usePage()




const props = defineProps({
  user: Object,
  stats: Object,
  applicants: Object,
  employers: Object,
  beneficiaries: Array,
  interviews: Array,
  jobListings: Array,
  announcements: Array,
  approvedBeneficiaries: Array,
  approvedEmployers: Array
})








// =====================================================
// UI STATE
// =====================================================
const isSidebarOpen = ref(true)
const selectedTab = ref('home')
const settingsTab = ref('profile')
const menuOpen = ref(false)
const profileMenu = ref(null)
const menuButton = ref(null)








// =====================================================
// MODAL STATE
// =====================================================
const selectedImage = ref(null)
const selectedDocument = ref(null)
const selectedDocumentName = ref('')
const showRevertModal = ref(false)
const showAttendanceHistoryModal = ref(false)








// =====================================================
// FORM STATE
// =====================================================
const search = ref('')




const scheduleForm = ref({
  application_id: '',
  application_ids: [],
  batch_title: '',
  batch_size: null,
  date: '',
  start_time: '',
  end_time: '',
  interviewer_id: '',
  meet_link: '',
  instructions: '',
  notify_beneficiaries: true
})




const examForm = ref({
  batch_name: '',
  batch_title: '',
  batch_size: 1,
  application_ids: [],
  exam_title: '',
  date: '',
  start_time: '',
  end_time: '',
  exam_date: '',
  location: '',
  instructions: '',
  notify_beneficiaries: true
})




const contractForm = ref({
  application_id: '',
  application_ids: [],
  batch_title: '',
  date: '',
  start_time: '',
  end_time: '',
  contract_date: '',
  location: '',
  instructions: '',
  notify_beneficiaries: true
})




const newAnnouncement = ref({
  title: '',
  message: '',
  image: null,
  targetRole: 'all'
})

const editingAnnouncement = ref(null)
const announcementToDelete = ref(null)
const deletingAnnouncement = ref(false)
const editAnnouncementForm = ref({
  title: '',
  message: '',
  image: null,
  targetRole: 'all'
})








// =====================================================
// TOAST STATE
// =====================================================
const toast = ref({
  show: false,
  message: '',
  color: 'green'
})








// =====================================================
// DATA STATE
// =====================================================
const reports = ref([])
const reporting = ref({
  summary: {},
  charts: {},
  reports: {},
  insights: [],
  filters: {
    employers: [],
    schools: [],
    categories: [],
    statuses: [],
    batches: [],
  },
})
const reportFilters = ref({
  start_date: '',
  end_date: '',
  employer_id: '',
  school_id: '',
  category: '',
  status: '',
  batch_id: '',
})
const workOutputs = ref([])
const exams = ref([])
const batchHistory = ref([])
const contracts = ref([])
const applications = ref([])
const interviewApplications = ref([])
const interviews = ref(props.interviews || [])
const beneficiaries = ref(props.beneficiaries || [])
const jobListings = ref(props.jobListings || [])
const announcements = ref(props.announcements || [])
const approvedBeneficiaries = ref(props.approvedBeneficiaries || [])
const approvedEmployers = ref(props.approvedEmployers || [])
const users = ref([])
const interviewerUsers = ref([])
const roles = ref([])
const auditTrail = ref([])
const auditFilters = ref({
  user: '',
  action: '',
  date: '',
})
const auditPage = ref(1)
const auditPerPage = 10
const filteredAnnouncements = computed(() => announcements.value)
const filteredAuditTrail = computed(() => {
  return auditTrail.value.filter((entry) => {
    const user = String(entry.user_name || entry.user || 'System').toLowerCase()
    const action = String(entry.action || entry.description || '').toLowerCase()
    const date = String(entry.created_at || entry.date || '').slice(0, 10)

    const matchesUser = !auditFilters.value.user || user.includes(auditFilters.value.user.toLowerCase())
    const matchesAction = !auditFilters.value.action || action.includes(auditFilters.value.action.toLowerCase())
    const matchesDate = !auditFilters.value.date || date === auditFilters.value.date

    return matchesUser && matchesAction && matchesDate
  })
})
const auditTotalPages = computed(() => Math.max(1, Math.ceil(filteredAuditTrail.value.length / auditPerPage)))
const paginatedAuditTrail = computed(() => {
  const start = (auditPage.value - 1) * auditPerPage
  return filteredAuditTrail.value.slice(start, start + auditPerPage)
})

watch(auditFilters, () => {
  auditPage.value = 1
}, { deep: true })

watch(filteredAuditTrail, () => {
  if (auditPage.value > auditTotalPages.value) {
    auditPage.value = auditTotalPages.value
  }
})




const selectedExamApplicantId = ref('')




const sortedApplications = computed(() => {
  return [...applications.value].sort((a, b) => {
    const aDate = a.created_at ? new Date(a.created_at) : null
    const bDate = b.created_at ? new Date(b.created_at) : null




    if (aDate && bDate) {
      return bDate - aDate
    }




    return (b.id || 0) - (a.id || 0)
  })
})




const minDateTime = computed(() => {
  const now = new Date()
  now.setMinutes(now.getMinutes() + 30)
  return now.toISOString().slice(0, 16)
})




function isAssignedApplication(application) {
  const status = String(application?.status || '').toLowerCase()

  return Boolean(
    application?.assigned_employer ||
    status === 'assigned' ||
    status === 'for_contract' ||
    status === 'contract' ||
    status === 'hired' ||
    status === 'completed' ||
    status === 'rejected'
  )
}




function selectExamApplicant(application) {
  if (!examForm.value.application_ids.includes(application.id)) {
    examForm.value.application_ids.push(application.id)
    examForm.value.batch_size = examForm.value.application_ids.length
  }
}




function getSelectedExamApplicantName() {
  if (!examForm.value.application_ids || examForm.value.application_ids.length === 0) {
    return 'No beneficiaries selected'
  }
  return `${examForm.value.application_ids.length} beneficiaries selected`
}




function clearExamSelection() {
  examForm.value.application_ids = []
  examForm.value.batch_size = 1
  selectedExamApplicantId.value = ''
}




function hasScheduledExam(applicationId) {
  return exams.value.some(exam => exam.application_id === applicationId)
}

const examSchedulingApplications = computed(() => {
  const finalExamResults = ['passed', 'failed']
  const hiddenApplicationStatuses = [
    'passed',
    'failed',
    'exam_passed',
    'exam_failed',
    'qualified',
    'approved',
    'assigned',
    'for_contract',
    'contract_signed',
    'deployed',
    'ongoing',
    'completion_review',
    'completed',
    'rejected',
  ]

  return sortedApplications.value.filter((application) => {
    const status = normalizedStatus(application.status)
    const exam = exams.value.find((item) => Number(item.application_id) === Number(application.id))
    const examResult = normalizedStatus(application.exam_result || application.exam?.result || exam?.result)

    if (status !== 'for_exam') return false
    if (hiddenApplicationStatuses.includes(status)) return false
    if (finalExamResults.includes(examResult)) return false
    if (hasScheduledExam(application.id)) return false

    return true
  })
})




function selectBatchCustom() {
  if (!examForm.value.batch_name) {
    showToast('Please enter a batch name')
    return
  }
  if (examForm.value.batch_size < 1) {
    showToast('Batch size must be at least 1')
    return
  }
  // Get unassigned and unscheduled applications
  const availableApps = sortedApplications.value.filter(
    app => !isAssignedApplication(app) && !hasScheduledExam(app.id)
  )
  
  const selectedCount = Math.min(examForm.value.batch_size, availableApps.length)
  examForm.value.application_ids = availableApps.slice(0, selectedCount).map(app => app.id)
  showToast(`Selected ${selectedCount} beneficiaries for batch`)
}




function isValidText(text) {
  return typeof text === 'string' && text.trim().length > 0
}




function selectApplicant(applicationId) {
  if (!contractForm.value.application_ids) {
    contractForm.value.application_ids = []
  }

  const exists = contractForm.value.application_ids.some(
    (id) => Number(id) === Number(applicationId)
  )

  if (exists) {
    contractForm.value.application_ids = contractForm.value.application_ids.filter(
      (id) => Number(id) !== Number(applicationId)
    )
  } else {
    contractForm.value.application_ids.push(applicationId)
  }

  contractForm.value.application_id = contractForm.value.application_ids[0] || ''
}




function getSelectedApplicantName() {
  const selectedIds = contractForm.value.application_ids || []

  if (selectedIds.length > 1) {
    return `${selectedIds.length} beneficiaries selected`
  }

  const applicationId = selectedIds[0] || contractForm.value.application_id
  const application = applications.value.find(
    (app) => String(app.id) === String(applicationId)
  )




  return application?.beneficiary_name || application?.name || `Applicant #${applicationId}`
}




function clearApplicantSelection() {
  contractForm.value.application_id = ''
  contractForm.value.application_ids = []
}




async function updateContractResult(contractId, result) {
  const payload = {
    result: result || 'signed',
  }

  try {
    await axios.post(`/peso/contracts/${contractId}/result`, payload)
    showToast('Contract result updated successfully!')
    await loadData()
  } catch (error) {
    console.error('Contract result update error:', error.response?.data || error.message)
    showToast(scheduleResultErrorMessage(error, 'Failed to update contract result'))
  }
}

async function markContractDeployed(applicationId) {
  if (!applicationId) {
    showToast('Application identifier missing.')
    return
  }

  try {
    await axios.post(`/peso/applications/${applicationId}/deploy`)
    showToast('Application marked as deployed.')
    await loadData()
  } catch (error) {
    console.error('Deployment update error:', error.response?.data || error.message)
    showToast(error.response?.data?.message || 'Failed to mark application as deployed')
  }
}

async function markApplicationQualified(application) {
  if (!application?.id || application.is_real_application === false) {
    showToast('A valid application is required.')
    return
  }

  try {
    await axios.post(`/peso/applications/${application.id}/qualify`)
    showToast('Application marked as qualified.')
    await loadData()
  } catch (error) {
    console.error('Qualification update error:', error.response?.data || error.message)
    showToast(error.response?.data?.message || 'Failed to mark application as qualified')
  }
}

async function updateApplicationStatus(applicationId, status) {
  const application = applications.value.find((item) => String(item.id) === String(applicationId))

  if (!application || application.is_real_application === false) {
    showToast('A valid application is required.')
    return
  }

  const endpoint = status === 'approved'
    ? `/peso/beneficiaries/${application.beneficiary_id}/approve`
    : `/peso/beneficiaries/${application.beneficiary_id}/reject`

  const payload = status === 'rejected'
    ? { rejection_reason: 'Rejected by CPESO/Admin from applications management.' }
    : {}

  try {
    await axios.post(endpoint, payload)
    showToast(status === 'approved' ? 'Beneficiary approved.' : 'Beneficiary rejected.')
    await loadData()
  } catch (error) {
    console.error('Application status update error:', error.response?.data || error.message)
    showToast(error.response?.data?.message || 'Failed to update application status')
  }
}




function statusClass(status) {
  const normalized = String(status || '').toLowerCase()




  if (normalized.includes('approved')) {
    return 'bg-green-100 text-green-700'
  }




  if (normalized.includes('pending')) {
    return 'bg-yellow-100 text-yellow-700'
  }




  if (normalized.includes('rejected') || normalized.includes('failed')) {
    return 'bg-red-100 text-red-700'
  }




  if (normalized.includes('no application')) {
    return 'bg-blue-100 text-blue-700'
  }




  return 'bg-gray-100 text-gray-700'
}




function viewBeneficiaryApplications(beneficiaryId) {
  if (!beneficiaryId) {
    showToast('Beneficiary identifier missing.')
    return
  }




  Inertia.visit(`/peso/beneficiaries/${beneficiaryId}/applications`)
}




function viewContractApplication(contract) {
  if (!contract || !contract.application_id) {
    showToast('Invalid contract selected.')
    return
  }








  const application = applications.value.find(a => String(a.id) === String(contract.application_id))




  if (application && application.beneficiary_id) {
    Inertia.visit(`/peso/beneficiaries/${application.beneficiary_id}/applications`)
    return
  }




  showToast('Unable to locate beneficiary for this contract')
}




async function updateExamResult(examId, result) {
  const payload = {
    result: result || 'passed',
  }

  try {
    await axios.post(`/peso/exams/${examId}/result`, payload)
    showToast('Exam result updated successfully!')
    await loadData()
  } catch (error) {
    console.error('Exam result update error:', error.response?.data || error.message)
    showToast(scheduleResultErrorMessage(error, 'Failed to update exam result'))
  }
}

function scheduleResultErrorMessage(error, fallback) {
  const data = error.response?.data
  const validationErrors = data?.errors || data?.error

  if (typeof data?.message === 'string') return data.message
  if (typeof validationErrors === 'string') return validationErrors

  if (validationErrors && typeof validationErrors === 'object') {
    return Object.values(validationErrors)
      .flat()
      .filter(Boolean)
      .join('\n') || fallback
  }

  return fallback
}




async function updateInterviewResult(interviewId, result) {
  try {
    await axios.post(`/peso/interviews/${interviewId}/result`, { result })
    showToast('Interview result updated successfully!')
    await loadData()
  } catch (error) {
    console.error('Interview result update error:', error.response?.data || error.message)

    const message = error.response?.data?.message || 'Failed to update interview result'
    showToast(message)
  }
}








// =====================================================
// DASHBOARD STATE
// =====================================================
const stats = ref(props.stats || {})
const applicants = ref(props.applicants || {})
const employers = ref(props.employers || {})




const performance = ref({
  labels: [],
  series: []
})




const attendance = ref({
  labels: [],
  data: []
})




const completion = ref({
  labels: [],
  data: []
})








// =====================================================
// ATTENDANCE STATE
// =====================================================
const attendanceRecords = ref([])




const attendanceSummary = ref({
  beneficiariesMonitored: 0,
  records: 0,
  avgPresenceDays: 0
})




const attendanceFilters = ref({
  school: '',
  employer: '',
  status: ''
})




const attendanceFilterOptions = ref({
  schools: [],
  employers: [],
  statuses: []
})




const filteredAttendanceRecords = computed(() => {
  return attendanceRecords.value.filter((record) => {
    const schoolMatch = !attendanceFilters.value.school || record.school === attendanceFilters.value.school
    const employerMatch = !attendanceFilters.value.employer || record.employer_name === attendanceFilters.value.employer
    const statusMatch = !attendanceFilters.value.status || record.status === attendanceFilters.value.status




    return schoolMatch && employerMatch && statusMatch
  })
})




function resetAttendanceFilters() {
  attendanceFilters.value = {
    school: '',
    employer: '',
    status: ''
  }
}








// =====================================================
// LOADING STATE
// =====================================================
const isLoadingData = ref(false)
const dashboardError = ref('')
const schedulingInterview = ref(false)
const schedulingContract = ref(false)








// =====================================================
// CHART FILTERS
// =====================================================
const selectedDays = ref(7)
const dateFilter = ref('last_7_days')




const customRange = ref({
  start: '',
  end: ''
})




const chartStats = ref({
  chart_dates: [],
  users_growth: [],
  applications_by_peso: []
})




const chartKey = ref(0)








// =====================================================
// REVERT STATE
// =====================================================
const revertPayload = ref({
  type: '',
  id: null,
  name: ''
})








// =====================================================
// ROLE COMPUTED
// =====================================================
const userRoles = computed(() => props.user?.roles || [])

const userRoleNames = computed(() =>
  userRoles.value
    .map((role) => (typeof role === 'string' ? role : role?.name))
    .filter(Boolean)
)




const isAdmin = computed(() =>
  userRoleNames.value.some(role =>
    ['Admin', 'Super Admin', 'PESO Admin'].includes(role)
  )
)




const isPesoAdmin = computed(() =>
  userRoleNames.value.some(role => role === 'PESO Admin')
)




const isPesoUser = computed(() =>
  userRoleNames.value.some(role => role === 'PESO')
)




const isAdminRole = computed(() =>
  isAdmin.value || isPesoAdmin.value
)

const canCreateSchedules = computed(() => isAdminRole.value)

const canCompleteAssignedInterviews = computed(() =>
  isAdminRole.value || isPesoUser.value
)

const currentUserId = computed(() => props.user?.id || page.props.auth?.user?.id || null)

const assignedInterviews = computed(() => {
  if (!currentUserId.value) return []

  return interviews.value.filter((interview) =>
    Number(interview.interviewer_id) === Number(currentUserId.value) ||
    Number(interview.interviewer?.id) === Number(currentUserId.value)
  )
})

const scheduleExams = computed(() => (isAdminRole.value ? exams.value : []))
const scheduleContracts = computed(() => (isAdminRole.value ? contracts.value : []))
const scheduleInterviews = computed(() => (isAdminRole.value ? interviews.value : assignedInterviews.value))




const canViewDashboard = computed(() =>
  isAdmin.value || isPesoAdmin.value || isPesoUser.value
)


function normalizedStatus(value) {
  return String(value || '').toLowerCase().replace(/\s+/g, '_')
}

async function rescheduleSchedule(row, payload) {
  const endpointMap = {
    Interview: `/peso/interviews/${row.id}/reschedule`,
    Exam: `/peso/exams/${row.id}/reschedule`,
    Contract: `/peso/contracts/${row.id}/reschedule`,
  }

  const endpoint = endpointMap[row.type]

  if (!endpoint) {
    showToast('Unsupported schedule type')
    return
  }

  try {
    await axios.patch(endpoint, payload)
    showToast(`${row.type} rescheduled successfully!`)
    await loadData()
  } catch (error) {
    console.error('Reschedule error:', error.response?.data || error.message)
    const response = error.response?.data
    const message = response?.message ||
      (response?.errors ? Object.values(response.errors).flat().join(' ') : 'Failed to reschedule')
    showToast(message)
    throw error
  }
}


function recordDate(record, fields = ['date', 'schedule_date', 'start', 'created_at']) {
  for (const field of fields) {
    if (record?.[field]) {
      return record[field]
    }
  }

  return null
}


function hasIncompleteDocuments(beneficiary) {
  const documents = beneficiary?.documents
  if (!documents) return true

  if (Array.isArray(documents)) {
    return documents.length === 0 || documents.some((document) => {
      const status = normalizedStatus(document?.status)
      return ['missing', 'pending', 'rejected', 'needs_correction'].includes(status)
    })
  }

  if (typeof documents === 'object') {
    const values = Object.values(documents)
    return values.length === 0 || values.some((document) => {
      if (!document) return true
      if (typeof document === 'string') return false
      const status = normalizedStatus(document?.status)
      return ['missing', 'pending', 'rejected', 'needs_correction'].includes(status)
    })
  }

  return false
}


const pendingApplications = computed(() =>
  applications.value.filter((application) => {
    const status = normalizedStatus(application.status || application.approval_status)
    return ['pending', 'pending_review', 'submitted', 'for_review', 'needs_review'].includes(status)
  })
)


const incompleteRequirements = computed(() =>
  beneficiaries.value.filter((beneficiary) =>
    hasIncompleteDocuments(beneficiary) ||
    normalizedStatus(beneficiary.approval_status) === 'needs_correction' ||
    normalizedStatus(beneficiary.status) === 'needs_correction'
  )
)


const awaitingAssignment = computed(() =>
  beneficiaries.value.filter((beneficiary) => {
    const status = normalizedStatus(beneficiary.status || beneficiary.approval_status)
    return ['approved', 'qualified'].includes(status) && !beneficiary.employer_id && !beneficiary.job_listing_id
  })
)


const completionReviewQueue = computed(() =>
  beneficiaries.value
    .filter((beneficiary) => normalizedStatus(beneficiary.application_status || beneficiary.status) === 'completion_review')
    .map((beneficiary) => {
      const readiness = beneficiary.completion_readiness || {}
      const checks = [
        readiness.has_dtr,
        readiness.has_approved_daily_reports,
        readiness.has_employer_rating,
        readiness.has_certificate,
      ]

      return {
        id: beneficiary.id,
        application_id: beneficiary.application_id,
        beneficiary_name: beneficiary.name || beneficiary.beneficiary_name || 'Unknown beneficiary',
        employer_name: beneficiary.assigned_employer || beneficiary.employer_name || 'Unassigned',
        job_title: beneficiary.job_title || 'SPES placement',
        has_dtr: Boolean(readiness.has_dtr),
        has_approved_daily_reports: Boolean(readiness.has_approved_daily_reports),
        has_employer_rating: Boolean(readiness.has_employer_rating),
        has_certificate: Boolean(readiness.has_certificate),
        readiness_score: checks.filter(Boolean).length,
      }
    })
)


const pendingEmployers = computed(() => {
  const pendingFromStats = Number(stats.value?.pending_employers || stats.value?.pendingEmployers || 0)
  if (pendingFromStats) return Array.from({ length: pendingFromStats }, (_, index) => ({ id: `stat-${index}` }))

  return approvedEmployers.value.filter((employer) => {
    const status = normalizedStatus(employer.approval_status || employer.status)
    return status === 'pending' || employer.approved === false
  })
})


const priorityCards = computed(() => [
  {
    label: 'Pending Applications',
    value: pendingApplications.value.length || Number(stats.value?.pending_applications || 0),
    description: 'Applicants waiting for CPESO review.',
    target: 'beneficiaries',
  },
  {
    label: 'Incomplete Requirements',
    value: incompleteRequirements.value.length,
    description: 'Applicants with missing or correction-needed documents.',
    target: 'beneficiaryMonitoring',
  },
  {
    label: 'Awaiting Assignment',
    value: awaitingAssignment.value.length,
    description: 'Approved beneficiaries not yet placed with an employer.',
    target: 'assignment',
  },
  {
    label: 'Pending Employers',
    value: pendingEmployers.value.length,
    description: 'Employer accounts waiting for approval.',
    target: 'approvedEmployers',
  },
])


function scheduleItemTitle(item, fallback) {
  return item?.beneficiary_name ||
    item?.applicant_name ||
    item?.name ||
    item?.title ||
    item?.beneficiary?.user?.name ||
    fallback
}


const scheduleGroups = computed(() => [
  {
    label: 'Exams',
    items: exams.value.slice(0, 4).map((exam) => ({
      id: exam.id,
      title: scheduleItemTitle(exam, 'Exam schedule'),
      date: recordDate(exam, ['exam_date', 'date', 'start', 'created_at']),
    })),
  },
  {
    label: 'Interviews',
    items: interviews.value.slice(0, 4).map((interview) => ({
      id: interview.id,
      title: scheduleItemTitle(interview, 'Interview schedule'),
      date: recordDate(interview, ['scheduled_at', 'start', 'interview_date', 'date', 'created_at']),
    })),
  },
  {
    label: 'Contracts',
    items: contracts.value.slice(0, 4).map((contract) => ({
      id: contract.id,
      title: scheduleItemTitle(contract, 'Contract signing'),
      date: recordDate(contract, ['contract_date', 'date', 'start', 'created_at']),
    })),
  },
])


const quickActionItems = computed(() => [
  { label: 'Review Applications', target: 'beneficiaries' },
  { label: 'Assign Beneficiaries', target: 'assignment' },
  { label: 'Post Announcement', target: 'announcements' },
  { label: 'Generate Report', target: 'reports' },
])

const analyticsCardDefinitions = [
  ['total_applicants', 'Total Applicants', 'Applications received by CPESO.'],
  ['approved_beneficiaries', 'Total Approved Beneficiaries', 'Beneficiaries approved for SPES.'],
  ['students', 'Total Students', 'Approved and applicant records tagged as student.'],
  ['osy', 'Total OSY', 'Out-of-school youth records.'],
  ['ddw', 'Total DDW', 'Dependents of displaced workers.'],
  ['participating_employers', 'Total Participating Employers', 'Approved or active employer partners.'],
  ['schools_represented', 'Total Schools Represented', 'Schools found in beneficiary records.'],
  ['ongoing_beneficiaries', 'Ongoing Beneficiaries', 'Approved beneficiaries still active.'],
  ['completed_beneficiaries', 'Completed Beneficiaries', 'Beneficiaries marked completed.'],
  ['rejected_applicants', 'Rejected Applicants', 'Applications rejected by CPESO.'],
  ['pending_applications', 'Pending Applications', 'Applications still awaiting action.'],
  ['dtr_submitted', 'Total DTR Submitted', 'Attendance/DTR records submitted.'],
  ['daily_reports_submitted', 'Total Daily Reports Submitted', 'Daily accomplishment reports submitted.'],
]

const analyticsCards = computed(() =>
  analyticsCardDefinitions.map(([key, label, description]) => ({
    key,
    label,
    description,
    value: Number(reporting.value.summary?.[key] ?? 0).toLocaleString(),
  }))
)

function updateReportFilters(nextFilters) {
  reportFilters.value = {
    ...reportFilters.value,
    ...(nextFilters || {}),
  }
  loadData()
}


const recentTriageActivity = computed(() => {
  return applications.value
    .filter((application) => ['approved', 'rejected', 'assigned'].includes(normalizedStatus(application.status)))
    .sort((a, b) => new Date(b.updated_at || b.created_at || 0) - new Date(a.updated_at || a.created_at || 0))
    .slice(0, 6)
    .map((application) => {
      const status = normalizedStatus(application.status)
      const name = application.beneficiary_name || application.name || `Application #${application.id}`
      const title = status === 'approved'
        ? 'Recent approval'
        : status === 'rejected'
          ? 'Recent rejection'
          : 'Recent assignment'

      return {
        id: application.id,
        title,
        description: `${name} was marked ${String(application.status || '').replace(/_/g, ' ')}.`,
        date: application.updated_at || application.created_at,
      }
    })
})


const urgentAnnouncements = computed(() =>
  announcements.value
    .filter((announcement) => {
      const text = `${announcement.title || ''} ${announcement.content || announcement.message || ''}`.toLowerCase()
      return ['urgent', 'important', 'deadline', 'today', 'reminder'].some((keyword) => text.includes(keyword))
    })
    .slice(0, 3)
)


const analyticsSummary = computed(() => {
  const totalBeneficiaries = Number(stats.value?.totalBeneficiaries || beneficiaries.value.length || 0)
  const activeBeneficiaries = beneficiaries.value.filter((beneficiary) => {
    const status = normalizedStatus(beneficiary.status || beneficiary.approval_status)
    return ['approved', 'active', 'assigned', 'ongoing'].includes(status)
  }).length
  const approvedEmployerCount = Number(stats.value?.approvedEmployers || approvedEmployers.value.length || 0)
  const completionRate = totalBeneficiaries
    ? Math.round((approvedBeneficiaries.value.length / totalBeneficiaries) * 100)
    : Number(stats.value?.completionRate || 0)

  return [
    { label: 'Total Beneficiaries', value: totalBeneficiaries },
    { label: 'Active Beneficiaries', value: activeBeneficiaries },
    { label: 'Approved Employers', value: approvedEmployerCount },
    { label: 'Completion Rate', value: `${completionRate}%` },
  ]
})








// =====================================================
// PROFILE PHOTO
// =====================================================
const profilePhoto = computed(() => {
  return page.props.auth?.user?.profile_photo_url
    || '/images/default-avatar.png'
})








// =====================================================
// SIDEBAR MENU
// =====================================================
const { menuItems } = useSidebarMenu(props.user)








// =====================================================
// HELPERS
// =====================================================
function isToastErrorMessage(message) {
  const text = String(message || '').toLowerCase()

  return [
    'failed',
    'error',
    'required',
    'invalid',
    'missing',
    'unable',
    'unsupported',
    'must'
  ].some((keyword) => text.includes(keyword))
}

function showToast(message, color = null) {
  toast.value.message = message
  toast.value.color = color || (isToastErrorMessage(message) ? 'red' : 'green')




  toast.value.show = true




  setTimeout(() => {
    toast.value.show = false
  }, 4000)
}








function formatDate(value) {
  if (!value) return ''




  const date = new Date(value)




  return date.toLocaleString(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
    timeZone: 'Asia/Manila'
  })
}

function parseAuditProperties(entry) {
  if (entry?.properties && typeof entry.properties === 'object') {
    return entry.properties
  }

  if (typeof entry?.details !== 'string') {
    return null
  }

  const value = entry.details.trim()
  if (!value.startsWith('{') && !value.startsWith('[')) {
    return null
  }

  try {
    const parsed = JSON.parse(value)
    return parsed && typeof parsed === 'object' ? parsed : null
  } catch {
    return null
  }
}

function labelFromKey(key) {
  return String(key)
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (letter) => letter.toUpperCase())
}

function formatAuditDetails(entry) {
  const properties = parseAuditProperties(entry)

  if (!properties) {
    return entry.details || entry.description || entry.record || 'No details provided.'
  }

  const hiddenKeys = new Set(['module', 'backfilled'])
  const preferredKeys = [
    'batch_title',
    'exam_date',
    'location',
    'status',
    'result',
    'reschedule_reason',
    'beneficiary_id',
    'application_id',
    'exam_id',
  ]

  const keys = [
    ...preferredKeys.filter((key) => properties[key] !== undefined && properties[key] !== null && properties[key] !== ''),
    ...Object.keys(properties).filter((key) => !preferredKeys.includes(key) && !hiddenKeys.has(key)),
  ]

  const details = keys
    .filter((key) => !hiddenKeys.has(key))
    .map((key) => {
      const value = key.endsWith('_date') || key.endsWith('_at')
        ? formatDate(properties[key])
        : properties[key]

      return `${labelFromKey(key)}: ${value}`
    })

  return details.length ? details.join(' | ') : entry.description || 'No details provided.'
}

function combineDateAndTime(date, time) {
  if (!date || !time) return ''
  return `${date}T${time}`
}

function addMinutesToDateTime(value, minutes) {
  if (!value) return ''

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''

  date.setMinutes(date.getMinutes() + minutes)

  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const mins = String(date.getMinutes()).padStart(2, '0')

  return `${year}-${month}-${day}T${hours}:${mins}`
}

function getFormStart(form, fallbackField) {
  return combineDateAndTime(form.date, form.start_time) || form[fallbackField] || ''
}

function getFormEnd(form) {
  return combineDateAndTime(form.date, form.end_time) || ''
}








function toggleSidebar() {
  isSidebarOpen.value = !isSidebarOpen.value
}








function toggleMenu(event) {
  event.stopPropagation()
  menuOpen.value = !menuOpen.value
}








function logout() {
  Inertia.post('/logout')
}








function openImage(src) {
  selectedImage.value = src
}








function closeImage() {
  selectedImage.value = null
}








function openDocument(url, name = 'Document') {
  selectedDocument.value = url
  selectedDocumentName.value = name
}








function closeDocument() {
  selectedDocument.value = null
  selectedDocumentName.value = ''
}




function isPreviewableDocument(url) {
  if (!url) return false
  try {
    const previewExtensions = ['.pdf', '.png', '.jpg', '.jpeg', '.gif', '.webp', '.svg']
    const lower = String(url).split('?')[0].toLowerCase()
    return previewExtensions.some((ext) => lower.endsWith(ext))
  } catch (e) {
    return false
  }
}








function openRevertModal(type, id, name) {
  revertPayload.value = { type, id, name }
  showRevertModal.value = true
}




async function confirmRevertToPending() {
  if (!revertPayload.value.type || !revertPayload.value.id) return




  const endpoint = revertPayload.value.type === 'beneficiary'
    ? `/peso/beneficiaries/${revertPayload.value.id}/revert`
    : `/peso/employers/${revertPayload.value.id}/revert`




  try {
    await axios.post(endpoint)
    showToast(`${revertPayload.value.name} reverted to pending.`)
    showRevertModal.value = false
    await loadData()
  } catch (error) {
    console.error(error)
    showToast('Failed to revert to pending')
  }
}

async function approveCompletionFromQueue(item) {
  if (!item?.application_id) {
    showToast('Application identifier missing.')
    return
  }

  try {
    await axios.post(`/peso/applications/${item.application_id}/approve-completion`)
    showToast('Completion approved.')
    await loadData()
  } catch (error) {
    showToast(error.response?.data?.message || 'Failed to approve completion')
  }
}








// =====================================================
// MENU ACTIONS
// =====================================================
function handleMenuClick(key) {




  if (key === 'roles') {
    Inertia.visit('/admin/roles')
    return
  }


  if (key === 'assignment') {
    Inertia.visit('/admin/assignment')
    return
  }

  if (key === 'employerApplications') {
    Inertia.visit('/peso/employers/pending')
    return
  }

  selectedTab.value = key
}

function openScheduler(key) {
  if (!canCreateSchedules.value) {
    showToast('Only Admin or CPESO Admin users can create schedules.')
    return
  }

  selectedTab.value = key
}




function viewApplications(jobId) {
  Inertia.visit(`/peso/analytics/job-listings/${jobId}/applications`)
}




function handleDateFilterChange(value) {
  dateFilter.value = value
  loadData()
}




function handleCustomRangeChange(range) {
  customRange.value = {
    start: range.start || '',
    end: range.end || ''
  }
  loadData()
}








// =====================================================
// API CALLS
// =====================================================
async function loadData() {




  chartKey.value++




  try {




    isLoadingData.value = true








    // ================= STATS =================
    if (isAdminRole.value) {




      const statsRes = await axios.get('/admin/stats', {
        params: {
          days: selectedDays.value
        }
      })




      stats.value = statsRes.data
    }








    // ================= ANALYTICS =================
    const analyticsRes = await axios.get('/peso/analytics/dashboard', {
      params: {
        date_filter: dateFilter.value,
        start_date: customRange.value.start || undefined,
        end_date: customRange.value.end || undefined,
        report_start_date: reportFilters.value.start_date || undefined,
        report_end_date: reportFilters.value.end_date || undefined,
        employer_id: reportFilters.value.employer_id || undefined,
        school_id: reportFilters.value.school_id || undefined,
        category: reportFilters.value.category || undefined,
        status: reportFilters.value.status || undefined,
        batch_id: reportFilters.value.batch_id || undefined,
      }
    })




    applicants.value = analyticsRes.data.applicantsBySchool || {}
    employers.value = analyticsRes.data.topEmployers || {}
    performance.value = analyticsRes.data.performanceTrends || { labels: [], series: [] }
    chartStats.value = analyticsRes.data.stats || {
      chart_dates: [],
      users_growth: [],
      applications_by_peso: []
    }
    reporting.value = analyticsRes.data.reporting || {
      summary: {},
      charts: {},
      reports: {},
      insights: [],
      filters: {
        employers: [],
        schools: [],
        categories: [],
        statuses: [],
        batches: [],
      },
    }








    // ================= BENEFICIARIES =================
    const beneficiariesRes = await axios.get('/peso/beneficiaries/monitoring')




    beneficiaries.value = beneficiariesRes.data








    // ================= APPLICATIONS =================
    const applicationsRes = await axios.get('/peso/applications')




    applications.value = applicationsRes.data

    const interviewApplicationsRes = await axios.get('/peso/applications/for-interview')




    interviewApplications.value = interviewApplicationsRes.data








    // ================= INTERVIEWS =================
    const interviewsRes = await axios.get('/peso/interviews/upcoming')




    interviews.value = interviewsRes.data








    // ================= EXAMS =================
    if (isAdminRole.value) {
      const examsRes = await axios.get('/peso/exams/upcoming')
      exams.value = examsRes.data
    } else {
      exams.value = []
    }








    // ================= CONTRACTS =================
    if (isAdminRole.value) {
      const contractsRes = await axios.get('/peso/contracts/upcoming')
      contracts.value = contractsRes.data
    } else {
      contracts.value = []
    }








    // ================= REPORTS =================
    const reportsRes = await axios.get('/peso/reports')




    reports.value = reportsRes.data








    // ================= JOBS =================
    const jobsRes = await axios.get('/peso/job-listings')




    jobListings.value = jobsRes.data








    // ================= ANNOUNCEMENTS =================
    const announcementsRes = await axios.get('/peso/announcements')




    announcements.value = announcementsRes.data




    // ================= ATTENDANCE =================
    const attendanceRes = await axios.get('/peso/attendance')
    const attendanceData = attendanceRes.data || []




    attendanceRecords.value = attendanceData.map((record) => ({
      id: record.id,
      beneficiary_name: record?.beneficiary?.user?.name || record?.beneficiary?.name || 'Unknown',
      employer_name: record?.employer?.company_name || record?.employer?.name || 'Unknown',
      school: record?.beneficiary?.school?.name || record?.beneficiary?.school || 'Unknown',
      date: record.date || record.created_at || '',
      time_in: record.time_in || '',
      time_out: record.time_out || '',
      status: record.status || 'Unknown',
      presence_days: record.presence_days || 0,
    }))




    attendanceSummary.value = {
      beneficiariesMonitored: new Set(attendanceRecords.value.map((r) => r.beneficiary_name)).size,
      records: attendanceRecords.value.length,
      avgPresenceDays: attendanceRecords.value.length > 0
        ? Number(
            (
              attendanceRecords.value.reduce((sum, rec) => sum + (Number(rec.presence_days) || 0), 0) /
              attendanceRecords.value.length
            ).toFixed(1)
          )
        : 0,
    }




    attendanceFilterOptions.value = {
      schools: [...new Set(attendanceRecords.value.map((r) => r.school).filter(Boolean))],
      employers: [...new Set(attendanceRecords.value.map((r) => r.employer_name).filter(Boolean))],
      statuses: [...new Set(attendanceRecords.value.map((r) => r.status).filter(Boolean))],
    }

    // ================= DAILY ACCOMPLISHMENT REPORTS =================
    const workOutputsRes = await axios.get('/peso/work-outputs')
    workOutputs.value = workOutputsRes.data || []




    // ================= APPROVED BENEFICIARIES =================
    const approvedBeneficiariesRes = await axios.get('/peso/beneficiaries/approved')
    approvedBeneficiaries.value = approvedBeneficiariesRes.data




    // ================= APPROVED EMPLOYERS =================
    const approvedEmployersRes = await axios.get('/peso/employers/approved')
    approvedEmployers.value = approvedEmployersRes.data

    // ================= AUDIT TRAIL =================
    if (isAdminRole.value) {
      const auditTrailRes = await axios.get('/peso/audit-trail')
      auditTrail.value = auditTrailRes.data || []
    } else {
      auditTrail.value = []
    }

    const interviewersRes = await axios.get('/peso/users/interviewers')
    interviewerUsers.value = interviewersRes.data || []




    // ================= USERS AND ROLES =================
    if (isAdminRole.value) {
      try {
        const rolesRes = await axios.get('/admin/roles/json')
        users.value = rolesRes.data.users || []
        roles.value = rolesRes.data.roles || []
      } catch (error) {
        console.error('Failed to fetch users and roles:', error)
        users.value = []
        roles.value = []
      }
    }








  } catch (error) {




    console.error(error)
    dashboardError.value = 'Failed to load dashboard data.'




  } finally {




    isLoadingData.value = false
  }
}








// =====================================================
// INTERVIEW FUNCTIONS
// =====================================================
async function scheduleInterview() {
  const selectedIds = scheduleForm.value.application_ids || []
  const selectedCount = selectedIds.length || (scheduleForm.value.application_id ? 1 : 0)
  const start = getFormStart(scheduleForm.value, 'start')
  const endAt = addMinutesToDateTime(start, Math.max(selectedCount, 1) * 30)

  if (selectedIds.length === 0 && !scheduleForm.value.application_id) {
    showToast('Please select at least one applicant')
    return
  }

  if (!start) {
    showToast('Please select interview date and start time')
    return
  }

  if (!scheduleForm.value.meet_link) {
    showToast('Please provide a Google Meet link')
    return
  }

  if (!scheduleForm.value.interviewer_id) {
    showToast('Please assign a PESO interviewer')
    return
  }

  schedulingInterview.value = true

  try {
    await axios.post('/peso/schedule-interview', {
      application_id: scheduleForm.value.application_id || null,
      application_ids: selectedIds,
      batch_title: scheduleForm.value.batch_title,
      interviewer_id: scheduleForm.value.interviewer_id || null,
      start,
      end_at: endAt,
      summary: 'SPES Interview',
      attendees: [],
      meet_link: scheduleForm.value.meet_link || null,
      instructions: scheduleForm.value.instructions || null,
      notify_beneficiaries: Boolean(scheduleForm.value.notify_beneficiaries)
    })

    showToast('Interview scheduled successfully!')

    scheduleForm.value = {
      application_id: '',
      application_ids: [],
      batch_title: '',
      batch_size: null,
      date: '',
      start_time: '',
      end_time: '',
      interviewer_id: '',
      meet_link: '',
      instructions: '',
      notify_beneficiaries: true
    }

    await loadData()
  } catch (error) {
    console.error('Interview scheduling error:', error.response?.data || error.message)

    const response = error.response?.data

    let message = 'Failed to schedule interview'

    if (response?.message) {
      message = response.message
    } else if (response?.errors) {
      const errors = Object.values(response.errors).flat()

      if (errors.length) {
        message = errors.join(' ')
      }
    }

    showToast(message)
  } finally {
    schedulingInterview.value = false
  }
}








// =====================================================
// EXAM FUNCTIONS
// =====================================================
async function scheduleExam() {
  const examDate = getFormStart(examForm.value, 'exam_date')
  const endAt = getFormEnd(examForm.value)

  if (!examForm.value.application_ids || examForm.value.application_ids.length === 0) {
    showToast('Please select at least one beneficiary')
    return
  }

  if (!examDate) {
    showToast('Please select an exam date and start time')
    return
  }

  if (!endAt) {
    showToast('Please select an exam end time')
    return
  }

  if (!examForm.value.location) {
    showToast('Please enter the physical exam venue')
    return
  }

  try {
    await axios.post('/peso/exams', {
      batch_title: examForm.value.batch_title || examForm.value.batch_name || 'Batch ' + new Date().toLocaleDateString(),
      batch_name: examForm.value.batch_title || examForm.value.batch_name || 'Batch ' + new Date().toLocaleDateString(),
      batch_size: examForm.value.application_ids.length,
      application_ids: examForm.value.application_ids,
      exam_date: examDate,
      end_at: endAt,
      location: examForm.value.location,
      instructions: examForm.value.instructions || null,
      notify_beneficiaries: Boolean(examForm.value.notify_beneficiaries)
    })

    showToast('Exam scheduled successfully!')

    examForm.value = {
      batch_name: '',
      batch_title: '',
      batch_size: 1,
      application_ids: [],
      exam_title: '',
      date: '',
      start_time: '',
      end_time: '',
      exam_date: '',
      location: '',
      instructions: '',
      notify_beneficiaries: true
    }

    selectedExamApplicantId.value = ''
    await loadData()
  } catch (error) {
    console.error('Exam scheduling error:', error.response?.data || error.message)
    showToast('Failed to schedule exam: ' + (error.response?.data?.message || 'Unknown error'))
  }
}








// =====================================================
// CONTRACT FUNCTIONS
// =====================================================
async function scheduleContract() {
  const selectedIds = contractForm.value.application_ids || []
  const contractDate = getFormStart(contractForm.value, 'contract_date')
  const endAt = getFormEnd(contractForm.value)

  if (selectedIds.length === 0 && !contractForm.value.application_id) {
    showToast('Please select at least one beneficiary')
    return
  }

  if (!contractDate) {
    showToast('Please select contract date and start time')
    return
  }

  if (!endAt) {
    showToast('Please select contract end time')
    return
  }

  if (!contractForm.value.location) {
    showToast('Please enter the physical contract signing venue')
    return
  }

  schedulingContract.value = true




  try {




    await axios.post('/peso/contracts', {
      application_id: contractForm.value.application_id || null,
      application_ids: selectedIds,
      batch_title: contractForm.value.batch_title || null,
      contract_date: contractDate,
      end_at: endAt,
      location: contractForm.value.location,
      instructions: contractForm.value.instructions || null,
      notify_beneficiaries: Boolean(contractForm.value.notify_beneficiaries)
    })




    showToast('Contract scheduled successfully!')




    contractForm.value = {
      application_id: '',
      application_ids: [],
      batch_title: '',
      date: '',
      start_time: '',
      end_time: '',
      contract_date: '',
      location: '',
      instructions: '',
      notify_beneficiaries: true
    }




    await loadData()




  } catch (error) {




    console.error(error)
    // Try to surface a helpful message from the backend if available
    const resp = error && error.response && error.response.data
    let message = 'Failed to schedule contract'
    if (resp) {
      if (resp.message) message = resp.message
      else if (resp.errors) {
        // Flatten validation errors
        const errs = Object.values(resp.errors).flat()
        if (errs.length) message = errs.join(' ')
      }
    }




    showToast(message)




  } finally {




    schedulingContract.value = false
  }
}








// =====================================================
// ANNOUNCEMENT FUNCTIONS
// =====================================================
async function createAnnouncement() {




  try {




    const formData = new FormData()




    formData.append('title', newAnnouncement.value.title)
    formData.append('message', newAnnouncement.value.message)
    formData.append('target_role', newAnnouncement.value.targetRole)








    if (newAnnouncement.value.image) {
      formData.append('image', newAnnouncement.value.image)
    }








    await axios.post('/peso/announcements', formData)








    showToast('Announcement created successfully!')








    newAnnouncement.value = {
      title: '',
      message: '',
      image: null,
      targetRole: 'all'
    }








    await loadData()




  } catch (error) {




    console.error(error)
    showToast('Failed to create announcement')
  }
}








function handleImageUpload(event) {
  newAnnouncement.value.image = event.target.files[0]
}

function openEditAnnouncement(announcement) {
  editingAnnouncement.value = announcement
  editAnnouncementForm.value = {
    title: announcement?.title || '',
    message: announcement?.message || announcement?.content || '',
    image: null,
    targetRole: announcement?.targetRole || announcement?.target_role || announcement?.audience || 'all'
  }
}

function closeEditAnnouncement() {
  editingAnnouncement.value = null
  editAnnouncementForm.value = {
    title: '',
    message: '',
    image: null,
    targetRole: 'all'
  }
}

function handleEditAnnouncementImage(event) {
  editAnnouncementForm.value.image = event.target.files?.[0] || null
}

async function updateAnnouncement() {
  if (!editingAnnouncement.value?.id) {
    showToast('Announcement identifier missing.')
    return
  }

  try {
    const formData = new FormData()
    formData.append('_method', 'PATCH')
    formData.append('title', editAnnouncementForm.value.title)
    formData.append('message', editAnnouncementForm.value.message)
    formData.append('target_role', editAnnouncementForm.value.targetRole)

    if (editAnnouncementForm.value.image) {
      formData.append('image', editAnnouncementForm.value.image)
    }

    await axios.post(`/peso/announcements/${editingAnnouncement.value.id}`, formData)

    showToast('Announcement updated successfully!')
    closeEditAnnouncement()
    await loadData()
  } catch (error) {
    console.error(error)
    showToast(error.response?.data?.message || 'Failed to update announcement')
  }
}

async function deleteAnnouncement(announcement) {
  if (!announcement?.id) {
    showToast('Announcement identifier missing.')
    return
  }

  announcementToDelete.value = announcement
}

function closeDeleteAnnouncement() {
  if (deletingAnnouncement.value) return

  announcementToDelete.value = null
}

async function confirmDeleteAnnouncement() {
  if (!announcementToDelete.value?.id) {
    showToast('Announcement identifier missing.')
    return
  }

  deletingAnnouncement.value = true
  try {
    await axios.delete(`/peso/announcements/${announcementToDelete.value.id}`)
    showToast('Announcement deleted successfully!')
    announcementToDelete.value = null
    await loadData()
  } catch (error) {
    console.error(error)
    showToast(error.response?.data?.message || 'Failed to delete announcement')
  } finally {
    deletingAnnouncement.value = false
  }
}








// =====================================================
// CLICK OUTSIDE
// =====================================================
function handleClickOutside(event) {




  const clickedMenu = profileMenu.value?.contains(event.target)




  const clickedButton = menuButton.value?.contains(event.target)








  if (!clickedMenu && !clickedButton) {
    menuOpen.value = false
  }
}








// =====================================================
// AUTO REFRESH
// =====================================================
let interval = null








// =====================================================
// LIFECYCLE
// =====================================================
onMounted(async () => {




  await loadData()








  interval = setInterval(() => {
    loadData()
  }, 20000)








  document.addEventListener('click', handleClickOutside)
})








onBeforeUnmount(() => {




  if (interval) {
    clearInterval(interval)
  }








  document.removeEventListener('click', handleClickOutside)
})








// =====================================================
// ATTENDANCE ANALYTICS
// =====================================================
const averageRatings = computed(() => {
  if (!attendanceRecords.value.length) return 0




  const total = attendanceRecords.value.reduce((sum, record) => {
    return sum + (Number(record.rating) || 0)
  }, 0)




  return (total / attendanceRecords.value.length).toFixed(1)
})




const employerReliability = computed(() => {
  if (!attendanceRecords.value.length) return 0




  const completed = attendanceRecords.value.filter(record =>
    String(record.status).toLowerCase() === 'present'
  ).length




  return Math.round((completed / attendanceRecords.value.length) * 100)
})








// =====================================================
// TIME FORMATTER
// =====================================================
function formatTime(time) {
  if (!time) return '—'




  try {
    return new Date(`1970-01-01T${time}`).toLocaleTimeString([], {
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (e) {
    return time
  }
}








// =====================================================
// EXPORT FUNCTIONS
// =====================================================
function exportApplicants() {
  try {
    const data = JSON.stringify(applicants.value, null, 2)




    const blob = new Blob([data], {
      type: 'application/json'
    })




    const url = URL.createObjectURL(blob)




    const a = document.createElement('a')
    a.href = url
    a.download = 'applicants-report.json'
    a.click()




    URL.revokeObjectURL(url)




    showToast('Applicants exported successfully!')
  } catch (error) {
    console.error(error)
    showToast('Failed to export applicants')
  }
}




function exportEmployers() {
  try {
    const data = JSON.stringify(employers.value, null, 2)




    const blob = new Blob([data], {
      type: 'application/json'
    })




    const url = URL.createObjectURL(blob)




    const a = document.createElement('a')
    a.href = url
    a.download = 'employers-report.json'
    a.click()




    URL.revokeObjectURL(url)




    showToast('Employers exported successfully!')
  } catch (error) {
    console.error(error)
    showToast('Failed to export employers')
  }
}




</script>


