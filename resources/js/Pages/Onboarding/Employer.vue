<script setup>
import { useForm } from '@inertiajs/inertia-vue3'
import { route } from 'ziggy-js'

const form = useForm({
  company_name: '',
  phone: '',
  address: ''
})

const submit = () => {
  form.post(route('onboarding.submit'))
}
</script>

<template>
  <div class="space-y-4 p-6">
    <h1 class="text-2xl font-bold">Employer Onboarding</h1>

    <!-- Company Name -->
    <div>
      <label for="company_name">Company Name</label>
      <input
        v-model="form.company_name"
        name="company_name"
        id="company_name"
        type="text"
        autocomplete="organization"
        class="input"
      />
      <p v-if="form.errors.company_name" class="error">{{ form.errors.company_name }}</p>
    </div>

    <!-- Phone -->
    <div>
      <label for="phone">Phone</label>
      <input
        v-model="form.phone"
        name="phone"
        id="phone"
        type="text"
        autocomplete="tel"
        class="input"
      />
      <p v-if="form.errors.phone" class="error">{{ form.errors.phone }}</p>
    </div>

    <!-- Address -->
    <div>
      <label for="address">Address</label>
      <input
        v-model="form.address"
        name="address"
        id="address"
        type="text"
        autocomplete="street-address"
        class="input"
      />
      <p v-if="form.errors.address" class="error">{{ form.errors.address }}</p>
    </div>

    <!-- Submit -->
    <button
      class="btn-primary"
      :disabled="form.processing"
      @click="submit"
    >
      Submit Onboarding
    </button>
  </div>
</template>

<style scoped>
.input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  margin-top: 0.25rem;
}
.error {
  color: red;
  font-size: 0.875rem;
}
.btn-primary {
  background: #059669;
  color: white;
  padding: 0.75rem 1rem;
  border-radius: 6px;
  cursor: pointer;
}
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
