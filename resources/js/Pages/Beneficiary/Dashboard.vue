<template>
    <div class="flex min-h-screen bg-gradient-to-br from-blue-100 to-blue-200">




      <!-- SIDEBAR -->
      <aside :class="['bg-[#1f3a63] text-white flex flex-col transition-all duration-300 shadow-xl rounded-r-3xl sticky top-0 h-screen', isOpen ? 'w-72 p-6' : 'w-20 p-3']">
        <!-- Logo + Toggle -->
        <div class="flex items-center justify-between mb-6">
          <div v-if="isOpen">
            <p class="text-xs text-gray-300">BENEFICIARY</p>
            <h2 class="font-bold text-xl">DASHBOARD</h2>
          </div>
          <button @click="toggleSidebar" class="w-10 h-10 flex items-center justify-center text-xl rounded hover:bg-blue-800 transition">
            ☰
          </button>
        </div>




        <!-- USER -->
        <div class="flex items-center mb-8" :class="isOpen ? 'gap-3' : 'justify-center'">
          <img
    :src="$page.props.auth.user?.profile_photo_url || '/default-avatar.png'"
    alt="Profile"
    class="w-14 h-14 rounded-full object-cover"
  />
          <div v-if="isOpen">
            <p class="font-semibold">{{ user.name || 'User' }}</p>
            <p class="text-sm text-gray-300">Student / Applicant</p>
          </div>
        </div>




        <!-- MENU -->
        <nav class="flex-1 overflow-y-auto text-sm space-y-6 pr-2">
          <div v-for="menu in menus" :key="menu.name" class="relative">
            <div
              class="menu-item"
              @click="menu.children
  ? toggleDropdown(menu.name)
  : menu.href && router.visit(menu.href)"
              @mouseenter="!isOpen && (collapsedHover = menu.name)"
              @mouseleave="!isOpen && (collapsedHover = null)"
            >
              <span class="text-lg">{{ menu.icon }}</span>
              <span v-if="isOpen">{{ menu.label }}</span>
              <span v-if="menu.children && isOpen" class="ml-auto">▾</span>
            </div>




            <!-- Expanded submenu inline -->
            <div v-if="isOpen && menu.children && openDropdown === menu.name" class="pl-6 text-gray-300 text-xs mt-1 space-y-1">
              <Link
                v-for="child in menu.children"
                :key="child.label"
                :href="child.href"
                class="block hover:text-white"
              >
                {{ child.label }}
              </Link>
            </div>




            <!-- Collapsed sidebar hover dropdown -->
            <div v-if="!isOpen && menu.children && collapsedHover === menu.name"
                class="absolute left-full top-0 ml-2 bg-white text-gray-800 shadow-lg rounded-xl p-3 w-48 z-50">
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




      <!-- MAIN CONTENT -->
      <div class="flex-1 p-6 overflow-auto">
        <div class="max-w-7xl mx-auto">




          <!-- HEADER -->
          <header class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div>
              <h1 class="text-2xl font-bold text-gray-700">Welcome, {{ user.name || 'User' }}!</h1>
              <p class="text-sm text-gray-500">Dashboard overview</p>
            </div>









            <div class="flex items-center gap-4">
              <!-- 🔔 NOTIFICATION -->
              <div class="relative notif-menu">
                <button
                  @click.stop="toggleNotif"
                  class="relative w-10 h-10 rounded-full bg-white shadow hover:bg-gray-100 flex items-center justify-center"
                >
                  🔔
                  <span
                    v-if="unreadCount > 0"
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full font-bold"
                  >
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                  </span>
                </button>




    <!-- DROPDOWN -->
    <div
      v-if="notifOpen"
      class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border z-50 max-h-[500px] flex flex-col"
    >
      <!-- Header -->
      <div class="p-4 border-b flex items-center justify-between bg-gray-50 rounded-t-xl">
        <h3 class="font-bold text-lg">Notifications</h3>
        <button 
          v-if="notifications.length > 0"
          @click="markAllAsRead"
          class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-full transition"
        >
          Mark all as read
        </button>
      </div>

      <!-- Tabs -->
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

      <!-- Notifications List -->
      <ul v-if="filteredNotifications.length" class="overflow-y-auto flex-1">
        <li
          v-for="n in filteredNotifications.slice(0, 5)"
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
                :src="`/storage/${n.image}`"
                class="w-full mt-2 rounded-lg shadow max-h-32 object-cover"
                alt="Notification Image"
              />
              <p class="text-[10px] text-gray-400 mt-2">
                {{ formatDate(n.created_at) }}
              </p>
            </div>
          </div>
        </li>
      </ul>

      <!-- Empty State -->
      <div v-else class="p-8 text-center text-gray-500 text-sm">
        <p>{{ notifFilter === 'unread' ? 'No unread notifications' : 'No notifications' }}</p>
      </div>

      <!-- Footer - See All -->
      <div class="border-t p-3 bg-gray-50 rounded-b-xl">
        <button 
          @click="goToNotifications"
          class="w-full text-center text-blue-600 hover:text-blue-700 font-semibold text-sm py-2 transition"
        >
          See all notifications
        </button>
      </div>
    </div>

    <div v-if="notificationModalOpen" @click.self="closeNotificationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
      <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ activeNotification?.title }}</h3>
            <p class="text-xs text-gray-500">{{ formatDate(activeNotification?.created_at) }}</p>
          </div>
          <button @click="closeNotificationModal" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        <div class="p-6 space-y-4">
          <p class="text-sm text-gray-700">{{ activeNotification?.content }}</p>
          <img
            v-if="activeNotification?.image"
            :src="`/storage/${activeNotification.image}`"
            class="w-full rounded-2xl object-cover"
            alt="Notification Image"
          />
        </div>
        <div class="flex justify-end gap-3 px-6 pb-6">
          <button @click="closeNotificationModal" class="rounded-full border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Close</button>
          <button @click="goToNotifications" class="rounded-full bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">See all</button>
        </div>
      </div>
    </div>

    <!-- CONTRACT HISTORY MODAL -->
    <div v-if="contractHistoryModalOpen" @click.self="closeContractHistoryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
      <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden max-h-[80vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-900">Contract Schedule History</h3>
          <button @click="closeContractHistoryModal" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6">
          <div v-if="contractHistoryLoading" class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
          </div>

          <div v-else-if="contractHistory.length === 0" class="text-center py-8 text-gray-500">
            <p class="text-sm">No contract history found</p>
          </div>

          <div v-else class="space-y-4">
            <div v-for="contract in contractHistory" :key="contract.id" class="border rounded-lg p-4 hover:bg-gray-50 transition">
              <div class="flex justify-between items-start mb-2">
                <div>
                  <p class="font-semibold text-gray-900">Contract Signing</p>
                  <p class="text-sm text-gray-600">{{ formatDate(contract.contract_date) }}</p>
                </div>
                <span :class="[
                  'text-xs px-3 py-1 rounded-full font-semibold',
                  contract.status === 'completed' && contract.result === 'signed' ? 'bg-green-100 text-green-800' : 
                  contract.status === 'completed' && contract.result === 'not_signed' ? 'bg-red-100 text-red-800' :
                  contract.status === 'scheduled' ? 'bg-yellow-100 text-yellow-800' :
                  'bg-gray-100 text-gray-800'
                ]">
                  {{ contract.status }}
                </span>
              </div>

              <div class="space-y-2 text-sm">
                <p><span class="text-gray-600">📍 Location:</span> {{ contract.location }}</p>
                <p v-if="contract.result !== 'pending'"><span class="text-gray-600">✓ Result:</span> <span :class="contract.result === 'signed' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">{{ contract.result }}</span></p>
                <p v-if="contract.notes" class="text-gray-600 mt-2">📝 {{ contract.notes }}</p>
              </div>

              <div class="text-xs text-gray-400 mt-3 pt-3 border-t">
                Updated: {{ formatDate(contract.updated_at) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t p-4 bg-gray-50 flex justify-end">
          <button @click="closeContractHistoryModal" class="rounded-full bg-blue-600 px-6 py-2 text-sm text-white hover:bg-blue-700 transition">Close</button>
        </div>
      </div>
    </div>

    <!-- INTERVIEW HISTORY MODAL -->
    <div v-if="interviewHistoryModalOpen" @click.self="closeInterviewHistoryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
      <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden max-h-[80vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-900">Interview History</h3>
          <button @click="closeInterviewHistoryModal" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6">
          <div v-if="interviewHistoryLoading" class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
          </div>

          <div v-else-if="interviewHistory.length === 0" class="text-center py-8 text-gray-500">
            <p class="text-sm">No interview history found</p>
          </div>

          <div v-else class="space-y-4">
            <div v-for="interview in interviewHistory" :key="interview.id" class="border rounded-lg p-4 hover:bg-gray-50 transition">
              <div class="flex justify-between items-start mb-2">
                <div>
                  <p class="font-semibold text-gray-900">{{ interview.job_title }}</p>
                  <p class="text-sm text-gray-600">{{ interview.employer }}</p>
                </div>
                <span :class="[
                  'text-xs px-3 py-1 rounded-full font-semibold',
                  interview.result === 'passed' ? 'bg-green-100 text-green-800' : 
                  interview.result === 'failed' ? 'bg-red-100 text-red-800' :
                  'bg-yellow-100 text-yellow-800'
                ]">
                  {{ interview.result }}
                </span>
              </div>

              <div class="space-y-2 text-sm">
                <p><span class="text-gray-600">📅 Scheduled:</span> {{ formatDate(interview.scheduled_at) }}</p>
                <p v-if="interview.meet_link"><span class="text-gray-600">🔗 Meeting:</span> <a :href="interview.meet_link" target="_blank" class="text-blue-500 hover:text-blue-700">Join Link</a></p>
                <p v-if="interview.notes" class="text-gray-600 mt-2">📝 {{ interview.notes }}</p>
              </div>

              <div class="text-xs text-gray-400 mt-3 pt-3 border-t">
                Updated: {{ formatDate(interview.updated_at) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t p-4 bg-gray-50 flex justify-end">
          <button @click="closeInterviewHistoryModal" class="rounded-full bg-blue-600 px-6 py-2 text-sm text-white hover:bg-blue-700 transition">Close</button>
        </div>
      </div>
    </div>

    </div>

              <!-- PROFILE -->

              <div class="relative profile-menu">
                <button @click.stop="toggleMenu">
    <img
      :src="$page.props.auth.user?.profile_photo_url || '/default-avatar.png'"
      class="w-10 h-10 rounded-full object-cover border hover:opacity-80"
    />
  </button>




                <div v-if="menuOpen" class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border z-50">
                  <Link href="/beneficiary/settings" class="block px-4 py-2 hover:bg-gray-100">Settings</Link>
                  <button @click="logout" class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">Logout</button>
                </div>
              </div>
            </div>
          </header>




         <!-- APPLICATION PROGRESS -->
<div class="bg-white p-6 rounded-2xl shadow-lg mb-6">
  <h2 class="font-semibold mb-4">Application Progress</h2>




  <div class="flex items-center justify-between text-xs mb-2">
    <span
      v-for="step in steps"
      :key="step"
      :class="statusClass(step)"
    >
      {{ step.replace('_',' ') }}
    </span>
  </div>




  <div class="w-full bg-gray-200 h-3 rounded-full overflow-hidden">
    <div
      class="h-full bg-blue-500 transition-all duration-500"
      :style="{ width: progressWidth }"
    ></div>
  </div>




  <p class="mt-2 text-sm text-gray-600">
    Current Status: <b>{{ applicationStatus }}</b>
  </p>
</div>




 




  </div>




 




          <!-- GRID -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- SCHEDULE -->
          <div class="bg-white p-6 rounded-2xl shadow-lg">
  <h2 class="font-semibold mb-2">Upcoming Schedule</h2>




  <!-- EMPTY -->
  <div v-if="(!interviews || interviews.length === 0) && (!exams || exams.length === 0)"
       class="text-gray-500 text-sm">
    No upcoming schedule
  </div>




  <!-- 🔵 INTERVIEWS -->
  <div v-for="i in interviews || []" :key="'interview-' + i.id" class="text-sm mb-3">
    <p class="font-medium">
      {{ i.job_title || 'Interview Schedule' }}
      <span class="text-blue-500 text-xs">(Interview)</span>
    </p>




    <p class="text-gray-600">
      {{ formatDate(i.scheduled_at) }}
    </p>




    <Link
      v-if="i.meet_link"
      :href="canJoin(i) ? i.meet_link : '#'"
      target="_blank"
      class="inline-block mt-2 px-4 py-2 rounded-full shadow"
      :class="canJoin(i)
        ? 'bg-orange-500 text-white'
        : 'bg-gray-300 text-gray-500 pointer-events-none'"
    >
      Join Google Meet
    </Link>
  </div>




  <!-- 🟢 EXAMS -->
  <div v-for="e in exams || []" :key="'exam-' + e.id" class="text-sm mb-3">
    <p class="font-medium">
      Exam Schedule
      <span class="text-xs">
  ({{ e.result || 'pending' }})
</span>
    </p>




    <p class="text-gray-600">
      {{ formatDate(e.exam_date) }}
    </p>




    <p class="text-blue-600 text-xs mt-1">
      📍 {{ e.location }}
    </p>
  </div>




</div>
        <!-- INTERVIEW STATUS -->
<div class="bg-white p-6 rounded-2xl shadow-lg">
  <div class="flex justify-between items-center mb-3">
    <h2 class="font-semibold">Interview Status</h2>
    <button
      @click="openInterviewHistoryModal"
      class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-full transition"
    >
      📋 View History
    </button>
  </div>



  <table class="w-full text-sm">
    <thead class="text-gray-500 border-b">
      <tr>
        <th class="text-left py-2">Employer</th>
        <th class="text-left py-2">Result</th>
      </tr>
    </thead>



    <tbody>
      <tr v-for="item in interviewItems" :key="item.id" class="border-b">
        <td class="py-2">{{ item.employer }}</td>
        <td class="py-2">
          <span
            :class="{
              'text-green-600': item.result === 'passed',
              'text-red-600': item.result === 'failed',
              'text-yellow-600': item.result === 'pending'
            }"
          >
            {{ item.result }}
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<div class="bg-white p-6 rounded-2xl shadow-lg mt-6">
  <div class="flex justify-between items-center mb-2">
    <h2 class="font-semibold">Contract Schedule</h2>
    <button
      @click="openContractHistoryModal"
      class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-full transition"
    >
      📋 View History
    </button>
  </div>

  <div v-if="(!contracts || contracts.length === 0)"
       class="text-gray-500 text-sm">
    No upcoming contract schedule
  </div>

  <div v-for="c in contracts || []" :key="'contract-' + c.id" class="text-sm mb-3">
    <p class="font-medium">
      Contract Signing
      <span class="text-xs">({{ c.status || 'scheduled' }})</span>
    </p>

    <p class="text-gray-600">
      {{ formatDate(c.contract_date) }}
    </p>

    <p class="text-blue-600 text-xs mt-1">
      📍 {{ c.location }}
    </p>
  </div>
</div>


<!-- ✅ MOVE THIS OUTSIDE -->
<div class="bg-white p-6 rounded-2xl shadow-lg mt-6">
  <h2 class="font-semibold mb-3">Exam Status</h2>




  <table class="w-full text-sm">
    <thead class="text-gray-500 border-b">
      <tr>
        <th class="text-left py-2">Location</th>
        <th class="text-left py-2">Result</th>
      </tr>
    </thead>




    <tbody>
      <tr v-for="item in examItems" :key="item.id" class="border-b">
        <td class="py-2">{{ item.location }}</td>
        <td class="py-2">
          <span
            :class="{
              'text-green-600': item.result === 'passed',
              'text-red-600': item.result === 'failed',
              'text-yellow-600': item.result === 'pending'
            }"
          >
            {{ item.result }}
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</div>
           








          </div>




          <!-- CHART -->
          <div class="bg-white p-6 rounded-2xl shadow-lg mb-10">
            <h2 class="font-semibold mb-3">Attendance</h2>
            <div class="h-64">
              <canvas id="attendanceChart"></canvas>
            </div>
          </div>




          <!-- DTR TABLE -->
<div class="bg-white p-6 rounded-2xl shadow-lg mb-10">
  <h2 class="font-semibold mb-3">Daily Time Record</h2>




  <table class="w-full text-sm">
    <thead class="border-b text-gray-500">
      <tr>
        <th class="text-left py-2">Date</th>
        <th class="text-left py-2">Status</th>
        <th class="text-left py-2">Time In</th>
        <th class="text-left py-2">Time Out</th>
        <th class="text-left py-2">Proof</th>
      </tr>
    </thead>




    <tbody>
      <tr v-for="d in attendance" :key="d.date" class="border-b">
        <td class="py-2">{{ formatDate(d.date) }}</td>
        <td class="py-2">
          <span :class="{
            'text-green-600': d.status === 'present',
            'text-yellow-600': d.status === 'late' || d.status === 'pending',
            'text-red-600': d.status === 'absent',
            'text-gray-500': !d.status
          }">
            {{ d.status || 'pending' }}
          </span>
        </td>
        <td class="py-2">{{ d.time_in || '—' }}</td>
        <td class="py-2">{{ d.time_out || '—' }}</td>
        <td class="py-2 text-gray-600">{{ d.notes || 'No proof attached' }}</td>
      </tr>
    </tbody>
  </table>
</div>



<!-- RECENT ACTIVITIES -->
<div class="bg-white p-6 rounded-2xl shadow-lg mb-6">
  <div class="flex justify-between items-center mb-3">
    <h2 class="font-semibold">Recent Activities</h2>
    <Link href="/beneficiary/activities" class="text-sm text-blue-600 hover:text-blue-800">
      View All Activities →
    </Link>
  </div>


  <div v-if="activities.length === 0" class="text-gray-500 text-sm">
    No recent activities
  </div>


  <div v-else class="space-y-3">
    <div v-for="activity in activities" :key="activity.date" class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
      <span class="text-lg">{{ activity.icon }}</span>
      <div class="flex-1">
        <p class="font-medium text-sm">{{ activity.title }}</p>
        <p class="text-gray-600 text-xs">{{ activity.description }}</p>
        <p class="text-gray-400 text-xs mt-1">{{ formatDate(activity.date) }}</p>
      </div>
    </div>
  </div>
</div>








<!-- COMPLETION CERTIFICATE -->
<div v-if="applicationStatus === 'completed'" class="bg-white p-6 rounded-2xl shadow-lg text-center mb-6">
  <h2 class="font-semibold mb-3">Program Completed</h2>
  <p class="text-gray-600 mb-4">
    Congratulations! You can download your SPES Completion Certificate.
  </p>
  <a
    href="/beneficiary/certificate"
    class="bg-green-600 text-white px-6 py-2 rounded-full shadow hover:bg-green-700"
  >
    Download Certificate
  </a>
</div>








        </div>
      </div>
  </template>




  <script setup>
import { ref, onMounted, nextTick, onBeforeUnmount, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
import { usePage } from '@inertiajs/vue3'




Chart.register(...registerables)




/* =============================
STATE
============================= */
const loadingDocs = ref(true)
const isOpen = ref(true)
const openDropdown = ref(null)
const collapsedHover = ref(null)
const menuOpen = ref(false)
const notifOpen = ref(false)
const notifFilter = ref('all')




const page = usePage()
const user = ref(page.props.auth.user)
const interviews = ref([])
const contracts = ref([])
const attendance = ref([])
const documents = ref([])
const notifications = ref([])
const exams = ref([])
const activities = ref([])
const interviewItems = computed(() =>
  interviews.value.map(i => ({
    id: i.id,
    employer: i.employer || i.company || 'Unknown',
    result: i.result || 'pending',
    type: 'Interview'
  }))
)




 const examItems = computed(() =>
  exams.value.map(e => ({
    id: e.id,
    location: e.location || 'Not specified',
    result: e.result || 'pending',
    type: 'Exam'
  }))
)








const contractItems = computed(() =>
  contracts.value.map(c => ({
    id: c.id,
    contract_date: c.contract_date,
    location: c.location || 'TBA',
    status: c.status || 'scheduled',
    result: c.result || 'pending'
  }))
)

const filteredNotifications = computed(() => {
  if (notifFilter.value === 'unread') {
    return notifications.value.filter(n => !n.read)
  }
  return notifications.value
})

const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.read).length
})

const notificationModalOpen = ref(false)
const activeNotification = ref(null)

const contractHistoryModalOpen = ref(false)
const contractHistory = ref([])
const contractHistoryLoading = ref(false)

const interviewHistoryModalOpen = ref(false)
const interviewHistory = ref([])
const interviewHistoryLoading = ref(false)

const assignedEmployer = ref(null)

const evaluation = ref([])
const workHistory = ref([])




const applicationStatus = ref('')




let attendanceChart = null




function canJoin(interview) {
  const now = new Date()
  const sched = new Date(interview.scheduled_at)




  const diff = (sched - now) / 60000 // minutes




  return diff <= 10 && diff >= -60
}








/* =============================
APPLICATION PROGRESS STEPS
============================= */




const steps = [
  'applied',
  'screening',
  'qualified',
  'exam',
  'interview',
  'for_approval',
  'approved',
  'rejected'
]




const progressWidth = computed(() => {
  const index = steps.indexOf(applicationStatus.value)
  if (index === -1) return '0%'
  return ((index + 1) / steps.length * 100) + '%'
})








function statusClass(step) {




  const current = applicationStatus.value
  const stepIndex = steps.indexOf(step)
  const currentIndex = steps.indexOf(current)




  if (current === 'rejected') {
    return 'text-red-600 font-semibold'
  }




  if (stepIndex < currentIndex) {
    return 'text-green-600 font-semibold'
  }




  if (stepIndex === currentIndex) {
    return 'text-blue-600 font-bold'
  }




  return 'text-gray-400'
}








/* =============================
SIDEBAR
============================= */




const toggleSidebar = () => {
  isOpen.value = !isOpen.value
}




const toggleDropdown = (name) => {
  openDropdown.value =
    openDropdown.value === name ? null : name
}








/* =============================
PROFILE MENU
============================= */




const toggleMenu = () => {
  menuOpen.value = !menuOpen.value
}




function toggleNotif() {
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

function markAsRead(notificationId) {
  const notification = notifications.value.find(n => n.id === notificationId)
  if (notification) {
    notification.read = true
  }
}

function closeNotificationModal() {
  notificationModalOpen.value = false
  activeNotification.value = null
}

function goToNotifications() {
  notifOpen.value = false
  router.visit('/beneficiary/notifications')
}








/* =============================
LOGOUT
============================= */




async function logout() {
  try {




    await axios.post('/logout')




    window.location.href = '/login'




  } catch (err) {




    console.error('Logout failed', err)




  }
}








/* =============================
UPLOAD PAGE
============================= */




function openUpload() {




  router.visit('/beneficiary/upload')




}








/* =============================
FORMAT DATE
============================= */
function formatDate(v) {
  if (!v) return ''
  const d = new Date(v)
  if (isNaN(d)) return v




  return d.toLocaleString('en-PH', {
    dateStyle: 'medium',
    timeStyle: 'short',
    timeZone: 'Asia/Manila'
  })
}








/* =============================
LOAD PROFILE DATA
ONE API CALL
============================= */




async function loadProfileData() {




  try {




    const res = await axios.get('/api/beneficiary/profile')
    console.log(res.data)




    user.value = res.data.user || { name: 'User' }




    assignedEmployer.value = res.data.employer || null




    evaluation.value = res.data.evaluation || []




    workHistory.value = res.data.work_history || []




  const statusRes = await axios.get('/api/beneficiary/application-status')
applicationStatus.value = statusRes.data.status




  } catch (err) {




    console.error('Failed to load profile', err)




  }




}








/* =============================
DOCUMENTS
============================= */




async function loadDocuments() {
  try {
    loadingDocs.value = true




    const res = await axios.get('/api/beneficiary/dashboard-stats')




    documents.value = res.data.documents || []




  } catch (err) {
    console.error('Failed to load documents', err)
    documents.value = []
  } finally {
    loadingDocs.value = false
  }
}








/* =============================
NOTIFICATIONS
============================= */




async function loadNotifications() {




  try {




    const res = await axios.get('/api/beneficiary/notifications')




    notifications.value = res.data || []




  } catch (err) {




    console.error('Notifications failed', err)




    notifications.value = []




  }




}








/* =============================
INTERVIEWS
============================= */




async function loadInterviews() {




  try {




    const res = await axios.get('/api/beneficiary/interviews')




    interviews.value = res.data || []




  } catch (err) {




    console.error('Interview load failed', err)




    interviews.value = []




  }




}
async function loadExams() {
  try {
    const res = await axios.get('/api/beneficiary/exams')
    exams.value = res.data || []
  } catch (err) {
    console.error('Failed to load exams', err)
    exams.value = []
  }
}

async function loadContracts() {
  try {
    console.log('Loading contracts...')
    const res = await axios.get('/api/beneficiary/contracts')
    console.log('Contracts response:', res.data)
    contracts.value = res.data || []
  } catch (err) {
    console.error('Failed to load contract schedules', err)
    contracts.value = []
  }
}

async function loadContractHistory() {
  try {
    contractHistoryLoading.value = true
    const res = await axios.get('/api/beneficiary/contracts/history')
    console.log('Contract history response:', res.data)
    contractHistory.value = res.data || []
  } catch (err) {
    console.error('Failed to load contract history', err)
    contractHistory.value = []
  } finally {
    contractHistoryLoading.value = false
  }
}

function openContractHistoryModal() {
  loadContractHistory()
  contractHistoryModalOpen.value = true
}

function closeContractHistoryModal() {
  contractHistoryModalOpen.value = false
}

async function loadInterviewHistory() {
  try {
    interviewHistoryLoading.value = true
    const res = await axios.get('/api/beneficiary/interviews/history')
    console.log('Interview history response:', res.data)
    interviewHistory.value = res.data || []
  } catch (err) {
    console.error('Failed to load interview history', err)
    interviewHistory.value = []
  } finally {
    interviewHistoryLoading.value = false
  }
}

function openInterviewHistoryModal() {
  loadInterviewHistory()
  interviewHistoryModalOpen.value = true
}

function closeInterviewHistoryModal() {
  interviewHistoryModalOpen.value = false
}








/* =============================
ATTENDANCE
============================= */




async function loadAttendance() {




  try {




    const res = await axios.get('/api/beneficiary/attendance')




    attendance.value = res.data || []




    nextTick(() => {
  setTimeout(renderAttendanceChart, 100)
})




  } catch (err) {




    console.error('Attendance load failed', err)




    attendance.value = []




  }




}








/* =============================
RECENT ACTIVITIES
============================= */




async function loadActivities() {




  try {




    const res = await axios.get('/api/beneficiary/recent-activities')




    activities.value = res.data || []




  } catch (err) {




    console.error('Activities load failed', err)




    activities.value = []




  }




}








/* =============================
ATTENDANCE CHART
============================= */




function renderAttendanceChart() {




  const canvas = document.getElementById('attendanceChart')




  if (!canvas) return




  if (attendanceChart) attendanceChart.destroy()




  attendanceChart = new Chart(canvas, {




    type: 'line',




    data: {




      labels: attendance.value.map(r => r.date),




      datasets: [
        {
          label: 'Hours Worked',




          data: attendance.value.map(r => r.hours || 0),




          fill: true,




          backgroundColor: 'rgba(59,130,246,0.2)',




          borderColor: '#3b82f6'
        }
      ]
    },




    options: {




      responsive: true,




      maintainAspectRatio: false




    }




  })




}








/* =============================
CLICK OUTSIDE
============================= */




function handleClick(e) {




  if (!e.target.closest('.profile-menu')) {




    menuOpen.value = false




  }




  if (!e.target.closest('.notif-menu')) {




    notifOpen.value = false




  }




}








/* =============================
MOUNT
============================= */




onMounted(() => {




  loadExams()




  loadProfileData()




  loadDocuments()




  loadNotifications()




  loadInterviews()
  loadContracts()




  loadAttendance()




  loadActivities()




  document.addEventListener('click', handleClick)




})








onBeforeUnmount(() => {
  document.removeEventListener('click', handleClick)




  if (attendanceChart) {
    attendanceChart.destroy()
  }
})








/* =============================
SIDEBAR MENUS
============================= */




const menus = [




  {
    name:'profile',
    label:'Profile Management',
    icon:'👤',
    children:[
     
      {label:'Edit Info',href:'/beneficiary/settings'}
    ]
  },




  {
    name:'application',
    label:'Application Management',
    icon:'💼',
    children:[
      {label:'My Applications',href:'/beneficiary/applications'}
    ]
  },




 {
  name:'jobs',
  label:'Job Vacancies',
  icon:'🧳',
  children:[
    { label:'View Jobs', href:'/beneficiary/jobs' }
  ]
},








  {
    name:'exam',
    label:'Exam & Qualification',
    icon:'📢',
    children:[
      {label:'Exam Notifications',href:'/beneficiary/exams'}
    ]
  },




  {
    name:'interview',
    label:'Interview Management',
    icon:'🎤',
    children:[
      {label:'Schedule',href:'/beneficiary/interviews'}
    ]
  },




  {
    name:'attendance',
    label:'Attendance & Work',
    icon:'🕒',
    children:[
      {label:'Submit DTR',href:'/beneficiary/attendance'}
    ]
  },
  {
  name:'ratings',
  label:'Beneficiary Ratings',
  icon:'⭐',
  children:[
    {label:'Ratings History', href:'/beneficiary/ratings/history'},
    {label:'Activity History', href:'/beneficiary/activities'}
  ]
},
  {
    name:'notifications',
    label:'Notifications',
    icon:'🔔',
    children:[
      {label:'Announcements',href:'/beneficiary/notifications'}
    ]
  }




]




</script>



































