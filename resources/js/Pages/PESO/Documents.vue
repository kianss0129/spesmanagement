<script setup>
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Documents" />

  <main class="min-h-screen bg-slate-100 px-4 py-8 text-slate-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl">
      <Link
        href="/dashboard"
        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
      >
        Back to Dashboard
      </Link>

      <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">
          CPESO
        </p>

        <h1 class="mt-3 text-3xl font-bold text-slate-900">
          Documents
        </h1>

        <p class="mt-3 text-sm leading-6 text-slate-600">
          Submitted documents will appear here.
        </p>
      </section>
    </div>
  </main>
</template>
