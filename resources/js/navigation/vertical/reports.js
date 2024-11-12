export default [
  {
    heading: 'Reports',
    action: 'see',
    subject: 'reports',
  },
  {
    title: 'Produzione',
    icon: { icon: 'tabler-chart-line' },
    to: 'reports-production',
    action: 'access',
    subject: 'reports-production',
  },
  {
    title: 'Amministrativo',
    icon: { icon: 'tabler-file-analytics' },
    to: 'reports-admin',
    action: 'access',
    subject: 'reports-admin',
  },
  {
    title: 'Generati',
    icon: { icon: 'tabler-cloud-check' },
    to: 'reports-saved',
    action: 'access',
    subject: 'reports-admin',
  },
  // {
  //   title: 'Appuntamenti',
  //   icon: { icon: 'tabler-chart-bubble' },
  //   to: 'reports-appointments',
  //   action: 'access',
  //   subject: 'reports-appointments',
  // },
]
