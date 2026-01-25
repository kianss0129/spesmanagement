<template>
  <div @click="go" class="bg-white rounded-lg shadow p-4 cursor-pointer hover:shadow-md transition">
    <h3 class="font-medium text-gray-700">{{ title }}</h3>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'
import { Inertia } from '@inertiajs/inertia'

defineProps({
  title: String,
  href: {
    type: String,
    default: null
  }
})

function go() {
  if (href) {
    Inertia.visit(href)
  }
}
</script>
