<template>
  <div class="flex min-h-screen bg-gradient-to-br from-blue-100 to-blue-200">


    <Sidebar
      :is-open="isSidebarOpen"
      :selected-tab="selectedTab"
      :menu-items="menuItems"
      @toggle="toggleSidebar"
      @select="handleMenuClick"
      @logout="logout"
    />



    <!-- ================= MAIN CONTENT ================= -->
    <main class="flex-1 overflow-auto">








      <!-- ================= TOP BAR ================= -->
      <div class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-gray-200 px-4 sm:px-6 py-4 flex items-center justify-between shadow-sm">
        <!-- Title -->
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-700 capitalize">{{ selectedTab }}</h1>








        <!-- Search, Bell, Profile -->
        <div class="flex items-center gap-4">








          <!-- PROFILE -->
          <div class="relative">
            <button @click.stop="toggleMenu">
  <img
    :src="profilePhoto"
    class="w-10 h-10 rounded-full object-cover border hover:opacity-80 transition duration-300 hover:scale-105"
  />
</button>








            <div v-if="menuOpen" class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border z-50">
              <a :href="route('admin.settings')" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
              <button @click="logout" class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">Logout</button>
            </div>
          </div>
        </div>
      </div>








      <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
















   
















<!-- ================= EXAM FLOW CONTROL ================= -->
<div v-if="selectedTab === 'exam'" class="bg-white p-4 rounded shadow">
  <h2 class="text-lg font-bold mb-4">Exam Status Control</h2>








  <div class="mb-4 bg-gray-50 p-4 rounded">
  <h3 class="font-medium mb-2">Schedule Exam</h3>








  <form @submit.prevent="scheduleExam" class="space-y-2">
    <div>
      <!-- ASSIGNED BENEFICIARIES -->
      <div class="mb-6">
        <h4 class="font-semibold text-lg mb-3">Assigned Beneficiaries</h4>
        <div class="overflow-x-auto">
          <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Beneficiary ID</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Full Name</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Assigned Job</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Employer Name</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="app in sortedApplications.filter(isAssignedApplication)" :key="app.id" class="border-t">
                <td class="px-4 py-2 text-sm">{{ app.id }}</td>
                <td class="px-4 py-2 text-sm">{{ app.beneficiary_name }}</td>
                <td class="px-4 py-2 text-sm">{{ app.job_title || 'N/A' }}</td>
                <td class="px-4 py-2 text-sm">{{ app.employer_name || 'N/A' }}</td>
                <td class="px-4 py-2 text-sm">
                  <button @click="selectExamApplicant(app.id)" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">
                    Select
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- UNASSIGNED BENEFICIARIES -->
      <div class="mb-6">
        <h4 class="font-semibold text-lg mb-3">Unassigned Beneficiaries</h4>
        <div class="overflow-x-auto">
          <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Beneficiary ID</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Full Name</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Status</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="app in sortedApplications.filter(app => !isAssignedApplication(app))" :key="app.id" class="border-t">
                <td class="px-4 py-2 text-sm">{{ app.id }}</td>
                <td class="px-4 py-2 text-sm">{{ app.beneficiary_name }}</td>
                <td class="px-4 py-2 text-sm">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    Unassigned
                  </span>
                </td>
                <td class="px-4 py-2 text-sm">
                  <button @click="selectExamApplicant(app.id)" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">
                    Select
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="examForm.application_id" class="bg-blue-50 p-3 rounded flex items-center justify-between gap-4">
        <p class="text-sm font-medium">Selected Applicant: {{ getSelectedExamApplicantName() }}</p>
        <button
          type="button"
          @click="clearExamSelection"
          class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600"
        >
          Remove
        </button>
      </div>
    </div>








    <input
      v-model="examForm.exam_date"
      type="datetime-local"
      :min="minDateTime"
      class="w-full border px-2 py-1 rounded"
      required
    />








    <input
      v-model="examForm.location"
      type="text"
      placeholder="Location"
      class="w-full border px-2 py-1 rounded"
    />








    <button class="bg-blue-600 text-white px-3 py-1 rounded">
      Schedule Exam
    </button>
  </form>
</div>








  <table class="w-full table-auto">
    <tr v-for="a in applications" :key="a.id" class="border-b">
      <td class="px-4 py-2">{{ a.name }}</td>
    </tr>
  </table>
  <!-- ================= UPCOMING EXAMS ================= -->
<div class="mt-4 bg-gray-50 p-4 rounded">
  <h3 class="font-medium mb-2">Upcoming Exams</h3>








  <ul class="text-sm space-y-2">
    <li v-for="exam in exams" :key="exam.id" class="border p-2 rounded">








      <!-- NAME (optional if backend provides) -->
      <div class="font-semibold">
        {{ exam.beneficiary_name || 'Applicant #' + exam.application_id }}
      </div>








      <!-- SCHEDULE -->
      <div>
    📅 {{ formatDate(exam.exam_date) }}
 </div>






      <!-- LOCATION -->
      <div v-if="exam.location">
        📍 {{ exam.location }}
      </div>








      <!-- STATUS -->
     <div class="text-xs text-gray-500">
  Status: {{ exam.status }}
  <span
  v-if="exam.result"
  :class="{
    'text-green-600': exam.result === 'passed',
    'text-red-600': exam.result === 'failed'
  }"
>
  ({{ exam.result }})
</span>
</div>








<!-- ACTION BUTTONS -->
<div v-if="exam.status === 'scheduled'" class="flex gap-2 mt-2">
  <button
    @click="updateExamResult(exam.id, 'passed')"
    class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs"
  >
    Pass
  </button>








  <button
    @click="updateExamResult(exam.id, 'failed')"
    class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs"
  >
    Fail
  </button>
</div>








    </li>








   <li v-if="exams.length === 0" class="text-gray-500">
  No scheduled exams
</li>
  </ul>
</div>
</div>

<!-- ================= CONTRACT SIGNING ================= -->
<div v-if="selectedTab === 'contract'" class="bg-white p-4 rounded shadow">
  <h2 class="text-lg font-bold mb-4">Contract Signing</h2>

  <div class="mb-4 bg-gray-50 p-4 rounded">
  <h3 class="font-medium mb-2">Select Applicant</h3>

  <!-- ASSIGNED BENEFICIARIES -->
  <div class="mb-6">
    <h4 class="font-semibold text-lg mb-3">Assigned Beneficiaries</h4>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white border border-gray-200 rounded-lg">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Beneficiary ID</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Full Name</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Assigned Job</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Employer Name</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="app in sortedApplications.filter(isAssignedApplication)" :key="app.id" class="border-t">
            <td class="px-4 py-2 text-sm">{{ app.id }}</td>
            <td class="px-4 py-2 text-sm">{{ app.beneficiary_name }}</td>
            <td class="px-4 py-2 text-sm">{{ app.job_title || 'N/A' }}</td>
            <td class="px-4 py-2 text-sm">{{ app.employer_name || 'N/A' }}</td>
            <td class="px-4 py-2 text-sm">
              <button @click="selectApplicant(app.id)" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">
                Select
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- UNASSIGNED BENEFICIARIES -->
  <div class="mb-6">
    <h4 class="font-semibold text-lg mb-3">Unassigned Beneficiaries</h4>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white border border-gray-200 rounded-lg">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Beneficiary ID</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Full Name</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Status</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="app in sortedApplications.filter(app => !isAssignedApplication(app))" :key="app.id" class="border-t">
            <td class="px-4 py-2 text-sm">{{ app.id }}</td>
            <td class="px-4 py-2 text-sm">{{ app.beneficiary_name }}</td>
            <td class="px-4 py-2 text-sm">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                Unassigned
              </span>
            </td>
            <td class="px-4 py-2 text-sm">
              <button @click="selectApplicant(app.id)" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">
                Select
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <form @submit.prevent="scheduleContract" class="space-y-2">
    <div v-if="contractForm.application_id" class="bg-blue-50 p-3 rounded flex items-center justify-between">
      <p class="text-sm font-medium">Selected Applicant: {{ getSelectedApplicantName() }}</p>
      <button @click="clearApplicantSelection" class="bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600">
        Remove
      </button>
    </div>

    <input
      v-model="contractForm.contract_date"
      type="datetime-local"
      :min="minDateTime"
      class="w-full border px-2 py-1 rounded"
      required
    />

    <input
      v-model="contractForm.location"
      type="text"
      placeholder="Location"
      class="w-full border px-2 py-1 rounded"
    />

    <button
      type="submit"
      :disabled="schedulingContract"
      class="bg-blue-600 text-white px-3 py-1 rounded disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
    >
      <svg v-if="schedulingContract" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      {{ schedulingContract ? 'Scheduling...' : 'Schedule Contract' }}
    </button>
  </form>
</div>

  <!-- ================= UPCOMING CONTRACTS ================= -->
<div class="mt-4 bg-gray-50 p-4 rounded">
  <h3 class="font-medium mb-2">Upcoming Contracts</h3>

  <ul class="text-sm space-y-2">
    <li v-for="contract in contracts" :key="contract.id" class="border p-2 rounded">
      <!-- NAME -->
      <div class="font-semibold">
        {{ contract.beneficiary_name || 'Applicant #' + contract.application_id }}
      </div>

      <!-- SCHEDULE -->
      <div>
    📅 {{ formatDate(contract.contract_date) }}
 </div>

      <!-- LOCATION -->
      <div v-if="contract.location">
        📍 {{ contract.location }}
      </div>

      <!-- STATUS -->
     <div class="text-xs text-gray-500">
  Status: {{ contract.status }}
  <span
  v-if="contract.result"
  :class="{
    'text-green-600': contract.result === 'signed',
    'text-red-600': contract.result === 'not_signed'
  }"
>
  ({{ contract.result }})
</span>
</div>

<!-- ACTION BUTTONS -->
<div v-if="contract.status === 'scheduled'" class="flex gap-2 mt-2">
  <button
    @click="updateContractResult(contract.id, 'signed')"
    class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs"
  >
    Signed
  </button>

  <button
    @click="updateContractResult(contract.id, 'not_signed')"
    class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs"
  >
    Not Signed
  </button>
</div>

    </li>

   <li v-if="contracts.length === 0" class="text-gray-500">
  No scheduled contracts
</li>
  </ul>
</div>
</div>








        <!-- ================= DASHBOARD ================= -->
        <div v-if="selectedTab === 'home'" class="space-y-6">
          <!-- Welcome Section -->
          <div class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">
              Welcome, {{ props.user?.name || 'User' }}!
            </h1>
            <p class="text-gray-500 text-base sm:text-lg">Dashboard overview</p>
          </div>

          <!-- Stats Cards -->
          <StatsCards
            v-if="canViewDashboard"
            :stats="stats"
            :show-peso-stats="isAdminRole"
          />

          <!-- Charts Section -->
          <div v-if="canViewDashboard" class="grid gap-6">
            <Charts
              :key="chartKey"
              :applicants="applicants"
              :employers="employers"
              :performance="performance"
              :completion="completion"
              :attendance="attendance"
              :stats="stats"
              :chart-stats="chartStats"
              :date-filter="dateFilter"
              :custom-range="customRange"
              :selected-days="selectedDays"
              :show-applicants-chart="true"
              :show-employers-chart="true"
              :show-growth-chart="true"
              :show-peso-chart="false"
              :can-export="isAdminRole"
              @export-applicants="exportApplicants"
              @export-employers="exportEmployers"
              @update:dateFilter="handleDateFilterChange"
              @update:customRange="handleCustomRangeChange"
              @refresh="loadData"
            />
          </div>


















          <div class="grid gap-6">
            <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
                <div>
                  <h2 class="text-2xl font-bold text-gray-900 mb-1">Attendance / DTR Monitoring</h2>
                  <p class="text-gray-500">Monitor beneficiary attendance logs and employer DTR submission status.</p>
                </div>
                <button @click="resetAttendanceFilters" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                  Reset Filters
                </button>
              </div>


              <div class="grid gap-4 md:grid-cols-3 mb-6">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">School</label>
                  <select v-model="attendanceFilters.school" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">All Schools</option>
                    <option v-for="school in attendanceFilterOptions.schools" :key="school" :value="school">{{ school }}</option>
                  </select>
                </div>


                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Employer</label>
                  <select v-model="attendanceFilters.employer" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">All Employers</option>
                    <option v-for="employer in attendanceFilterOptions.employers" :key="employer" :value="employer">{{ employer }}</option>
                  </select>
                </div>


                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                  <select v-model="attendanceFilters.status" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <option v-for="status in attendanceFilterOptions.statuses" :key="status" :value="status">{{ status }}</option>
                  </select>
                </div>
              </div>


              <div class="grid gap-4 md:grid-cols-3 mb-8">
                <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-blue-50 to-white p-6">
                  <div class="text-sm font-medium text-gray-600">Beneficiaries Monitored</div>
                  <div class="mt-3 text-3xl font-bold text-blue-600">{{ attendanceSummary.beneficiariesMonitored }}</div>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-green-50 to-white p-6">
                  <div class="text-sm font-medium text-gray-600">Attendance Records</div>
                  <div class="mt-3 text-3xl font-bold text-green-600">{{ attendanceSummary.records }}</div>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-purple-50 to-white p-6">
                  <div class="text-sm font-medium text-gray-600">Average Presence</div>
                  <div class="mt-3 text-3xl font-bold text-purple-600">{{ attendanceSummary.avgPresenceDays }} days</div>
                </div>
              </div>


              <div class="grid gap-4 lg:grid-cols-2">
                <div>
                  <div class="overflow-x-auto rounded-2xl border border-gray-200">
                    <table class="w-full text-left text-sm">
                      <thead class="bg-gray-50 text-gray-600">
                        <tr>
                          <th class="px-4 py-3">Beneficiary</th>
                          <th class="px-4 py-3">Employer</th>
                          <th class="px-4 py-3">Date</th>
                          <th class="px-4 py-3">Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="record in filteredAttendanceRecords" :key="record.id" class="border-b border-gray-200 hover:bg-gray-50">
                          <td class="px-4 py-3">{{ record.beneficiary_name }}</td>
                          <td class="px-4 py-3">{{ record.employer_name }}</td>
                          <td class="px-4 py-3">{{ record.date }}</td>
                          <td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs font-semibold" :class="record.status === 'present' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">{{ record.status }}</span></td>
                        </tr>
                        <tr v-if="filteredAttendanceRecords.length === 0">
                          <td colspan="4" class="px-4 py-6 text-center text-slate-500">No attendance records available.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>


                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6">
                  <h3 class="text-lg font-semibold text-gray-900 mb-3">DTR Review</h3>
                  <p class="text-sm text-slate-500 mb-4">Review recent DTR submissions and notes from beneficiaries.</p>
                  <div v-if="filteredAttendanceRecords.length === 0" class="text-slate-500">No recent DTR items found.</div>
                  <ul v-else class="space-y-3">
                    <li v-for="record in filteredAttendanceRecords.slice(0, 5)" :key="record.id" class="rounded-2xl border border-slate-200 bg-white p-4">
                      <div class="flex items-center justify-between gap-3">
                        <div>
                          <div class="font-semibold text-slate-900">{{ record.beneficiary_name }}</div>
                          <div class="text-sm text-slate-500">{{ record.date }} Â· {{ record.employer_name }}</div>
                        </div>
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">{{ formatTime(record.time_in) || 'No time in' }}</span>
                      </div>
                      <p class="mt-3 text-sm text-slate-600">{{ record.notes || 'No notes provided.' }}</p>
                    </li>
                  </ul>
                </div>
              </div>
            </div>


            <div class="grid gap-6 lg:grid-cols-2">
              <div class="bg-white p-6 rounded-2xl shadow">
                <div class="flex items-center justify-between mb-4">
                  <div>
                    <h3 class="text-lg font-semibold text-gray-800">Average Rating Analytics</h3>
                    <p class="text-sm text-gray-500">Track average beneficiary ratings across key categories.</p>
                  </div>
                  <div class="text-sm font-semibold text-blue-600">{{ averageRatings.submitted_count }} submitted</div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                  <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm text-slate-500">Punctuality</div>
                    <div class="mt-2 text-2xl font-bold text-slate-900">{{ averageRatings.punctuality }}</div>
                  </div>
                  <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm text-slate-500">Work Attitude</div>
                    <div class="mt-2 text-2xl font-bold text-slate-900">{{ averageRatings.attitude }}</div>
                  </div>
                  <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm text-slate-500">Output Quality</div>
                    <div class="mt-2 text-2xl font-bold text-slate-900">{{ averageRatings.work_quality }}</div>
                  </div>
                  <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm text-slate-500">Communication</div>
                    <div class="mt-2 text-2xl font-bold text-slate-900">{{ averageRatings.communication }}</div>
                  </div>
                </div>
              </div>


              <div class="bg-white p-6 rounded-2xl shadow">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Employer Reliability Analytics</h3>
                <p class="text-sm text-gray-500 mb-4">See which employers have the highest number of completed beneficiaries.</p>
                <div v-if="!employerReliability.length" class="text-sm text-gray-500">No employer reliability data available.</div>
                <ul v-else class="space-y-3">
                  <li v-for="item in employerReliability.slice(0, 5)" :key="item.employer_id || item.employer_name" class="rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between gap-4">
                      <div>
                        <div class="font-semibold text-slate-900">{{ item.employer_name }}</div>
                        <div class="text-sm text-slate-500" v-if="item.job_listing_count">
                          {{ item.job_listing_count }} job listing<span v-if="item.job_listing_count > 1">s</span>
                        </div>
                      </div>
                      <div class="text-2xl font-bold text-slate-900">{{ item.completed_count }}</div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>








        <!-- ================= BENEFICIARIES ================= -->
        <div v-if="selectedTab === 'beneficiaries'" class="space-y-6">








          <AdminWorkflow
            v-if="isAdmin || isPesoAdmin || isPesoUser"
            :stats="stats"
            :selected-days="selectedDays"
            :can-manage-roles="isAdmin"
            :can-approve="isPesoAdmin"
            :read-only="isPesoUser"
            @update:selectedDays="selectedDays = $event"
            @refresh="loadData"
          />








          <div class="bg-white p-4 rounded shadow mt-4 overflow-x-auto">
            <h2 class="text-lg font-bold mb-4">Beneficiary Monitoring</h2>
            <table class="w-full table-auto">
              <thead>
                <tr class="bg-gray-100">
                  <th class="px-4 py-2 text-left">Name</th>
                  <th class="px-4 py-2 text-left">Status</th>
                  <th class="px-4 py-2 text-left">Assigned Employer</th>
                  <th class="px-4 py-2 text-left" v-if="!isPesoUser">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="beneficiary in beneficiaries" :key="beneficiary.id" class="border-b">
                  <td class="px-4 py-2">{{ beneficiary.name }}</td>
                  <td class="px-4 py-2">
                    <span :class="statusClass(beneficiary.status)" class="px-2 py-1 rounded text-xs">
                      {{ beneficiary.status }}
                    </span>
                  </td>
                  <td class="px-4 py-2">{{ beneficiary.assigned_employer || 'None' }}</td>
                  <td class="px-4 py-2" v-if="!isPesoUser">
                    <button @click="viewBeneficiaryApplications(beneficiary.id)"
                      class="bg-purple-600 text-white px-2 py-1 rounded text-xs mr-2">View applicants</button>
                   
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>








        <!-- ================= APPROVED BENEFICIARIES ================= -->
         <div v-if="selectedTab === 'approvedBeneficiaries'" class="space-y-6">
  <div class="bg-white p-4 rounded shadow">
    <h2 class="text-lg font-bold mb-4">Approved Beneficiaries</h2>








    <div class="overflow-x-auto">
      <table class="w-full table-auto">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-4 py-2 text-left">Name</th>
            <th class="px-4 py-2 text-left">Email</th>
            <th class="px-4 py-2 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
  <tr v-for="b in approvedBeneficiaries" :key="b.id" class="border-b">
  <td class="px-4 py-2">{{ b.name }}</td>
  <td class="px-4 py-2">{{ b.email }}</td>
  <td class="px-4 py-2">
    <button
      @click="openRevertModal('beneficiaries', b.id)"
      class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs"
    >
      Revert to Pending
    </button>
    
  </td>
</tr>








  <tr v-if="approvedBeneficiaries.length === 0">
    <td colspan="3" class="px-4 py-2 text-gray-500">No approved beneficiaries</td>
  </tr>
</tbody>
      </table>
    </div>
  </div>
</div>








<!-- ================= APPROVED EMPLOYERS ================= -->
 <div v-if="selectedTab === 'approvedEmployers'" class="space-y-6">
  <div class="bg-white p-4 rounded shadow">
    <h2 class="text-lg font-bold mb-4">Approved Employers</h2>








    <div class="overflow-x-auto">
      <table class="w-full table-auto">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-4 py-2 text-left">Company</th>
            <th class="px-4 py-2 text-left">Email</th>
            <th class="px-4 py-2 text-left">Actions</th>
          </tr>
        </thead>
       <tbody>
  <tr v-for="e in approvedEmployers" :key="e.id" class="border-b">
    <td class="px-4 py-2">{{ e.company_name }}</td>
    <td class="px-4 py-2">{{ e.email }}</td>
  <td class="px-4 py-2">
    <button
      @click="openRevertModal('employers', e.id)"
      class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs"
    >
      Revert to Pending
    </button>
  </td>
</tr>








  <tr v-if="approvedEmployers.length === 0">
    <td colspan="3" class="px-4 py-2 text-gray-500">No approved employers</td>
  </tr>
</tbody>
      </table>
    </div>
  </div>
</div>




       <!-- ================= JOBS ================= -->
<div v-if="selectedTab === 'jobs'">
  <div class="bg-white p-4 rounded shadow hover:shadow-md transition">
    <h2 class="text-lg font-bold mb-4">
      Job Listing & Application Management
    </h2>

    <QuickActions
      v-if="isPesoAdmin"
      :can-assign="isPesoAdmin"
      :can-schedule="isPesoAdmin"
      @data-changed="loadData"
      class="mb-6"
    />








    <div class="overflow-x-auto rounded-2xl border border-gray-200 shadow-sm">
      <table class="w-full min-w-full divide-y divide-gray-200 bg-white">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-4 py-2 text-left">Job ID</th>
            <th class="px-4 py-2 text-left">Job Title</th>
            <th class="px-4 py-2 text-left">Employer</th>
            <th class="px-4 py-2 text-left">Applications</th>
         
            <th class="px-4 py-2 text-left">Actions</th>
          </tr>
        </thead>








        <tbody>
          <tr
            v-for="job in jobListings"
            :key="job.id"
            class="border-b"
          >
            <td class="p-4 font-medium text-gray-800">
              {{ job.id }}
            </td>








            <td class="px-4 py-2">
              {{ job.title }}
            </td>








            <td class="px-4 py-2">
              {{ job.employer_name }}
            </td>








            <td class="px-4 py-2">
              {{ job.applications_count }}
            </td>








           








            <td class="px-4 py-2">
              <!-- VIEW APPLICANTS -->
              <button
                @click="viewApplications(job.id)"
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs"
              >
                View Applicants
              </button>








            </td>
          </tr>








          <tr v-if="jobListings.length === 0">
            <td colspan="5" class="text-center py-4 text-gray-500">
              No job listings found
            </td>
          </tr>
        </tbody>
      </table>
    </div>









  </div>
</div>
        <!-- ================= INTERVIEWS ================= -->
        <div v-if="selectedTab === 'interviews'" class="space-y-4">
          <div class="bg-white p-4 rounded shadow">
            <h2 class="text-lg font-bold mb-4">Interview & Exam Scheduling</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Schedule Interview Form -->
              <div class="bg-gray-50 p-4 rounded">
                <h3 class="font-medium mb-2">Schedule Interview</h3>
                <form @submit.prevent="scheduleInterview" class="space-y-3">
                  <div>
                    <label class="text-sm">Select Application</label>
                    <select v-model="scheduleForm.application_id" class="w-full border rounded px-3 py-2" required>
                      <option value="">-- Select an applicant --</option>
                      <option v-for="app in applications" :key="app.id" :value="app.id">
                        #{{ app.id }} - {{ isValidText(app.beneficiary_name) ? app.beneficiary_name : `Applicant #${app.id}` }}
                        <span v-if="isValidText(app.job_title)"> — {{ app.job_title }}</span>
                        <span v-else class="text-gray-400"> — Unassigned job</span>
                        <span v-if="isValidText(app.employer_name)"> at {{ app.employer_name }}</span>
                        <span v-else class="text-gray-400"> at Unassigned employer</span>
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="text-sm">Scheduled At</label>
                    <input
                    v-model="scheduleForm.start"
                    type="datetime-local"
                    :min="minDateTime"
                    class="w-full border rounded px-3 py-2"
                    required
                  />
                  </div>
                  <div>
      <label class="text-sm">Meet Link</label>
      <input
        v-model="scheduleForm.meet_link"
        type="url"
        placeholder="https://meet.google.com/..."
        class="w-full border rounded px-3 py-2"
      />
    </div>
                  <div>
                  </div>
                  <button
                    type="submit"
                    :disabled="schedulingInterview"
                    class="bg-blue-600 text-white px-3 py-2 rounded disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                  >
                    <svg v-if="schedulingInterview" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ schedulingInterview ? 'Scheduling...' : 'Schedule' }}
                  </button>
                </form>
              </div>








              <!-- Upcoming Interviews -->
              <div class="bg-gray-50 p-4 rounded">
                <h3 class="font-medium mb-2">Upcoming Interviews</h3>
                <ul class="text-sm space-y-2">
                  <li v-for="interview in interviews" :key="interview.id" class="border p-2 rounded">








  <div class="font-semibold">
    <div class="text-gray-800">
      {{ isValidText(interview.beneficiary_name) ? interview.beneficiary_name : 'Applicant' }}
    </div>
    <div v-if="isValidText(interview.job_title) || isValidText(interview.employer_name)" class="text-xs text-gray-500 mt-1">
      <template v-if="isValidText(interview.job_title)">
        {{ interview.job_title }}
      </template>
      <template v-if="isValidText(interview.job_title) && isValidText(interview.employer_name)">
        -
      </template>
      <template v-if="isValidText(interview.employer_name)">
        {{ interview.employer_name }}
      </template>
    </div>
  </div>








  <div>
    📅 {{ formatDate(interview.scheduled_at) }}
  </div>








  <div v-if="interview.meet_link" class="text-blue-600">
    <a :href="interview.meet_link" target="_blank">Join Meet</a>
  </div>








  <!-- STATUS -->
  <div class="text-xs text-gray-500">
    Status: {{ interview.status }}








    <span
      v-if="interview.result"
      :class="{
        'text-green-600': interview.result === 'passed',
        'text-red-600': interview.result === 'failed'
      }"
    >
      ({{ interview.result }})
    </span>
  </div>








  <!-- ACTION BUTTONS -->
  <div v-if="interview.status === 'scheduled'" class="flex gap-2 mt-2">
    <button
      @click="updateInterviewResult(interview.id, 'passed')"
      class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs"
    >
      Pass
    </button>








    <button
      @click="updateInterviewResult(interview.id, 'failed')"
      class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs"
    >
      Fail
    </button>
  </div>








                 

                  </li>
                  <li v-if="interviews.length === 0" class="text-gray-500">No upcoming interviews</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
<!-- ================= EMPLOYER REPORTS ================= -->
<div v-if="selectedTab === 'reports'" class="space-y-6">
  <div class="bg-white p-4 rounded shadow">
    <h2 class="text-lg font-bold mb-4">Employer Reports</h2>








    <div class="overflow-x-auto">
      <table class="w-full table-auto">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-4 py-2 text-left">Title</th>
            <th class="px-4 py-2 text-left">Body</th>
            <th class="px-4 py-2 text-left">Employer ID</th>
            <th class="px-4 py-2 text-left">Document</th>
            <th class="px-4 py-2 text-left">Date</th>
          </tr>
        </thead>








        <tbody>
          <tr v-for="r in reports" :key="r.id" class="border-b">
            <td class="px-4 py-2">{{ r.title }}</td>
            <td class="px-4 py-2">{{ r.body }}</td>
            <td class="px-4 py-2">{{ r.employer_id }}</td>
            <td class="px-4 py-2">
              <button
                v-if="r.file_url"
                @click.prevent="openDocument(r.file_url, r.title)"
                class="text-blue-600 hover:text-blue-800 underline"
              >
                View
              </button>
              <span v-else class="text-gray-500">No file</span>
            </td>
            <td class="px-4 py-2">{{ formatDate(r.created_at) }}</td>
          </tr>








          <tr v-if="reports.length === 0">
            <td colspan="5" class="px-4 py-2 text-gray-500">
              No reports found
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
        <!-- ================= ANNOUNCEMENTS & AUDIT ================= -->
        <div v-if="selectedTab === 'announcements'" class="space-y-4">
          <div class="bg-white p-4 rounded shadow">
            <h2 class="text-lg font-bold mb-4">Announcements & Audit Trail</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Announcements -->
              <div class="bg-gray-50 p-4 rounded">
                <h3 class="font-medium mb-2">System Announcements</h3>
                <!-- CREATE ANNOUNCEMENT (Admin Only) -->
<div v-if="isAdminRole" class="mb-4 bg-white p-3 rounded border">
  <h4 class="font-semibold mb-2">Create Announcement</h4>








  <form @submit.prevent="createAnnouncement" class="space-y-2">
    <input
      v-model="newAnnouncement.title"
      type="text"
      placeholder="Title"
      class="w-full border rounded px-3 py-2"
      required
    />








    <textarea
      v-model="newAnnouncement.message"
      placeholder="Message"
      class="w-full border rounded px-3 py-2"
      rows="3"
      required
    ></textarea>








    <input
  type="file"
  accept="image/*"
  @change="handleImageUpload"
  class="w-full border rounded px-3 py-2"
/>








    <!-- ANNOUNCE TARGET -->
    <div class="mb-3">
      <label class="text-sm font-medium mr-2">Announce for :</label>
      <select v-model="newAnnouncement.targetRole" class="border rounded px-2 py-1">
        <option value="all">All</option>
        <option value="beneficiary">Beneficiaries</option>
        <option value="employer">Employers</option>
      </select>
    </div>








    <button
      type="submit"
      class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700"
    >
      Post Announcement
    </button>








   <!-- View Previous Announcements -->
<a
  href="/peso/notifications"
  class="inline-block bg-gray-600 text-white px-3 py-2 rounded hover:bg-gray-700"
>
  View Previous Announcements
</a>
  </form>
</div>
                <ul class="text-sm space-y-2">
                  <li v-for="announcement in filteredAnnouncements" :key="announcement.id" class="border p-2 rounded">
                    <div class="font-semibold">{{ announcement.title }}</div>
                    <div>{{ announcement.message }}</div>
                    <img
  v-if="announcement.image"
 :src="`/storage/${announcement.image}`"
  class="w-32 mt-2 rounded cursor-pointer hover:opacity-80"
 @click="openImage(`/storage/${announcement.image}`)"
/>
                    <div class="text-xs text-gray-500">
  {{ formatDate(announcement.created_at) }}
</div>
                  </li>
                  <li v-if="filteredAnnouncements.length === 0" class="text-gray-500">No announcements</li>
                </ul>
              </div>
              <!-- Audit Trail -->
              <div class="bg-gray-50 p-4 rounded">
                <h3 class="font-medium mb-2">Recent Activity</h3>
                <ul class="text-sm space-y-2">
                  <li v-for="activity in auditTrail" :key="activity.id" class="border p-2 rounded">
                    <div class="text-xs text-gray-500">
  {{ formatDate(activity.created_at) }}
</div>
                    <div class="text-sm">{{ activity.description }}</div>
                    <div class="text-xs text-gray-400">By: {{ activity.causer_name ?? 'system' }}</div>
                  </li>
                  <li v-if="auditTrail.length === 0" class="text-gray-500">No recent activity</li>
                </ul>
              </div>
            </div>
          </div>
        </div>








      </div>
    </main>
  </div>
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

<div v-if="selectedDocument" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 px-4 py-6" @click.self="closeDocument">
  <div class="w-full max-w-5xl h-[90vh] bg-white rounded-3xl overflow-hidden shadow-2xl flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b">
      <div>
        <h3 class="text-lg font-semibold text-gray-900">{{ selectedDocumentName }}</h3>
        <p class="text-xs text-gray-500 break-all">{{ selectedDocument }}</p>
      </div>
      <button @click="closeDocument" class="text-gray-500 hover:text-gray-900">Close</button>
    </div>
    <div class="flex-1 bg-slate-100">
      <iframe
        v-if="isPreviewableDocument(selectedDocument)"
        :src="selectedDocument"
        class="w-full h-full"
        frameborder="0"
      ></iframe>
      <div v-else class="flex h-full flex-col items-center justify-center gap-3 p-6 text-center">
        <p class="text-gray-700">Preview is not available for this document type.</p>
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








<ConfirmationModal :show="showRevertModal" @close="showRevertModal = false">
  <template #title>Confirm Revert</template>
  <template #content>
    Are you sure you want to revert this {{ revertPayload.name }} to Pending? This action cannot be undone.
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

<!-- TOASTER -->
<div
  v-if="toast.show"
  :class="`fixed bottom-6 right-6 bg-${toast.color}-600 text-white px-4 py-2 rounded shadow-lg z-50 animate-fade-in`"
>
  {{ toast.message }}
</div>
















</template>








<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import axios from 'axios'
import { route } from 'ziggy-js'








import StatsCards from '@/Components/StatsCards.vue'
import Charts from '@/Components/Charts.vue'
import QuickActions from '@/Components/QuickActions.vue'
import AdminWorkflow from '@/Components/AdminWorkflow.vue'
import Sidebar from '@/Components/Sidebar.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'
import { useSidebarMenu } from '@/composables/useSidebarMenu'








async function updateInterviewResult(id, result) {
  try {
    await axios.post(`/peso/interviews/${id}/result`, { result })








    showToast(`Interview marked as ${result} âœ…`)








    await loadData()
  } catch (e) {
    console.error(e)
    showToast('Failed to update interview result ❌')
  }
}
























async function updateExamResult(id, result) {
  try {
    await axios.post(`/peso/exams/${id}/result`, { result })








    showToast(`Exam marked as ${result} ✅`)








    await loadData()
  } catch (e) {
    console.error(e)
    showToast('Failed to update exam result ❌')
  }
}

async function updateContractResult(id, result) {
  try {
    await axios.post(`/peso/contracts/${id}/result`, { result })

    showToast(`Contract marked as ${result} ✅`)

    await loadData()
  } catch (e) {
    console.error(e)
    showToast('Failed to update contract result ❌')
  }
}
















const statusSteps = [
  'applied',
  'processing',
  'approved',
  'rejected'
]
function handleMenuClick(key) {
  if (key === 'roles') {
    Inertia.visit('/admin/roles') // or /admin/roles
    return
  }








  selectedTab.value = key
}
























function isStepActive(currentStatus, step) {
  return statusSteps.indexOf(step) <= statusSteps.indexOf(currentStatus)
}
function getProgress(status) {
  const index = statusSteps.indexOf(status)








  if (index === -1) return 0








  if (status === 'rejected') return 100








  return Math.round((index / (statusSteps.length - 1)) * 100)
}
















function formatDate(v) {








  if (!v) return ''








  const d = new Date(v)








  if (isNaN(d)) return v








  return d.toLocaleString(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
    timeZone: 'Asia/Manila'
  })
}

function formatTime(v) {
  if (!v) return ''

  const timeStr = String(v).trim()
  if (!timeStr) return ''

  const ampmMatch = timeStr.match(/(am|pm)$/i)
  if (ampmMatch) {
    const parsed = new Date(`1970-01-01T${timeStr.replace(/\s+/g, '').toUpperCase()}`)
    if (!isNaN(parsed)) {
      return parsed.toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
      })
    }
  }

  const hhmmMatch = timeStr.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/)
  if (hhmmMatch) {
    const hour = Number(hhmmMatch[1])
    const minute = hhmmMatch[2]
    return `${String(hour).padStart(2, '0')}:${minute}`
  }

  const parsed = new Date(timeStr)
  if (!isNaN(parsed)) {
    return parsed.toLocaleTimeString('en-GB', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: false,
    })
  }

  return timeStr
}
















// ================== STATE =================-
const isSidebarOpen = ref(true)
const selectedTab = ref('home')
const search = ref('')
const menuOpen = ref(false)
const page = usePage()
const newAnnouncement = ref({
  title: '',
  message: '',
  image: null,
  targetRole: 'all' // all, beneficiary, employer
})








function isValidText(value) {
  if (!value) return false
  const normalized = String(value).trim().toLowerCase()
  return normalized && normalized !== 'n/a' && normalized !== 'na' && normalized !== 'null' && normalized !== 'undefined'
}

const profilePhoto = computed(() => {
  const url = page.props.auth?.user?.profile_photo_url
  return isValidText(url) ? url : '/images/default-avatar.png'
})




const minDateTime = computed(() => {
  const now = new Date()




  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const day = String(now.getDate()).padStart(2, '0')
  const hours = String(now.getHours()).padStart(2, '0')
  const minutes = String(now.getMinutes()).padStart(2, '0')




  return `${year}-${month}-${day}T${hours}:${minutes}`
})




const props = defineProps({
  user: Object,
  stats: Object,
  applicants: Object,
  employers: Object,
  beneficiaries: Array,
  interviews: Array,
  jobListings: Array,
  announcements: Array
})
const reports = ref([])
const exams = ref([])
const contracts = ref([])
const stats = ref(props.stats || {})
const applicants = ref(props.applicants || { labels: [], data: [] })
const employers = ref(props.employers || { labels: [], data: [] })
const performance = ref({ labels: [], series: [] })
const completion = ref({ labels: [], data: [] })
const attendance = ref({ labels: [], data: [] })
const beneficiaries = ref(props.beneficiaries || [])
const interviews = ref(props.interviews || [])
const approvedBeneficiaries = ref([])
const jobListings = ref(props.jobListings || [])
const applications = ref([])
const attendanceRecords = ref([])
const attendanceSummary = ref({ beneficiariesMonitored: 0, records: 0, avgPresenceDays: 0 })
const attendanceFilters = ref({ school: '', employer: '', status: '' })
const attendanceFilterOptions = ref({ schools: [], employers: [], statuses: [] })
const topBeneficiaries = ref([])
const averageRatings = ref({ punctuality: 0, work_quality: 0, attitude: 0, communication: 0, overall: 0, submitted_count: 0 })
const showRevertModal = ref(false)
const revertPayload = ref({ type: '', id: null, name: '' })
const employerReliability = ref([])

const isAssignedApplication = (app) => {
  const employerName = String(app.employer_name || '').trim().toLowerCase()
  const jobTitle = String(app.job_title || '').trim().toLowerCase()
  const hasEmployer = employerName && employerName !== 'n/a'
  const hasJob = jobTitle && jobTitle !== 'n/a'

  return app.status === 'assigned' || hasEmployer || hasJob
}

const sortedApplications = computed(() => {
  const assigned = []
  const unassigned = []
 
  applications.value.forEach(app => {
    if (isAssignedApplication(app)) {
      assigned.push(app)
    } else {
      unassigned.push(app)
    }
  })
 
  return [...assigned, ...unassigned]
})
const filteredAttendanceRecords = computed(() => {
  return attendanceRecords.value.filter(record => {
    const schoolMatch = attendanceFilters.value.school
      ? record.school_name === attendanceFilters.value.school
      : true
    const employerMatch = attendanceFilters.value.employer
      ? record.employer_name === attendanceFilters.value.employer
      : true
    const statusMatch = attendanceFilters.value.status
      ? record.status === attendanceFilters.value.status
      : true


    return schoolMatch && employerMatch && statusMatch
  })
})
const applicationStatusFlow = ref([])
const announcements = ref((props.announcements || []).slice(0, 5))
const auditTrail = ref(props.stats?.recent_activity || [])
const selectedDays = ref(7)
const dateFilter = ref('last_7_days')
const customRange = ref({ start: '', end: '' })
const chartStats = ref({ chart_dates: [], users_growth: [], applications_by_peso: [] })
const chartKey = ref(0)
const scheduleForm = ref({ application_id: '', start: '', meet_link: '' })
const examForm = ref({
  application_id: '',
  exam_date: '',
  location: ''
})
const contractForm = ref({
  application_id: '',
  contract_date: '',
  location: ''
})
const selectedImage = ref(null)
const selectedDocument = ref(null)
const selectedDocumentName = ref('')
const schedulingInterview = ref(false)
const schedulingContract = ref(false)























































// local toast notification state
const toast = ref({ show: false, message: '', color: 'green' })
function showToast(msg) {
  toast.value.message = msg
  toast.value.color = msg.toLowerCase().includes('fail') ? 'red' : 'green'
  toast.value.show = true
  setTimeout(() => { toast.value.show = false }, 4000)
}








function openImage(src) {
  selectedImage.value = src
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
  const ext = String(url).split('.').pop().toLowerCase()
  return ['pdf', 'png', 'jpg', 'jpeg'].includes(ext)
}








function closeImage() {
  selectedImage.value = null
}


function resetAttendanceFilters() {
  attendanceFilters.value = { school: '', employer: '', status: '' }
}


function applyAttendanceFilters() {
  // reactive filters update automatically via computed values
}








// ================== ROLES =================-
const userRoles = computed(() => props.user?.roles || [])
const isAdmin = computed(() => userRoles.value.some(r => ['Admin', 'Super Admin', 'PESO Admin'].includes(r.name)))
const isPesoAdmin = computed(() => userRoles.value.some(r => r.name === 'PESO Admin'))
const isPesoUser = computed(() => userRoles.value.some(r => r.name === 'PESO'))
const isAdminRole = computed(() => isAdmin.value || isPesoAdmin.value)








// ---------------- New computed for dashboard visibility ----------------
const canViewDashboard = computed(() => isAdmin.value || isPesoAdmin.value || isPesoUser.value)








const approvedEmployers = ref([]) // initialize empty array








// ================== ANNOUNCEMENT FILTERING =================-
// determine a simple role string used for filtering
const userRole = computed(() => {
  if (isAdminRole.value) return 'admin'
  if (isPesoUser.value) return 'beneficiary'
  // fallback to employer role (can be expanded if more roles exist)
  return 'employer'
})








// only show announcements appropriate for the current user
const filteredAnnouncements = computed(() => {
  if (isAdminRole.value) {
    // admins see everything
    return announcements.value
  }








  return announcements.value.filter(a => {
    return a.target_role === 'all' || a.target_role === userRole.value
  })
})
// Function to load approved employers
async function loadApprovedEmployers() {
  try {
    const res = await axios.get('/peso/employers/approved')
    approvedEmployers.value = res.data
  } catch (e) {
    console.error('Failed to load approved employers', e)
  }
}








// ================== MENU =================-
const { menuItems } = useSidebarMenu()
















// ================== HELPERS =================-
const statusClass = (status) => ({
  'bg-gray-100 text-gray-800': status === 'received',
  'bg-blue-100 text-blue-800': status === 'screening',
  'bg-indigo-100 text-indigo-800': status === 'qualified',








  'bg-purple-100 text-purple-800': status === 'interview',








  'bg-yellow-100 text-yellow-800': status === 'exam',








  'bg-orange-100 text-orange-800': status === 'for_approval',








  'bg-green-100 text-green-800': status === 'approved',








  'bg-teal-100 text-teal-800': status === 'completed',








  'bg-red-100 text-red-800': status === 'rejected'
})








const jobStatusClass = (status) => ({
  'bg-yellow-100 text-yellow-800': status === 'open',
  'bg-green-100 text-green-800': status === 'filled',
  'bg-gray-100 text-gray-800': status === 'closed'
})








// ================== ACTIONS =================-
const logout = () => Inertia.post('/logout')
const toggleSidebar = () => { isSidebarOpen.value = !isSidebarOpen.value }
const toggleMenu = () => { menuOpen.value = !menuOpen.value }
function openRevertModal(type, id) {
  revertPayload.value = {
    type,
    id,
    name: type === 'beneficiaries' ? 'beneficiary' : 'employer'
  }
  showRevertModal.value = true

}
async function confirmRevertToPending() {
  if (!revertPayload.value.type || !revertPayload.value.id) {
    showRevertModal.value = false
    return
  }

  showRevertModal.value = false








  try {
    await axios.post(`/peso/${revertPayload.value.type}/${revertPayload.value.id}/revert`)








    showToast('Reverted to Pending successfully.')








    if (type === 'beneficiaries') {
      await loadApprovedBeneficiaries()
      await loadData()
    }








    if (type === 'employers') {
      await loadApprovedEmployers()
    }








  } catch (error) {
    console.error(error)
    showToast('Failed to revert.')
  }
}








function viewProfile(id) {
  window.location.href = `/peso/beneficiaries/${id}/profile`
}








function viewDocuments(id) {
  window.location.href = `/peso/beneficiaries/${id}/documents`
}
function viewBeneficiaryApplications(id) {
  Inertia.visit(`/peso/beneficiaries/${id}/applications`)
}
function viewApplications(jobId) {
  window.location.href = `/peso/analytics/job-listings/${jobId}/applications`
}
async function updateApplicationStatus(applicationId, status) {
  try {
    await axios.post('/peso/applications/update-status', {
      application_id: applicationId,
      status: status
    })








    await loadData()
    showToast(`Status updated to ${status}`)
  } catch (e) {
    console.error(e)
    alert('Failed to update status')
  }
}
  async function assignBeneficiary(jobId, event) {
    if (event) event.preventDefault()





    try {
      const beneficiaryId = prompt('Enter Beneficiary ID to assign')


      if (!beneficiaryId || isNaN(beneficiaryId)) {
        alert('Valid Beneficiary ID required')
        return
      }


      await axios.post(route('peso.assignBeneficiary'), {
    beneficiary_id: Number(beneficiaryId),
    job_listing_id: Number(jobId),
  })








    alert('Beneficiary assigned successfully!')
    await loadData()
  } catch (error) {
    alert('Failed to assign beneficiary')
    console.error(error)
  }
}
function createJobListing() {
  window.location.href = `/employer/jobs/create`
}


function downloadCSV(filename, headers, rows) {
  const csvContent = [
    headers.join(','),
    ...rows.map(row => row.map(value => `"${String(value).replace(/"/g, '""')}"`).join(','))
  ].join('\n')


  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)


  link.setAttribute('href', url)
  link.setAttribute('download', filename)
  link.style.visibility = 'hidden'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}


function exportApplicants() {
  const headers = ['School', 'Applications']
  const rows = (applicants.value.labels || []).map((label, index) => [label, applicants.value.data?.[index] ?? 0])
  downloadCSV(`applicants-${new Date().toISOString().slice(0,10)}.csv`, headers, rows)
}


function exportEmployers() {
  const headers = ['Employer', 'Hires']
  const rows = (employers.value.labels || []).map((label, index) => [label, employers.value.data?.[index] ?? 0])
  downloadCSV(`employers-${new Date().toISOString().slice(0,10)}.csv`, headers, rows)
}


function exportUsers() { window.location.href = '/admin/export-users' }

function handleDateFilterChange(filter) {
  dateFilter.value = filter
  if (filter !== 'custom') {
    customRange.value = { start: '', end: '' }
  }
  loadData()
}

function handleCustomRangeChange(range) {
  customRange.value = {
    start: range.start || '',
    end: range.end || ''
  }
  if (customRange.value.start && customRange.value.end) {
    dateFilter.value = 'custom'
    loadData()
  }
}








// Close profile menu when clicking outside
function handleClickOutside() {
  menuOpen.value = false
}


// ================== DATA FETCHING =================-
async function loadApprovedBeneficiaries() {
  try {
    const res = await axios.get('/peso/beneficiaries/approved')
    approvedBeneficiaries.value = res.data
  } catch (e) {
    console.error('Failed to load approved beneficiaries', e)
  }
}
async function loadData() {
  chartKey.value++








  try {








    //  ONLY ADMIN & PESO ADMIN can access /admin/stats
    if (isAdminRole.value) {
      const statsRes = await axios.get('/admin/stats', {
        params: { days: selectedDays.value }
      })








      stats.value = statsRes.data
      auditTrail.value = statsRes.data?.recent_activity || []
    }








    //  All PESO roles can access analytics
    if (isAdminRole.value || isPesoUser.value) {
      const analyticsParams = {
        date_filter: dateFilter.value
      }

      if (dateFilter.value === 'custom' && customRange.value.start && customRange.value.end) {
        analyticsParams.start_date = customRange.value.start
        analyticsParams.end_date = customRange.value.end
      }

      const analyticsRes = await axios.get('/peso/analytics/dashboard', {
        params: analyticsParams
      })

      chartStats.value = analyticsRes.data.stats || { chart_dates: [], users_growth: [], applications_by_peso: [] }








      performance.value =
  analyticsRes.data.performanceTrends || { labels: [], series: [] }








completion.value =
  analyticsRes.data.completionRate || { labels: [], data: [] }








attendance.value =
  analyticsRes.data.attendanceCompliance || { labels: [], data: [] }








      applicants.value =
        analyticsRes.data.applicantsBySchool || { labels: [], data: [] }








      employers.value =
        analyticsRes.data.topEmployers || { labels: [], data: [] }
    }








    //  Beneficiaries (All PESO roles)
    const beneficiariesRes = await axios.get('/peso/beneficiaries/monitoring')
    beneficiaries.value = beneficiariesRes.data


    //  Attendance / DTR Monitoring
    const attendanceRes = await axios.get('/peso/attendance')
    attendanceRecords.value = attendanceRes.data.map(record => {
      const beneficiaryName = record.beneficiary?.user?.name
        || [record.beneficiary?.first_name, record.beneficiary?.last_name].filter(Boolean).join(' ')
        || record.beneficiary?.name
        || 'Unknown'


      return {
        id: record.id,
        beneficiary_name: beneficiaryName,
        employer_name: record.employer?.company_name || 'N/A',
        school_name: record.beneficiary?.school?.name || 'N/A',
        date: record.date,
        time_in: record.time_in,
        time_out: record.time_out,
        status: record.status || (record.time_in && record.time_out ? 'present' : 'pending'),
        notes: record.remarks || record.notes || '',
      }
    })


    attendanceFilterOptions.value.statuses = Array.from(new Set(attendanceRecords.value.map(r => r.status).filter(Boolean)))


    attendanceSummary.value.records = attendanceRecords.value.length
    attendanceSummary.value.beneficiariesMonitored = new Set(attendanceRecords.value.map(r => r.beneficiary_name)).size
    attendanceSummary.value.avgPresenceDays = attendanceRecords.value.length && attendanceSummary.value.beneficiariesMonitored
      ? Number((attendanceRecords.value.length / attendanceSummary.value.beneficiariesMonitored).toFixed(1))
      : 0


    const topBeneficiariesRes = await axios.get('/peso/analytics/top-beneficiaries')
    topBeneficiaries.value = topBeneficiariesRes.data


    const averageRatingsRes = await axios.get('/peso/analytics/average-ratings')
    averageRatings.value = averageRatingsRes.data


    const employerReliabilityRes = await axios.get('/peso/analytics/employer-reliability')
    employerReliability.value = employerReliabilityRes.data.map(item => ({
      employer_id: item.employer_id,
      employer_name: item.employer_name || 'Unknown',
      completed_count: item.completed_count || item.hired_count || 0,
      job_listing_count: item.job_listing_count || 0,
    }))


    // ================= APPLICATIONS (ADD THIS) =================
    const applicationsRes = await axios.get('/peso/applications')








const grouped = Object.values(
  applicationsRes.data.reduce((acc, app) => {








    const key = app.beneficiary_id + '-' + app.job_listing_id








    if (!acc[key]) {
      acc[key] = app
    }








    // keep latest status (important)
    if (statusSteps.indexOf(app.status) >
        statusSteps.indexOf(acc[key].status)) {
      acc[key] = app
    }








    return acc
  }, {})
)








applications.value = grouped


    attendanceFilterOptions.value.schools = Array.from(new Set(
      [
        ...attendanceRecords.value.map(r => r.school_name),
        ...(applicants.value.labels || []),
        ...applications.value.map(a => a.school_name || a.beneficiary?.school?.name || '')
      ].filter(name => name && name !== 'N/A')
    )).sort()


    attendanceFilterOptions.value.employers = Array.from(new Set(
      [
        ...attendanceRecords.value.map(r => r.employer_name),
        ...applications.value.map(a => a.employer_name)
      ].filter(name => name && name !== 'N/A')
    )).sort()


    //  Interviews (All PESO roles)
    const interviewsRes = await axios.get('/peso/interviews/upcoming')
    interviews.value = interviewsRes.data








   // Exams (All PESO roles)
const examsRes = await axios.get('/peso/exams/upcoming')
exams.value = examsRes.data

    // Contracts (All PESO roles)
const contractsRes = await axios.get('/peso/contracts/upcoming')
contracts.value = contractsRes.data





// REMOVE THIS TEMPORARILY
// const applicationsRes = await axios.get('/api/beneficiary/applications')








// APPLICATION STATUS FLOW (CRITICAL FOR EXAM â†’ REJECT â†’ APPROVE)
// TEMP FIX
// const applicationStatusRes = await axios.get('/peso/applications/status-flow')
// applicationStatusFlow.value = applicationStatusRes.data








// âœ… ADD THIS HERE
const reportsRes = await axios.get('/peso/reports')








console.log('REPORTS RESPONSE:', reportsRes)
console.log('REPORTS DATA:', reportsRes.data)








reports.value = reportsRes.data








    //  Jobs & Announcements ONLY for Admin & PESO Admin
    if (isAdminRole.value) {
      const jobsRes = await axios.get('/peso/job-listings')
      jobListings.value = jobsRes.data








      const announcementsRes = await axios.get('/peso/announcements')
      announcements.value = announcementsRes.data.slice(0, 5)
    }
   
























































  } catch (e) {
    console.error('Failed to load dashboard data', e)
  }
}
















// ================== INTERVIEW SCHEDULE =================-
async function scheduleInterview() {
  schedulingInterview.value = true
 
  try {
   await axios.post('/peso/schedule-interview', {
  application_id: scheduleForm.value.application_id,
  start: scheduleForm.value.start,
  summary: 'SPES Interview',
  attendees: [],
  meet_link: scheduleForm.value.meet_link
})
    showToast('Interview scheduled successfully! 🎉')
    scheduleForm.value = { application_id: '', start: '', meet_link: '' }
    loadData()








  } catch (e) {
    console.error('Schedule failed', e)
    showToast(e?.response?.data?.message ?? 'Failed to schedule interview ❌')
  } finally {
    schedulingInterview.value = false
  }
}








async function scheduleExam() {
  try {
  await axios.post('/peso/exams', {
  application_id: examForm.value.application_id,
  exam_date: examForm.value.exam_date,
  location: examForm.value.location
})








    showToast('Exam scheduled successfully! 🎉')








    examForm.value = {
      application_id: '',
      exam_date: '',
      location: ''
    }








    await loadData()








  } catch (e) {
    console.error(e.response?.data || e)
    showToast(e.response?.data?.message || 'Failed to schedule exam ❌')
  }
}

async function scheduleContract() {
  if (!contractForm.value.application_id) {
    showToast('Please select an applicant before scheduling a contract.')
    return
  }

  schedulingContract.value = true

  try {
    await axios.post('/peso/contracts', {
      application_id: contractForm.value.application_id,
      contract_date: contractForm.value.contract_date,
      location: contractForm.value.location
    })

    showToast('Contract scheduled successfully! 🎉')

    contractForm.value = {
      application_id: '',
      contract_date: '',
      location: ''
    }

    await loadData()
  } catch (e) {
    console.error(e.response?.data || e)
    showToast(e.response?.data?.message || 'Failed to schedule contract ❌')
  } finally {
    schedulingContract.value = false
  }
}

function selectApplicant(applicationId) {
  contractForm.value.application_id = applicationId
}

function getSelectedApplicantName() {
  const app = sortedApplications.value.find(a => a.id === contractForm.value.application_id)
  return app ? `${app.beneficiary_name} (#${app.id})` : 'None selected'
}

function clearApplicantSelection() {
  contractForm.value.application_id = ''
}

function clearExamSelection() {
  examForm.value.application_id = ''
}

function selectExamApplicant(applicationId) {
  examForm.value.application_id = applicationId
}

function getSelectedExamApplicantName() {
  const app = sortedApplications.value.find(a => a.id === examForm.value.application_id)
  return app ? `${app.beneficiary_name} (#${app.id})` : 'None selected'
}








async function createAnnouncement() {
  try {
    const formData = new FormData()
    formData.append('title', newAnnouncement.value.title)
    formData.append('message', newAnnouncement.value.message)
    formData.append('target_role', newAnnouncement.value.targetRole)








    if (newAnnouncement.value.image) {
      formData.append('image', newAnnouncement.value.image)
    }








    await axios.post('/peso/announcements', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })








    // show toaster locally
    showToast('Announcement posted successfully.')








    // reset form including the new field
    newAnnouncement.value = {
      title: '',
      message: '',
      image: null,
      targetRole: 'all'
    }








    await loadData()








  } catch (e) {
    console.error('Failed to create announcement', e)
  }
}








function handleImageUpload(e) {
  newAnnouncement.value.image = e.target.files[0]
}
let interval = null


onMounted(async () => {
  await loadData()
  loadApprovedEmployers()
  loadApprovedBeneficiaries()


  interval = setInterval(() => {
    loadData()
    loadApprovedEmployers()
    loadApprovedBeneficiaries()
  }, 20000)


  document.addEventListener('click', handleClickOutside)
})


onBeforeUnmount(() => {
  if (interval) clearInterval(interval)
    document.removeEventListener('click', handleClickOutside)
})








</script>
<style scoped>
@keyframes fadeInOut {
  0%, 100% { opacity: 0; transform: translateY(20px); }
  10%, 90% { opacity: 1; transform: translateY(0); }
}








.animate-fade-in {
  animation: fadeInOut 4s ease forwards;
}
</style>













































