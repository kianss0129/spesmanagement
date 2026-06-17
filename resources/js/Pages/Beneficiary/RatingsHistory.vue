<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-100 via-blue-50 to-slate-100 relative overflow-hidden">

    <!-- GLOW EFFECT -->
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-300/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-cyan-300/20 rounded-full blur-3xl"></div>

    <div class="relative z-10 p-6 lg:p-10">

      <!-- TOP BAR -->
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 mb-10">

        <div>
          <div
            class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-100 border border-blue-200 text-blue-700 text-xs font-semibold mb-4 shadow-sm"
          >
            ⭐ Beneficiary Portal
          </div>

          <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight">
            Ratings History
          </h1>

         
        </div>

        <!-- BACK -->
        <button
          @click="goBack"
          class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-white border border-slate-200 text-slate-700 font-semibold shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition duration-300"
        >
          ← Back
        </button>

      </div>

      <!-- SUMMARY -->
      <div
        v-if="ratings.length"
        class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-10"
      >

        <!-- CARD -->
        <div
          class="relative overflow-hidden rounded-[28px] bg-white border border-slate-200 p-6 shadow-xl"
        >
          <div class="absolute top-0 right-0 w-32 h-32 bg-blue-100 rounded-full blur-3xl"></div>

          <div class="relative z-10">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">
                  Avg Punctuality
                </p>

                <h2 class="text-4xl font-black text-blue-600 mt-3">
                  {{ avg('punctuality') }}
                </h2>
              </div>

              <div
                class="w-16 h-16 rounded-2xl bg-blue-100 flex items-center justify-center text-3xl"
              >
                ⏰
              </div>
            </div>
          </div>
        </div>

        <!-- CARD -->
        <div
          class="relative overflow-hidden rounded-[28px] bg-white border border-slate-200 p-6 shadow-xl"
        >
          <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-100 rounded-full blur-3xl"></div>

          <div class="relative z-10">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">
                  Avg Work
                </p>

                <h2 class="text-4xl font-black text-cyan-600 mt-3">
                  {{ avg('work_quality') }}
                </h2>
              </div>

              <div
                class="w-16 h-16 rounded-2xl bg-cyan-100 flex items-center justify-center text-3xl"
              >
                💼
              </div>
            </div>
          </div>
        </div>

        <!-- CARD -->
        <div
          class="relative overflow-hidden rounded-[28px] bg-white border border-slate-200 p-6 shadow-xl"
        >
          <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-100 rounded-full blur-3xl"></div>

          <div class="relative z-10">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">
                  Avg Attitude
                </p>

                <h2 class="text-4xl font-black text-yellow-500 mt-3">
                  {{ avg('attitude') }}
                </h2>
              </div>

              <div
                class="w-16 h-16 rounded-2xl bg-yellow-100 flex items-center justify-center text-3xl"
              >
                😊
              </div>
            </div>
          </div>
        </div>

        <!-- CARD -->
        <div
          class="relative overflow-hidden rounded-[28px] bg-white border border-slate-200 p-6 shadow-xl"
        >
          <div class="absolute top-0 right-0 w-32 h-32 bg-green-100 rounded-full blur-3xl"></div>

          <div class="relative z-10">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">
                  Overall Score
                </p>

                <h2 class="text-4xl font-black text-green-600 mt-3">
                  {{ avg('overall') }}
                </h2>
              </div>

              <div
                class="w-16 h-16 rounded-2xl bg-green-100 flex items-center justify-center text-3xl"
              >
                ⭐
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- EMPTY -->
      <div
        v-if="ratings.length === 0"
        class="bg-white border border-slate-200 rounded-[32px] shadow-2xl p-20 text-center"
      >

        <div
          class="w-28 h-28 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-6xl mb-6"
        >
          📭
        </div>

        <h2 class="text-2xl font-black text-slate-800">
          No Ratings Yet
        </h2>

        <p class="text-slate-500 mt-3">
          Employer ratings and evaluations will appear here.
        </p>

      </div>

      <!-- TABLE -->
      <div
        v-else
        class="rounded-[32px] bg-white border border-slate-200 shadow-2xl overflow-hidden"
      >

        <!-- HEADER -->
        <div
          class="px-8 py-6 border-b border-slate-200 bg-slate-50 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4"
        >

          <div>
            <h2 class="text-2xl font-black text-slate-900">
              Employer Feedback Overview
            </h2>

            <p class="text-slate-500 text-sm mt-2">
              Review employer evaluations and comments.
            </p>
          </div>

          <!-- COUNT -->
          <div
            class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-blue-100 text-blue-700 text-sm font-bold"
          >
            📊 {{ ratings.length }} Ratings
          </div>

        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto">

          <table class="w-full text-sm">

            <thead class="bg-slate-100 text-slate-600 uppercase text-xs tracking-wider">
              <tr>
                <th class="p-5 text-left">Employer</th>
                <th class="p-5 text-center">Punctuality</th>
                <th class="p-5 text-center">Work</th>
                <th class="p-5 text-center">Attitude</th>
                <th class="p-5 text-center">Comm</th>
                <th class="p-5 text-center">Overall</th>
                <th class="p-5 text-left">Comment</th>
                <th class="p-5 text-center">Date</th>
              </tr>
            </thead>

            <tbody>

              <tr
                v-for="r in ratings"
                :key="r.id"
                class="border-t border-slate-100 hover:bg-blue-50/60 transition duration-300"
              >

                <!-- EMPLOYER -->
                <td class="p-5">
                  <div class="flex items-center gap-4">

                    <div
                      class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-400 text-white flex items-center justify-center font-black text-lg shadow-lg"
                    >
                      {{
                        (r.employer_name ??
                        r.employer?.company_name ??
                        'U').charAt(0)
                      }}
                    </div>

                    <div>
                      <p class="font-bold text-slate-800">
                        {{
                          r.employer_name ??
                          r.employer?.company_name ??
                          'Unknown'
                        }}
                      </p>

                      <p class="text-xs text-slate-400 mt-1">
                        Employer Evaluation
                      </p>
                    </div>

                  </div>
                </td>

                <!-- SCORES -->
                <td class="p-5 text-center font-bold text-slate-700">
                  ⭐ {{ r.punctuality ?? 0 }}
                </td>

                <td class="p-5 text-center font-bold text-slate-700">
                  ⭐ {{ r.work_quality ?? 0 }}
                </td>

                <td class="p-5 text-center font-bold text-slate-700">
                  ⭐ {{ r.attitude ?? 0 }}
                </td>

                <td class="p-5 text-center font-bold text-slate-700">
                  ⭐ {{ r.communication ?? 0 }}
                </td>

                <!-- OVERALL -->
                <td class="p-5 text-center">
                  <span
                    :class="
                      r.overall >= 4
                        ? 'bg-green-100 text-green-700'
                        : r.overall >= 3
                        ? 'bg-yellow-100 text-yellow-700'
                        : 'bg-red-100 text-red-700'
                    "
                    class="px-4 py-2 rounded-full font-black text-xs shadow-sm"
                  >
                    ⭐ {{ r.overall ?? 0 }}
                  </span>
                </td>

                <!-- COMMENT -->
                <td class="p-5">
                  <div
                    class="max-w-xs rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3 text-slate-600 leading-relaxed"
                  >
                    {{ r.comment || 'No comment provided.' }}
                  </div>
                </td>

                <!-- DATE -->
                <td class="p-5 text-center text-slate-400 text-xs whitespace-nowrap">
                  {{ formatDate(r.created_at) }}
                </td>

              </tr>

            </tbody>

          </table>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

const props = defineProps({
  ratings: {
    type: Array,
    default: () => []
  }
})

function goBack() {
  window.history.back()
}

function formatDate(date) {
  return date
    ? new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      })
    : ''
}

function avg(field) {
  if (!props.ratings.length) return 0

  const sum = props.ratings.reduce((acc, r) => {
    return acc + (r[field] ?? 0)
  }, 0)

  return (sum / props.ratings.length).toFixed(1)
}
</script>