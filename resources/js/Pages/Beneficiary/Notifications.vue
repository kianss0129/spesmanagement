<template>
  <div class="p-6 max-w-5xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Notifications & Announcements</h1>

    <div v-if="notifications.length === 0" class="text-gray-500">
      No notifications yet.
    </div>

    <div
      v-for="n in notifications"
      :key="n.id"
      class="bg-white p-4 rounded shadow mb-3"
    >
      <div class="flex justify-between">
        <h2 class="font-semibold">{{ n.title }}</h2>
        <span class="text-xs text-gray-500">{{ formatDate(n.created_at) }}</span>
      </div>
      <p class="text-sm text-gray-700 mt-2">{{ n.message }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const notifications = ref([])

function formatDate(d){
  return new Date(d).toLocaleString()
}

onMounted(async () => {
  const res = await axios.get('/beneficiary/notifications')
  notifications.value = res.data ?? []
})
</script>
