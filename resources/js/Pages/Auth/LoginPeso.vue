<template>
  <div
    class="min-h-screen bg-cover bg-center bg-no-repeat relative overflow-hidden"
    style="background-image: url('/images/spes-bg.jpg');"
  >
    <!-- OVERLAY -->
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- LOGO -->
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
    <div
      class="relative z-10 min-h-screen flex items-center justify-center p-4"
    >
      <div
        class="w-full max-w-md backdrop-blur-xl bg-white/10 border border-white/20 rounded-3xl shadow-2xl p-8"
      >

        <!-- HEADER -->
        <div class="text-center mb-8">
          <h1 class="text-4xl font-extrabold text-white mb-2">
            PESO Sign In
          </h1>

          <p class="text-gray-200">
            Authorized PESO personnel only
          </p>
        </div>

        <!-- FORM -->
        <form @submit.prevent="submit">

          <!-- EMAIL -->
          <div class="mb-5">
            <label
              for="email"
              class="block text-sm text-gray-200 mb-2"
            >
              Email Address
            </label>

            <input
              id="email"
              name="email"
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              placeholder="Enter your email"
              class="input"
            />

            <p
              v-if="errors.email"
              class="error"
            >
              {{ errors.email }}
            </p>
          </div>

          <!-- PASSWORD -->
          <div class="mb-5 relative">
            <label
              for="password"
              class="block text-sm text-gray-200 mb-2"
            >
              Password
            </label>

            <input
              id="password"
              name="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              autocomplete="current-password"
              placeholder="Enter your password"
              class="input pr-12"
            />

            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-4 top-11 text-gray-300 hover:text-white"
              :aria-label="showPassword ? 'Hide password' : 'Show password'"
            >
              👁️
            </button>

            <p
              v-if="errors.password"
              class="error"
            >
              {{ errors.password }}
            </p>
          </div>

          <!-- RECAPTCHA -->
          <div class="mb-5">
            <div ref="recaptchaEl"></div>

            <p
              v-if="recaptchaError"
              class="error"
            >
              {{ recaptchaError }}
            </p>

            <p
              v-if="errors.recaptcha"
              class="error"
            >
              {{ errors.recaptcha }}
            </p>
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
              Sign In
            </span>
          </button>

        </form>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'

const siteKey = import.meta.env.VITE_RECAPTCHA_SITE_KEY

const recaptchaEl = ref(null)
const recaptchaWidgetId = ref(null)
const recaptchaError = ref(null)

const showPassword = ref(false)
const processing = ref(false)

const errors = reactive({})

const form = useForm({
  email: '',
  password: '',
  remember: false,
  recaptcha: null,
  role: 'peso',
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
    recaptchaError.value =
      'Please verify that you are not a robot.'

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

<style scoped>
.input {
  width: 100%;
  padding: 14px 16px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  outline: none;
  transition: 0.3s;
}

.input::placeholder {
  color: rgba(255, 255, 255, 0.7);
}

.input:focus {
  border-color: #60a5fa;
  box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
}

.error {
  color: #fca5a5;
  font-size: 12px;
  margin-top: 6px;
}
</style>