<template>
  <div class="relative p-6 min-h-screen bg-gray-50">

    <!-- Logout Button -->
    <div class="absolute top-6 right-6">
      <form @submit.prevent="logout" method="POST">
        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
          Logout
        </button>
      </form>
    </div>

    <div class="max-w-7xl mx-auto space-y-6">

      <!-- Header + Export Buttons (Admin + PESO Admin + PESO User) -->
      <div v-if="isAdmin || isPesoAdmin || isPesoUser" class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0">
        <div>
          <h1 class="text-2xl font-semibold">{{ roleTitle }}</h1>
          <p class="text-sm text-gray-600">{{ roleDescription }}</p>
        </div>
        <div v-if="isPesoAdmin || isPesoUser" class="flex flex-wrap gap-2">
          <button @click="exportDOLE('pdf')" class="bg-indigo-600 text-white px-3 py-2 rounded hover:bg-indigo-700">
            Export DOLE (PDF)
          </button>
          <button @click="exportDOLE('excel')" class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700">
            Export DOLE (Excel)
          </button>
        </div>
      </div>

      <!-- Basic Stats (PESO Staff) -->
      <div v-if="isPesoUser" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded shadow">
          <div class="text-gray-500">Pending Beneficiaries</div>
          <div class="text-2xl font-bold">{{ stats.pending_beneficiaries ?? 0 }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <div class="text-gray-500">Assigned Beneficiaries</div>
          <div class="text-2xl font-bold">{{ stats.assigned_beneficiaries ?? 0 }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <div class="text-gray-500">Upcoming Interviews</div>
          <div class="text-2xl font-bold">{{ interviews.length }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <div class="text-gray-500">Attendance Compliance</div>
          <div class="text-2xl font-bold">{{ stats.attendance_compliance ?? 0 }}%</div>
        </div>
      </div>

      <!-- Stats Cards (Admin + PESO Admin) -->
      <StatsCards v-if="isAdmin || isPesoAdmin" :stats="stats" :show-peso-stats="isAdminRole" />

      <!-- Charts (Admin + PESO Admin) -->
      <Charts
        v-if="isAdmin || isPesoAdmin"
        :key="chartKey"
        :applicants="applicants"
        :employers="employers"
        :stats="stats"
        :selected-days="selectedDays"
        :show-applicants-chart="isPesoAdmin"
        :show-employers-chart="isPesoAdmin"
        :show-growth-chart="isAdmin || isPesoAdmin"
        :show-peso-chart="isAdmin || isPesoAdmin"
        :can-export="isAdmin || isPesoAdmin"
        @export-users="exportUsers"
        @refresh="refresh"
      />

      <!-- Quick Actions (PESO Admin) -->
      <QuickActions
        v-if="isPesoAdmin"
        :can-assign="isPesoAdmin"
        :can-schedule="isPesoAdmin"
        @data-changed="loadData"
      />

      <!-- Admin Workflow (Admin + PESO Admin + PESO User read-only) -->
      <AdminWorkflow
        v-if="isAdmin || isPesoAdmin || isPesoUser"
        :stats="stats"
        :selected-days="selectedDays"
        :can-manage-roles="isAdmin"
        :can-approve="isPesoAdmin"
        :read-only="isPesoUser"
        @update:selectedDays="selectedDays = $event"
        @refresh="refresh"
      />

      <!-- Job Listing & Application Management (Admin + PESO Admin) -->
      <div v-if="isAdmin || isPesoAdmin" class="bg-white p-4 rounded shadow hover:shadow-md transition">
        <h2 class="text-lg font-bold mb-4">Job Listing & Application Management</h2>
        <div class="overflow-x-auto">
          <table class="w-full table-auto">
            <thead>
              <tr class="bg-gray-100">
                <th class="px-4 py-2 text-left">Job Title</th>
                <th class="px-4 py-2 text-left">Employer</th>
                <th class="px-4 py-2 text-left">Applications</th>
                <th class="px-4 py-2 text-left">Status</th>
                <th class="px-4 py-2 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="job in jobListings" :key="job.id" class="border-b">
                <td class="px-4 py-2">{{ job.title }}</td>
                <td class="px-4 py-2">{{ job.employer_name }}</td>
                <td class="px-4 py-2">{{ job.applications_count }}</td>
                <td class="px-4 py-2">
                  <span :class="{
                    'bg-yellow-100 text-yellow-800': job.status === 'open',
                    'bg-green-100 text-green-800': job.status === 'filled',
                    'bg-gray-100 text-gray-800': job.status === 'closed'
                  }" class="px-2 py-1 rounded text-xs">
                    {{ job.status }}
                  </span>
                </td>
                <td class="px-4 py-2">
                  <button @click="viewApplications(job.id)" class="bg-blue-600 text-white px-2 py-1 rounded text-xs mr-2">View Apps</button>
                  <button @click="assignBeneficiary(job.id)" class="bg-green-600 text-white px-2 py-1 rounded text-xs">Assign</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="mt-4">
          <button @click="createJobListing" class="bg-indigo-600 text-white px-4 py-2 rounded">Create New Job Listing</button>
        </div>
      </div>

      <!-- PESO Staff Monitoring (PESO Admin, PESO User, Admin) -->
      <div v-if="isAdmin || isPesoAdmin || isPesoUser" class="bg-white p-4 rounded shadow hover:shadow-md transition">
        <h2 class="text-lg font-bold mb-4">Beneficiary Monitoring</h2>
        <div class="overflow-x-auto">
          <table class="w-full table-auto">
            <thead>
              <tr class="bg-gray-100">
                <th class="px-4 py-2 text-left">Name</th>
                <th class="px-4 py-2 text-left">Status</th>
                <th class="px-4 py-2 text-left">Assigned Employer</th>
                <th class="px-4 py-2 text-left" v-if="isAdmin || isPesoAdmin">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="beneficiary in beneficiaries" :key="beneficiary.id" class="border-b">
                <td class="px-4 py-2">{{ beneficiary.name }}</td>
                <td class="px-4 py-2">
                  <span :class="{
                    'bg-yellow-100 text-yellow-800': beneficiary.status === 'Pending',
                    'bg-blue-100 text-blue-800': beneficiary.status === 'Qualified',
                    'bg-green-100 text-green-800': beneficiary.status === 'Assigned',
                    'bg-purple-100 text-purple-800': beneficiary.status === 'Interview Scheduled',
                    'bg-indigo-100 text-indigo-800': beneficiary.status === 'Hired',
                    'bg-gray-100 text-gray-800': beneficiary.status === 'Completed'
                  }" class="px-2 py-1 rounded text-xs">
                    {{ beneficiary.status }}
                  </span>
                </td>
                <td class="px-4 py-2">{{ beneficiary.assigned_employer || 'None' }}</td>
                <td class="px-4 py-2">
                  <button @click="viewBeneficiaryApplications(beneficiary.id)" class="bg-purple-600 text-white px-2 py-1 rounded text-xs mr-2">View Applications</button>
                  <button v-if="isAdmin || isPesoAdmin" @click="viewProfile(beneficiary.id)" class="bg-blue-600 text-white px-2 py-1 rounded text-xs mr-2">View Profile</button>
                  <button v-if="isAdmin || isPesoAdmin" @click="viewDocuments(beneficiary.id)" class="bg-green-600 text-white px-2 py-1 rounded text-xs">Documents</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Interview & Exam Scheduling (PESO Admin, PESO User, Admin) -->
      <div v-if="isAdmin || isPesoAdmin || isPesoUser" class="bg-white p-4 rounded shadow hover:shadow-md transition">
        <h2 class="text-lg font-bold mb-4">Interview & Exam Scheduling</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Schedule Interview Form -->
          <div v-if="isAdmin || isPesoAdmin || isPesoUser" class="bg-gray-50 p-4 rounded">
            <h3 class="font-medium mb-2">Schedule Interview</h3>
            <form @submit.prevent="scheduleInterview" class="space-y-3">
              <div>
                <label class="text-sm">Application ID</label>
                <input v-model="scheduleForm.application_id" type="number" class="w-full border rounded px-3 py-2" required />
              </div>
              <div>
                <label class="text-sm">Scheduled At</label>
                <input v-model="scheduleForm.scheduled_at" type="datetime-local" class="w-full border rounded px-3 py-2" required />
              </div>
              <div>
                <label class="text-sm">Meet Link (optional)</label>
                <input v-model="scheduleForm.meet_link" type="url" class="w-full border rounded px-3 py-2" />
              </div>
              <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded">Schedule</button>
              <span v-if="scheduleMessage" class="text-sm text-green-600 ml-2">{{ scheduleMessage }}</span>
            </form>
          </div>
          <!-- Upcoming Interviews -->
          <div class="bg-gray-50 p-4 rounded">
            <h3 class="font-medium mb-2">Upcoming Interviews</h3>
            <ul class="text-sm space-y-2">
              <li v-for="interview in interviews" :key="interview.id" class="border p-2 rounded">
                <div class="font-semibold">{{ interview.beneficiary_name }}</div>
                <div>{{ interview.scheduled_at }}</div>
                <div v-if="interview.meet_link" class="text-blue-600"><a :href="interview.meet_link" target="_blank">Join Meet</a></div>
              </li>
              <li v-if="interviews.length === 0" class="text-gray-500">No upcoming interviews</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Recent Lists (PESO Admin) -->
      <div v-if="isPesoAdmin" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-4 rounded shadow hover:shadow-md transition">
          <h3 class="font-medium mb-2">Recent Applications by School</h3>
          <ul class="text-sm space-y-2">
            <li v-for="(val, idx) in applicants.labels" :key="idx" class="flex justify-between">
              <span>{{ val }}</span>
              <span class="font-semibold text-right">{{ applicants.data[idx] ?? 0 }}</span>
            </li>
          </ul>
        </div>

        <div class="bg-white p-4 rounded shadow hover:shadow-md transition">
          <h3 class="font-medium mb-2">Top Employers</h3>
          <ol class="text-sm list-decimal ml-5">
            <li v-for="(name, idx) in employers.labels" :key="idx">
              {{ name }} — <span class="font-semibold">{{ employers.data[idx] ?? 0 }}</span>
            </li>
          </ol>
        </div>
      </div>

      <!-- Announcements & Audit Trail (Admin + PESO Admin + PESO User) -->
      <div v-if="isAdmin || isPesoAdmin || isPesoUser" class="bg-white p-4 rounded shadow hover:shadow-md transition">
        <h2 class="text-lg font-bold mb-4">Announcements & Audit Trail</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Announcements -->
          <div class="bg-gray-50 p-4 rounded">
            <h3 class="font-medium mb-2">System Announcements</h3>
            <ul class="text-sm space-y-2">
              <li v-for="announcement in announcements" :key="announcement.id" class="border p-2 rounded">
                <div class="font-semibold">{{ announcement.title }}</div>
                <div>{{ announcement.message }}</div>
                <div class="text-xs text-gray-500">{{ announcement.created_at }}</div>
              </li>
              <li v-if="announcements.length === 0" class="text-gray-500">No announcements</li>
            </ul>
            <div v-if="isAdmin || isPesoAdmin" class="mt-4">
              <button @click="createAnnouncement" class="bg-blue-600 text-white px-3 py-2 rounded text-sm">Create Announcement</button>
              <button @click="sendBulkNotification" class="bg-green-600 text-white px-3 py-2 rounded text-sm ml-2">Bulk Notification</button>
            </div>
          </div>
          <!-- Audit Trail -->
          <div class="bg-gray-50 p-4 rounded">
            <h3 class="font-medium mb-2">Recent Activity</h3>
            <ul class="text-sm space-y-2">
              <li v-for="activity in auditTrail" :key="activity.id" class="border p-2 rounded">
                <div class="text-xs text-gray-500">{{ activity.created_at }}</div>
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
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
import { Inertia } from '@inertiajs/inertia'

import StatsCards from '@/Components/StatsCards.vue'
import Charts from '@/Components/Charts.vue'
import QuickActions from '@/Components/QuickActions.vue'
import AdminWorkflow from '@/Components/AdminWorkflow.vue'

Chart.register(...registerables)

// Props from controller
const props = defineProps({
  user: Object,
  stats: Object,
  applicants: { type: Object, default: () => ({ labels: [], data: [] }) },
  employers: { type: Object, default: () => ({ labels: [], data: [] }) },
  beneficiaries: { type: Array, default: () => [] },
  interviews: { type: Array, default: () => [] },
  jobListings: { type: Array, default: () => [] },
  completionRates: { type: Object, default: () => ({ labels: [], data: [] }) },
  attendanceCompliance: { type: Object, default: () => ({ labels: [], data: [] }) },
  announcements: { type: Array, default: () => [] },
  totals: { type: Object, default: () => ({ applications: 0, assigned: 0, interviews: 0, attendance: 0 }) }
})

// Reactive data
const stats = ref(props.stats || {})
const applicants = ref(props.applicants)
const employers = ref(props.employers)
const beneficiaries = ref(props.beneficiaries)
const interviews = ref(props.interviews)
const jobListings = ref(props.jobListings)
const completionRates = ref(props.completionRates)
const attendanceCompliance = ref(props.attendanceCompliance)
const announcements = ref(props.announcements)
const auditTrail = ref(props.stats?.recent_activity || [])
const selectedDays = ref(7)
const chartKey = ref(0)
const scheduleForm = ref({ application_id: '', scheduled_at: '', meet_link: '' })
const scheduleMessage = ref('')

// Role-based computed properties
const userRoles = computed(() => props.user?.roles || [])
const isAdmin = computed(() => userRoles.value.some(role => role.name === 'Admin'))
const isPesoAdmin = computed(() => userRoles.value.some(role => role.name === 'PESO Admin'))
const isPesoUser = computed(() => userRoles.value.some(role => role.name === 'PESO'))
const isAdminRole = computed(() => isAdmin.value || isPesoAdmin.value)
const isPesoRole = computed(() => isPesoAdmin.value || isPesoUser.value)

const roleTitle = computed(() => {
  if (isAdmin.value) return 'Admin Dashboard'
  if (isPesoAdmin.value) return 'PESO Admin Dashboard'
  if (isPesoUser.value) return 'PESO Dashboard'
  return 'Dashboard'
})

const roleDescription = computed(() => {
  if (isAdmin.value) return 'System administration and oversight'
  if (isPesoAdmin.value) return 'PESO administration and approvals'
  if (isPesoUser.value) return 'Quick overview and actions for PESO officers'
  return 'Dashboard'
})

// Logout
const logout = () => router.post(window.route('logout'))

// Load data based on role
async function loadData() {
  try {
    chartKey.value++ // Force chart re-render
    if (isAdminRole.value) {
      const res = await axios.get('/admin/stats', { params: { days: selectedDays.value } })
      stats.value = res.data
    } else if (isPesoRole.value) {
      const res = await axios.get('/peso/analytics/dashboard')
      applicants.value = res.data.applicantsBySchool || { labels: [], data: [] }
      employers.value = res.data.topEmployers || { labels: [], data: [] }
    }
    // Load beneficiaries for monitoring
    if (isAdmin.value || isPesoAdmin.value || isPesoUser.value) {
      const res = await axios.get('/peso/beneficiaries/monitoring')
      beneficiaries.value = res.data
      // Load interviews
      const intRes = await axios.get('/peso/interviews/upcoming')
      interviews.value = intRes.data
      // Load job listings for management
      if (isAdmin.value || isPesoAdmin.value) {
        const jobRes = await axios.get('/peso/job-listings')
        jobListings.value = jobRes.data
        // Load analytics
        const compRes = await axios.get('/peso/analytics/completion-rate')
        completionRates.value = compRes.data
        const attRes = await axios.get('/peso/analytics/attendance-compliance')
        attendanceCompliance.value = attRes.data
      }
    }
  } catch (e) {
    console.error('Failed to load data', e)
  }
}

// Export functions
function exportUsers() {
  window.location.href = '/admin/export-users'
}

function exportDOLE(format = 'pdf') {
  window.open(`/peso/reports/dole?format=${format}`, '_blank')
}

function refresh() {
  loadData()
}

function viewProfile(id) {
  // Navigate to profile view
  window.location.href = `/peso/beneficiaries/${id}`
}

function viewDocuments(id) {
  // Navigate to documents view
  window.location.href = `/peso/beneficiaries/${id}/documents`
}

function viewBeneficiaryApplications(beneficiaryId) {
  Inertia.visit(`/beneficiaries/${beneficiaryId}/applications`)
}

function viewEmployerApplications(employerId) {
  Inertia.visit(`/employers/${employerId}/applications`)
}

async function scheduleInterview() {
  scheduleMessage.value = ''
  try {
    await axios.post('/peso/schedule-interview', scheduleForm.value)
    scheduleMessage.value = 'Interview scheduled successfully.'
    scheduleForm.value = { application_id: '', scheduled_at: '', meet_link: '' }
    loadData()
  } catch (e) {
    scheduleMessage.value = e?.response?.data?.message ?? 'Failed to schedule interview.'
    console.error('Schedule failed', e)
  }
}

function viewApplications(jobId) {
  window.location.href = `/peso/job-listings/${jobId}/applications`
}

function assignBeneficiary(jobId) {
  // Open assign modal or navigate
  window.location.href = `/peso/assign-beneficiary?job_id=${jobId}`
}

function createJobListing() {
  // Navigate to create job page
  window.location.href = `/employer/jobs/create`
}

function createAnnouncement() {
  // Placeholder for creating announcement
  alert('Create Announcement feature not implemented yet.')
}

function sendBulkNotification() {
  // Placeholder for bulk notification
  alert('Bulk Notification feature not implemented yet.')
}

// Watch for selectedDays changes
watch(selectedDays, loadData)

// Initial load
onMounted(() => {
  loadData()
})
</script>
