<template>
  <div class="p-6 max-w-5xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Exams & Qualification</h1>

    <div v-if="exams.length === 0" class="text-gray-500">
      No exams or screenings assigned yet.
    </div>

    <div v-for="e in exams" :key="e.id" class="bg-white p-4 rounded shadow mb-4">
      <div class="flex justify-between">
        <div>
          <h2 class="font-semibold">{{ e.title }}</h2>
          <p class="text-sm text-gray-600">
            {{ formatDate(e.exam_date) }} • {{ e.location }}
          </p>
        </div>
        <span
          class="px-2 py-1 rounded text-xs"
          :class="statusClass(e.status)"
        >
          {{ e.status }}
        </span>
      </div>

      <div v-if="e.result" class="mt-3 text-sm">
        <strong>Result:</strong> {{ e.result }}
      </div>

      <div v-if="e.work_start_date" class="mt-2 text-sm text-green-700">
        Work Start: {{ formatDate(e.work_start_date) }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const exams = ref([])

function formatDate(d){
  return new Date(d).toLocaleString()
}

function statusClass(s){
  return {
    Qualified: 'bg-green-100 text-green-700',
    Failed: 'bg-red-100 text-red-700',
    Pending: 'bg-yellow-100 text-yellow-700'
  }[s] || 'bg-gray-100 text-gray-700'
}

onMounted(async () => {
  const res = await axios.get('/beneficiary/exams')
  exams.value = res.data ?? []
})
</script>
