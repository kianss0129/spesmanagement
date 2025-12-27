<script setup>
import { useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
defineProps(['patients', 'doctors']);

const form = useForm({
  patient_id: '',
  doctor_id: '',
  appointment_date: '',
});
</script>

<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Booking removed</h1>
    <p class="text-sm text-gray-600">Appointment booking was removed as part of SPES migration.</p>
  </div>
</template>

<script setup>
// stubbed
</script>
