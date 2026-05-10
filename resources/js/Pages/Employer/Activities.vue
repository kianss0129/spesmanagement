<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 to-blue-200 p-6">
    <div class="max-w-4xl mx-auto">

      <!-- Header -->
      <div class="mb-6">
        <button @click="router.visit('/employer')" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center">
          ← Back to Dashboard
        </button>
        <h1 class="text-3xl font-bold text-gray-800">All Activities</h1>
        <p class="text-gray-600">Complete history of your employer activities</p>
      </div>

      <!-- Activities List -->
      <div class="bg-white rounded-2xl shadow-lg p-6">
        <div v-if="activities.length === 0" class="text-center py-12">
          <div class="text-gray-400 text-6xl mb-4">📋</div>
          <h3 class="text-xl font-semibold text-gray-600 mb-2">No Activities Yet</h3>
          <p class="text-gray-500">Your activity history will appear here once you start posting jobs and managing beneficiaries.</p>
        </div>

        <div v-else class="space-y-4">
          <div v-for="activity in activities" :key="activity.type + activity.date" class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100 hover:bg-gray-100 transition">
            <span class="text-2xl">{{ activity.icon }}</span>
            <div class="flex-1">
              <p class="font-semibold text-gray-800">{{ activity.title }}</p>
              <p class="text-gray-600 text-sm mt-1">{{ activity.description }}</p>
              <p class="text-gray-400 text-xs mt-2">{{ formatDate(activity.date) }}</p>
            </div>
          </div>
        </div>

        <!-- Load More Button (if needed in future) -->
        <div v-if="activities.length > 0 && hasMore" class="text-center mt-6">
          <button class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
            Load More Activities
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'

// ================= Data =================
const activities = ref([])
const hasMore = ref(false)

// ================= Date Formatting =================
function formatDate(dateString) {
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24))
  const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
  const diffMinutes = Math.floor(diffMs / (1000 * 60))

  if (diffMinutes < 1) return 'Just now'
  if (diffMinutes < 60) return `${diffMinutes} minutes ago`
  if (diffHours < 24) return `${diffHours} hours ago`
  if (diffDays < 7) return `${diffDays} days ago`

  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// ================= Load Activities =================
async function loadActivities() {
  try {
    const res = await axios.get('/employer/activities/data')
    activities.value = res.data || []
  } catch (err) {
    console.error('Failed to load activities', err)
    activities.value = []
  }
}

// ================= Initial Load =================
onMounted(async () => {
  await loadActivities()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>