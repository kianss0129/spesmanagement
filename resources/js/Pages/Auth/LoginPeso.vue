<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow p-6">
      <h1 class="text-2xl font-semibold mb-1">PESO Sign in</h1>
      <p class="text-sm text-gray-600 mb-4">
        Authorized PESO personnel only
      </p>

      <form @submit.prevent="submit">
        <div class="mb-4">
          <label class="block text-sm text-gray-600 mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full border rounded px-3 py-2"
          />
          <p v-if="errors.email" class="text-xs text-red-500 mt-1">
            {{ errors.email }}
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
          <p v-if="errors.password" class="text-xs text-red-500 mt-1">
            {{ errors.password }}
          </p>
        </div>

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
          :disabled="processing"
          class="w-full bg-green-600 text-white py-2 rounded"
        >
          <span v-if="processing">Signing in...</span>
          <span v-else>Sign in</span>
        </button>
      </form>

      <div class="text-sm text-center mt-4">
        Don’t have an account?
        <Link href="/register/peso" class="text-blue-600 hover:underline">
          Register as PESO
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useForm, Link } from '@inertiajs/inertia-vue3'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const errors = reactive({})
const processing = ref(false)

function submit() {
  processing.value = true
  form.post('/login', {
    onSuccess: () => { processing.value = false },
    onError: () => {
      processing.value = false
      Object.assign(errors, form.errors)
    },
    onFinish: () => { processing.value = false }
  })
}
</script>
