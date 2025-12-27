<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Applicants</h1>
    <div>
      <label>Job ID:</label>
      <input v-model="jobId" class="border px-2 py-1" />
      <button @click="load" class="ml-2 bg-blue-600 text-white px-3 py-1 rounded">Load</button>
    </div>

    <div v-if="applications.length" class="mt-4">
      <div v-for="a in applications" :key="a.id" class="bg-white p-3 mb-2 rounded shadow">
        <div class="font-semibold">{{ a.beneficiary?.name || 'Unknown' }}</div>
        <div class="text-sm text-gray-600">Applied at: {{ a.created_at }}</div>
        <div class="mt-2">
          <button @click="viewRatings(a.beneficiary_id)" class="text-sm text-blue-600">View ratings</button>
          <button @click="choose(a.id)" class="ml-2 text-sm text-green-600">Select</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const jobId = ref('')
const applications = ref([])

const load = async () => {
  if (!jobId.value) return alert('Enter job id')
  const res = await axios.get(`/employer/jobs/${jobId.value}/applicants`)
  applications.value = res.data
}

const viewRatings = async (beneficiaryId) => {
  const res = await axios.get(`/employer/applicants/${beneficiaryId}/ratings`)
  alert(JSON.stringify(res.data, null, 2))
}

const choose = async (appId) => {
  const res = await axios.post(`/employer/jobs/${jobId.value}/choose/${appId}`)
  alert(res.data.message)
}
</script>
