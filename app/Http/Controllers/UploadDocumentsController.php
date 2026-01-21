<template>
  <div class="p-6 max-w-3xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">
      Upload Documents - {{ pageTitle }}
    </h1>

    <div v-if="statusMessage" class="mb-4 p-3 rounded bg-yellow-100 text-yellow-800">
      {{ statusMessage }}
    </div>

    <form @submit.prevent="submit" class="bg-white p-5 rounded shadow space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Select documents (PDF, JPG, PNG)</label>
        <input type="file" multiple @change="onFiles" class="border p-2 rounded w-full" />
      </div>

      <div class="flex items-start gap-2 text-sm">
        <input type="checkbox" v-model="acceptedTerms" class="mt-1" />
        <span>
          I agree to the
          <button type="button" class="text-blue-600 underline hover:text-blue-800" @click="showTerms = true">
            Terms & Conditions (SPES Policy)
          </button>
        </span>
      </div>

      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50" :disabled="uploading || !acceptedTerms">
        {{ uploading ? 'Uploading...' : 'Upload Documents' }}
      </button>
    </form>

    <!-- Terms Modal -->
    <div v-if="showTerms" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg w-full max-w-2xl p-6">
        <h3 class="text-lg font-semibold mb-4">SPES Program – Terms & Conditions</h3>
        <div class="h-64 overflow-y-auto text-sm text-gray-700 space-y-3">
          <p><strong>1. Eligibility</strong> – Applicant must qualify under SPES guidelines.</p>
          <p><strong>2. Documents</strong> – All submitted documents must be valid and authentic.</p>
          <p><strong>3. Attendance</strong> – Beneficiaries must follow assigned schedules.</p>
          <p><strong>4. Conduct</strong> – Proper workplace behavior is required.</p>
          <p><strong>5. Termination</strong> – Violations may result in disqualification.</p>
          <p><strong>6. Data Privacy</strong> – Personal data will be used for SPES processing only.</p>
        </div>
        <div class="text-right mt-4">
          <button @click="showTerms = false" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { Inertia } from '@inertiajs/inertia'

const props = defineProps({
  userTypeProp: String // "student", "osy", "dependent", "employer"
})

const files = ref([])
const acceptedTerms = ref(false)
const showTerms = ref(false)
const statusMessage = ref('')
const uploading = ref(false)

const pageTitle = computed(() => {
  switch(props.userTypeProp) {
    case 'student': return 'Student'
    case 'osy': return 'OSY'
    case 'dependent': return 'Dependent'
    case 'employer': return 'Employer'
    default: return ''
  }
})

const onFiles = (e) => {
  files.value = Array.from(e.target.files || [])
}

const submit = async () => {
  if (!files.value.length) {
    alert('Please select at least one file.')
    return
  }

  if (!acceptedTerms.value) {
    alert('You must accept the Terms & Conditions.')
    return
  }

  const formData = new FormData()
  files.value.forEach(file => formData.append('documents[]', file))

  uploading.value = true

  try {
    const res = await axios.post('/beneficiary/upload-documents', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    statusMessage.value = res.data.message || 'Documents submitted successfully.'
    files.value = []
    acceptedTerms.value = false

  } catch (err) {
    console.error(err)
    alert('Upload failed. Please try again.')
  } finally {
    uploading.value = false
  }
}
</script>
