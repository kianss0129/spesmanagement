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

      <!-- Analytics Chart -->
      <div class="bg-white p-4 rounded shadow mb-6">
        <h2 class="font-semibold mb-2">Applicants per Job</h2>
        <canvas ref="applicantsCanvas" height="140"></canvas>
      </div>

      <!-- Dashboard Cards -->
      <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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

// Canvas ref for chart
const applicantsCanvas = ref(null)

// Load analytics chart
onMounted(async () => {
  try {
    const res = await axios.get('/employer/analytics/applicants-per-job')
    const jobTitles = res.data.map(j => j.title)
    const applicants = res.data.map(j => j.total)

    if (applicantsCanvas.value) {
      new Chart(applicantsCanvas.value.getContext('2d'), {
        type: 'bar',
        data: {
          labels: jobTitles,
          datasets: [
            { label: 'Applicants', data: applicants, backgroundColor: '#3b82f6' }
          ]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } }
        }
      })
    }
  } catch (e) {
    console.error('Failed to load employer analytics', e)
  }
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
