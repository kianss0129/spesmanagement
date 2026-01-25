<template>
  <div class="p-6">
    <h2 class="text-xl font-bold mb-4">Applications for {{ employer.company_name }}</h2>
    <table class="w-full table-auto">
      <thead>
        <tr class="bg-gray-100">
          <th class="px-4 py-2">Application ID</th>
          <th class="px-4 py-2">Beneficiary Name</th>
          <th class="px-4 py-2">Status</th>
          <th class="px-4 py-2">Interview Schedule</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="app in employer.applications" :key="app.id" class="border-b">
          <td class="px-4 py-2">{{ app.id }}</td>
          <td class="px-4 py-2">{{ app.beneficiary.name }}</td>
          <td class="px-4 py-2">{{ app.status }}</td>
          <td class="px-4 py-2">{{ app.interview_schedule ?? 'Not Scheduled' }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
const { props } = usePage();
const employer = props.value.employer;
</script>