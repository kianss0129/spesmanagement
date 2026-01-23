<template>
  <div class="p-6 min-h-screen bg-gray-50">
    <h1 class="text-2xl font-bold mb-4">Pending Beneficiaries</h1>

    <table class="w-full border rounded">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2 border">Name</th>
          <th class="p-2 border">Email</th>
          <th class="p-2 border">Submitted At</th>
          <th class="p-2 border">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="b in beneficiaries" :key="b.id">
          <td class="p-2 border">{{ b.user.name }}</td>
          <td class="p-2 border">{{ b.user.email }}</td>
          <td class="p-2 border">{{ b.onboarding_completed_at }}</td>
          <td class="p-2 border space-x-2">
            <button
              v-if="canApprove"
              class="bg-green-600 text-white px-3 py-1 rounded"
              @click="approve(b.id)"
            >
              Approve
            </button>
            <button
              v-if="canApprove"
              class="bg-red-600 text-white px-3 py-1 rounded"
              @click="reject(b.id)"
            >
              Reject
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  beneficiaries: Array,
  canApprove: Boolean
})

const approve = (id) => {
  if (confirm('Are you sure you want to approve this beneficiary?')) {
    router.post(route('peso.beneficiaries.approve', id), {}, {
      onSuccess: () => router.reload()
    })
  }
}

const reject = (id) => {
  if (confirm('Are you sure you want to reject this beneficiary?')) {
    router.post(route('peso.beneficiaries.reject', id), {}, {
      onSuccess: () => router.reload()
    })
  }
}
</script>
