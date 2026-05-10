<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-100 to-blue-200 p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">

      <h1 class="text-2xl font-bold text-blue-700 mb-4">
        Reset Password via OTP
      </h1>

      <!-- STEP 1: Enter Email -->
      <form v-if="step === 1" @submit.prevent="sendOtp">
        <div class="mb-5">
          <label class="block text-sm text-gray-600 mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-blue-500"
          />
          <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">
            {{ form.errors.email }}
          </p>
        </div>

        <button
          type="submit"
          :disabled="form.processing || otpSending"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg transition"
        >
          {{ otpSending ? `Sending OTP...` : 'Send OTP' }}
        </button>
      </form>

      <!-- STEP 2: Verify OTP + New Password -->
      <form v-if="step === 2" @submit.prevent="resetPassword">
        <div class="mb-4">
          <label class="block text-sm text-gray-600 mb-1">OTP Code</label>
          <input
            v-model="form.otp"
            type="text"
            required
            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-blue-500"
          />
          <p v-if="form.errors.otp" class="text-xs text-red-500 mt-1">{{ form.errors.otp }}</p>
        </div>

        <div class="mb-4">
          <label class="block text-sm text-gray-600 mb-1">New Password</label>
          <input
            v-model="form.password"
            type="password"
            required
            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-blue-500"
          />
          <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</p>
        </div>

        <div class="mb-4">
          <label class="block text-sm text-gray-600 mb-1">Confirm Password</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            required
            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-blue-500"
          />
          <p v-if="form.errors.password_confirmation" class="text-xs text-red-500 mt-1">{{ form.errors.password_confirmation }}</p>
        </div>

        <div class="mb-4 flex justify-between items-center">
          <button
            type="button"
            @click="resendOtp"
            :disabled="timer > 0 || form.processing"
            class="text-sm text-blue-600 hover:underline disabled:text-gray-400"
          >
            Resend OTP {{ timer > 0 ? `(${timer}s)` : '' }}
          </button>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg transition"
        >
          Reset Password
        </button>
      </form>

      <div class="text-sm text-center mt-6">
        <Link href="/login" class="text-blue-600 hover:underline">
          Back to Login
        </Link>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

const step = ref(1)
const timer = ref(0)
const otpSending = ref(false)

const form = useForm({
  email: '',
  otp: '',
  password: '',
  password_confirmation: '',
})

let timerInterval = null

function startTimer() {
  timer.value = 60
  timerInterval = setInterval(() => {
    if (timer.value > 0) {
      timer.value -= 1
    } else {
      clearInterval(timerInterval)
    }
  }, 1000)
}

// Send OTP to email
function sendOtp() {
  otpSending.value = true
  form.post('/forgot-password-otp', {
    onSuccess: () => {
      step.value = 2
      alert('OTP sent! Please check your email.')
      startTimer()
    },
    onFinish: () => {
      otpSending.value = false
    },
    onError: (errors) => {
      console.log('Errors sending OTP:', errors)
    }
  })
}

// Resend OTP (after timer expires)
function resendOtp() {
  if (timer.value === 0) {
    sendOtp()
  }
}

// Reset password with OTP
function resetPassword() {
  form.post('/reset-password-otp', {
    onSuccess: () => {
      alert('Password successfully reset! You can now login.')
      step.value = 1
      form.reset('otp', 'password', 'password_confirmation')
      timer.value = 0
    },
    onError: (errors) => {
      console.log('Errors resetting password:', errors)
    }
  })
}
</script>
