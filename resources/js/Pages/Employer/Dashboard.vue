<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Employer Dashboard</h1>

    <div class="bg-white p-4 rounded shadow mb-6">
      <h2 class="font-semibold mb-2">Applicants per Job</h2>
      <canvas id="applicantsJobChart"></canvas>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';

onMounted(async () => {
  const res = await axios.get('/employer/analytics/applicants-per-job');
  const jobTitles = res.data.map(j => j.title);
  const applicants = res.data.map(j => j.total);

  new Chart(document.getElementById('applicantsJobChart'), {
    type: 'bar',
    data: { labels: jobTitles, datasets: [{ label: 'Applicants', data: applicants, backgroundColor: '#3B82F6' }] },
    options: { responsive: true, plugins: { legend: { display: false } } }
  });
});
</script>
