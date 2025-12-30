<template>
  <div class="p-6 min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto">
      <!-- Header -->
      <header class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Upcoming Interviews</h1>
        <button
          @click="logout"
          class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition"
        >
          Logout
        </button>
      </header>

      <!-- Interview List -->
      <section class="space-y-4">
        <div
          v-if="interviews.length === 0"
          class="text-gray-500 text-center py-10"
        >
          No upcoming interviews.
        </div>

        <div
          v-for="interview in interviews"
          :key="interview.id"
          class="bg-white p-4 rounded-lg shadow flex flex-col sm:flex-row justify-between items-start sm:items-center"
        >
          <div>
            <h3 class="font-medium text-gray-800">{{ interview.job_title }}</h3>
            <p class="text-gray-600">Scheduled: {{ formatDate(interview.scheduled_at) }}</p>
            <p class="text-gray-600">Employer: {{ interview.employer_name }}</p>
          </div>
          <div class="mt-3 sm:mt-0">
            <a
              v-if="interview.meet_link"
              :href="interview.meet_link"
              target="_blank"
              class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition"
            >
              Join Google Meet
            </a>
            <span v-else class="text-gray-500">No Meet link yet</span>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { Inertia } from '@inertiajs/inertia'

const interviews = ref([])

// Logout function
const logout = () => Inertia.post('/logout')

// Format datetime nicely
const formatDate = (datetime) => {
  return new Date(datetime).toLocaleString(undefined, {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Fetch upcoming interviews for the logged-in beneficiary
const fetchInterviews = async () => {
  try {
    const res = await axios.get('/beneficiary/upcoming-interviews')
    // Backend should return an array like: [{id, job_title, employer_name, scheduled_at, meet_link}, ...]
    interviews.value = res.data
  } catch (e) {
    console.error('Failed to fetch interviews:', e)
  }
}

onMounted(() => {
  fetchInterviews()
})
</script>

<style scoped>
/* Optional hover effects */
</style>
