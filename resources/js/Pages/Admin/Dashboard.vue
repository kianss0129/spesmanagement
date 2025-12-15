<template>
  <div class="p-6 min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto">
      <h1 class="text-2xl font-bold mb-4">Admin Dashboard</h1>
      <div class="bg-white p-4 rounded shadow">
        <div>Total Users: {{ stats.users }}</div>
        <div>Total Beneficiaries: {{ stats.beneficiaries }}</div>
        <div>Total Employers: {{ stats.employers }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const stats = ref({ users: 0, beneficiaries: 0, employers: 0 });

onMounted(async () => {
  try {
    const res = await axios.get('/admin/stats');
    stats.value = res.data;
  } catch (e) {
    console.error('Failed to load admin stats', e)
  }
});
</script>
  