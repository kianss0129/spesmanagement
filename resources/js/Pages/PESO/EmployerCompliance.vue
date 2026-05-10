<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">
      Employer Compliance
    </h1>

    <div class="bg-white p-4 rounded shadow">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b">
            <th class="text-left p-2">Company</th>
            <th class="text-left p-2">Compliance Rate</th>
            <th class="text-left p-2">Status</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="item in employers" :key="item.id" class="border-b">
            <td class="p-2">
              {{ item.company_name }}
            </td>

            <td class="p-2">
              {{ item.compliance_rate }}%
            </td>

            <td class="p-2">
              <span
                :class="item.compliance_rate >= 75 ? 'text-green-600' : 'text-red-600'"
              >
                {{ item.compliance_rate >= 75 ? 'Compliant' : 'Non-Compliant' }}
              </span>
            </td>
          </tr>

          <tr v-if="!employers.length">
            <td colspan="3" class="text-center p-4 text-gray-500">
              No data found
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({
  employers: Array
})
</script>