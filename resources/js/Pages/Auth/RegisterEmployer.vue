<template>
  <div
    class="min-h-screen bg-cover bg-center bg-no-repeat relative flex items-center justify-center p-4 overflow-hidden"
    style="background-image: url('/images/spes-bg.jpg');"
  >
    <!-- OVERLAY -->
    <div class="absolute inset-0 bg-gradient-to-br from-black/80 via-black/60 to-blue-900/60"></div>

    <!-- FLOATING BLUR -->
    <div class="absolute top-10 left-10 w-72 h-72 bg-blue-500/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-10 right-10 w-72 h-72 bg-cyan-400/20 rounded-full blur-3xl"></div>

    <!-- FORM CARD -->
    <div
      class="relative z-10 w-full max-w-md bg-white/10 backdrop-blur-2xl border border-white/20 rounded-3xl shadow-2xl p-8 animate-fade"
    >
      <!-- LOGO -->
      <div class="flex justify-center mb-4">
        <img
          src="/images/spes-logo.png"
          alt="SPES Logo"
          class="w-20 h-20 object-contain drop-shadow-xl"
        />
      </div>

      <!-- TITLE -->
      <div class="text-center mb-6">
        <h1 class="text-3xl font-extrabold text-white tracking-wide">
          Employer Registration
        </h1>

        <p class="text-sm text-gray-200 mt-2">
          Create your employer account to post job opportunities.
        </p>
      </div>

      <!-- FORM -->
      <form @submit.prevent="submit" class="space-y-5">

        <!-- EMAIL -->
        <div>
          <label class="label">Email Address</label>

          <div class="relative">
            <input
              v-model="form.email"
              type="email"
              class="input"
              placeholder="Enter email"
            />

            <span class="icon">✉️</span>
          </div>

          <p v-if="form.errors.email" class="error">
            {{ form.errors.email }}
          </p>
        </div>

        <!-- PASSWORD -->
        <div>
          <label class="label">Password</label>

          <div class="relative">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              class="input pr-12"
              placeholder="Enter password"
            />

            <button
              type="button"
              class="eye"
              @click="showPassword = !showPassword"
            >
              {{ showPassword ? '🙈' : '👁️' }}
            </button>
          </div>

          <div class="mt-2">
            <div class="w-full h-2 rounded-full bg-white/20 overflow-hidden">
              <div
                class="h-full transition-all duration-300"
                :class="passwordStrengthColor"
                :style="{ width: passwordStrength + '%' }"
              ></div>
            </div>

            <p class="text-xs text-gray-300 mt-1">
              Password Strength: {{ passwordStrengthText }}
            </p>
          </div>

          <p v-if="passwordError" class="error">
            {{ passwordError }}
          </p>
        </div>

        <!-- CONFIRM PASSWORD -->
        <div>
          <label class="label">Confirm Password</label>

          <div class="relative">
            <input
              v-model="form.password_confirmation"
              :type="showPasswordConfirmation ? 'text' : 'password'"
              class="input pr-12"
              placeholder="Confirm password"
            />

            <button
              type="button"
              class="eye"
              @click="showPasswordConfirmation = !showPasswordConfirmation"
            >
              {{ showPasswordConfirmation ? '🙈' : '👁️' }}
            </button>
          </div>

          <p
            v-if="form.password_confirmation"
            class="text-xs mt-1"
            :class="passwordsMatch ? 'text-green-300' : 'text-red-300'"
          >
            {{
              passwordsMatch
                ? '✔ Password matched'
                : '✖ Password does not match'
            }}
          </p>

          <p v-if="confirmPasswordError" class="error">
            {{ confirmPasswordError }}
          </p>
        </div>

        <!-- TERMS -->
        <div class="flex items-start gap-3 text-white text-sm">
          <input
            type="checkbox"
            v-model="form.accept_terms"
            class="mt-1 accent-blue-500"
          />

          <span class="leading-relaxed">
            I agree to the
            <button
              type="button"
              class="underline text-cyan-300 hover:text-cyan-200"
              @click="showTermsModal = true"
            >
              Terms & Conditions
            </button>
          </span>
        </div>

        <!-- RECAPTCHA -->
        <div>
          <div
            ref="recaptchaEl"
            class="flex justify-center"
          ></div>

          <p v-if="recaptchaError" class="error text-center">
            {{ recaptchaError }}
          </p>
        </div>

<div class="mb-5">
  <a
    :href="route('google.login', { role: 'Employer' })"
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


        <!-- BUTTON -->
        <button
          type="submit"
          :disabled="processing"
          class="w-full bg-gradient-to-r from-blue-600 to-cyan-500 hover:scale-[1.02] hover:shadow-2xl transition-all duration-300 text-white py-3 rounded-xl font-bold tracking-wide disabled:opacity-50"
        >
          <span v-if="processing">Registering...</span>
          <span v-else>Create Employer Account</span>
        </button>

        <!-- LOGIN -->
        <div class="text-center text-sm text-gray-200">
          Already have an account?

          <a
            href="/login"
            class="text-cyan-300 hover:text-cyan-200 font-semibold"
          >
            Login here
          </a>
        </div>
      </form>
    </div>

    <!-- TERMS MODAL -->
    <transition name="fade">
      <div
        v-if="showTermsModal"
        class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      >
        <div
          class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl animate-scale"
        >
          <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">
            Terms & Conditions
          </h2>

          <div class="text-sm text-gray-600 space-y-3 max-h-80 overflow-y-auto">
            <p>
              Employers must provide accurate and legitimate job postings.
            </p>

            <p>
              Any misleading information or fraudulent activity may result in
              account suspension.
            </p>

            <p>
              The SPES Management System reserves the right to review and remove
              inappropriate job listings.
            </p>

            <p>
              By registering, you agree to comply with all SPES policies and
              applicable labor regulations.
            </p>
          </div>

          <button
            class="mt-6 w-full bg-gradient-to-r from-blue-600 to-cyan-500 hover:opacity-90 text-white py-3 rounded-xl font-semibold transition"
            @click="showTermsModal = false"
          >
            Close
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const siteKey = import.meta.env.VITE_RECAPTCHA_SITE_KEY

const showTermsModal = ref(false)

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const recaptchaEl = ref(null)
const recaptchaWidgetId = ref(null)

const recaptchaError = ref('')
const passwordError = ref('')
const confirmPasswordError = ref('')
const processing = ref(false)

const form = useForm({
  email: '',
  password: '',
  password_confirmation: '',
  accept_terms: false,
  recaptcha: null,
})

function validatePassword(password) {
  return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(password)
}

const passwordsMatch = computed(() => {
  return (
    form.password &&
    form.password_confirmation &&
    form.password === form.password_confirmation
  )
})

const passwordStrength = computed(() => {
  const password = form.password

  let strength = 0

  if (password.length >= 8) strength += 25
  if (/[A-Z]/.test(password)) strength += 25
  if (/[0-9]/.test(password)) strength += 25
  if (/[^A-Za-z0-9]/.test(password)) strength += 25

  return strength
})

const passwordStrengthText = computed(() => {
  if (passwordStrength.value <= 25) return 'Weak'
  if (passwordStrength.value <= 50) return 'Fair'
  if (passwordStrength.value <= 75) return 'Good'
  return 'Strong'
})

const passwordStrengthColor = computed(() => {
  if (passwordStrength.value <= 25) return 'bg-red-500'
  if (passwordStrength.value <= 50) return 'bg-yellow-500'
  if (passwordStrength.value <= 75) return 'bg-blue-500'
  return 'bg-green-500'
})

onMounted(() => {
  window.onRecaptchaSuccess = (token) => {
    form.recaptcha = token
    recaptchaError.value = ''
  }

  if (window.grecaptcha) {
    recaptchaWidgetId.value = window.grecaptcha.render(
      recaptchaEl.value,
      {
        sitekey: siteKey,
        callback: window.onRecaptchaSuccess,
      }
    )
  }
})

function submit() {
  passwordError.value = ''
  confirmPasswordError.value = ''
  recaptchaError.value = ''

  const pass = form.password?.trim()
  const confirm = form.password_confirmation?.trim()

  // PASSWORD CHECK
  if (!pass) {
    passwordError.value = 'Password is required.'
    return
  }

  if (!validatePassword(pass)) {
    passwordError.value =
      'Must contain uppercase, lowercase, number, special character, and 8+ characters.'
    return
  }

  // CONFIRM PASSWORD
  if (pass !== confirm) {
    confirmPasswordError.value = 'Password does not match.'
    return
  }

  // TERMS
  if (!form.accept_terms) {
    alert('You must accept the Terms & Conditions.')
    return
  }

  // RECAPTCHA
  if (!form.recaptcha) {
    recaptchaError.value = 'Please complete reCAPTCHA.'
    return
  }

  processing.value = true

  form.post(route('register.employer.store'), {
    preserveScroll: true,

    onSuccess: () => {
      form.reset()

      window.grecaptcha?.reset(recaptchaWidgetId.value)
    },

    onFinish: () => {
      processing.value = false
      form.recaptcha = null
    },
  })
}
</script>

<style scoped>
.animate-fade {
  animation: fadeIn 0.8s ease;
}

.animate-scale {
  animation: scaleIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.label {
  display: block;
  color: white;
  font-size: 14px;
  margin-bottom: 8px;
  font-weight: 600;
}

.input {
  width: 100%;
  padding: 12px 14px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  outline: none;
  transition: 0.3s ease;
  backdrop-filter: blur(10px);
}

.input::placeholder {
  color: rgba(255, 255, 255, 0.6);
}

.input:focus {
  border-color: rgba(59, 130, 246, 0.8);
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
}

.error {
  color: #ff9c9c;
  font-size: 12px;
  margin-top: 6px;
}

.eye {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: white;
  font-size: 18px;
}

.icon {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  opacity: 0.8;
}
</style>