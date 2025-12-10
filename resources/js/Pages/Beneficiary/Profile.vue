<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">{{ beneficiary.first_name }} {{ beneficiary.last_name }}</h1>

    <div class="mb-4">
      <h2 class="font-semibold">Average Rating: {{ average }} / 5</h2>
    </div>

    <div v-for="rating in ratings" :key="rating.id" class="border p-4 rounded mb-2">
      <p><strong>Employer:</strong> {{ rating.employer.name }}</p>
      <p><strong>Application:</strong> {{ rating.application.job_listing.title }}</p>
      <p><strong>Punctuality:</strong> {{ rating.punctuality }}</p>
      <p><strong>Work Attitude:</strong> {{ rating.work_attitude }}</p>
      <p><strong>Output Quality:</strong> {{ rating.output_quality }}</p>
      <p><strong>Communication:</strong> {{ rating.communication }}</p>
      <p><strong>Overall:</strong> {{ rating.overall }}</p>
      <p><strong>Comment:</strong> {{ rating.comment }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const ratings = ref([]);
const average = ref(0);
const beneficiary = ref({ id: 1, first_name: '', last_name: '' }); // replace with real data

onMounted(async () => {
  const res = await axios.get(`/api/beneficiaries/${beneficiary.value.id}/ratings`);
  ratings.value = res.data.ratings;
  average.value = res.data.average;
});
</script>
