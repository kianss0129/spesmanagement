<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Upload Work Output</h1>
    <div>
      <label>Beneficiary ID</label>
      <input v-model="beneficiaryId" class="border px-2 py-1" />
    </div>
    <div class="mt-2">
      <input type="file" multiple @change="onFiles" />
    </div>
    <div class="mt-2">
      <button @click="submit" class="bg-blue-600 text-white px-3 py-1 rounded">Upload</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
const beneficiaryId = ref('')
const files = ref([])
const onFiles = (e) => { files.value = Array.from(e.target.files || []) }
const submit = async () => {
  if (!beneficiaryId.value) return alert('Beneficiary id required')
  if (!files.value.length) return alert('Select files')
  const form = new FormData()
  form.append('beneficiary_id', beneficiaryId.value)
  files.value.forEach(f => form.append('files[]', f))
  const res = await axios.post('/employer/work-output', form, { headers: { 'Content-Type': 'multipart/form-data' } })
  alert(res.data.message)
}
</script>
