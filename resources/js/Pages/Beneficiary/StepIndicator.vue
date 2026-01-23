

<template>
  <div class="w-full max-w-4xl mx-auto my-8">
    <!-- Steps Circles -->
    <div class="flex justify-between items-center mb-6">
      <div
        v-for="(step, index) in currentSteps"
        :key="index"
        class="flex-1 flex flex-col items-center text-center"
      >
        <!-- Circle -->
        <div
          :class="[
            'w-10 h-10 rounded-full flex items-center justify-center mb-2 transition-colors duration-300',
            activeStep >= index + 1
              ? 'bg-indigo-600 text-white'
              : 'bg-gray-200 text-gray-500'
          ]"
        >
          {{ index + 1 }}
        </div>
        <!-- Label -->
        <div class="text-sm font-medium">{{ step }}</div>
      </div>
    </div>

    <!-- Progress Bar -->
    <div class="relative h-2 bg-gray-200 rounded-full">
      <div
        class="absolute h-2 bg-indigo-600 rounded-full transition-all duration-300"
        :style="{ width: progressWidth }"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

// Props
const props = defineProps({
  type: { type: String, required: true }, // student, osy, dependent, employer
  activeStep: { type: Number, default: 1 } // current step number
})

// Step labels for each type
const stepsByType = {
  student: ['Personal Info', 'School Info', 'Documents', 'Review', 'Submit'],
  osy: ['Personal Info', 'Skills/Training', 'Documents', 'Review', 'Submit'],
  dependent: ['Personal Info', 'Parent Info', 'Documents', 'Review', 'Submit'],
  employer: ['Company Info', 'Pledge Submission', 'Documents', 'Review', 'Submit']
}

// Computed current steps
const currentSteps = computed(() => stepsByType[props.type] || [])

// Compute progress width
const progressWidth = computed(() => {
  if (!currentSteps.value.length) return '0%'
  const percent = ((props.activeStep - 1) / (currentSteps.value.length - 1)) * 100
  return `${percent}%`
})
</script>
