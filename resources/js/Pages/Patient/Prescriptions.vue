  <template>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-2">My Prescriptions</h1>
      <p class="mb-4 text-gray-600">This page will show your prescriptions.</p>

      <div v-if="prescriptions.length === 0">
        <p class="text-gray-500">No prescriptions found.</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="prescription in prescriptions"
          :key="prescription.id"
          class="border p-4 rounded shadow"
        >
          <p><strong>Medicine:</strong> {{ prescription.medicine_name }}</p>
          <p><strong>Dosage:</strong> {{ prescription.dosage }}</p>
          <p><strong>Doctor:</strong> {{ prescription.doctor?.name ?? 'Unknown' }}</p>
          <p><strong>Date:</strong> {{ formatDate(prescription.prescribed_date) }}</p>
        </div>
      </div>
    </div>
  </template>

  <script setup>
  import { usePage } from '@inertiajs/vue3'

  const props = defineProps({
    prescriptions: Array
  })

  function formatDate(date) {
    return new Date(date).toLocaleDateString()
  }
  </script>
