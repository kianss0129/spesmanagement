<template>
  <div class="bg-white max-w-md p-8 rounded-2xl shadow-lg">

    <h1 class="text-2xl font-bold text-blue-700 mb-6">
      Change Password
    </h1>

    <!-- SUCCESS -->
    <div v-if="success" class="mb-4 bg-green-100 text-green-700 p-3 rounded">
      {{ success }}
    </div>

    <!-- ERROR -->
    <div v-if="error" class="mb-4 bg-red-100 text-red-700 p-3 rounded">
      {{ error }}
    </div>

    <form @submit.prevent="changePassword">

      <div class="mb-4">
        <label class="text-sm">Current Password</label>
        <input
          type="password"
          v-model="form.current_password"
          class="w-full border rounded-lg p-2 mt-1"
          required
        />
      </div>

      <div class="mb-4">
        <label class="text-sm">New Password</label>
        <input
          type="password"
          v-model="form.password"
          class="w-full border rounded-lg p-2 mt-1"
          required
        />
        <!-- Password validation messages -->
        <ul class="text-xs mt-1 text-red-600">
          <li v-if="form.password && !validations.length">❌ At least 8 characters</li>
          <li v-if="form.password && !validations.upper">❌ At least 1 uppercase letter</li>
          <li v-if="form.password && !validations.lower">❌ At least 1 lowercase letter</li>
          <li v-if="form.password && !validations.special">❌ At least 1 special character</li>
        </ul>
      </div>

      <div class="mb-6">
        <label class="text-sm">Confirm Password</label>
        <input
          type="password"
          v-model="form.password_confirmation"
          class="w-full border rounded-lg p-2 mt-1"
          required
        />
        <p v-if="form.password_confirmation && form.password !== form.password_confirmation"
           class="text-xs text-red-600 mt-1">
          ❌ Passwords do not match
        </p>
      </div>

      <button
        :disabled="loading || !isPasswordValid"
        class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50"
      >
        {{ loading ? 'Updating...' : 'Change Password' }}
      </button>

    </form>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'

const loading = ref(false)
const success = ref('')
const error = ref('')

const form = ref({
  current_password: '',
  password: '',
  password_confirmation: ''
})

// Password validations
const validations = computed(() => {
  const pwd = form.value.password
  return {
    length: pwd.length >= 8,
    upper: /[A-Z]/.test(pwd),
    lower: /[a-z]/.test(pwd),
    special: /[!@#$%^&*(),.?":{}|<>]/.test(pwd)
  }
})

// Check if all password requirements are met
const isPasswordValid = computed(() => {
  return Object.values(validations.value).every(v => v)
})

async function changePassword() {
  if (!isPasswordValid.value) {
    error.value = 'Password does not meet the requirements.'
    return
  }

  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match.'
    return
  }

  loading.value = true
  success.value = ''
  error.value = ''

  try {
    const res = await axios.post('/beneficiary/change-password', form.value)
    success.value = res.data.message

    form.value = {
      current_password: '',
      password: '',
      password_confirmation: ''
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Something went wrong.'
  }

  loading.value = false
}
</script>