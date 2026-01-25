<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

import { route } from 'ziggy-js'

const showTypeModal = ref(true)
const showTermsModal = ref(false)
const agreedToTerms = ref(false)
const selectedType = ref('')

// Inertia form
const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  type: '',
})

onMounted(() => {
  showTypeModal.value = true
})

// Select beneficiary type
function selectType(type) {
  selectedType.value = type
  form.type = type
  showTypeModal.value = false
}

// Computed label
const typeLabel = computed(() => {
  if (selectedType.value === 'student') return 'Student'
  if (selectedType.value === 'osy') return 'Out-of-School Youth (OSY)'
  if (selectedType.value === 'dependent') return 'Dependent of Displaced Workers'
  return ''
})

// Submit form
function submit() {
  if (!agreedToTerms.value) {
    alert('You must agree to the Terms & Conditions.')
    return
  }

  form.post(route('register.beneficiary.store'), {
    onSuccess: () => {
      console.log('Registration successful')
    },
    onError: (errors) => {
      console.error('Validation errors:', errors)
    },
  })
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">

    <!-- Beneficiary Type Modal -->
    <div v-if="showTypeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-xl w-96">
        <h3 class="text-xl font-semibold mb-4 text-center">Select Beneficiary Type</h3>

        <button class="btn w-full" @click="selectType('student')">Student</button>
        <button class="btn w-full mt-2" @click="selectType('osy')">Out-of-School Youth (OSY)</button>
        <button class="btn w-full mt-2" @click="selectType('dependent')">Dependent of Displaced Workers</button>
      </div>
    </div>

    <!-- Registration Form -->
    <div v-if="!showTypeModal" class="w-full max-w-md bg-white p-6 rounded shadow">
      <h1 class="text-xl font-bold mb-2 text-center">Beneficiary Registration</h1>
      <p class="text-sm text-center mb-4">
        Registering as: <strong>{{ typeLabel }}</strong>
      </p>

      <form @submit.prevent="submit">

        <!-- Full Name -->
        <input
          id="name"
          name="name"
          autocomplete="name"
          v-model="form.name"
          class="input"
          placeholder="Full Name"
        />
        <div class="error" v-if="form.errors.name">{{ form.errors.name }}</div>

        <!-- Email -->
        <input
          id="email"
          name="email"
          autocomplete="email"
          v-model="form.email"
          class="input"
          placeholder="Email"
        />
        <div class="error" v-if="form.errors.email">{{ form.errors.email }}</div>

        <!-- Password -->
        <input
          id="password"
          name="password"
          type="password"
          autocomplete="new-password"
          v-model="form.password"
          class="input"
          placeholder="Password"
        />
        <div class="error" v-if="form.errors.password">{{ form.errors.password }}</div>

        <!-- Confirm Password -->
        <input
          id="password_confirmation"
          name="password_confirmation"
          type="password"
          autocomplete="new-password"
          v-model="form.password_confirmation"
          class="input"
          placeholder="Confirm Password"
        />
        <div class="error" v-if="form.errors.password_confirmation">{{ form.errors.password_confirmation }}</div>

        <!-- Hidden Beneficiary Type -->
        <input
          type="hidden"
          id="type"
          name="type"
          v-model="form.type"
        />

        <!-- Terms & Conditions -->
        <div class="mt-3 flex items-center gap-2 text-sm">
          <input
            id="terms"
            name="terms"
            type="checkbox"
            v-model="agreedToTerms"
          />
          <label for="terms">
            I agree to the
            <button
              type="button"
              class="text-blue-600 underline"
              @click="showTermsModal = true"
            >
              Terms & Conditions
            </button>
          </label>
        </div>

        <!-- Register Button -->
        <button
          class="btn-primary w-full mt-4"
          type="submit"
          :disabled="form.processing || !agreedToTerms"
        >
          Register
        </button>

        <p class="text-sm text-center mt-3">
          Already have an account?
          <Link :href="route('login')" class="text-blue-600 underline">Login</Link>
        </p>
      </form>
    </div>

    <!-- Terms & Conditions Modal -->
<div
  v-if="showTermsModal"
  class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
>
  <div class="bg-white rounded-xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
    
    <!-- Title -->
    <h3 class="text-xl font-semibold mb-4 text-center">
      TERMS AND CONDITIONS<br />
      <span class="text-sm font-normal">
        Special Program for the Employment of Students (SPES)
      </span>
    </h3>

    <!-- Intro -->
    <p class="text-sm mb-4 text-justify">
      By registering for and participating in the Special Program for the Employment
      of Students (SPES), the applicant agrees to comply with the following terms and
      conditions set by the Department of Labor and Employment (DOLE) in accordance
      with Republic Act No. 7323.
    </p>

    <!-- 1 -->
    <h4 class="font-semibold mt-4">1. Eligibility</h4>
    <ul class="list-disc pl-5 text-sm space-y-1">
      <li>A Filipino student aged 15 to 30 years old</li>
      <li>Currently enrolled in Senior High School, College, or Technical-Vocational education</li>
      <li>From a low-income family, as defined by DOLE guidelines</li>
      <li>Physically and mentally fit to work</li>
      <li>Not a beneficiary of SPES for the same school year, unless otherwise allowed</li>
    </ul>

    <!-- 2 -->
    <h4 class="font-semibold mt-4">2. Nature of Employment</h4>
    <ul class="list-disc pl-5 text-sm space-y-1">
      <li>SPES provides temporary employment only</li>
      <li>Employment shall not exceed fifty-two (52) working days</li>
      <li>Work may be assigned in government offices or private establishments</li>
      <li>Tasks shall be safe, lawful, and appropriate to the applicant’s age and capability</li>
    </ul>

    <!-- 3 -->
    <h4 class="font-semibold mt-4">3. Work Schedule and Conduct</h4>
    <ul class="list-disc pl-5 text-sm space-y-1">
      <li>Follow the assigned work schedule</li>
      <li>Observe workplace rules, policies, and discipline</li>
      <li>Perform duties honestly, responsibly, and efficiently</li>
      <li>Maintain professional behavior at all times</li>
    </ul>

    <!-- 4 -->
    <h4 class="font-semibold mt-4">4. Compensation</h4>
    <ul class="list-disc pl-5 text-sm space-y-1">
      <li>Wages shall be based on the prevailing regional minimum wage</li>
      <li>60% of wages shall be paid by the employer</li>
      <li>40% of wages shall be subsidized by DOLE</li>
      <li>Compensation is released upon completion of the employment period</li>
    </ul>

    <!-- 5 -->
    <h4 class="font-semibold mt-4">5. Use of Earnings</h4>
    <p class="text-sm text-justify">
      Earnings from SPES shall be used primarily for educational expenses, including
      tuition fees, school supplies, and other academic needs.
    </p>

    <!-- 6 -->
    <h4 class="font-semibold mt-4">6. Required Documents</h4>
    <ul class="list-disc pl-5 text-sm space-y-1">
      <li>Certificate of Enrollment or School ID</li>
      <li>Barangay Certificate of Residency</li>
      <li>Parent/Guardian Consent (for minors)</li>
      <li>Proof of identity and income, if required</li>
    </ul>

    <!-- 7 -->
    <h4 class="font-semibold mt-4">7. Disqualification and Termination</h4>
    <ul class="list-disc pl-5 text-sm space-y-1">
      <li>Providing false or misleading information</li>
      <li>Failure to comply with work requirements</li>
      <li>Misconduct or repeated absenteeism</li>
      <li>Abandonment of assigned duties without valid reason</li>
    </ul>
    <p class="text-sm mt-2">
      Disqualification may result in non-payment of subsidy and ineligibility for future
      SPES participation.
    </p>

    <!-- 8 -->
    <h4 class="font-semibold mt-4">8. Data Privacy</h4>
    <p class="text-sm text-justify">
      All personal information collected shall be handled in accordance with the Data
      Privacy Act of 2012 (RA 10173) and shall be used solely for SPES implementation
      and monitoring.
    </p>

    <!-- 9 -->
    <h4 class="font-semibold mt-4">9. Acknowledgment and Consent</h4>
    <p class="text-sm text-justify">
      By submitting an application, the applicant confirms that all information provided
      is accurate, agrees to abide by these Terms and Conditions, and understands that
      SPES participation is subject to approval and availability of slots.
    </p>

    <!-- Close Button -->
    <div class="text-center mt-6">
      <button
        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        @click="showTermsModal = false"
      >
        Close
      </button>
    </div>

  </div>
</div>

  </div>
</template>

<style scoped>
.input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  margin-top: 10px;
}

.btn {
  border: 1px solid #4f46e5;
  padding: 10px;
  border-radius: 6px;
}

.btn-primary {
  background: #4f46e5;
  color: white;
  padding: 10px;
  border-radius: 6px;
  transition: background 0.2s;
}
.btn-primary:disabled {
  background: #a5b4fc;
  cursor: not-allowed;
}

.error {
  color: red;
  font-size: 12px;
}
</style>
