<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ job: Object });

const form = useForm({
  title: props.job.title,
  description: props.job.description,
  location: props.job.location,
  type: props.job.type,
  slots: props.job.slots,
  closing_date: props.job.closing_date,
});

function submit() {
  form.put(`/employer/jobs/${props.job.id}`);
}
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Edit Job</h1>

    <form @submit.prevent="submit" class="space-y-3">
      <input v-model="form.title" class="input">
      <textarea v-model="form.description" class="input"></textarea>
      <input v-model="form.location" class="input">
      <select v-model="form.type" class="input">
        <option value="Full-time">Full-time</option>
        <option value="Part-time">Part-time</option>
      </select>
      <input v-model="form.slots" type="number" class="input">
      <input v-model="form.closing_date" type="date" class="input">

      <button class="btn-primary">Update</button>
    </form>
  </div>
</template>
