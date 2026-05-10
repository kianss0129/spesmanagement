<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const form = useForm({
  birth_certificate: null,
  school_record: null,
  osy_certificate: null,
  income_proof: null,
  displacement_certificate: null,
  termination_notice: null,
})

const handleFile = (e, field) => {
  form[field] = e.target.files[0]
}

const submit = () => {
  form.post('/onboarding/upload', {
    forceFormData: true,
    onSuccess: () => {
      alert('Documents uploaded successfully!')
      router.visit('/dashboard')
    }
  })
}
</script>

<template>
  <div class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-3xl mx-auto bg-white shadow-xl rounded-2xl p-8">

      <h1 class="text-2xl font-bold mb-6">Upload Required Documents</h1>

      <form @submit.prevent="submit" class="space-y-6">

        <div>
          <label class="block font-medium mb-1">Birth Certificate</label>
          <input type="file" @change="e => handleFile(e,'birth_certificate')" />
          <div v-if="form.errors.birth_certificate" class="text-red-500 text-sm">
            {{ form.errors.birth_certificate }}
          </div>
        </div>

        <div>
          <label class="block font-medium mb-1">School Record</label>
          <input type="file" @change="e => handleFile(e,'school_record')" />
        </div>

        <div>
          <label class="block font-medium mb-1">OSY Certificate</label>
          <input type="file" @change="e => handleFile(e,'osy_certificate')" />
        </div>

        <div>
          <label class="block font-medium mb-1">Income Proof</label>
          <input type="file" @change="e => handleFile(e,'income_proof')" />
        </div>

        <div>
          <label class="block font-medium mb-1">Displacement Certificate</label>
          <input type="file" @change="e => handleFile(e,'displacement_certificate')" />
        </div>

        <div>
          <label class="block font-medium mb-1">Termination Notice</label>
          <input type="file" @change="e => handleFile(e,'termination_notice')" />
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-700"
        >
          <span v-if="form.processing">Uploading...</span>
          <span v-else>Submit Documents</span>
        </button>

      </form>

    </div>
  </div>
</template>