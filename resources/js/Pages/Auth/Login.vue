<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow p-6">
      <h1 class="text-2xl font-semibold mb-4">Sign in</h1>

      <form @submit.prevent="submit">
        <!-- Email -->
        <div class="mb-4">
          <label class="block text-sm text-gray-600 mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full border rounded px-3 py-2"
          />
          <p v-if="errors.email" class="text-xs text-red-500 mt-1">
            {{ errors.email }}
          </p>
        </div>

        <!-- Password -->
        <div class="mb-4">
          <label class="block text-sm text-gray-600 mb-1">Password</label>
          <input
            v-model="form.password"
            type="password"
            required
            class="w-full border rounded px-3 py-2"
          />
          <p v-if="errors.password" class="text-xs text-red-500 mt-1">
            {{ errors.password }}
          </p>
        </div>

        <!-- ✅ reCAPTCHA -->
        <div class="mb-4">
          <div ref="recaptchaEl"></div>

          <p v-if="recaptchaError" class="text-xs text-red-500 mt-1">
            {{ recaptchaError }}
          </p>

          <p v-if="errors.recaptcha" class="text-xs text-red-500 mt-1">
            {{ errors.recaptcha }}
          </p>
        </div>

        <!-- Remember -->
        <div class="flex items-center justify-between mb-4">
          <label class="flex items-center text-sm">
            <input type="checkbox" v-model="form.remember" class="mr-2" />
            Remember me
          </label>

          <Link href="/forgot-password" class="text-sm text-blue-600 hover:underline">
            Forgot password?
          </Link>
        </div>

        <!-- Submit -->
        <button
          type="submit"
          :disabled="processing"
          class="w-full bg-blue-600 text-white py-2 rounded"
        >
          <span v-if="processing">Signing in...</span>
          <span v-else>Sign in</span>
        </button>
      </form>

      <div class="text-sm text-center mt-4">
        Don’t have an account?
        <Link href="/register/beneficiary" class="text-blue-600 hover:underline">
          Register as Beneficiary
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useForm, Link } from '@inertiajs/inertia-vue3'

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

  // Render manually (SPA-safe)
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
