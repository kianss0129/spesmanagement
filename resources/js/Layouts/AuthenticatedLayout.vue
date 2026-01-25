<template>
  <div>
    <!-- Topbar -->
    <div class="flex justify-between items-center bg-white shadow px-6 py-4">
      <div class="text-sm text-gray-700">
        {{ $page.props.auth.user.name }}
      </div>

      <button
        @click="logout"
        class="text-red-600 text-sm hover:underline"
      >
        Logout
      </button>
    </div>

    <div class="flex">
      <!-- Sidebar -->
      <aside class="w-64 bg-gray-800 text-white min-h-screen p-4">
        <ul class="space-y-2">
          <li><Link href="/admin/dashboard">Dashboard</Link></li>
          <li><Link href="/peso/beneficiaries/pending">Pending Beneficiaries</Link></li>
          <li><Link href="/peso/employers/pending">Pending Employers</Link></li>
        </ul>
      </aside>

      <!-- Main -->
      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'

const logout = () => {
  router.post('/logout')
}
</script>
