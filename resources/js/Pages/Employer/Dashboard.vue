<template>
  <div class="flex min-h-screen bg-gray-50">

    <!-- ================= SIDEBAR ================= -->
    <aside :class="['bg-[#1f3a63] text-white flex flex-col transition-all duration-300 shadow-xl rounded-r-3xl', isOpen ? 'w-72 p-6' : 'w-20 p-3']">
      
      <!-- Logo + Toggle -->
      <div class="flex items-center justify-between mb-6">
        <div v-if="isOpen">
          <p class="text-xs text-gray-300">EMPLOYER</p>
          <h2 class="font-bold text-xl">DASHBOARD</h2>
        </div>
        <button @click="toggleSidebar" class="w-10 h-10 flex items-center justify-center text-xl rounded hover:bg-blue-800 transition">
          ☰
        </button>
      </div>

      <!-- USER -->
      <div class="flex items-center mb-8" :class="isOpen ? 'gap-3' : 'justify-center'">
        <div class="w-14 h-14 rounded-full bg-gray-200"></div>
        <div v-if="isOpen">
          <p class="font-semibold">{{ user.name || 'User' }}</p>
          <p class="text-sm text-gray-300">Company / Employer</p>
        </div>
      </div>

      <!-- SIDEBAR MENU -->
      <nav class="flex-1 overflow-y-auto text-sm space-y-4 pr-2">
        <div v-for="menu in menus" :key="menu.name" class="relative">
          <a
            href="#"
            class="menu-item"
            @click.prevent="toggleDropdown(menu.name)"
            @mouseenter="!isOpen && (collapsedHover = menu.name)"
            @mouseleave="!isOpen && (collapsedHover = null)"
          >
            <span class="text-lg">{{ menu.icon }}</span>
            <span v-if="isOpen">{{ menu.label }}</span>
            <span v-if="menu.children && isOpen" class="ml-auto">▾</span>
          </a>

          <!-- Expanded submenu -->
          <div v-if="isOpen && menu.children && openDropdown === menu.name" class="pl-6 text-gray-300 text-xs mt-1 space-y-1">
            <a v-for="child in menu.children" :key="child.label" :href="child.href" class="block hover:text-white">
              {{ child.label }}
            </a>
          </div>

          <!-- Collapsed hover submenu -->
          <div v-if="!isOpen && menu.children && collapsedHover === menu.name"
               class="absolute left-full top-0 ml-2 bg-white text-gray-800 shadow-lg rounded-xl p-3 w-52 z-50">
            <a v-for="child in menu.children" :key="child.label" :href="child.href" class="block px-2 py-1 hover:bg-gray-100 rounded">
              {{ child.label }}
            </a>
          </div>
        </div>
      </nav>

    </aside>

    <!-- ================= MAIN CONTENT ================= -->
    <div class="flex-1 p-6 overflow-auto">
      <div class="max-w-7xl mx-auto">

        <!-- HEADER -->
        <header class="flex flex-wrap items-center justify-between gap-4 mb-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-700">Welcome, {{ user.name || 'Employer' }}!</h1>
            <p class="text-sm text-gray-500">Dashboard overview</p>
          </div>

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
        </header>

        <!-- ================= SUMMARY CARDS ================= -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
          <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500">Open Jobs</div>
            <div class="text-2xl font-bold">{{ stats.open_jobs ?? 0 }}</div>
          </div>
          <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500">Applicants</div>
            <div class="text-2xl font-bold">{{ stats.applicants ?? 0 }}</div>
          </div>
          <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500">Upcoming Interviews</div>
            <div class="text-2xl font-bold">{{ stats.upcoming_interviews ?? 0 }}</div>
          </div>
          <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500">Pending Ratings</div>
            <div class="text-2xl font-bold">{{ stats.pending_ratings ?? 0 }}</div>
          </div>
          <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500">Attendance Today</div>
            <div class="text-2xl font-bold">{{ stats.today_attendance ?? 0 }}</div>
          </div>
        </div>

        <!-- ================= CHARTS ================= -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div class="bg-white p-4 rounded shadow">
            <h2 class="font-semibold mb-2">Applicants per Job</h2>
            <canvas ref="applicantsCanvas" height="140"></canvas>
          </div>

          <div class="bg-white p-4 rounded shadow">
            <h2 class="font-semibold mb-2">Applications Over Time</h2>
            <canvas ref="applicationsOverTimeCanvas" height="140"></canvas>
          </div>
        </div>

        <!-- ================= FEATURE CARDS ================= -->
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
          <FeatureCard title="Post Job Listing" href="/employer/jobs" />
          <FeatureCard title="View Applicant Ratings" href="/employer/applicants/page" />
          <FeatureCard title="Recommended Candidates" href="/employer/recommended/page" />
          <FeatureCard title="Choose Applicant" href="/employer/applicants/page" />
          <FeatureCard title="Daily Time Record (DTR)" href="/employer/attendance/page" />
          <FeatureCard title="Attendance" href="/employer/attendance/page" />
          <FeatureCard title="Performance Evaluation" href="/employer/performance/page" />
          <FeatureCard title="Work Output Upload" href="/employer/work-output/page" />
          <FeatureCard title="Work Schedule" href="/employer/interviews/page" />
          <FeatureCard title="Employer Reports" href="/employer/reports/page" />
          <FeatureCard title="Generate Google Meet Link" href="/employer/interviews/page" />
        </section>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import axios from 'axios'
import Chart from 'chart.js/auto'
import FeatureCard from '@/Components/FeatureCard.vue'

// Sidebar
const isOpen = ref(true)
const openDropdown = ref(null)
const collapsedHover = ref(null)
const menuOpen = ref(false)
const toggleSidebar = () => (isOpen.value = !isOpen.value)
const toggleDropdown = (name) => openDropdown.value === name ? openDropdown.value = null : openDropdown.value = name
const toggleMenu = () => (menuOpen.value = !menuOpen.value)

// User
const user = ref({ name: 'ACME Corp' })

// Logout
const logout = () => Inertia.post('/logout')

// Stats & Charts
const stats = ref({})
const jobs = ref([])
const applicantsCanvas = ref(null)
const applicationsOverTimeCanvas = ref(null)

const loadStats = async () => {
  const res = await axios.get('/employer/stats')
  stats.value = res.data
  renderApplicantsPerJob()
  renderApplicationsOverTime()
}

const loadJobs = async () => {
  const res = await axios.get('/employer/analytics/applicants-per-job')
  jobs.value = res.data
}

const renderApplicantsPerJob = () => {
  if (!applicantsCanvas.value) return
  new Chart(applicantsCanvas.value, {
    type: 'bar',
    data: {
      labels: jobs.value.map(j => j.title),
      datasets: [{ data: jobs.value.map(j => j.total), backgroundColor: '#3b82f6' }]
    },
    options: { responsive: true }
  })
}

const renderApplicationsOverTime = () => {
  if (!applicationsOverTimeCanvas.value) return
  new Chart(applicationsOverTimeCanvas.value, {
    type: 'line',
    data: {
      labels: stats.value.applications_over_time?.map(r => r.date) ?? [],
      datasets: [{ data: stats.value.applications_over_time?.map(r => r.total) ?? [], borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.2)', fill: true }]
    },
    options: { responsive: true }
  })
}

// Sidebar menus (Sections A–G)
const menus = [
  { name: 'profile', label: 'Employer Profile', icon: '👤', children: [
    { label: 'View Company Profile', href: '#' },
    { label: 'Update Company Information', href: '#' },
    { label: 'Upload Company Documents', href: '#' },
    { label: 'View Compliance Status', href: '#' }
  ]},
  { name: 'jobs', label: 'Job Listing Management', icon: '💼', children: [
    { label: 'Post Job Vacancies', href: '#' },
    { label: 'Define Job Requirements', href: '#' },
    { label: 'Set Work Schedule', href: '#' },
    { label: 'View Assigned Beneficiaries', href: '#' }
  ]},
  { name: 'candidates', label: 'Candidate Viewing', icon: '👥', children: [
    { label: 'View Recommended Beneficiaries', href: '#' },
    { label: 'View Applicant Profiles', href: '#' },
    { label: 'View Ratings & Work History', href: '#' }
  ]},
  { name: 'interviews', label: 'Interview Management', icon: '🎤', children: [
    { label: 'Schedule Interview', href: '#' },
    { label: 'Generate Google Meet Link', href: '#' },
    { label: 'View Interview Calendar', href: '#' },
    { label: 'Conduct Online Interviews', href: '#' }
  ]},
  { name: 'attendance', label: 'Attendance & Work Monitoring', icon: '🕒', children: [
    { label: 'Review DTR Submissions', href: '#' },
    { label: 'Approve / Flag Attendance', href: '#' },
    { label: 'View Uploaded Outputs', href: '#' },
    { label: 'Monitor Daily Work Compliance', href: '#' }
  ]},
  { name: 'performance', label: 'Performance Evaluation', icon: '⭐', children: [
    { label: 'Rate Performance', href: '#' },
    { label: 'Submit Evaluation Report', href: '#' },
    { label: 'View Past Evaluations', href: '#' }
  ]},
  { name: 'reports', label: 'Reporting', icon: '📄', children: [
    { label: 'Submit Employer Reports', href: '#' },
    { label: 'View Compliance Checklist', href: '#' },
    { label: 'Download Summary Reports', href: '#' }
  ]}
]

onMounted(async () => {
  await loadJobs()
  await loadStats()
})
</script>

<style>
.menu-item {
  display:flex;
  align-items:center;
  gap:12px;
  padding:8px;
  border-radius:8px;
  cursor:pointer;
  transition:0.2s;
}
.menu-item:hover { background:#1e40af; }
</style>
