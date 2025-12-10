<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">PESO Analytics</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Applicants per Month Chart -->
      <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Applicants per Month</h2>
        <canvas id="applicantsChart"></canvas>
      </div>

      <!-- Top Hiring Employers Chart -->
      <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Top Hiring Employers</h2>
        <canvas id="employersChart"></canvas>
      </div>

      <!-- Performance Ratings per Employer -->
      <div class="bg-white p-4 rounded shadow col-span-1 md:col-span-2">
        <h2 class="font-semibold mb-2">Performance Ratings</h2>
        <canvas id="ratingsChart"></canvas>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import Chart from 'chart.js/auto';
import axios from 'axios';

onMounted(async () => {
  // 1️⃣ Applicants per Month
  const res1 = await axios.get('/peso/analytics/applicants-by-month');
  const months = res1.data.map(r => r.month);
  const totals = res1.data.map(r => r.total);

  new Chart(document.getElementById('applicantsChart'), {
    type: 'bar',
    data: {
      labels: months,
      datasets: [{
        label: 'Applicants',
        data: totals,
        backgroundColor: '#3B82F6'
      }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
  });

  // 2️⃣ Top Hiring Employers
  const res2 = await axios.get('/peso/analytics/top-employers');
  const employers = res2.data.map(e => e.name);
  const hires = res2.data.map(e => e.hires);

  new Chart(document.getElementById('employersChart'), {
    type: 'bar',
    data: {
      labels: employers,
      datasets: [{
        label: 'Hires',
        data: hires,
        backgroundColor: '#10B981'
      }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
  });

  // 3️⃣ Performance Ratings per Employer
  const res3 = await axios.get('/peso/analytics/performance-trends');
  const employerNames = res3.data.map(r => r.employer.name);
  const avgRatings = res3.data.map(r => r.avg_rating);

  new Chart(document.getElementById('ratingsChart'), {
    type: 'line',
    data: {
      labels: employerNames,
      datasets: [{
        label: 'Average Rating',
        data: avgRatings,
        borderColor: '#F59E0B',
        backgroundColor: 'rgba(245,158,11,0.2)',
        tension: 0.3,
        fill: true
      }]
    },
    options: { responsive: true }
  });
});
</script>
