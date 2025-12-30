<template>
  <div class="p-6 min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <header class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-semibold">Beneficiary Dashboard</h1>
          <p class="text-sm text-gray-600">Overview of your applications, interviews, attendance and work history.</p>
        </div>
        <div class="flex items-center space-x-2">
          <button @click="openUpload" class="bg-indigo-600 text-white px-3 py-2 rounded hover:bg-indigo-700">Upload Documents</button>
          <button @click="logout" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">Logout</button>
        </div>
      </header>

      <!-- Stat cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <div class="bg-white p-4 rounded shadow">
          <div class="text-gray-500">Your Applications</div>
          <div class="text-2xl font-bold">{{ totals.applications }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <div class="text-gray-500">Upcoming Interviews</div>
          <div class="text-2xl font-bold">{{ totals.interviews }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <div class="text-gray-500">Documents Uploaded</div>
          <div class="text-2xl font-bold">{{ totals.documents }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <div class="text-gray-500">Attendance (avg)</div>
          <div class="text-2xl font-bold">{{ totals.attendance }}%</div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        <!-- Attendance Chart -->
        <div class="bg-white p-4 rounded shadow lg:col-span-2">
          <h2 class="text-lg font-bold mb-4">Attendance (Last 30 Days)</h2>
          <canvas id="attendanceChart"></canvas>
        </div>

        <!-- Upcoming Interviews -->
        <div class="bg-white p-4 rounded shadow">
          <h2 class="text-lg font-bold mb-4">Upcoming Interviews</h2>
          <ul class="space-y-3 text-sm">
            <li v-if="interviews.length === 0" class="text-gray-500">No upcoming interviews</li>
            <li v-for="i in interviews" :key="i.id" class="border p-2 rounded">
              <div class="font-medium">{{ i.job_title }}</div>
              <div class="text-xs text-gray-600">{{ i.employer_name }} — {{ formatDate(i.scheduled_at) }}</div>
              <div v-if="i.meet_link" class="mt-2"><a :href="i.meet_link" target="_blank" class="text-blue-600">Join Meeting</a></div>
            </li>
          </ul>
        </div>
      </div>

      <!-- Timeline & Applications -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded shadow lg:col-span-2">
          <h2 class="text-lg font-bold mb-4">Work History & Activity</h2>
          <ul class="space-y-3 text-sm">
            <li v-if="timeline.length === 0" class="text-gray-500">No activity yet</li>
            <li v-for="t in timeline" :key="t.date + t.type" class="border p-3 rounded">
              <div class="text-xs text-gray-500">{{ t.date }}</div>
              <div class="font-medium">{{ humanType(t.type) }}</div>
              <pre class="text-xs mt-1 text-gray-700">{{ JSON.stringify(t.data, null, 2) }}</pre>
            </li>
          </ul>
        </div>

        <div class="bg-white p-4 rounded shadow">
          <h2 class="text-lg font-bold mb-4">Your Applications</h2>
          <ul class="text-sm space-y-2">
            <li v-if="applications.length === 0" class="text-gray-500">No applications yet</li>
            <li v-for="a in applications" :key="a.id" class="border p-2 rounded">
              <div class="font-medium">{{ a.jobListing?.title ?? 'Job' }}</div>
              <div class="text-xs text-gray-600">Status: <span class="font-semibold">{{ a.status }}</span></div>
            </li>
          </ul>
        </div>
      </div>

      <!-- Upload modal (simple inline) -->
      <div v-if="showUpload" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded shadow w-full max-w-md">
          <h3 class="font-semibold mb-3">Upload Documents</h3>
          <input ref="files" type="file" multiple class="mb-3" />
          <div class="flex items-center space-x-2">
            <button @click="submitUpload" :disabled="uploading" class="bg-indigo-600 text-white px-3 py-2 rounded">{{ uploading ? 'Uploading...' : 'Upload' }}</button>
            <button @click="closeUpload" class="px-3 py-2 rounded border">Cancel</button>
            <span class="text-sm text-green-600" v-if="uploadMessage">{{ uploadMessage }}</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
import { Inertia } from '@inertiajs/inertia'
Chart.register(...registerables)

const totals = ref({ applications: 0, interviews: 0, documents: 0, attendance: 0 })
const applications = ref([])
const interviews = ref([])
const attendance = ref([])
const timeline = ref([])
const uploadMessage = ref('')
const uploading = ref(false)
const showUpload = ref(false)

function logout(){ Inertia.post('/logout') }
function openUpload(){ showUpload.value = true }
function closeUpload(){ showUpload.value = false; uploadMessage.value = '' }

function humanType(type){
  switch(type){
    case 'rating': return 'Employer Rating'
    case 'work_output': return 'Work Output'
    case 'interview': return 'Interview'
    case 'application': return 'Application'
    default: return type
  }
}

function formatDate(s){
  try{ return new Date(s).toLocaleString() }catch(e){ return s }
}

let attendanceChart = null

async function loadData(){
  try{
    const [appsRes, intRes, attRes, thRes] = await Promise.all([
      axios.get('/beneficiary/applications'),
      axios.get('/beneficiary/upcoming-interviews'),
      axios.get('/beneficiary/analytics/attendance'),
      axios.get('/beneficiary/work-history')
    ])

    applications.value = appsRes.data.applications ?? []
    interviews.value = intRes.data ?? []
    attendance.value = attRes.data ?? []
    timeline.value = thRes.data.timeline ?? []

    totals.value.applications = applications.value.length
    totals.value.interviews = interviews.value.length
    // documents count: infer from user props if available
    totals.value.documents = (window?.__INITIAL_PAGE__?.props?.auth?.user?.documents ?? []).length || 0

    // compute attendance average
    const avg = attendance.value.length ? Math.round(attendance.value.reduce((s,r)=>s+Number(r.percentage),0)/attendance.value.length) : 0
    totals.value.attendance = avg

    renderAttendanceChart()
  }catch(e){
    console.error('Failed to load beneficiary dashboard data', e)
  }
}

function renderAttendanceChart(){
  const ctx = document.getElementById('attendanceChart')?.getContext('2d')
  if(!ctx) return
  if(attendanceChart) attendanceChart.destroy()
  const labels = attendance.value.map(r=>r.date)
  const data = attendance.value.map(r=>Number(r.percentage))
  attendanceChart = new Chart(ctx, {
    type: 'line',
    data: { labels, datasets: [{ label: 'Attendance %', data, borderColor: '#3B82F6', backgroundColor: 'rgba(59,130,246,0.2)', fill: true }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 100 } } }
  })
}

async function submitUpload(){
  uploadMessage.value = ''
  const input = $refs?.files
  const files = input?.files
  if(!files || files.length === 0) { uploadMessage.value = 'Please choose files'; return }

  const fd = new FormData()
  for(let i=0;i<files.length;i++) fd.append('documents[]', files[i])
  uploading.value = true
  try{
    const res = await axios.post('/beneficiary/upload-documents', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    uploadMessage.value = res.data.message || 'Uploaded'
    // close modal after small delay
    setTimeout(()=>{ closeUpload(); loadData() }, 800)
  }catch(e){
    uploadMessage.value = e?.response?.data?.message ?? 'Upload failed'
    console.error('Upload failed', e)
  }finally{ uploading.value = false }
}

onMounted(()=>{ loadData() })
</script>

<style scoped>
/* minor tweaks */
</style>
