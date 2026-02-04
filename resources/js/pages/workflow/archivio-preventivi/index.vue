<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'preventivi',
  },
})

// Controlla se l'utente è un agente
const loggedInUser = useCookie('userData').value
const isAgent = loggedInUser?.roles?.some(role => role.name === 'agente')

// 👉 Store
const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(40)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

// Filtri
const selectedAgent = ref()
const selectedCustomer = ref()
const selectedRoof = ref('')
const selectedExposition = ref('')
const selectedGeoArea = ref('')
const selectedPaymentMode = ref([])
const selectedMaintenanceOption = ref('')
const selectedInsuranceOption = ref('')
const maintenanceCostMin = ref('')
const maintenanceCostMax = ref('')
const insuranceCostMin = ref('')
const insuranceCostMax = ref('')
const productionEstimatedMin = ref('')
const productionEstimatedMax = ref('')
const selfConsumptionSavingMin = ref('')
const selfConsumptionSavingMax = ref('')
const excessSaleRidMin = ref('')
const excessSaleRidMax = ref('')
const cerIncentiveMin = ref('')
const cerIncentiveMax = ref('')
const taxDeductionMin = ref('')
const taxDeductionMax = ref('')

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Preventivo',
    key: 'id_preventivo',
  },
  {
    title: 'Data',
    key: 'created_at',
  },
  {
    title: 'Cliente',
    key: 'cliente',
    sortable: false,
  },
  {
    title: 'Agente',
    key: 'agente',
    sortable: false,
  },
  {
    title: 'Stato',
    key: 'stato',
  },
  {
    title: 'Tetto',
    key: 'tetto_salvato',
  },
  {
    title: 'Esposizione/Area',
    key: 'esposizione_salvata',
  },
  {
    title: 'Pagamento',
    key: 'modalita_pagamento_salvata',
  },
  {
    title: 'Produzione',
    key: 'produzione_annua_stimata',
  },
  {
    title: 'Azioni',
    key: 'actions',
    sortable: false,
  },
]

// Costruisci i parametri della query
const queryParams = computed(() => {
  const params = {
    q: searchQuery.value,
    itemsPerPage: itemsPerPage.value,
    page: page.value,
  }

  if (sortBy.value) {
    params.sortBy = sortBy.value
  }
  if (orderBy.value) {
    params.orderBy = orderBy.value
  }
  if (selectedAgent.value) {
    params.agentId = selectedAgent.value
  }
  if (selectedCustomer.value) {
    params.customerId = selectedCustomer.value
  }
  if (selectedRoof.value) {
    params.roof = selectedRoof.value
  }
  if (selectedExposition.value) {
    params.exposition = selectedExposition.value
  }
  if (selectedGeoArea.value) {
    params.geoArea = selectedGeoArea.value
  }
  if (selectedPaymentMode.value && selectedPaymentMode.value.length > 0) {
    params.paymentMode = selectedPaymentMode.value.join(',')
  }
  if (selectedMaintenanceOption.value) {
    params.maintenanceOption = selectedMaintenanceOption.value
  }
  if (selectedInsuranceOption.value) {
    params.insuranceOption = selectedInsuranceOption.value
  }
  if (maintenanceCostMin.value) {
    params.maintenanceCostMin = maintenanceCostMin.value
  }
  if (maintenanceCostMax.value) {
    params.maintenanceCostMax = maintenanceCostMax.value
  }
  if (insuranceCostMin.value) {
    params.insuranceCostMin = insuranceCostMin.value
  }
  if (insuranceCostMax.value) {
    params.insuranceCostMax = insuranceCostMax.value
  }
  if (productionEstimatedMin.value) {
    params.productionEstimatedMin = productionEstimatedMin.value
  }
  if (productionEstimatedMax.value) {
    params.productionEstimatedMax = productionEstimatedMax.value
  }
  if (selfConsumptionSavingMin.value) {
    params.selfConsumptionSavingMin = selfConsumptionSavingMin.value
  }
  if (selfConsumptionSavingMax.value) {
    params.selfConsumptionSavingMax = selfConsumptionSavingMax.value
  }
  if (excessSaleRidMin.value) {
    params.excessSaleRidMin = excessSaleRidMin.value
  }
  if (excessSaleRidMax.value) {
    params.excessSaleRidMax = excessSaleRidMax.value
  }
  if (cerIncentiveMin.value) {
    params.cerIncentiveMin = cerIncentiveMin.value
  }
  if (cerIncentiveMax.value) {
    params.cerIncentiveMax = cerIncentiveMax.value
  }
  if (taxDeductionMin.value) {
    params.taxDeductionMin = taxDeductionMin.value
  }
  if (taxDeductionMax.value) {
    params.taxDeductionMax = taxDeductionMax.value
  }

  return params
})

const {
  data: preventiviData,
  execute: fetchPreventivi,
} = await useApi(createUrl('/preventivi', {
  query: queryParams,
}))

const preventivi = computed(() => preventiviData.value?.preventivi || [])
const totalPreventivi = computed(() => preventiviData.value?.totalPreventivi || 0)

// Carica agenti
const agents = ref([])
const fetchAgents = async () => {
  agents.value = []
  try {
    const response = await $api('/agents?itemsPerPage=99999999&select=1')
    if (response?.agents) {
      for (let i = 0; i < response.agents.length; i++) {
        agents.value.push({
          title: [response.agents[i].name, response.agents[i].last_name].join(' '),
          value: response.agents[i].id,
        })
      }
    }
  } catch (error) {
    console.error('Errore nel caricamento agenti:', error)
  }
}
if (useAbility().can('view', 'users')) {
  fetchAgents()
}

// Carica clienti
const customers = ref([])
const fetchCustomers = async () => {
  customers.value = []
  try {
    const response = await $api('/customers?itemsPerPage=99999999&select=1')
    if (response?.customers) {
      for (let i = 0; i < response.customers.length; i++) {
        const customer = response.customers[i]
        customers.value.push({
          title: customer.business_name || [customer.name, customer.last_name].join(' ') || `Cliente #${customer.id}`,
          value: customer.id,
        })
      }
    }
  } catch (error) {
    console.error('Errore nel caricamento clienti:', error)
  }
}
fetchCustomers()

// Opzioni per i filtri
const roofOptions = ref([
  { title: 'Piano', value: 'piano' },
  { title: 'A falda', value: 'A falda' },
  { title: 'Inclinato', value: 'Inclinato' },
])

const expositionOptions = ref([
  { title: 'Sud', value: 'sud' },
  { title: 'Sud Est', value: 'sud est' },
  { title: 'Sud Ovest', value: 'sud ovest' },
  { title: 'Est', value: 'est' },
  { title: 'Ovest', value: 'ovest' },
  { title: 'Nord', value: 'nord' },
])

const geoAreaOptions = ref([
  { title: 'Nord', value: 'nord' },
  { title: 'Centro', value: 'centro' },
  { title: 'Sud', value: 'sud' },
  { title: 'Isole', value: 'isole' },
])

const paymentModeOptions = ref([
  { title: 'Bonifico', value: 'bonifico' },
  { title: 'Finanziamento', value: 'finanziamento' },
])

const maintenanceOptions = ref([
  { title: 'Sì', value: 'si' },
  { title: 'No', value: 'no' },
])

const insuranceOptions = ref([
  { title: 'Sì', value: 'si' },
  { title: 'No', value: 'no' },
])

const getCustomerName = (customer) => {
  if (!customer) {
    return 'N/A'
  }
  if (customer.business_name) {
    return customer.business_name
  } else if (customer.name) {
    return [customer.name, customer.last_name].join(' ')
  } else {
    return `Cliente #${customer.id}`
  }
}

const getAgentName = (agent) => {
  if (!agent) {
    return 'N/A'
  }
  return [agent.name, agent.last_name].join(' ')
}

// Export
const isExporting = ref(false)
const exportPreventivi = async () => {
  isExporting.value = true
  try {
    const params = { ...queryParams.value, export: 'true' }
    const queryString = new URLSearchParams(params).toString()
    const data = await $api(`/preventivi?${queryString}`, {
      method: 'GET',
      responseType: 'blob'
    })

    const fileName = `preventivi_${new Date().toISOString().slice(0, 10)}.xlsx`

    // Assicurati che il tipo MIME sia corretto per Safari
    const blobType = data.type || 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    const blob = new Blob([data], { type: blobType })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', fileName)
    
    // Safari richiede che il link sia aggiunto al DOM prima del click
    document.body.appendChild(link)
    link.click()
    
    // Piccolo delay per Safari prima della pulizia
    setTimeout(() => {
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
    }, 100)
  } catch (error) {
    console.error('Errore nell\'esportazione preventivi:', error)
  } finally {
    isExporting.value = false
  }
}

const viewPdf = async (preventivoId) => {
  try {
    const response = await $api(`/preventivi/download/${preventivoId}`)
    if (response && response.downloadUrl) {
      window.open(response.downloadUrl, '_blank')
    } else {
      console.error('URL di download non disponibile')
    }
  } catch (error) {
    console.error('Errore nel caricamento del PDF:', error)
  }
}

const downloadPdf = async (preventivo) => {
  try {
    const preventivoId = typeof preventivo === 'object' ? preventivo.id_preventivo : preventivo
    
    // Richiedi il file direttamente come blob
    const response = await $api(`/preventivi/download/${preventivoId}`, {
      responseType: 'blob'
    })

    if (response) {
      // Costruisci il nome del file: <id> <Nome> <Cognome>.pdf
      let fileName = `${preventivoId}`
      if (typeof preventivo === 'object' && preventivo.cliente) {
        const cliente = preventivo.cliente
        if (cliente.name || cliente.last_name) {
          const nome = cliente.name || ''
          const cognome = cliente.last_name || ''
          const nomeCompleto = [nome, cognome].filter(Boolean).join(' ')
          if (nomeCompleto) {
            fileName = `${preventivoId} ${nomeCompleto}.pdf`
          }
        }
      }
      if (!fileName.endsWith('.pdf')) {
        fileName += '.pdf'
      }
      
      // Crea un URL temporaneo per il blob
      const url = window.URL.createObjectURL(response)
      
      // Crea il link e cliccalo
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', fileName)
      
      document.body.appendChild(link)
      link.click()
      
      // Pulizia
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
    } else {
      console.error('Download non riuscito o risposta vuota')
    }
  } catch (error) {
    console.error('Errore nel download del PDF:', error)
  }
}
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filtri</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <!-- Agente -->
          <VCol
            v-if="$can('view', 'users') && !isAgent"
            cols="12"
            sm="6"
            md="3"
          >
            <AppAutocomplete
              v-model="selectedAgent"
              label="Agente"
              clearable
              :items="agents"
              item-title="title"
              item-value="value"
              placeholder="Seleziona un agente"
            />
          </VCol>

          <!-- Cliente -->
          <VCol
            cols="12"
            :sm="isAgent ? '12' : '6'"
            :md="isAgent ? '12' : '3'"
          >
            <AppAutocomplete
              v-model="selectedCustomer"
              label="Cliente"
              clearable
              :items="customers"
              item-title="title"
              item-value="value"
              placeholder="Seleziona un cliente"
            />
          </VCol>

          <!-- Tetto -->
          <VCol
            v-if="!isAgent"
            cols="12"
            sm="6"
            md="3"
          >
            <AppAutocomplete
              v-model="selectedRoof"
              label="Tipo Tetto"
              clearable
              :items="roofOptions"
              placeholder="Seleziona tipo tetto"
            />
          </VCol>

          <!-- Esposizione -->
          <VCol
            v-if="!isAgent"
            cols="12"
            sm="6"
            md="3"
          >
            <AppAutocomplete
              v-model="selectedExposition"
              label="Esposizione"
              clearable
              :items="expositionOptions"
              placeholder="Seleziona esposizione"
            />
          </VCol>

          <!-- Area Geografica -->
          <VCol
            v-if="!isAgent"
            cols="12"
            sm="6"
            md="3"
          >
            <AppAutocomplete
              v-model="selectedGeoArea"
              label="Area Geografica"
              clearable
              :items="geoAreaOptions"
              placeholder="Seleziona area geografica"
            />
          </VCol>

          <!-- Modalità Pagamento -->
          <VCol
            v-if="!isAgent"
            cols="12"
            sm="6"
            md="3"
          >
            <AppAutocomplete
              v-model="selectedPaymentMode"
              label="Modalità Pagamento"
              clearable
              :items="paymentModeOptions"
              multiple
              chips
              placeholder="Seleziona modalità pagamento"
            />
          </VCol>

          <!-- Opzione Manutenzione -->
          <VCol
            v-if="!isAgent"
            cols="12"
            sm="6"
            md="3"
          >
            <AppAutocomplete
              v-model="selectedMaintenanceOption"
              label="Manutenzione"
              clearable
              :items="maintenanceOptions"
              placeholder="Seleziona opzione manutenzione"
            />
          </VCol>

          <!-- Opzione Assicurazione -->
          <VCol
            v-if="!isAgent"
            cols="12"
            sm="6"
            md="3"
          >
            <AppAutocomplete
              v-model="selectedInsuranceOption"
              label="Assicurazione"
              clearable
              :items="insuranceOptions"
              placeholder="Seleziona opzione assicurazione"
            />
          </VCol>
        </VRow>

        <!-- Range di costi e valori -->
        <VRow v-if="!isAgent" class="mt-2">
          <!-- Costo Manutenzione -->
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppTextField
              v-model="maintenanceCostMin"
              label="Costo Manutenzione Min (€)"
              type="number"
              clearable
              placeholder="Min"
            />
          </VCol>
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppTextField
              v-model="maintenanceCostMax"
              label="Costo Manutenzione Max (€)"
              type="number"
              clearable
              placeholder="Max"
            />
          </VCol>

          <!-- Costo Assicurazione -->
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppTextField
              v-model="insuranceCostMin"
              label="Costo Assicurazione Min (€)"
              type="number"
              clearable
              placeholder="Min"
            />
          </VCol>
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppTextField
              v-model="insuranceCostMax"
              label="Costo Assicurazione Max (€)"
              type="number"
              clearable
              placeholder="Max"
            />
          </VCol>

          <!-- Produzione Stimata -->
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppTextField
              v-model="productionEstimatedMin"
              label="Produzione Stimata Min (kWh)"
              type="number"
              clearable
              placeholder="Min"
            />
          </VCol>
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppTextField
              v-model="productionEstimatedMax"
              label="Produzione Stimata Max (kWh)"
              type="number"
              clearable
              placeholder="Max"
            />
          </VCol>

          <!-- Risparmio Autoconsumo -->
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppTextField
              v-model="selfConsumptionSavingMin"
              label="Risparmio Autoconsumo Min (€)"
              type="number"
              clearable
              placeholder="Min"
            />
          </VCol>
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppTextField
              v-model="selfConsumptionSavingMax"
              label="Risparmio Autoconsumo Max (€)"
              type="number"
              clearable
              placeholder="Max"
            />
          </VCol>

          <!-- Vendita Eccedenze RID -->
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppTextField
              v-model="excessSaleRidMin"
              label="Vendita Eccedenze RID Min (€)"
              type="number"
              clearable
              placeholder="Min"
            />
          </VCol>
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppTextField
              v-model="excessSaleRidMax"
              label="Vendita Eccedenze RID Max (€)"
              type="number"
              clearable
              placeholder="Max"
            />
          </VCol>

          <!-- Incentivo CER -->
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppTextField
              v-model="cerIncentiveMin"
              label="Incentivo CER Min (€)"
              type="number"
              clearable
              placeholder="Min"
            />
          </VCol>
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppTextField
              v-model="cerIncentiveMax"
              label="Incentivo CER Max (€)"
              type="number"
              clearable
              placeholder="Max"
            />
          </VCol>

          <!-- Detrazione Fiscale -->
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppTextField
              v-model="taxDeductionMin"
              label="Detrazione Fiscale Min (€)"
              type="number"
              clearable
              placeholder="Min"
            />
          </VCol>
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppTextField
              v-model="taxDeductionMax"
              label="Detrazione Fiscale Max (€)"
              type="number"
              clearable
              placeholder="Max"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3 d-flex gap-3">
          <AppSelect
            :model-value="itemsPerPage"
            :items="[
              { value: 10, title: '10' },
              { value: 25, title: '25' },
              { value: 40, title: '40' },
              { value: 50, title: '50' },
              { value: 100, title: '100' },
              { value: 9999999, title: 'All' },
            ]"
            style="inline-size: 6.25rem;"
            @update:model-value="itemsPerPage = parseInt($event, 10)"
          />
        </div>
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
          <!-- 👉 Search  -->
          <div style="inline-size: 15.625rem;">
            <AppTextField
              v-model="searchQuery"
              placeholder="Cerca"
            />
          </div>

          <!-- 👉 Export button -->
          <VBtn
            variant="tonal"
            color="primary"
            :prepend-icon="isExporting ? 'tabler-loader' : 'tabler-download'"
            :disabled="isExporting"
            @click="exportPreventivi"
          >
            Esporta
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="preventivi"
        :items-length="totalPreventivi"
        :headers="headers"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
        <!-- Numero Preventivo -->
        <template #item.numero_preventivo="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.numero_preventivo || 'N/A' }}
            </div>
          </div>
        </template>

        <!-- Data Preventivo -->
        <template #item.created_at="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.created_at ? new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(new Date(item.created_at)) : 'N/A' }}
            </div>
          </div>
        </template>

        <!-- Cliente -->
        <template #item.cliente="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ getCustomerName(item.cliente) }}
            </div>
          </div>
        </template>

        <!-- Agente -->
        <template #item.agente="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ getAgentName(item.agente) }}
            </div>
          </div>
        </template>

        <!-- Stato -->
        <template #item.stato="{ item }">
          <div class="d-flex align-center gap-x-2">
            <VChip
              :color="item.stato === 'confermato' ? 'success' : item.stato === 'in_attesa' ? 'warning' : 'default'"
              size="small"
              label
              class="text-capitalize"
            >
              {{ item.stato || 'N/A' }}
            </VChip>
          </div>
        </template>

        <!-- Tetto -->
        <template #item.tetto_salvato="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.tetto_salvato || 'N/A' }}
            </div>
          </div>
        </template>

        <!-- Esposizione/Area -->
        <template #item.esposizione_salvata="{ item }">
          <div class="d-flex flex-column gap-y-1">
            <!-- Esposizione -->
            <div v-if="item.esposizione_salvata" class="text-high-emphasis text-body-1" style="font-size: 0.875rem;">
              {{ item.esposizione_salvata }}
            </div>
            <!-- Area Geografica -->
            <div v-if="item.area_geografica_salvata" class="d-flex align-center gap-x-1" style="font-size: 0.75rem; opacity: 0.8;">
              <VIcon
                icon="tabler-map"
                size="12"
                color="secondary"
                style="opacity: 0.7;"
              />
              <span>{{ item.area_geografica_salvata }}</span>
            </div>
            <div v-if="!item.esposizione_salvata && !item.area_geografica_salvata" class="text-high-emphasis text-body-1">
              N/A
            </div>
          </div>
        </template>

        <!-- Modalità Pagamento -->
        <template #item.modalita_pagamento_salvata="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.modalita_pagamento_salvata || 'N/A' }}
            </div>
          </div>
        </template>

        <!-- Produzione Stimata -->
        <template #item.produzione_annua_stimata="{ item }">
          <div class="d-flex align-center gap-x-2">
            <div class="text-high-emphasis text-body-1">
              {{ item.produzione_annua_stimata ? `${item.produzione_annua_stimata.toLocaleString('it-IT')} kWh` : 'N/A' }}
            </div>
          </div>
        </template>

        <!-- Azioni -->
        <template #item.actions="{ item }">
          <div class="d-flex align-center gap-x-2">
          
            <VBtn
              v-if="item.id_preventivo"
              icon
              size="x-small"
              color="primary"
              variant="text"
              @click="downloadPdf(item)"
            >
              <VIcon
                size="22"
                icon="tabler-download"
              />
              <VTooltip
                activator="parent"
                location="top"
              >
                Scarica PDF
              </VTooltip>
            </VBtn>
          </div>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalPreventivi"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
</template>

<style>
.tabler-loader,
.tabler-fidget-spinner,
.tabler-loader-3,
.tabler-loader-quarter,
.tabler-refresh-dot,
.tabler-reload,
.tabler-loader-2 {
  animation: spin-animation .8s infinite;
}

@keyframes spin-animation {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(359deg);
  }
}
</style>

