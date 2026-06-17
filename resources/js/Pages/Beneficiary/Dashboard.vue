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
          <button @click="toggleSidebar"
          class="w-10 h-10 flex items-center justify-center text-xl rounded hover:bg-blue-800 transition"
          aria-label="Toggle Sidebar">
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
        <nav class="flex-1 overflow-y-auto text-sm space-y-1 pr-2">
          <div v-for="menu in isApproved ? menus : pendingMenus" :key="menu.name" class="relative">
            <div
              class="flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-all duration-200 hover:bg-white/10"
              :class="currentRoute === menu.href ? 'bg-white/15 text-white font-semibold' : 'text-gray-300'"
              @click="menu.children
  ? toggleDropdown(menu.name)
  : menu.href && router.visit(menu.href)"
              @mouseenter="!isOpen && (collapsedHover = menu.name)"
              @mouseleave="!isOpen && (collapsedHover = null)"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path v-if="menu.icon === 'dashboard'" stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                <path v-else-if="menu.icon === 'application'" stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                <path v-else-if="menu.icon === 'requirements'" stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                <path v-else-if="menu.icon === 'job'" stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.193 23.193 0 0112 15c-3.183 0-6.22-.64-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                <path v-else-if="menu.icon === 'schedule'" stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                <path v-else-if="menu.icon === 'attendance'" stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                <path v-else-if="menu.icon === 'messages'" stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                <path v-else-if="menu.icon === 'profile'" stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              <span v-if="isOpen" class="truncate">{{ menu.label }}</span>
              <span v-if="menu.children && isOpen" class="ml-auto text-xs opacity-60">▾</span>
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
              <span class="text-lg"></span>
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
              <!--  NOTIFICATION -->
              <div class="relative notif-menu">
                <button
                  @click.stop="toggleNotif"
                  title="Notifications"
                  class="relative w-10 h-10 rounded-full bg-white border border-gray-200 shadow-sm hover:bg-blue-50 hover:border-blue-300 flex items-center justify-center transition"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                  <span
                    v-if="unreadCount > 0"
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] min-w-[18px] h-[18px] flex items-center justify-center rounded-full font-bold shadow-sm"
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

    <div v-if="notificationModalOpen" @click.self="closeNotificationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 px-4 py-8">
      <div class="relative w-full max-w-4xl mx-auto bg-white rounded-lg shadow-2xl overflow-hidden max-h-[90vh]">
        <button @click="closeNotificationModal" class="absolute top-4 right-4 z-20 rounded-full bg-white/90 p-2 text-gray-700 shadow hover:bg-white transition">
          
        </button>

        <div class="p-6 space-y-4 overflow-auto max-h-[90vh]">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ activeNotification?.title }}</h3>
            <p class="text-xs text-gray-500">{{ formatDate(activeNotification?.created_at) }}</p>
          </div>

          <p class="text-sm text-gray-700">{{ activeNotification?.content }}</p>

          <div v-if="activeNotification?.image" class="relative w-full h-[60vh] max-h-[70vh] rounded-lg overflow-hidden bg-gray-100">
            <img
              :src="`/storage/${activeNotification.image}`"
              class="h-full w-full object-contain"
              alt="Notification Image"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- CONTRACT HISTORY MODAL -->
    <div v-if="contractHistoryModalOpen" @click.self="closeContractHistoryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
      <div class="w-full max-w-2xl bg-white rounded-lg shadow-2xl overflow-hidden max-h-[80vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-900">Contract Schedule History</h3>
          <button @click="closeContractHistoryModal" class="text-gray-500 hover:text-gray-700"></button>
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
                <p><span class="text-gray-600"> Location:</span> {{ contract.location }}</p>
                <p v-if="contract.result !== 'pending'"><span class="text-gray-600"> Result:</span> <span :class="contract.result === 'signed' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">{{ contract.result }}</span></p>
                <p v-if="contract.notes" class="text-gray-600 mt-2"> {{ contract.notes }}</p>
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
      <div class="w-full max-w-2xl bg-white rounded-lg shadow-2xl overflow-hidden max-h-[80vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-900">Interview History</h3>
          <button @click="closeInterviewHistoryModal" class="text-gray-500 hover:text-gray-700"></button>
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
                <p><span class="text-gray-600"> Scheduled:</span> {{ formatDate(interview.scheduled_at) }}</p>
                <p v-if="interview.meet_link"><span class="text-gray-600"> Meeting:</span> <a :href="interview.meet_link" target="_blank" class="text-blue-500 hover:text-blue-700">Join Link</a></p>
                <p v-if="interview.notes" class="text-gray-600 mt-2"> {{ interview.notes }}</p>
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

          <section class="space-y-6">
            <!-- STATUS HERO -->
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
              <div class="grid gap-6 p-6 lg:grid-cols-[1.4fr_0.6fr] lg:p-8">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">
                    Current SPES Status
                  </p>
                  <h2 class="mt-3 text-3xl font-bold text-slate-900">
                    {{ statusLabel }}
                  </h2>
                  <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                    {{ statusDescription }}
                  </p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                  <p class="text-sm font-semibold text-slate-700">Application progress</p>
                  <div class="mt-4 flex items-end gap-2">
                    <span class="text-4xl font-bold text-blue-700">{{ progressPercent }}%</span>
                    <span class="pb-1 text-sm text-slate-500">complete</span>
                  </div>
                  <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-200">
                    <div
                      class="h-full rounded-full bg-blue-600 transition-all duration-500"
                      :style="{ width: `${progressPercent}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- NEXT STEP -->
            <div class="rounded-lg border border-blue-100 bg-blue-50 p-5 shadow-sm sm:p-6">
              <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-700">
                    What to do next
                  </p>
                  <h3 class="mt-2 text-xl font-bold text-slate-900">
                    {{ nextStep.title }}
                  </h3>
                  <p class="mt-2 text-sm leading-6 text-slate-600">
                    {{ nextStep.description }}
                  </p>
                </div>
                <Link
                  :href="nextStep.href"
                  class="inline-flex shrink-0 items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
                >
                  {{ nextStep.action }}
                </Link>
              </div>
            </div>

            <!-- TIMELINE -->
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
              <div class="mb-5 flex flex-col gap-1">
                <h3 class="text-lg font-bold text-slate-900">Application timeline</h3>
                <p class="text-sm text-slate-500">Follow your SPES application from submission to completion.</p>
              </div>

              <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div
                  v-for="item in timelineItems"
                  :key="item.key"
                  class="rounded-xl border p-4"
                  :class="item.done ? 'border-green-200 bg-green-50' : item.current ? 'border-blue-200 bg-blue-50' : 'border-slate-200 bg-slate-50'"
                >
                  <div
                    class="mb-3 flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold"
                    :class="item.done ? 'bg-green-600 text-white' : item.current ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500'"
                  >
                    {{ item.done ? '' : item.order }}
                  </div>
                  <p class="font-semibold text-slate-900">{{ item.label }}</p>
                  <p class="mt-1 text-xs leading-5 text-slate-500">{{ item.description }}</p>
                </div>
              </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
              <!-- REQUIREMENTS -->
              <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5 flex items-start justify-between gap-4">
                  <div>
                    <h3 class="text-lg font-bold text-slate-900">Requirements checklist</h3>
                    <p class="mt-1 text-sm text-slate-500">
                      {{ missingRequirements.length ? `${missingRequirements.length} item(s) still need attention.` : 'All listed requirements look submitted.' }}
                    </p>
                  </div>
                  <Link href="/beneficiary/applications" class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                    View application
                  </Link>
                </div>

                <div class="space-y-3">
                  <div
                    v-for="requirement in requirementItems"
                    :key="requirement.key"
                    class="flex items-start justify-between gap-4 rounded-xl border border-slate-200 p-4"
                  >
                    <div>
                      <p class="font-semibold text-slate-900">{{ requirement.name }}</p>
                      <p class="mt-1 text-xs text-slate-500">{{ requirement.note }}</p>
                    </div>
                    <span
                      class="rounded-full px-3 py-1 text-xs font-semibold"
                      :class="requirement.complete ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
                    >
                      {{ requirement.complete ? 'Submitted' : 'Missing' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- SCHEDULE -->
              <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5 flex items-start justify-between gap-4">
                  <div>
                    <h3 class="text-lg font-bold text-slate-900">Upcoming schedule</h3>
                    <p class="mt-1 text-sm text-slate-500">Exams, interviews, and contract signing dates.</p>
                  </div>
                  <Link href="/beneficiary/interviews" class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                    View
                  </Link>
                </div>

                <div v-if="upcomingScheduleItems.length" class="space-y-3">
                  <div
                    v-for="item in upcomingScheduleItems"
                    :key="item.id"
                    class="rounded-xl border border-slate-200 p-4"
                  >
                    <div class="flex items-start justify-between gap-3">
                      <div>
                        <p class="font-semibold text-slate-900">{{ item.title }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ item.when }}</p>
                        <p v-if="item.meta" class="mt-1 text-xs text-slate-500">{{ item.meta }}</p>
                      </div>
                      <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        {{ item.type }}
                      </span>
                    </div>
                  </div>
                </div>

                <div v-else class="rounded-xl border border-dashed border-slate-300 p-6 text-center">
                  <p class="font-semibold text-slate-700">No upcoming schedule yet</p>
                  <p class="mt-1 text-sm text-slate-500">New schedules will appear here when CPESO posts them.</p>
                </div>
              </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[0.9fr_1fr]">
              <!-- ASSIGNED JOB -->
              <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h3 class="text-lg font-bold text-slate-900">Assigned job / employer</h3>
                <div v-if="assignedEmployer" class="mt-5 rounded-xl border border-green-200 bg-green-50 p-4">
                  <p class="text-xs font-semibold uppercase tracking-[0.18em] text-green-700">Assigned</p>
                  <p class="mt-2 text-xl font-bold text-slate-900">{{ assignedEmployer.company }}</p>
                  <p class="mt-1 text-sm text-slate-600">{{ assignedEmployer.job_title || 'Assigned position' }}</p>
                  <Link href="/beneficiary/jobs" class="mt-4 inline-flex text-sm font-semibold text-green-700 hover:text-green-800">
                    View placement details
                  </Link>
                </div>
                <div v-else class="mt-5 rounded-xl border border-dashed border-slate-300 p-6 text-center">
                  <p class="font-semibold text-slate-700">No assigned employer yet</p>
                  <p class="mt-1 text-sm text-slate-500">Your placement will appear after CPESO assigns you to an employer.</p>
                </div>
              </div>

              <!-- RECENT MESSAGES -->
              <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5 flex items-start justify-between gap-4">
                  <div>
                    <h3 class="text-lg font-bold text-slate-900">Recent Announcements</h3>
                    <p class="mt-1 text-sm text-slate-500">Only the latest notices and status updates are shown here.</p>
                  </div>
                  <Link href="/beneficiary/notifications" class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                    See all
                  </Link>
                </div>

                <div v-if="recentMessages.length" class="space-y-3">
                  <button
                    v-for="message in recentMessages"
                    :key="message.id"
                    type="button"
                    @click="openNotification(message)"
                    class="w-full rounded-xl border border-slate-200 p-4 text-left transition hover:border-blue-200 hover:bg-blue-50"
                  >
                    <div class="flex items-start justify-between gap-3">
                      <div>
                        <p class="font-semibold text-slate-900">{{ message.title }}</p>
                        <p class="mt-1 line-clamp-2 text-sm text-slate-600">{{ message.content }}</p>
                        <p class="mt-2 text-xs text-slate-400">{{ formatDate(message.created_at) }}</p>
                      </div>
                      <span v-if="!message.read" class="mt-1 h-2 w-2 rounded-full bg-blue-600"></span>
                    </div>
                  </button>
                </div>

                <div v-else class="rounded-xl border border-dashed border-slate-300 p-6 text-center">
                  <p class="font-semibold text-slate-700">No messages yet</p>
                  <p class="mt-1 text-sm text-slate-500">Announcements and reminders from CPESO will appear here.</p>
                </div>
              </div>
            </div>

            <div
              v-if="isApproved && applicationStatus === 'completed'"
              class="rounded-lg border border-green-200 bg-green-50 p-5 text-center shadow-sm sm:p-6"
            >
              <h3 class="text-lg font-bold text-slate-900">Program completed</h3>
              <p class="mt-2 text-sm text-slate-600">
                Congratulations. Your SPES completion certificate is ready when available.
              </p>
              <a
                href="/beneficiary/certificate"
                class="mt-4 inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700"
              >
                Download certificate
              </a>
            </div>
          </section>

          <div v-if="false">
          <!-- ONBOARDING SUMMARY -->
          <section v-if="!isApproved" class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
              <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-blue-600">
                  Onboarding
                </p>
                <h2 class="text-2xl font-bold text-gray-800 mt-2">
                  Complete your application profile
                </h2>
                <p class="text-sm text-gray-600 mt-2">
                  Track your progress and finish the application requirements to unlock your full dashboard.
                </p>
              </div>

              <div class="rounded-lg bg-blue-50 px-5 py-4 min-w-[180px]">
                <p class="text-sm text-blue-700 font-semibold">Progress</p>
                <p class="text-3xl font-bold text-blue-900 mt-2">{{ onboardingProgress }}%</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                v-for="step in onboardingSteps"
                :key="step.label"
                class="flex items-start gap-3 rounded-lg border border-gray-100 p-4 bg-gray-50"
              >
                <div
                  class="flex h-8 w-8 items-center justify-center rounded-full"
                  :class="step.done ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500'"
                >
                  {{ step.done ? '' : '' }}
                </div>

                <div>
                  <p class="font-semibold text-gray-800">{{ step.label }}</p>
                  <p class="text-sm text-gray-500 mt-1">{{ step.description }}</p>
                </div>
              </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
              <Link
                href="/onboarding"
                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white shadow hover:bg-blue-700 transition"
              >
                Submit Application
              </Link>

              <span class="inline-flex items-center rounded-xl bg-gray-100 px-4 py-3 text-sm text-gray-700">
                {{ onboardingStatusLabel }}
              </span>
            </div>
          </section>

         <!-- APPLICATION PROGRESS -->
<div class="bg-white p-6 rounded-lg shadow-lg mb-6">
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

  
</div>


  </div>

          <!-- GRID -->
          <div v-if="isApproved" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- SCHEDULE -->
       




  
  










   
</div>





           








          </div>




          



        









          </div>

<!-- COMPLETION CERTIFICATE -->
<div v-if="false && isApproved && applicationStatus === 'completed'" class="bg-white p-6 rounded-lg shadow-lg text-center mb-6">
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
const beneficiaryProfile = ref(page.props.beneficiary || {})
const approvalStatus = computed(() => beneficiaryProfile.value?.approval_status || 'pending')
const isApproved = computed(() => approvalStatus.value === 'approved')
const currentRoute = computed(() => window.location.pathname)
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
  'exam',
  'exam_passed',
  'interview',
  'interview_passed',
  'qualified',
  'approved',
  'assigned',
  'contract_signing',
  'contract_signed',
  'deployed',
  'ongoing',
  'completion_review',
  'completed'
]




const submissionCompleted = computed(() => Boolean(
  beneficiaryProfile.value?.screening_at ||
  beneficiaryProfile.value?.completion_percentage >= 100 ||
  beneficiaryProfile.value?.onboarding_step >= 5 ||
  beneficiaryProfile.value?.draft_status === 'screening'
))

const displayApplicationStatus = computed(() => {
  return applicationStatus.value || 'applied'
})

const progressWidth = computed(() => {


  const workflowSteps = steps.filter((step) => step !== 'rejected')
  const index = workflowSteps.indexOf(normalizeWorkflowStatus(applicationStatus.value))
  if (index === -1) return '0%'
  return ((index + 1) / workflowSteps.length * 100) + '%'
})








const onboardingSteps = computed(() => [
  {
    label: 'Profile Information',
    done: submissionCompleted.value || Boolean(user.value?.name && user.value?.email),
    description: 'Your name, email, and account details are saved.'
  },
  {
    label: 'Personal Details',
    done: submissionCompleted.value || Boolean(beneficiaryProfile.value?.phone || beneficiaryProfile.value?.school_id || beneficiaryProfile.value?.skills || beneficiaryProfile.value?.parent_name),
    description: 'Complete your personal profile and category-specific details.'
  },
  {
    label: 'Required Documents',
    done: submissionCompleted.value || documents.value.length > 0,
    description: 'Upload the documents required for your application.'
  },
  {
    label: 'Review Applications',
    done: submissionCompleted.value || Boolean(applicationStatus.value && applicationStatus.value !== 'not_started' && applicationStatus.value !== 'applied'),
    description: 'Review your application information and confirm your submission.'
  },
  {
    label: 'Submit Application',
    done: submissionCompleted.value || Boolean(applicationStatus.value && applicationStatus.value !== 'not_started' && applicationStatus.value !== 'applied'),
    description: 'Submit your application and wait for review.'
  }
])

const completedOnboardingSteps = computed(() => onboardingSteps.value.filter(step => step.done).length)
const onboardingProgress = computed(() => Math.round((completedOnboardingSteps.value / onboardingSteps.value.length) * 100))
const onboardingStatusLabel = computed(() => {
  if (completedOnboardingSteps.value === onboardingSteps.value.length) {
    return 'All onboarding steps are completed. Your application is ready for review.'
  }

  return 'Complete the pending steps to finish your application and unlock the full dashboard.'
})
function statusClass(step) {
  const current = normalizeWorkflowStatus(applicationStatus.value)

  if (current === 'rejected') {
    return step === 'rejected'
      ? 'text-red-600 font-bold'
      : 'text-gray-400'
  }

  const stepIndex = steps.indexOf(step)
  const currentIndex = steps.indexOf(current)

  if (stepIndex < currentIndex) {
    return 'text-green-600 font-semibold'
  }

  if (stepIndex === currentIndex) {
    return 'text-blue-600 font-bold'
  }

  return 'text-gray-400'
}

const currentStatus = computed(() => {
  return normalizeWorkflowStatus(applicationStatus.value || 'applied')
})

function normalizeWorkflowStatus(status) {
  const value = String(status || 'applied').toLowerCase()

  if (['pending', 'submitted', 'under_review', 'review'].includes(value)) return 'screening'
  if (['for_exam', 'exam_scheduled'].includes(value)) return 'exam'
  if (value === 'exam_passed') return 'exam_passed'
  if (['for_interview', 'scheduled', 'interview_scheduled'].includes(value)) return 'interview'
  if (value === 'interview_passed') return 'interview_passed'
  if (['for_approval', 'passed', 'qualified'].includes(value)) return 'qualified'
  if (value === 'selected') return 'approved'
  if (value === 'placed') return 'assigned'
  if (['contract', 'for_contract', 'contract_signing', 'contract-signing'].includes(value)) return 'contract_signing'
  if (['contract_signed', 'signed'].includes(value)) return 'contract_signed'

  return value
}

const statusLabel = computed(() => {
  const labels = {
    not_started: 'Application not started',
    applied: 'Application submitted',
    screening: 'Under CPESO screening',
    needs_correction: 'Needs Correction',
    exam: 'Exam stage',
    exam_passed: 'Exam passed',
    interview: 'Interview stage',
    interview_passed: 'Interview passed',
    qualified: 'Qualified',
    for_approval: 'For final approval',
    approved: 'Approved',
    assigned: 'Assigned to employer',
    contract_signing: 'Contract signing',
    contract_signed: 'Contract signed',
    deployed: 'Work deployment',
    ongoing: 'Currently employed under SPES',
    completion_review: 'For completion review',
    completed: 'Program completed',
    rejected: 'Needs correction or resubmission',
    pending: 'Pending review'
  }

  // Show different label for 'applied' depending on onboarding state
  if (currentStatus.value === 'applied') {
    const onboardingDone = beneficiaryProfile.value?.onboarding_completed_at || beneficiaryProfile.value?.draft_status === 'submitted'
    return onboardingDone ? 'Application submitted' : 'Application not yet completed'
  }

  return labels[currentStatus.value] || 'Application in progress'
})

const statusDescription = computed(() => {
  if (currentStatus.value === 'rejected' || currentStatus.value === 'needs_correction') {
    return beneficiaryProfile.value?.rejection_reason || 'CPESO requested corrections to your submitted requirements. Please review and update the required documents.'
  }

  if (currentStatus.value === 'completed') {
    return 'You have completed the SPES process. Keep your records and certificate for future reference.'
  }

  if (['deployed', 'ongoing', 'completion_review'].includes(currentStatus.value)) {
    return 'You have an assigned employer. Keep track of your schedule and submit your attendance on time.'
  }

  if (['approved', 'assigned', 'for_contract', 'contract_signed'].includes(currentStatus.value)) {
    return 'Your application is approved. Watch this page for placement, schedule, and attendance updates.'
  }

  // For 'applied' status, differentiate based on onboarding completion
  if (currentStatus.value === 'applied') {
    const onboardingDone = beneficiaryProfile.value?.onboarding_completed_at || beneficiaryProfile.value?.draft_status === 'submitted'
    if (onboardingDone) {
      return 'Your SPES application has been submitted. Please wait for CPESO screening.'
    }
    return 'Your SPES application has not been completed yet. Please finish the onboarding process and submit all required information.'
  }

  return 'Your application is being prepared or reviewed. Check your status and follow CPESO updates.'
})

const progressPercent = computed(() => {
  if (currentStatus.value === 'completed') return 100
  if (currentStatus.value === 'rejected') return 0

  const workflowSteps = steps.filter((step) => step !== 'rejected')
  const index = workflowSteps.indexOf(currentStatus.value)
  if (index === -1) return 0
  return Math.round(((index + 1) / workflowSteps.length) * 100)
})

const requiredDocumentNames = computed(() => {
  const category = beneficiaryProfile.value?.category || user.value?.beneficiary_type || 'student'

  if (category === 'student') {
    return [
      { key: 'valid_id', name: 'Valid ID' },
      { key: 'school_enrollment', name: 'School Enrollment / Proof of Study' },
      { key: 'barangay_certificate', name: 'Barangay Certificate' },
    ]
  }

  if (category === 'osy') {
    return [
      { key: 'valid_id', name: 'Valid ID' },
      { key: 'birth_certificate', name: 'Birth Certificate' },
      { key: 'barangay_certificate', name: 'Barangay Certificate of Residency' },
      { key: 'osy_certificate', name: 'Certificate of Out-of-School Youth Status' },
    ]
  }

  if (category === 'dependent') {
    return [
      { key: 'birth_certificate', name: 'Birth Certificate' },
      { key: 'income_proof', name: 'Proof of Family Income' },
      { key: 'displacement_proof', name: 'Proof of Displacement' },
      { key: 'parent_valid_id', name: 'Parent / Guardian Valid ID' },
    ]
  }

  // Fallback
  return [
    { key: 'valid_id', name: 'Valid ID' },
    { key: 'barangay_certificate', name: 'Barangay Certificate' },
    { key: 'school_enrollment', name: 'School Enrollment' },
  ]
})

function normalizeText(value) {
  return String(value || '').toLowerCase().replace(/[^a-z0-9]/g, '')
}

function documentIsComplete(document) {
  if (!document) return false
  const status = String(document.status || '').toLowerCase()
  return Boolean(
    status === 'uploaded' ||
    status === 'submitted' ||
    status === 'approved' ||
    document.path ||
    document.url
  )
}

const requirementItems = computed(() => {
  const docs = Array.isArray(documents.value) ? documents.value : []

  return requiredDocumentNames.value.map((required) => {
    const match = docs.find((doc) => {
      const docName = normalizeText(doc.name || doc.label || doc.type || doc.key || '')
      return docName.includes(normalizeText(required.key)) || docName.includes(normalizeText(required.name))
    })

    const complete = documentIsComplete(match)

    return {
      ...required,
      complete,
      note: complete
        ? 'Submitted and ready for review.'
        : 'Required for your SPES application.'
    }
  })
})

const missingRequirements = computed(() =>
  requirementItems.value.filter((requirement) => !requirement.complete)
)

const timelineItems = computed(() => {
  const items = [
    { key: 'applied', label: 'Applied', description: 'Application submitted.' },
    { key: 'screening', label: 'Screening', description: 'CPESO reviews requirements and eligibility.' },
    { key: 'exam', label: 'Exam', description: 'Beneficiary takes the scheduled examination.' },
    { key: 'exam_passed', label: 'Exam Passed', description: 'Exam result is passed and ready for interview scheduling.' },
    { key: 'interview', label: 'Interview', description: 'Beneficiary attends the scheduled interview.' },
    { key: 'interview_passed', label: 'Interview Passed', description: 'Interview result is passed and ready for qualification.' },
    { key: 'qualified', label: 'Qualified', description: 'Beneficiary passed screening, exam, and interview requirements.' },
    { key: 'approved', label: 'Approved', description: 'Officially approved for SPES participation.' },
    { key: 'assigned', label: 'Assigned', description: 'Assigned to employer/job placement.' },
    { key: 'contract_signing', label: 'Contract Signing', description: 'Contract signing is scheduled with employer/CPESO.' },
    { key: 'contract_signed', label: 'Contract Signed', description: 'Contract has been signed and is ready for deployment.' },
    { key: 'deployed', label: 'Deployed', description: 'Beneficiary is deployed to the assigned work site.' },
    { key: 'ongoing', label: 'Ongoing Work', description: 'Submit DTR and daily accomplishment reports during work.' },
    { key: 'completion_review', label: 'Completion Review', description: 'Employer and CPESO validate completion records.' },
    { key: 'completed', label: 'Completed', description: 'SPES participation completed.' }
  ]

  const hasSignedContract = (contracts.value || []).some((contract) => {
    const status = String(contract.status || '').toLowerCase()
    const result = String(contract.result || '').toLowerCase()
    return status.includes('completed') || result.includes('signed')
  })

  let statusForTimeline = currentStatus.value

  if (assignedEmployer.value && ['approved', 'qualified'].includes(statusForTimeline)) {
    statusForTimeline = 'assigned'
  }

  if (hasSignedContract && !['contract_signed', 'deployed', 'ongoing', 'completion_review', 'completed'].includes(statusForTimeline)) {
    statusForTimeline = 'contract_signed'
  }

  const currentIndex = items.findIndex((item) => item.key === statusForTimeline)

  return items.map((item, index) => ({
    ...item,
    order: index + 1,
    done: currentStatus.value === 'completed' || (currentIndex !== -1 && index < currentIndex),
    current: currentIndex === index
  }))
})

function scheduleTimestamp(value) {
  const date = value ? new Date(value) : null
  return date && !Number.isNaN(date.getTime()) ? date.getTime() : Number.MAX_SAFE_INTEGER
}

const upcomingScheduleItems = computed(() => {
  const interviewItems = (interviews.value || []).map((item) => ({
    id: `interview-${item.id}`,
    type: 'Interview',
    title: item.job_title || 'Interview schedule',
    when: formatDate(item.scheduled_at),
    meta: item.employer || item.company || item.meet_link || '',
    sortDate: scheduleTimestamp(item.scheduled_at)
  }))

  const examScheduleItems = (exams.value || []).map((item) => ({
    id: `exam-${item.id}`,
    type: 'Exam',
    title: 'Exam schedule',
    when: formatDate(item.exam_date),
    meta: item.location || item.result || '',
    sortDate: scheduleTimestamp(item.exam_date)
  }))

  const contractItemsForSchedule = (contracts.value || []).map((item) => ({
    id: `contract-${item.id}`,
    type: 'Contract',
    title: 'Contract signing',
    when: formatDate(item.contract_date),
    meta: item.location || item.status || '',
    sortDate: scheduleTimestamp(item.contract_date)
  }))

  return [...interviewItems, ...examScheduleItems, ...contractItemsForSchedule]
    .sort((a, b) => a.sortDate - b.sortDate)
    .slice(0, 4)
})

const recentMessages = computed(() => {
  return [...(notifications.value || [])]
    .sort((a, b) => scheduleTimestamp(b.created_at) - scheduleTimestamp(a.created_at))
    .slice(0, 3)
})

const nextStep = computed(() => {
  const status = currentStatus.value

  if (status === 'completed') {
    return {
      title: 'View your completion record',
      description: 'Your SPES participation is completed. You may view your certificate or completion record.',
      action: 'View certificate',
      href: '/beneficiary/certificate'
    }
  }

  if (status === 'needs_correction' || status === 'rejected' || approvalStatus.value === 'rejected') {
    return {
      title: 'Correct and resubmit requirements',
      description: beneficiaryProfile.value?.rejection_reason || 'CPESO requested corrections. Review and update your submitted documents.',
      action: 'View corrections',
      href: '/beneficiary/applications'
    }
  }

  if (status === 'completion_review') {
    return {
      title: 'Wait for completion approval',
      description: 'Your completion records are under CPESO review.',
      action: 'View application',
      href: '/beneficiary/applications'
    }
  }

  if (status === 'ongoing') {
    return {
      title: 'Submit DTR and daily reports',
      description: 'Continue submitting your attendance and accomplishment reports during your work period.',
      action: 'Open attendance',
      href: '/beneficiary/attendance'
    }
  }

  if (status === 'deployed') {
    return {
      title: 'Start submitting your DTR',
      description: 'You are deployed. Begin recording your attendance and daily reports.',
      action: 'Open attendance',
      href: '/beneficiary/attendance'
    }
  }

  if (status === 'contract_signed') {
    return {
      title: 'Prepare for deployment',
      description: 'Your contract has been signed. Please wait for deployment instructions.',
      action: 'View placement',
      href: '/beneficiary/jobs'
    }
  }

  if (status === 'contract_signing' || status === 'for_contract') {
    return {
      title: 'Attend contract signing',
      description: 'Your contract signing has been scheduled. Review the schedule details.',
      action: 'View schedule',
      href: '/beneficiary/interviews'
    }
  }

  if (status === 'assigned') {
    return {
      title: 'Review your job placement',
      description: 'You have been assigned to an employer/job placement. Review your placement details.',
      action: 'View placement',
      href: '/beneficiary/jobs'
    }
  }

  if (status === 'approved') {
    return {
      title: 'Wait for job placement updates',
      description: 'Your application is approved. CPESO will post assignment or schedule updates here.',
      action: 'View jobs',
      href: '/beneficiary/jobs'
    }
  }

  if (status === 'qualified') {
    return {
      title: 'Wait for final approval',
      description: 'You are qualified for SPES. Please wait for CPESO final approval.',
      action: 'View application',
      href: '/beneficiary/applications'
    }
  }

  if (status === 'interview_passed') {
    return {
      title: 'Wait for qualification review',
      description: 'You passed the interview. CPESO will review your qualification status.',
      action: 'View application',
      href: '/beneficiary/applications'
    }
  }

  if (status === 'interview' || status === 'for_interview') {
    return {
      title: 'Attend your scheduled interview',
      description: 'You have an interview schedule. Review the details and join on time.',
      action: 'View interview',
      href: '/beneficiary/interviews'
    }
  }

  if (status === 'exam_passed') {
    return {
      title: 'Wait for interview scheduling',
      description: 'You passed the exam. Please wait for your interview schedule.',
      action: 'View schedule',
      href: '/beneficiary/interviews'
    }
  }

  if (status === 'exam' || status === 'for_exam') {
    return {
      title: 'Check your exam schedule',
      description: 'You have a scheduled exam. Review the date, time, and instructions.',
      action: 'View schedule',
      href: '/beneficiary/interviews'
    }
  }

  if (status === 'screening') {
    return {
      title: 'Wait for CPESO screening review',
      description: 'Your submitted application and requirements are currently under CPESO review.',
      action: 'View application',
      href: '/beneficiary/applications'
    }
  }

  // Default: applied or unknown — check if onboarding is completed
  if (beneficiaryProfile.value?.onboarding_completed_at || beneficiaryProfile.value?.draft_status === 'submitted') {
    return {
      title: 'Application submitted — under review',
      description: 'Your SPES application has been submitted. Please wait for CPESO screening.',
      action: 'View application',
      href: '/beneficiary/applications'
    }
  }

  return {
    title: 'Complete your SPES application',
    description: 'Finish your onboarding form and submit all required documents.',
    action: 'Start application',
    href: '/onboarding'
  }
})







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
  axios.post('/api/beneficiary/notifications/mark-all-read')
    .then(() => {
      notifications.value = notifications.value.map(n => ({
        ...n,
        read: true
      }))
    })
    .catch(err => console.error('Failed to mark all as read', err))
}

function openNotification(notification) {
  notificationModalOpen.value = true
  activeNotification.value = notification

  if (!notification.read) {
    axios.post(`/api/beneficiary/notifications/${notification.id}/mark-read`)
      .then(() => {
        notification.read = true
      })
      .catch(err => console.error('Failed to mark as read', err))
  }
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




    user.value = res.data.user || { name: 'User' }
    beneficiaryProfile.value = {
      ...beneficiaryProfile.value,
      ...res.data,
      approval_status: res.data.approval_status || beneficiaryProfile.value?.approval_status || 'pending'
    }




    assignedEmployer.value = res.data.employer || null




    evaluation.value = res.data.evaluation || []




    workHistory.value = res.data.work_history || []




    const statusRes = await axios.get('/api/beneficiary/application-status')
    applicationStatus.value = statusRes.data.status || 'applied'

    loadExams()
    loadInterviews()
    loadContracts()

    if (beneficiaryProfile.value?.approval_status === 'approved' || ['for_exam', 'exam_passed', 'for_interview', 'interview_passed', 'qualified', 'approved', 'assigned', 'for_contract', 'contract_signed', 'deployed', 'ongoing', 'completion_review', 'completed'].includes(applicationStatus.value)) {
      loadAttendance()
      loadActivities()
    }




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
    const res = await axios.get('/api/beneficiary/contracts')
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
  if (!isApproved.value) {
    return
  }




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
  if (!isApproved.value) {
    return
  }




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

const legacyMenus = [

  {
    name:'profile',
    label:'Profile',
    icon:'',
    children:[
     
      {label:'Edit Info',href:'/beneficiary/settings'}
    ]
  },




  {
    name:'application',
    label:'My Application',
    icon:'',
    children:[
      {label:'Application Status',href:'/beneficiary/applications'}
    ]
  },


 {
  name:'jobs',
  label:'Job Placement',
  icon:'',
  children:[
    { label:'View Placement', href:'/beneficiary/jobs' }
  ]
},


  {
    name:'exam',
    label:'Schedule',
    icon:'',
    children:[
      {label:'View Schedule',href:'/beneficiary/interviews'}
    ]
  },

  {
    name:'attendance',
    label:'Attendance / DTR',
    icon:'',
    children:[
      {label:'Submit DTR',href:'/beneficiary/attendance'}
    ]
  },
  {
  name:'ratings',
  label:'Activity History',
  icon:'',
  children:[
    {label:'Ratings History', href:'/beneficiary/ratings/history'},
    {label:'Activity History', href:'/beneficiary/activities'}
  ]
},
  {
    name:'notifications',
    label:'Messages',
    icon:'',
    children:[
      {label:'Messages',href:'/beneficiary/notifications'}
    ]
  }

]

const legacyPendingMenus = computed(() => [
  {
    name:'profile',
    label:'Profile',
    icon:'',
    children:[
      {label:'Edit Info',href:'/beneficiary/settings'}
    ]
  },
  {
    name:'onboarding',
    label:'Application Steps',
    icon:'',
    children:[
      {label:'Personal Information',href:'/onboarding?step=1'},
      {label:'Category Details',href:'/onboarding?step=2'},
      {label:'Documents',href:'/onboarding?step=3'},
      {label:'Review',href:'/onboarding?step=4'},
      {label:'Submit',href:'/onboarding?step=5'}
    ]
  },
  {
    name:'notifications',
    label:'Messages',
    icon:'',
    children:[
      {label:'Messages',href:'/beneficiary/notifications'}
    ]
  }
])

const menus = [
  {
    name: 'dashboard',
    label: 'Dashboard',
    icon: 'dashboard',
    href: '/beneficiary'
  },
  {
    name: 'application',
    label: 'My Application',
    icon: 'application',
    href: '/beneficiary/applications'
  },
  {
    name: 'requirements',
    label: 'Requirements',
    icon: 'requirements',
    href: '/beneficiary/upload'
  },
  {
    name: 'jobPlacement',
    label: 'Job Placement',
    icon: 'job',
    href: '/beneficiary/jobs'
  },
  {
    name: 'schedule',
    label: 'Schedule',
    icon: 'schedule',
    href: '/beneficiary/interviews'
  },
  {
    name: 'attendance',
    label: 'Attendance / DTR',
    icon: 'attendance',
    href: '/beneficiary/attendance'
  },
  {
  name: 'ratings',
  label: 'Ratings',
  icon: 'application',
  href: '/beneficiary/ratings/history'
},
  {
    name: 'messages',
    label: 'Messages',
    icon: 'messages',
    href: '/beneficiary/notifications'
  },
  {
    name: 'profile',
    label: 'Profile',
    icon: 'profile',
    href: '/beneficiary/settings'
  }
]

const pendingMenus = computed(() => [
  {
    name: 'dashboard',
    label: 'Dashboard',
    icon: 'dashboard',
    href: '/beneficiary'
  },
  {
    name: 'application',
    label: 'My Application',
    icon: 'application',
    href: '/beneficiary/applications'
  },
  {
    name: 'requirements',
    label: 'Requirements',
    icon: 'requirements',
    href: '/onboarding?step=3'
  },
  {
    name: 'messages',
    label: 'Messages',
    icon: 'messages',
    href: '/beneficiary/notifications'
  },
  {
    name: 'profile',
    label: 'Profile',
    icon: 'profile',
    href: '/beneficiary/settings'
  }
])

</script>
