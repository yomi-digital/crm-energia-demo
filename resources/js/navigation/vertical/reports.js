export default [
  {
    heading: 'Reports',
    action: 'see',
    subject: 'reports',
  },
  {
    title: 'Amministrativo',
    icon: { icon: 'tabler-file-analytics' },
    to: 'workflow-customers',
    action: 'access',
    subject: 'reports-admin',
  },
  {
    title: 'Produzione',
    icon: { icon: 'tabler-chart-line' },
    to: 'workflow-customers',
    action: 'access',
    subject: 'reports-production',
  },
  {
    title: 'Appuntamenti',
    icon: { icon: 'tabler-chart-bubble' },
    to: 'workflow-customers',
    action: 'access',
    subject: 'reports-appointments',
  },
]
