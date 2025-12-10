<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">My Dashboard</h1>

    <div class="bg-white p-4 rounded shadow mb-6">
      <h2 class="font-semibold mb-2">Attendance Compliance</h2>
      <canvas id="attendanceChart"></canvas>
    </div>
  </div>

  <div v-for="interview in interviews" :key="interview.id" class="card">
    <p>Scheduled: {{ interview.scheduled_at }}</p>

    <a :href="interview.meet_link" target="_blank"
       class="btn btn-success">
       Join Interview
    </a>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';

onMounted(async () => {
  const res = await axios.get('/beneficiary/analytics/attendance');
  const dates = res.data.map(d => d.date);
  const compliance = res.data.map(d => d.percentage);

  new Chart(document.getElementById('attendanceChart'), {
    type: 'line',
    data: {
      labels: dates,
      datasets: [{
        label: 'Attendance %',
        data: compliance,
        borderColor: '#10B981',
        backgroundColor: 'rgba(16,185,129,0.2)',
        tension: 0.3,
        fill: true
      }]
    },
    options: { responsive: true }
  });
});
</script>
