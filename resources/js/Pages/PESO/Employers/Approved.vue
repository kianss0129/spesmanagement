<template>
  <div class="p-6 min-h-screen bg-gray-50">
    <h1 class="text-2xl font-bold mb-4">Approved Employers</h1>

    <table class="w-full border rounded">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2 border">Company Name</th>
          <th class="p-2 border">Email</th>
          <th class="p-2 border">Approved At</th>
          <th class="p-2 border">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="e in employers" :key="e.id">
          <td class="p-2 border">{{ e.company_name }}</td>
          <td class="p-2 border">{{ e.email }}</td>
          <td class="p-2 border">{{ e.approved_at }}</td>
          <td class="p-2 border">
            <button
              v-if="canApprove"
              class="bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700"
              @click="undoApproval(e.id)"
            >
              Undo Approval
            </button>
          </td>
        </tr>

        <tr v-if="employers.length === 0">
          <td colspan="4" class="p-4 text-center text-gray-500">
            No approved employers
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { defineProps } from 'vue'

const props = defineProps({
  employers: {
    type: Array,
    default: () => [],
  },
  canApprove: {
    type: Boolean,
    default: false,
  },
})

const undoApproval = (id) => {
  if (confirm('Undo approval for this employer?')) {
    router.post(route('peso.employers.undo', { id }), {}, {
      onSuccess: () => router.reload()
    })
  }
}
</script>

<style scoped>
table th,
table td {
  text-align: left;
}
</style>