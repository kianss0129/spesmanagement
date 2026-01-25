<template>
  <div class="bg-white p-4 rounded shadow hover:shadow-md transition">
    <h2 class="text-lg font-bold mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">

      <!-- Assign Beneficiary -->
      <form v-if="showAssignForm" @submit.prevent="assignBeneficiary" class="space-y-3 bg-gray-50 p-4 rounded shadow-inner">
        <h3 class="font-medium">Assign Beneficiary</h3>
        <div>
          <label class="text-sm">Job Listing ID</label>
          <input v-model="assignForm.job_listing_id" type="number" class="w-full border rounded px-3 py-2" required />
        </div>
        <div>
          <label class="text-sm">Beneficiary ID</label>
          <input v-model="assignForm.beneficiary_id" type="number" class="w-full border rounded px-3 py-2" required />
        </div>
        <div class="flex items-center space-x-2">
          <button type="submit" class="bg-indigo-600 text-white px-3 py-2 rounded">Assign</button>
          <span v-if="assignMessage" class="text-sm text-green-600">{{ assignMessage }}</span>
        </div>
      </form>

      <!-- Schedule Interview -->
      <form v-if="showScheduleForm" @submit.prevent="scheduleInterview" class="space-y-3 bg-gray-50 p-4 rounded shadow-inner">
        <h3 class="font-medium">Schedule Interview</h3>
        <div>
          <label class="text-sm">Application ID</label>
          <input v-model="scheduleForm.application_id" type="number" class="w-full border rounded px-3 py-2" required />
        </div>
        <div>
          <label class="text-sm">Scheduled At</label>
          <input v-model="scheduleForm.scheduled_at" type="datetime-local" class="w-full border rounded px-3 py-2" required />
        </div>
        <div>
          <label class="text-sm">Meet Link (optional)</label>
          <input v-model="scheduleForm.meet_link" type="url" class="w-full border rounded px-3 py-2" />
        </div>
        <div class="flex items-center space-x-2">
          <button type="submit" class="bg-green-600 text-white px-3 py-2 rounded">Schedule</button>
          <span v-if="scheduleMessage" class="text-sm text-green-600">{{ scheduleMessage }}</span>
        </div>
      </form>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  showAssignForm: { type: Boolean, default: true },
  showScheduleForm: { type: Boolean, default: true }
})

const emit = defineEmits(['dataChanged'])

const assignForm = ref({ job_listing_id: '', beneficiary_id: '' })
const scheduleForm = ref({ application_id: '', scheduled_at: '', meet_link: '' })
const assignMessage = ref('')
const scheduleMessage = ref('')

async function assignBeneficiary() {
  assignMessage.value = ''
  try {
    await axios.post('/peso/assign-beneficiary', assignForm.value)
    assignMessage.value = 'Assigned successfully.'
    emit('dataChanged')
  } catch (e) {
    assignMessage.value = e?.response?.data?.error ?? 'Failed to assign.'
    console.error('Assign failed', e)
  }
}

async function scheduleInterview() {
  scheduleMessage.value = ''
  try {
    await axios.post('/peso/schedule-interview', scheduleForm.value)
    scheduleMessage.value = 'Interview scheduled.'
    emit('dataChanged')
  } catch (e) {
    scheduleMessage.value = e?.response?.data?.message ?? 'Failed to schedule.'
    console.error('Schedule failed', e)
  }
}
</script>