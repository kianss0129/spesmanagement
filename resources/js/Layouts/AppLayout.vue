<script setup>
import { ref, onMounted } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';

defineProps({
  title: String,
});

const showingNavigationDropdown = ref(false);
const page = usePage();
const flashMessage = ref('');

onMounted(() => {
  // ✅ Flash message timeout
  if (page.props.flash?.success) {
    flashMessage.value = page.props.flash.success;
    setTimeout(() => {
      flashMessage.value = '';
    }, 4000);
  }

  // ✅ Dynamically load Google reCAPTCHA script
  const recaptchaScript = document.createElement('script');
  recaptchaScript.src = 'https://www.google.com/recaptcha/api.js';
  recaptchaScript.async = true;
  recaptchaScript.defer = true;
  document.head.appendChild(recaptchaScript);
});

const logout = () => {
  router.post(route('logout'));
};
</script>

<template>
  <div>
    <Head :title="title" />
    <Banner />

    <div class="min-h-screen bg-gray-100">
      <!-- Navigation -->
      <nav class="bg-white border-b border-gray-200 shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16 items-center">
            <!-- ✅ Custom Logo -->
            <div class="flex items-center">
              <img src="/images/spes-logo.png" alt="" class="h-9 w-auto" />
              <span class="ml-2 font-bold text-lg text-gray-800">SPES MANAGEMENT SYSTEM</span>
            </div>

            <!-- Logout Button -->
            <div class="flex items-center space-x-4">
              <span class="text-sm text-gray-700">
                {{ page.props.auth.user.name }}
              </span>
              <!-- Optional: Add logout button -->
              
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Header -->
      <header v-if="$slots.header" class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Content -->
      <main class="py-6 px-4 sm:px-6 lg:px-8">
        <slot />
      </main>
    </div>

    <!-- ✅ Success Toast Notification -->
    <div
      v-if="flashMessage"
      class="fixed bottom-6 right-6 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50"
    >
      {{ flashMessage }}
    </div>
  </div>
</template>
