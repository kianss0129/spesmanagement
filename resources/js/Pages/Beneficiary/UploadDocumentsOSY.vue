<script setup>
import { useForm, Head } from '@inertiajs/vue3'

const form = useForm({
  birth_certificate: null,
  osy_certificate: null,
  income_proof: null,
})

function submit() {
  form.post(route('beneficiary.uploadDocuments'), {
    forceFormData: true,
  })
}
</script>

<template>
  <Head title="Upload Documents - OSY" />

  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Upload Documents - OSY</h1>

    <form @submit.prevent="submit" class="space-y-5">

      <div>
        <label class="block font-medium mb-1">Birth Certificate / Government ID</label>
        <input type="file" @change="e => form.birth_certificate = e.target.files[0]" required />
      </div>

      <div>
        <label class="block font-medium mb-1">OSY Certificate (Barangay / SWDO)</label>
        <input type="file" @change="e => form.osy_certificate = e.target.files[0]" required />
      </div>

      <div>
        <label class="block font-medium mb-1">Proof of Low Income (ITR / Certificate)</label>
        <input type="file" @change="e => form.income_proof = e.target.files[0]" required />
      </div>

      <div v-if="form.errors.any" class="text-red-600">
        <div v-for="(error, key) in form.errors" :key="key">{{ error }}</div>
      </div>

      <button
        type="submit"
        class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700"
        :disabled="form.processing"
      >
        Submit Documents
      </button>
    </form>
  </div>
</template>
