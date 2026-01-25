<template>
  <div class="p-6">
    <h2 class="text-xl font-bold mb-4">Applications for {{ beneficiary.name }}</h2>
    <table class="w-full table-auto">
      <thead>
        <tr class="bg-gray-100">
          <th class="px-4 py-2">Application ID</th>
          <th class="px-4 py-2">Job Listing</th>
          <th class="px-4 py-2">Status</th>
          <th class="px-4 py-2">Assigned Employer</th>
          <th class="px-4 py-2">Interview</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="app in beneficiary.applications" :key="app.id" class="border-b">
          <td class="px-4 py-2">{{ app.id }}</td>
          <td class="px-4 py-2">{{ app.jobListing.title }}</td>
          <td class="px-4 py-2">{{ app.status }}</td>
          <td class="px-4 py-2">{{ app.employer?.company_name ?? 'N/A' }}</td>
          <td class="px-4 py-2">{{ app.interview_schedule ?? 'Not Scheduled' }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
const { props } = usePage();
const beneficiary = props.value.beneficiary;
</script>