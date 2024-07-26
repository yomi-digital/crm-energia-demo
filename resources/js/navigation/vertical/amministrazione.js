export default [
  {
    heading: 'Amministrazione',
    action: 'see',
    subject: 'administration',
  },
  {
    title: 'Account',
    icon: { icon: 'tabler-user' },
    to: 'admin-users',
    action: 'access',
    subject: 'users',
  },
  // {
  //   title: 'Logs',
  //   icon: { icon: 'tabler-article' },
  //   action: 'access',
  //   subject: 'logs',
  //   children: [
  //     { title: 'Account', to: 'admin-users', action: 'access', subject: 'logs' },
  //     { title: 'Login', to: 'admin-users', action: 'access', subject: 'logs' },
  //     { title: 'Archivio Notifiche', to: 'admin-users', action: 'access', subject: 'logs' },
  //   ],
  // }
]
