<template>
  <div class="p-6 max-w-3xl mx-auto bg-white shadow rounded-lg">
    <h1 class="text-2xl font-semibold mb-4">Interview Details</h1>

    <div class="space-y-2">
      <p><strong>Job:</strong> {{ interview.job_listing.title }}</p>
      <p><strong>Scheduled At:</strong> {{ formatDate(interview.scheduled_at) }}</p>
      <p v-if="interview.google_meet_link">
        <strong>Google Meet Link:</strong>
        <a :href="interview.google_meet_link" target="_blank" class="text-blue-600 underline">
          Join Interview
        </a>
      </p>
      <p v-else class="text-red-500">No Google Meet link yet.</p>
    </div>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import dayjs from 'dayjs'

const props = usePage().props.value
const interview = ref(props.interview)

const formatDate = (datetime) => dayjs(datetime).format('MMMM D, YYYY h:mm A')
</script>
