<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Attendance / DTR</h1>
    <div class="bg-white p-4 rounded shadow">
      <label>Beneficiary ID</label>
      <input v-model="beneficiaryId" class="border px-2 py-1" />
      <label class="ml-4">Date</label>
      <input v-model="date" type="date" class="border px-2 py-1" />
      <label class="ml-4">Time In</label>
      <input v-model="timeIn" type="time" class="border px-2 py-1" />
      <label class="ml-4">Time Out</label>
      <input v-model="timeOut" type="time" class="border px-2 py-1" />
      <button @click="submit" class="ml-4 bg-blue-600 text-white px-3 py-1 rounded">Submit</button>
    </div>

    <div class="mt-6">
      <button @click="load" class="bg-gray-200 px-3 py-1 rounded">Load recent</button>
      <div v-for="a in records" :key="a.id" class="bg-white p-3 mt-2 rounded shadow">
        <div class="font-semibold">{{ a.beneficiary?.name || a.beneficiary_id }} — {{ a.date }}</div>
        <div class="text-sm">In: {{ a.time_in }} Out: {{ a.time_out }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
const beneficiaryId = ref('')
const date = ref(new Date().toISOString().slice(0,10))
const timeIn = ref('')
const timeOut = ref('')
const records = ref([])

const submit = async () => {
  if (!beneficiaryId.value) return alert('Beneficiary id required')
  const res = await axios.post('/employer/attendance/mark', {
    beneficiary_id: beneficiaryId.value,
    date: date.value,
    time_in: timeIn.value || null,
    time_out: timeOut.value || null
  })
  alert(res.data.message)
}

const load = async () => {
  const res = await axios.get('/employer/attendance')
  records.value = res.data
}
</script>
