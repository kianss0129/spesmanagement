<script setup>
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const form = useForm({
  company_name: '',
  email: '',
  contact_person: '',
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

    <!-- Email -->
    <div>
      <label for="email">Company Email</label>
      <input
        v-model="form.email"
        name="email"
        id="email"
        type="email"
        autocomplete="email"
        class="input"
      />
      <p v-if="form.errors.email" class="error">{{ form.errors.email }}</p>
    </div>

    <!-- Contact Person -->
    <div>
      <label for="contact_person">Contact Person</label>
      <input
        v-model="form.contact_person"
        name="contact_person"
        id="contact_person"
        type="text"
        autocomplete="name"
        class="input"
      />
      <p v-if="form.errors.contact_person" class="error">{{ form.errors.contact_person }}</p>
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
