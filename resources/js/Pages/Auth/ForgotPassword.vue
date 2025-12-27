<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
    <div class="w-full max-w-md bg-white rounded shadow p-6">
      <h1 class="text-2xl font-semibold mb-4">Forgot Password</h1>

      <form @submit.prevent="submit">
        <div class="mb-4">
          <label class="block text-sm mb-1">Email</label>
          <input v-model="form.email" type="email" class="w-full border rounded px-3 py-2" />
          <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">
            {{ form.errors.email }}
          </p>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-blue-600 text-white py-2 rounded"
        >
          <span v-if="form.processing">Sending…</span>
          <span v-else>Send Reset Link</span>
        </button>
      </form>

      <p v-if="status" class="text-sm text-green-600 mt-4">
        {{ status }}
      </p>

      <p v-if="successMessage && !status" class="text-sm text-green-600 mt-4">
        {{ successMessage }}
      </p>

      <p v-if="networkError" class="text-sm text-red-600 mt-4">
        {{ networkError }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/inertia-vue3'
import { ref, onMounted, onBeforeUnmount } from 'vue'

const page = usePage()
const status = page.props.value.flash?.status

const form = useForm({
  email: '',
})

const networkError = ref('')
const successMessage = ref('')

function handleNetworkEvent(e) {
  networkError.value = e?.detail?.message || 'Network error. Could not reach backend.'
}

onMounted(() => window.addEventListener('network-error', handleNetworkEvent))
onBeforeUnmount(() => window.removeEventListener('network-error', handleNetworkEvent))

async function submit() {
  networkError.value = ''
  successMessage.value = ''
  try {
    await form.post(route('password.email'), {
      onSuccess: () => {
        successMessage.value = 'If that email exists in our system, a password reset link has been sent.'
      },
    })
  } catch (err) {
    // Axios / network errors bubble here; show friendly message
    networkError.value = 'Could not reach the server. Please make sure your backend is running (php artisan serve or Apache) and try again.'
    console.error('Network error sending reset link', err)
  }
}
</script>
