<template>
  <div class="relative p-6 min-h-screen bg-gray-50">
    <!-- Logout Button -->
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
          <Chart
            chart-id="applicantsBySchoolChart"
            title="Applicants by School"
            :data="applicantsBySchool"
            type="bar"
            :period-options="['month','semester','year']"
            @period-change="fetchApplicantsBySchool"
          />

          <!-- Performance Trends -->
          <Chart
            chart-id="performanceTrendsChart"
            title="Performance Trends"
            :data="performanceTrends"
            type="line"
            :period-options="['month','semester','year']"
            @period-change="fetchPerformanceTrends"
          />

          <!-- Top Employers -->
          <Chart
            chart-id="topEmployersChart"
            title="Top Performing Employers"
            :data="topEmployers"
            type="bar"
          />
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import axios from 'axios'
import Chart from '@/Components/Chart.vue'

// Feature Card component
const FeatureCard = {
  props: ['title'],
  template: `
    <div class="bg-white rounded-lg shadow p-4 cursor-pointer hover:shadow-md transition">
      <h3 class="font-medium text-gray-700">{{ title }}</h3>
    </div>
  `
}

// Logout
const logout = () => Inertia.post('/logout')

// Reactive chart data
const applicantsBySchool = ref({ labels: [], datasets: [{ label: 'Applicants', data: [], backgroundColor: '#3B82F6' }] })
const performanceTrends = ref({ labels: [], datasets: [{ label: 'Average Rating', data: [], borderColor: '#10B981', fill: false }] })
const topEmployers = ref({ labels: [], datasets: [{ label: 'Total Hires', data: [], backgroundColor: '#F59E0B' }] })

// Fetch Applicants by School
const fetchApplicantsBySchool = async (period = 'year') => {
  try {
    const res = await axios.get(`/peso/analytics/applicants-by-school?period=${period}`)
    applicantsBySchool.value.labels = res.data.map(i => i.school)
    applicantsBySchool.value.datasets[0].data = res.data.map(i => i.total)
  } catch (e) { console.error(e) }
}

// Fetch Performance Trends
const fetchPerformanceTrends = async (period = 'year') => {
  try {
    const res = await axios.get(`/peso/analytics/performance-trends?period=${period}`)
    performanceTrends.value.labels = res.data.map(i => i.period)
    performanceTrends.value.datasets[0].data = res.data.map(i => i.avg_rating)
  } catch (e) { console.error(e) }
}

// Fetch Top Employers
const fetchTopEmployers = async () => {
  try {
    const res = await axios.get(`/peso/analytics/top-hiring-employers`)
    topEmployers.value.labels = res.data.map(i => i.employer_name)
    topEmployers.value.datasets[0].data = res.data.map(i => i.hires)
  } catch (e) { console.error(e) }
}

// On mounted
onMounted(() => {
  fetchApplicantsBySchool()
  fetchPerformanceTrends()
  fetchTopEmployers()
})
</script>
