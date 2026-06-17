<template>
  <div
    class="min-h-screen bg-cover bg-center bg-no-repeat relative overflow-hidden"
    style="background-image: url('/images/spes-bg.jpg');"
  >
    <!-- OVERLAY -->
    <div class="absolute inset-0 bg-gradient-to-br from-black/80 via-black/60 to-blue-900/70"></div>

    <!-- BLUR EFFECTS -->
    <div class="absolute top-0 left-0 w-80 h-80 bg-blue-500/20 blur-3xl rounded-full"></div>
    <div class="absolute bottom-0 right-0 w-80 h-80 bg-cyan-400/20 blur-3xl rounded-full"></div>

    <!-- LOGO -->
    <div class="absolute top-5 left-5 z-20 flex items-center gap-3">
      <img
        src="/images/spes-logo.png"
        alt="SPES Logo"
        class="w-14 h-14 rounded-full shadow-2xl border border-white/20"
      />

      <div>
        <h1 class="text-white font-bold text-xl md:text-2xl">
          SPES Management System
        </h1>

        <p class="text-gray-300 text-sm">
          DOLE Employment Portal
        </p>
      </div>
    </div>

    <!-- TYPE MODAL -->
    <transition name="fade">
      <div
        v-if="showTypeModal"
        class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      >
        <div
          class="w-full max-w-md bg-white/10 backdrop-blur-2xl border border-white/20 rounded-3xl shadow-2xl p-8 animate-scale"
        >
          <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-white mb-2">
              Select Beneficiary Type
            </h2>

            <p class="text-gray-300">
              Choose your registration category
            </p>
          </div>

          <div class="space-y-4">

            <button
              class="type-btn"
              @click="selectType('student')"
            >
              <span class="text-2xl">👨‍🎓</span>

              <div class="text-left">
                <h3 class="font-bold">Student</h3>
                <p class="text-xs text-gray-300">
                  Currently enrolled students
                </p>
              </div>
            </button>

            <button
              class="type-btn"
              @click="selectType('osy')"
            >
              <span class="text-2xl">🧑</span>

              <div class="text-left">
                <h3 class="font-bold">
                  Out-of-School Youth
                </h3>

                <p class="text-xs text-gray-300">
                  Youth not currently enrolled
                </p>
              </div>
            </button>

            <button
              class="type-btn"
              @click="selectType('dependent')"
            >
              <span class="text-2xl">👨‍👩‍👧</span>

              <div class="text-left">
                <h3 class="font-bold">
                  Dependent of Displaced Workers
                </h3>

                <p class="text-xs text-gray-300">
                  Qualified dependents
                </p>
              </div>
            </button>

          </div>
        </div>
      </div>
    </transition>

    <!-- REGISTER FORM -->
    <div
      v-if="!showTypeModal"
      class="relative z-10 min-h-screen flex items-center justify-center p-4"
    >
      <div
        class="w-full max-w-md bg-white/10 backdrop-blur-2xl border border-white/20 rounded-3xl shadow-2xl p-8 animate-fade"
      >

        <!-- HEADER -->
        <div class="text-center mb-8">

          <div class="flex justify-center mb-4">
            <img
              src="/images/spes-logo.png"
              alt="SPES"
              class="w-20 h-20 object-contain drop-shadow-2xl"
            />
          </div>

          <h1 class="text-4xl font-extrabold text-white mb-2">
            Create Account
          </h1>

          <p class="text-gray-200">
            Registering as:

            <span class="font-semibold text-cyan-300">
              {{ typeLabel }}
            </span>
          </p>
        </div>

        <!-- FORM -->
        <form @submit.prevent="submit">

       

          <!-- EMAIL -->
          <div class="mb-5">
            <label class="label">
              Email Address
            </label>

            <div class="relative">
              <input
                v-model="form.email"
                type="email"
                placeholder="Enter your email"
                class="input"
              />

              <span class="icon">✉️</span>
            </div>

            <p v-if="form.errors.email" class="error">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- PASSWORD -->
          <div class="mb-5">
            <label class="label">
              Password
            </label>

            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Enter your password"
                class="input pr-12"
              />

              <button
                type="button"
                @click="showPassword = !showPassword"
                class="eye"
              >
                {{ showPassword ? '🙈' : '👁️' }}
              </button>
            </div>

            <!-- PASSWORD STRENGTH -->
            <div class="mt-3">
              <div class="w-full h-2 rounded-full bg-white/20 overflow-hidden">
                <div
                  class="h-full transition-all duration-300"
                  :class="passwordStrengthColor"
                  :style="{ width: passwordStrength + '%' }"
                ></div>
              </div>

              <p class="text-xs text-gray-300 mt-1">
                Password Strength:
                {{ passwordStrengthText }}
              </p>
            </div>

            <p v-if="passwordError" class="error">
              {{ passwordError }}
            </p>
          </div>

          <!-- CONFIRM PASSWORD -->
          <div class="mb-5">
            <label class="label">
              Confirm Password
            </label>

            <div class="relative">
              <input
                v-model="form.password_confirmation"
                :type="showPasswordConfirmation ? 'text' : 'password'"
                placeholder="Confirm your password"
                class="input pr-12"
              />

              <button
                type="button"
                @click="showPasswordConfirmation = !showPasswordConfirmation"
                class="eye"
              >
                {{ showPasswordConfirmation ? '🙈' : '👁️' }}
              </button>
            </div>

            <p
              v-if="form.password_confirmation"
              class="text-xs mt-2"
              :class="passwordsMatch ? 'text-green-300' : 'text-red-300'"
            >
              {{
                passwordsMatch
                  ? '✔ Password matched'
                  : '✖ Password does not match'
              }}
            </p>
          </div>

          <!-- TERMS -->
          <div class="flex items-start gap-3 mb-5">
            <input
              type="checkbox"
              v-model="agreedToTerms"
              class="mt-1 accent-blue-500"
            />

            <label class="text-sm text-gray-200 leading-relaxed">
              I agree to the

              <button
                type="button"
                @click="showTermsModal = true"
                class="text-cyan-300 underline hover:text-white"
              >
                Terms & Conditions
              </button>
            </label>
          </div>

          <!-- RECAPTCHA -->
          <div class="mb-5">
            <div
              ref="recaptchaEl"
              class="flex justify-center"
            ></div>

            <p
              v-if="recaptchaError"
              class="text-xs text-red-300 mt-2 text-center"
            >
              {{ recaptchaError }}
            </p>
          </div>

<div class="mb-5">
  <a
    :href="route('google.login', { role: 'Beneficiary' })"
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
            :disabled="form.processing || !agreedToTerms || processing"
            class="w-full bg-gradient-to-r from-blue-600 to-cyan-500 hover:scale-[1.02] hover:shadow-2xl transition-all duration-300 text-white py-3 rounded-xl font-bold tracking-wide disabled:opacity-50"
          >
            <span v-if="processing">
              Registering...
            </span>

            <span v-else>
              Register
            </span>
          </button>
          

          <!-- LOGIN -->
          <div class="text-center mt-6 text-sm text-gray-200">
            Already have an account?

            <Link
              :href="route('login')"
              class="text-cyan-300 hover:text-white hover:underline font-semibold"
            >
              Login
            </Link>
          </div>

        </form>
      </div>
    </div>

    <!-- TERMS MODAL -->
    <transition name="fade">
      <div
        v-if="showTermsModal"
        class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      >
        <div
          class="bg-white rounded-3xl p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl animate-scale"
        >
          <h2 class="text-3xl font-bold text-center text-blue-700 mb-2">
            Terms & Conditions
          </h2>

          <p class="text-center text-gray-500 mb-6">
            Special Program for the Employment of Students (SPES)
          </p>

          <div class="space-y-4 text-gray-700 text-sm leading-relaxed">

            <p>
              By registering for the SPES program, the applicant agrees
              to comply with all policies and guidelines established by
              the Department of Labor and Employment (DOLE).
            </p>

            <h3 class="font-semibold text-blue-700">
              1. Eligibility
            </h3>

            <ul class="list-disc pl-5 space-y-1">
              <li>Must be a Filipino citizen</li>
              <li>Must be 15–30 years old</li>
              <li>Must be enrolled or qualified under SPES guidelines</li>
              <li>Must provide accurate information</li>
            </ul>

            <h3 class="font-semibold text-blue-700">
              2. Employment Rules
            </h3>

            <ul class="list-disc pl-5 space-y-1">
              <li>Temporary employment only</li>
              <li>Work duration follows DOLE regulations</li>
              <li>Applicants must comply with workplace policies</li>
            </ul>

            <h3 class="font-semibold text-blue-700">
              3. Data Privacy
            </h3>

            <ul class="list-disc pl-5 space-y-1">
              <li>
                Personal information shall only be used for SPES processing.
              </li>

              <li>
                Data will remain confidential and protected.
              </li>
            </ul>

          </div>

          <div class="mt-8 text-center">
            <button
              @click="showTermsModal = false"
              class="px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-500 hover:opacity-90 text-white rounded-xl transition"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import {
  ref,
  computed,
  nextTick,
} from 'vue'

import {
  useForm,
  Link,
} from '@inertiajs/vue3'

import { route } from 'ziggy-js'

const showTypeModal = ref(true)
const showTermsModal = ref(false)

const agreedToTerms = ref(false)

const selectedType = ref('')

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const recaptchaEl = ref(null)
const recaptchaWidgetId = ref(null)
  
const recaptchaError = ref(null)

const processing = ref(false)

const passwordError = ref('')

const siteKey = import.meta.env.VITE_RECAPTCHA_SITE_KEY

const form = useForm({
  email: '',
  password: '',
  password_confirmation: '',
  type: '',
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

async function selectType(type) {
  selectedType.value = type
  form.type = type

  showTypeModal.value = false

  await nextTick()

  if (recaptchaEl.value && window.grecaptcha) {
    recaptchaWidgetId.value = window.grecaptcha.render(
      recaptchaEl.value,
      {
        sitekey: siteKey,

        callback: (token) => {
          form.recaptcha = token
          recaptchaError.value = null
        },
      }
    )
  }
}

const typeLabel = computed(() => {
  if (selectedType.value === 'student') {
    return 'Student'
  }

  if (selectedType.value === 'osy') {
    return 'Out-of-School Youth (OSY)'
  }

  if (selectedType.value === 'dependent') {
    return 'Dependent of Displaced Workers'
  }

  return ''
})

function submit() {

  passwordError.value = ''
  recaptchaError.value = ''

  const password = form.password?.trim()
  const confirm = form.password_confirmation?.trim()

  // PASSWORD CHECK
  if (!password) {
    passwordError.value = 'Password is required.'
    return
  }

  // STRONG PASSWORD
  if (!validatePassword(password)) {
    passwordError.value =
      'Password must contain uppercase, lowercase, number, special character, and 8+ characters.'
    return
  }

  // MATCH CHECK
  if (password !== confirm) {
    passwordError.value = 'Password does not match.'
    return
  }

  // TERMS
  if (!agreedToTerms.value) {
    alert('You must agree to the Terms & Conditions.')
    return
  }

  // RECAPTCHA
  if (!form.recaptcha) {
    recaptchaError.value =
      'Please verify that you are not a robot.'
    return
  }


  processing.value = true

  form.post(route('register.beneficiary.store'), {
    preserveScroll: true,

    onSuccess: () => {
      form.reset()
      agreedToTerms.value = false
      window.grecaptcha?.reset(recaptchaWidgetId.value)
    },

    onError: () => {
      window.grecaptcha?.reset(recaptchaWidgetId.value)

      form.recaptcha = null
    },

    onFinish: () => {
      processing.value = false
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

.input {
  width: 100%;
  padding: 14px 16px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  outline: none;
  transition: 0.3s ease;
  backdrop-filter: blur(10px);
}

.input::placeholder {
  color: rgba(255, 255, 255, 0.65);
}

.input:focus {
  border-color: rgba(59, 130, 246, 0.9);
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
}

.label {
  display: block;
  color: white;
  font-size: 14px;
  margin-bottom: 8px;
  font-weight: 600;
}

.error {
  color: #fca5a5;
  font-size: 12px;
  margin-top: 6px;
}

.eye {
  position: absolute;
  right: 14px;
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

.type-btn {
  width: 100%;
  padding: 16px;
  border-radius: 20px;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.15);
  color: white;
  transition: 0.3s ease;
  display: flex;
  align-items: center;
  gap: 14px;
}

.type-btn:hover {
  background: linear-gradient(
    135deg,
    rgba(37,99,235,0.9),
    rgba(6,182,212,0.9)
  );

  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(0,0,0,0.3);
}
</style>