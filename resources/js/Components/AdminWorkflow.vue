<template>
  <div>
    <!-- ================= PENDING APPROVALS ================= -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <!-- Pending Beneficiaries -->
      <div class="bg-white p-4 rounded shadow flex flex-col justify-between">
        <div>
          <div class="text-gray-500 mb-2">Pending Beneficiaries</div>
          <div class="text-2xl font-bold">{{ stats.pending_beneficiaries ?? 0 }}</div>
        </div>
        <a href="/peso/beneficiaries/pending"
           class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-center">
          View for Approval
        </a>
      </div>

      <!-- Pending Employers -->
      <div class="bg-white p-4 rounded shadow flex flex-col justify-between">
        <div>
          <div class="text-gray-500 mb-2">Pending Employers</div>
          <div class="text-2xl font-bold">{{ stats.pending_employers ?? 0 }}</div>
        </div>
        <a href="/peso/employers/pending"
           class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-center">
          View for Approval
        </a>
      </div>
    </div>

    <!-- ================= LATEST USERS TABLE ================= -->
    <div v-if="showUsersTable" class="bg-white p-4 rounded shadow mb-6">
      <h2 class="text-lg font-bold mb-4">Latest Users</h2>
      <table class="w-full table-auto">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-4 py-2 text-left">ID</th>
            <th class="px-4 py-2 text-left">Name</th>
            <th class="px-4 py-2 text-left">Email</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in stats.latest_users" :key="user.id" class="border-b">
            <td class="px-4 py-2">{{ user.id }}</td>
            <td class="px-4 py-2">{{ user.name }}</td>
            <td class="px-4 py-2">{{ user.email }}</td>
          </tr>
          <tr v-if="!stats.latest_users || stats.latest_users.length === 0">
            <td colspan="3" class="px-4 py-2 text-center text-gray-500">No users found</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ================= RECENT ACTIVITY ================= -->
    <div v-if="showActivity" class="bg-white p-4 rounded shadow">
      <h2 class="text-lg font-bold mb-4">Recent Activity</h2>
      <ul class="text-sm space-y-2">
        <li v-if="!stats.recent_activity || stats.recent_activity.length === 0" class="text-gray-500">
          No recent activity
        </li>
        <li v-for="a in stats.recent_activity" :key="a.id" class="border p-2 rounded">
          <div class="text-xs text-gray-500">{{ a.created_at }}</div>
          <div class="text-sm">{{ a.description }}</div>
          <div class="text-xs text-gray-400">By: {{ a.causer_name ?? a.causer_id ?? 'system' }}</div>
        </li>
      </ul>

      <div class="mt-4 flex space-x-2">
        <a v-if="canManageRoles"
           href="/admin/roles"
           class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">
          Manage Roles
        </a>
        <button @click="refresh" class="bg-gray-200 px-4 py-2 rounded">Refresh</button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  stats: Object,
  selectedDays: Number,
  showUsersTable: { type: Boolean, default: true },
  showActivity: { type: Boolean, default: true },
  canManageRoles: { type: Boolean, default: false }
})

const emit = defineEmits(['update:selectedDays', 'refresh'])

function refresh() {
  emit('refresh')
}
</script>
