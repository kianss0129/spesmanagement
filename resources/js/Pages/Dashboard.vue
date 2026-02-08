yung peso user dapat nafefetch nya rin yung existing data, statistic and analytic 
<template>
  <div class="flex min-h-screen bg-gray-50">

    <!-- ================= SIDEBAR ================= -->
    <aside
      class="bg-[#1f3a63] text-white flex flex-col transition-all duration-300 shadow-xl"
      :class="isSidebarOpen ? 'w-72 p-6' : 'w-20 p-3'"
    >
      <!-- Logo + Toggle -->
      <div class="flex items-center justify-between mb-6">
        <span class="font-bold text-lg" v-if="isSidebarOpen">Dashboard</span>
        <button @click="isSidebarOpen = !isSidebarOpen" class="focus:outline-none">
          <svg v-if="isSidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M6 18L18 6M6 6l12 12" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>

      <!-- Menu -->
      <nav class="flex-1">
        <ul class="space-y-2">
          <li v-for="item in menuItems" :key="item.key">
            <button
              @click="selectedTab = item.key"
              :class="{'bg-blue-600': selectedTab === item.key, 'hover:bg-blue-500': selectedTab !== item.key}"
              class="w-full text-left px-4 py-2 rounded flex items-center gap-2 transition"
            >
              <span>{{ isSidebarOpen ? item.label : item.shortLabel }}</span>
            </button>
          </li>
          <!-- Logout Button -->
          <li class="mt-6">
            <button @click="logout" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
              <span v-if="isSidebarOpen">Logout</span>
              <span v-else>L</span>
            </button>
          </li>
        </ul>
      </nav>
    </aside>

    <!-- ================= MAIN CONTENT ================= -->
    <main class="flex-1 overflow-auto">

      <!-- ================= TOP BAR ================= -->
      <div class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-gray-200 px-6 py-4 flex items-center justify-between shadow-sm">
        <!-- Title -->
        <h1 class="text-2xl font-bold text-gray-700 capitalize">{{ selectedTab }}</h1>

        <!-- Search, Bell, Profile -->
        <div class="flex items-center gap-4">
          <!-- SEARCH -->
          <div class="relative">
            <input
              v-model="search"
              type="text"
              placeholder="Search..."
              class="pl-10 pr-4 py-2 rounded-full border shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
            />
            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
          </div>

          <!-- BELL -->
          <button class="relative w-10 h-10 rounded-full bg-white shadow hover:bg-gray-100">
            🔔
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">3</span>
          </button>

          <!-- PROFILE -->
          <div class="relative">
            <button @click.stop="toggleMenu" class="w-10 h-10 rounded-full bg-gray-300 hover:bg-gray-400"></button>

            <div v-if="menuOpen" class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border z-50">
              <a href="/beneficiary/settings" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
              <button @click="logout" class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">Logout</button>
            </div>
          </div>
        </div>
      </div>

      <div class="p-6 space-y-6">

        <!-- ================= DASHBOARD ================= -->
        <div v-if="selectedTab === 'home'" class="space-y-6">
          <!-- Welcome Message -->
          <h1 class="text-3xl font-bold text-gray-700">
            Welcome, {{ props.user?.name || 'User' }}!
          </h1>

          <StatsCards
            v-if="isAdmin || isPesoAdmin"
            :stats="stats"
            :show-peso-stats="isAdminRole"
          />

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
            @refresh="loadData"
          />
        </div>


        <!-- ================= BENEFICIARIES ================= -->
        <div v-if="selectedTab === 'beneficiaries'" class="space-y-6">
          <QuickActions
            v-if="isPesoAdmin"
            :can-assign="isPesoAdmin"
            :can-schedule="isPesoAdmin"
            @data-changed="loadData"
          />

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
                  <th class="px-4 py-2 text-left" v-if="isAdmin || isPesoAdmin">Actions</th>
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
                  <td class="px-4 py-2">
                    <button @click="viewBeneficiaryApplications(beneficiary.id)"
                      class="bg-purple-600 text-white px-2 py-1 rounded text-xs mr-2">View Apps</button>
                    <button v-if="isAdmin || isPesoAdmin" @click="viewProfile(beneficiary.id)"
                      class="bg-blue-600 text-white px-2 py-1 rounded text-xs mr-2">View Profile</button>
                    <button v-if="isAdmin || isPesoAdmin" @click="viewDocuments(beneficiary.id)"
                      class="bg-green-600 text-white px-2 py-1 rounded text-xs">Documents</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ================= JOBS ================= -->
        <div v-if="selectedTab === 'jobs'">
          <div class="bg-white p-4 rounded shadow hover:shadow-md transition">
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
                      <span :class="jobStatusClass(job.status)" class="px-2 py-1 rounded text-xs">
                        {{ job.status }}
                      </span>
                    </td>
                    <td class="px-4 py-2">
                      <button @click="viewApplications(job.id)"
                        class="bg-blue-600 text-white px-2 py-1 rounded text-xs mr-2">View Apps</button>
                      <button @click="assignBeneficiary(job.id)"
                        class="bg-green-600 text-white px-2 py-1 rounded text-xs">Assign</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="mt-4">
              <button @click="createJobListing" class="bg-indigo-600 text-white px-4 py-2 rounded">Create New Job Listing</button>
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
        </div>

        <!-- ================= ANNOUNCEMENTS & AUDIT ================= -->
        <div v-if="selectedTab === 'announcements'" class="space-y-4">
          <div class="bg-white p-4 rounded shadow">
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
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import axios from 'axios'

import StatsCards from '@/Components/StatsCards.vue'
import Charts from '@/Components/Charts.vue'
import QuickActions from '@/Components/QuickActions.vue'
import AdminWorkflow from '@/Components/AdminWorkflow.vue'

// ================== STATE ==================
const isSidebarOpen = ref(true)
const selectedTab = ref('home')
const search = ref('')
const menuOpen = ref(false)

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

const stats = ref(props.stats || {})
const applicants = ref(props.applicants || { labels: [], data: [] })
const employers = ref(props.employers || { labels: [], data: [] })
const beneficiaries = ref(props.beneficiaries || [])
const interviews = ref(props.interviews || [])
const jobListings = ref(props.jobListings || [])
const announcements = ref(props.announcements || [])
const auditTrail = ref(props.stats?.recent_activity || [])
const selectedDays = ref(7)
const chartKey = ref(0)
const scheduleForm = ref({ application_id: '', scheduled_at: '', meet_link: '' })
const scheduleMessage = ref('')

// ================== ROLES ==================
const userRoles = computed(() => props.user?.roles || [])
const isAdmin = computed(() => userRoles.value.some(r => ['Admin', 'Super Admin', 'PESO Admin'].includes(r.name)))
const isPesoAdmin = computed(() => userRoles.value.some(r => r.name === 'PESO Admin'))
const isPesoUser = computed(() => userRoles.value.some(r => r.name === 'PESO'))
const isAdminRole = computed(() => isAdmin.value || isPesoAdmin.value)

// ================== MENU ==================
const menuItems = [
  { key: 'home', label: 'Dashboard', shortLabel: 'D' },
  { key: 'beneficiaries', label: 'Beneficiaries', shortLabel: 'B' },
  { key: 'jobs', label: 'Job Listings', shortLabel: 'J' },
  { key: 'interviews', label: 'Interviews', shortLabel: 'I' },
  { key: 'announcements', label: 'Announcements', shortLabel: 'A' },
]

// ================== HELPERS ==================
const statusClass = (status) => ({
  'bg-yellow-100 text-yellow-800': status === 'Pending',
  'bg-blue-100 text-blue-800': status === 'Qualified',
  'bg-green-100 text-green-800': status === 'Assigned',
  'bg-purple-100 text-purple-800': status === 'Interview Scheduled',
  'bg-indigo-100 text-indigo-800': status === 'Hired',
  'bg-gray-100 text-gray-800': status === 'Completed'
})

const jobStatusClass = (status) => ({
  'bg-yellow-100 text-yellow-800': status === 'open',
  'bg-green-100 text-green-800': status === 'filled',
  'bg-gray-100 text-gray-800': status === 'closed'
})

// ================== ACTIONS ==================
const logout = () => Inertia.post('/logout')

function viewProfile(id) { window.location.href = `/peso/beneficiaries/${id}` }
function viewDocuments(id) { window.location.href = `/peso/beneficiaries/${id}/documents` }
function viewBeneficiaryApplications(id) { Inertia.visit(`/beneficiaries/${id}/applications`) }
function viewApplications(jobId) { window.location.href = `/peso/job-listings/${jobId}/applications` }
function assignBeneficiary(jobId) { window.location.href = `/peso/assign-beneficiary?job_id=${jobId}` }
function createJobListing() { window.location.href = `/employer/jobs/create` }
function exportUsers() { window.location.href = '/admin/export-users' }

const toggleMenu = () => { menuOpen.value = !menuOpen.value }

// Close profile menu when clicking outside
document.addEventListener('click', () => { menuOpen.value = false })

// ================== DATA FETCHING ==================
async function loadData() {
  chartKey.value++ // force chart refresh
  try {
    if (isAdminRole.value) {
      const statsRes = await axios.get('/admin/stats', { params: { days: selectedDays.value } })
      stats.value = statsRes.data

      const analyticsRes = await axios.get('/peso/analytics/dashboard')
      applicants.value = analyticsRes.data.applicantsBySchool || { labels: [], data: [] }
      employers.value = analyticsRes.data.topEmployers || { labels: [], data: [] }
    }

    const beneficiariesRes = await axios.get('/peso/beneficiaries/monitoring')
    beneficiaries.value = beneficiariesRes.data

    const interviewsRes = await axios.get('/peso/interviews/upcoming')
    interviews.value = interviewsRes.data

    if (isAdminRole.value) {
      const jobsRes = await axios.get('/peso/job-listings')
      jobListings.value = jobsRes.data
    }
  } catch (e) {
    console.error('Failed to load dashboard data', e)
  }
}

// ================== INTERVIEW SCHEDULE ==================
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

onMounted(() => {
  loadData()
})
</script>
