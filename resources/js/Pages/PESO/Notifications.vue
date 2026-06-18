<template>
  <div
    class="min-h-screen bg-cover bg-center bg-no-repeat relative overflow-hidden"
    style="background-image: url('/images/spes-notiff.jpg');"
  >
    <!-- DARK OVERLAY -->
    <div class="absolute inset-0 bg-black/70"></div>

    <!-- GLOW EFFECTS -->
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute w-80 h-80 bg-blue-500/20 blur-3xl rounded-full -top-20 -left-20 animate-pulse"></div>
      <div class="absolute w-80 h-80 bg-cyan-400/20 blur-3xl rounded-full bottom-0 right-0 animate-pulse"></div>
    </div>

    <div class="relative z-10 max-w-5xl mx-auto px-4 py-10">

      <!-- BACK BUTTON -->
      <button
        @click="goBack"
        class="mb-6 flex items-center gap-2 text-white/80 hover:text-white transition"
      >
        <svg
          class="w-5 h-5"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 19l-7-7 7-7"
          />
        </svg>

        <span class="font-medium">
          Back
        </span>
      </button>

      <!-- HEADER -->
      <div class="mb-10">
        <h1 class="text-4xl font-extrabold text-white mb-2">
          Notifications & Announcements
        </h1>

        <p class="text-gray-300">
          Stay updated with the latest announcements and system updates.
        </p>
      </div>

      <!-- EMPTY STATE -->
      <div
        v-if="announcements.length === 0"
        class="bg-white/10 backdrop-blur-xl border border-white/20
               rounded-3xl shadow-2xl p-10 text-center"
      >
        <div class="text-6xl mb-4 animate-bounce">
          🔔
        </div>

        <h2 class="text-2xl font-bold text-white mb-2">
          No Notifications Yet
        </h2>

        <p class="text-gray-300">
          Announcements will appear here once available.
        </p>
      </div>

      <!-- ANNOUNCEMENTS -->
      <div v-else class="space-y-5">

        <div
          v-for="n in announcements"
          :key="n.id"
          class="bg-white/10 backdrop-blur-xl border border-white/20
                 rounded-3xl shadow-xl overflow-hidden transition
                 hover:scale-[1.01] hover:shadow-2xl"
        >

          <!-- CARD HEADER -->
          <div
            class="flex justify-between items-center p-5 cursor-pointer"
            @click="toggleFold(n.id)"
          >

            <div>
              <h2 class="text-xl font-bold text-white">
                {{ n.title }}
              </h2>

              <p class="text-xs text-gray-300 mt-1">
                {{ formatDate(n.created_at) }}
              </p>
            </div>

            <!-- TOGGLE ICON -->
            <div
              class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center"
            >
              <svg
                :class="{ 'rotate-180': isOpen(n.id) }"
                class="w-5 h-5 text-white transform transition duration-300"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </div>

          </div>

          <!-- COLLAPSIBLE CONTENT -->
          <transition name="fade">
            <div
              v-show="isOpen(n.id)"
              class="px-5 pb-5 border-t border-white/10"
            >

              <p class="text-gray-200 leading-relaxed mt-4 whitespace-pre-line">
                {{ n.content }}
              </p>

              <!-- IMAGE -->
              <div v-if="n.image" class="mt-5">
                <img
                  :src="`/storage/${n.image}`"
                  alt="Announcement Image"
                  class="rounded-2xl w-full max-h-[450px] object-cover
                         border border-white/10 shadow-lg
                         cursor-pointer hover:opacity-90 transition"
                  @click="openModal(`/storage/${n.image}`)"
                />
              </div>

            </div>
          </transition>

        </div>

      </div>

      <!-- IMAGE MODAL -->
      <transition name="fade">
        <div
          v-if="modalImage"
          class="fixed inset-0 bg-black/80 backdrop-blur-sm
                 flex items-center justify-center z-50 p-4"
        >

          <!-- CLOSE BUTTON -->
          <button
            @click="closeModal"
            class="absolute top-5 right-5 w-10 h-10 rounded-full
                   bg-white/10 hover:bg-white/20 text-white text-xl
                   flex items-center justify-center transition"
          >
            ✕
          </button>

          <!-- MODAL IMAGE -->
          <img
            :src="modalImage"
            class="max-h-[90vh] max-w-[95vw]
                   rounded-3xl shadow-2xl border border-white/10"
          />

        </div>
      </transition>

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

// OPEN ANNOUNCEMENT IDS
const openIds = ref([])

// MODAL IMAGE
const modalImage = ref(null)

// TOGGLE FOLD
function toggleFold(id) {
  if (openIds.value.includes(id)) {
    openIds.value = openIds.value.filter(i => i !== id)
  } else {
    openIds.value.push(id)
  }
}

// CHECK OPEN STATE
function isOpen(id) {
  return openIds.value.includes(id)
}

// OPEN MODAL
function openModal(imageUrl) {
  modalImage.value = imageUrl
}

// CLOSE MODAL
function closeModal() {
  modalImage.value = null
}

// BACK BUTTON
function goBack() {
  history.back()
}

// FORMAT DATE
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

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.25s ease;
  overflow: hidden;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  max-height: 0;
  transform: translateY(-5px);
}

.fade-enter-to,
.fade-leave-from {
  opacity: 1;
  max-height: 1000px;
  transform: translateY(0);
}
</style>
