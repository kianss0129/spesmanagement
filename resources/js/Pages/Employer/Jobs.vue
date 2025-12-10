<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Job Listings</h1>
    <div v-for="job in jobs" :key="job.id" class="border p-4 rounded mb-2">
      <h2 class="font-semibold">{{ job.title }}</h2>
      <p>{{ job.description }}</p>
      <p>Status: {{ job.status }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const jobs = ref([]);

onMounted(async () => {
  const res = await axios.get('/employer/jobs');
  jobs.value = res.data;
});
</script>
