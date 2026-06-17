<script setup>
import { router } from '@inertiajs/vue3'

function goBack() {
  router.visit('/employer')
}
</script>

<template>
  <main class="min-h-screen bg-slate-100 px-4 py-8 text-slate-900 sm:px-6 lg:px-8">
    <section class="mx-auto max-w-3xl rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Schedule</p>
      <h1 class="mt-2 text-3xl font-bold">Scheduling is handled by CPESO</h1>
      <p class="mt-3 text-sm leading-6 text-slate-600">
        Employers do not create exam, interview, or contract schedules. Please use the assigned beneficiaries,
        DTR review, daily reports, ratings, and completion submission pages for your employer workflow.
      </p>
      <div class="mt-6 flex flex-wrap gap-3">
        <button
          type="button"
          class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
          @click="goBack"
        >
          Back to Dashboard
        </button>
        <a
          href="/employer/completion-rate"
          class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
        >
          Assigned Beneficiaries
        </a>
      </div>
    </section>
  </main>
</template>
