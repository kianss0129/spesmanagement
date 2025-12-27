<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Interviews / Schedule</h1>
    <div>
      <button @click="load" class="bg-blue-600 text-white px-3 py-1 rounded">Load Interviews</button>
    </div>
    <div class="mt-4 space-y-3">
      <div v-for="i in interviews" :key="i.id" class="bg-white p-3 rounded shadow">
        <div class="font-semibold">{{ i.job_listing?.title || 'Job' }} — {{ i.scheduled_at }}</div>
        <div class="text-sm">Meet Link: <a :href="i.meet_link" target="_blank">{{ i.meet_link }}</a></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
const interviews = ref([])
const load = async () => {
  const res = await axios.get('/employer/interviews')
  interviews.value = res.data
}
</script>
