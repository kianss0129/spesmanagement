<template>
  <div
    class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6"
  >
    <!-- CARD -->
    <div
      class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-md border border-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl"
    >
      <div
        class="absolute top-0 right-0 h-24 w-24 bg-blue-100 rounded-full blur-3xl opacity-60 group-hover:scale-125 transition"
      ></div>

      <div class="relative flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">
            Total Users
          </p>

          <h2 class="mt-2 text-3xl font-extrabold text-gray-800">
            {{ statsData.users }}
          </h2>
        </div>

        <div
          class="flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-500 text-white shadow-lg"
        >
          👤
        </div>
      </div>
    </div>

    <!-- BENEFICIARIES -->
    <div
      class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-md border border-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl"
    >
      <div
        class="absolute top-0 right-0 h-24 w-24 bg-green-100 rounded-full blur-3xl opacity-60 group-hover:scale-125 transition"
      ></div>

      <div class="relative flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">
            Total Beneficiaries
          </p>

          <h2 class="mt-2 text-3xl font-extrabold text-gray-800">
            {{ statsData.beneficiaries }}
          </h2>
        </div>

        <div
          class="flex items-center justify-center w-14 h-14 rounded-2xl bg-green-500 text-white shadow-lg"
        >
          🎓
        </div>
      </div>
    </div>

    <!-- EMPLOYERS -->
    <div
      class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-md border border-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl"
    >
      <div
        class="absolute top-0 right-0 h-24 w-24 bg-purple-100 rounded-full blur-3xl opacity-60 group-hover:scale-125 transition"
      ></div>

      <div class="relative flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">
            Total Employers
          </p>

          <h2 class="mt-2 text-3xl font-extrabold text-gray-800">
            {{ statsData.employers }}
          </h2>
        </div>

        <div
          class="flex items-center justify-center w-14 h-14 rounded-2xl bg-purple-500 text-white shadow-lg"
        >
          🏢
        </div>
      </div>
    </div>

    <!-- PENDING -->
    <div
      class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-md border border-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl"
    >
      <div
        class="absolute top-0 right-0 h-24 w-24 bg-yellow-100 rounded-full blur-3xl opacity-60 group-hover:scale-125 transition"
      ></div>

      <div class="relative flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">
            Pending Applications
          </p>

          <h2 class="mt-2 text-3xl font-extrabold text-gray-800">
            {{ statsData.pending_applications }}
          </h2>
        </div>

        <div
          class="flex items-center justify-center w-14 h-14 rounded-2xl bg-yellow-500 text-white shadow-lg"
        >
          📄
        </div>
      </div>
    </div>

    <!-- PESO USERS -->
    <div
      v-if="showPesoStats"
      class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-md border border-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl"
    >
      <div
        class="absolute top-0 right-0 h-24 w-24 bg-pink-100 rounded-full blur-3xl opacity-60 group-hover:scale-125 transition"
      ></div>

      <div class="relative flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">
            PESO Users
          </p>

          <h2 class="mt-2 text-3xl font-extrabold text-gray-800">
            {{ statsData.peso_users }}
          </h2>
        </div>

        <div
          class="flex items-center justify-center w-14 h-14 rounded-2xl bg-pink-500 text-white shadow-lg"
        >
          💼
        </div>
      </div>
    </div>

    <!-- ASSIGNED -->
    <div
      class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-md border border-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl"
    >
      <div
        class="absolute top-0 right-0 h-24 w-24 bg-indigo-100 rounded-full blur-3xl opacity-60 group-hover:scale-125 transition"
      ></div>

      <div class="relative flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">
            Assigned Beneficiaries
          </p>

          <h2 class="mt-2 text-3xl font-extrabold text-gray-800">
            {{ statsData.assigned_beneficiaries }}
          </h2>
        </div>

        <div
          class="flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-500 text-white shadow-lg"
        >
          ✅
        </div>
      </div>
    </div>

    <!-- INTERVIEWS -->
    <div
      class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-md border border-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl"
    >
      <div
        class="absolute top-0 right-0 h-24 w-24 bg-red-100 rounded-full blur-3xl opacity-60 group-hover:scale-125 transition"
      ></div>

      <div class="relative flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">
            Upcoming Interviews
          </p>

          <h2 class="mt-2 text-3xl font-extrabold text-gray-800">
            {{ statsData.upcoming_interviews }}
          </h2>
        </div>

        <div
          class="flex items-center justify-center w-14 h-14 rounded-2xl bg-red-500 text-white shadow-lg"
        >
          📅
        </div>
      </div>
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