<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow p-6">
      <h1 class="text-2xl font-semibold mb-1">Employer Sign in</h1>
      <p class="text-sm text-gray-600 mb-4">Login to your employer account</p>

      <form @submit.prevent="submit">
        <div class="mb-4">
          <label class="block text-sm text-gray-600 mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full border rounded px-3 py-2"
          />
          <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">
            {{ form.errors.email }}
          </p>
        </div>

        <div class="mb-4 relative">
          <label class="block text-sm text-gray-600 mb-1">Password</label>
          <input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            required
            class="w-full border rounded px-3 py-2 pr-10"
          />
          <button
            type="button"
            @click="showPassword = !showPassword"
            class="absolute right-3 top-[38px] text-gray-500 hover:text-gray-700"
            :aria-label="showPassword ? 'Hide password' : 'Show password'"
          >
            <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.965 9.965 0 012.622-4.063m2.1-1.6A9.966 9.966 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-4.3 5.92" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
            </svg>
          </button>
          <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">
            {{ form.errors.password }}
          </p>
        </div>

        <!-- ✅ ADDED: reCAPTCHA -->
        <div class="mb-4">
          <div
            class="g-recaptcha"
            :data-sitekey="siteKey"
            data-callback="onRecaptchaSuccess"
          ></div>

          <p v-if="form.errors.recaptcha" class="text-xs text-red-500 mt-1">
            {{ form.errors.recaptcha }}
          </p>

          <p v-if="recaptchaError" class="text-xs text-red-500 mt-1">
            {{ recaptchaError }}
          </p>
        </div>
        <!-- ✅ END -->

        <div class="flex items-center justify-between mb-4">
          <label class="flex items-center text-sm">
            <input type="checkbox" v-model="form.remember" class="mr-2" />
            Remember me
          </label>

          <Link href="/forgot-password" class="text-sm text-blue-600 hover:underline">
            Forgot password?
          </Link>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-blue-600 text-white py-2 rounded"
        >
          <span v-if="form.processing">Signing in...</span>
          <span v-else>Sign in</span>
        </button>
      </form>

      <div class="text-sm text-center mt-4">
        Don’t have an account?
        <Link href="/register/employer" class="text-blue-600 hover:underline">
          Register as Employer
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'

import { onMounted, ref } from 'vue' // ✅ ADDED

const siteKey = import.meta.env.VITE_RECAPTCHA_SITE_KEY // ✅ ADDED
const recaptchaError = ref(null) // ✅ ADDED
const showPassword = ref(false)

const form = useForm({
  email: '',
  password: '',
  remember: false,
  recaptcha: null, // ✅ ADDED
})

onMounted(() => {
  window.onRecaptchaSuccess = (token) => {
    form.recaptcha = token
    recaptchaError.value = null
  }
})

function submit() {
  if (!form.recaptcha) {
    recaptchaError.value = 'Please verify that you are not a robot.'
    return
  }

  form.post('/login', {
    onFinish: () => {
      grecaptcha.reset() // ✅ ADDED
      form.recaptcha = null
    },
  })
}
</script>

<script>
export default { components: { Link } }
</script>
