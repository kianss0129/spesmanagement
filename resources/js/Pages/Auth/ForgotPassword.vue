<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticationCard from '@/Components/AuthenticationCard.vue'
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'

defineProps({ status: String })

const statusMessage = ref('')

const form = useForm({ email: '' })

const submit = () => {
  form.post(route('password.email'), {
    onSuccess: () => {
      statusMessage.value = 'Reset link sent to your email!'
    },
  })
}
</script>

<template>
  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <Head title="Forgot Password" />

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <InputLabel for="email" value="Email" />
        <TextInput
          id="email"
          v-model="form.email"
          type="email"
          class="mt-1 block w-full"
          required
          autofocus
        />
        <InputError :message="form.errors.email" class="mt-2" />
      </div>

      <PrimaryButton class="mt-4" :disabled="form.processing">
        Email Password Reset Link
      </PrimaryButton>

      <div v-if="status || statusMessage" class="mt-4 text-green-600 text-sm">
        {{ status || statusMessage }}
      </div>
    </form>
  </AuthenticationCard>
</template>
