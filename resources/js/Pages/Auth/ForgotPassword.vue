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

    <!-- TOAST -->
    <div
      v-if="toast.show"
      class="fixed top-5 right-5 z-50 px-5 py-3 rounded-xl shadow-lg text-white transition-all duration-300"
      :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
    >
      {{ toast.message }}
    </div>

    <!-- RESET CARD -->
    <div class="relative z-10 min-h-screen flex items-center justify-center p-4">
      <div
        class="w-full max-w-md backdrop-blur-xl bg-white/10 border border-white/20 rounded-3xl shadow-2xl p-8"
      >

        <!-- HEADER -->
        <div class="text-center mb-8">
          <h1 class="text-4xl font-extrabold text-white mb-2">
            Reset Password
          </h1>

          <p class="text-gray-200">
            Reset your password securely using OTP verification
          </p>
        </div>

        <!-- STEP 1 -->
        <form v-if="step === 1" @submit.prevent="sendOtp">

          <div class="mb-5">
            <label class="block text-sm text-gray-200 mb-2">
              Email Address
            </label>

            <input
              v-model="form.email"
              type="email"
              required
              placeholder="Enter your email"
              class="input"
            />

            <p v-if="form.errors.email" class="error">
              {{ form.errors.email }}
            </p>
          </div>

          <button
            type="submit"
            :disabled="form.processing || otpSending"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition duration-300 shadow-lg hover:scale-[1.02] disabled:opacity-60"
          >
            <span v-if="otpSending">Sending OTP...</span>
            <span v-else>Send OTP</span>
          </button>

        </form>

        <!-- STEP 2 -->
        <form v-if="step === 2" @submit.prevent="resetPassword">

          <!-- OTP -->
          <div class="mb-5">
            <label class="block text-sm text-gray-200 mb-2">
              OTP Code
            </label>

            <input
              v-model="form.otp"
              type="text"
              required
              placeholder="Enter OTP"
              class="input"
            />

            <p v-if="form.errors.otp" class="error">
              {{ form.errors.otp }}
            </p>
          </div>

          <!-- PASSWORD -->
          <div class="mb-5 relative">
            <label class="block text-sm text-gray-200 mb-2">
              New Password
            </label>

            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              placeholder="Enter new password"
              class="input pr-12"
            />

            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-4 top-11 text-gray-300 hover:text-white"
            >
              👁️
            </button>

            <p v-if="form.errors.password" class="error">
              {{ form.errors.password }}
            </p>

            <!-- PASSWORD VALIDATION -->
            <div class="mt-3 space-y-1 text-xs">

              <p :class="passwordRules.minLength ? 'text-green-400' : 'text-red-300'">
                ✓ At least 8 characters
              </p>

              <p :class="passwordRules.hasUppercase ? 'text-green-400' : 'text-red-300'">
                ✓ At least 1 uppercase letter
              </p>

              <p :class="passwordRules.hasLowercase ? 'text-green-400' : 'text-red-300'">
                ✓ At least 1 lowercase letter
              </p>

              <p :class="passwordRules.hasNumber ? 'text-green-400' : 'text-red-300'">
                ✓ At least 1 number
              </p>

              <p :class="passwordRules.hasSpecial ? 'text-green-400' : 'text-red-300'">
                ✓ At least 1 special character
              </p>

            </div>
          </div>

          <!-- CONFIRM PASSWORD -->
          <div class="mb-5 relative">
            <label class="block text-sm text-gray-200 mb-2">
              Confirm Password
            </label>

            <input
              v-model="form.password_confirmation"
              :type="showPasswordConfirmation ? 'text' : 'password'"
              required
              placeholder="Confirm password"
              class="input pr-12"
            />

            <button
              type="button"
              @click="showPasswordConfirmation = !showPasswordConfirmation"
              class="absolute right-4 top-11 text-gray-300 hover:text-white"
            >
              👁️
            </button>

            <p v-if="form.errors.password_confirmation" class="error">
              {{ form.errors.password_confirmation }}
            </p>
          </div>

          <!-- RESEND -->
          <div class="flex justify-between items-center mb-5">
            <button
              type="button"
              @click="resendOtp"
              :disabled="timer > 0 || form.processing"
              class="text-sm text-blue-300 hover:text-white hover:underline disabled:text-gray-400"
            >
              Resend OTP {{ timer > 0 ? `(${timer}s)` : '' }}
            </button>
          </div>

          <!-- SUBMIT -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl transition duration-300 shadow-lg hover:scale-[1.02] disabled:opacity-60"
          >
            <span v-if="form.processing">Resetting...</span>
            <span v-else>Reset Password</span>
          </button>

        </form>

        <!-- BACK -->
        <div class="text-center mt-6 text-sm text-gray-200">
          Back to

          <Link
            href="/login"
            class="text-blue-300 hover:text-white hover:underline font-semibold"
          >
            Login
          </Link>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

const step = ref(1)
const timer = ref(0)
const otpSending = ref(false)

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

/* TOAST */
const toast = ref({
  show: false,
  message: '',
  type: 'success',
})

function showToast(message, type = 'success') {
  toast.value.message = message
  toast.value.type = type
  toast.value.show = true

  setTimeout(() => {
    toast.value.show = false
  }, 3000)
}

/* FORM */
const form = useForm({
  email: '',
  otp: '',
  password: '',
  password_confirmation: '',
})

/* PASSWORD VALIDATION */
const passwordRules = computed(() => ({
  minLength: form.password.length >= 8,
  hasUppercase: /[A-Z]/.test(form.password),
  hasLowercase: /[a-z]/.test(form.password),
  hasNumber: /[0-9]/.test(form.password),
  hasSpecial: /[^A-Za-z0-9]/.test(form.password),
}))

const isPasswordValid = computed(() => {
  return Object.values(passwordRules.value).every(Boolean)
})

/* TIMER */
let timerInterval = null

function startTimer() {
  timer.value = 60

  timerInterval = setInterval(() => {
    if (timer.value > 0) {
      timer.value--
    } else {
      clearInterval(timerInterval)
    }
  }, 1000)
}

/* SEND OTP */
function sendOtp() {
  otpSending.value = true

  form.post('/forgot-password-otp', {
    onSuccess: () => {
      step.value = 2
      showToast('OTP sent successfully!', 'success')
      startTimer()
    },

    onError: () => {
      showToast('Failed to send OTP', 'error')
    },

    onFinish: () => {
      otpSending.value = false
    },
  })
}

/* RESEND OTP */
function resendOtp() {
  if (timer.value === 0) {
    sendOtp()
  }
}

/* RESET PASSWORD */
function resetPassword() {

  if (!isPasswordValid.value) {
    showToast('Password does not meet requirements', 'error')
    return
  }

  if (form.password !== form.password_confirmation) {
    showToast('Passwords do not match', 'error')
    return
  }

  form.post('/reset-password-otp', {
    onSuccess: () => {
      showToast('Password reset successfully!', 'success')

      step.value = 1

      form.reset(
        'otp',
        'password',
        'password_confirmation'
      )

      timer.value = 0
    },

    onError: () => {
      showToast('Invalid OTP or password', 'error')
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