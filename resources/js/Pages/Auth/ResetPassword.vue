<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
    <div class="w-full max-w-md bg-white rounded shadow p-6">
      <h1 class="text-2xl font-semibold mb-4">Reset Password</h1>

      <form @submit.prevent="submit">
        <input type="hidden" v-model="form.token" />

        <div class="mb-4">
          <label>Email</label>
          <input v-model="form.email" type="email" class="w-full border rounded px-3 py-2" />
        </div>

        <div class="mb-4">
          <label>Password</label>
          <input v-model="form.password" type="password" class="w-full border rounded px-3 py-2" />
        </div>

        <div class="mb-4">
          <label>Confirm Password</label>
          <input v-model="form.password_confirmation" type="password" class="w-full border rounded px-3 py-2" />
        </div>

        <button class="w-full bg-blue-600 text-white py-2 rounded">
          Reset Password
        </button>
      </form>

      <p v-if="networkError" class="text-sm text-red-600 mt-4">
        {{ networkError }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  token: String,
  email: String,
})

import { ref, onMounted, onBeforeUnmount } from 'vue'
const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

const networkError = ref('')

function handleNetworkEvent(e) {
  networkError.value = e?.detail?.message || 'Network error. Could not reach backend.'
}

onMounted(() => window.addEventListener('network-error', handleNetworkEvent))
onBeforeUnmount(() => window.removeEventListener('network-error', handleNetworkEvent))

async function submit() {
  networkError.value = ''
  try {
    await form.post(route('password.update'))
  } catch (err) {
    networkError.value = 'Could not reach the server. Please make sure your backend is running and try again.'
    console.error('Network error resetting password', err)
  }
}
</script>
