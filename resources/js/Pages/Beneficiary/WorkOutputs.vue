<template>
  <div class="p-6 max-w-5xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Work Outputs</h1>

    <div class="bg-white p-4 rounded shadow mb-6">
      <h2 class="font-semibold mb-3">Submit Work Output</h2>
      <input type="text" v-model="title" placeholder="Title" class="border rounded p-2 w-full mb-3" />
      <input type="file" ref="file" />
      <button @click="submit" class="mt-3 bg-indigo-600 text-white px-4 py-2 rounded">
        Upload
      </button>
    </div>

    <div class="bg-white p-4 rounded shadow">
      <h2 class="font-semibold mb-3">Submitted Outputs</h2>

      <ul class="text-sm">
        <li v-for="o in outputs" :key="o.id" class="border-b py-2">
          {{ o.title }} — <span class="text-gray-600">{{ o.status }}</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const outputs = ref([])
const title = ref('')

async function submit(){
  const fd = new FormData()
  fd.append('title', title.value)
  fd.append('file', $refs.file.files[0])
  await axios.post('/beneficiary/work-outputs', fd)
}

onMounted(async () => {
  const res = await axios.get('/beneficiary/work-outputs')
  outputs.value = res.data ?? []
})
</script>
