export function useSidebarMenu() {
  const menuItems = [
    { key: 'home', label: 'Dashboard & Analytics', shortLabel: 'D&A' },
    { key: 'beneficiaries', label: 'Beneficiaries', shortLabel: 'B' },
    { key: 'approvedBeneficiaries', label: 'Approved Beneficiaries', shortLabel: 'AB' },
    { key: 'approvedEmployers', label: 'Approved Employers', shortLabel: 'AE' },
    { key: 'reports', label: 'Employer Reports', shortLabel: 'R' },
    { key: 'roles', label: 'Manage Roles', shortLabel: 'MR' },
    { key: 'jobs', label: 'Job Listings', shortLabel: 'J' },
    { key: 'interviews', label: 'Interviews', shortLabel: 'I' },
    { key: 'announcements', label: 'Announcements', shortLabel: 'A' },
    { key: 'exam', label: 'Exam Flow Control', shortLabel: 'EX' },
    { key: 'contract', label: 'Contract Signing', shortLabel: 'CS' },
  ]

  return { menuItems }
}
