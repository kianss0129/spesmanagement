<template>
  <div class="relative p-6 min-h-screen bg-gray-50">
    <!-- Logout Button -->
    <div class="absolute top-6 right-6">
      <form @submit.prevent="logout">
        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Logout</button>
      </form>
    </div>

    <div class="max-w-6xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold">PESO Dashboard</h1>
          <p class="text-sm text-gray-600">Quick overview and actions for PESO officers.</p>
        </div>
        <div class="flex items-center space-x-2">
          <button @click="exportDOLE('pdf')" class="bg-indigo-600 text-white px-3 py-2 rounded hover:bg-indigo-700">Export DOLE (PDF)</button>
          <button @click="exportDOLE('excel')" class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700">Export DOLE (Excel)</button>
        </div>
      </div>

      <!-- Stat cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded shadow">
          <div class="text-gray-500">Total Applications</div>
          <div class="text-2xl font-bold">{{ totals.applications }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <div class="text-gray-500">Assigned Beneficiaries</div>
          <div class="text-2xl font-bold">{{ totals.assigned }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <div class="text-gray-500">Upcoming Interviews</div>
          <div class="text-2xl font-bold">{{ totals.interviews }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <div class="text-gray-500">Attendance Compliance (avg %)</div>
          <div class="text-2xl font-bold">{{ totals.attendance }}%</div>
        </div>
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white p-4 rounded shadow">
          <h2 class="text-lg font-bold mb-4">Applicants by School</h2>
          <canvas id="applicantsChart"></canvas>
        </div>

        <div class="bg-white p-4 rounded shadow">
          <h2 class="text-lg font-bold mb-4">Top Hiring Employers</h2>
          <canvas id="employersChart"></canvas>
        </div>
      </div>

      <!-- Quick actions -->
      <div class="bg-white p-4 rounded shadow">
        <h2 class="text-lg font-bold mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <form @submit.prevent="assignBeneficiary" class="space-y-3 bg-gray-50 p-4 rounded">
            <h3 class="font-medium">Assign Beneficiary</h3>
            <div>
              <label class="text-sm">Job Listing ID</label>
              <input v-model="assignForm.job_listing_id" type="number" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
              <label class="text-sm">Beneficiary ID</label>
              <input v-model="assignForm.beneficiary_id" type="number" class="w-full border rounded px-3 py-2" required />
            </div>
            <div class="flex items-center space-x-2">
              <button type="submit" class="bg-indigo-600 text-white px-3 py-2 rounded">Assign</button>
              <span v-if="assignMessage" class="text-sm text-green-600">{{ assignMessage }}</span>
            </div>
          </form>

          <form @submit.prevent="scheduleInterview" class="space-y-3 bg-gray-50 p-4 rounded">
            <h3 class="font-medium">Schedule Interview</h3>
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
            <div class="flex items-center space-x-2">
              <button type="submit" class="bg-green-600 text-white px-3 py-2 rounded">Schedule</button>
              <span v-if="scheduleMessage" class="text-sm text-green-600">{{ scheduleMessage }}</span>
            </div>
          </form>
        </div>
      </div>

      <!-- Recent lists -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white p-4 rounded shadow">
          <h3 class="font-medium mb-2">Recent Applications by School</h3>
          <ul class="text-sm space-y-2">
            <li v-for="(val, idx) in applicants.labels" :key="idx" class="flex justify-between">
              <span>{{ val }}</span>
              <span class="font-semibold">{{ applicants.data[idx] ?? 0 }}</span>
            </li>
          </ul>
        </div>

        <div class="bg-white p-4 rounded shadow">
          <h3 class="font-medium mb-2">Top Employers</h3>
          <ol class="text-sm list-decimal ml-5">
            <li v-for="(name, idx) in employers.labels" :key="idx">{{ name }} — <span class="font-semibold">{{ employers.data[idx] ?? 0 }}</span></li>
          </ol>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
Chart.register(...registerables)

// Logout helper
const logout = () => Inertia.post('/logout')

const applicants = ref({ labels: [], data: [] })
const employers = ref({ labels: [], data: [] })
const totals = ref({ applications: 0, assigned: 0, interviews: 0, attendance: 0 })
const assignForm = ref({ job_listing_id: '', beneficiary_id: '' })
const scheduleForm = ref({ application_id: '', scheduled_at: '', meet_link: '' })
const assignMessage = ref('')
const scheduleMessage = ref('')

function sum(arr){ return arr.reduce((s,n)=>s+Number(n||0),0) }

async function exportDOLE(format = 'pdf'){
  window.open(`/peso/reports/dole?format=${format}`, '_blank')
}

async function assignBeneficiary(){
  assignMessage.value = ''
  try{
    await axios.post('/peso/assign-beneficiary', assignForm.value)
    assignMessage.value = 'Assigned successfully.'
    loadData()
  }catch(e){
    assignMessage.value = e?.response?.data?.error ?? 'Failed to assign.'
    console.error('Assign failed', e)
  }
}

async function scheduleInterview(){
  scheduleMessage.value = ''
  try{
    await axios.post('/peso/schedule-interview', scheduleForm.value)
    scheduleMessage.value = 'Interview scheduled.'
    loadData()
  }catch(e){
    scheduleMessage.value = e?.response?.data?.message ?? 'Failed to schedule.'
    console.error('Schedule failed', e)
  }
}

let applicantsChart = null
let employersChart = null

async function loadData(){
  try{
    // Applicants by school
    const resA = await axios.get('/peso/analytics/applicants-by-school')
    applicants.value = resA.data

    // Top employers
    const resE = await axios.get('/peso/analytics/top-employers')
    employers.value = resE.data

    // Attendance compliance (use small sample - first 10)
    const resAtt = await axios.get('/peso/analytics/attendance-compliance?required_days=20')
    const attData = resAtt.data.data || resAtt.data
    const avgAtt = attData.length ? Math.round(attData.reduce((s,n)=>s+Number(n),0)/attData.length) : 0

    // Totals
    totals.value.applications = sum(applicants.value.data)
    totals.value.assigned = 0 // we don't have a direct endpoint; keep 0 or compute server-side later
    totals.value.interviews = 0 // same as above; could be added later
    totals.value.attendance = avgAtt

    // Render charts
    renderApplicantsChart()
    renderEmployersChart()
  }catch(e){
    console.error('Failed to load PESO dashboard data', e)
  }
}

function renderApplicantsChart(){
  const ctx = document.getElementById('applicantsChart')?.getContext('2d')
  if(!ctx) return
  if(applicantsChart) applicantsChart.destroy()
  applicantsChart = new Chart(ctx, {
    type: 'bar',
    data: { labels: applicants.value.labels, datasets: [{ label: 'Applicants', data: applicants.value.data, backgroundColor: '#6366F1' }] },
    options: { responsive: true, plugins: { legend: { display: false } } }
  })
}

function renderEmployersChart(){
  const ctx = document.getElementById('employersChart')?.getContext('2d')
  if(!ctx) return
  if(employersChart) employersChart.destroy()
  employersChart = new Chart(ctx, {
    type: 'bar',
    data: { labels: employers.value.labels, datasets: [{ label: 'Hires', data: employers.value.data, backgroundColor: '#10B981' }] },
    options: { responsive: true, plugins: { legend: { display: false } } }
  })
}

onMounted(()=>{ loadData() })
</script>

<style scoped>
/* small responsive tweaks */
</style>
