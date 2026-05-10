<template>
  <div class="flex flex-col sm:flex-row min-h-screen bg-gradient-to-br from-blue-100 to-blue-200 overflow-hidden relative">

    <div v-if="mobileSidebarOpen" @click="mobileSidebarOpen = false" class="fixed inset-0 z-30 bg-black/40 sm:hidden"></div>

    <!-- ================= SIDEBAR ================= -->
     <aside
      class="dashboard-sidebar"    :class="[
      'bg-[#1f3a63] text-white flex flex-col transition-all duration-300 shadow-xl sm:rounded-r-3xl sm:sticky sm:top-0 sm:h-screen',
      isOpen ? 'w-72 p-6' : 'w-20 p-3',
      mobileSidebarOpen
        ? 'fixed inset-y-0 left-0 z-40 translate-x-0'
        : 'fixed inset-y-0 left-0 -translate-x-full sm:relative sm:inset-auto sm:translate-x-0'
    ]"
  >

      <!-- Logo + Toggle -->
      <div class="flex items-center justify-between mb-6">
        <div v-if="isOpen">
          <p class="text-xs text-gray-300">EMPLOYER</p>
          <h2 class="font-bold text-xl">DASHBOARD</h2>
        </div>
        <button @click="toggleSidebar"
          class="w-10 h-10 flex items-center justify-center text-xl rounded hover:bg-blue-800 transition"
          aria-label="Toggle Sidebar">
          ☰
        </button>
      </div>

      <!-- USER -->
      <div class="flex items-center mb-8" :class="isOpen ? 'gap-3' : 'justify-center'">
        <img
          :src="user.profile_photo_url || '/default-profile.png'"
          class="w-14 h-14 rounded-full object-cover"
        />
        <div v-if="isOpen">
          <p class="font-semibold">{{ user.name || 'Employer' }}</p>
          <p class="text-sm text-gray-300">Company / Employer</p>
        </div>
      </div>

      <!-- MENU -->
      <nav class="flex-1 min-h-0 overflow-y-auto text-sm space-y-4 pr-2">
        <div v-for="menu in menus" :key="menu.name" class="relative">

          <div
            class="menu-item"
            @click="menu.children ? toggleDropdown(menu.name) : router.visit(menu.href)"
            @mouseenter="!isOpen && (collapsedHover = menu.name)"
            @mouseleave="!isOpen && (collapsedHover = null)"
          >
            <span class="text-lg">{{ menu.icon }}</span>
            <span v-if="isOpen">{{ menu.label }}</span>
            <span v-if="menu.children && isOpen" class="ml-auto">▾</span>
          </div>

          <!-- Expanded submenu -->
          <div v-if="isOpen && menu.children && openDropdown === menu.name"
            class="pl-6 text-gray-300 text-xs mt-1 space-y-1">

            <Link
              v-for="child in menu.children"
              :key="child.label"
              :href="child.href"
              class="block hover:text-white"
            >
              {{ child.label }}
            </Link>

          </div>

          <!-- Collapsed Hover -->
          <div v-if="!isOpen && menu.children && collapsedHover === menu.name"
            class="absolute left-full top-0 ml-2 bg-white text-gray-800 shadow-lg rounded-xl p-3 w-52 z-50">

            <Link
              v-for="child in menu.children"
              :key="child.label"
              :href="child.href"
              class="block px-2 py-1 hover:bg-gray-100 rounded"
            >
              {{ child.label }}
            </Link>

          </div>

        </div>

        <!-- SETTINGS DIRECT LINK -->
        <div class="menu-item mt-4"
          @click="router.visit('/employer/settings')">
          <span class="text-lg"></span>
          <span v-if="isOpen">Settings</span>
        </div>

        <!-- LOGOUT -->
        <div class="mt-6 px-1">
          <button
            @click="logout"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-red-500 hover:bg-red-600 transition text-white"
          >
            <span class="text-lg">⏻</span>
            <span v-if="isOpen">Logout</span>
          </button>
        </div>

      </nav>
    </aside>

    <!-- ================= MAIN ================= -->
    <div class="flex-1 p-4 sm:p-6 overflow-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- HEADER -->
        <header class="flex flex-wrap items-center justify-between gap-4 mb-8">
          <button
            @click="mobileSidebarOpen = true"
            class="sm:hidden inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-gray-700 shadow-md"
            aria-label="Open Menu"
          >
            ☰
          </button>
          <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold text-gray-700 truncate">
              Welcome, {{ user.name || 'Employer' }}!
            </h1>
            <p class="text-sm text-gray-500">Dashboard overview</p>
          </div>

          <div class="flex items-center gap-4">

            <!--  Notification -->
            <div class="relative notif-menu">
              <button
                @click.stop="toggleNotif"
                class="relative w-10 h-10 rounded-full bg-yellow-400 shadow hover:bg-yellow-500 flex items-center justify-center text-white"
              >
                🔔
                <span
                  v-if="unreadCount > 0"
                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full"
                >
                  {{ unreadCount > 99 ? '99+' : unreadCount }}
                </span>
              </button>

              <!-- Dropdown -->
              <div
                v-if="notifOpen"
                class="absolute right-0 top-full mt-2 w-[22rem] max-w-[90vw] sm:w-80 bg-white rounded-xl shadow-lg border z-50 max-h-[520px] flex flex-col overflow-hidden"
              >
                <div class="p-4 border-b flex items-center justify-between bg-gray-50 rounded-t-xl">
                  <h3 class="font-bold text-lg">Notifications</h3>
                  <button
                    v-if="visibleNotifications.length > 0"
                    @click="markAllAsRead"
                    class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-full transition"
                  >
                    Mark all as read
                  </button>
                </div>

                <div class="flex border-b px-4 pt-3">
                  <button
                    @click="notifFilter = 'all'"
                    :class="['px-4 py-2 font-semibold text-sm border-b-2 transition', notifFilter === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900']"
                  >
                    All
                  </button>
                  <button
                    @click="notifFilter = 'unread'"
                    :class="['px-4 py-2 font-semibold text-sm border-b-2 transition', notifFilter === 'unread' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900']"
                  >
                    Unread
                  </button>
                </div>

                <ul v-if="visibleNotifications.length" class="overflow-y-auto flex-1">
                  <li
                    v-for="n in visibleNotifications.slice(0, 6)"
                    :key="n.id"
                    @click="openNotification(n)"
                    :class="['p-4 hover:bg-gray-50 cursor-pointer border-b transition', !n.read ? 'bg-blue-50' : '']"
                  >
                    <div class="flex gap-3">
                      <div v-if="!n.read" class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                      <div class="flex-1">
                        <p class="font-semibold text-sm text-gray-900">{{ n.title }}</p>
                        <p class="text-xs text-gray-600 mt-1">{{ n.content }}</p>
                        <img
                          v-if="n.image"
                          :src="'/storage/' + n.image"
                          class="w-full mt-2 rounded-lg shadow max-h-32 object-cover"
                          alt="Notification Image"
                        />
                        <p class="text-[10px] text-gray-400 mt-2">
                          {{ new Date(n.created_at).toLocaleString() }}
                        </p>
                      </div>
                    </div>
                  </li>
                </ul>

                <div v-else class="p-8 text-center text-gray-500 text-sm">
                  <p>{{ notifFilter === 'unread' ? 'No unread notifications' : 'No notifications' }}</p>
                </div>

                <div class="border-t p-3 bg-gray-50 rounded-b-xl">
                  <button
                    @click="goToNotifications"
                    class="w-full text-center text-blue-600 hover:text-blue-700 font-semibold text-sm py-2 transition"
                  >
                    See all notifications
                  </button>
                </div>
              </div>
            </div>

            <!-- PROFILE -->
            <div class="relative profile-menu">
              <button @click.stop="toggleMenu">
                <img
                  :src="user.profile_photo_url || '/default-profile.png'"
                  class="w-10 h-10 rounded-full object-cover border hover:opacity-80"
                />
              </button>

              <div v-if="menuOpen"
                class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border z-50">
                <button
                  @click="router.visit('/employer/settings')"
                  class="block w-full text-left px-4 py-2 hover:bg-gray-100">
                  Settings
                </button>
                <button @click="logout"
                  class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">
                  Logout
                </button>
              </div>
            </div>

          </div>
        </header>

        <!-- ================= SUMMARY CARDS ================= -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">

          <div class="card border-blue-500">
            <div class="text-sm text-gray-500">Open Jobs</div>
            <div class="text-3xl font-bold text-blue-600">
              {{ stats.open_jobs ?? 0 }}
            </div>
          </div>

          <div class="card border-green-500">
            <div class="text-sm text-gray-500">Applicants</div>
            <div class="text-3xl font-bold text-green-600">
              {{ stats.applicants ?? 0 }}
            </div>
          </div>

          <div class="card border-purple-500">
            <div class="text-sm text-gray-500">Pending Ratings</div>
            <div class="text-3xl font-bold text-purple-600">
              {{ stats.pending_ratings ?? 0 }}
            </div>
          </div>

          <div class="card border-indigo-500">
            <div class="text-sm text-gray-500">Attendance Today</div>
            <div class="text-3xl font-bold text-indigo-600">
              {{ stats.today_attendance ?? 0 }}
            </div>
          </div>

          <div class="card border-teal-500">
            <div class="text-sm text-gray-500">Completed Applications</div>
            <div class="text-3xl font-bold text-teal-600">
              {{ stats.completed_applications ?? 0 }}
            </div>
          </div>

        </div>

        <!-- ================= CHARTS ================= -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">

          <div class="bg-white p-6 rounded-2xl shadow-lg">
            <div class="flex items-start justify-between gap-4 mb-4">
              <div>
                <h2 class="font-semibold text-lg">Applicants per Job</h2>
                <p class="text-sm text-gray-500">How many applicants each job received</p>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-blue-600">{{ totalApplicants }}</div>
                <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Total applicants</p>
              </div>
            </div>
            <div class="relative">
              <canvas ref="applicantsCanvas" height="220"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-gray-500">
              <div class="rounded-2xl bg-blue-50 p-3">
                <div class="font-semibold text-blue-700">{{ jobCount }}</div>
                <div>Jobs tracked</div>
              </div>
              <div class="rounded-2xl bg-slate-50 p-3">
                <div class="font-semibold text-slate-700">{{ topJob?.title || 'No jobs yet' }}</div>
                <div>{{ topJobCount }} applicants</div>
              </div>
            </div>
          </div>

          <div class="bg-white p-6 rounded-2xl shadow-lg flex flex-col items-center justify-center">
            <div class="w-full flex items-center justify-between mb-4">
              <div>
                <h2 class="font-semibold">Completion Rate</h2>
                <p class="text-sm text-gray-500">Beneficiary outcome</p>
              </div>
              <div class="text-right">
                <div class="text-3xl font-bold text-green-600">{{ stats.completion_rate ?? 0 }}%</div>
                <div class="text-xs text-gray-500">of completed applications</div>
              </div>
            </div>
            <div class="w-full flex items-center justify-center mb-4">
              <canvas ref="completionCanvas" width="180" height="180" class="max-w-[180px] max-h-[180px]"></canvas>
            </div>
            <div class="w-full grid grid-cols-3 gap-3 mt-6 text-center text-xs">
              <div>
                <div class="h-2 w-full rounded-full bg-green-500 mb-1"></div>
                <div class="font-semibold text-sm text-gray-800">{{ stats.completed_applications ?? 0 }}</div>
                <div class="text-gray-500">Completed</div>
              </div>
              <div>
                <div class="h-2 w-full rounded-full bg-amber-500 mb-1"></div>
                <div class="font-semibold text-sm text-gray-800">{{ stats.ongoing_applications ?? 0 }}</div>
                <div class="text-gray-500">Ongoing</div>
              </div>
              <div>
                <div class="h-2 w-full rounded-full bg-red-500 mb-1"></div>
                <div class="font-semibold text-sm text-gray-800">{{ stats.not_started_applications ?? 0 }}</div>
                <div class="text-gray-500">Not Started</div>
              </div>
            </div>
          </div>

        </div>

        <!-- PERFORMANCE EVALUATION -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-10">
          <div class="xl:col-span-2 bg-white p-6 rounded-2xl shadow-lg">
            <div class="flex items-start justify-between gap-4 mb-4">
              <div>
                <h2 class="font-semibold text-lg">Performance Evaluation</h2>
                <p class="text-sm text-gray-500">Average employer rating across core categories</p>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-blue-600">{{ ratingSummary.count ?? 0 }}</div>
                <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Ratings submitted</p>
              </div>
            </div>
            <div class="relative" style="min-height: 240px; max-height: 280px;">
              <canvas ref="ratingChartCanvas" class="w-full h-full"></canvas>
            </div>
          </div>

          <div class="bg-white p-6 rounded-2xl shadow-lg">
            <h2 class="font-semibold text-lg mb-4">Rating summary</h2>
            <div class="grid grid-cols-2 gap-3 text-sm text-gray-600 mb-6">
              <div class="rounded-2xl bg-blue-50 p-4">
                <div class="font-semibold text-blue-700">{{ ratingSummary.punctuality }}</div>
                <div>Punctuality</div>
              </div>
              <div class="rounded-2xl bg-slate-50 p-4">
                <div class="font-semibold text-slate-800">{{ ratingSummary.work_quality }}</div>
                <div>Work Quality</div>
              </div>
              <div class="rounded-2xl bg-slate-50 p-4">
                <div class="font-semibold text-slate-800">{{ ratingSummary.attitude }}</div>
                <div>Attitude</div>
              </div>
              <div class="rounded-2xl bg-slate-50 p-4">
                <div class="font-semibold text-slate-800">{{ ratingSummary.communication }}</div>
                <div>Communication</div>
              </div>
              <div class="rounded-2xl bg-emerald-50 p-4 col-span-2">
                <div class="font-semibold text-emerald-700 text-2xl">{{ ratingSummary.overall }}</div>
                <div>Overall Score</div>
              </div>
            </div>

            <div>
              <div class="flex items-center justify-between gap-3 mb-3">
                <h3 class="font-semibold text-sm text-gray-700">Recent ratings</h3>
                <Link
                  href="/employer/ratings/history"
                  class="text-xs text-blue-600 hover:text-blue-800 font-semibold"
                >
                  View all ratings →
                </Link>
              </div>
              <div v-if="recentRatings.length" class="space-y-3">
                <div v-for="rating in recentRatings" :key="rating.id" class="rounded-2xl border border-gray-100 p-3 bg-gray-50">
                  <div class="flex items-center justify-between gap-2 text-xs text-gray-500 mb-2">
                    <div>{{ rating.beneficiary_name }}</div>
                    <div>{{ new Date(rating.created_at).toLocaleDateString() }}</div>
                  </div>
                  <div class="text-sm text-gray-800">Overall: <span class="font-semibold">{{ rating.overall }}</span></div>
                  <p class="text-gray-500 text-xs mt-1 truncate">{{ rating.comment || 'No comment' }}</p>
                </div>
              </div>
              <div v-else class="text-gray-500 text-sm">
                No ratings yet.
              </div>
            </div>
          </div>
        </div>

        <!-- RECENT ACTIVITIES -->
        <div class="bg-white p-6 rounded-2xl shadow-lg mb-10">
          <div class="flex justify-between items-center mb-3">
            <h2 class="font-semibold">Recent Activities</h2>
            <Link href="/employer/activities" class="text-sm text-blue-600 hover:text-blue-800">
              View All Activities →
            </Link>
          </div>
          <div v-if="activities.length === 0" class="text-gray-500 text-sm">
            No recent activities yet.
          </div>
          <div v-else class="space-y-3">
            <div v-for="activity in activities" :key="activity.type + activity.date" class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100">
              <span class="text-xl">{{ activity.icon }}</span>
              <div class="flex-1">
                <p class="font-semibold text-sm">{{ activity.title }}</p>
                <p class="text-gray-600 text-xs mt-1">{{ activity.description }}</p>
                <p class="text-gray-400 text-[11px] mt-2">{{ formatDate(activity.date) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Floating Button -->
        <button
          @click="router.visit('/employer/jobs')"
          class="fixed bottom-6 right-6 bg-indigo-600 text-white px-6 py-3 rounded-full shadow-lg hover:bg-indigo-700 transition"
        >
          + Post Job
        </button>

      </div>
    </div>

  </div>

  <!-- IMAGE MODAL -->
<div v-if="modalOpen" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
  <div class="relative max-w-3xl w-full p-4">
    <button
      @click="closeModal"
      class="absolute top-2 right-2 text-white text-2xl font-bold hover:text-gray-300"
    >×</button>

    <img :src="modalImage" class="w-full rounded-lg shadow-lg" />
  </div>
</div>

<!-- NOTIFICATION DETAILS MODAL -->
<div v-if="notificationModalOpen" @click.self="closeNotificationModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 px-4">
  <div class="relative max-w-2xl w-full bg-white rounded-3xl shadow-xl overflow-hidden">
    <div class="flex items-center justify-between p-5 border-b">
      <div>
        <h2 class="text-xl font-semibold text-gray-900">{{ activeNotification?.title || 'Notification' }}</h2>
        <p class="text-sm text-gray-500">{{ activeNotification ? new Date(activeNotification.created_at).toLocaleString() : '' }}</p>
      </div>
      <button
        @click="closeNotificationModal"
        class="text-gray-400 hover:text-gray-700 text-2xl font-bold"
      >×</button>
    </div>

    <div class="p-5 space-y-4">
      <p class="text-sm text-gray-700" v-if="activeNotification">{{ activeNotification.content }}</p>
      <img
        v-if="activeNotification && activeNotification.image"
        :src="'/storage/' + activeNotification.image"
        class="w-full rounded-2xl shadow-sm object-cover max-h-80"
        alt="Notification Image"
      />
      <div class="rounded-2xl bg-gray-50 p-4 text-sm text-gray-600">
        <p><span class="font-semibold">Type:</span> {{ activeNotification?.type || 'General' }}</p>
        <p><span class="font-semibold">Status:</span> {{ activeNotification?.read ? 'Read' : 'Unread' }}</p>
      </div>
    </div>

    <div class="p-4 border-t bg-gray-50 text-right">
      <button
        @click="closeNotificationModal"
        class="px-4 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition"
      >Close</button>
    </div>
  </div>
</div>

<!-- TOASTER -->
<div
  v-if="toast.show"
  :class="`fixed top-6 right-6 bg-${toast.color}-600 text-white px-4 py-2 rounded shadow-lg z-50 animate-fade-in`"
>
  {{ toast.message }}
</div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
const { props } = usePage()
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import Chart from 'chart.js/auto'

// ================= Sidebar & Dropdown =================
const isOpen = ref(true)
const mobileSidebarOpen = ref(false)
const openDropdown = ref(null)
const collapsedHover = ref(null)
const menuOpen = ref(false)
const search = ref('')
const interviews = ref([])

// ================= Notifications =================
const notifOpen = ref(false)
const notifFilter = ref('all')
const notificationModalOpen = ref(false)
const activeNotification = ref(null)
const notifications = ref([])
// Modal state
const modalOpen = ref(false)
const modalImage = ref('')

const visibleNotifications = computed(() => {
  const employerNotifications = notifications.value.filter(n => {
    return n.target_role === 'all' || n.target_role === 'employer'
  })
  if (notifFilter.value === 'unread') {
    return employerNotifications.filter(n => !n.read)
  }
  return employerNotifications
})

const unreadCount = computed(() => {
  return notifications.value.filter(n => {
    return (n.target_role === 'all' || n.target_role === 'employer') && !n.read
  }).length
})

// ================= Toast =================
const toast = ref({ show: false, message: '', color: 'green' })
function showToast(msg) {
  toast.value.message = msg
  toast.value.color = msg.toLowerCase().includes('fail') ? 'red' : 'green'
  toast.value.show = true
  setTimeout(() => { toast.value.show = false }, 4000)
}

function openImagePreview(imagePath) {
  modalImage.value = imagePath
  modalOpen.value = true
}

function closeModal() {
  modalOpen.value = false
  modalImage.value = ''
}
// computed list that only shows announcements meant for employer (or all)
const toggleNotif = () => {
  notifOpen.value = !notifOpen.value
  menuOpen.value = false
}

function markAllAsRead() {
  notifications.value = notifications.value.map(n => ({
    ...n,
    read: true
  }))
}

function openNotification(notification) {
  notificationModalOpen.value = true
  activeNotification.value = notification
  notification.read = true
}

function closeNotificationModal() {
  notificationModalOpen.value = false
  activeNotification.value = null
}

function goToNotifications() {
  notifOpen.value = false
  router.visit('/employer/notifications')
}

async function loadNotifications() {
  try {
    const res = await axios.get('/employer/notifications/data') // <-- use /data
    notifications.value = res.data || []
  } catch (err) {
    console.error('Failed to load notifications', err)
    notifications.value = []
  }
}

// ================= Sidebar Functions =================
const toggleSidebar = () => {
  if (typeof window !== 'undefined' && window.innerWidth < 640) {
    mobileSidebarOpen.value = !mobileSidebarOpen.value
    return
  }
  isOpen.value = !isOpen.value
}
const toggleDropdown = (name) =>
  openDropdown.value === name ? openDropdown.value = null : openDropdown.value = name
const toggleMenu = () => (menuOpen.value = !menuOpen.value)

// Close menus on outside click
function handleClick(e) {
  if (!e.target.closest('.profile-menu')) menuOpen.value = false
  if (!e.target.closest('.notif-menu')) notifOpen.value = false
}
onMounted(() => {
  document.addEventListener('click', handleClick)
  loadNotifications()
})
onBeforeUnmount(() => document.removeEventListener('click', handleClick))

// ================= User =================
const user = ref(props.auth.user || { name: 'Employer' })

// ================= Logout =================
async function logout() {
  await axios.post('/logout')
  router.visit('/login')
}

// ================= Stats =================
const stats = ref({})
const jobs = ref([])
const activities = ref([])
const applicantsCanvas = ref(null)
const completionCanvas = ref(null)
const ratingChartCanvas = ref(null)
const applicationsOverTimeCanvas = ref(null)

const ratingSummary = computed(() => {
  return stats.value.rating_summary ?? {
    punctuality: 0,
    work_quality: 0,
    attitude: 0,
    communication: 0,
    overall: 0,
    count: 0,
  }
})

const recentRatings = computed(() => stats.value.recent_ratings ?? [])

const totalApplicants = computed(() => {
  return jobs.value.reduce((sum, job) => {
    return sum + (job.total ?? job.count ?? job.applications_count ?? job.applicants_count ?? 0)
  }, 0)
})

const jobCount = computed(() => jobs.value.length)

const topJob = computed(() => {
  if (!jobs.value.length) return null
  return jobs.value.reduce((winner, job) => {
    const current = job.total ?? job.count ?? job.applications_count ?? job.applicants_count ?? 0
    const winnerCount = winner.total ?? winner.count ?? winner.applications_count ?? winner.applicants_count ?? 0
    return current > winnerCount ? job : winner
  }, jobs.value[0])
})

const topJobCount = computed(() => {
  if (!topJob.value) return 0
  return topJob.value.total ?? topJob.value.count ?? topJob.value.applications_count ?? topJob.value.applicants_count ?? 0
})

let applicantsChart = null
let completionChart = null
let applicationsChart = null

async function loadJobs() {
  const res = await axios.get('/employer/analytics/applicants-per-job')
  jobs.value = res.data
}

async function loadStats() {
  const res = await axios.get('/employer/stats')
  stats.value = res.data
}

async function loadInterviews() {
  try {
    const res = await axios.get('/employer/interviews/data')
    interviews.value = res.data
  } catch (err) {
    console.error('Failed to load interviews', err)
    interviews.value = []
  }
}

async function loadActivities() {
  try {
    const res = await axios.get('/employer/recent-activities')
    activities.value = res.data || []
  } catch (err) {
    console.error('Failed to load activities', err)
    activities.value = []
  }
}

// ================= Date Formatting =================
function formatDate(dateString) {
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24))
  const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
  const diffMinutes = Math.floor(diffMs / (1000 * 60))

  if (diffMinutes < 1) return 'Just now'
  if (diffMinutes < 60) return `${diffMinutes} minutes ago`
  if (diffHours < 24) return `${diffHours} hours ago`
  if (diffDays < 7) return `${diffDays} days ago`

  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// ================= Charts =================
function renderApplicantsChart() {
  if (!applicantsCanvas.value) return
  if (applicantsChart) applicantsChart.destroy()

  const labels = jobs.value.map(j => j.title || j.name || 'Job')
  const values = jobs.value.map(j => j.total ?? j.count ?? j.applications_count ?? j.applicants_count ?? 0)

  applicantsChart = new Chart(applicantsCanvas.value, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        data: values,
        backgroundColor: labels.map(() => 'rgba(59, 130, 246, 0.85)'),
        borderRadius: 12,
        borderSkipped: false,
        maxBarThickness: 40
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label(context) {
              return `${context.formattedValue} applicants`
            }
          }
        }
      },
      layout: {
        padding: {
          top: 12,
          bottom: 6,
          left: 4,
          right: 4
        }
      },
      scales: {
        x: {
          ticks: {
            color: '#475569',
            maxRotation: 45,
            minRotation: 0,
            autoSkip: false
          },
          grid: { display: false }
        },
        y: {
          beginAtZero: true,
          ticks: {
            color: '#475569',
            precision: 0
          },
          grid: {
            color: 'rgba(148, 163, 184, 0.2)'
          }
        }
      }
    }
  })
}

function renderCompletionChart() {
  if (!completionCanvas.value) return
  if (completionChart) completionChart.destroy()

  completionChart = new Chart(completionCanvas.value, {
    type: 'doughnut',
    data: {
      labels: ['Completed', 'Ongoing', 'Not Started'],
      datasets: [{
        data: [
          stats.value.completed_applications ?? 0,
          stats.value.ongoing_applications ?? 0,
          stats.value.not_started_applications ?? 0
        ],
        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
        hoverOffset: 8,
        borderWidth: 0
      }]
    },
    options: {
      cutout: '70%',
      plugins: {
        legend: { display: false }
      }
    }
  })
}

let ratingEvaluationChart = null
function renderRatingChart() {
  if (!ratingChartCanvas.value) return
  if (ratingEvaluationChart) ratingEvaluationChart.destroy()

  ratingEvaluationChart = new Chart(ratingChartCanvas.value, {
    type: 'radar',
    data: {
      labels: ['Punctuality', 'Work Quality', 'Attitude', 'Communication', 'Overall'],
      datasets: [{
        label: 'Average score',
        data: [
          ratingSummary.value.punctuality,
          ratingSummary.value.work_quality,
          ratingSummary.value.attitude,
          ratingSummary.value.communication,
          ratingSummary.value.overall
        ],
        backgroundColor: 'rgba(59, 130, 246, 0.18)',
        borderColor: '#2563eb',
        borderWidth: 2,
        pointBackgroundColor: '#2563eb',
        pointBorderColor: '#ffffff',
        pointHoverRadius: 6,
        tension: 0.4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        r: {
          beginAtZero: true,
          min: 0,
          max: 5,
          ticks: {
            stepSize: 1,
            color: '#64748b'
          },
          angleLines: {
            color: 'rgba(148, 163, 184, 0.25)'
          },
          grid: {
            color: 'rgba(148, 163, 184, 0.2)'
          },
          pointLabels: {
            color: '#475569',
            font: { size: 12 }
          }
        }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label(context) {
              return `${context.formattedValue} / 5`
            }
          }
        }
      }
    }
  })
}

function renderApplicationsChart() {
  if (!applicationsOverTimeCanvas.value) return
  if (applicationsChart) applicationsChart.destroy()

  applicationsChart = new Chart(applicationsOverTimeCanvas.value, {
    type: 'line',
    data: {
      labels: stats.value.applications_over_time?.map(r => r.date) ?? [],
      datasets: [{
        data: stats.value.applications_over_time?.map(r => r.total) ?? [],
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59,130,246,0.2)',
        fill: true
      }]
    }
  })
}

// Re-render charts automatically when data changes
watch([jobs, stats], () => {
  renderApplicantsChart()
  renderCompletionChart()
  renderApplicationsChart()
  renderRatingChart()
})

// ================= Sidebar Menus =================
const menus = [
  { name:'profile', label:'Employer Profile', icon:'', children:[
    {label:'Company Profile', href:'/employer/settings'},
  ]},
  { name:'jobs', label:'Job Listing Management', icon:'', children:[
    {label:'Post Job', href:'/employer/jobs'},
    { label:'View assigned applicants and submit ratings', href:'/employer/applicants'},
    /* ✅ ADD THIS */
      { label:'Completion Rate', href:'/employer/completion-rate' }
  ]},
  { name:'attendance', label:'Attendance & Work', icon:'', children:[
    {label:'Review DTR', href:'/employer/attendance'}
  ]},
  { name:'reports', label:'Reports', icon:'', children:[
    {label:'Employer Reports', href:'/employer/reports'},
    {label:'Activity History', href:'/employer/activities'}
  ]},
  { name:'notifications', label:'Notifications', icon:'', children:[
    {label:'Announcements',href:'/employer/notifications'}
  ]},
]
// ================= Beneficiaries =================
const beneficiaries = ref([])

async function loadBeneficiaries() {
  try {
    const res = await axios.get('/employer/beneficiaries')
    console.log('Backend response:', res.data) // check what backend sends

    const data = Array.isArray(res.data) ? res.data : res.data.beneficiaries ?? []

    beneficiaries.value = data.map(b => ({
      ...b,
      ratings: {
        punctuality: 5,
        work_quality: 5,
        attitude: 5,
        communication: 5,
        overall: 5,
        remarks: ''
      },
      submitting: false
    }))
  } catch (err) {
    console.error('Failed to load beneficiaries:', err)
    beneficiaries.value = []
  }
}
// ================= Initial Load =================
onMounted(async () => {
  await loadJobs()
  await loadStats()
  await loadInterviews()
  await loadBeneficiaries()
  await loadActivities()
  renderApplicantsChart()
  renderCompletionChart()
  renderApplicationsChart()
})
</script>

<style>
.menu-item {
  display:flex;
  align-items:center;
  gap:12px;
  padding:8px;
  border-radius:8px;
  cursor:pointer;
  transition:0.2s;
}
.menu-item:hover {
  background:#1e40af;
}
.card {
  background:white;
  padding:24px;
  border-radius:16px;
  box-shadow:0 10px 20px rgba(0,0,0,0.08);
  border-left:4px solid;
}
</style>