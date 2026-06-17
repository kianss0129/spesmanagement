<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 to-blue-200 p-6">
    <div class="max-w-7xl mx-auto space-y-6">

      <!-- HEADER CARD -->
      <div class="bg-white rounded-2xl shadow-lg p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-800">Ratings History</h1>
            <p class="text-sm text-gray-500 mt-1">View submitted beneficiary performance evaluations.</p>
          </div>

          <div class="flex flex-wrap items-center gap-3">
            <select
              v-model="sortOrder"
              class="px-4 py-2 rounded-xl border border-gray-200 bg-white text-sm text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none"
            >
              <option value="latest">Latest First</option>
              <option value="oldest">Oldest First</option>
              <option value="high">Overall: High → Low</option>
              <option value="low">Overall: Low → High</option>
            </select>

            <Link
              href="/employer"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold shadow hover:bg-blue-700 transition"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
              </svg>
              Dashboard
            </Link>
          </div>
        </div>
      </div>

      <!-- STAT CARDS -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-lg p-5 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Total Ratings</p>
            <p class="text-2xl font-bold text-gray-800">{{ ratings.length }}</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-5 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Average Overall</p>
            <p class="text-2xl font-bold text-yellow-600">{{ averageOverall }}</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-5 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Highest Rating</p>
            <p class="text-2xl font-bold text-green-600">{{ highestRating }}</p>
          </div>
        </div>
      </div>

      <!-- TABLE CARD -->
      <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

        <!-- TABLE TOP BAR -->
        <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h2 class="text-lg font-bold text-gray-800">Evaluation Records</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ filteredRatings.length }} {{ filteredRatings.length === 1 ? 'record' : 'records' }}</p>
          </div>

          <!-- SEARCH -->
          <div class="relative w-full sm:w-72">
            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              v-model="search"
              type="text"
              placeholder="Search beneficiary..."
              class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2 pl-10 pr-9 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 focus:bg-white outline-none transition"
            />
            <button
              v-if="search"
              type="button"
              class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 transition"
              @click="search = ''"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto">
          <table v-if="filteredRatings.length" class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500 border-b border-gray-100">
                <th class="px-6 py-3 text-left">Beneficiary</th>
                <th class="px-4 py-3 text-center">Punctuality</th>
                <th class="px-4 py-3 text-center">Work Quality</th>
                <th class="px-4 py-3 text-center">Attitude</th>
                <th class="px-4 py-3 text-center">Communication</th>
                <th class="px-4 py-3 text-center">Overall</th>
                <th class="px-5 py-3 text-left">Comment</th>
                <th class="px-5 py-3 text-left">Date</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-50">
              <tr
                v-for="rating in filteredRatings"
                :key="rating.id"
                class="hover:bg-blue-50/50 transition"
              >
                <!-- BENEFICIARY -->
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-bold shadow-sm">
                      {{ initial(rating.beneficiary_name) }}
                    </div>
                    <div>
                      <p class="font-semibold text-gray-800">{{ rating.beneficiary_name }}</p>
                      <p class="text-xs text-gray-400">ID: {{ rating.beneficiary_id }}</p>
                    </div>
                  </div>
                </td>

                <!-- SCORES -->
                <td class="px-4 py-4 text-center">
                  <span :class="scoreBadge(rating.punctuality)">{{ rating.punctuality }}</span>
                </td>
                <td class="px-4 py-4 text-center">
                  <span :class="scoreBadge(rating.work_quality)">{{ rating.work_quality }}</span>
                </td>
                <td class="px-4 py-4 text-center">
                  <span :class="scoreBadge(rating.attitude)">{{ rating.attitude }}</span>
                </td>
                <td class="px-4 py-4 text-center">
                  <span :class="scoreBadge(rating.communication)">{{ rating.communication }}</span>
                </td>

                <!-- OVERALL -->
                <td class="px-4 py-4 text-center">
                  <span :class="['inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold', overallBadge(rating.overall)]">
                    ★ {{ rating.overall }}
                  </span>
                </td>

                <!-- COMMENT -->
                <td class="px-5 py-4">
                  <p class="max-w-[180px] text-gray-600 truncate" :title="rating.comment || 'No comment'">
                    {{ rating.comment || '—' }}
                  </p>
                </td>

                <!-- DATE -->
                <td class="px-5 py-4 text-gray-500 whitespace-nowrap">
                  {{ formatDate(rating.created_at) }}
                </td>
              </tr>
            </tbody>
          </table>

          <!-- EMPTY STATE -->
          <div v-else class="py-16 text-center">
            <div class="mx-auto w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
              </svg>
            </div>
            <p class="font-semibold text-gray-700">No ratings found</p>
            <p class="text-sm text-gray-500 mt-1">
              {{ search ? 'Try a different search term.' : 'Ratings you submit for beneficiaries will appear here.' }}
            </p>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'

const ratings = ref([])
const search = ref('')
const sortOrder = ref('latest')

function formatDate(value) {
  if (!value) return '—'
  return new Date(value).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

function initial(name) {
  return String(name || '?').charAt(0).toUpperCase()
}

function scoreBadge(value) {
  const num = Number(value) || 0
  const base = 'inline-flex h-7 w-7 items-center justify-center rounded-lg text-xs font-bold'
  if (num >= 5) return `${base} bg-green-100 text-green-700`
  if (num >= 4) return `${base} bg-blue-100 text-blue-700`
  if (num >= 3) return `${base} bg-yellow-100 text-yellow-700`
  return `${base} bg-red-100 text-red-700`
}

function overallBadge(value) {
  const num = Number(value) || 0
  if (num >= 5) return 'bg-green-100 text-green-700'
  if (num >= 4) return 'bg-blue-100 text-blue-700'
  if (num >= 3) return 'bg-yellow-100 text-yellow-700'
  return 'bg-red-100 text-red-700'
}

const averageOverall = computed(() => {
  if (!ratings.value.length) return '—'
  const total = ratings.value.reduce((sum, r) => sum + Number(r.overall || 0), 0)
  return (total / ratings.value.length).toFixed(1)
})

const highestRating = computed(() => {
  if (!ratings.value.length) return '—'
  return Math.max(...ratings.value.map(r => Number(r.overall || 0)))
})

const filteredRatings = computed(() => {
  let data = [...ratings.value]

  if (search.value) {
    const term = search.value.toLowerCase()
    data = data.filter(r =>
      (r.beneficiary_name || '').toLowerCase().includes(term)
    )
  }

  if (sortOrder.value === 'high') data.sort((a, b) => b.overall - a.overall)
  if (sortOrder.value === 'low') data.sort((a, b) => a.overall - b.overall)
  if (sortOrder.value === 'latest') data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  if (sortOrder.value === 'oldest') data.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))

  return data
})

async function loadRatings() {
  try {
    const res = await axios.get('/employer/ratings/history/data')
    ratings.value = res.data || []
  } catch (err) {
    console.error('Failed to load ratings history', err)
    ratings.value = []
  }
}

onMounted(loadRatings)
</script>
