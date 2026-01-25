<template>
  <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
    <div class="bg-white p-4 rounded shadow">
      <div class="text-gray-500">Total Users</div>
      <div class="text-2xl font-bold">{{ stats.users }}</div>
    </div>
    <div class="bg-white p-4 rounded shadow">
      <div class="text-gray-500">Total Beneficiaries</div>
      <div class="text-2xl font-bold">{{ stats.beneficiaries }}</div>
    </div>
    <div class="bg-white p-4 rounded shadow">
      <div class="text-gray-500">Total Employers</div>
      <div class="text-2xl font-bold">{{ stats.employers }}</div>
    </div>
    <div v-if="showPesoStats" class="bg-white p-4 rounded shadow">
      <div class="text-gray-500">PESO Users</div>
      <div class="text-2xl font-bold">{{ stats.peso_users ?? 0 }}</div>
    </div>
    <div class="bg-white p-4 rounded shadow">
      <div class="text-gray-500">Assigned Beneficiaries</div>
      <div class="text-2xl font-bold">{{ stats.assigned_beneficiaries ?? 0 }}</div>
    </div>
    <div class="bg-white p-4 rounded shadow">
      <div class="text-gray-500">Upcoming Interviews</div>
      <div class="text-2xl font-bold">{{ stats.upcoming_interviews ?? 0 }}</div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  stats: Object,
  showPesoStats: {
    type: Boolean,
    default: true
  }
})
</script>