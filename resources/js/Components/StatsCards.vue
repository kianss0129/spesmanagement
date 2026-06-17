<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    <div class="bg-white p-4 rounded shadow">
      <div class="text-gray-500">Total Users</div>
      <div class="text-2xl font-bold">{{ statsData.users }}</div>
    </div>
    <div class="bg-white p-4 rounded shadow">
      <div class="text-gray-500">Total Beneficiaries</div>
      <div class="text-2xl font-bold">{{ statsData.beneficiaries }}</div>
    </div>
    <div class="bg-white p-4 rounded shadow">
      <div class="text-gray-500">Total Employers</div>
      <div class="text-2xl font-bold">{{ statsData.employers }}</div>
    </div>
    <div class="bg-white p-4 rounded shadow">
      <div class="text-gray-500">Pending Applications</div>
      <div class="text-2xl font-bold">{{ statsData.pending_applications }}</div>
    </div>
    <div v-if="showPesoStats" class="bg-white p-4 rounded shadow">
      <div class="text-gray-500">PESO Users</div>
      <div class="text-2xl font-bold">{{ statsData.peso_users }}</div>
    </div>
    <div class="bg-white p-4 rounded shadow">
      <div class="text-gray-500">Assigned Beneficiaries</div>
      <div class="text-2xl font-bold">{{ statsData.assigned_beneficiaries }}</div>
    </div>
    <div class="bg-white p-4 rounded shadow">
      <div class="text-gray-500">Upcoming Interviews</div>
      <div class="text-2xl font-bold">{{ statsData.upcoming_interviews }}</div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({
  stats: {
    type: Object,
    default: () => ({})
  },
  showPesoStats: {
    type: Boolean,
    default: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  }
})

const statsData = computed(() => ({
  users: Number(props.stats?.users ?? props.stats?.totalUsers ?? props.stats?.total_users ?? 0),
  beneficiaries: Number(props.stats?.beneficiaries ?? props.stats?.totalBeneficiaries ?? props.stats?.total_beneficiaries ?? 0),
  employers: Number(props.stats?.employers ?? props.stats?.totalEmployers ?? props.stats?.total_employers ?? 0),
  pending_applications: Number(props.stats?.pending_applications ?? props.stats?.pendingApplications ?? 0),
  peso_users: Number(props.stats?.peso_users ?? props.stats?.pesoUsers ?? 0),
  assigned_beneficiaries: Number(props.stats?.assigned_beneficiaries ?? props.stats?.assignedBeneficiaries ?? 0),
  upcoming_interviews: Number(props.stats?.upcoming_interviews ?? props.stats?.upcomingInterviews ?? 0),
}))
</script>