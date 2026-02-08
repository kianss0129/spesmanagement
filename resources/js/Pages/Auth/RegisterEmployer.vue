<template>
  <div class="min-h-screen flex items-center justify-center bg-blue-50 p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
      <h1 class="text-2xl font-bold text-center text-blue-700 mb-6">
        Register as an Employer 
      </h1>

      <form @submit.prevent="submit" class="space-y-4">

        <!-- Full Name -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">
            Full Name
          </label>
          <input
            v-model="form.name"
            id="name"
            type="text"
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
          <p v-if="errors.name" class="text-xs text-red-500 mt-1">
            {{ errors.name }}
          </p>
        </div>

        <!-- Company Name -->
        <div>
          <label for="company_name" class="block text-sm font-medium text-gray-700">
            Company Name
          </label>
          <input
            v-model="form.company_name"
            id="company_name"
            type="text"
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
          <p v-if="errors.company_name" class="text-xs text-red-500 mt-1">
            {{ errors.company_name }}
          </p>
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">
            Email
          </label>
          <input
            v-model="form.email"
            id="email"
            type="email"
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
          <p v-if="errors.email" class="text-xs text-red-500 mt-1">
            {{ errors.email }}
          </p>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">
            Password
          </label>
          <input
            v-model="form.password"
            id="password"
            type="password"
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
          <p v-if="errors.password" class="text-xs text-red-500 mt-1">
            {{ errors.password }}
          </p>
        </div>

        <!-- Confirm Password -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
            Confirm Password
          </label>
          <input
            v-model="form.password_confirmation"
            id="password_confirmation"
            type="password"
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
        </div>

        <!-- Terms & Conditions Checkbox -->
        <div class="flex items-center gap-2 text-sm">
          <input
            type="checkbox"
            id="accept_terms"
            v-model="form.accept_terms"
            class="form-checkbox"
          />
          <label for="accept_terms" class="text-sm">
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

        <!-- Submit -->
        <button
          type="submit"
          :disabled="form.processing || !form.accept_terms"
          class="w-full bg-blue-600 text-white py-2 rounded-lg font-medium hover:bg-blue-700 transition"
        >
          <span v-if="processing">Registering...</span>
          <span v-else>Register</span>
        </button>

        <!-- Login Link -->
        <p class="text-sm text-center text-gray-600">
          Already have an account?
          <Link href="/login" class="text-blue-600 underline">Login</Link>
        </p>
      </form>
    </div>

    <!-- Terms & Conditions Modal -->
    <div v-if="showTermsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl p-6 w-full max-w-lg relative max-h-[80vh] overflow-y-auto">
        <h3 class="text-xl font-semibold mb-4 text-center">Terms & Conditions</h3>
        <div class="space-y-3 text-sm text-gray-700">
          <p><strong>1. Eligibility:</strong> Only legitimate employers or companies may register.</p>
          <p><strong>2. Job Posting:</strong> Must provide accurate and legal job information.</p>
          <p><strong>3. Conduct:</strong> Misuse of the system may result in account suspension.</p>
          <p><strong>4. Data Privacy:</strong> Data will be used only for SPES administration.</p>
          <p><strong>5. Agreement:</strong> By registering, you accept all SPES policies.</p>
        </div>
        <div class="mt-4 text-center">
          <button class="px-6 py-2 bg-blue-600 text-white rounded-lg" @click="showTermsModal = false">Close</button>
        </div>
        <button class="absolute top-2 right-3 text-xl text-gray-500" @click="showTermsModal = false" aria-label="Close Terms Modal">×</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const siteKey = import.meta.env.VITE_RECAPTCHA_SITE_KEY

const showTermsModal = ref(false)
const recaptchaEl = ref(null)
const recaptchaWidgetId = ref(null)
const recaptchaError = ref(null)
const processing = ref(false)
const errors = reactive({})

const form = useForm({
  name: '',
  company_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  accept_terms: false,
  recaptcha: null,
})

onMounted(() => {
  window.onRecaptchaSuccess = (token) => {
    form.recaptcha = token
    recaptchaError.value = null
  }

  // Wait for grecaptcha to load
  if (window.grecaptcha) {
    recaptchaWidgetId.value = window.grecaptcha.render(
      recaptchaEl.value,
      {
        sitekey: siteKey,
        callback: window.onRecaptchaSuccess,
      }
    )
  } else {
    // Retry after a short delay if grecaptcha isn't loaded yet
    const retryCount = 5
    let attempts = 0
    const retryInterval = setInterval(() => {
      attempts++
      if (window.grecaptcha && recaptchaEl.value) {
        recaptchaWidgetId.value = window.grecaptcha.render(
          recaptchaEl.value,
          {
            sitekey: siteKey,
            callback: window.onRecaptchaSuccess,
          }
        )
        clearInterval(retryInterval)
      } else if (attempts >= retryCount) {
        console.error('reCAPTCHA failed to load')
        recaptchaError.value = 'Failed to load reCAPTCHA. Please refresh the page.'
        clearInterval(retryInterval)
      }
    }, 100)
  }
})

const submit = () => {
  if (!form.accept_terms) {
    alert('You must agree to the Terms & Conditions.')
    return
  }

  if (!form.recaptcha) {
    recaptchaError.value = 'Please verify that you are not a robot.'
    return
  }

  processing.value = true

  form.post(route('register.employer.store'), {
    onSuccess: () => {
      processing.value = false
      window.grecaptcha.reset(recaptchaWidgetId.value)
      form.recaptcha = null
    },
    onError: () => {
      processing.value = false
      Object.assign(errors, form.errors)
      window.grecaptcha.reset(recaptchaWidgetId.value)
      form.recaptcha = null
    },
  })
}
</script>

<style scoped>
.form-checkbox {
  width: 16px;
  height: 16px;
  border: 1px solid #ddd;
  border-radius: 4px;
}
</style>