<template>
  <div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">Upload Documents</h1>
    <form @submit.prevent="submit" class="bg-white p-4 rounded shadow space-y-3">
      <div>
        <label class="block text-sm font-medium mb-1">Select documents (pdf, jpg, png)</label>
        <input type="file" multiple @change="onFiles" />
      </div>
      <div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Upload</button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { Inertia } from '@inertiajs/inertia'

const files = ref([])

const onFiles = (e) => {
  files.value = Array.from(e.target.files || [])
}

const submit = async () => {
  if (!files.value.length) {
    alert('Select at least one file')
    return
  }

  const form = new FormData()
  files.value.forEach(f => form.append('documents[]', f))

  try {
    const res = await axios.post('/beneficiary/upload-documents', form, { headers: { 'Content-Type': 'multipart/form-data' } })
    alert(res.data.message || 'Uploaded')
  } catch (e) {
    console.error('Upload error', e)
    if (e.response) {
      if (e.response.status === 401) {
        Inertia.visit('/login')
        return
      }
      const body = e.response.data
      alert('Upload failed: ' + e.response.status + ' ' + (body?.message || JSON.stringify(body)))
      return
    }
    alert('Upload failed: network error')
  }
}
</script>
