<template>
  <div class="p-6 max-w-5xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Attendance & DTR</h1>

    <!-- DTR Submission -->
    <div class="bg-white p-4 rounded shadow mb-6">
      <h2 class="font-semibold mb-3">Submit Daily Time Record</h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <input type="date" v-model="form.date" class="border rounded p-2" />
        <input type="time" v-model="form.time_in" class="border rounded p-2" />
        <input type="time" v-model="form.time_out" class="border rounded p-2" />
      </div>

      <input type="file" ref="proof" class="mt-3" />

      <button
        @click="submitDTR"
        class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded"
      >
        Submit
      </button>

      <p v-if="message" class="text-sm mt-2 text-green-600">{{ message }}</p>
    </div>

    <!-- Attendance Summary -->
    <div class="bg-white p-4 rounded shadow">
      <h2 class="font-semibold mb-3">Attendance Summary</h2>

      <table class="w-full text-sm">
        <thead>
          <tr class="text-left border-b">
            <th>Date</th>
            <th>Status</th>
            <th>Compliance</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in attendance" :key="a.id" class="border-b">
            <td>{{ a.date }}</td>
            <td>{{ a.status }}</td>
            <td>{{ a.compliance }}%</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const attendance = ref([])
const message = ref('')
const form = ref({
  date: '',
  time_in: '',
  time_out: ''
})

async function submitDTR(){
  const fd = new FormData()
  Object.entries(form.value).forEach(([k,v]) => fd.append(k,v))
  if($refs.proof?.files[0]){
    fd.append('proof', $refs.proof.files[0])
  }

  await axios.post('/beneficiary/dtr', fd)
  message.value = 'DTR submitted successfully'
}

onMounted(async () => {
  const res = await axios.get('/beneficiary/attendance')
  attendance.value = res.data ?? []
})
</script>
