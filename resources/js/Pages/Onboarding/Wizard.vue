  <script setup>
  import { ref, computed } from 'vue'

  /* Props (from Inertia) */
  const props = defineProps({
    user: Object
  })

  /* Step control */
  const step = ref(1)

  /* Terms modal */
  const showTerms = ref(false)
  const acceptedTerms = ref(false)

  /* Step indicator styles */
  const activeStep = 'w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center'
  const inactiveStep = 'w-8 h-8 rounded-full bg-gray-300 text-gray-700 flex items-center justify-center'

  /* Dummy step components (replace later) */
  const StepOne = {
    template: `<div><h3 class="text-lg font-semibold mb-2">Profile Information</h3><p class="text-gray-600">Fill in your personal details.</p></div>`
  }

  const StepTwo = {
    template: `<div><h3 class="text-lg font-semibold mb-2">Upload Documents</h3><p class="text-gray-600">Upload your required documents.</p></div>`
  }

  const StepThree = {
    template: `<div><h3 class="text-lg font-semibold mb-2">Confirm Details</h3><p class="text-gray-600">Review your information.</p></div>`
  }

  const StepFour = {
    template: `<div><h3 class="text-lg font-semibold mb-2">Final Step</h3><p class="text-gray-600">Accept the terms to complete registration.</p></div>`
  }

  /* Component resolver */
  const currentComponent = computed(() => {
    switch (step.value) {
      case 1: return StepOne
      case 2: return StepTwo
      case 3: return StepThree
      case 4: return StepFour
      default: return StepOne
    }
  })

  /* Navigation */
  function nextStep() {
    if (step.value === 4) {
      if (!acceptedTerms.value) return
      submitForm()
      return
    }

    step.value++
  }

  /* Final submit */
  function submitForm() {
    alert('Registration submitted successfully!')

    // Example Inertia post:
    // router.post('/beneficiary/register', formData)
  }
  </script>

  <template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
      <div class="bg-white rounded-xl shadow-lg w-full max-w-3xl p-8">

        <h2 class="text-2xl font-bold mb-1">Welcome, {{ user?.name }} 👋</h2>
        <p class="text-gray-600 mb-6">Please complete your requirements to continue.</p>

        <!-- Steps -->
        <div class="flex justify-between mb-8">
          <div v-for="n in 4" :key="n" class="flex items-center">
            <div :class="step >= n ? activeStep : inactiveStep">{{ n }}</div>
            <div v-if="n < 4" class="w-16 h-1 bg-gray-300"></div>
          </div>
        </div>

        <!-- Step content -->
        <component :is="currentComponent" />

        <!-- Terms section -->
        <div v-if="step === 4" class="mt-6">

          <div class="flex items-start gap-2 mb-2">
            <input type="checkbox" v-model="acceptedTerms" class="mt-1" />
            <span class="text-sm text-gray-700">
              I agree to the
              <button
                type="button"
                @click="showTerms = true"
                class="text-blue-600 underline hover:text-blue-800"
              >
                Terms & Conditions (SPES Policy)
              </button>
            </span>
          </div>

          <!-- Modal -->
          <div v-if="showTerms" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg w-full max-w-2xl p-6 relative">

              <h3 class="text-lg font-semibold mb-4">SPES Program – Terms & Conditions</h3>

              <div class="h-64 overflow-y-auto text-sm text-gray-700 space-y-3">
                <p><strong>1. Eligibility</strong> – Applicant must be currently enrolled and qualified under SPES guidelines.</p>
                <p><strong>2. Documents</strong> – All submitted documents must be valid and authentic.</p>
                <p><strong>3. Attendance</strong> – Beneficiaries must strictly follow assigned schedules.</p>
                <p><strong>4. Conduct</strong> – Proper behavior and workplace discipline is required.</p>
                <p><strong>5. Termination</strong> – Violation of rules may result in disqualification.</p>
                <p><strong>6. Data Privacy</strong> – Personal data will be used solely for program processing.</p>
              </div>

              <div class="text-right mt-4">
                <button
                  @click="showTerms = false"
                  class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                  Close
                </button>
              </div>

            </div>
          </div>

        </div>

        <!-- Navigation -->
        <div class="flex justify-between mt-8">
          <button
            v-if="step > 1"
            @click="step--"
            class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"
          >
            Back
          </button>

          <button
            @click="nextStep"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            :disabled="step === 4 && !acceptedTerms"
            :class="{ 'opacity-50 cursor-not-allowed': step === 4 && !acceptedTerms }"
          >
            {{ step === 4 ? 'Register' : 'Next' }}
          </button>
        </div>

      </div>
    </div>
  </template>
