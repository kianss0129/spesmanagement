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

        <div class="mb-4">
          <label class="block text-sm text-gray-600 mb-1">Password</label>
          <input
            v-model="form.password"
            type="password"
            required
            class="w-full border rounded px-3 py-2"
          />
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
import { useForm, Link } from '@inertiajs/inertia-vue3'
import { onMounted, ref } from 'vue' // ✅ ADDED

const siteKey = import.meta.env.VITE_RECAPTCHA_SITE_KEY // ✅ ADDED
const recaptchaError = ref(null) // ✅ ADDED

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
