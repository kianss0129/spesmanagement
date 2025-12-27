<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Submit Report</h1>
    <div>
      <input v-model="title" placeholder="Title" class="border px-2 py-1 w-full" />
    </div>
    <div class="mt-2">
      <textarea v-model="body" class="border w-full h-48"></textarea>
    </div>
    <div class="mt-2">
      <button @click="submit" class="bg-blue-600 text-white px-3 py-1 rounded">Submit</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
const title = ref('')
const body = ref('')
const submit = async () => {
  const res = await axios.post('/employer/reports', { title: title.value, body: body.value })
  alert(res.data.message)
}
</script>
