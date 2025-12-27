<template>
  <div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">My Applications</h1>
    <div v-if="applications.length === 0" class="text-gray-500">You have no applications yet.</div>
    <ul class="space-y-3">
      <li v-for="app in applications" :key="app.id" class="bg-white p-4 rounded shadow">
        <div class="flex items-center justify-between">
          <div>
            <div class="font-medium">{{ app.job_listing?.title || 'Job' }}</div>
            <div class="text-xs text-gray-500">Applied: {{ new Date(app.created_at).toLocaleString() }}</div>
          </div>
          <div class="text-sm text-gray-600">Status: {{ app.status || 'Pending' }}</div>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { Inertia } from '@inertiajs/inertia'

const applications = ref([])

onMounted(async () => {
  try {
    const res = await axios.get('/beneficiary/applications')
    applications.value = res.data.applications || []
  } catch (e) {
    if (e.response && e.response.status === 401) {
      Inertia.visit('/login')
      return
    }
    console.error('Failed to load applications', e)
  }
})
</script>
