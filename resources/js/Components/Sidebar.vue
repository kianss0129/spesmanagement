<template>
  <aside
    class="bg-[#1f3a63] text-white flex flex-col transition-all duration-300 shadow-xl"
    :class="['sticky top-0 h-screen', isOpen ? 'w-72 p-6' : 'w-20 p-3']"
  >
    <div class="flex items-center justify-between mb-6">
      <span class="font-bold text-lg" v-if="isOpen">Dashboard</span>
      <button @click="$emit('toggle')" class="focus:outline-none">
        <svg v-if="isOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <nav class="flex-1">
      <ul class="space-y-2">
        <li v-for="item in menuItems" :key="item.key">
          <button
            @click="$emit('select', item.key)"
            :class="{ 'bg-blue-600': selectedTab === item.key, 'hover:bg-blue-500': selectedTab !== item.key }"
            class="w-full text-left px-4 py-2 rounded flex items-center gap-2 transition"
          >
            <span>{{ isOpen ? item.label : item.shortLabel }}</span>
          </button>
        </li>
        <li class="mt-6">
          <!-- LOGOUT -->
        <div class="mt-6 px-1">
          <button
            @click="$emit('logout')"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-red-500 hover:bg-red-600 transition text-white"
          >
            <span class="text-lg">⏻</span>
            <span v-if="isOpen">Logout</span>
          </button>
        </div>
        </li>
      </ul>
    </nav>
  </aside>
</template>

<script setup>
const props = defineProps({
  isOpen: { type: Boolean, required: true },
  selectedTab: { type: String, required: true },
  menuItems: { type: Array, required: true }
})

defineEmits(['toggle', 'select', 'logout'])
</script>
