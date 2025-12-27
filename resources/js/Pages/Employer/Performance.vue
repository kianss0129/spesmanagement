<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Performance Evaluation</h1>
    <div>
      <label>Beneficiary ID:</label>
      <input v-model="beneficiaryId" class="border px-2 py-1" />
      <button @click="submit" class="ml-2 bg-blue-600 text-white px-3 py-1 rounded">Submit</button>
    </div>
    <div class="mt-4">
      <label>Overall (1-5)</label>
      <input v-model.number="overall" type="number" min="1" max="5" class="border px-2 py-1" />
    </div>
    <div class="mt-2">
      <label>Comment</label>
      <textarea v-model="comment" class="border w-full"></textarea>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
const beneficiaryId = ref('')
const overall = ref(5)
const comment = ref('')
const submit = async () => {
  if (!beneficiaryId.value) return alert('Enter beneficiary id')
  const res = await axios.post('/employer/jobs/0/rate/' + beneficiaryId.value, { // job id not required here
    employer_id: 0,
    beneficiary_id: beneficiaryId.value,
    overall: overall.value,
    comment: comment.value
  })
  alert(res.data.message)
}
</script>
