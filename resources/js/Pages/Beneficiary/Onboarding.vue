<template>
  <div class="min-h-screen bg-gray-50 p-6 relative">

    <!-- Page Title -->
    <h1 class="text-2xl font-bold mb-6 text-center">
      Registering as - {{ userTypeLabel }}
    </h1>

    <!-- Step Indicator -->
    <StepIndicator :type="userType" :active-step="currentStep" />

    <div class="bg-white p-6 rounded-lg shadow-md max-w-2xl mx-auto">
      <form @submit.prevent="submitForm">

        <!-- Step 1 -->
        <div v-if="currentStep === 1">
          <h2 class="font-semibold mb-2">Step 1: {{ steps[currentStep - 1] }}</h2>

          <template v-if="userType === 'student' || userType === 'osy' || userType === 'dependent'">
            <input type="text" placeholder="Full Name" v-model="form.name" class="border p-2 w-full mb-1 rounded"/>
            <p v-if="errors.name" class="text-red-600 text-sm mb-3">{{ errors.name }}</p>

            <input type="email" placeholder="Email" v-model="form.email" class="border p-2 w-full mb-1 rounded"/>
            <p v-if="errors.email" class="text-red-600 text-sm mb-3">{{ errors.email }}</p>

            <!-- PHONE INPUT WITH MASK -->
            <div class="relative">
              <span class="absolute left-3 top-2 text-gray-500">+63</span>
              <input
                type="tel"
                placeholder="9123456789"
                v-model="form.phone"
                class="border p-2 pl-12 w-full mb-1 rounded"
                maxlength="10"
                @input="formatPhone"
                required
              />
            </div>
            <p v-if="errors.phone" class="text-red-600 text-sm mb-3">{{ errors.phone }}</p>
          </template>

          <template v-else-if="userType === 'employer'">
            <input type="text" placeholder="Company Name" v-model="form.companyName" class="border p-2 w-full mb-1 rounded"/>
            <p v-if="errors.companyName" class="text-red-600 text-sm mb-3">{{ errors.companyName }}</p>

            <input type="email" placeholder="Company Email" v-model="form.email" class="border p-2 w-full mb-1 rounded"/>
            <p v-if="errors.email" class="text-red-600 text-sm mb-3">{{ errors.email }}</p>

            <!-- PHONE INPUT FOR EMPLOYER -->
            <div class="relative">
              <span class="absolute left-3 top-2 text-gray-500">+63</span>
              <input
                type="tel"
                placeholder="9123456789"
                v-model="form.phone"
                class="border p-2 pl-12 w-full mb-1 rounded"
                maxlength="10"
                @input="formatPhone"
                required
              />
            </div>
            <p v-if="errors.phone" class="text-red-600 text-sm mb-3">{{ errors.phone }}</p>
          </template>
        </div>

        <!-- Step 2 -->
        <div v-else-if="currentStep === 2">
          <h2 class="font-semibold mb-2">Step 2: {{ steps[currentStep - 1] }}</h2>

          <template v-if="userType === 'student'">
            <input type="text" placeholder="School Name" v-model="form.school" class="border p-2 w-full mb-1 rounded"/>
            <p v-if="errors.school" class="text-red-600 text-sm mb-3">{{ errors.school }}</p>
          </template>

          <template v-else-if="userType === 'osy'">
            <input type="text" placeholder="Skills / Training" v-model="form.skills" class="border p-2 w-full mb-1 rounded"/>
            <p v-if="errors.skills" class="text-red-600 text-sm mb-3">{{ errors.skills }}</p>
          </template>

          <template v-else-if="userType === 'dependent'">
            <input type="text" placeholder="Parent / Guardian Name" v-model="form.parentName" class="border p-2 w-full mb-1 rounded"/>
            <p v-if="errors.parentName" class="text-red-600 text-sm mb-3">{{ errors.parentName }}</p>

            <input type="text" placeholder="Displacement Reason" v-model="form.displacementReason" class="border p-2 w-full mb-1 rounded"/>
            <p v-if="errors.displacementReason" class="text-red-600 text-sm mb-3">{{ errors.displacementReason }}</p>
          </template>

          <template v-else-if="userType === 'employer'">
            <label class="block text-sm font-medium mb-2">Contact Person</label>
            <input type="text" placeholder="Contact Person Name" v-model="form.contactPerson" class="border p-2 w-full mb-1 rounded"/>
            <p v-if="errors.contactPerson" class="text-red-600 text-sm mb-3">{{ errors.contactPerson }}</p>
            
            <label class="block text-sm font-medium mb-2">Company Address</label>
            <textarea placeholder="Company Address" v-model="form.address" class="border p-2 w-full mb-1 rounded"></textarea>
            <p v-if="errors.address" class="text-red-600 text-sm mb-3">{{ errors.address }}</p>
          </template>
        </div>

        <!-- Step 3: Upload Documents -->
        <div v-else-if="currentStep === 3">
          <h2 class="font-semibold mb-2">Step 3: Upload Documents</h2>
          
          <!-- Video Upload Error Message -->
          <div v-if="videoUploadError" class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
            <p class="font-semibold">File Upload Error</p>
            <p>{{ videoUploadError }}</p>
          </div>
          
          <div class="mb-4 bg-gray-100 p-4 rounded">
            <h3 class="font-semibold mb-2">Required Documents:</h3>

            <div v-for="(doc, i) in requiredDocuments" :key="i" class="flex items-center justify-between mb-1">
              <span class="font-medium">{{ doc }}</span>
              <label class="cursor-pointer">
  <input type="file" class="hidden" @change="handleSingleFileUpload($event, i)" />
  <div
    class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 flex items-center"
  >
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v4h16v-4M12 12v8m0-8l-4 4m4-4l4 4" />
    </svg>
    {{ form.documents[i] ? form.documents[i].name : 'Upload' }}
  </div>
</label>
            </div>
            <p v-for="(doc, i) in requiredDocuments" :key="'error-'+i" v-if="errors[`documents_${i}`]" class="text-red-600 text-sm mb-2">
              {{ errors[`documents_${i}`] }}
            </p>

            <div v-for="(file, i) in form.documents" :key="'file-'+i" class="text-sm text-gray-600 mb-1" v-if="file">
              {{ requiredDocuments[i] }}: {{ file.name }}
            </div>
          </div>
        </div>

        <!-- Step 4 -->
        <div v-else-if="currentStep === 4">
          <h2 class="font-semibold mb-4">Step 4: Review Your Information</h2>

          <div v-if="userType === 'student' || userType === 'osy' || userType === 'dependent'">
            <p><strong>Full Name:</strong> {{ form.name }}</p>
            <p><strong>Email:</strong> {{ form.email }}</p>
          </div>

          <div v-if="userType === 'student'">
            <p><strong>School Name:</strong> {{ form.school }}</p>
          </div>

          <div v-else-if="userType === 'osy'">
            <p><strong>Skills / Training:</strong> {{ form.skills }}</p>
          </div>

          <div v-else-if="userType === 'dependent'">
            <p><strong>Parent / Guardian Name:</strong> {{ form.parentName }}</p>
            <p><strong>Displacement Reason:</strong> {{ form.displacementReason }}</p>
          </div>

          <div v-else-if="userType === 'employer'">
            <p><strong>Email:</strong> {{ form.email }}</p>
            <p><strong>Contact Person:</strong> {{ form.contactPerson }}</p>
            <p><strong>Phone:</strong> +63{{ form.phone }}</p>
            <p><strong>Address:</strong> {{ form.address }}</p>
          </div>

          <div class="mb-4">
            <h3 class="font-semibold">Uploaded Documents:</h3>
            <ul>
              <li v-for="(file, i) in form.documents" :key="i">{{ file.name }}</li>
              <li v-if="form.documents.length === 0">No documents uploaded yet.</li>
            </ul>
          </div>
        </div>

        <!-- Step 5 -->
        <div v-else-if="currentStep === 5">
          <h2 class="font-semibold mb-2">Step 5: Submit</h2>
          <p>Click submit to complete your registration.</p>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between mt-6">
          <button type="button" class="px-4 py-2 bg-gray-300 rounded disabled:opacity-50" :disabled="currentStep === 1" @click="prevStep">
            Previous
          </button>

          <button v-if="currentStep < steps.length" type="button" class="px-4 py-2 bg-indigo-600 text-white rounded" @click="nextStep">
            Next
          </button>

          <button v-if="currentStep === steps.length" type="submit" class="px-4 py-2 bg-green-600 text-white rounded">
            Submit
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import StepIndicator from './StepIndicator.vue'
import { Inertia } from '@inertiajs/inertia'
import { usePage } from '@inertiajs/vue3'

const { props: pageProps } = usePage()
const urlParams = new URLSearchParams(window.location.search)
const category = urlParams.get('category') || 'student'
const userType = ref(category)

const form = ref({
  name: '',
  email: '',
  phone: '',
  school: '',
  skills: '',
  parentName: '',
  displacementReason: '',
  companyName: '',
  contactPerson: '',
  address: '',
  documents: []
})

const errors = ref({})
const videoUploadError = ref('')

const stepsByType = {
  student: ['Personal Info', 'School Info', 'Documents', 'Review', 'Submit'],
  osy: ['Personal Info', 'Skills / Training', 'Documents', 'Review', 'Submit'],
  dependent: ['Personal Info', 'Parent Info / Displacement Info', 'Documents', 'Review', 'Submit'],
  employer: ['Company Info', 'Contact  Person', 'Documents', 'Review', 'Submit'],
}

const currentStep = ref(1)
const steps = computed(() => stepsByType[userType.value] || stepsByType.student)

const userTypeLabel = computed(() => {
  switch(userType.value) {
    case 'student': return 'Student'
    case 'osy': return 'Out-of-School Youth'
    case 'dependent': return 'Dependent / Displaced Worker'
    case 'employer': return 'Employer'
  }
})

const requiredDocuments = computed(() => {
  switch(userType.value) {
    case 'student': return ['Birth Certificate', 'ITR/Certificate of Low Income', 'Form 138/137']
    case 'osy': return ['Birth Certificate', 'OSY Certificate', 'Proof of Low Income']
    case 'dependent': return ['Birth Certificate', 'Displacement Certification', 'Notice of Termination']
    case 'employer': return ['Pledge of Commitment', 'Proof of capacity to pay', 'Business permit/SEC/DTI']
  }
})

// --- PHONE MASK LOGIC ---
function formatPhone() {
  // Remove non-digit characters and limit to 10 digits
  form.value.phone = form.value.phone.replace(/\D/g, '').slice(0,10)
}

// --- VALIDATION ---
function validateStep(step) {
  errors.value = {}
  let valid = true

  if(step === 1) {
    if(['student','osy','dependent'].includes(userType.value)) {
      if(!form.value.name) { errors.value.name = 'Full Name is required'; valid = false }
      if(!form.value.email) { errors.value.email = 'Email is required'; valid = false }
      if(!form.value.phone) { errors.value.phone = 'Phone Number is required'; valid = false }
      else if(!/^\d{10}$/.test(form.value.phone)) { errors.value.phone = 'Phone must be 10 digits'; valid = false }
    } else if(userType.value === 'employer') {
      if(!form.value.companyName) { errors.value.companyName = 'Company Name is required'; valid = false }
      if(!form.value.email) { errors.value.email = 'Email is required'; valid = false }
      if(!form.value.phone) { errors.value.phone = 'Phone Number is required'; valid = false }
      else if(!/^\d{10}$/.test(form.value.phone)) { errors.value.phone = 'Phone must be 10 digits'; valid = false }
    }
  }

  // Other steps...
  if(step === 2) {
    if(userType.value === 'student' && !form.value.school) { errors.value.school = 'School Name is required'; valid = false }
    if(userType.value === 'osy' && !form.value.skills) { errors.value.skills = 'Skills/Training is required'; valid = false }
    if(userType.value === 'dependent') {
      if(!form.value.parentName) { errors.value.parentName = 'Parent/Guardian Name is required'; valid = false }
      if(!form.value.displacementReason) { errors.value.displacementReason = 'Displacement Reason is required'; valid = false }
    }
    if(userType.value === 'employer') {
      if(!form.value.contactPerson) { errors.value.contactPerson = 'Contact Person is required'; valid = false }
      if(!form.value.address) { errors.value.address = 'Company Address is required'; valid = false }
    }
  }

  if(step === 3) {
    requiredDocuments.value.forEach((doc, i) => {
      if(!form.value.documents[i]) {
        errors.value[`documents_${i}`] = `${doc} is required`
        valid = false
      }
    })
  }

  return valid
}

function nextStep() {
  if(validateStep(currentStep.value)) {
    if(currentStep.value < steps.value.length) currentStep.value++
  }
}

function prevStep() {
  if(currentStep.value > 1) currentStep.value--
}

function handleSingleFileUpload(event, index) {
  const file = event.target.files[0] || null
  videoUploadError.value = ''
  
  if (file) {
    // Check if file is a video
    const videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'webm', 'm4v']
    const videoMimeTypes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-matroska', 'video/x-flv', 'video/x-ms-wmv', 'video/webm', 'video/x-m4v']
    
    const fileExtension = file.name.split('.').pop().toLowerCase()
    const isVideoByExtension = videoExtensions.includes(fileExtension)
    const isVideoByMimeType = videoMimeTypes.includes(file.type)
    
    if (isVideoByExtension || isVideoByMimeType) {
      videoUploadError.value = `❌ Video files (${file.name}) are not allowed. Please upload only PDF, DOC, DOCX, JPG, JPEG, or PNG files.`
      form.value.documents[index] = null
      return
    }
  }
  
  form.value.documents[index] = file
}

function submitForm() {
  if(!validateStep(currentStep.value)) return

  const payload = new FormData()
  Object.keys(form.value).forEach(key => {
    if(key === 'documents') {
      form.value.documents.forEach((file, idx) => {
        if(file) payload.append(`documents[${idx}]`, file)
      })
    } else if(form.value[key]) {
      let fieldName = key
      if(key === 'companyName') fieldName = 'company_name'
      if(key === 'contactPerson') fieldName = 'contact_person'
      payload.append(fieldName, form.value[key])
    }
  })

  Inertia.post('/onboarding/submit', payload)
}

function goHome() {
  Inertia.visit('/')
}
</script>
