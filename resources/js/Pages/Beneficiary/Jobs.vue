<template>
  <div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">Job Vacancies</h1>
    <div v-if="jobs.length === 0" class="text-gray-500">No job listings available.</div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="job in jobs" :key="job.id" class="bg-white p-4 rounded shadow">
        <h3 class="font-medium">{{ job.title }}</h3>
        <p class="text-sm text-gray-600">{{ job.description }}</p>
        <div class="mt-3 flex items-center justify-between">
          <div class="text-xs text-gray-500">{{ job.employer?.name || 'Employer' }}</div>
          <button @click="apply(job.id)" class="bg-green-500 text-white px-3 py-1 rounded">Apply</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { Inertia } from '@inertiajs/inertia'

const jobs = ref([])

onMounted(async () => {
  try {
    const res = await axios.get('/beneficiary/jobs')
    jobs.value = res.data
  } catch (e) {
    console.error('Failed to fetch jobs', e)
  }
})

const apply = async (jobId) => {
  try {
    await axios.post('/beneficiary/applications', { job_listing_id: jobId })
    alert('Application submitted')
  } catch (e) {
    if (e.response && e.response.status === 401) {
      Inertia.visit('/login')
      return
    }
    alert('Failed to apply')
  }
}
</script>
