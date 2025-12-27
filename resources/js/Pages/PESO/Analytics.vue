<template>
  <div class="relative p-6 min-h-screen bg-gray-50">
    <!-- Logout Button in Top Right -->
    <div class="absolute top-6 right-6">
      <form @submit.prevent="logout">
        <button
          type="submit"
          class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition"
        >
          Logout
        </button>
      </form>
    </div>

    <div class="max-w-7xl mx-auto space-y-8">
      <!-- Header -->
      <header class="mb-6">
        <h1 class="text-2xl font-semibold">Officials / PESO Dashboard</h1>
        <p class="text-sm text-gray-600">Overview of SPES Management</p>
      </header>

      <!-- Core Functions -->
      <section>
        <h2 class="text-lg font-medium mb-3">Core Functions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <FeatureCard title="Official list of SPES beneficiaries" />
          <FeatureCard title="Application status management" />
          <FeatureCard title="Contract details" />
          <FeatureCard title="Referral documents" />
          <FeatureCard title="Evaluation and monitoring records" />
        </div>
      </section>

      <!-- Analytics Section -->
      <section>
        <h2 class="text-lg font-medium mb-3">Analytics</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Applicants by School -->
          <div class="bg-white rounded shadow p-4">
            <h3 class="font-medium text-gray-700 mb-3">Applicants by School</h3>
            <canvas id="applicantsBySchoolChart"></canvas>
          </div>

          <!-- Applicant Trends -->
          <div class="bg-white rounded shadow p-4">
            <h3 class="font-medium text-gray-700 mb-3">Applicant Trends</h3>
            <canvas id="applicantTrendsChart"></canvas>
          </div>

          <!-- Top Employers -->
          <div class="bg-white rounded shadow p-4 md:col-span-2">
            <h3 class="font-medium text-gray-700 mb-3">Top Performing Employers</h3>
            <canvas id="topEmployersChart"></canvas>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
Chart.register(...registerables)

// Feature card component
const FeatureCard = {
  props: ['title'],
  template: `
    <div class="bg-white rounded-lg shadow p-4 cursor-pointer hover:shadow-md transition">
      <h3 class="font-medium text-gray-700">{{ title }}</h3>
    </div>
  `
}

// Logout
const logout = () => {
  Inertia.post('/logout')
}

// Chart Data
const analyticsData = ref({
  applicantsBySchool: [],
  applicantTrends: { labels: [], data: [] },
  topEmployers: []
})

onMounted(async () => {
  try {
    const res = await axios.get('/peso/analytics/dashboard') // create this API endpoint in controller
    analyticsData.value = res.data

    // Applicants by School Chart
    const ctxSchool = document.getElementById('applicantsBySchoolChart').getContext('2d')
    new Chart(ctxSchool, {
      type: 'bar',
      data: {
        labels: analyticsData.value.applicantsBySchool.map(i => i.school_name),
        datasets: [{
          label: 'Applicants',
          data: analyticsData.value.applicantsBySchool.map(i => i.total),
          backgroundColor: '#3B82F6'
        }]
      },
      options: { responsive: true }
    })

    // Applicant Trends Chart
    const ctxTrends = document.getElementById('applicantTrendsChart').getContext('2d')
    new Chart(ctxTrends, {
      type: 'line',
      data: {
        labels: analyticsData.value.applicantTrends.labels,
        datasets: [{
          label: 'Applicants',
          data: analyticsData.value.applicantTrends.data,
          borderColor: '#10B981',
          fill: false
        }]
      },
      options: { responsive: true }
    })

    // Top Employers Chart
    const ctxEmployers = document.getElementById('topEmployersChart').getContext('2d')
    new Chart(ctxEmployers, {
      type: 'bar',
      data: {
        labels: analyticsData.value.topEmployers.map(i => i.employer_name),
        datasets: [{
          label: 'Total Hires',
          data: analyticsData.value.topEmployers.map(i => i.total),
          backgroundColor: '#F59E0B'
        }]
      },
      options: { responsive: true }
    })

  } catch (error) {
    console.error('Failed to load analytics', error)
  }
})
</script>
