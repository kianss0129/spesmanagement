<template>
  <aside
    class="bg-[#1f3a63] text-white flex flex-col transition-all duration-300 shadow-xl"
    :class="['sticky top-0 h-screen', isOpen ? 'w-72 p-5' : 'w-20 p-3']"
  >
    <!-- HEADER -->
    <div class="flex items-center justify-between mb-6">
      <div v-if="isOpen" class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-blue-500/30 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
          </svg>
        </div>
        <span class="font-bold text-base">SPES System</span>
      </div>
      <button @click="$emit('toggle')" class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-white/10 transition">
        <svg v-if="isOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <!-- NAVIGATION -->
    <nav class="flex-1 overflow-y-auto">
      <ul class="space-y-1">
        <li v-for="item in menuItems" :key="item.key">

          <!-- SINGLE ITEM (no children) -->
          <button
            v-if="!item.children?.length"
            @click="$emit('select', item.key)"
            class="w-full text-left px-3 py-2.5 rounded-xl flex items-center gap-3 transition-all duration-200 text-sm"
            :class="selectedTab === item.key
              ? 'bg-blue-600 text-white font-semibold shadow-md'
              : 'text-blue-100 hover:bg-white/10'"
          >
            <span class="w-5 text-center text-xs font-bold opacity-70">{{ item.shortLabel }}</span>
            <span v-if="isOpen">{{ item.label }}</span>
          </button>

          <!-- GROUP (with children) -->
          <div v-else class="space-y-0.5">
            <!-- Group header -->
            <div
              class="w-full px-3 py-2 rounded-xl flex items-center gap-3 text-sm cursor-default"
              :class="isGroupActive(item) ? 'text-white' : 'text-blue-200/70'"
            >
              <span class="w-5 text-center text-xs font-bold opacity-60">{{ item.shortLabel }}</span>
              <span v-if="isOpen" class="text-xs font-semibold uppercase tracking-wider">{{ item.label }}</span>
            </div>

            <!-- Children -->
            <ul class="space-y-0.5" :class="isOpen ? 'pl-5' : 'pl-0'">
              <li v-for="child in item.children" :key="child.key">
                <button
                  @click="$emit('select', child.key)"
                  class="w-full text-left px-3 py-2 rounded-lg flex items-center gap-2 text-sm transition-all duration-200"
                  :class="selectedTab === child.key
                    ? 'bg-blue-600 text-white font-semibold shadow-sm'
                    : 'text-blue-100/80 hover:bg-white/10 hover:text-white'"
                >
                  <span v-if="!isOpen" class="w-5 text-center text-xs font-bold opacity-70">{{ child.shortLabel }}</span>
                  <span v-if="isOpen">{{ child.label }}</span>
                </button>
              </li>
            </ul>
          </div>
        </li>

        <!-- SEPARATOR -->
        <li class="pt-4 mt-4 border-t border-white/10">
          <button
            @click="$emit('logout')"
            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl bg-red-500/20 hover:bg-red-500 transition-all duration-200 text-red-200 hover:text-white text-sm"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span v-if="isOpen">Logout</span>
          </button>
        </li>
      </ul>
    </nav>
  </aside>
</template>

<script setup>
const props = defineProps({
  isOpen: { type: Boolean, required: true },
  selectedTab: { type: String, required: true },
  menuItems: { type: Array, required: true },
})

defineEmits(['toggle', 'select', 'logout'])

function isGroupActive(item) {
  return item.children?.some((child) => child.key === props.selectedTab)
}
</script>
