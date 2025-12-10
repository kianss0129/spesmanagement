<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Admin Dashboard</h1>
    <div>Total Users: {{ stats.users }}</div>
    <div>Total Beneficiaries: {{ stats.beneficiaries }}</div>
    <div>Total Employers: {{ stats.employers }}</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const stats = ref({ users: 0, beneficiaries: 0, employers: 0 });

onMounted(async () => {
  const res = await axios.get('/admin/stats');
  stats.value = res.data;
});
</script>
