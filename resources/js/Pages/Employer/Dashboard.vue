<template>
  <div class="p-6 min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto">
      <h1 class="text-2xl font-bold mb-4">Employer Dashboard</h1>

      <div class="bg-white p-4 rounded shadow mb-6">
        <h2 class="font-semibold mb-2">Applicants per Job</h2>
        <canvas ref="applicantsCanvas" height="140"></canvas>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Chart from 'chart.js/auto'

const applicantsCanvas = ref(null)

onMounted(async () => {
  try {
    const res = await axios.get('/employer/analytics/applicants-per-job')
    const jobTitles = res.data.map(j => j.title)
    const applicants = res.data.map(j => j.total)

    if (applicantsCanvas.value) {
      new Chart(applicantsCanvas.value.getContext('2d'), {
        type: 'bar',
        data: { labels: jobTitles, datasets: [{ label: 'Applicants', data: applicants }] },
        options: { responsive: true, plugins: { legend: { display: false } } }
      })
    }
  } catch (e) {
    console.error('Failed to load employer analytics', e)
  }
})
</script>
