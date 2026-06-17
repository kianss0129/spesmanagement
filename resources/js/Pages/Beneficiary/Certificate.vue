<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
  certificatePath: {
    type: String,
    default: null,
  },
  applicationStatus: {
    type: String,
    default: null,
  },
  completedAt: {
    type: String,
    default: null,
  },
})

const certificateUrl = computed(() => {
  if (!props.certificatePath) return ''
  if (/^https?:\/\//i.test(props.certificatePath)) return props.certificatePath
  if (props.certificatePath.startsWith('/storage/')) return props.certificatePath
  return `/storage/${props.certificatePath.replace(/^public\//, '')}`
})

const displayStatus = computed(() => {
  return String(props.applicationStatus || 'Pending')
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (char) => char.toUpperCase())
})
</script>

<template>
  <Head title="Certificate / Completion Record" />

  <main class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
    <section class="mx-auto max-w-3xl">
      <Link
        href="/beneficiary"
        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
      >
        Back to Dashboard
      </Link>

      <div class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">
          SPES Record
        </p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">
          Certificate / Completion Record
        </h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">
          Current application status: <span class="font-semibold text-slate-900">{{ displayStatus }}</span>
        </p>

        <div v-if="certificateUrl" class="mt-8 rounded-lg border border-green-200 bg-green-50 p-5">
          <h2 class="text-lg font-semibold text-green-900">Certificate available</h2>
          <p class="mt-2 text-sm text-green-800">
            Your SPES completion certificate is ready for viewing or download.
          </p>
          <div class="mt-5 flex flex-col gap-3 sm:flex-row">
            <a
              :href="certificateUrl"
              target="_blank"
              rel="noopener"
              class="inline-flex items-center justify-center rounded-lg bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700"
            >
              View Certificate
            </a>
            <a
              :href="certificateUrl"
              download
              class="inline-flex items-center justify-center rounded-lg border border-green-300 bg-white px-5 py-3 text-sm font-semibold text-green-800 hover:bg-green-100"
            >
              Download Certificate
            </a>
          </div>
        </div>

        <div v-else class="mt-8 rounded-lg border border-amber-200 bg-amber-50 p-5">
          <h2 class="text-lg font-semibold text-amber-900">Certificate pending</h2>
          <p class="mt-2 text-sm leading-6 text-amber-800">
            Your certificate will be available after CPESO approves your completion.
          </p>
        </div>
      </div>
    </section>
  </main>
</template>
