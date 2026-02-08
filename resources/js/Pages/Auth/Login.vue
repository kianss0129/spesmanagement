<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-100 to-blue-200 p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">

      <!-- Header -->
      <h1 class="text-2xl font-bold text-blue-700 mb-2">
        Sign in
      </h1>

      <form @submit.prevent="submit">
        <!-- Email -->
        <div class="mb-5">
          <label class="block text-sm text-gray-600 mb-1">
            Email
          </label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full rounded-lg border border-gray-300 px-4 py-2
                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
          <p v-if="errors.email" class="text-xs text-red-500 mt-1">
            {{ errors.email }}
          </p>
        </div>

        <!-- Password -->
        <div class="mb-5">
          <label class="block text-sm text-gray-600 mb-1">
            Password
          </label>
          <input
            v-model="form.password"
            type="password"
            required
            class="w-full rounded-lg border border-gray-300 px-4 py-2
                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
          <p v-if="errors.password" class="text-xs text-red-500 mt-1">
            {{ errors.password }}
          </p>
        </div>

        <!-- reCAPTCHA -->
        <div class="mb-5">
          <div ref="recaptchaEl"></div>

          <p v-if="recaptchaError" class="text-xs text-red-500 mt-1">
            {{ recaptchaError }}
          </p>

          <p v-if="errors.recaptcha" class="text-xs text-red-500 mt-1">
            {{ errors.recaptcha }}
          </p>
        </div>

        <!-- Remember / Forgot -->
<div class="flex items-center justify-between mb-5">

  <Link
    href="/forgot-password"
    class="text-sm text-blue-600 hover:underline"
  >
    Forgot password?
  </Link>
</div>

        

        <!-- Submit -->
        <button
          type="submit"
          :disabled="processing"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium
                 py-2.5 rounded-lg transition disabled:opacity-60"
        >
          <span v-if="processing">Signing in...</span>
          <span v-else>Sign in</span>
        </button>
      </form>

      <!-- Footer -->
      <div class="text-sm text-center text-gray-600 mt-6">
        Don’t have an account?
        <Link
          href="/register/beneficiary"
          class="text-blue-600 hover:underline font-medium"
        >
          Register as Beneficiary
        </Link>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

const siteKey = import.meta.env.VITE_RECAPTCHA_SITE_KEY

const recaptchaEl = ref(null)
const recaptchaWidgetId = ref(null)
const recaptchaError = ref(null)
const processing = ref(false)
const errors = reactive({})

const form = useForm({
  email: '',
  password: '',
  remember: false,
  recaptcha: null,
})

onMounted(() => {
  window.onRecaptchaSuccess = (token) => {
    form.recaptcha = token
    recaptchaError.value = null
  }

  recaptchaWidgetId.value = window.grecaptcha.render(
    recaptchaEl.value,
    {
      sitekey: siteKey,
      callback: window.onRecaptchaSuccess,
    }
  )
})

function submit() {
  if (!form.recaptcha) {
    recaptchaError.value = 'Please verify that you are not a robot.'
    return
  }

  processing.value = true

  form.post('/login', {
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