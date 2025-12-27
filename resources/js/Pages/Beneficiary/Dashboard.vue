<template>
  <div class="p-6 min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <header class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Beneficiary Dashboard</h1>
        <!-- Logout Button -->
        <button
          @click="logout"
          class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition"
        >
          Logout
        </button>
      </header>

      <!-- Beneficiary Features -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <FeatureCard title="Profile" href="/beneficiary/profile" />
        <FeatureCard title="Application" href="/beneficiary/applications/page" />
        <FeatureCard title="Upload Documents" href="/beneficiary/upload-documents/page" />
        <FeatureCard title="Requirements Submission" />
        <FeatureCard title="Announcement / Schedule" />
        <FeatureCard title="Notification of Exam / Qualification" />
        <FeatureCard title="Submission of Attendance / Report" />
        <FeatureCard title="View Job Vacancy" />
        <FeatureCard title="Interview Schedule + Google Meet Link" />
        <FeatureCard title="View Employer Ratings Received" />
      </section>
    </div>
  </div>
</template>

<script setup>
import { Inertia } from '@inertiajs/inertia'

// Logout function
const logout = () => {
  Inertia.post('/logout')
}

// Modern FeatureCard component inline
const FeatureCard = {
  props: {
    title: { type: String, required: true },
    href: { type: String, default: null }
  },
  template: `
    <div
      @click="href && go()"
      class="bg-white rounded-lg shadow p-4 cursor-pointer hover:shadow-md transition"
    >
      <h3 class="font-medium text-gray-700">{{ title }}</h3>
    </div>
  `,
  methods: {
    go() {
      Inertia.visit(this.href)
    }
  }
}
</script>

<style scoped>
/* Optional: smooth hover effect for cards */
</style>
