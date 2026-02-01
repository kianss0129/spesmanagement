<template>
  <div class="min-h-screen bg-gray-50 p-6 relative">

    <!-- Logout Button -->
    <form @submit.prevent="logout" class="absolute top-4 right-4">
      <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
        Logout
      </button>
    </form>

    <!-- Page Title -->
    <h1 class="text-2xl font-bold mb-6 text-center">
      Onboarding - {{ userTypeLabel }}
    </h1>

    <!-- Step Indicator -->
    <StepIndicator :type="userType" :active-step="currentStep" />

    <div class="bg-white p-6 rounded-lg shadow-md max-w-2xl mx-auto">
      <form @submit.prevent="submitForm">

        <!-- Step 1: Personal / Company Info -->
        <div v-if="currentStep === 1">
          <h2 class="font-semibold mb-2">Step 1: {{ steps[currentStep - 1] }}</h2>

          <template v-if="userType === 'student' || userType === 'osy' || userType === 'dependent'">
            <input type="text" placeholder="Full Name" v-model="form.name" class="border p-2 w-full mb-3 rounded"/>
            <input type="email" placeholder="Email" v-model="form.email" name="email" class="border p-2 w-full mb-3 rounded"/>
            <input type="tel" placeholder="Phone Number" v-model="form.phone" name="phone" class="border p-2 w-full mb-3 rounded"/>
          </template>

          <template v-else-if="userType === 'employer'">
            <input type="text" placeholder="Company Name" v-model="form.companyName" class="border p-2 w-full mb-3 rounded"/>
            <input type="email" placeholder="Company Email" v-model="form.email" name="email" class="border p-2 w-full mb-3 rounded"/>
            <input type="tel" placeholder="Phone Number" v-model="form.phone" name="phone" class="border p-2 w-full mb-3 rounded"/>
          </template>
        </div>

        <!-- Step 2: Type-specific Info -->
        <div v-else-if="currentStep === 2">
          <h2 class="font-semibold mb-2">Step 2: {{ steps[currentStep - 1] }}</h2>

          <template v-if="userType === 'student'">
            <input type="text" placeholder="School Name" v-model="form.school" class="border p-2 w-full mb-3 rounded"/>
          </template>

          <template v-else-if="userType === 'osy'">
            <input type="text" placeholder="Skills / Training" v-model="form.skills" class="border p-2 w-full mb-3 rounded"/>
          </template>

          <template v-else-if="userType === 'dependent'">
            <input type="text" placeholder="Parent / Guardian Name" v-model="form.parentName" class="border p-2 w-full mb-3 rounded"/>
            <input type="text" placeholder="Displacement Reason" v-model="form.displacementReason" class="border p-2 w-full mb-3 rounded"/>
          </template>

          <template v-else-if="userType === 'employer'">
            <label class="block text-sm font-medium mb-2">Contact Person</label>
            <input type="text" placeholder="Contact Person Name" v-model="form.contactPerson" class="border p-2 w-full mb-3 rounded"/>
            
            <label class="block text-sm font-medium mb-2">Company Address</label>
            <textarea placeholder="Company Address" v-model="form.address" class="border p-2 w-full mb-3 rounded"></textarea>
          </template>
        </div>

        <!-- Step 3: Upload Documents -->
        <div v-else-if="currentStep === 3">
          <h2 class="font-semibold mb-2">Step 3: Upload Documents</h2>
          <div class="mb-4 bg-gray-100 p-4 rounded">
            <h3 class="font-semibold mb-2">Required Documents:</h3>
            <ul class="list-disc list-inside">
              <li v-for="(doc, i) in requiredDocuments" :key="i">{{ doc }}</li>
            </ul>
          </div>
          <input type="file" multiple @change="handleFileUpload" class="border p-2 w-full mb-3 rounded"/>
        </div>

        <!-- Step 4: Review -->
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
            <p><strong>Company Name:</strong> {{ form.companyName }}</p>
            <p><strong>Email:</strong> {{ form.email }}</p>
            <p><strong>Contact Person:</strong> {{ form.contactPerson }}</p>
            <p><strong>Phone:</strong> {{ form.phone }}</p>
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

        <!-- Step 5: Submit -->
        <div v-else-if="currentStep === 5">
          <h2 class="font-semibold mb-2">Step 5: Submit</h2>
          <p>Click submit to complete your onboarding.</p>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between mt-6">
          <button type="button" class="px-4 py-2 bg-gray-300 rounded disabled:opacity-50" :disabled="currentStep === 1" @click="prevStep">Previous</button>
          <button type="button" class="px-4 py-2 bg-indigo-600 text-white rounded disabled:opacity-50" :disabled="currentStep === steps.length" @click="nextStep">Next</button>
          <button v-if="currentStep === steps.length" type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Submit</button>
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

// Get user type from query string or default to 'student'
const { props: pageProps } = usePage()
const urlParams = new URLSearchParams(window.location.search)
const category = urlParams.get('category') || 'student'
const userType = ref(category)

// Dynamic form fields
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

// Steps by type
const stepsByType = {
  student: ['Personal Info', 'School Info', 'Documents', 'Review', 'Submit'],
  osy: ['Personal Info', 'Skills / Training', 'Documents', 'Review', 'Submit'],
  dependent: ['Personal Info', 'Parent Info / Displacement Info', 'Documents', 'Review', 'Submit'],
  employer: ['Company Info', 'Pledge Submission', 'Documents', 'Review', 'Submit']
}

const currentStep = ref(1)
const steps = computed(() => stepsByType[userType.value] || stepsByType.student)

// Display label
const userTypeLabel = computed(() => {
  switch(userType.value) {
    case 'student': return 'Student'
    case 'osy': return 'Out-of-School Youth'
    case 'dependent': return 'Dependent / Displaced Worker'
    case 'employer': return 'Employer'
  }
})

// Required documents per type
const requiredDocuments = computed(() => {
  switch(userType.value) {
    case 'student': return ['Birth Certificate', 'ITR/Certificate of Low Income', 'Form 138/137']
    case 'osy': return ['Birth Certificate', 'OSY Certificate', 'Proof of Low Income']
    case 'dependent': return ['Birth Certificate', 'Displacement Certification', 'Notice of Termination']
    case 'employer': return ['Pledge of Commitment', 'Proof of capacity to pay', 'Business permit/SEC/DTI']
  }
})

// Navigation
function nextStep() { if(currentStep.value < steps.value.length) currentStep.value++ }
function prevStep() { if(currentStep.value > 1) currentStep.value-- }

// File upload
function handleFileUpload(e) { form.value.documents = Array.from(e.target.files) }

// Submit
function submitForm() {
  const payload = new FormData()
  Object.keys(form.value).forEach(key => {
    if(key === 'documents') {
      form.value.documents.forEach((file, idx) => payload.append(`documents[${idx}]`, file))
    } else if(form.value[key] !== '' && form.value[key] !== null && form.value[key] !== undefined) {
      // Map camelCase form keys to snake_case for Laravel
      let fieldName = key
      if(key === 'companyName') fieldName = 'company_name'
      if(key === 'contactPerson') fieldName = 'contact_person'
      payload.append(fieldName, form.value[key])
    }
  })
  Inertia.post('/onboarding/submit', payload)
}

// Logout
function logout() { Inertia.post('/logout') }
</script>
