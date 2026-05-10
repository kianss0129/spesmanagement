<template>
  <div class="p-6 bg-gradient-to-br from-slate-100 via-blue-50 to-slate-100 min-h-screen">
    
    <!-- HEADER -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-8">
      <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
          Ratings History
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          View and manage all employer ratings submitted for beneficiaries.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <!-- FILTER -->
        <select
          v-model="sortOrder"
          class="px-4 py-2 rounded-2xl border border-slate-200 bg-white shadow-sm text-sm focus:ring-2 focus:ring-blue-500 outline-none"
        >
          <option value="high">Overall: High → Low</option>
          <option value="low">Overall: Low → High</option>
          <option value="latest">Latest First</option>
          <option value="oldest">Oldest First</option>
        </select>

        <!-- BACK -->
        <Link
          href="/employer"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-blue-600 text-white text-sm font-semibold shadow hover:bg-blue-700 transition"
        >
          ← Dashboard
        </Link>
      </div>
    </div>

    <!-- STATS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
      <div class="bg-white rounded-3xl p-5 shadow-lg border border-slate-200">
        <p class="text-sm text-slate-500">Total Ratings</p>
        <h2 class="text-3xl font-bold text-slate-900 mt-2">
          {{ ratings.length }}
        </h2>
      </div>

      <div class="bg-white rounded-3xl p-5 shadow-lg border border-slate-200">
        <p class="text-sm text-slate-500">Average Overall</p>
        <h2 class="text-3xl font-bold text-yellow-500 mt-2">
          ⭐ {{ averageOverall }}
        </h2>
      </div>

      <div class="bg-white rounded-3xl p-5 shadow-lg border border-slate-200">
        <p class="text-sm text-slate-500">Highest Rating</p>
        <h2 class="text-3xl font-bold text-green-600 mt-2">
          {{ highestRating }}
        </h2>
      </div>
    </div>

    <!-- TABLE CARD -->
    <div class="bg-white rounded-[28px] shadow-2xl border border-slate-200 overflow-hidden">

      <!-- TOP BAR -->
      <div class="p-6 border-b bg-slate-50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h2 class="text-xl font-bold text-slate-900">
            Employer Ratings
          </h2>
          <p class="text-sm text-slate-500 mt-1">
            Ratings overview with comments and evaluation details.
          </p>
        </div>

        <!-- SEARCH -->
        <div class="relative w-full md:w-80">
          <input
            v-model="search"
            type="text"
            placeholder="Search beneficiary..."
            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pl-11 text-sm shadow-sm focus:ring-2 focus:ring-blue-500 outline-none"
          />
          <span class="absolute left-4 top-3.5 text-slate-400">
            🔍
          </span>
        </div>
      </div>

      <!-- TABLE -->
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-slate-700">
          
          <thead class="bg-slate-100 text-slate-700 uppercase text-xs tracking-wider">
            <tr>
              <th class="px-6 py-4 text-left">Beneficiary</th>
              <th class="px-6 py-4 text-center">Punctuality</th>
              <th class="px-6 py-4 text-center">Work Quality</th>
              <th class="px-6 py-4 text-center">Attitude</th>
              <th class="px-6 py-4 text-center">Communication</th>
              <th class="px-6 py-4 text-center">Overall</th>
              <th class="px-6 py-4 text-left">Comment</th>
              <th class="px-6 py-4 text-left">Date</th>
            </tr>
          </thead>

          <tbody>
            <!-- EMPTY -->
            <tr v-if="filteredRatings.length === 0">
              <td colspan="8" class="py-14 text-center">
                <div class="flex flex-col items-center">
                  <div class="text-5xl mb-3">📭</div>
                  <p class="font-semibold text-slate-700">
                    No ratings found
                  </p>
                  <p class="text-sm text-slate-500">
                    Try adjusting your filters or search.
                  </p>
                </div>
              </td>
            </tr>

            <!-- ROW -->
            <tr
              v-for="rating in filteredRatings"
              :key="rating.id"
              class="border-b hover:bg-blue-50/40 transition"
            >
              <!-- BENEFICIARY -->
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <div class="w-11 h-11 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                    {{ rating.beneficiary_name?.charAt(0) }}
                  </div>

                  <div>
                    <p class="font-semibold text-slate-900">
                      {{ rating.beneficiary_name }}
                    </p>
                    <p class="text-xs text-slate-500">
                      ID: {{ rating.beneficiary_id }}
                    </p>
                  </div>
                </div>
              </td>

              <!-- RATINGS -->
              <td class="px-6 py-5 text-center">
                {{ rating.punctuality }}
              </td>

              <td class="px-6 py-5 text-center">
                {{ rating.work_quality }}
              </td>

              <td class="px-6 py-5 text-center">
                {{ rating.attitude }}
              </td>

              <td class="px-6 py-5 text-center">
                {{ rating.communication }}
              </td>

              <!-- OVERALL -->
              <td class="px-6 py-5 text-center">
                <span
                  class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold"
                  :class="overallClass(rating.overall)"
                >
                  ⭐ {{ rating.overall }}
                </span>
              </td>

              <!-- COMMENT -->
              <td class="px-6 py-5">
                <div class="max-w-xs text-slate-600 line-clamp-2">
                  {{ rating.comment || 'No comment provided.' }}
                </div>
              </td>

              <!-- DATE -->
              <td class="px-6 py-5 text-slate-500 whitespace-nowrap">
                {{ formatDate(rating.created_at) }}
              </td>
            </tr>
          </tbody>

        </table>
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
const sortOrder = ref('high')

function formatDate(value) {
  return new Date(value).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

function overallClass(value) {
  if (value >= 4.5) {
    return 'bg-green-100 text-green-700'
  }

  if (value >= 3) {
    return 'bg-yellow-100 text-yellow-700'
  }

  return 'bg-red-100 text-red-700'
}

const averageOverall = computed(() => {
  if (!ratings.value.length) return 0

  const total = ratings.value.reduce((sum, r) => {
    return sum + Number(r.overall)
  }, 0)

  return (total / ratings.value.length).toFixed(1)
})

const highestRating = computed(() => {
  if (!ratings.value.length) return 0

  return Math.max(...ratings.value.map(r => Number(r.overall)))
})

const filteredRatings = computed(() => {
  let data = [...ratings.value]

  // SEARCH
  if (search.value) {
    data = data.filter(r =>
      r.beneficiary_name
        ?.toLowerCase()
        .includes(search.value.toLowerCase())
    )
  }

  // SORT
  if (sortOrder.value === 'high') {
    data.sort((a, b) => b.overall - a.overall)
  }

  if (sortOrder.value === 'low') {
    data.sort((a, b) => a.overall - b.overall)
  }

  if (sortOrder.value === 'latest') {
    data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  }

  if (sortOrder.value === 'oldest') {
    data.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
  }

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