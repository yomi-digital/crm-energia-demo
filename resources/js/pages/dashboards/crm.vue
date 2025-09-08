<script setup>
import { onMounted, ref } from 'vue'
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

let quickLinks = [
  { name: 'Clienti', icon: 'tabler-users', to: '/workflow/customers' },
]

// Solo se NON è backoffice, aggiungi "Crea Pratica AI"
if (!isBackoffice) {
  quickLinks.unshift({ name: 'Crea Pratica AI', icon: 'tabler-file-plus', action: () => document.querySelector('#ai-contract-upload-btn').click() })
}

if (isAdmin) {
  quickLinks.push({ name: 'Report', icon: 'tabler-report', to: '/reports/admin' })
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
    text: title,
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
    text: title,
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
  ...getDonutOptions('Pratiche di Oggi'),
  series: [],
  labels: [],
})

const currentMonthDonutOptions = ref({
  ...getDonutOptions('Pratiche del Mese'),
  series: [],
  labels: [],
})

const lastMonthDonutOptions = ref({
  ...getDonutOptions('Pratiche del Mese Precedente'),
  series: [],
  labels: [],
})

const todayChartOptions = ref({
  ...getAreaOptions('Pratiche di Oggi'),
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
  ...getAreaOptions('Trend Mensile'),
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
  ...getAreaOptions('Trend Mese Precedente'),
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
    const response = await $api('/ai-paperworks', {
      params: {
        status: 2,
        page: aiPaperworksPage.value,
        itemsPerPage: aiPaperworksItemsPerPage.value,
      },
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

// Helper function for ticket status
const ticketStatusText = (status) => {
  return ['Aperto', 'In Lavorazione', 'Risolto'][status - 1]
}

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
      series: Object.values(data.today),
      labels: Object.keys(data.today),
    }
    
    currentMonthDonutOptions.value = {
      ...currentMonthDonutOptions.value,
      series: Object.values(data.currentMonth),
      labels: Object.keys(data.currentMonth),
    }
    
    lastMonthDonutOptions.value = {
      ...lastMonthDonutOptions.value,
      series: Object.values(data.previousMonth),
      labels: Object.keys(data.previousMonth),
    }
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
    todayChartOptions.value = {
      ...todayChartOptions.value,
      series: todaySeries
    }

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
    monthChartOptions.value = {
      ...monthChartOptions.value,
      xaxis: {
        ...monthChartOptions.value.xaxis,
        categories: monthCategories
      },
      series: monthSeries
    }

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
    lastMonthChartOptions.value = {
      ...lastMonthChartOptions.value,
      xaxis: {
        ...lastMonthChartOptions.value.xaxis,
        categories: lastMonthCategories
      },
      series: lastMonthSeries
    }

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

// Initial data fetch
onMounted(async () => {
  await fetchPaperworks()
  await fetchAiPaperworks()
  await fetchTickets()
  await fetchStats()
  await fetchTimeSeriesData()
  await loadBrands()
  await loadProducts()
  await loadAgents()
  await loadCustomers()
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
</script>

<template>
  <div>
    <!-- Quick Links -->
    <VRow class="quick-links mb-6">
      <VCol
        v-for="link in quickLinks"
        :key="link.name"
        :cols="quickLinks.length === 2 ? 6 : 4"
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


    <VRow class="mt-6">
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
            :headers="[
              { title: '#', key: 'id', width: '80' },
              { title: 'Agente', key: 'user_id', sortable: false },
              { title: 'File', key: 'filepath', sortable: false },
              { title: 'Stato', key: 'status' },
              { title: 'Data', key: 'created_at', sortable: false },
            ]"
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

            
            <template #item.status="{ item }">
              <VChip
                :color="getStatusChipColor(item.status)"
                size="small"
                class="text-capitalize"
              >
                {{ getStatusText(item.status) }}
              </VChip>
            </template>

            
            <template #item.created_at="{ item }">
              <div class="text-high-emphasis text-body-1">
                {{ item.created_at }}
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
        </VCard>
      </VCol>
    </VRow>

    <VRow class="mt-6">
      <VCol cols="12">
        <VCard variant="outlined" class="pa-4">
          <VCardTitle class="text-h5 mb-4">
            Le mie pratiche
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
              {{ item.state }}
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
    <VRow class="mt-6">
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
            :headers="[
              { title: 'ID', key: 'id', width: '80' },
              { title: 'Pratica', key: 'paperwork_id', sortable: false },
              { title: 'Cliente', key: 'customer', sortable: false },
              { title: 'Titolo', key: 'title', sortable: false },
              { title: 'Stato', key: 'status' },
              { title: 'Creato Da', key: 'created_by', sortable: false },
              { title: 'Data Creazione', key: 'created_at', sortable: false },
            ]"
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
          <VueApexCharts
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
          <VueApexCharts
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
          <VueApexCharts
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
</template>

<style lang="scss" scoped>
.quick-links {
  .quick-link-card {
    cursor: pointer;
    transition: all 0.3s ease;
    background-color: rgb(var(--v-theme-primary));
    border: 1px solid rgb(var(--v-theme-primary));

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
  align-items: center;
  justify-content: center;

  :deep(.apexcharts-canvas) {
    margin: 0 auto;
  }
}
</style>
