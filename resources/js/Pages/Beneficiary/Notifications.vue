<script setup>
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  announcements: {
    type: Array,
    default: () => [],
  },
})

const activeFilter = ref('all')
const modalImage = ref('')
const messages = ref(props.announcements.map(normalizeMessage))

const filters = [
  { key: 'all', label: 'All' },
  { key: 'status', label: 'Status Updates' },
  { key: 'requirements', label: 'Requirements' },
  { key: 'schedule', label: 'Schedule' },
  { key: 'announcements', label: 'Announcements' },
]

const categoryStyles = {
  'Status Update': 'border-blue-200 bg-blue-50 text-blue-800',
  'Requirement Reminder': 'border-amber-200 bg-amber-50 text-amber-800',
  'Schedule Reminder': 'border-indigo-200 bg-indigo-50 text-indigo-800',
  'Document Correction': 'border-red-200 bg-red-50 text-red-800',
  'Assignment Update': 'border-green-200 bg-green-50 text-green-800',
  Announcement: 'border-slate-200 bg-slate-100 text-slate-700',
}

const filteredMessages = computed(() => {
  return messages.value
    .filter((message) => {
      if (activeFilter.value === 'all') return true
      if (activeFilter.value === 'status') return message.category === 'Status Update'
      if (activeFilter.value === 'requirements') {
        return ['Requirement Reminder', 'Document Correction'].includes(message.category)
      }
      if (activeFilter.value === 'schedule') return message.category === 'Schedule Reminder'
      if (activeFilter.value === 'announcements') return message.category === 'Announcement'

      return true
    })
    .sort((a, b) => {
      if (a.read !== b.read) return a.read ? 1 : -1
      return new Date(b.created_at || 0) - new Date(a.created_at || 0)
    })
})

const unreadCount = computed(() => messages.value.filter((message) => !message.read).length)

function normalizeMessage(message) {
  const category = resolveCategory(message)

  return {
    ...message,
    category,
    read: Boolean(message.read),
    action: resolveAction(category),
  }
}

function resolveCategory(message) {
  const text = `${message.title || ''} ${message.content || ''}`.toLowerCase()

  // Broadcast/general announcements with target_role 'all' default to Announcement
  // unless the content strongly targets a specific beneficiary action
  const isBroadcast = !message.target_role || message.target_role === 'all'

  if (hasAny(text, ['correction', 'correct', 'replace', 'rejected document', 'unclear', 'invalid document'])) {
    return 'Document Correction'
  }

  if (!isBroadcast && hasAny(text, ['requirement', 'document', 'upload', 'missing', 'incomplete'])) {
    return 'Requirement Reminder'
  }

  if (hasAny(text, ['schedule', 'interview', 'exam', 'orientation', 'appointment'])) {
    return 'Schedule Reminder'
  }

  if (hasAny(text, ['assigned', 'assignment', 'employer', 'job placement', 'placement'])) {
    return 'Assignment Update'
  }

  if (!isBroadcast && hasAny(text, ['status', 'application', 'approved', 'qualified', 'pending', 'completed'])) {
    return 'Status Update'
  }

  return 'Announcement'
}

function hasAny(value, keywords) {
  return keywords.some((keyword) => value.includes(keyword))
}

function resolveAction(category) {
  if (category === 'Announcement') {
    return null
  }

  if (category === 'Status Update') {
    return { label: 'View Application', href: '/beneficiary/applications' }
  }

  if (['Requirement Reminder', 'Document Correction'].includes(category)) {
    return { label: 'Upload Requirement', href: '/beneficiary/upload' }
  }

  if (category === 'Schedule Reminder') {
    return { label: 'View Schedule', href: '/beneficiary/interviews' }
  }

  if (category === 'Assignment Update') {
    return { label: 'View Job Placement', href: '/beneficiary/jobs' }
  }

  return null
}

function categoryClass(category) {
  return categoryStyles[category] || categoryStyles.Announcement
}

function imageUrl(path) {
  if (!path) return ''
  if (String(path).startsWith('http') || String(path).startsWith('/storage/')) return path
  return `/storage/${path}`
}

function excerpt(content) {
  const text = String(content || '').replace(/\s+/g, ' ').trim()
  if (text.length <= 180) return text
  return `${text.slice(0, 180)}...`
}

function formatDate(date) {
  if (!date) return 'Date not available'
  const parsed = new Date(date)
  if (Number.isNaN(parsed.getTime())) return date

  return parsed.toLocaleString('en-PH', {
    dateStyle: 'medium',
    timeStyle: 'short',
    timeZone: 'Asia/Manila',
  })
}

async function markAsRead(message) {
  if (message.read || !message.id) return

  message.read = true

  try {
    await axios.post(`/api/beneficiary/notifications/${message.id}/mark-read`)
  } catch (error) {
    message.read = false
  }
}

async function markAllAsRead() {
  const previous = messages.value.map((message) => ({ id: message.id, read: message.read }))
  messages.value = messages.value.map((message) => ({ ...message, read: true }))

  try {
    await axios.post('/api/beneficiary/notifications/mark-all-read')
  } catch (error) {
    messages.value = messages.value.map((message) => {
      const previousMessage = previous.find((item) => item.id === message.id)
      return { ...message, read: previousMessage?.read ?? message.read }
    })
  }
}
</script>

<template>
  <Head title="Messages" />

  <main class="min-h-screen bg-slate-100 px-4 py-6 text-slate-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <Link
          href="/beneficiary"
          class="inline-flex w-fit items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
        >
          Back to Dashboard
        </Link>

        <button
          type="button"
          :disabled="unreadCount === 0"
          class="inline-flex w-fit items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
          @click="markAllAsRead"
        >
          Mark all as read
        </button>
      </div>

      <section class="mt-6">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">
          Communication Center
        </p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Announcements</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
          Check application updates, requirement reminders, schedules, document corrections, job placement updates, and CPESO announcements.
        </p>
      </section>

      <section class="mt-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-sm font-semibold text-slate-800">
              {{ unreadCount }} unread message{{ unreadCount === 1 ? '' : 's' }}
            </p>
            <p class="mt-1 text-sm text-slate-800">
              Unread and important announcements are shown first.
            </p>
          </div>

          <div class="flex flex-wrap gap-2">
            <button
              v-for="filter in filters"
              :key="filter.key"
              type="button"
              class="rounded-lg border px-3 py-2 text-sm font-semibold transition"
              :class="activeFilter === filter.key
                ? 'border-blue-600 bg-blue-600 text-white'
                : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'"
              @click="activeFilter = filter.key"
            >
              {{ filter.label }}
            </button>
          </div>
        </div>
      </section>

      <section class="mt-6">
        <div
          v-if="filteredMessages.length === 0"
          class="rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm"
        >
          <h2 class="text-lg font-bold text-slate-900">No messages found</h2>
          <p class="mt-2 text-sm text-slate-500">
            Messages from CPESO will appear here once available.
          </p>
        </div>

        <div v-else class="grid gap-4">
          <article
            v-for="message in filteredMessages"
            :key="message.id"
            class="rounded-lg border bg-white p-5 shadow-sm transition hover:border-blue-200"
            :class="message.read ? 'border-slate-200' : 'border-blue-300 ring-1 ring-blue-100'"
          >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <span
                    class="rounded-full border px-3 py-1 text-xs font-semibold"
                    :class="categoryClass(message.category)"
                  >
                    {{ message.category }}
                  </span>
                  <span
                    class="rounded-full px-3 py-1 text-xs font-semibold"
                    :class="message.read ? 'bg-slate-100 text-slate-600' : 'bg-blue-100 text-blue-800'"
                  >
                    {{ message.read ? 'Read' : 'Unread' }}
                  </span>
                </div>

                <h2 class="mt-3 text-lg font-bold text-slate-900">
                  {{ message.title }}
                </h2>
                <p class="mt-1 text-xs font-medium text-slate-500">
                  {{ formatDate(message.created_at) }}
                </p>
              </div>
            </div>

            <p class="mt-4 text-sm leading-6 text-slate-700">
              {{ excerpt(message.content) }}
            </p>

            <div v-if="message.image" class="mt-4">
              <button
                type="button"
                class="block overflow-hidden rounded-lg border border-slate-200 bg-slate-50 text-left"
                @click="modalImage = imageUrl(message.image); markAsRead(message)"
              >
                <img
                  :src="imageUrl(message.image)"
                  alt="Announcement image"
                  class="max-h-80 w-full object-cover"
                />
              </button>
            </div>

            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
              <Link
                v-if="message.action"
                :href="message.action.href"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                @click="markAsRead(message)"
              >
                {{ message.action.label }}
              </Link>

              <button
                v-if="!message.read"
                type="button"
                class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                @click="markAsRead(message)"
              >
                Mark as read
              </button>
            </div>
          </article>
        </div>
      </section>
    </div>

    <div
      v-if="modalImage"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
      @click.self="modalImage = ''"
    >
      <div class="max-h-[90vh] max-w-5xl">
        <button
          type="button"
          class="mb-3 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-800"
          @click="modalImage = ''"
        >
          Close
        </button>
        <img
          :src="modalImage"
          alt="Announcement preview"
          class="max-h-[82vh] w-full rounded-lg object-contain"
        />
      </div>
    </div>
  </main>
</template>
