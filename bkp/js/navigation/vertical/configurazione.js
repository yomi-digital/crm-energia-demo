export default [
  {
    heading: 'Configurazione',
    action: 'see',
    subject: 'configuration',
  },
  {
    title: 'Mandati',
    icon: { icon: 'tabler-building-skyscraper' },
    to: 'configuration-mandates',
    action: 'access',
    subject: 'mandates',
  },
  {
    title: 'Agenzie di Fatturazioni',
    icon: { icon: 'tabler-building-bank' },
    to: 'configuration-agencies',
    action: 'access',
    subject: 'agencies',
  },
  // {
  //   title: 'Categorie Brands',
  //   icon: { icon: 'tabler-user' },
  //   to: 'workflow-customers',
  // },
  {
    title: 'Brands',
    icon: { icon: 'tabler-badge-tm' },
    to: 'configuration-brands',
    action: 'access',
    subject: 'brands',
  },
  {
    title: 'Prodotti',
    icon: { icon: 'tabler-star' },
    to: 'configuration-products',
    action: 'access',
    subject: 'products',
    // children: [
    //   { title: 'Compensi', to: 'workflow-customers' },
    // ],
  },
]
