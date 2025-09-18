export default [
  {
    heading: 'Extra',
    action: 'see',
    subject: 'extra',
    roles: ['gestione', 'backoffice', 'amministrazione'],
  },
  {
    title: 'Registro Login',
    icon: { icon: 'tabler-login' },
    to: 'extra-login-registry',
    action: 'access',
    subject: 'extra-login-registry',
    roles: ['gestione', 'backoffice', 'amministrazione'],
  },
  {
    title: 'Incentivi',
    icon: { icon: 'tabler-gift' },
    to: 'extra-incentivi-registry',
    action: 'access',
    subject: 'extra-incentivi-registry',
    roles: ['gestione', 'backoffice', 'amministrazione'],
  },
] 
