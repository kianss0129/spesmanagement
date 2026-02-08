<template>
  <div class="min-h-screen flex items-center justify-center bg-blue-50 p-4">

    <!-- Beneficiary Type Modal -->
    <div v-if="showTypeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-xl w-96 shadow-lg">
        <h3 class="text-xl font-semibold mb-4 text-center text-blue-700">Select Beneficiary Type</h3>

        <button class="btn w-full text-blue-700 border-blue-700" @click="selectType('student')">Student</button>
        <button class="btn w-full mt-2 text-blue-700 border-blue-700" @click="selectType('osy')">Out-of-School Youth (OSY)</button>
        <button class="btn w-full mt-2 text-blue-700 border-blue-700" @click="selectType('dependent')">Dependent of Displaced Workers</button>
      </div>
    </div>

    <!-- Registration Form -->
    <div v-if="!showTypeModal" class="w-full max-w-md bg-white p-6 rounded shadow-lg border border-blue-200">
      <h1 class="text-xl font-bold mb-2 text-center text-blue-700">Beneficiary Registration</h1>
      <p class="text-sm text-center mb-4 text-blue-600">
        Registering as: <strong>{{ typeLabel }}</strong>
      </p>

      <form @submit.prevent="submit">

        <!-- Name -->
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
        <input type="hidden" id="type" name="type" v-model="form.type" />

        <!-- Terms & Conditions -->
        <div class="mt-3 flex items-center gap-2 text-sm">
          <input
            id="terms"
            name="terms"
            type="checkbox"
            v-model="agreedToTerms"
            class="form-checkbox border-blue-600 text-blue-600"
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

        <!-- reCAPTCHA -->
        <div class="my-4">
          <div ref="recaptchaEl"></div>
          <p v-if="recaptchaError" class="text-xs text-red-500 mt-1">
            {{ recaptchaError }}
          </p>
        </div>

        <!-- Register Button -->
        <button
          class="btn-primary w-full mt-4"
          type="submit"
          :disabled="form.processing || !agreedToTerms || processing"
        >
          <span v-if="processing">Registering...</span>
          <span v-else>Register</span>
        </button>

        <p class="text-sm text-center mt-3">
          Already have an account?
          <Link :href="route('login')" class="text-blue-600 underline">Login</Link>
        </p>
      </form>
    </div>

    <!-- Terms & Conditions Modal -->
    <div v-if="showTermsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-lg border border-blue-200">
        <h3 class="text-xl font-semibold mb-4 text-center text-blue-700">
          TERMS AND CONDITIONS<br />
          <span class="text-sm font-normal text-blue-600">
            Special Program for the Employment of Students (SPES)
          </span>
        </h3>

        <p class="text-sm mb-4 text-justify text-blue-700">
          By registering for and participating in the Special Program for the Employment
          of Students (SPES), the applicant agrees to comply with the following terms and
          conditions set by the Department of Labor and Employment (DOLE) in accordance
          with Republic Act No. 7323.
        </p>

        <h4 class="font-semibold mt-4 text-blue-700">1. Eligibility</h4>
        <ul class="list-disc pl-5 text-sm space-y-1 text-blue-700">
          <li>A Filipino student aged 15 to 30 years old</li>
          <li>Currently enrolled in Senior High School, College, or Technical-Vocational education</li>
          <li>From a low-income family, as defined by DOLE guidelines</li>
          <li>Physically and mentally fit to work</li>
          <li>Not a beneficiary of SPES for the same school year, unless otherwise allowed</li>
        </ul>

        <h4 class="font-semibold mt-4 text-blue-700">2. Nature of Employment</h4>
        <ul class="list-disc pl-5 text-sm space-y-1 text-blue-700">
          <li>SPES provides temporary employment only</li>
          <li>Employment shall not exceed fifty-two (52) working days</li>
          <li>Work may be assigned in government offices or private establishments</li>
          <li>Tasks shall be safe, lawful, and appropriate to the applicant’s age and capability</li>
        </ul>

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

<script setup>
import { ref, computed, nextTick, reactive } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const showTypeModal = ref(true)
const showTermsModal = ref(false)
const agreedToTerms = ref(false)
const selectedType = ref('')

const recaptchaEl = ref(null)
const recaptchaWidgetId = ref(null)
const recaptchaError = ref(null)
const processing = ref(false)
const errors = reactive({})

const siteKey = import.meta.env.VITE_RECAPTCHA_SITE_KEY

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  type: '',
  recaptcha: null
})

// Select beneficiary type
async function selectType(type) {
  selectedType.value = type
  form.type = type
  showTypeModal.value = false

  // Wait for form to render
  await nextTick()

  // Initialize reCAPTCHA after form is visible
  if (recaptchaEl.value && window.grecaptcha) {
    recaptchaWidgetId.value = window.grecaptcha.render(recaptchaEl.value, {
      sitekey: siteKey,
      callback: (token) => {
        form.recaptcha = token
        recaptchaError.value = null
      }
    })
  }
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

  if (!form.recaptcha) {
    recaptchaError.value = 'Please verify that you are not a robot.'
    return
  }

  processing.value = true

  form.post(route('register.beneficiary.store'), {
    onSuccess: () => {
      processing.value = false
      if (window.grecaptcha) window.grecaptcha.reset(recaptchaWidgetId.value)
      form.recaptcha = null
      console.log('Registration successful')
    },
    onError: (errs) => {
      processing.value = false
      Object.assign(errors, form.errors)
      if (window.grecaptcha) window.grecaptcha.reset(recaptchaWidgetId.value)
      form.recaptcha = null
      console.error('Validation errors:', errs)
    }
  })
}
</script>

<style scoped>
.input {
  width: 100%;
  padding: 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  margin-top: 10px;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.input:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}

.btn {
  border: 1px solid #2563eb;
  padding: 12px;
  border-radius: 8px;
  background: white;
  font-weight: 500;
  transition: background 0.2s, color 0.2s, transform 0.1s;
}
.btn:hover {
  background: #2563eb;
  color: white;
  transform: translateY(-1px);
}

.btn-primary {
  background: #2563eb;
  color: white;
  padding: 12px;
  border-radius: 8px;
  font-weight: 500;
  transition: background 0.2s, transform 0.1s;
}
.btn-primary:hover {
  background: #1e40af;
  transform: translateY(-1px);
}
.btn-primary:disabled {
  background: #93c5fd;
  cursor: not-allowed;
}

.error {
  color: red;
  font-size: 12px;
}

.w-full.max-w-md {
  box-shadow: 0 10px 25px rgba(37, 99, 235, 0.1);
  border-radius: 12px;
}

.form-checkbox {
  width: 16px;
  height: 16px;
  border: 1px solid #2563eb;
  border-radius: 4px;
}
</style>