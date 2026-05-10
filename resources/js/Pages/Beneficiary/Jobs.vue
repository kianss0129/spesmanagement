<template>
  <div class="p-6">

    <!-- HEADER WITH BACK BUTTON -->
    <div class="flex items-center gap-3 mb-4">

      <!-- BACK BUTTON -->
      <button
        @click="goBack"
        class="flex items-center gap-2 px-3 py-2 bg-white shadow rounded-xl hover:bg-gray-100 transition"
      >
        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-5 h-5 text-gray-600"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor">
          <path stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 19l-7-7 7-7" />
        </svg>

        <span class="text-sm font-medium text-gray-700">
          Back
        </span>
      </button>

      <h1 class="text-2xl font-semibold">Job Vacancies</h1>
    </div>

    <!-- EMPTY STATE -->
    <div v-if="jobs.length === 0" class="text-gray-500">
      No job listings available.
    </div>

    <!-- GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

      <div
        v-for="job in jobs"
        :key="job.id"
        class="bg-white p-5 rounded-xl shadow hover:shadow-md transition"
      >

        <!-- HEADER -->
        <div class="flex justify-between items-start">
          <div>
            <h3 class="font-bold text-lg">{{ job.title }}</h3>
            <p class="text-xs text-gray-500">
              {{ job.employer?.name || 'Employer' }}
            </p>
          </div>

          <span
            class="text-xs px-3 py-1 rounded-full font-semibold"
            :class="statusClass(job)"
          >
            {{ getStatus(job) }}
          </span>
        </div>

        <p class="text-sm text-gray-600 mt-3">
          {{ job.description }}
        </p>

        <div class="mt-4 text-sm space-y-1 text-gray-700">

          <p>📍 Location: <span class="font-medium">{{ job.location }}</span></p>
          <p>💼 Type: <span class="font-medium">{{ job.type }}</span></p>
        <p>👥 Slots: 
  <span class="font-medium">
    {{ availableSlots(job) }} / {{ displaySlots(job) }}
  </span>
</p>
          <p>📅 Closing: <span class="font-medium">{{ job.closing_date }}</span></p>

        </div>

        <div class="mt-4 text-xs text-gray-400 italic">
          Applications are managed by PESO
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'



function goBack() {
  window.history.back()
}

const jobs = ref([])
let refreshInterval = null

async function loadJobs() {
  try {
    const res = await axios.get('/api/beneficiary/jobs')
    jobs.value = res.data
  } catch (e) {
    console.error('Failed to fetch jobs', e)
  }
}

onMounted(async () => {
  await loadJobs()
  refreshInterval = setInterval(loadJobs, 10000)
})

onBeforeUnmount(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
    refreshInterval = null
  }
})

const displaySlots = (job) => {
  return Number(job.slots) || 0
}

const availableSlots = (job) => {
  const slots = Number(job.slots) || 0
  const assigned = Number(job.assigned_count) || 0
  const remaining = slots - assigned
  return remaining >= 0 ? remaining : 0
}

/* STATUS LOGIC */
const getStatus = (job) => {
  const slots = Number(job.slots) || 0
  const assigned = Number(job.assigned_count) || 0

  if (assigned >= slots && slots > 0) return 'FULL'
  const today = new Date().toISOString().split('T')[0]
  if (job.closing_date < today) return 'CLOSED'
  return 'OPEN'
}

const statusClass = (job) => {
  const status = getStatus(job)

  if (status === 'OPEN') return 'bg-green-100 text-green-700'
  if (status === 'FULL') return 'bg-red-100 text-red-600'
  return 'bg-gray-200 text-gray-600'
}
</script>