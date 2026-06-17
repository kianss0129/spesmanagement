<template>
  <div
    class="min-h-screen bg-cover bg-center bg-no-repeat relative overflow-hidden"
    style="background-image: url('/images/spes-bg.jpg');"
  >
    <!-- DARK OVERLAY -->
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- TOP LEFT LOGO -->
    <div class="absolute top-5 left-5 z-20 flex items-center gap-3">
      <img
        src="/images/spes-logo.png"
        alt="SPES Logo"
        class="w-12 h-12 rounded-full shadow-lg"
      />

      <h1 class="text-white font-bold text-xl md:text-2xl">
        SPES Management System
      </h1>
    </div>

    <!-- LOGIN CARD -->
    <div class="relative z-10 min-h-screen flex items-center justify-center p-4">

      <div
        class="w-full max-w-md backdrop-blur-xl bg-white/10 border border-white/20 rounded-3xl shadow-2xl p-8"
      >

        <!-- HEADER -->
        <div class="text-center mb-8">
          <h1 class="text-4xl font-extrabold text-white mb-2">
            Welcome Back
          </h1>

          <p class="text-gray-200">
            Sign in to continue to your account
          </p>
        </div>

        <!-- FORM -->
        <form @submit.prevent="submit">

          <!-- EMAIL -->
          <div class="mb-5">
            <label class="block text-sm text-gray-200 mb-2">
              Email Address
            </label>

            <input
              v-model="form.email"
              type="email"
              required
              class="w-full rounded-xl bg-white/10 border border-white/20 text-white px-4 py-3
                     placeholder-gray-300 focus:outline-none focus:ring-2
                     focus:ring-blue-500"
              placeholder="Enter your email"
            />

            <p v-if="errors.email" class="text-xs text-red-300 mt-1">
              {{ errors.email }}
            </p>
          </div>

          <!-- PASSWORD -->
          <div class="mb-5 relative">
            <label class="block text-sm text-gray-200 mb-2">
              Password
            </label>

            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              class="w-full rounded-xl bg-white/10 border border-white/20 text-white px-4 py-3 pr-12
                     placeholder-gray-300 focus:outline-none focus:ring-2
                     focus:ring-blue-500"
              placeholder="Enter your password"
            />

            <!-- SHOW/HIDE PASSWORD -->
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-4 top-11 text-gray-300 hover:text-white"
            >
              👁️
            </button>

            <p v-if="errors.password" class="text-xs text-red-300 mt-1">
              {{ errors.password }}
            </p>
          </div>

          <!-- FORGOT PASSWORD -->
          <div class="flex justify-end mb-5">
            <Link
              href="/forgot-password"
              class="text-sm text-blue-300 hover:text-white hover:underline"
            >
              Forgot password?
            </Link>
          </div>

          <!-- RECAPTCHA -->
          <div class="mb-5">
            <div ref="recaptchaEl"></div>

            <p v-if="recaptchaError" class="text-xs text-red-300 mt-2">
              {{ recaptchaError }}
            </p>

            <p v-if="errors.recaptcha" class="text-xs text-red-300 mt-2">
              {{ errors.recaptcha }}
            </p>
          </div>

          <!-- GOOGLE LOGIN -->
          <div class="mb-5">
            <a
              :href="route('google.login', { role: 'User' })"
              class="w-full bg-white hover:bg-gray-100 text-gray-700 py-3 rounded-xl flex items-center justify-center gap-3 font-semibold shadow-lg transition"
            >
              <svg
                class="w-5 h-5"
                viewBox="0 0 48 48"
              >
                <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3 0 5.8 1.1 8 3l5.7-5.7C34.1 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z"/>
                <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.4 19 12 24 12c3 0 5.8 1.1 8 3l5.7-5.7C34.1 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>
                <path fill="#4CAF50" d="M24 44c5.2 0 10-2 13.6-5.2l-6.3-5.3c-2.1 1.5-4.7 2.5-7.3 2.5-5.3 0-9.8-3.3-11.4-8l-6.6 5.1C9.3 39.7 16 44 24 44z"/>
                <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-1.1 3.2-3.3 5.6-6.3 7.1l6.3 5.3C39.7 36.5 44 31 44 24c0-1.3-.1-2.4-.4-3.5z"/>
              </svg>

              Continue with Google
            </a>
          </div>

          <!-- SUBMIT BUTTON -->
          <button
            type="submit"
            :disabled="processing"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white
                   font-semibold py-3 rounded-xl transition duration-300
                   shadow-lg hover:scale-[1.02] disabled:opacity-60"
          >
            <span v-if="processing">
              Signing in...
            </span>

            <span v-else>
              Login
            </span>
          </button>

        </form>

        <!-- FOOTER -->
        <div class="text-sm text-center text-gray-200 mt-8">
          Don’t have an account?

          <Link
            href="/register/beneficiary"
            class="text-blue-300 hover:text-white hover:underline font-semibold"
          >
            Register as Beneficiary
          </Link>
        </div>

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
const showPassword = ref(false)

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