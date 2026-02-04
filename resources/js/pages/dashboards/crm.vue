<script setup>
import AIPaperworkUnassignedModal from '@/components/dialogs/AIPaperworkUnassignedModal.vue'
import SuspendedPaperworksModal from '@/components/dialogs/SuspendedPaperworksModal.vue'
import TicketsModal from '@/components/dialogs/TicketsModal.vue'
import StatusChip from '@/components/StatusChip.vue'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import VueApexCharts from 'vue3-apexcharts'

definePage({
  meta: {
    action: 'access',
    subject: 'dashboard',
  },
})

const loggedInUser = useCookie('userData').value
const isAdmin = loggedInUser?.roles?.some(role => role.name === 'gestione' || role.name === 'backoffice' || role.name === 'amministrazione')
const isBackoffice = loggedInUser?.roles?.some(role => role.name === 'backoffice')
const isAgent = loggedInUser?.roles?.some(role => role.name === 'agente')
const isStruttura = loggedInUser?.roles?.some(role => role.name === 'struttura')
const isStrutturaOrAgente = isStruttura || isAgent

// Controlla se l'utente può vedere il pulsante AI (admin, backoffice, struttura, agente)
const canSeeAI = loggedInUser?.roles?.some(role => 
  ['gestione', 'amministrazione', 'backoffice', 'struttura', 'agente'].includes(role.name)
)

let quickLinks = []

// Solo se può vedere AI, aggiungi "Crea Pratica AI"
if (canSeeAI) {
  quickLinks.push({ name: 'Crea Pratica AI', icon: 'tabler-file-plus', action: () => document.querySelector('#ai-contract-upload-btn').click() })
}

const dateFilters = ref({
  startDate: '',
  endDate: '',
  status: '',
  type: '',
  category: '',
})

const isSaving = ref(false)

// Add new refs for chart filters
const chartFilters = ref({
  brandId: null,
  productId: null,
  agentId: null,
})

// Add refs for filter options
const brands = ref([])
const products = ref([])

// Add pagination state variables
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

// Add loading states
const isLoadingBrands = ref(false)
const isLoadingProducts = ref(false)
const isLoadingAgents = ref(false)
const isLoadingCustomers = ref(false)

// Track if data has been loaded
const hasLoadedBrands = ref(false)
const hasLoadedProducts = ref(false)
const hasLoadedAgents = ref(false)
const hasLoadedCustomers = ref(false)

const router = useRouter()

// Add refs for paperworks data
const clientData = ref([])
const totalItems = ref(0)

// Add refs for AI paperworks data
const aiPaperworksData = ref([])
const totalAiPaperworks = ref(0)
const aiPaperworksPage = ref(1)
const aiPaperworksItemsPerPage = ref(10)

// Add refs for tickets data
const ticketsData = ref([])
const totalTickets = ref(0)
const ticketsPage = ref(1)
const ticketsItemsPerPage = ref(10)

const updateOptions = async ({ page: newPage, itemsPerPage: newItemsPerPage }) => {
  page.value = newPage
  itemsPerPage.value = newItemsPerPage
  await fetchPaperworks()
}

const stats = ref({
  today: { completed: 0, total: 0 },
  currentMonth: { completed: 0, total: 0 },
  previousMonth: { completed: 0, total: 0 },
})

const searchFilters = ref({
  agent_id: null,
  customer_id: null,
  tax_id: '',
  phone: '',
  order_number: '',
})

const agents = ref([])
const customers = ref([])
const customerSearch = ref('')

// Chart configurations
const getDonutOptions = (title) => ({
  chart: {
    type: 'donut',
    height: 350,
  },
  plotOptions: {
    pie: {
      donut: {
        size: '70%'
      }
    }
  },
  labels: [],
  title: {
    text: '', // Rimuoviamo il titolo dal grafico, lo aggiungeremo nel template
    align: 'center',
    style: {
      fontSize: '16px'
    }
  },
  legend: {
    position: 'bottom'
  },
  colors: ['#2E5AAC', '#4CAF50', '#FFC107', '#FF5252', '#9C27B0', '#00BCD4'],
  noData: {
    text: 'Nessun dato disponibile',
    align: 'center',
    verticalAlign: 'middle',
    style: {
      fontSize: '16px'
    }
  }
})

const getAreaOptions = (title) => ({
  chart: {
    type: 'area',
    height: 350,
    stacked: false,
    toolbar: {
      show: false,
    },
    animations: {
      enabled: true
    }
  },
  dataLabels: {
    enabled: false,
  },
  stroke: {
    curve: 'smooth',
    width: [3, 2, 2, 2, 2],
    opacity: [1, 0.5, 0.5, 0.5, 0.5]
  },
  fill: {
    type: 'gradient',
    gradient: {
      opacityFrom: [0.4, 0.2, 0.2, 0.2, 0.2],
      opacityTo: [0.1, 0.05, 0.05, 0.05, 0.05]
    }
  },
  colors: ['#2E5AAC', '#4CAF50', '#FFC107', '#FF5252', '#9C27B0'],
  xaxis: {
    type: 'category',
    tickPlacement: 'on',
    labels: {
      show: true,
      rotate: 0,
      rotateAlways: false,
    },
    axisBorder: {
      show: true
    },
    axisTicks: {
      show: true
    }
  },
  yaxis: {
    min: 0,
    tickAmount: 4,
    labels: {
      formatter: (value) => Math.round(value)
    }
  },
  grid: {
    show: true,
    xaxis: {
      lines: {
        show: false
      }
    }
  },
  title: {
    text: '', // Rimuoviamo il titolo dal grafico, lo aggiungeremo nel template
    align: 'left',
    style: {
      fontSize: '16px'
    }
  },
  legend: {
    position: 'top',
    horizontalAlign: 'right'
  },
  tooltip: {
    shared: true,
    intersect: false
  },
  noData: {
    text: 'Nessun dato disponibile',
    align: 'center',
    verticalAlign: 'middle',
    style: {
      fontSize: '16px'
    }
  }
})

// Chart refs
const todayDonutOptions = ref({
  ...getDonutOptions(''),
  series: [],
  labels: [],
})

const currentMonthDonutOptions = ref({
  ...getDonutOptions(''),
  series: [],
  labels: [],
})

const lastMonthDonutOptions = ref({
  ...getDonutOptions(''),
  series: [],
  labels: [],
})

const todayChartOptions = ref({
  ...getAreaOptions(''),
  chart: {
    ...getAreaOptions('').chart,
    id: 'today-chart',
  },
  xaxis: {
    ...getAreaOptions('').xaxis,
    categories: Array.from({ length: 24 }, (_, i) => `${String(i).padStart(2, '0')}:00`),
  },
  series: [{
    name: 'Totale',
    data: Array(24).fill(0)
  }]
})

const monthChartOptions = ref({
  ...getAreaOptions(''),
  chart: {
    ...getAreaOptions('').chart,
    id: 'month-chart',
  },
  xaxis: {
    ...getAreaOptions('').xaxis,
    categories: []
  },
  series: [{
    name: 'Totale',
    data: []
  }]
})

const lastMonthChartOptions = ref({
  ...getAreaOptions(''),
  chart: {
    ...getAreaOptions('').chart,
    id: 'last-month-chart',
  },
  xaxis: {
    ...getAreaOptions('').xaxis,
    categories: []
  },
  series: [{
    name: 'Totale',
    data: []
  }]
})

// Data fetching functions
const fetchPaperworks = async () => {
  try {
    const response = await $api('/dashboard/paperworks', {
      params: {
        page: page.value,
        itemsPerPage: itemsPerPage.value,
      },
    })
    clientData.value = response.data.data
    totalItems.value = response.data.total
  } catch (error) {
    console.error('Error fetching paperworks:', error)
  }
}

// Function to fetch AI paperworks
const fetchAiPaperworks = async () => {
  try {
    const params = {
      page: aiPaperworksPage.value,
      itemsPerPage: aiPaperworksItemsPerPage.value,
    }

    // Per ruoli diversi dal backoffice mostriamo solo le pratiche AI già processate (status = 2).
    // Per i backoffice, invece, chiediamo esplicitamente solo la coda "in entrata":
    // pratiche assegnate a lui, non confermate (status != 5) e ancora in attesa di accettazione.
    if (isBackoffice) {
      params.only_pending_assignment = 1
    } else {
      params.status = 2
    }

    const response = await $api('/ai-paperworks', {
      params,
    })
    aiPaperworksData.value = response.entries || []
    totalAiPaperworks.value = response.totalEntries || 0
  } catch (error) {
    console.error('Error fetching AI paperworks:', error)
  }
}

// Function to update AI paperworks options
const updateAiPaperworksOptions = async ({ page: newPage, itemsPerPage: newItemsPerPage }) => {
  aiPaperworksPage.value = newPage
  aiPaperworksItemsPerPage.value = newItemsPerPage
  await fetchAiPaperworks()
}

// Polling automatico ogni 10 secondi per "Pratiche in entrata"
let aiPaperworksPollingInterval = null

const startAiPaperworksPolling = () => {
  // Previeni polling multipli
  if (aiPaperworksPollingInterval) {
    clearInterval(aiPaperworksPollingInterval)
  }
  
  aiPaperworksPollingInterval = setInterval(async () => {
    await fetchAiPaperworks()
  }, 10000) // 10 secondi
}

const stopAiPaperworksPolling = () => {
  if (aiPaperworksPollingInterval) {
    clearInterval(aiPaperworksPollingInterval)
    aiPaperworksPollingInterval = null
  }
}

// Helper function for AI paperwork status
const getStatusChipColor = (status) => {
  switch (status) {
    case 0:
      return 'warning'
    case 1:
      return 'info'
    case 2:
      return 'success'
    case 5:
      return 'success'
    case 8:
      return 'error'
    case 9:
      return 'error'
    default:
      return 'error'
  }
}

const getStatusText = (status) => {
  switch (status) {
    case 0:
      return 'In attesa'
    case 1:
      return 'In elaborazione'
    case 2:
      return 'Processato'
    case 8:
      return 'Annullato'
    case 9:
      return 'Errore'
    case 5:
      return 'Confermato'
    default:
      return 'Errore'
  }
}

// Testo leggibile per lo stato di accettazione AI (assignment_status)
const getAiAssignmentStatusText = (status) => {
  if (!status || status === 'pending') {
    return 'In attesa'
  }

  if (status === 'accept' || status === 'accepted') {
    return 'Accettata'
  }

  return status
}

// Snackbar per notifiche AI (pratiche in entrata)
const isAiSnackbarVisible = ref(false)
const aiSnackbarMessage = ref('')
const aiSnackbarColor = ref('success')

// Modal per pratiche non assegnate
const unassignedModalOpen = ref(false)
const unassignedModalBrandName = ref(null)

// Modal per pratiche sospese
const isSuspendedModalVisible = ref(false)

// Modal per ticket
const isTicketsModalVisible = ref(false)

// Headers dinamici per la tabella "Pratiche in entrata" in base al ruolo
const aiPaperworksHeaders = computed(() => {
  const baseHeaders = [
    { title: '#', key: 'id', width: '80' },
    { title: 'Agente', key: 'user_id', sortable: false },
    { title: 'File', key: 'filepath', sortable: false },
    { title: 'Stato AI', key: 'status' },
    { title: 'Data', key: 'created_at', sortable: false },
  ]

  // Per i backoffice aggiungiamo Accettazione da parte tua + Azioni (con bottone Accetta)
  if (isBackoffice) {
    return [
      ...baseHeaders,
      { title: 'Accettazione da parte tua', key: 'assignment_status', sortable: false, width: '160px' },
      { title: 'Azioni', key: 'actions', sortable: false, width: '140px' },
    ]
  }

  // Per admin/gestione aggiungiamo Backoffice assegnato + Stato accettazione backoffice
  return [
    ...baseHeaders,
    { title: 'Backoffice assegnato', key: 'assigned_backoffice', sortable: false },
    { title: 'Accettazione backoffice', key: 'assignment_status', sortable: false, width: '160px' },
  ]
})

// Apri modal per pratica non assegnata
const openUnassignedModal = (item) => {
  unassignedModalBrandName.value = item.brand?.name || null
  unassignedModalOpen.value = true
}

// Azione di accettazione pratica AI dalla dashboard
const acceptAiPaperwork = async item => {
  try {
    await $api(`/ai-paperworks/${item.id}/accept-assignment`, {
      method: 'POST',
    })

    aiSnackbarColor.value = 'success'
    aiSnackbarMessage.value = 'Pratica AI accettata con successo.'
    isAiSnackbarVisible.value = true

    await fetchAiPaperworks()
  } catch (error) {
    console.error('Errore durante l\'accettazione della pratica AI:', error)
    aiSnackbarColor.value = 'error'

    let message = error?.data?.message || error?.data?.error || 'Si è verificato un errore durante l\'accettazione della pratica.'
    aiSnackbarMessage.value = message
    isAiSnackbarVisible.value = true
  }
}

// Helper function for ticket status
const ticketStatusText = (status) => {
  return ['Aperto', 'In Lavorazione', 'Risolto'][status - 1]
}

// Headers tabella Tickets: per struttura/agente aggiungi colonna Azioni
const ticketTableHeaders = computed(() => {
  const base = [
    { title: 'ID', key: 'id', width: '80' },
    { title: 'Pratica', key: 'paperwork_id', sortable: false },
    { title: 'Cliente', key: 'customer', sortable: false },
    { title: 'Oggetto', key: 'title', sortable: false },
    { title: 'Agente', key: 'agent', sortable: false },
    { title: 'Stato', key: 'status' },
    { title: 'Creato Da', key: 'created_by', sortable: false },
    { title: 'Data Creazione', key: 'created_at', sortable: false },
  ]
  if (isStrutturaOrAgente) {
    return [...base, { title: 'Azioni', key: 'actions', sortable: false, width: '100' }]
  }
  return base
})

// Function to fetch tickets
const fetchTickets = async () => {
  try {
    const response = await $api('/tickets', {
      params: {
        status: '1,2',
        page: ticketsPage.value,
        itemsPerPage: ticketsItemsPerPage.value,
      },
    })
    ticketsData.value = response.tickets || []
    totalTickets.value = response.totalTickets || 0
  } catch (error) {
    console.error('Error fetching tickets:', error)
  }
}

// Function to update tickets options
const updateTicketsOptions = async ({ page: newPage, itemsPerPage: newItemsPerPage }) => {
  ticketsPage.value = newPage
  ticketsItemsPerPage.value = newItemsPerPage
  await fetchTickets()
}

// Polling automatico ogni 10 secondi per "Tickets"
let ticketsPollingInterval = null

const startTicketsPolling = () => {
  // Previeni polling multipli
  if (ticketsPollingInterval) {
    clearInterval(ticketsPollingInterval)
  }
  
  ticketsPollingInterval = setInterval(async () => {
    await fetchTickets()
  }, 10000) // 10 secondi
}

const stopTicketsPolling = () => {
  if (ticketsPollingInterval) {
    clearInterval(ticketsPollingInterval)
    ticketsPollingInterval = null
  }
}

const fetchStats = async () => {
  try {
    const data = await $api('/dashboard/stats', {
      params: {
        ...chartFilters.value
      }
    })
    
    // Update donut charts
    todayDonutOptions.value = {
      ...todayDonutOptions.value,
      labels: Object.keys(data.today)
    }
    todayDonutOptions.value.series = Object.values(data.today)
    
    currentMonthDonutOptions.value = {
      ...currentMonthDonutOptions.value,
      labels: Object.keys(data.currentMonth)
    }
    currentMonthDonutOptions.value.series = Object.values(data.currentMonth)
    
    lastMonthDonutOptions.value = {
      ...lastMonthDonutOptions.value,
      labels: Object.keys(data.previousMonth)
    }
    lastMonthDonutOptions.value.series = Object.values(data.previousMonth)
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

const fetchTimeSeriesData = async () => {
  try {
    const response = await $api('/dashboard/time-series', {
      params: { 
        ...chartFilters.value
      },
    })

    console.log('Response:', response)

    const data = response

    if (!data) return

    console.log('Time series data:', data) // Debug log

    // Process today's data
    const todaySeries = [{
      name: 'Totale',
      data: Array(24).fill(0)
    }]

    // Fill in total counts for today
    if (data.today?.total) {
      data.today.total.forEach(d => {
        const hour = parseInt(d.period)
        if (hour >= 0 && hour < 24) {
          todaySeries[0].data[hour] = d.count
        }
      })
    }

    // Process brand data for today
    if (data.today?.byBrand?.length > 0) {
      const brandGroups = {}
      data.today.byBrand.forEach(d => {
        const hour = parseInt(d.period)
        if (hour >= 0 && hour < 24) {
          if (!brandGroups[d.brand]) {
            brandGroups[d.brand] = Array(24).fill(0)
          }
          brandGroups[d.brand][hour] = d.count
        }
      })

      Object.entries(brandGroups).forEach(([brand, counts]) => {
        todaySeries.push({
          name: brand,
          data: counts
        })
      })
    }

    // Update today's chart
    todayChartOptions.value.series = todaySeries

    // Process this month's data
    const now = new Date()
    const currentMonth = now.getMonth()
    const currentYear = now.getFullYear()
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate()
    
    const monthSeries = [{
      name: 'Totale',
      data: Array(daysInMonth).fill(0)
    }]

    // Create x-axis categories for current month
    const monthCategories = Array.from({ length: daysInMonth }, (_, i) => 
      `${String(i + 1).padStart(2, '0')}/${String(currentMonth + 1).padStart(2, '0')}`
    )

    // Fill in total counts for this month
    if (data.thisMonth?.total) {
      data.thisMonth.total.forEach(d => {
        const day = new Date(d.period).getDate() - 1
        if (day >= 0 && day < daysInMonth) {
          monthSeries[0].data[day] = d.count
        }
      })
    }

    // Process brand data for this month
    if (data.thisMonth?.byBrand?.length > 0) {
      const brandGroups = {}
      data.thisMonth.byBrand.forEach(d => {
        const day = new Date(d.period).getDate() - 1
        if (day >= 0 && day < daysInMonth) {
          if (!brandGroups[d.brand]) {
            brandGroups[d.brand] = Array(daysInMonth).fill(0)
          }
          brandGroups[d.brand][day] = d.count
        }
      })

      Object.entries(brandGroups).forEach(([brand, counts]) => {
        monthSeries.push({
          name: brand,
          data: counts
        })
      })
    }

    // Update this month's chart
    monthChartOptions.value.xaxis.categories = monthCategories
    monthChartOptions.value.series = monthSeries

    // Process last month's data
    const lastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1)
    const daysInLastMonth = new Date(lastMonth.getFullYear(), lastMonth.getMonth() + 1, 0).getDate()
    
    const lastMonthSeries = [{
      name: 'Totale',
      data: Array(daysInLastMonth).fill(0)
    }]

    // Create x-axis categories for last month
    const lastMonthCategories = Array.from({ length: daysInLastMonth }, (_, i) => 
      `${String(i + 1).padStart(2, '0')}/${String(lastMonth.getMonth() + 1).padStart(2, '0')}`
    )

    // Fill in total counts for last month
    if (data.lastMonth?.total) {
      data.lastMonth.total.forEach(d => {
        const day = new Date(d.period).getDate() - 1
        if (day >= 0 && day < daysInLastMonth) {
          lastMonthSeries[0].data[day] = d.count
        }
      })
    }

    // Process brand data for last month
    if (data.lastMonth?.byBrand?.length > 0) {
      const brandGroups = {}
      data.lastMonth.byBrand.forEach(d => {
        const day = new Date(d.period).getDate() - 1
        if (day >= 0 && day < daysInLastMonth) {
          if (!brandGroups[d.brand]) {
            brandGroups[d.brand] = Array(daysInLastMonth).fill(0)
          }
          brandGroups[d.brand][day] = d.count
        }
      })

      Object.entries(brandGroups).forEach(([brand, counts]) => {
        lastMonthSeries.push({
          name: brand,
          data: counts
        })
      })
    }

    // Update last month's chart
    lastMonthChartOptions.value.xaxis.categories = lastMonthCategories
    lastMonthChartOptions.value.series = lastMonthSeries

    console.log('Updated chart options:', {
      today: todayChartOptions.value,
      month: monthChartOptions.value,
      lastMonth: lastMonthChartOptions.value
    })
  } catch (error) {
    console.error('Error fetching time series data:', error)
  }
}

const loadBrands = async () => {
  if (hasLoadedBrands.value) return
  try {
    isLoadingBrands.value = true
    const response = await $api('/brands?itemsPerPage=9999&select=1')
    brands.value = response.brands
    hasLoadedBrands.value = true
  } catch (error) {
    console.error('Error loading brands:', error)
  } finally {
    isLoadingBrands.value = false
  }
}

const loadProducts = async () => {
  if (hasLoadedProducts.value) return
  try {
    isLoadingProducts.value = true
    const response = await $api('/products?itemsPerPage=9999&select=1')
    products.value = response.products
    hasLoadedProducts.value = true
  } catch (error) {
    console.error('Error loading products:', error)
  } finally {
    isLoadingProducts.value = false
  }
}

const loadAgents = async () => {
  if (hasLoadedAgents.value) return
  try {
    isLoadingAgents.value = true
    const response = await $api('/agents?itemsPerPage=9999&select=1')
    agents.value = response.agents.map(agent => ({
      id: agent.id,
      name: agent.name + ' ' + agent.last_name,
    }))
    hasLoadedAgents.value = true
  } catch (error) {
    console.error('Error loading agents:', error)
  } finally {
    isLoadingAgents.value = false
  }
}

const loadCustomers = async () => {
  if (hasLoadedCustomers.value) return
  try {
    isLoadingCustomers.value = true
    const response = await $api('/customers?itemsPerPage=9999&select=1')
    customers.value = response.customers.map(customer => ({
      id: customer.id,
      name: customer.business_name ? customer.business_name : customer.name + ' ' + customer.last_name,
    }))
    hasLoadedCustomers.value = true
  } catch (error) {
    console.error('Error loading customers:', error)
  } finally {
    isLoadingCustomers.value = false
  }
}

// Add watch for chart filters to update both stats and time series
watch(chartFilters, () => {
  fetchStats()
  fetchTimeSeriesData()
}, { deep: true })

// Verifica validità del token all'avvio
const verifyToken = async () => {
  const accessToken = useCookie('accessToken').value
  if (!accessToken) {
    window.location.href = '/login'
    return false
  }
  
  try {
    // Verifica se il token è ancora valido chiamando l'endpoint user
    await $api('/auth/user')
    return true
  } catch (error) {
    // Se il token non è valido (401), reindirizza al login
    if (error?.status === 401 || error?.response?.status === 401) {
      useCookie('accessToken').value = null
      useCookie('userData').value = null
      window.location.href = '/login'
    }
    return false
  }
}

// Initial data fetch
onMounted(async () => {
  // Verifica il token prima di caricare i dati
  const isValid = await verifyToken()
  if (!isValid) {
    return // Il redirect sarà gestito da useApi.js
  }
  
  await fetchPaperworks()
  await fetchAiPaperworks()
  await fetchTickets()
  await fetchStats()
  await fetchTimeSeriesData()
  await loadBrands()
  await loadProducts()
  await loadAgents()
  await loadCustomers()
  
  // Avvia il polling per "Pratiche in entrata" e "Tickets"
  startAiPaperworksPolling()
  startTicketsPolling()
})

// Ferma il polling quando l'utente esce dalla pagina
onUnmounted(() => {
  stopAiPaperworksPolling()
  stopTicketsPolling()
})

const handleSearch = () => {
  // Build query params from non-empty filters
  const queryParams = Object.entries(searchFilters.value)
    .filter(([_, value]) => value !== null && value !== '')
    .reduce((acc, [key, value]) => {
      acc[key] = value
      return acc
    }, {})

  // Navigate to paperworks page with filters
  router.push({
    path: '/workflow/paperworks',
    query: queryParams
  })
}

// Funzioni per navigare alla pagina paperworks con filtri temporali
const navigateToTodayPaperworks = () => {
  const today = new Date()
  const todayStr = today.toISOString().split('T')[0]
  router.push({
    path: '/workflow/paperworks',
    query: {
      date_from: todayStr,
      date_to: todayStr
    }
  })
}

const navigateToCurrentMonthPaperworks = () => {
  const now = new Date()
  const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
  const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0)
  router.push({
    path: '/workflow/paperworks',
    query: {
      date_from: firstDay.toISOString().split('T')[0],
      date_to: lastDay.toISOString().split('T')[0]
    }
  })
}

const navigateToPreviousMonthPaperworks = () => {
  const now = new Date()
  const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1)
  const lastDay = new Date(now.getFullYear(), now.getMonth(), 0)
  router.push({
    path: '/workflow/paperworks',
    query: {
      date_from: firstDay.toISOString().split('T')[0],
      date_to: lastDay.toISOString().split('T')[0]
    }
  })
}

const navigateToSuspendedPaperworks = () => {
  isSuspendedModalVisible.value = true
}

const navigateToOpenTickets = () => {
  isTicketsModalVisible.value = true
}
</script>

<template>
  <div>
    
    <!-- Quick Links e Contatori -->
    <VRow class="quick-links-counters mb-6">
      <!-- Crea Pratica AI -->
      <VCol
        v-for="link in quickLinks"
        :key="link.name"
        cols="12"
        :md="quickLinks.length > 0 ? 4 : 0"
      >
        <VCard
          class="quick-link-card"
          :to="link.to"
          v-if="link.to"
          flat
        >
          <VCardItem>
            <template #prepend>
              <VIcon
                :icon="link.icon"
                size="24"
              />
            </template>
            <VCardTitle>
              {{ link.name }}
            </VCardTitle>
          </VCardItem>
        </VCard>
        <VCard
          v-else
          class="quick-link-card"
          flat
          @click="link.action"
        >
          <VCardItem style="height: 100%;" class="h-100">
            <template #prepend>
              <VIcon
                :icon="link.icon"
                size="24"
              />
            </template>
            <VCardTitle>
              {{ link.name }}
            </VCardTitle>
          </VCardItem>
        </VCard>
      </VCol>
      
      <!-- Pratiche Sospese -->
      <VCol cols="12" :md="quickLinks.length > 0 ? 4 : 6">
        <VCard
          class="counter-card"
          variant="outlined"
          @click="navigateToSuspendedPaperworks"
        >
          <VCardItem>
            <template #prepend>
              <VIcon
                icon="tabler-page-break"
                size="32"
                color="warning"
              />
            </template>
            <VCardTitle class="text-h6 mb-1">
              Pratiche Sospese
            </VCardTitle>
            <VCardSubtitle class="text-h3 font-weight-bold">
              {{ totalItems }}
            </VCardSubtitle>
          </VCardItem>
        </VCard>
      </VCol>
      
      <!-- Ticket Aperti -->
      <VCol cols="12" :md="quickLinks.length > 0 ? 4 : 6">
        <VCard
          class="counter-card"
          variant="outlined"
          @click="navigateToOpenTickets"
        >
          <VCardItem>
            <template #prepend>
              <VIcon
                icon="tabler-ticket"
                size="32"
                color="primary"
              />
            </template>
            <VCardTitle class="text-h6 mb-1">
              Ticket Aperti
            </VCardTitle>
            <VCardSubtitle class="text-h3 font-weight-bold">
              {{ totalTickets }}
            </VCardSubtitle>
          </VCardItem>
        </VCard>
      </VCol>
    </VRow>

    <!-- Filters Section -->
    <VCard class="mt-6 d-none">
      <VCardTitle class="text-h6 px-6 py-4">
        Ricerca pratica
      </VCardTitle>
      <VCardText>
        <div class="search-grid">
          <AppSelect
            v-model="searchFilters.agent_id"
            label="Cerca per agente"
            placeholder="Seleziona"
            :items="agents"
            :loading="isLoadingAgents"
            item-title="name"
            item-value="id"
            clearable
          />
          <AppAutocomplete
            v-model="searchFilters.customer_id"
            v-model:search="customerSearch"
            label="Cerca per cliente"
            placeholder="Seleziona"
            :items="customers"
            :loading="isLoadingCustomers"
            item-title="name"
            item-value="id"
            clearable
          />
          <AppTextField
            v-model="searchFilters.tax_id"
            label="Cerca per P.IVA o C.F."
            placeholder="Inserisci P.IVA o C.F."
          />
          <AppTextField
            v-model="searchFilters.phone"
            label="Cerca per numero di telefono"
            placeholder="Inserisci numero"
          />
          <AppTextField
            v-model="searchFilters.order_number"
            label="Cerca per ID pratica"
            placeholder="Inserisci ID"
          />
        </div>
        <div class="d-flex justify-end mt-4">
          <VBtn
            color="primary"
            @click="handleSearch"
            width="200"
          >
            Cerca
          </VBtn>
        </div>
      </VCardText>
    </VCard>


    <VRow
      v-if="isAdmin"
      class="mt-6"
    >
      <VCol cols="12">
        <VCard variant="outlined" class="pa-4">
          <VCardTitle class="text-h5 mb-4">
            Pratiche in entrata
          </VCardTitle>
          <VDataTableServer
            v-model:items-per-page="aiPaperworksItemsPerPage"
            v-model:page="aiPaperworksPage"
            :items="aiPaperworksData"
            :items-length="totalAiPaperworks"
            :headers="aiPaperworksHeaders"
            class="text-no-wrap"
            @update:options="updateAiPaperworksOptions"
          >
            
            <template #item.id="{ item }">
              <RouterLink
                :to="{ name: 'workflow-aipaperworks-id', params: { id: item.id } }"
                class="font-weight-medium text-link"
              >
                {{ item.id }}
              </RouterLink>
            </template>

            
            <template #item.user_id="{ item }">
              <RouterLink
                v-if="item.user && $can('view', 'users')"
                :to="{ name: 'admin-users-id', params: { id: item.user.id } }"
                class="font-weight-medium text-link"
              >
                {{ [item.user.name, item.user.last_name].join(' ') }}
              </RouterLink>
              <template v-else>
                {{ [item.user?.name, item.user?.last_name].join(' ') }}
              </template>
            </template>

            
            <template #item.filepath="{ item }">
              <div class="text-high-emphasis text-body-1">
                {{ item.filepath ? item.filepath.split('/').pop() : '' }}
              </div>
            </template>

            <!-- Stato AI -->
            <template #item.status="{ item }">
              <VChip
                :color="getStatusChipColor(item.status)"
                size="small"
                class="text-capitalize"
              >
                {{ getStatusText(item.status) }}
              </VChip>
            </template>

            <!-- Backoffice assegnato (solo per admin) -->
            <template #item.assigned_backoffice="{ item }">
              <span v-if="item.assigned_backoffice">
                {{ [item.assigned_backoffice.name, item.assigned_backoffice.last_name].join(' ') }}
              </span>
              <VBtn
                v-else
                size="small"
                color="warning"
                variant="tonal"
                @click.stop="openUnassignedModal(item)"
              >
                Da assegnare
              </VBtn>
            </template>

            <!-- Accettazione backoffice (per admin) -->
            <template v-if="!isBackoffice" #item.assignment_status="{ item }">
              <VChip
                v-if="item.assignment_status === 'accept' || item.assignment_status === 'accepted'"
                color="success"
                size="small"
              >
                Accettata
              </VChip>
              <VChip
                v-else-if="item.assignment_status === 'pending'"
                color="warning"
                size="small"
              >
                In attesa
              </VChip>
              <VChip
                v-else
                color="default"
                size="small"
              >
                Non assegnata
              </VChip>
            </template>

            <!-- Accettazione da parte tua (per backoffice) -->
            <template v-if="isBackoffice" #item.assignment_status="{ item }">
              <VChip
                size="small"
                variant="tonal"
                class="text-capitalize"
                :color="item.assignment_status === 'accept' || item.assignment_status === 'accepted' ? 'success' : 'warning'"
              >
                {{ getAiAssignmentStatusText(item.assignment_status) }}
              </VChip>
            </template>

            <!-- Azioni (accetta pratica AI - solo per backoffice) -->
            <template #item.actions="{ item }">
              <div class="d-flex align-center gap-x-1" style="flex-wrap: nowrap;">
                <VBtn
                  v-if="isBackoffice && item.assignment_status !== 'accept' && item.assignment_status !== 'accepted'"
                  size="small"
                  color="success"
                  variant="flat"
                  @click.stop="acceptAiPaperwork(item)"
                >
                  Accetta di lavorarla
                </VBtn>
              </div>
            </template>

            
            <template #item.created_at="{ item }">
              <div class="text-high-emphasis text-body-1">
                {{ new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(item.created_at)) }}
              </div>
            </template>

            
            <template #bottom>
              <TablePagination
                v-model:page="aiPaperworksPage"
                :items-per-page="aiPaperworksItemsPerPage"
                :total-items="totalAiPaperworks"
              />
            </template>
          </VDataTableServer>

          <VSnackbar
            v-model="isAiSnackbarVisible"
            :color="aiSnackbarColor"
            location="top end"
            variant="flat"
          >
            {{ aiSnackbarMessage }}
          </VSnackbar>
        </VCard>
      </VCol>
    </VRow>

    <VRow class="mt-6">
      <VCol cols="12">
        <VCard variant="outlined" class="pa-4">
          <VCardTitle class="text-h5 mb-4">
            Pratiche sospese
          </VCardTitle>
          <VDataTableServer
            v-model:items-per-page="itemsPerPage"
            v-model:page="page"
            :items="clientData"
            :items-length="totalItems"
            :headers="[
              { title: 'ID', key: 'id', width: '80' },
              { title: 'Agente', key: 'agent', sortable: false },
              { title: 'Cliente', key: 'customer', sortable: false },
              { title: 'Prodotto', key: 'product', sortable: false },
              { title: 'Stato', key: 'state' },
              { title: 'Backoffice', key: 'backoffice', sortable: false },
              { title: 'Ticket', key: 'hasTicket', sortable: false, align: 'center', width: '80' },
              { title: 'Data', key: 'created_at', sortable: false },
            ]"
            class="text-no-wrap"
            @update:options="updateOptions"
          >
            <!-- Paperwork ID -->
            <template #item.id="{ item }">
              <RouterLink
                :to="{ name: 'workflow-paperworks-id', params: { id: item.id } }"
                class="font-weight-medium text-link"
              >
                {{ item.id }}
              </RouterLink>
            </template>

            <!-- Agent -->
            <template #item.agent="{ item }">
              <RouterLink
                :to="{ name: 'admin-users-id', params: { id: item.agent_id } }"
                class="font-weight-medium text-link"
              >
                {{ item.agent }}
              </RouterLink>
            </template>

            <!-- Customer -->
            <template #item.customer="{ item }">
              <RouterLink
                :to="{ name: 'workflow-customers-id', params: { id: item.customer_id } }"
                class="font-weight-medium text-link"
              >
                {{ item.customer }}
              </RouterLink>
            </template>

            <!-- Product -->
            <template #item.product="{ item }">
              <RouterLink
                :to="{ name: 'configuration-products-id', params: { id: item.product_id } }"
                class="font-weight-medium text-link"
              >
                {{ item.product }}
              </RouterLink>
            </template>

            <!-- State -->
            <template #item.state="{ item }">
              <StatusChip 
                :status="item.state" 
                size="small"
                fallback-style="text"
                class="compact-chip"
              />
            </template>

            <!-- Backoffice -->
            <template #item.backoffice="{ item }">
              <span v-if="item.backoffice">
                {{ item.backoffice }}
              </span>
              <span v-else>
                N/A
              </span>
            </template>

            <!-- Has Ticket -->
            <template #item.hasTicket="{ item }">
              <VIcon
                v-if="item.hasTicket"
                icon="tabler-bell"
                color="warning"
                size="20"
              />
            </template>

            <!-- pagination -->
            <template #bottom>
              <TablePagination
                v-model:page="page"
                :items-per-page="itemsPerPage"
                :total-items="totalItems"
              />
            </template>
          </VDataTableServer>
        </VCard>
      </VCol>
    </VRow>

    <!-- Tickets Section -->
    <VRow class="mt-6" data-section="tickets">
      <VCol cols="12">
        <VCard variant="outlined" class="pa-4">
          <VCardTitle class="text-h5 mb-4">
            Tickets
          </VCardTitle>
          <VDataTableServer
            v-model:items-per-page="ticketsItemsPerPage"
            v-model:page="ticketsPage"
            :items="ticketsData"
            :items-length="totalTickets"
            :headers="ticketTableHeaders"
            class="text-no-wrap"
            @update:options="updateTicketsOptions"
          >
            <!-- Ticket ID -->
            <template #item.id="{ item }">
              <RouterLink
                :to="{ name: 'workflow-tickets-id', params: { id: item.id } }"
                class="font-weight-medium text-link"
              >
                {{ item.id }}
              </RouterLink>
            </template>

            <!-- Paperwork -->
            <template #item.paperwork_id="{ item }">
              <RouterLink
                :to="{ name: 'workflow-paperworks-id', params: { id: item.paperwork_id } }"
                class="font-weight-medium text-link"
              >
                {{ item.paperwork_id }}
              </RouterLink>
            </template>

            <!-- Customer -->
            <template #item.customer="{ item }">
              <RouterLink
                :to="{ name: 'workflow-customers-id', params: { id: item.paperwork.customer.id } }"
                class="font-weight-medium text-link"
              >
                {{ item.paperwork.customer.name ? item.paperwork.customer.name : item.paperwork.customer.business_name }}
              </RouterLink>
            </template>

            <!-- Title -->
            <template #item.title="{ item }">
              <div class="text-high-emphasis text-body-1">
                {{ item.title }}
              </div>
            </template>

            <!-- Status -->
            <template #item.status="{ item }">
              <div class="text-high-emphasis text-body-1">
                {{ ticketStatusText(item.status) }}
              </div>
            </template>

            <!-- Agent -->
            <template #item.agent="{ item }">
              <div class="text-high-emphasis text-body-1">
                {{ item.paperwork.user ? [item.paperwork.user.name, item.paperwork.user.last_name].join(' ') : 'N/A' }}
              </div>
            </template>

            <!-- Created By -->
            <template #item.created_by="{ item }">
              <div class="text-high-emphasis text-body-1">
                {{ [item.created_by.name, item.created_by.last_name].join(' ') }}
              </div>
            </template>

            <!-- Created At -->
            <template #item.created_at="{ item }">
              <div class="text-high-emphasis text-body-1">
                {{ item.created_at }}
              </div>
            </template>

            <!-- Azioni (solo per struttura/agente) -->
            <template
              v-if="isStrutturaOrAgente"
              #item.actions="{ item }"
            >
              <VBtn
                size="small"
                color="info"
                variant="tonal"
                class="compact-btn"
                :to="{ name: 'workflow-tickets-id', params: { id: item.id } }"
                title="Apri ticket"
              >
                Vedi
              </VBtn>
            </template>

            <!-- pagination -->
            <template #bottom>
              <TablePagination
                v-model:page="ticketsPage"
                :items-per-page="ticketsItemsPerPage"
                :total-items="totalTickets"
              />
            </template>
          </VDataTableServer>
        </VCard>
      </VCol>
    </VRow>

    <!-- Filters for Stats -->
    <VRow class="mt-6">
      <VCol cols="12">
        <VCard variant="outlined" class="pa-4">
          <VCardTitle class="text-h6 mb-4">
            Filtri Statistiche
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="4">
                <AppSelect
                  v-model="chartFilters.brandId"
                  label="Brand"
                  placeholder="Seleziona Brand"
                  :items="brands"
                  :loading="isLoadingBrands"
                  item-title="name"
                  item-value="id"
                  clearable
                />
              </VCol>
              <VCol cols="12" md="4">
                <AppSelect
                  v-model="chartFilters.productId"
                  label="Prodotto"
                  placeholder="Seleziona Prodotto"
                  :items="products"
                  :loading="isLoadingProducts"
                  item-title="name"
                  item-value="id"
                  clearable
                />
              </VCol>
              <VCol cols="12" md="4">
                <AppSelect
                  v-if="isAdmin"
                  v-model="chartFilters.agentId"
                  label="Agente"
                  placeholder="Seleziona Agente"
                  :items="agents"
                  :loading="isLoadingAgents"
                  item-title="name"
                  item-value="id"
                  clearable
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Statistics Section -->
    <VRow class="mt-6">
      <!-- Today's Stats -->
      <VCol cols="12" md="4">
        <VCard
          variant="outlined"
          class="pa-4 chart-card"
        >
          <div 
            class="chart-title-clickable mb-4"
            @click="navigateToTodayPaperworks"
          >
            <span class="text-h6">Pratiche di Oggi</span>
            <VIcon 
              icon="tabler-external-link" 
              size="20" 
              class="ml-2"
            />
          </div>
          <VueApexCharts
            :key="'today-donut-' + JSON.stringify(todayDonutOptions.labels)"
            :options="todayDonutOptions"
            :series="todayDonutOptions.series"
            height="350"
          />
        </VCard>
      </VCol>

      <!-- Current Month Stats -->
      <VCol cols="12" md="4">
        <VCard
          variant="outlined"
          class="pa-4 chart-card"
        >
          <div 
            class="chart-title-clickable mb-4"
            @click="navigateToCurrentMonthPaperworks"
          >
            <span class="text-h6">Pratiche del Mese</span>
            <VIcon 
              icon="tabler-external-link" 
              size="20" 
              class="ml-2"
            />
          </div>
          <VueApexCharts
            :key="'current-month-donut-' + JSON.stringify(currentMonthDonutOptions.labels)"
            :options="currentMonthDonutOptions"
            :series="currentMonthDonutOptions.series"
            height="350"
          />
        </VCard>
      </VCol>

      <!-- Previous Month Stats -->
      <VCol cols="12" md="4">
        <VCard
          variant="outlined"
          class="pa-4 chart-card"
        >
          <div 
            class="chart-title-clickable mb-4"
            @click="navigateToPreviousMonthPaperworks"
          >
            <span class="text-h6">Pratiche del Mese Precedente</span>
            <VIcon 
              icon="tabler-external-link" 
              size="20" 
              class="ml-2"
            />
          </div>
          <VueApexCharts
            :key="'last-month-donut-' + JSON.stringify(lastMonthDonutOptions.labels)"
            :options="lastMonthDonutOptions"
            :series="lastMonthDonutOptions.series"
            height="350"
          />
        </VCard>
      </VCol>
    </VRow>

    <!-- Time Series Charts -->
    <VRow class="mt-6">
      <VCol cols="12">
        <VCard variant="outlined" class="pa-4">
          <div 
            class="chart-title-clickable mb-4"
            @click="navigateToTodayPaperworks"
          >
            <span class="text-h6">Pratiche di Oggi</span>
            <VIcon 
              icon="tabler-external-link" 
              size="20" 
              class="ml-2"
            />
          </div>
          <VueApexCharts
            type="area"
            height="350"
            :key="'today-' + JSON.stringify(todayChartOptions.value)"
            :options="todayChartOptions"
            :series="todayChartOptions.series"
          />
        </VCard>
      </VCol>
    </VRow>

    <VRow class="mt-6">
      <VCol cols="12">
        <VCard variant="outlined" class="pa-4">
          <div 
            class="chart-title-clickable mb-4"
            @click="navigateToCurrentMonthPaperworks"
          >
            <span class="text-h6">Trend Mensile</span>
            <VIcon 
              icon="tabler-external-link" 
              size="20" 
              class="ml-2"
            />
          </div>
          <VueApexCharts
            type="area"
            height="350"
            :key="'month-' + JSON.stringify(monthChartOptions.value)"
            :options="monthChartOptions"
            :series="monthChartOptions.series"
          />
        </VCard>
      </VCol>
    </VRow>

    <VRow class="mt-6">
      <VCol cols="12">
        <VCard variant="outlined" class="pa-4">
          <div 
            class="chart-title-clickable mb-4"
            @click="navigateToPreviousMonthPaperworks"
          >
            <span class="text-h6">Trend Mese Precedente</span>
            <VIcon 
              icon="tabler-external-link" 
              size="20" 
              class="ml-2"
            />
          </div>
          <VueApexCharts
            type="area"
            height="350"
            :key="'last-month-' + JSON.stringify(lastMonthChartOptions.value)"
            :options="lastMonthChartOptions"
            :series="lastMonthChartOptions.series"
          />
        </VCard>
      </VCol>
    </VRow>

    
  </div>

  <!-- Modal per pratiche non assegnate -->
  <AIPaperworkUnassignedModal
    v-model="unassignedModalOpen"
    :brand-name="unassignedModalBrandName"
  />
  
  <!-- Modal per pratiche sospese -->
  <SuspendedPaperworksModal
    v-model="isSuspendedModalVisible"
  />
  
  <!-- Modal per ticket -->
  <TicketsModal
    v-model="isTicketsModalVisible"
  />
  
</template>

<style lang="scss" scoped>
.quick-links-counters {
  .quick-link-card {
    cursor: pointer;
    transition: all 0.3s ease;
    background-color: rgb(var(--v-theme-primary));
    border: 1px solid rgb(var(--v-theme-primary));
    height: 100%;

    :deep(.v-icon),
    .v-card-title {
      color: white;
    }

    &:hover {
      background-color: rgb(var(--v-theme-surface));
      
      :deep(.v-icon),
      .v-card-title {
        color: rgb(var(--v-theme-primary));
      }
    }
  }

  .counter-card {
    cursor: pointer;
    transition: all 0.3s ease;
    height: 100%;
    
    &:hover {
      transform: translateY(-4px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    :deep(.v-card-item) {
      padding: 24px;
    }
    
    :deep(.v-card-title) {
      margin-bottom: 8px;
    }
    
    :deep(.v-card-subtitle) {
      opacity: 1;
      color: rgb(var(--v-theme-on-surface));
    }
  }
}

.v-progress-circular {
  margin: 0 auto;
  display: block;
}

:deep(.v-card) {
  border-radius: 8px;
  background-color: rgb(var(--v-theme-surface));
}

:deep(.v-data-table) {
  border-radius: 8px;
  background-color: rgb(var(--v-theme-surface));
}

.search-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 16px;
}

.chart-card {
  min-height: 418px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;

  :deep(.apexcharts-canvas) {
    margin: 0 auto;
  }
}

.chart-title-clickable {
  display: flex;
  align-items: center;
  cursor: pointer;
  transition: all 0.2s ease;
  width: 100%;
  
  &:hover {
    color: rgb(var(--v-theme-primary));
    
    :deep(.v-icon) {
      color: rgb(var(--v-theme-primary));
    }
  }
  
  :deep(.v-icon) {
    transition: all 0.2s ease;
  }
}

</style>
