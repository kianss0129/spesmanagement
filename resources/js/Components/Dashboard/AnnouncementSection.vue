<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  selectedTab: String,
  isAdminRole: Boolean,
  isPesoUser: Boolean,
  filteredAnnouncements: {
    type: Array,
    default: () => [],
  },
  newAnnouncement: Object,
  openImage: Function,
  formatDate: Function,
})

const emit = defineEmits(['createAnnouncement', 'imageUpload', 'editAnnouncement', 'deleteAnnouncement'])

const currentPage = ref(1)
const perPage = 8
const selectedAnnouncement = ref(null)

const totalPages = computed(() => Math.max(Math.ceil(props.filteredAnnouncements.length / perPage), 1))
const paginatedAnnouncements = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return props.filteredAnnouncements.slice(start, start + perPage)
})

const audienceCounts = computed(() => ({
  beneficiaries: props.filteredAnnouncements.filter((item) => audienceValue(item).includes('beneficiary')).length,
  employers: props.filteredAnnouncements.filter((item) => audienceValue(item).includes('employer')).length,
  all: props.filteredAnnouncements.filter((item) => audienceValue(item).includes('all')).length,
}))

function submitAnnouncement() {
  emit('createAnnouncement')
}

function onImageUpload(event) {
  emit('imageUpload', event)
}

function audienceValue(announcement) {
  return String(announcement?.targetRole || announcement?.target_role || announcement?.audience || 'all').toLowerCase()
}

function audienceLabel(announcement) {
  const value = audienceValue(announcement)
  if (value.includes('beneficiary')) return 'Beneficiaries'
  if (value.includes('employer')) return 'Employers'
  return 'All'
}

function statusLabel(announcement) {
  return announcement?.status || (announcement?.published_at === null ? 'Draft' : 'Posted')
}

function statusClass(announcement) {
  const value = String(statusLabel(announcement)).toLowerCase()
  if (value.includes('draft')) return 'bg-slate-100 text-slate-700'
  if (value.includes('archived') || value.includes('inactive')) return 'bg-amber-100 text-amber-800'
  return 'bg-green-100 text-green-800'
}

function postedDate(announcement) {
  return announcement?.created_at || announcement?.published_at || announcement?.date
}

function nextPage() {
  if (currentPage.value < totalPages.value) currentPage.value++
}

function prevPage() {
  if (currentPage.value > 1) currentPage.value--
}

watch(
  () => props.filteredAnnouncements,
  () => {
    currentPage.value = 1
  }
)
</script>

<template>
  <section v-if="selectedTab === 'announcements'" class="space-y-6">
    <header class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">CPESO Communication</p>
      <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">Announcements</h1>
      <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
        Post SPES updates and review announcements sent to beneficiaries and employers.
      </p>
    </header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-semibold text-slate-600">Total Posts</p>
        <p class="mt-3 text-3xl font-bold text-slate-900">{{ filteredAnnouncements.length }}</p>
      </div>
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-semibold text-slate-600">Beneficiaries</p>
        <p class="mt-3 text-3xl font-bold text-slate-900">{{ audienceCounts.beneficiaries }}</p>
      </div>
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-semibold text-slate-600">Employers</p>
        <p class="mt-3 text-3xl font-bold text-slate-900">{{ audienceCounts.employers }}</p>
      </div>
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-semibold text-slate-600">All Audience</p>
        <p class="mt-3 text-3xl font-bold text-slate-900">{{ audienceCounts.all }}</p>
      </div>
    </section>

    <section v-if="isAdminRole" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <div>
        <h2 class="text-lg font-bold text-slate-900">Post Announcement</h2>
        <p class="mt-1 text-sm text-slate-500">Choose the audience and publish a clear SPES notice.</p>
      </div>

      <form class="mt-5 grid gap-4" @submit.prevent="submitAnnouncement">
        <input
          v-model="newAnnouncement.title"
          type="text"
          required
          placeholder="Announcement title"
          class="rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
        >

        <textarea
          v-model="newAnnouncement.message"
          rows="4"
          required
          placeholder="Write announcement details..."
          class="rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
        />

        <div class="grid gap-4 lg:grid-cols-[1fr_1fr_auto] lg:items-end">
          <div>
            <label class="text-sm font-semibold text-slate-700">Audience</label>
            <select
              v-model="newAnnouncement.targetRole"
              class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
            >
              <option value="beneficiary">Beneficiaries</option>
              <option value="employer">Employers</option>
              <option value="all">All</option>
            </select>
          </div>

          <div>
            <label class="text-sm font-semibold text-slate-700">Image</label>
            <input
              type="file"
              accept="image/*"
              class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm"
              @change="onImageUpload"
            >
          </div>

          <button type="submit" class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
            Post Announcement
          </button>
        </div>
      </form>
    </section>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-200 p-5">
        <h2 class="text-lg font-bold text-slate-900">Announcement List</h2>
        <p class="mt-1 text-sm text-slate-500">Latest notices and their target audience.</p>
      </div>

      <div class="hidden grid-cols-[1.5fr_0.8fr_0.9fr_0.7fr_1fr] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 xl:grid">
        <span>Title</span>
        <span>Audience</span>
        <span>Date Posted</span>
        <span>Status</span>
        <span>Actions</span>
      </div>

      <div
        v-for="announcement in paginatedAnnouncements"
        :key="announcement.id"
        class="grid gap-4 border-b border-slate-200 px-5 py-5 last:border-b-0 xl:grid-cols-[1.5fr_0.8fr_0.9fr_0.7fr_1fr] xl:items-center"
      >
        <div>
          <p class="font-semibold text-slate-900">{{ announcement.title }}</p>
          <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ announcement.message || announcement.content }}</p>
        </div>
        <span class="w-fit rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
          {{ audienceLabel(announcement) }}
        </span>
        <p class="text-sm text-slate-700">{{ formatDate(postedDate(announcement)) }}</p>
        <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(announcement)">
          {{ statusLabel(announcement) }}
        </span>
        <div class="flex flex-wrap gap-2">
          <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="selectedAnnouncement = announcement">
            View
          </button>
          <button v-if="isAdminRole" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100" @click="emit('editAnnouncement', announcement)">
            Edit
          </button>
          <button v-if="isAdminRole" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100" @click="emit('deleteAnnouncement', announcement)">
            Delete
          </button>
        </div>
      </div>

      <div v-if="paginatedAnnouncements.length === 0" class="px-5 py-12 text-center">
        <p class="text-sm font-semibold text-slate-700">No announcements yet.</p>
        <p class="mt-1 text-sm text-slate-500">Posted announcements will appear here.</p>
      </div>
    </section>

    <div v-if="totalPages > 1" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-center">
      <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 disabled:opacity-50" :disabled="currentPage === 1" @click="prevPage">Previous</button>
      <span class="text-sm text-slate-600">Page {{ currentPage }} of {{ totalPages }}</span>
      <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 disabled:opacity-50" :disabled="currentPage === totalPages" @click="nextPage">Next</button>
    </div>

    <div v-if="selectedAnnouncement" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="selectedAnnouncement = null">
      <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-lg font-bold text-slate-900">{{ selectedAnnouncement.title }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ audienceLabel(selectedAnnouncement) }} • {{ formatDate(postedDate(selectedAnnouncement)) }}</p>
          </div>
          <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700" @click="selectedAnnouncement = null">Close</button>
        </div>
        <p class="mt-5 whitespace-pre-line text-sm leading-6 text-slate-700">{{ selectedAnnouncement.message || selectedAnnouncement.content }}</p>
        <img
          v-if="selectedAnnouncement.image"
          :src="`/storage/${selectedAnnouncement.image}`"
          alt="Announcement image"
          class="mt-5 max-h-72 w-full rounded-lg object-cover"
          @click="openImage && openImage(`/storage/${selectedAnnouncement.image}`)"
        >
      </div>
    </div>
  </section>
</template>
