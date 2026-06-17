<script setup>
import { computed, onMounted, ref } from 'vue'

const props = defineProps({
  announcements: {
    type: Array,
    default: () => [],
  },
})

const selectedMessage = ref(null)
const search = ref('')

const filteredAnnouncements = computed(() => {
  const keyword = search.value.trim().toLowerCase()
  if (!keyword) return props.announcements

  return props.announcements.filter(item =>
    [item.title, item.content, item.created_at].join(' ').toLowerCase().includes(keyword)
  )
})

function goBack() {
  history.back()
}

function openMessage(message) {
  selectedMessage.value = message
}

function closeMessage() {
  selectedMessage.value = null
}

function formatDate(date) {
  if (!date) return 'No date'
  const value = new Date(date)
  if (Number.isNaN(value.getTime())) return date
  return value.toLocaleString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  selectedMessage.value = props.announcements[0] || null
})
</script>

<template>
  <main class="min-h-screen bg-slate-100 px-4 py-6 text-slate-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
      <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
          <button type="button" class="mb-4 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50" @click="goBack">
            Back
          </button>
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">CPESO Communication</p>
          <h1 class="mt-2 text-3xl font-bold text-slate-950">Announcements</h1>
          <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
            Review CPESO advisories, reminders, and employer workflow updates in one place.
          </p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-xs font-semibold uppercase text-slate-500">Announcements</p>
          <p class="mt-1 text-2xl font-bold">{{ announcements.length }}</p>
        </div>
      </header>

      <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <input
          v-model="search"
          type="search"
          placeholder="Search messages..."
          class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
        >
      </section>

      <section class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 bg-slate-50 px-5 py-3">
            <h2 class="text-sm font-bold uppercase tracking-[0.12em] text-slate-500">Inbox</h2>
          </div>

          <div v-if="filteredAnnouncements.length === 0" class="p-8 text-center text-sm text-slate-500">
            No messages found.
          </div>

          <button
            v-for="message in filteredAnnouncements"
            :key="message.id"
            type="button"
            class="block w-full border-b border-slate-200 px-5 py-4 text-left last:border-b-0 hover:bg-slate-50"
            :class="selectedMessage?.id === message.id ? 'bg-blue-50' : 'bg-white'"
            @click="openMessage(message)"
          >
            <div class="flex items-start justify-between gap-3">
              <p class="font-semibold text-slate-900">{{ message.title }}</p>
              <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700">CPESO</span>
            </div>
            <p class="mt-2 line-clamp-2 text-sm text-slate-500">{{ message.content }}</p>
            <p class="mt-2 text-xs text-slate-400">{{ formatDate(message.created_at) }}</p>
          </button>
        </div>

        <article class="min-h-[360px] rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <div v-if="!selectedMessage" class="flex h-full items-center justify-center text-center text-sm text-slate-500">
            Select a message to view its details.
          </div>

          <div v-else>
            <div class="flex flex-col gap-3 border-b border-slate-200 pb-5 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-600">Announcement</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-950">{{ selectedMessage.title }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ formatDate(selectedMessage.created_at) }}</p>
              </div>
              <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Employer</span>
            </div>

            <p class="mt-5 whitespace-pre-line text-sm leading-7 text-slate-700">{{ selectedMessage.content }}</p>

            <img
              v-if="selectedMessage.image"
              :src="`/storage/${selectedMessage.image}`"
              alt="Announcement attachment"
              class="mt-5 max-h-[440px] w-full rounded-lg border border-slate-200 object-contain"
            >
          </div>
        </article>
      </section>
    </div>

    <div v-if="selectedMessage" class="fixed inset-0 z-50 hidden bg-black/50 p-4" @click.self="closeMessage"></div>
  </main>
</template>
