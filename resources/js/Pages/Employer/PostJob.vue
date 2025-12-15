<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  title: '',
  description: '',
  location: '',
  type: '',
  slots: 1,
  closing_date: '',
});

function submit() {
  form.post('/employer/jobs');
}
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Post a Job</h1>

    <form @submit.prevent="submit" class="space-y-3">
      <input v-model="form.title" class="input" placeholder="Job Title">
      <textarea v-model="form.description" class="input" placeholder="Description"></textarea>
      <input v-model="form.location" class="input" placeholder="Location">
      <select v-model="form.type" class="input">
        <option value="Full-time">Full-time</option>
        <option value="Part-time">Part-time</option>
      </select>
      <input v-model="form.slots" type="number" class="input" placeholder="Slots">
      <input v-model="form.closing_date" type="date" class="input">

      <button class="btn-primary" type="submit">Submit</button>
    </form>
  </div>
</template>
