<template>
  <div class="flex min-h-screen bg-gradient-to-br from-blue-100 to-blue-200">

    <!-- SIDEBAR -->
    <aside :class="['bg-[#1f3a63] text-white flex flex-col transition-all duration-300 shadow-xl rounded-r-3xl', isOpen ? 'w-72 p-6' : 'w-20 p-3']">
      <!-- Logo + Toggle -->
      <div class="flex items-center justify-between mb-6">
        <div v-if="isOpen">
          <p class="text-xs text-gray-300">BENEFICIARY</p>
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
          <p class="text-sm text-gray-300">Student / Applicant</p>
        </div>
      </div>

      <!-- MENU -->
      <nav class="flex-1 overflow-y-auto text-sm space-y-6 pr-2">
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

          <!-- Expanded submenu inline -->
          <div v-if="isOpen && menu.children && openDropdown === menu.name" class="pl-6 text-gray-300 text-xs mt-1 space-y-1">
            <a v-for="child in menu.children" :key="child.label" :href="child.href" class="block hover:text-white">{{ child.label }}</a>
          </div>

          <!-- Collapsed sidebar hover dropdown -->
          <div v-if="!isOpen && menu.children && collapsedHover === menu.name"
               class="absolute left-full top-0 ml-2 bg-white text-gray-800 shadow-lg rounded-xl p-3 w-48 z-50">
            <a v-for="child in menu.children" :key="child.label" :href="child.href" class="block px-2 py-1 hover:bg-gray-100 rounded">{{ child.label }}</a>
          </div>
        </div>
      </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="flex-1 p-6 overflow-auto">
      <div class="max-w-7xl mx-auto">

        <!-- HEADER -->
        <header class="flex flex-wrap items-center justify-between gap-4 mb-8">
          <div>
            <h1 class="text-2xl font-bold text-gray-700">Welcome, {{ user.name || 'User' }}!</h1>
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

        <!-- STATUS -->
        <div class="bg-white p-6 rounded-2xl shadow-lg mb-6">
          <h2 class="font-semibold mb-3">Application Status</h2>
          <div class="w-full bg-gray-200 h-3 rounded-full overflow-hidden">
            <div class="bg-green-500 h-full" style="width:60%"></div>
          </div>
          <p class="mt-2 text-green-600 text-sm font-medium">Interview Scheduled</p>
        </div>

        <!-- GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <!-- SCHEDULE -->
          <div class="bg-white p-6 rounded-2xl shadow-lg">
            <h2 class="font-semibold mb-2">Upcoming Schedule</h2>
            <div v-if="!interviews || interviews.length === 0" class="text-gray-500 text-sm">No upcoming schedule</div>
            <div v-for="i in interviews || []" :key="i.id" class="text-sm mb-3">
              <p class="font-medium">{{ i.job_title }}</p>
              <p class="text-gray-600">{{ formatDate(i.scheduled_at) }}</p>
              <a v-if="i.meet_link" :href="i.meet_link" target="_blank" class="inline-block mt-2 bg-orange-500 text-white px-4 py-2 rounded-full shadow">
                Join Google Meet
              </a>
            </div>
          </div>

          <!-- DOCUMENTS -->
          <div class="bg-white p-6 rounded-2xl shadow-lg">
            <h2 class="font-semibold mb-3">Required Documents</h2>
            <ul class="text-sm space-y-2">
              <li v-for="doc in documents" :key="doc.name" :class="doc.uploaded ? 'text-green-600' : 'text-gray-500'">
                {{ doc.name }} <span v-if="doc.uploaded">✓</span><span v-else>(Pending)</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- CHART -->
        <div class="bg-white p-6 rounded-2xl shadow-lg mb-10">
          <h2 class="font-semibold mb-3">Attendance</h2>
          <div class="h-64">
            <canvas id="attendanceChart"></canvas>
          </div>
        </div>

        <!-- FLOATING BUTTON -->
        <button @click="openUpload" class="fixed bottom-6 right-6 bg-indigo-600 text-white px-5 py-3 rounded-full shadow-lg hover:bg-indigo-700">Upload Documents</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
Chart.register(...registerables)

const isOpen = ref(true)
const openDropdown = ref(null)
const collapsedHover = ref(null)
const menuOpen = ref(false)
const search = ref('')
const interviews = ref([])
const attendance = ref([])
const user = ref({ name: '' })
const documents = ref([])

// Sidebar
const toggleSidebar = () => (isOpen.value = !isOpen.value)
const toggleDropdown = (name) => openDropdown.value === name ? openDropdown.value = null : openDropdown.value = name
const toggleMenu = () => (menuOpen.value = !menuOpen.value)

// Load user info
async function loadUser() {
  try {
    const res = await axios.get('/api/beneficiary/profile') // replace with your endpoint
    user.value = res.data
  } catch {
    user.value = { name: 'Juan Dela Cruz' } // fallback
  }
}

// Load uploaded documents
async function loadDocuments() {
  try {
    const res = await axios.get('/api/beneficiary/documents') // replace with your endpoint
    documents.value = res.data
  } catch {
    documents.value = [
      { name: 'PSA Birth Certificate', uploaded: true },
      { name: 'Barangay Clearance', uploaded: false },
      { name: 'School ID', uploaded: true },
      { name: 'Onboarding Form', uploaded: false }
    ]
  }
}

// Logout SPA-friendly
async function logout() {
  try {
    await axios.post('/logout')
    window.location.href = '/login'
  } catch (err) {
    console.error('Logout failed', err)
  }
}

// Upload page
function openUpload() { window.location.href = '/beneficiary/upload' }
function formatDate(v) { return v ? new Date(v).toLocaleString() : '' }

// Chart
let attendanceChart = null
function renderAttendanceChart() {
  const canvas = document.getElementById('attendanceChart')
  if (!canvas) return
  if (attendanceChart) attendanceChart.destroy()

  attendanceChart = new Chart(canvas, {
    type: 'line',
    data: {
      labels: attendance.value?.map(r => r.date) || [],
      datasets: [{
        label: 'Attendance %',
        data: attendance.value?.map(r => r.percentage) || [],
        fill: true,
        backgroundColor: 'rgba(59,130,246,0.2)',
        borderColor: '#3b82f6'
      }]
    },
    options: { responsive: true, maintainAspectRatio: false }
  })
}

// Load mock data
function loadData() {
  interviews.value = [
    { id: 1, job_title: 'Front-End Developer', scheduled_at: new Date(), meet_link: 'https://meet.google.com/' },
    { id: 2, job_title: 'Backend Developer', scheduled_at: new Date(), meet_link: null },
  ]
  attendance.value = [
    { date: '2026-02-01', percentage: 80 },
    { date: '2026-02-02', percentage: 90 },
    { date: '2026-02-03', percentage: 70 },
    { date: '2026-02-04', percentage: 100 },
  ]
  nextTick().then(renderAttendanceChart)
}

// Close profile menu on outside click
function handleClick(e) { if (!e.target.closest('.relative')) menuOpen.value = false }

onMounted(() => {
  loadUser()
  loadData()
  loadDocuments()
  document.addEventListener('click', handleClick)
})
onBeforeUnmount(() => document.removeEventListener('click', handleClick))

// Menu
const menus = [
  { name:'profile', label:'Profile Management', icon:'👤', children:[ {label:'View Profile',href:'#'},{label:'Edit Info',href:'#'}] },
  { name:'application', label:'Application Management', icon:'💼', children:[ {label:'Jobs',href:'#'},{label:'Apply',href:'#'}] },
  { name:'documents', label:'Documents & Requirements', icon:'📄', children:[ {label:'Upload',href:'#'} ] },
  { name:'exam', label:'Exam & Qualification', icon:'📢', children:[ {label:'Exam Notif',href:'#'} ] },
  { name:'interview', label:'Interview Management', icon:'🎤', children:[ {label:'Schedule',href:'#'} ] },
  { name:'attendance', label:'Attendance & Work', icon:'🕒', children:[ {label:'Submit DTR',href:'#'} ] },
  { name:'notifications', label:'Notifications', icon:'🔔', children:[ {label:'Announcements',href:'#'} ] },
]
</script>

<style>
.menu-item {
  display:flex; align-items:center; gap:12px; padding:8px; border-radius:8px; cursor:pointer; transition:0.2s;
}
.menu-item:hover { background:#1e40af; }
</style>
