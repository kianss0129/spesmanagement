Pages/Employer/UploadDocumentsEmployer.vue

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import ImageCropUpload from '@/Components/ImageCropUpload.vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'


const form = useForm({
  pledge_of_commitment: null,
  financial_proof: null,
  business_registration: null,
})


const documentErrors = ref({})
const success = ref(null)


const documents = [
  {
    key: 'pledge_of_commitment',
    label: 'Pledge of Commitment to PESO',
    help: 'Upload your signed pledge of commitment to participate in SPES. You can zoom and adjust the document image for clarity.'
  },
  {
    key: 'financial_proof',
    label: 'Proof of Capacity to Pay 60% of Salaries',
    help: 'Upload budget certification, financial statement, or board resolution showing capacity. You can zoom and adjust the image.'
  },
  {
    key: 'business_registration',
    label: 'Business Permit or SEC/DTI Registration',
    help: 'Upload your valid business permit or government registration documents. You can zoom and adjust the image.'
  }
]


function handleDocumentUpload(key, file) {
  documentErrors.value[key] = ''


  if (!file) {
    form[key] = null
    return
  }


  // Basic validation
  const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf']
  if (!allowedTypes.includes(file.type)) {
    documentErrors.value[key] = 'Only JPG, PNG, and PDF files are allowed.'
    return
  }


  const maxSize = 10 * 1024 * 1024 // 10MB
  if (file.size > maxSize) {
    documentErrors.value[key] = 'File size exceeds 10MB limit.'
    return
  }


  form[key] = file
}


function submit() {
  success.value = null
  documentErrors.value = {}


  // Validate that at least one document is uploaded
  const hasDocument = form.pledge_of_commitment || form.financial_proof || form.business_registration
  if (!hasDocument) {
    toast.error('Please upload at least one document.')
    return
  }


  form.post(route('employer.upload-documents'), {
    forceFormData: true,
    onSuccess: () => {
      toast.success('Documents uploaded successfully!')
      form.reset('pledge_of_commitment', 'financial_proof', 'business_registration')
    },
    onError: (errors) => {
      Object.assign(documentErrors.value, errors)
      toast.error('Upload failed. Please check your files and try again.')
    }
  })
}
</script>


<template>
  <div class="max-w-4xl mx-auto p-6">
    <div class="rounded-2xl bg-white shadow-lg p-8">
      <h1 class="text-3xl font-bold mb-2 text-slate-900">Upload Documents</h1>
      <p class="text-slate-600 mb-6">Upload and crop employer documents for SPES verification</p>


      <div class="mb-6 rounded-xl bg-sky-50 border border-sky-200 p-4">
        <p class="font-semibold text-slate-800 mb-2">Required Documents:</p>
        <ul class="list-disc list-inside space-y-1 text-sm text-slate-700">
          <li>Pledge of Commitment to PESO</li>
          <li>Proof of capacity to pay 60% of salaries (Budget/Certification/Resolution)</li>
          <li>Business Permit or SEC/DTI Registration</li>
        </ul>
      </div>


      <form @submit.prevent="submit" class="space-y-6">
        <div v-for="doc in documents" :key="doc.key" class="rounded-xl border border-slate-200 bg-slate-50 p-5">
          <p class="text-sm font-semibold text-slate-800 mb-3">{{ doc.label }}</p>
          <ImageCropUpload
            :label="doc.label"
            :help="doc.help"
            @update:file="(file) => handleDocumentUpload(doc.key, file)"
          />
          <p v-if="documentErrors[doc.key]" class="mt-2 text-sm text-rose-600">
            {{ documentErrors[doc.key] }}
          </p>
        </div>


        <div class="flex gap-3 justify-end pt-4 border-t border-slate-200">
          <button
            type="button"
            class="rounded-xl border border-slate-300 px-6 py-2.5 font-semibold text-slate-700 hover:bg-slate-100 transition"
            @click="form.reset('pledge_of_commitment', 'financial_proof', 'business_registration')"
          >
            Clear All
          </button>
          <button
            type="submit"
            class="rounded-xl bg-emerald-600 px-6 py-2.5 font-semibold text-white hover:bg-emerald-700 transition disabled:opacity-50"
            :disabled="form.processing"
          >
            <span v-if="form.processing">Uploading...</span>
            <span v-else>Upload Documents</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>



