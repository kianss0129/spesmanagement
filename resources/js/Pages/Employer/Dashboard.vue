<template>
  <div class="flex min-h-screen bg-gradient-to-br from-blue-100 to-blue-200 overflow-x-hidden relative">


    <div v-if="mobileSidebarOpen" @click="mobileSidebarOpen = false" class="fixed inset-0 z-30 bg-black/40 sm:hidden"></div>


    <!-- ================= SIDEBAR ================= -->
     <aside
      class="dashboard-sidebar"    :class="[
  'bg-[#1f3a63] text-white flex flex-col transition-all duration-300 shadow-xl overflow-hidden fixed inset-y-0 left-0 z-40 h-screen max-h-screen sm:rounded-r-3xl',
      isOpen ? 'w-72 p-6' : 'w-20 p-3',
      mobileSidebarOpen
        ? 'fixed inset-y-0 left-0 z-40 translate-x-0'
        : '-translate-x-full sm:translate-x-0'
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
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
              <path v-if="menu.icon === 'profile'" stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              <path v-else-if="menu.icon === 'job'" stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.193 23.193 0 0112 15c-3.183 0-6.22-.64-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              <path v-else-if="menu.icon === 'attendance'" stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              <path v-else-if="menu.icon === 'reports'" stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              <path v-else-if="menu.icon === 'messages'" stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              <path v-else-if="menu.icon === 'requirements'" stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <span v-if="isOpen">{{ menu.label }}</span>
            <span v-if="menu.children && isOpen" class="ml-auto text-xs opacity-60">▾</span>
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
        <div v-if="!isRestricted" class="menu-item mt-4"
          @click="router.visit('/employer/settings')">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <span v-if="isOpen">Settings</span>
        </div>


        <!-- LOGOUT -->
        <div class="mt-6 px-1">
          <button
            @click="logout"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-red-500 hover:bg-red-600 transition text-white"
          >
            
            <span v-if="isOpen">Logout</span>
          </button>
        </div>


      </nav>
    </aside>


    <!-- ================= MAIN ================= -->
    <div
      class="min-h-screen flex-1 p-4 transition-all duration-300 sm:p-6"
      :class="isOpen ? 'sm:ml-72' : 'sm:ml-20'"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


        <!-- HEADER -->
        <header class="flex flex-wrap items-center justify-between gap-4 mb-8">
          <button
            @click="mobileSidebarOpen = true"
            class="sm:hidden inline-flex h-11 w-11 items-center justify-center rounded-lg bg-white text-gray-700 shadow-md"
            aria-label="Open Menu"
          >
            
          </button>
          <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold text-gray-700 truncate">
              Employer Dashboard
            </h1>
            <p class="text-sm text-gray-500">SPES Employer Tasks</p>
          </div>


          <div class="flex items-center gap-4">


            <!--  Notification -->
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


        <div v-if="isRestricted" class="bg-white p-6 rounded-lg shadow-lg mb-8">
          <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between gap-4">
              <div>
                <p class="text-sm text-gray-500 uppercase tracking-[0.18em]">Onboarding Progress</p>
                <h2 class="text-2xl font-semibold text-slate-800">Employer access is limited</h2>
                <p class="text-sm text-gray-500 mt-1">Complete the steps below to unlock the full employer dashboard.</p>
              </div>
              <div class="text-right">
                <div class="text-4xl font-bold text-blue-600">{{ onboardingProgress.percentage }}%</div>
                <p class="text-xs uppercase text-gray-400">Progress</p>
              </div>
            </div>


            <div class="w-full bg-slate-100 rounded-full h-4 overflow-hidden">
              <div class="h-4 bg-blue-600 rounded-full transition-all" :style="`width: ${onboardingProgress.percentage}%`"></div>
            </div>


            <div class="grid gap-3">
              <div
                v-for="step in onboardingProgress.steps"
                :key="step.label"
                class="flex items-center justify-between gap-3 p-4 rounded-lg border"
                :class="step.complete ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-white'"
              >
                <div>
                  <p class="font-medium text-slate-800">{{ step.label }}</p>
                  <p v-if="step.complete" class="text-xs text-green-700">Completed</p>
                  <p v-else class="text-xs text-gray-500">Pending</p>
                </div>
                <span class="text-xl">{{ step.complete ? '' : '' }}</span>
              </div>
            </div>


            <div class="pt-4 text-sm text-gray-600">
              <p v-if="onboardingProgress.status === 'approved'">Your account is approved and ready for full employer access.</p>
              <p v-else-if="onboardingProgress.status === 'pending'">Your information is ready and awaiting admin approval.</p>
              <p v-else>Your onboarding is still in progress. Verify your email, complete your company profile, and upload documents.</p>
            </div>


            <div class="mt-6 grid gap-4 sm:grid-cols-3">
              <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                <p class="text-sm font-semibold text-slate-900">Employer Profile</p>
                <p class="mt-2 text-sm text-slate-600">View and update your company details.</p>
              </div>
              <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                <p class="text-sm font-semibold text-slate-900">Onboarding</p>
                <p class="mt-2 text-sm text-slate-600">Submit requirements and complete your application.</p>
              </div>
              <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                <p class="text-sm font-semibold text-slate-900">Notifications</p>
                <p class="mt-2 text-sm text-slate-600">See updates from the admin and your application status.</p>
              </div>
            </div>


            <div class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Application status</p>
                  <h3 class="text-xl font-semibold text-slate-900">
                    {{ onboardingProgress.status === 'pending' ? 'Pending approval' : 'Onboarding incomplete' }}
                  </h3>
                  <p class="mt-2 text-sm text-slate-600">Complete the onboarding process to unlock full employer access.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                  <Link href="/onboarding" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                    Continue Onboarding
                  </Link>
                  <Link href="/employer/notifications" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition">
                    View Notifications
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>


        <div v-if="!isRestricted" class="space-y-6">
          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">
              SPES Employer Tasks
            </p>
            <h2 class="mt-2 text-3xl font-bold text-slate-900">Employer Dashboard</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
              See the applicants, beneficiaries, attendance records, reports, ratings, and CPESO messages that need your attention.
            </p>
          </section>

          <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Link
              v-for="task in priorityTasks"
              :key="task.label"
              :href="task.href"
              class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow-md"
            >
              <p class="text-sm font-semibold text-slate-600">{{ task.label }}</p>
              <div class="mt-4 flex items-end justify-between gap-3">
                <span class="text-3xl font-bold text-slate-900">{{ task.value }}</span>
                <span
                  class="rounded-full px-3 py-1 text-xs font-semibold"
                  :class="task.value > 0 ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800'"
                >
                  {{ task.value > 0 ? 'Needs action' : 'Clear' }}
                </span>
              </div>
              <p class="mt-3 text-sm text-slate-500">{{ task.description }}</p>
            </Link>
          </section>

          <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
              <div class="flex items-center justify-between gap-3 border-b border-slate-200 p-5">
                <div>
                  <h2 class="text-lg font-bold text-slate-900">Active Job Posts</h2>
                  <p class="mt-1 text-sm text-slate-500">Short list of jobs with applicant or assignment counts.</p>
                </div>
                <Link href="/employer/jobs" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                  Manage
                </Link>
              </div>

              <div class="divide-y divide-slate-200">
                <div
                  v-for="job in activeJobPosts"
                  :key="job.id || job.title"
                  class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div>
                    <p class="font-semibold text-slate-900">{{ job.title || job.name || 'Job post' }}</p>
                    <p class="mt-1 text-sm text-slate-500">
                      {{ jobApplicantCount(job) }} beneficiaries{{ jobApplicantCount(job) === 1 ? '' : '' }}
                    </p>
                  </div>
                  <Link href="/employer/applicants" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    View Beneficiaries
                  </Link>
                </div>

                <p v-if="activeJobPosts.length === 0" class="p-5 text-sm text-slate-500">
                  No active job posts yet.
                </p>
              </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
              <div class="flex items-center justify-between gap-3 border-b border-slate-200 p-5">
                <div>
                  <h2 class="text-lg font-bold text-slate-900">Recent CPESO Announcements</h2>
                  <p class="mt-1 text-sm text-slate-500">Latest announcements and reminders.</p>
                </div>
                <Link href="/employer/notifications" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                  View all
                </Link>
              </div>

              <div class="divide-y divide-slate-200">
                <button
                  v-for="message in recentCpesoMessages"
                  :key="message.id"
                  type="button"
                  class="block w-full p-5 text-left transition hover:bg-slate-50"
                  @click="openNotification(message)"
                >
                  <div class="flex items-start justify-between gap-3">
                    <p class="font-semibold text-slate-900">{{ message.title }}</p>
                    <span
                      class="rounded-full px-2 py-1 text-xs font-semibold"
                      :class="message.read ? 'bg-slate-100 text-slate-600' : 'bg-blue-100 text-blue-800'"
                    >
                      {{ message.read ? 'Read' : 'Unread' }}
                    </span>
                  </div>
                  <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ message.content }}</p>
                  <p class="mt-2 text-xs text-slate-400">{{ formatDate(message.created_at) }}</p>
                </button>

                <p v-if="recentCpesoMessages.length === 0" class="p-5 text-sm text-slate-500">
                  No recent CPESO messages.
                </p>
              </div>
            </div>
          </section>

          <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 p-5">
              <div>
                <h2 class="text-lg font-bold text-slate-900">Assigned Beneficiaries</h2>
                <p class="mt-1 text-sm text-slate-500">Acknowledge assignments and supervise active SPES beneficiaries.</p>
              </div>
              <Link href="/employer/completion-rate" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                Manage
              </Link>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                  <tr>
                    <th class="px-5 py-3">Name</th>
                    <th class="px-5 py-3">Job / Position</th>
                    <th class="px-5 py-3">Assignment Status</th>
                    <th class="px-5 py-3 text-right">Action</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                  <tr v-for="beneficiary in assignedBeneficiaries" :key="beneficiary.id">
                    <td class="px-5 py-4 font-semibold text-slate-900">{{ beneficiaryName(beneficiary) }}</td>
                    <td class="px-5 py-4 text-slate-600">{{ beneficiary.job_title || beneficiary.position || 'Assigned SPES role' }}</td>
                    <td class="px-5 py-4">
                      <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                        {{ beneficiary.assignment_status || 'Active' }}
                      </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                      <Link href="/employer/completion-rate" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Open
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>

              <p v-if="assignedBeneficiaries.length === 0" class="p-5 text-sm text-slate-500">
                No assigned beneficiaries yet.
              </p>
            </div>
          </section>

          <!-- CONTRACT SIGNING SCHEDULE -->
          <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 p-5">
              <div>
                <h2 class="text-lg font-bold text-slate-900">Scheduled Contract Signing</h2>
                <p class="mt-1 text-sm text-slate-500">Upcoming contract signing schedules with your assigned SPES beneficiaries.</p>
              </div>
              <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                {{ scheduledContracts.length }} schedule{{ scheduledContracts.length !== 1 ? 's' : '' }}
              </span>
            </div>

            <div v-if="scheduledContracts.length > 0" class="divide-y divide-slate-200">
              <div
                v-for="contract in scheduledContracts"
                :key="contract.id"
                class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between"
              >
                <div class="flex items-start gap-4">
                  <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-sm font-bold text-blue-700">
                    {{ contract.beneficiary_name ? contract.beneficiary_name.charAt(0).toUpperCase() : 'C' }}
                  </div>
                  <div>
                    <p class="font-semibold text-slate-900">{{ contract.beneficiary_name || 'Beneficiary' }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ formatContractDate(contract.contract_date) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Venue: {{ contract.location || 'To be announced' }}</p>
                  </div>
                </div>
                <span
                  class="rounded-full px-3 py-1 text-xs font-semibold"
                  :class="contract.status === 'scheduled' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'"
                >
                  {{ contract.status === 'scheduled' ? 'Scheduled' : 'Rescheduled' }}
                </span>
              </div>
            </div>

            <div v-else class="p-5 text-center">
              <p class="text-sm text-slate-500">No contract signing schedules at this time.</p>
              <p class="mt-1 text-xs text-slate-400">Schedules will appear here when CPESO sets a contract signing date for your assigned beneficiaries.</p>
            </div>
          </section>

          <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
              v-for="item in summaryStrip"
              :key="item.label"
              class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
            >
              <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ item.label }}</p>
              <p class="mt-3 text-2xl font-bold text-slate-900">{{ item.value }}</p>
            </div>
          </section>
        </div>

        <!-- ================= SUMMARY CARDS ================= -->
        <div v-if="false && !isRestricted" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">


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
        <div v-if="false && !isRestricted" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">


          <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-start justify-between gap-4 mb-4">
              <div>
                <h2 class="font-semibold text-lg">Applicants per Job</h2>
                <p class="text-sm text-gray-500">How many applicants each job received</p>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-blue-600">{{ totalApplicants }}</div>
                <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Total applicants</p>
              </div>

              <button
            @click="exportChart('applicants')"
            class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm"
          >
            Export
          </button>
            </div>
            <div class="relative">
              <canvas ref="applicantsCanvas" height="220"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-gray-500">
              <div class="rounded-lg bg-blue-50 p-3">
                <div class="font-semibold text-blue-700">{{ jobCount }}</div>
                <div>Jobs tracked</div>
              </div>
              <div class="rounded-lg bg-slate-50 p-3">
                <div class="font-semibold text-slate-700">{{ topJob?.title || 'No jobs yet' }}</div>
                <div>{{ topJobCount }} applicants</div>
              </div>
            </div>
          </div>


          <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center justify-center">
            <div class="w-full flex items-center justify-between mb-4">
              <div>
                <h2 class="font-semibold">Completion Submission</h2>
                <p class="text-sm text-gray-500">Beneficiary outcome</p>
              </div>
              <div class="text-right">
                <div class="text-3xl font-bold text-green-600">{{ stats.completion_rate ?? 0 }}%</div>
                <div class="text-xs text-gray-500">of completed applications</div>
              </div>
              <button
              @click="exportChart('completion')"
              class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm"
            >
              Export
            </button>
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
        <div v-if="false && !isRestricted" class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-10">
          <div class="xl:col-span-2 bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-start justify-between gap-4 mb-4">
              <div>
                <h2 class="font-semibold text-lg">Performance Evaluation</h2>
                <p class="text-sm text-gray-500">Average employer rating across core categories</p>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-blue-600">{{ ratingSummary.count ?? 0 }}</div>
                <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Ratings submitted</p>
              </div>
              <button
              @click="exportChart('rating')"
              class="px-3 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm"
            >
              Export
            </button>
            </div>
            <div class="relative" style="min-height: 240px; max-height: 280px;">
              <canvas ref="ratingChartCanvas" class="w-full h-full"></canvas>
            </div>
          </div>
          <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="font-semibold text-lg mb-4">Rating summary</h2>
            <div class="grid grid-cols-2 gap-3 text-sm text-gray-600 mb-6">
              <div class="rounded-lg bg-blue-50 p-4">
                <div class="font-semibold text-blue-700">{{ ratingSummary.punctuality }}</div>
                <div>Punctuality</div>
              </div>
              <div class="rounded-lg bg-slate-50 p-4">
                <div class="font-semibold text-slate-800">{{ ratingSummary.work_quality }}</div>
                <div>Work Quality</div>
              </div>
              <div class="rounded-lg bg-slate-50 p-4">
                <div class="font-semibold text-slate-800">{{ ratingSummary.attitude }}</div>
                <div>Attitude</div>
              </div>
              <div class="rounded-lg bg-slate-50 p-4">
                <div class="font-semibold text-slate-800">{{ ratingSummary.communication }}</div>
                <div>Communication</div>
              </div>
              <div class="rounded-lg bg-emerald-50 p-4 col-span-2">
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
                  View all ratings 
                </Link>
              </div>
              <div v-if="recentRatings.length" class="space-y-3">
                <div v-for="rating in recentRatings" :key="rating.id" class="rounded-lg border border-gray-100 p-3 bg-gray-50">
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
        <div v-if="false && !isRestricted" class="bg-white p-6 rounded-lg shadow-lg mb-10">
          <div class="flex justify-between items-center mb-3">
            <h2 class="font-semibold">Recent Activities</h2>
            <Link href="/employer/activities" class="text-sm text-blue-600 hover:text-blue-800">
              View All Activities 
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
          v-if="false && !isRestricted"
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
    ></button>


    <img :src="modalImage" class="w-full rounded-lg shadow-lg" />
  </div>
</div>


<!-- NOTIFICATION DETAILS MODAL -->
<div v-if="notificationModalOpen" @click.self="closeNotificationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 px-4 py-8">
  <div class="relative w-full max-w-4xl mx-auto bg-white rounded-lg shadow-2xl overflow-hidden max-h-[90vh]">
    <button
      @click="closeNotificationModal"
      class="absolute top-4 right-4 z-20 rounded-full bg-white/90 p-3 text-gray-700 shadow hover:bg-white transition"
    ></button>


    <div class="p-6 space-y-4 overflow-auto max-h-[90vh]">
      <div>
        <h2 class="text-xl font-semibold text-gray-900">{{ activeNotification?.title || 'Notification' }}</h2>
        <p class="text-sm text-gray-500">{{ activeNotification ? new Date(activeNotification.created_at).toLocaleString() : '' }}</p>
      </div>


      <p class="text-sm text-gray-700" v-if="activeNotification">{{ activeNotification.content }}</p>


      <div v-if="activeNotification && activeNotification.image" class="relative w-full h-[60vh] max-h-[70vh] rounded-lg overflow-hidden bg-gray-100">
        <img
          :src="'/storage/' + activeNotification.image"
          class="h-full w-full object-contain"
          alt="Notification Image"
        />
      </div>


      <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-600">
        <p><span class="font-semibold">Type:</span> {{ activeNotification?.type || 'General' }}</p>
        <p><span class="font-semibold">Status:</span> {{ activeNotification?.read ? 'Read' : 'Unread' }}</p>
      </div>
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
  axios.post('/employer/notifications/mark-all-read')
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
    axios.post(`/employer/notifications/${notification.id}/mark-read`)
      .then(() => {
        notification.read = true
      })
      .catch(err => console.error('Failed to mark as read', err))
  }
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
const employer = ref(props.employer || null)
const onboardingProgress = ref(props.onboardingProgress || { percentage: 0, steps: [] })
const limitedAccess = ref(props.limitedAccess ?? false)
const isRestricted = computed(() => limitedAccess.value)


const restrictedMenus = [
  { name:'profile', label:'Employer Profile', icon:'profile', href:'/employer/settings' },
  { name:'onboarding', label:'Onboarding', icon:'requirements', href:'/onboarding' },
  { name:'notifications', label:'Notifications', icon:'messages', href:'/employer/notifications' },
]


const fullMenus = [
  { name:'profile', label:'Company Profile', icon:'profile', children:[
    {label:'Company Profile', href:'/employer/settings'},
  ]},
  { name:'jobs', label:'Placement & Supervision', icon:'job', children:[
    {label:'Job Slots', href:'/employer/jobs'},
    { label:'Assigned Beneficiaries', href:'/employer/applicants'},
    { label:'Completion Submission', href:'/employer/completion-rate' }
  ]},
  { name:'attendance', label:'Attendance / DTR', icon:'attendance', children:[
    {label:'Review DTR', href:'/employer/attendance'}
  ]},
  { name:'reports', label:'Reports', icon:'reports', children:[
    {label:'Daily Reports', href:'/employer/work-outputs'},
    {label:'Employer Reports', href:'/employer/reports'},
    {label:'Activity History', href:'/employer/activities'}
  ]},
  { name:'notifications', label:'Messages', icon:'messages', children:[
    {label:'CPESO Messages',href:'/employer/notifications'}
  ]},
]


const menus = computed(() => isRestricted.value ? restrictedMenus : fullMenus)


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


function jobApplicantCount(job) {
  return job?.total ?? job?.count ?? job?.applications_count ?? job?.applicants_count ?? 0
}


function beneficiaryName(beneficiary) {
  return [
    beneficiary?.first_name,
    beneficiary?.middle_name,
    beneficiary?.last_name,
    beneficiary?.suffix,
  ].filter(Boolean).join(' ') || beneficiary?.name || 'Assigned beneficiary'
}


const activeJobPosts = computed(() => jobs.value.slice(0, 4))


const assignedBeneficiaries = computed(() => beneficiaries.value.slice(0, 5))


const pendingDtrs = computed(() =>
  Number(stats.value.pending_dtrs ?? stats.value.today_attendance ?? 0)
)


const reportsDue = computed(() =>
  Number(stats.value.reports_due ?? stats.value.pending_reports ?? 0)
)


const ratingsPending = computed(() =>
  Number(stats.value.pending_ratings ?? 0)
)


const priorityTasks = computed(() => [
  {
    label: 'Assignments needing review',
    value: Number(stats.value.pending_applicants ?? stats.value.applicants ?? 0),
    description: 'Review assigned beneficiaries and acknowledgement status.',
    href: '/employer/applicants',
  },
  {
    label: 'DTRs needing review',
    value: pendingDtrs.value,
    description: 'Check attendance entries submitted for your SPES beneficiaries.',
    href: '/employer/attendance',
  },
  {
    label: 'Reports due',
    value: reportsDue.value,
    description: 'Submit required employer reports and supporting documents.',
    href: '/employer/reports',
  },
  {
    label: 'Ratings pending',
    value: ratingsPending.value,
    description: 'Complete ratings for beneficiaries who need evaluation.',
    href: '/employer/applicants',
  },
])


const recentCpesoMessages = computed(() =>
  visibleNotifications.value
    .slice()
    .sort((a, b) => {
      if (Boolean(a.read) !== Boolean(b.read)) return a.read ? 1 : -1
      return new Date(b.created_at || 0) - new Date(a.created_at || 0)
    })
    .slice(0, 3)
)


const summaryStrip = computed(() => [
  { label: 'Open Jobs', value: Number(stats.value.open_jobs ?? jobs.value.length ?? 0) },
  { label: 'Assigned Beneficiaries', value: beneficiaries.value.length },
  { label: 'Pending DTRs', value: pendingDtrs.value },
  { label: 'Completed Beneficiaries', value: Number(stats.value.completed_applications ?? 0) },
])


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
function convertToCSV(data) {
  const array = [data.headers, ...data.rows]
  return array.map(row => 
    row.map(cell => {
      // Escape quotes and wrap in quotes if contains comma, newline, or quote
      const stringCell = String(cell)
      if (stringCell.includes(',') || stringCell.includes('\n') || stringCell.includes('"')) {
        return `"${stringCell.replace(/"/g, '""')}"`
      }
      return stringCell
    }).join(',')
  ).join('\n')
}

function downloadCSV(filename, csvContent) {
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)
  link.setAttribute('href', url)
  link.setAttribute('download', `${filename}.csv`)
  link.style.visibility = 'hidden'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

function exportChart(type) {
  let filename = ''
  let csvData = null

  switch (type) {
    case 'applicants':
      filename = 'Applicants-Per-Job'
      if (applicantsChart && applicantsChart.data) {
        const labels = applicantsChart.data.labels || []
        const values = applicantsChart.data.datasets[0]?.data || []
        csvData = {
          headers: ['Job Title', 'Number of Applicants'],
          rows: labels.map((label, idx) => [label, values[idx] || 0])
        }
      } else if (jobs.value.length > 0) {
        csvData = {
          headers: ['Job Title', 'Number of Applicants'],
          rows: jobs.value.map(j => [
            j.title || j.name || 'Job',
            j.total ?? j.count ?? j.applications_count ?? j.applicants_count ?? 0
          ])
        }
      }
      break

    case 'completion':
      filename = 'Completion-Rate'
      if (completionChart && completionChart.data) {
        const labels = completionChart.data.labels || []
        const values = completionChart.data.datasets[0]?.data || []
        csvData = {
          headers: ['Status', 'Count'],
          rows: labels.map((label, idx) => [label, values[idx] || 0])
        }
      } else {
        csvData = {
          headers: ['Status', 'Count'],
          rows: [
            ['Completed', stats.value.completed_applications ?? 0],
            ['Ongoing', stats.value.ongoing_applications ?? 0],
            ['Not Started', stats.value.not_started_applications ?? 0]
          ]
        }
      }
      break

    case 'rating':
      filename = 'Performance-Evaluation'
      if (ratingEvaluationChart && ratingEvaluationChart.data) {
        const labels = ratingEvaluationChart.data.labels || []
        const values = ratingEvaluationChart.data.datasets[0]?.data || []
        csvData = {
          headers: ['Category', 'Average Score'],
          rows: labels.map((label, idx) => [label, values[idx] || 0])
        }
      } else {
        csvData = {
          headers: ['Category', 'Average Score'],
          rows: [
            ['Punctuality', ratingSummary.value.punctuality],
            ['Work Quality', ratingSummary.value.work_quality],
            ['Attitude', ratingSummary.value.attitude],
            ['Communication', ratingSummary.value.communication],
            ['Overall', ratingSummary.value.overall]
          ]
        }
      }
      break
  }

  if (!csvData || csvData.rows.length === 0) {
    showToast('No data available to export')
    return
  }

  const csvContent = convertToCSV(csvData)
  downloadCSV(filename, csvContent)
  showToast(`${filename} exported as CSV successfully`)
}

// Re-render charts automatically when data changes
watch([jobs, stats], () => {
  renderApplicantsChart()
  renderCompletionChart()
  renderApplicationsChart()
  renderRatingChart()
})


// ================= Beneficiaries =================
const beneficiaries = ref([])


async function loadBeneficiaries() {
  try {
    const res = await axios.get('/employer/beneficiaries')
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

const scheduledContracts = ref([])

async function loadScheduledContracts() {
  try {
    const res = await axios.get('/employer/contracts/scheduled')
    scheduledContracts.value = Array.isArray(res.data) ? res.data : []
  } catch (err) {
    console.error('Failed to load scheduled contracts:', err)
    scheduledContracts.value = []
  }
}

function formatContractDate(dateStr) {
  if (!dateStr) return 'To be announced'
  const d = new Date(dateStr)
  if (Number.isNaN(d.getTime())) return dateStr
  return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' })
}
// ================= Initial Load =================
onMounted(async () => {
  await loadJobs()
  await loadStats()
  await loadBeneficiaries()
  await loadActivities()
  await loadScheduledContracts()
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
