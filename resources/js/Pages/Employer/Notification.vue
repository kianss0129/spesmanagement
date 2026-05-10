<template>
  <div class="min-h-screen bg-gray-100 py-10">
    <div class="max-w-4xl mx-auto px-4">

      <!-- Back Button -->
      <button
        @click="goBack"
        class="mb-6 flex items-center space-x-2 text-gray-700 hover:text-gray-900"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 19l-7-7 7-7"/>
        </svg>
        <span>Back</span>
      </button>

      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
          Notifications & Announcements
        </h1>
        <p class="text-gray-500 text-sm mt-1">
          Stay updated with the latest system announcements.
        </p>
      </div>

      <!-- Empty State -->
      <div
        v-if="announcements.length === 0"
        class="bg-white rounded-lg shadow p-8 text-center"
      >
        <div class="text-gray-400 text-5xl mb-3">🔔</div>
        <p class="text-gray-500 text-lg font-medium">
          No notifications yet
        </p>
        <p class="text-sm text-gray-400 mt-1">
          Announcements will appear here once available.
        </p>
      </div>

      <!-- Foldable Announcement Cards -->
      <div v-else class="space-y-4">
        <div
          v-for="n in announcements"
          :key="n.id"
          class="bg-white rounded-xl shadow hover:shadow-lg transition duration-300"
        >
          <!-- Header (clickable) -->
          <div
            class="flex justify-between items-center p-4 cursor-pointer"
            @click="toggleFold(n.id)"
          >
            <h2 class="text-lg font-semibold text-gray-800">
              {{ n.title }}
            </h2>
            <div class="flex items-center space-x-2">
              <span class="text-xs text-gray-400">
                {{ formatDate(n.created_at) }}
              </span>
              <svg
                :class="{'rotate-180': isOpen(n.id)}"
                class="w-4 h-4 text-gray-500 transform transition-transform duration-200"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>
          </div>

          <!-- Content (collapsible) -->
          <transition name="fade">
            <div v-show="isOpen(n.id)" class="p-4 pt-0 border-t text-gray-600">
              <p class="leading-relaxed">{{ n.content }}</p>

              <!-- Clickable Image -->
              <div v-if="n.image" class="mt-4">
                <img
                  :src="`/storage/${n.image}`"
                  alt="Announcement Image"
                  class="rounded-lg w-full max-h-80 object-cover border cursor-pointer hover:opacity-90 transition"
                  @click="openModal(`/storage/${n.image}`)"
                />
              </div>
            </div>
          </transition>
        </div>
      </div>

      <!-- Modal -->
      <div
        v-if="modalImage"
        class="fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50"
      >
        <div class="relative">
          <button
            @click="closeModal"
            class="absolute top-2 right-2 text-white bg-gray-800 rounded-full p-2 hover:bg-gray-700"
          >
            ✕
          </button>
          <img :src="modalImage" class="max-h-[80vh] max-w-[90vw] rounded-lg shadow-lg"/>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  announcements: {
    type: Array,
    default: () => []
  }
})

// Track open announcements
const openIds = ref([])

// Modal image
const modalImage = ref(null)

function toggleFold(id) {
  if (openIds.value.includes(id)) {
    openIds.value = openIds.value.filter(i => i !== id)
  } else {
    openIds.value.push(id)
  }
}

function isOpen(id) {
  return openIds.value.includes(id)
}

// Modal functions
function openModal(imageUrl) {
  modalImage.value = imageUrl
}

function closeModal() {
  modalImage.value = null
}

// Back button
function goBack() {
  history.back()
}

function formatDate(date) {
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<style>
.fade-enter-active, .fade-leave-active {
  transition: all 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  max-height: 0;
}
.fade-enter-to, .fade-leave-from {
  opacity: 1;
  max-height: 1000px;
}
</style>