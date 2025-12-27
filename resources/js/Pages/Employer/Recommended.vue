<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Recommended Candidates</h1>
    <div v-if="candidates.length" class="space-y-3">
      <div v-for="c in candidates" :key="c.id" class="bg-white p-3 rounded shadow">
        <div class="font-semibold">{{ c.name }} — Avg Rating: {{ c.ratings_avg_overall || 0 }}</div>
        <div class="text-sm text-gray-600">Email: {{ c.email }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const candidates = ref([])
onMounted(async () => {
  try {
    const res = await axios.get('/employer/recommended-candidates')
    candidates.value = res.data
  } catch (e) { console.error(e) }
})
</script>
