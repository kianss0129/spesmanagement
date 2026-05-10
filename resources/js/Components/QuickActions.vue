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
          <label class="text-sm">Beneficiary</label>
          <select v-model="assignForm.beneficiary_id" class="w-full border rounded px-3 py-2" required>
            <option value="">-- Select beneficiary --</option>
            <option v-for="beneficiary in beneficiaryOptions" :key="beneficiary.id" :value="beneficiary.id">
              #{{ beneficiary.id }} - {{ beneficiary.name }}
            </option>
          </select>
        </div>
        <div class="flex items-center space-x-2">
          <button type="submit" class="bg-indigo-600 text-white px-3 py-2 rounded">Assign</button>
          <span v-if="assignMessage" class="text-sm text-green-600">{{ assignMessage }}</span>
        </div>
      </form>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  showAssignForm: { type: Boolean, default: true }
})

const emit = defineEmits(['dataChanged'])

const applications = ref([])

const assignForm = ref({ job_listing_id: null, beneficiary_id: null })
const assignMessage = ref('')

const beneficiaryOptions = computed(() => {
  const map = {}
  applications.value.forEach(app => {
    if (app.beneficiary_id && !map[app.beneficiary_id]) {
      map[app.beneficiary_id] = {
        id: app.beneficiary_id,
        name: app.beneficiary_name || `Beneficiary #${app.beneficiary_id}`
      }
    }
  })
  return Object.values(map)
})

async function assignBeneficiary() {
  assignMessage.value = ''
  try {
    await axios.post('/peso/assign-beneficiary', assignForm.value)
    assignMessage.value = 'Assigned successfully.'
    emit('dataChanged')
  } catch (e) {
    assignMessage.value =
  e?.response?.data?.message ||
  e?.response?.data?.errors?.beneficiary_id?.[0] ||
  e?.response?.data?.errors?.job_listing_id?.[0] ||
  'Failed to assign.'
    console.error('Assign failed', e)
  }
}

onMounted(async () => {
  try {
    const res = await axios.get('/peso/applications')
    applications.value = res.data
  } catch (e) {
    console.error('Failed to load applications for dropdown', e)
  }
})
</script>