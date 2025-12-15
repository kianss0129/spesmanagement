<script setup>
import { useForm } from '@inertiajs/inertia-vue3'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

function submit() {
  form.post(route('register.beneficiary'))
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md bg-white p-6 rounded shadow">
      <h1 class="text-xl font-bold mb-4 text-center">Beneficiary Registration</h1>

      <form @submit.prevent="submit">
        <input v-model="form.name" type="text" placeholder="Full Name" class="input" />
        <div v-if="form.errors.name" class="error">{{ form.errors.name }}</div>

        <input v-model="form.email" type="email" placeholder="Email" class="input" />
        <div v-if="form.errors.email" class="error">{{ form.errors.email }}</div>

        <input v-model="form.password" type="password" placeholder="Password" class="input" />
        <div v-if="form.errors.password" class="error">{{ form.errors.password }}</div>

        <input v-model="form.password_confirmation" type="password" placeholder="Confirm Password" class="input" />

        <button class="btn-primary w-full mt-4">Register</button>

        <p class="text-sm text-center mt-3">
          Already have an account?
          <inertia-link :href="route('login')" class="text-blue-600 underline">Login</inertia-link>
        </p>
      </form>
    </div>
  </div>
</template>

<style scoped>
.input { width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; margin-top:10px; }
.btn-primary { background:#2563eb; color:white; padding:10px; border-radius:6px; }
.error { color:red; font-size:12px; }
</style>
