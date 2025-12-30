<template>
  <div class="p-6 min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <header class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Employer Dashboard</h1>

        <!-- Logout Button -->
        <button
          @click="logout"
          class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition"
        >
          Logout
        </button>
      </header>

          <!-- Top summary cards -->
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

      <!-- Controls: date range & export -->
      <div class="flex items-center space-x-4 mb-4">
        <div>
          <label class="text-sm text-gray-500">Range</label>
          <select v-model="selectedDays" @change="loadStats" class="border p-2 rounded">
            <option value="7">7 days</option>
            <option value="30">30 days</option>
            <option value="90">90 days</option>
          </select>
        </div>

        <div>
          <label class="text-sm text-gray-500">Export Applicants</label>
          <div class="flex items-center space-x-2">
            <select v-model="selectedJobForExport" class="border p-2 rounded">
              <option value="">Select Job</option>
              <option v-for="j in jobs" :key="j.id" :value="j.id">{{ j.title }}</option>
            </select>
            <button @click="exportApplicants" class="bg-blue-600 text-white px-3 py-2 rounded">Export</button>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-4 rounded shadow">
          <h2 class="font-semibold mb-2">Applicants per Job</h2>
          <canvas ref="applicantsCanvas" height="140"></canvas>
        </div>

        <div class="bg-white p-4 rounded shadow">
          <h2 class="font-semibold mb-2">Applications Over Time</h2>
          <canvas ref="applicationsOverTimeCanvas" height="140"></canvas>
          <div class="mt-4">
            <h3 class="text-sm font-medium">Pipeline</h3>
            <div class="flex space-x-4 mt-2">
              <div class="text-sm text-gray-500">Applied: <strong>{{ stats.pipeline?.applied ?? 0 }}</strong></div>
              <div class="text-sm text-gray-500">Selected: <strong>{{ stats.pipeline?.selected ?? 0 }}</strong></div>
              <div class="text-sm text-gray-500">Completed: <strong>{{ stats.pipeline?.completed ?? 0 }}</strong></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Dashboard Cards -->
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
</template>

<script setup>
import { ref, onMounted, defineComponent } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import axios from 'axios'
import Chart from 'chart.js/auto'

// Canvas refs for charts
const applicantsCanvas = ref(null)
const applicationsOverTimeCanvas = ref(null)

// Data & controls
const stats = ref({})
const jobs = ref([])
const selectedDays = ref(30)
const selectedJobForExport = ref('')

async function loadStats(){
  try{
    const res = await axios.get('/employer/stats', { params: { days: selectedDays.value } })
    stats.value = res.data

    // render charts
    renderApplicantsPerJob()
    renderApplicationsOverTime()
  }catch(e){
    console.error('Failed to load employer stats', e)
  }
}

async function loadJobs(){
  try{
    const res = await axios.get('/employer/analytics/applicants-per-job')
    jobs.value = res.data
  }catch(e){
    console.error('Failed to load jobs', e)
  }
}

function exportApplicants(){
  if(!selectedJobForExport.value) return alert('Select a job to export')
  window.location.href = `/employer/jobs/${selectedJobForExport.value}/export-applicants`
}

function renderApplicantsPerJob(){
  if(!applicantsCanvas.value) return
  const labels = jobs.value.map(j => j.title)
  const data = jobs.value.map(j => j.total)
  new Chart(applicantsCanvas.value.getContext('2d'), {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Applicants', data, backgroundColor: '#3b82f6' }] },
    options: { responsive: true, plugins: { legend: { display: false } } }
  })
}

function renderApplicationsOverTime(){
  if(!applicationsOverTimeCanvas.value) return
  const dates = stats.value.applications_over_time?.map(r => r.date) ?? []
  const totals = stats.value.applications_over_time?.map(r => r.total) ?? []

  new Chart(applicationsOverTimeCanvas.value.getContext('2d'), {
    type: 'line',
    data: { labels: dates, datasets: [{ label: 'Applications', data: totals, borderColor: '#10b981', fill: false }] },
    options: { responsive: true, plugins: { legend: { position: 'top' } } }
  })
}

// Load analytics chart on mount
onMounted(async () => {
  await loadJobs()
  await loadStats()
})

// Logout function
const logout = () => {
  Inertia.post('/logout')
}

// FeatureCard component
const FeatureCard = defineComponent({
  props: { title: String, href: { type: String, default: null } },
  setup(props) {
    const go = () => {
      if (props.href) Inertia.visit(props.href)
    }
    return { go }
  },
  template: `
    <div @click="go" class="bg-white rounded-lg shadow p-4 cursor-pointer hover:shadow-md transition">
      <h3 class="font-medium text-gray-700">{{ title }}</h3>
    </div>
  `
})
</script>
