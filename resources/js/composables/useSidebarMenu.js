/**
 * Generate menu items based on user role
 * Strict role-based menu access (no role mixing)
 *
 * @param {Object} user - User object with roles array
 * @returns {Array} Menu items for the user's role
 */
export function useSidebarMenu(user = null) {
  if (!user || (!user.roles && !user.role)) {
    return { menuItems: [] }
    }

  // Accept both string arrays and role object arrays from Inertia
  const roleNames = Array.isArray(user.roles)
    ? user.roles.map((role) => (typeof role === 'string' ? role : role?.name)).filter(Boolean)
    : (user.role ? [user.role] : [])
  const normalizedRoleNames = roleNames.map((role) => String(role).toLowerCase())




  // Check roles - use strict single role check
  const isAdmin =
  normalizedRoleNames.includes('admin') ||
  normalizedRoleNames.includes('super admin')
  const isPesoAdmin = normalizedRoleNames.includes('peso admin')
  const isPesoUser = normalizedRoleNames.includes('peso')
  const isEmployer = normalizedRoleNames.includes('employer')




  let menuItems = []




  /*
  |--------------------------------------------------------------------------
  | ADMIN - FULL ACCESS
  |--------------------------------------------------------------------------
  */
  if (isAdmin) {
    menuItems = [
      { key: 'home', label: 'Dashboard', shortLabel: 'D' },
      {
        key: 'applicationsGroup',
        label: 'Applications',
        shortLabel: 'AP',
        children: [
          { key: 'beneficiaries', label: 'Beneficiary Applications', shortLabel: 'BA' },
          { key: 'employerApplications', label: 'Employer Applications', shortLabel: 'EA' },
        ],
      },
      {
        key: 'recordsGroup',
        label: 'Records',
        shortLabel: 'RC',
        children: [
          { key: 'approvedBeneficiaries', label: 'Beneficiaries', shortLabel: 'B' },
          { key: 'approvedEmployers', label: 'Employers', shortLabel: 'E' },
        ],
      },
      {
        key: 'spesOperationsGroup',
        label: 'SPES Operations',
        shortLabel: 'SO',
        children: [
          { key: 'assignment', label: 'Placement', shortLabel: 'P' },
          { key: 'schedule', label: 'Schedule', shortLabel: 'S' },
          { key: 'attendance', label: 'Monitoring', shortLabel: 'M' },
        ],
      },
      {
        key: 'communicationGroup',
        label: 'Communication',
        shortLabel: 'CM',
        children: [
          { key: 'announcements', label: 'Announcements', shortLabel: 'A' },
        ],
      },
      {
        key: 'reportsGroup',
        label: 'Reports',
        shortLabel: 'RP',
        children: [
          { key: 'reports', label: 'Reports', shortLabel: 'R' },
          { key: 'auditTrail', label: 'Audit Trail', shortLabel: 'AT' },
        ],
      },
      {
        key: 'settings',
        label: 'Settings',
        shortLabel: 'ST',
        children: [
          { key: 'userManagement', label: 'Users & Roles', shortLabel: 'UR' },
          { key: 'systemSettings', label: 'System Settings', shortLabel: 'SS' },
        ],
      },
    ]
  }




  /*
  |--------------------------------------------------------------------------
  | PESO ADMIN - LIMITED ADMIN ACCESS
  |--------------------------------------------------------------------------
  */
  else if (isPesoAdmin) {
    menuItems = [
      { key: 'home', label: 'Dashboard', shortLabel: 'D' },
      {
        key: 'applicationsGroup',
        label: 'Applications',
        shortLabel: 'AP',
        children: [
          { key: 'beneficiaries', label: 'Beneficiary Applications', shortLabel: 'BA' },
          { key: 'employerApplications', label: 'Employer Applications', shortLabel: 'EA' },
        ],
      },
      {
        key: 'recordsGroup',
        label: 'Records',
        shortLabel: 'RC',
        children: [
          { key: 'approvedBeneficiaries', label: 'Beneficiaries', shortLabel: 'B' },
          { key: 'approvedEmployers', label: 'Employers', shortLabel: 'E' },
        ],
      },
      {
        key: 'spesOperationsGroup',
        label: 'SPES Operations',
        shortLabel: 'SO',
        children: [
          { key: 'assignment', label: 'Placement', shortLabel: 'P' },
          { key: 'schedule', label: 'Schedule', shortLabel: 'S' },
          { key: 'attendance', label: 'Monitoring', shortLabel: 'M' },
        ],
      },
      {
        key: 'communicationGroup',
        label: 'Communication',
        shortLabel: 'CM',
        children: [
          { key: 'announcements', label: 'Announcements', shortLabel: 'A' },
        ],
      },
      {
        key: 'reportsGroup',
        label: 'Reports',
        shortLabel: 'RP',
        children: [
          { key: 'reports', label: 'Reports', shortLabel: 'R' },
          { key: 'auditTrail', label: 'Audit Trail', shortLabel: 'AT' },
        ],
      },
      {
        key: 'settings',
        label: 'Settings',
        shortLabel: 'ST',
        children: [
          { key: 'userManagement', label: 'Users & Roles', shortLabel: 'UR' },
          { key: 'systemSettings', label: 'System Settings', shortLabel: 'SS' },
        ],
      },
    ]
  }




  /*
  |--------------------------------------------------------------------------
  | PESO USER - MONITORING ONLY (STRICT)
  |--------------------------------------------------------------------------
  | ONLY basic dashboard, beneficiary/employer monitoring, and announcements
  */
  else if (isPesoUser) {
    menuItems = [
      { key: 'home', label: 'Dashboard', shortLabel: 'D' },
      {
        key: 'recordsGroup',
        label: 'Records',
        shortLabel: 'RC',
        children: [
          { key: 'approvedBeneficiaries', label: 'Beneficiaries', shortLabel: 'B' },
          { key: 'approvedEmployers', label: 'Employers', shortLabel: 'E' },
        ],
      },
      {
        key: 'spesOperationsGroup',
        label: 'SPES Operations',
        shortLabel: 'SO',
        children: [
          { key: 'schedule', label: 'Schedule', shortLabel: 'S' },
          { key: 'beneficiaryMonitoring', label: 'Monitoring', shortLabel: 'M' },
        ],
      },
      {
        key: 'communicationGroup',
        label: 'Communication',
        shortLabel: 'CM',
        children: [
          { key: 'announcements', label: 'Announcements', shortLabel: 'A' },
        ],
      },
      {
        key: 'reportsGroup',
        label: 'Reports',
        shortLabel: 'RP',
        children: [
          { key: 'reports', label: 'Reports', shortLabel: 'R' },
        ],
      },
    ]
  }




  /*
  |--------------------------------------------------------------------------
  | EMPLOYER - SUPERVISION WORKFLOW
  |--------------------------------------------------------------------------
  */
  else if (isEmployer) {
    menuItems = [
      { key: 'employerDashboard', label: 'Dashboard', shortLabel: 'D', href: '/employer' },
      {
        key: 'employerJobsGroup',
        label: 'Jobs',
        shortLabel: 'J',
        children: [
          { key: 'employerJobs', label: 'Job Listings', shortLabel: 'JL', href: '/employer/jobs' },
          { key: 'employerCreateJob', label: 'Create Job', shortLabel: 'CJ', href: '/employer/jobs/create' },
        ],
      },
      {
        key: 'employerBeneficiariesGroup',
        label: 'Beneficiaries',
        shortLabel: 'B',
        children: [
          { key: 'employerAssignedBeneficiaries', label: 'Assigned Beneficiaries', shortLabel: 'AB', href: '/employer/applicants' },
          { key: 'employerAttendance', label: 'Attendance / DTR', shortLabel: 'AT', href: '/employer/attendance' },
          { key: 'employerDailyReports', label: 'Daily Reports', shortLabel: 'DR', href: '/employer/work-outputs' },
          { key: 'employerRatings', label: 'Ratings', shortLabel: 'RT', href: '/employer/ratings/history' },
        ],
      },
      {
        key: 'employerCompletionGroup',
        label: 'Completion',
        shortLabel: 'C',
        children: [
          { key: 'employerCompletionSubmission', label: 'Completion Submission', shortLabel: 'CS', href: '/employer/completion-rate' },
        ],
      },
      {
        key: 'employerMessagesGroup',
        label: 'Messages',
        shortLabel: 'M',
        children: [
          { key: 'employerNotifications', label: 'Notifications', shortLabel: 'N', href: '/employer/notifications' },
          { key: 'employerAnnouncements', label: 'Announcements', shortLabel: 'A', href: '/employer/notifications' },
        ],
      },
      {
        key: 'employerProfileGroup',
        label: 'Profile',
        shortLabel: 'P',
        children: [
          { key: 'employerCompanyProfile', label: 'Company Profile', shortLabel: 'CP', href: '/employer/settings' },
          { key: 'employerSettings', label: 'Settings', shortLabel: 'S', href: '/employer/settings' },
        ],
      },
    ]
  }




  return { menuItems }
}










