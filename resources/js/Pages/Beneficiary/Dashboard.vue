<template>
  <div class="p-6 min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <header class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-semibold">Beneficiary Dashboard</h1>
          <p class="text-sm text-gray-600">Overview</p>
        </div>
        <div>
          <!-- Logout Button -->
          <form @submit.prevent="logout" method="POST">
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
              Logout
            </button>
          </form>
        </div>
      </header>

      <!-- Attendance + Upcoming Interviews -->
      <section class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-medium mb-3">Attendance Compliance (Last 30 days)</h2>
          <canvas ref="attendanceCanvas" height="140"></canvas>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-medium mb-3">Upcoming Interviews</h2>
          <ul class="space-y-3">
            <li v-for="iv in interviews" :key="iv.id" class="border rounded p-3">
              <div class="flex justify-between items-center">
                <div>
                  <div class="font-medium">{{ iv.title }}</div>
                  <div class="text-xs text-gray-500">{{ iv.scheduled_at }}</div>
                </div>
                <div>
                  <a v-if="iv.meet_link" :href="iv.meet_link" target="_blank" class="text-sm text-blue-600 hover:underline">
                    Join
                  </a>
                </div>
              </div>
            </li>
            <li v-if="!interviews.length" class="text-sm text-gray-500">No upcoming interviews.</li>
          </ul>
        </div>
      </section>

      <!-- Beneficiary Features -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <FeatureCard title="Profile" />
        <FeatureCard title="Application" />
        <FeatureCard title="Upload Documents" />
        <FeatureCard title="Requirements Submission" />
        <FeatureCard title="Announcement / Schedule" />
        <FeatureCard title="Notification of Exam / Qualification" />
        <FeatureCard title="Submission of Attendance / Report" />
        <FeatureCard title="View Job Vacancy" />
        <FeatureCard title="Interview Schedule + Google Meet Link" />
        <FeatureCard title="View Employer Ratings Received" />
      </section>

      <!-- Employer Features (Optional) -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <FeatureCard title="Post Job Listing" />
        <FeatureCard title="View Applicant Ratings" />
        <FeatureCard title="View Recommended Candidates from PESO" />
        <FeatureCard title="Choose Applicant (if allowed)" />
        <FeatureCard title="Daily Time Record (DTR) Submission" />
        <FeatureCard title="Attendance" />
        <FeatureCard title="Performance Evaluation per Beneficiary" />
        <FeatureCard title="Actual Work Output Uploading" />
        <FeatureCard title="Work Schedule Management" />
        <FeatureCard title="Employer Report Submission" />
        <FeatureCard title="Generate Google Meet Interview Link" />
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineComponent } from 'vue'
import axios from 'axios'
import Chart from 'chart.js/auto'
import { Inertia } from '@inertiajs/inertia'

const attendanceCanvas = ref(null)
const interviews = ref([])

onMounted(async () => {
  try {
    const res = await axios.get('/beneficiary/analytics/attendance')
    const dates = res.data.map(d => d.date)
    const compliance = res.data.map(d => d.percentage)

    interviews.value = (await axios.get('/beneficiary/upcoming-interviews')).data || []

    if (attendanceCanvas.value) {
      new Chart(attendanceCanvas.value.getContext('2d'), {
        type: 'line',
        data: {
          labels: dates,
          datasets: [{
            label: 'Attendance %',
            data: compliance,
            borderColor: '#10B981',
            backgroundColor: 'rgba(16,185,129,0.2)',
            tension: 0.3,
            fill: true
          }]
        },
        options: { responsive: true, maintainAspectRatio: false }
      })
    }
  } catch (e) {
    console.error('Failed to load attendance analytics', e)
  }
})

// Feature card component
const FeatureCard = defineComponent({
  props: { title: String },
  template: `
    <div class="bg-white rounded-lg shadow p-4 cursor-pointer hover:shadow-md transition">
      <h3 class="font-medium text-gray-700">{{ title }}</h3>
    </div>
  `
})

// Logout function using Inertia
const logout = () => {
  Inertia.post('/logout')
}
</script>
