<template>
  <div class="flex items-center">
    <span v-for="i in 5" :key="i" class="text-yellow-400">
      <i :class="i <= value ? 'fas fa-star' : 'far fa-star'"></i>
    </span>
    <span class="ml-2 text-gray-600">{{ value }}/5</span>
  </div>
</template>

<script setup>
const props = defineProps({ value: { type: Number, required: true } });
</script>
