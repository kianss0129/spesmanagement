<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow p-6">
      <h1 class="text-2xl font-semibold mb-1">PESO Sign in</h1>
      <p class="text-sm text-gray-600 mb-4">
        Authorized PESO personnel only
      </p>

      <form @submit.prevent="submit">
        <!-- Email -->
        <div class="mb-4">
          <label for="email" class="block text-sm text-gray-600 mb-1">Email</label>
          <input
            id="email"
            name="email"
            v-model="form.email"
            type="email"
            required
            autocomplete="email"
            class="w-full border rounded px-3 py-2"
          />
          <p v-if="errors.email" class="text-xs text-red-500 mt-1">
            {{ errors.email }}
          </p>
        </div>

        <!-- Password -->
        <div class="mb-4">
          <label for="password" class="block text-sm text-gray-600 mb-1">Password</label>
          <input
            id="password"
            name="password"
            v-model="form.password"
            type="password"
            required
            autocomplete="current-password"
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

        <!-- Remember Me -->
        <div class="flex items-center justify-between mb-4">
          <label class="flex items-center text-sm" for="remember">
            <input
              id="remember"
              name="remember"
              type="checkbox"
              v-model="form.remember"
              class="mr-2"
            />
            Remember me
          </label>

          <Link href="/forgot-password" class="text-sm text-blue-600 hover:underline">
            Forgot password?
          </Link>
        </div>

        <button
          type="submit"
          :disabled="processing"
          class="w-full bg-green-600 text-white py-2 rounded"
        >
          <span v-if="processing">Signing in...</span>
          <span v-else>Sign in</span>
        </button>
      </form>

      <div class="text-sm text-center mt-4">
        Don’t have an account?
        <Link href="/register/peso" class="text-blue-600 hover:underline">
          Register as PESO
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

const form = useForm({
  email: '',
  password: '',
  remember: false,
  recaptcha: null,
})

const errors = reactive({})
const processing = ref(false)

onMounted(() => {
  window.onRecaptchaSuccess = (token) => {
    form.recaptcha = token
    recaptchaError.value = null
  }

  recaptchaWidgetId.value = window.grecaptcha.render(recaptchaEl.value, {
    sitekey: siteKey,
    callback: window.onRecaptchaSuccess,
  })
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
