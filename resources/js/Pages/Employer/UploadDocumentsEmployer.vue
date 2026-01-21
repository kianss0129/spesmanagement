<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  documents: [],
})

const errors = ref(null)
const success = ref(null)

function handleFiles(e) {
  form.documents = Array.from(e.target.files)
}

function submit() {
  success.value = null
  errors.value = null

  form.post(route('beneficiary.uploadDocs'), {
    forceFormData: true,
    onSuccess: () => {
      success.value = 'Documents uploaded successfully.'
      form.reset('documents')
    },
    onError: (err) => {
      errors.value = err
    }
  })
}
</script>

<template>
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Upload Documents – Employer</h1>

    <div class="mb-4">
      <p class="font-semibold mb-2">Required Documents:</p>
      <ul class="list-disc list-inside text-sm text-gray-700">
        <li>Pledge of Commitment to PESO</li>
        <li>Proof of capacity to pay 60% of salaries (Budget/Certification/Resolution)</li>
        <li>Business Permit or SEC/DTI Registration</li>
      </ul>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block mb-1 font-medium">Upload files</label>
        <input
          type="file"
          multiple
          @change="handleFiles"
          class="block w-full border rounded p-2"
          name="documents[]"
          id="documents"
        />
      </div>

      <div v-if="errors" class="text-red-600 text-sm">
        Upload failed. Please check your files.
      </div>

      <div v-if="success" class="text-green-600 text-sm">
        {{ success }}
      </div>

      <button
        type="submit"
        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
        :disabled="form.processing"
      >
        Upload Documents
      </button>
    </form>
  </div>
</template>
