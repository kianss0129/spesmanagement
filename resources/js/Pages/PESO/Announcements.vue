<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Manage Announcements</h1>

    <!-- SUCCESS FLASH MESSAGE -->
    <div v-if="flashSuccess" class="bg-green-100 text-green-800 p-3 rounded mb-4">
      {{ flashSuccess }}
    </div>

    <!-- CREATE FORM -->
    <div class="bg-white p-6 rounded-xl shadow mb-8">
      <h2 class="font-semibold mb-4">Create Announcement</h2>

      <input
        v-model="title"
        type="text"
        placeholder="Title"
        class="w-full border rounded p-2 mb-3"
      />

      <textarea
        v-model="message"
        placeholder="Message"
        class="w-full border rounded p-2 mb-3"
      ></textarea>

      <input type="file" @change="handleFile" class="mb-3" />

      <button
        @click="postAnnouncement"
        class="bg-blue-600 text-white px-4 py-2 rounded"
      >
        Post Announcement
      </button>
    </div>

    <!-- PREVIOUS ANNOUNCEMENTS -->
    <div>
      <h2 class="font-semibold mb-4">Previous Announcements</h2>

      <div
        v-for="a in announcements"
        :key="a.id"
        class="bg-white p-4 rounded-xl shadow mb-4"
      >
        <h3 class="font-bold">{{ a.title }}</h3>
        <p class="text-gray-600 mb-2">{{ a.content }}</p>

        <img
          v-if="a.image"
          :src="`/storage/${a.image}`"
          class="w-64 rounded mt-2"
        />

        <p class="text-xs text-gray-400 mt-2">
          {{ new Date(a.created_at).toLocaleString() }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'

defineProps({
  announcements: Array,
  success: String // flash message from session
})

const title = ref('')
const message = ref('')
const image = ref(null)
const flashSuccess = ref(success || null)

function handleFile(e) {
  image.value = e.target.files[0]
}

function postAnnouncement() {
  const formData = new FormData()
  formData.append('title', title.value)
  formData.append('message', message.value)

  if (image.value) {
    formData.append('image', image.value)
  }

  // Inertia POST request to backend
  Inertia.post('/peso/announcements', formData, {
    preserveScroll: true
  })
}
</script>

<style scoped>
/* Optional: basic styling */
</style>