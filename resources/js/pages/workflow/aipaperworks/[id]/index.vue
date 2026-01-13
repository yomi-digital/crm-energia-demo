<script setup>
import AIErrorBanner from '@/components/AIErrorBanner.vue'
import AIPaperworkTransfer from '@/components/AIPaperworkTransfer.vue'
import BrandOverrideAlert from '@/components/BrandOverrideAlert.vue'
import ProcessingAIStutteringBanner from '@/components/ProcessingAIStutteringBanner.vue'
import TransferTable from '@/components/TransferTable.vue'
import GeneralErrorDialog from '@/components/dialogs/GeneralErrorDialog.vue'
import { useDebounceFn } from '@vueuse/core'
import { onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

definePage({
  meta: {
    action: 'view',
    subject: 'aipaperworks',
  },
})

const route = useRoute()
const router = useRouter()
const id = route.params.id

// Controlla se l'utente può selezionare l'agente (gestione, struttura, backoffice)
const loggedInUser = useCookie('userData').value
const canSelectAgent = loggedInUser?.roles?.some(role => 
  ['gestione', 'struttura', 'backoffice'].includes(role.name)
)

const isProcessing = ref(false)
let pollingInterval = null

const {
  data: aiPaperwork,
  execute: fetchAIPaperwork,
} = await useApi(`/ai-paperworks/${id}`)

const products = ref([])
const isLoadingProducts = ref(false)

const fetchProducts = async (brandId = null) => {
  isLoadingProducts.value = true
  try {
    let url = '/products/personal?itemsPerPage=999999&enabled=1'
    if (brandId) {
      url += `&brand=${brandId}`
    }
    const response = await $api(url)
    products.value = response.products
  } catch (error) {
    console.error('Failed to load products:', error)
  } finally {
    isLoadingProducts.value = false
  }
}

// Fetch products - sarà chiamato dopo che extractedPaperwork è popolato

const brands = ref([])
const isLoadingBrands = ref(false)

const fetchBrands = async () => {
  isLoadingBrands.value = true
  try {
    const response = await $api('/brands/personal?itemsPerPage=999999&enabled=1')
    brands.value = response.brands || []
  } catch (error) {
    console.error('Failed to load brands:', error)
  } finally {
    isLoadingBrands.value = false
  }
}

// Fetch brands on component mount
await fetchBrands()

// Gestione appuntamenti (identica a PaperworkCustomer.vue)
const appointments = ref([])
const isLoadingAppointments = ref(false)

const mandates = ref([])
const isLoadingMandates = ref(false)

const fetchMandates = async () => {
  isLoadingMandates.value = true
  try {
    const response = await $api('/mandates?itemsPerPage=999999')
    mandates.value = response.mandates.map(mandate => ({
      title: mandate.name,
      value: mandate.id,
    }))
  } catch (error) {
    console.error('Failed to load mandates:', error)
    mandates.value = []
  } finally {
    isLoadingMandates.value = false
  }
}

// Fetch mandates on component mount
await fetchMandates()

// Agenti disponibili per cambiare l'agente della pratica AI
const agents = ref([])
const isLoadingAgents = ref(false)
const selectedAgentId = ref(null)

const fetchAgents = async (brandId = null) => {
  isLoadingAgents.value = true
  agents.value = []
  try {
    let url = '/agents?itemsPerPage=99999999&select=1&structures=1&gestione=1&backoffice=1'
    if (brandId) {
      url += `&brand_id=${brandId}`
    }
    
    const response = await $api(url)
    agents.value = response.agents.map(agent => ({
      title: [agent.name, agent.last_name].filter(Boolean).join(' '),
      value: agent.id,
    }))
    
    // Imposta l'agente corrente come selezionato
    if (aiPaperwork.value?.user_id) {
      selectedAgentId.value = aiPaperwork.value.user_id
    }
  } catch (error) {
    console.error('Failed to load agents:', error)
    agents.value = []
  } finally {
    isLoadingAgents.value = false
  }
}

// Carica gli agenti solo quando l'elaborazione è completata (status 2) e quando cambia il brand
watch(() => [aiPaperwork.value?.status, aiPaperwork.value?.brand_id], ([status, brandId]) => {
  // Carica gli agenti solo se l'elaborazione è completata (status 2)
  if (status === 2) {
    if (brandId) {
      fetchAgents(brandId)
    } else {
      fetchAgents()
    }
  }
}, { immediate: true })

// Watch per aggiornare l'agente selezionato quando cambia l'AI paperwork
watch(() => aiPaperwork.value?.user_id, (userId) => {
  if (userId) {
    selectedAgentId.value = userId
  }
})

const fetchAppointments = async (query) => {
  isLoadingAppointments.value = true
  try {
    const response = await $api('/appointments?itemsPerPage=999999&select=1&q=' + query)
    appointments.value = response.map(appointment => ({
      title: appointment.start + ' - ' + appointment.title,
      value: appointment.id,
    }))
  } catch (error) {
    console.error('Failed to load appointments:', error)
    appointments.value = []
  } finally {
    isLoadingAppointments.value = false
  }
}

const extractedCustomer = ref({})
const extractedPaperwork = ref({})

// Watch per caricare appuntamenti quando si attiva il toggle (solo se c'è cliente)
watch(() => extractedPaperwork.value.is_from_appointment, (isFromAppointment) => {
  if (isFromAppointment && extractedCustomer.value.id) {
    fetchAppointments('')
  }
})

// Watch for changes in the API response and update the refs
watch(() => aiPaperwork.value?.ai_extracted_customer, (newVal) => {
  if (newVal) {
    try {
      extractedCustomer.value = JSON.parse(newVal)
      // Esegui il controllo duplicati all'inizializzazione
      nextTick(() => {
        checkDuplicateCustomer()
      })
    } catch (e) {
      extractedCustomer.value = {}
    }
  }
}, { immediate: true })

watch(() => aiPaperwork.value?.ai_extracted_paperwork, (newVal) => {
  if (newVal) {
    try {
      const parsed = JSON.parse(newVal)
      extractedPaperwork.value = parsed
      // Imposta il brand_id dall'AI paperwork se esiste
      if (aiPaperwork.value?.brand_id) {
        extractedPaperwork.value.brand_id = aiPaperwork.value.brand_id
        console.log('Set brand_id to:', aiPaperwork.value.brand_id)
      }
      // Imposta il mandate_id dall'AI paperwork se esiste
      if (aiPaperwork.value?.mandate_id) {
        extractedPaperwork.value.mandate_id = aiPaperwork.value.mandate_id
        console.log('Set mandate_id to:', aiPaperwork.value.mandate_id)
      }
      
      // Carica i prodotti filtrati per il brand se presente
      const brandId = extractedPaperwork.value.brand_id || null
      fetchProducts(brandId)
    } catch (e) {
      extractedPaperwork.value = {}
      // Carica tutti i prodotti se non ci sono dati estratti
      fetchProducts()
    }
  } else {
    // Carica tutti i prodotti se non ci sono dati
    fetchProducts()
  }
}, { immediate: true })

// Watch separato per inizializzare il brand_id dal paperwork
watch(() => aiPaperwork.value?.brand_id, (brandId) => {
  if (brandId && extractedPaperwork.value) {
    extractedPaperwork.value.brand_id = brandId
    console.log('Watch brand_id updated to:', brandId)
  }
}, { immediate: true })

// Watch separato per inizializzare il mandate_id dal paperwork
watch(() => aiPaperwork.value?.mandate_id, (mandateId) => {
  if (mandateId && extractedPaperwork.value) {
    extractedPaperwork.value.mandate_id = mandateId
    console.log('Watch mandate_id updated to:', mandateId)
  }
}, { immediate: true })

const fetchPostalCode = useDebounceFn(async () => {
  const customer = extractedCustomer.value
  if (!customer.city || !customer.address) return
  
  try {
    const response = await $api('/geocoding/postal-code', {
      params: { 
        city: customer.city, 
        street: customer.address 
      }
    })
    
    if (response && response.postal_code) {
      extractedCustomer.value.zip_code = response.postal_code
    }
  } catch (error) {
    console.error('Error fetching postal code:', error)
  }
}, 800)

watch(
  [() => extractedCustomer.value.city, () => extractedCustomer.value.address],
  ([newCity, newAddress]) => {
    if (newCity && newAddress) {
      fetchPostalCode()
    }
  }
)

// Watch per compilare automaticamente la ragione sociale quando cambia P.IVA
const isSearchingCustomer = ref(false)
watch(() => extractedCustomer.value.vat_number, async (newVatNumber) => {
  if (newVatNumber && newVatNumber.trim() !== '' && !isSearchingCustomer.value) {
    isSearchingCustomer.value = true
    try {
      const response = await $api('/customers-search', {
        method: 'POST',
        body: {
          vat_number: newVatNumber.trim(),
          page: 1,
          itemsPerPage: 1
        }
      })
      if (response.customers && response.customers.length > 0) {
        const customer = response.customers[0]
        if (customer.business_name) {
          extractedCustomer.value.business_name = customer.business_name
        }
      }
    } catch (error) {
      console.error('Errore nella ricerca cliente per P.IVA:', error)
    } finally {
      isSearchingCustomer.value = false
    }
  }
})

// Watch per compilare automaticamente la ragione sociale quando cambia Codice Fiscale
// Priorità sempre a P.IVA: cerca per CF solo se P.IVA è vuota
watch(() => extractedCustomer.value.tax_id_code, async (newTaxIdCode) => {
  // Se c'è P.IVA compilata, ignora il CF e cerca solo per P.IVA
  if (extractedCustomer.value.vat_number && extractedCustomer.value.vat_number.trim() !== '') {
    if (extractedCustomer.value.vat_number.trim() !== '' && !isSearchingCustomer.value) {
      isSearchingCustomer.value = true
      try {
        const response = await $api('/customers-search', {
          method: 'POST',
          body: {
            vat_number: extractedCustomer.value.vat_number.trim(),
            page: 1,
            itemsPerPage: 1
          }
        })
        if (response.customers && response.customers.length > 0) {
          const customer = response.customers[0]
          if (customer.business_name) {
            extractedCustomer.value.business_name = customer.business_name
          }
        }
      } catch (error) {
        console.error('Errore nella ricerca cliente per P.IVA:', error)
      } finally {
        isSearchingCustomer.value = false
      }
    }
    return
  }
  
  // Solo se P.IVA è vuota, cerca per CF
  if (newTaxIdCode && newTaxIdCode.trim() !== '' && !isSearchingCustomer.value) {
    isSearchingCustomer.value = true
    try {
      const response = await $api('/customers-search', {
        method: 'POST',
        body: {
          tax_id_code: newTaxIdCode.trim(),
          page: 1,
          itemsPerPage: 1
        }
      })
      if (response.customers && response.customers.length > 0) {
        const customer = response.customers[0]
        if (customer.business_name) {
          extractedCustomer.value.business_name = customer.business_name
        }
      }
    } catch (error) {
      console.error('Errore nella ricerca cliente per CF:', error)
    } finally {
      isSearchingCustomer.value = false
    }
  }
})

// Watch per ricaricare i prodotti quando cambia il brand selezionato
watch(() => extractedPaperwork.value.brand_id, (newBrandId, oldBrandId) => {
  if (newBrandId !== oldBrandId) {
    // Reset product_id quando cambia il brand
    if (extractedPaperwork.value.product_id) {
      extractedPaperwork.value.product_id = null
    }
    // Ricarica i prodotti filtrati per il nuovo brand
    fetchProducts(newBrandId)
  }
})

const extractedText = computed({
  get() {
    return aiPaperwork.value.extracted_text || ''
  },
  set(val) {
    if (aiPaperwork.value) {
      aiPaperwork.value.extracted_text = val
    }
  }
})

const startPolling = () => {
  // Previeni polling multipli
  if (pollingInterval) {
    clearInterval(pollingInterval)
  }
  
  pollingInterval = setInterval(async () => {
    await fetchAIPaperwork()
    
    // Ferma il polling solo per status finali (2, 8, 9)
    if (aiPaperwork.value.status === 2 || aiPaperwork.value.status === 8 || aiPaperwork.value.status === 9) {
      stopPolling()
    }
  }, 5000) // Polling ogni 5 secondi
}

const stopPolling = () => {
  if (pollingInterval) {
    clearInterval(pollingInterval)
    pollingInterval = null
  }
}

const processDocument = async () => {
  isProcessing.value = true
  try {
    await $api(`/ai-paperworks/${id}/process`, {
      method: 'POST'
    })
    
    // Refresh per vedere nuovo status
    await fetchAIPaperwork()
    
    // Messaggio di conferma
    console.log('Documento reimpostato, lo scheduler lo processerà entro 1 minuto')
  } catch (error) {
    console.error('Errore nel reset del documento:', error)
  } finally {
    isProcessing.value = false
  }
}



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
    case 5:
      return 'Confermato'
    case 8:
      return 'Annullato'
    case 9:
      return 'Errore'
    default:
      return 'Errore'
  }
}

const basename = computed(() => {
  if (!aiPaperwork.value?.filepath) return ''
  return aiPaperwork.value.filepath.split('/').pop()
})

const downloadFile = async () => {
  try {
    const response = await $api(`/ai-paperworks/${id}/download`, {
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([response]))
    const link = document.createElement('a')
    link.href = url
    // Usa il nome originale se disponibile, altrimenti fallback al nome generico
    const filename = aiPaperwork.value?.original_filename || `pratica-${id}.pdf`
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    console.error('Error downloading file:', error)
  }
}

const isSaving = ref(false)
const isUpdatingEmail = ref(false)

const normalizePhone = (p) => p ? p.replace(/\D/g, '') : ''

const emailError = ref('')
const vatError = ref('')
const taxIdError = ref('')
const phoneError = ref('')
const mobileError = ref('')
const duplicateUsers = ref([])
const isDuplicateModalVisible = ref(false)
const overrideDuplicates = ref(true)
const isOverrideInfoModalVisible = ref(false)

const checkDuplicateCustomer = useDebounceFn(async () => {
  // Se lo stato è 5 (Confermato), non fare controlli
  if (aiPaperwork.value?.status === 5) return

  // Reset errors
  emailError.value = ''
  vatError.value = ''
  taxIdError.value = ''
  phoneError.value = ''
  mobileError.value = ''
  duplicateUsers.value = []

  const customer = extractedCustomer.value
  if (!customer) return

  // Gather data
  const params = {}
  
  let searchPhone = customer.mobile || customer.phone
  if (searchPhone) params.telefono = searchPhone
  
  if (customer.email) params.email = customer.email
  if (customer.vat_number) params.vat_number = customer.vat_number
  if (customer.tax_id_code) params.tax_id_code = customer.tax_id_code

  const hasData = Object.keys(params).length > 0
  if (!hasData) return

  try {
    const response = await $api('/customers-search-by-phone-email-tax-iva', {
      method: 'GET',
      params: params
    })

    let foundUsers = []
    if (response && response.users && Array.isArray(response.users)) {
        foundUsers = response.users
    } else if (Array.isArray(response)) {
        foundUsers = response
    } else if (response && response.data && Array.isArray(response.data)) {
        foundUsers = response.data
    } else if (response && response.customers && Array.isArray(response.customers)) {
        foundUsers = response.customers
    }

    if (customer.id && foundUsers.length > 0) {
        foundUsers = foundUsers.filter(u => u.id != customer.id)
    }

    if (foundUsers.length > 0) {
        duplicateUsers.value = foundUsers
        for (const user of foundUsers) {
            if (customer.email && user.email && user.email.toLowerCase() === customer.email.toLowerCase()) {
                emailError.value = 'Questa email è già registrata per un altro cliente'
            }
            if (customer.vat_number && user.vat_number && user.vat_number === customer.vat_number) {
                vatError.value = 'Questa Partita IVA è già registrata per un altro cliente'
            }
            if (customer.tax_id_code && user.tax_id_code && user.tax_id_code.toUpperCase() === customer.tax_id_code.toUpperCase()) {
                taxIdError.value = 'Questo Codice Fiscale è già registrato per un altro cliente'
            }
            
            // Check phones with normalization
            const pVal = normalizePhone(customer.phone)
            const mVal = normalizePhone(customer.mobile)
            const uPhone = normalizePhone(user.phone)
            const uMobile = normalizePhone(user.mobile)

            // Check phone input against user phone AND user mobile
            if (pVal && pVal.length > 5) {
                 if ((uPhone && uPhone.length > 5 && (uPhone.includes(pVal) || pVal.includes(uPhone))) || 
                     (uMobile && uMobile.length > 5 && (uMobile.includes(pVal) || pVal.includes(uMobile)))) {
                      phoneError.value = 'Questo numero di telefono è già registrato'
                 }
            }
            
            // Check mobile input against user phone AND user mobile
            if (mVal && mVal.length > 5) {
                 if ((uPhone && uPhone.length > 5 && (uPhone.includes(mVal) || mVal.includes(uPhone))) || 
                     (uMobile && uMobile.length > 5 && (uMobile.includes(mVal) || mVal.includes(uMobile)))) {
                      mobileError.value = 'Questo numero di cellulare è già registrato'
                 }
            }
        }
    }

  } catch (error) {
    console.error('Error checking duplicate customer:', error)
  }
}, 800)

const openDuplicateModal = () => {
    isDuplicateModalVisible.value = true
}

watch(() => extractedCustomer.value.email, () => checkDuplicateCustomer())
watch(() => extractedCustomer.value.vat_number, () => checkDuplicateCustomer())
watch(() => extractedCustomer.value.tax_id_code, () => checkDuplicateCustomer())
watch(() => extractedCustomer.value.phone, () => checkDuplicateCustomer())
watch(() => extractedCustomer.value.mobile, () => checkDuplicateCustomer())

// Error dialog state
const isErrorDialogVisible = ref(false)
const errorTitle = ref('')
const errorMessage = ref('')

// Success dialog state
const isSuccessDialogVisible = ref(false)
const successTitle = ref('')
const successMessage = ref('')
const shouldReloadOnSuccessClose = ref(false)

// Helper function to show error dialog
const showErrorDialog = (title, error) => {
  errorTitle.value = title
  
  // Extract message from API error response
  let message = ''
  
  if (error?.data) {
    // Check for validation errors (422)
    if (error.data.errors && typeof error.data.errors === 'object') {
      const errorMessages = Object.values(error.data.errors).flat()
      message = errorMessages.join('\n')
    } 
    // Check for error message
    else if (error.data.message) {
      message = error.data.message
    }
    // Check for error field
    else if (error.data.error) {
      message = error.data.error
    }
  }
  
  // Fallback to generic message
  if (!message) {
    message = 'Si è verificato un errore imprevisto. Riprova più tardi.'
  }
  
  errorMessage.value = message
  isErrorDialogVisible.value = true
}

// Helper function to show success dialog
const showSuccessDialog = (title, message, reloadOnClose = false) => {
  successTitle.value = title
  successMessage.value = message
  shouldReloadOnSuccessClose.value = reloadOnClose
  isSuccessDialogVisible.value = true
}

// Handle success dialog close
const handleSuccessDialogClose = () => {
  isSuccessDialogVisible.value = false
  if (shouldReloadOnSuccessClose.value) {
    window.location.reload()
  }
}

const saveModifications = async () => {
  // Validazione campi obbligatori prima del salvataggio
  if (!extractedPaperwork.value.type) {
    showErrorDialog('Campo obbligatorio', { data: { message: 'Il campo Tipo Fornitura è obbligatorio' } })
    return
  }
  
  if (!extractedPaperwork.value.energy_type) {
    showErrorDialog('Campo obbligatorio', { data: { message: 'Il campo Tipo Utenza è obbligatorio' } })
    return
  }
  
  if (!extractedPaperwork.value.category) {
    showErrorDialog('Campo obbligatorio', { data: { message: 'Il campo Categoria è obbligatorio' } })
    return
  }
  
  if (extractedPaperwork.value.energy_type === 'MOBILE' && !extractedPaperwork.value.mobile_type) {
    showErrorDialog('Campo obbligatorio', { data: { message: 'Il campo Tipologia Mobile è obbligatorio quando il tipo utenza è Mobile' } })
    return
  }
  
  if (isPodRequired.value && (!extractedPaperwork.value.account_pod_pdr || extractedPaperwork.value.account_pod_pdr.trim() === '')) {
    showErrorDialog('Campo obbligatorio', { data: { message: 'Il campo POD/PDR è obbligatorio per questo tipo di pratica' } })
    return
  }

  if (extractedPaperwork.value.account_pod_pdr && extractedPaperwork.value.account_pod_pdr.length !== 14) {
    showErrorDialog('Formato non valido', { data: { message: 'Il campo POD/PDR deve essere di 14 caratteri' } })
    return
  }
  
  if (extractedPaperwork.value.contract_type === 'Business' && (!extractedCustomer.value.business_name || extractedCustomer.value.business_name.trim() === '')) {
    showErrorDialog('Campo obbligatorio', { data: { message: 'La ragione sociale è obbligatoria per i contratti Business' } })
    return
  }

  isSaving.value = true
  try {
    await $api(`/ai-paperworks/${id}`, {
      method: 'PUT',
      body: {
        ai_extracted_customer: extractedCustomer.value,
        ai_extracted_paperwork: extractedPaperwork.value,
        brand_id: extractedPaperwork.value.brand_id,
        mandate_id: extractedPaperwork.value.mandate_id,
        user_id: selectedAgentId.value,
      }
    })
    // Show success message with reload on close
    showSuccessDialog('Modifiche salvate', 'Le modifiche sono state salvate con successo. La pagina verrà ricaricata.', true)
  } catch (error) {
    console.error('Error saving modifications:', error)
    showErrorDialog('Errore durante il salvataggio', error)
  } finally {
    isSaving.value = false
  }
}

const confirmPaperwork = async () => {
  if (!extractedPaperwork.value.product_id) {
    showErrorDialog('Prodotto richiesto', { data: { message: 'Seleziona un prodotto prima di confermare' } })
    return
  }

  // Validazione campi obbligatori prima della conferma
  if (!extractedPaperwork.value.type) {
    showErrorDialog('Campo obbligatorio', { data: { message: 'Il campo Tipo Fornitura è obbligatorio' } })
    return
  }
  
  if (!extractedPaperwork.value.energy_type) {
    showErrorDialog('Campo obbligatorio', { data: { message: 'Il campo Tipo Utenza è obbligatorio' } })
    return
  }
  
  if (!extractedPaperwork.value.category) {
    showErrorDialog('Campo obbligatorio', { data: { message: 'Il campo Categoria è obbligatorio' } })
    return
  }
  
  if (extractedPaperwork.value.energy_type === 'MOBILE' && !extractedPaperwork.value.mobile_type) {
    showErrorDialog('Campo obbligatorio', { data: { message: 'Il campo Tipologia Mobile è obbligatorio quando il tipo utenza è Mobile' } })
    return
  }
  
  if (isPodRequired.value && (!extractedPaperwork.value.account_pod_pdr || extractedPaperwork.value.account_pod_pdr.trim() === '')) {
    showErrorDialog('Campo obbligatorio', { data: { message: 'Il campo POD/PDR è obbligatorio per questo tipo di pratica' } })
    return
  }

  if (extractedPaperwork.value.account_pod_pdr && extractedPaperwork.value.account_pod_pdr.length !== 14) {
    showErrorDialog('Formato non valido', { data: { message: 'Il campo POD/PDR deve essere di 14 caratteri' } })
    return
  }
  
  if (extractedPaperwork.value.contract_type === 'Business' && (!extractedCustomer.value.business_name || extractedCustomer.value.business_name.trim() === '')) {
    showErrorDialog('Campo obbligatorio', { data: { message: 'La ragione sociale è obbligatoria per i contratti Business' } })
    return
  }

  try {
    const response = await $api(`/ai-paperworks/${id}/confirm`, {
      method: 'POST',
      body: {
        product_id: extractedPaperwork.value.product_id,
        brand_id: extractedPaperwork.value.brand_id,
        ai_extracted_customer: extractedCustomer.value,
        ai_extracted_paperwork: extractedPaperwork.value,
        status: 5,
        mandate_id: extractedPaperwork.value.mandate_id,
        override_customer: overrideDuplicates.value || false,
        user_id: selectedAgentId.value,
      }
    })
    // Update local status
    if (aiPaperwork.value) {
      aiPaperwork.value.status = 5
    }
    // Redirect to the new paperwork
    router.push(`/workflow/paperworks/${response.paperwork.id}`)
  } catch (error) {
    console.error('Error confirming paperwork:', error)
    showErrorDialog('Errore durante la conferma', error)
  }
}

const cancelPaperwork = async () => {
  try {
    await $api(`/ai-paperworks/${id}/cancel`, {
      method: 'POST',
      body: {
        status: 8
      }
    })
    // Refresh the data
    await fetchAIPaperwork()
  } catch (error) {
    console.error('Error canceling paperwork:', error)
    showErrorDialog('Errore durante l\'annullamento', error)
  }
}

// Funzione per ottenere le opzioni energy_type basate sul type selezionato
const getEnergyTypeOptions = () => {
  if (extractedPaperwork.value.type === 'ENERGIA') {
    return [
      { title: 'Luce', value: 'LUCE' },
      { title: 'Gas', value: 'GAS' },
    ]
  } else if (extractedPaperwork.value.type === 'TELEFONIA') {
    return [
      { title: 'Fisso', value: 'FISSO' },
      { title: 'Mobile', value: 'MOBILE' },
      { title: 'Fisso e Mobile', value: 'FISSO_MOBILE' },
    ]
  }
  return []
}

// Funzione per ottenere le categorie basate sul type selezionato
const getCategoryOptions = () => {
  if (extractedPaperwork.value.type === 'ENERGIA') {
    return [
      { title: 'ALLACCIO', value: 'ALLACCIO' },
      { title: 'OTP', value: 'OTP' },
      { title: 'SUBENTRO', value: 'SUBENTRO' },
      { title: 'VOLTURA', value: 'VOLTURA' },
      { title: 'SWITCH', value: 'SWITCH' },
    ]
  } else if (extractedPaperwork.value.type === 'TELEFONIA') {
    return [
      { title: 'NUOVA LINEA', value: 'NUOVA LINEA' },
      { title: 'PORTABILITÀ', value: 'PORTABILITÀ' },
    ]
  }
  return []
}

// Computed per determinare se il campo POD/PDR è obbligatorio
const isPodRequired = computed(() => {
  if (extractedPaperwork.value.category === 'ALLACCIO') {
    return false // ALLACCIO è sempre opzionale
  }
  
  // Controlla energy_type: opzionale solo per MOBILE
  return extractedPaperwork.value.energy_type !== 'MOBILE'
})

// Gestione eventi di trasferimento
const onHandleTrasferConfirmed = (transferData) => {
  console.log('Transfer confirmed:', transferData)
}

const onHandleTransferCompleted = async (eventData) => {
  if (eventData.shouldReload) {
    // Ricarica i dati della AI paperwork
    await fetchAIPaperwork()
    console.log('AI Paperwork data reloaded after transfer')
  }
}

// Traccia lo status precedente per rilevare il cambio di stato
let previousStatus = ref(aiPaperwork.value?.status)

// Watch per avviare automaticamente il polling se la pagina si carica con status=1
watch(() => aiPaperwork.value?.status, (newStatus, oldStatus) => {
  // Avvia polling per status 0 (In attesa) e 1 (In elaborazione)
  if ((newStatus === 0 || newStatus === 1) && !pollingInterval) {
    console.log('Avvio polling automatico per status:', newStatus)
    startPolling()
  }
  // Ferma polling per status finali (2, 8, 9)
  else if (newStatus === 2 || newStatus === 8 || newStatus === 9) {
    console.log('Status finale raggiunto, fermo polling')
    stopPolling()
    
    // Auto-refresh quando la pratica viene completata (passa a status 2 da 0 o 1)
    if (newStatus === 2 && (previousStatus.value === 0 || previousStatus.value === 1)) {
      console.log('Pratica completata, refresh automatico della pagina...')
      window.location.reload()
    }
  }
  
  // Aggiorna lo status precedente
  previousStatus.value = newStatus
}, { immediate: true })

// Cleanup: ferma il polling quando l'utente esce dalla pagina
onUnmounted(() => {
  stopPolling()
})

</script>

<template>
  <section>
    <VCard>
      <VCardTitle class="d-flex flex-column pa-4">
        <div class="d-flex justify-space-between align-center w-100 mb-4">
          <div>
            <h2 class="text-h5 font-weight-medium">
              Dettaglio Pratica AI #{{ aiPaperwork?.id }}
              <template v-if="aiPaperwork?.user">
                <span class="text-medium-emphasis">- Caricato a 
                  <RouterLink 
                    :to="`/users/${aiPaperwork.user.id}`"
                    class="text-decoration-none"
                  >
                    {{ aiPaperwork.user.name }} {{ aiPaperwork.user.last_name }}
                  </RouterLink>
                </span>
              </template>
            </h2>
            <div v-if="basename" class="text-subtitle-2 text-medium-emphasis">{{ basename }}</div>
          </div>
          <VChip
            :color="getStatusChipColor(aiPaperwork?.status)"
            size="small"
            class="text-capitalize"
          >
            {{ getStatusText(aiPaperwork?.status) }}
          </VChip>
        </div>
        
        <!-- Selettore Agente - visibile solo quando l'elaborazione è completata (status 2) e solo per gestione, struttura e backoffice -->
        <div 
          v-if="aiPaperwork?.status === 2 && canSelectAgent" 
          class="mt-4 mb-4" 
          style="max-width: 400px;"
        >
          <AppAutocomplete
            v-model="selectedAgentId"
            label="Agente"
            :items="agents"
            item-title="title"
            item-value="value"
            placeholder="Seleziona un agente"
            :loading="isLoadingAgents"
            :disabled="isSaving"
          />
        </div>
        
        <!-- Transfer component per pratiche AI -->
        <div 
          v-if="aiPaperwork?.status === 2"
          style="margin: 20px 0;"
        >
          <AIPaperworkTransfer 
            :ai-paperwork-id="id"
            :current-brand-id="aiPaperwork?.brand_id"
            @onTrasferConfirmed="onHandleTrasferConfirmed"
            @onTransferCompleted="onHandleTransferCompleted"
          />
        </div>

        <!-- Banner per elaborazione bloccata -->
        <ProcessingAIStutteringBanner
          v-if="aiPaperwork?.status === 1"
          :ai-paperwork="aiPaperwork"
          @on-reset="processDocument"
        />

        <!-- Banner per errori (status 8 o 9) -->
        <AIErrorBanner
          v-if="aiPaperwork?.status === 8 || aiPaperwork?.status === 9"
          :ai-paperwork="aiPaperwork"
          @on-retry="processDocument"
        />

        <!-- Componente professionale per brand sovrascritto -->
        <BrandOverrideAlert
          v-if="extractedPaperwork?.brand_override && extractedPaperwork?.matched_product"
          :matched-product="extractedPaperwork.matched_product"
          :matched-brand="extractedPaperwork.matched_brand"
          :original-brand-id="extractedPaperwork.original_brand_id"
        />
        
        <div class="d-flex justify-space-between align-start w-100">
          <div class="d-flex gap-2 align-start">
            <VBtn
              color="primary"
              prepend-icon="tabler-download"
              @click="downloadFile"
            >
              Scarica PDF
            </VBtn>
            <div v-if="aiPaperwork?.status === 2" class="d-flex flex-column">
              <VBtn
                color="success"
                prepend-icon="tabler-check"
                @click="confirmPaperwork"
              >
                Conferma Pratica
              </VBtn>
              <div class="d-flex align-center mt-1">
                <VCheckbox
                  v-model="overrideDuplicates"
                  label="Sovrascrivi tutti i duplicati"
                  density="compact"
                  hide-details
                  class="pa-0 ma-0"
                />
                <VIcon
                  icon="tabler-info-circle"
                  size="small"
                  class="cursor-pointer ml-1 text-medium-emphasis"
                  @click="isOverrideInfoModalVisible = true"
                />
              </div>
            </div>
            <!-- <VBtn
              v-if="aiPaperwork?.status !== 8 && aiPaperwork?.status !== 5"
              color="error"
              prepend-icon="tabler-x"
              @click="cancelPaperwork"
            >
              Annulla Pratica
            </VBtn> -->
          </div>
          <div class="d-flex gap-2">
            <VBtn
              v-if="aiPaperwork?.status !== 5 && ![0, 1, 8, 9].includes(aiPaperwork?.status)"
              color="primary"
              prepend-icon="tabler-device-floppy"
              :loading="isSaving"
              :disabled="isSaving"
              @click="saveModifications"
            >
              {{ isSaving ? 'Salvataggio...' : 'Salva Modifiche' }}
            </VBtn>
          </div>
        </div>
      </VCardTitle>

      <VCardText>
        <template v-if="![0, 1, 8, 9].includes(aiPaperwork?.status)">
          <VRow>
            <!-- Customer Section -->
            <VCol cols="6">
              <VForm>
                <h3 class="text-h6 mb-2">Dati Cliente Estratti</h3>
                <VDivider class="mb-4" />
                
                <VRow>
                  <VCol cols="6">
                    <AppTextField
                      v-model="extractedCustomer.name"
                      label="Nome"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                  <VCol cols="6">
                    <AppTextField
                      v-model="extractedCustomer.last_name"
                      label="Cognome"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                </VRow>

                <VRow v-if="extractedPaperwork.contract_type === 'Business'">
                  <VCol cols="12">
                    <AppTextField
                      v-model="extractedCustomer.business_name"
                      label="Ragione Sociale *"
                      :readonly="aiPaperwork?.status === 5"
                      :error="aiPaperwork?.status !== 5 && extractedPaperwork.contract_type === 'Business' && !extractedCustomer.business_name"
                      :error-messages="aiPaperwork?.status !== 5 && extractedPaperwork.contract_type === 'Business' && !extractedCustomer.business_name ? 'La ragione sociale è obbligatoria per i contratti Business' : ''"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="9">
                    <AppTextField
                      v-model="extractedCustomer.email"
                      label="Email"
                      :readonly="aiPaperwork?.status === 5"
                      :custom-error="emailError"
                      :show-error-details="!!emailError"
                      @click:errorDetails="openDuplicateModal"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="6">
                    <CustomerTelPhoneInput
                      v-model="extractedCustomer.phone"
                      :type="'phone'"
                      label="Telefono"
                      placeholder="Telefono fisso"
                      :readonly="aiPaperwork?.status === 5"
                      :customer-id="extractedPaperwork.customer_id"
                      :custom-error="phoneError"
                      :show-error-details="!!phoneError"
                      @click:errorDetails="openDuplicateModal"
                    />
                  </VCol>
                  <VCol cols="6">
                    <CustomerTelPhoneInput
                      v-model="extractedCustomer.mobile"
                      :type="'mobile'"
                      label="Cellulare"
                      placeholder="Cellulare"
                      :readonly="aiPaperwork?.status === 5"
                      :customer-id="extractedPaperwork.customer_id"
                      :custom-error="mobileError"
                      :show-error-details="!!mobileError"
                      @click:errorDetails="openDuplicateModal"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="12">
                    <AppTextField
                      v-model="extractedCustomer.address"
                      label="Indirizzo"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="6">
                    <AppTextField
                      v-model="extractedCustomer.city"
                      label="Città"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                  <VCol cols="6">
                    <AppTextField
                      v-model="extractedCustomer.zip_code"
                      label="CAP"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="6">
                    <AppTextField
                      v-model="extractedCustomer.province"
                      label="Provincia"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                  <VCol cols="6">
                    <AppTextField
                      v-model="extractedCustomer.region"
                      label="Regione"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="6">
                    <AppTextField
                      v-model="extractedCustomer.tax_id_code"
                      label="Codice Fiscale"
                      :readonly="aiPaperwork?.status === 5"
                      :custom-error="taxIdError"
                      :show-error-details="!!taxIdError"
                      @click:errorDetails="openDuplicateModal"
                    />
                  </VCol>
                  <VCol cols="6">
                    <AppTextField
                      v-model="extractedCustomer.vat_number"
                      label="Partita IVA"
                      :readonly="aiPaperwork?.status === 5"
                      :custom-error="vatError"
                      :show-error-details="!!vatError"
                      @click:errorDetails="openDuplicateModal"
                    />
                  </VCol>
                </VRow>
              </VForm>
            </VCol>

            <!-- Paperwork Section -->
            <VCol cols="6">
              <VForm>
                <h3 class="text-h6 mb-2">Dati Pratica Estratti</h3>
                <VDivider class="mb-4" />

                <VRow>
                  <VCol cols="12">
                    <AppAutocomplete
                      v-model="extractedPaperwork.type"
                      label="Tipo Fornitura"
                      :readonly="aiPaperwork?.status === 5"
                      :items="['ENERGIA', 'TELEFONIA']"
                      @update:modelValue="() => { extractedPaperwork.energy_type = null; extractedPaperwork.category = null; extractedPaperwork.previous_provider = null; extractedPaperwork.mobile_type = null; }"
                    />
                  </VCol>
                </VRow>

                <!-- Campo Energy Type unificato -->
                <VRow>
                  <VCol cols="12">
                    <AppAutocomplete
                      v-model="extractedPaperwork.energy_type"
                      label="Tipo Utenza *"
                      :readonly="aiPaperwork?.status === 5"
                      :items="getEnergyTypeOptions()"
                      placeholder="Seleziona tipo utenza"
                      :error="aiPaperwork?.status !== 5 && !extractedPaperwork.energy_type"
                      :error-messages="aiPaperwork?.status !== 5 && !extractedPaperwork.energy_type ? 'Il campo Tipo Utenza è obbligatorio' : ''"
                      @update:modelValue="() => { extractedPaperwork.mobile_type = null; }"
                    />
                  </VCol>
                </VRow>

                <!-- Campo Mobile Type per MOBILE -->
                <VRow v-if="extractedPaperwork.energy_type === 'MOBILE'">
                  <VCol cols="12">
                    <AppAutocomplete
                      v-model="extractedPaperwork.mobile_type"
                      label="Tipologia Mobile *"
                      :readonly="aiPaperwork?.status === 5"
                      :items="[{ title: 'MNP', value: 'MNP' }, { title: 'NIP', value: 'NIP' }]"
                      placeholder="Seleziona tipologia mobile"
                      :error="aiPaperwork?.status !== 5 && extractedPaperwork.energy_type === 'MOBILE' && !extractedPaperwork.mobile_type"
                      :error-messages="aiPaperwork?.status !== 5 && extractedPaperwork.energy_type === 'MOBILE' && !extractedPaperwork.mobile_type ? 'Il campo Tipologia Mobile è obbligatorio' : ''"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="12">
                    <AppAutocomplete
                      v-model="extractedPaperwork.product_id"
                      :items="products"
                      item-title="name"
                      item-value="id"
                      label="Prodotto"
                      placeholder="Seleziona un Prodotto"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="12">
                    <SearchBrand
                      v-model="extractedPaperwork.brand_id"
                      :items="brands"
                      item-title="name"
                      item-value="id"
                      label="Brand"
                      placeholder="Seleziona un Brand"
                      :readonly="aiPaperwork?.status === 5"
                      :loading="isLoadingBrands"
                      :class="{ 'opacity-50': aiPaperwork?.status === 5 }"
                    />
                  </VCol>
                </VRow>

                <!-- Nuovo campo mandate_id -->
                <VRow v-if="$can('access', 'mandates')">
                  <VCol cols="12">
                    <AppAutocomplete
                      v-model="extractedPaperwork.mandate_id"
                      label="Mandato"
                      :items="mandates"
                      item-title="title"
                      item-value="value"
                      placeholder="Seleziona un mandato"
                      :readonly="aiPaperwork?.status === 5"
                      :loading="isLoadingMandates"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="12">
                    <AppTextField
                      v-model="extractedPaperwork.account_pod_pdr"
                      :label="isPodRequired ? 'POD/PDR *' : 'POD/PDR'"
                      :readonly="aiPaperwork?.status === 5"
                      :error="aiPaperwork?.status !== 5 && (
                        (isPodRequired && (!extractedPaperwork.account_pod_pdr || extractedPaperwork.account_pod_pdr.trim() === '')) ||
                        (extractedPaperwork.account_pod_pdr && extractedPaperwork.account_pod_pdr.length !== 14)
                      )"
                      :error-messages="aiPaperwork?.status !== 5 ? (
                        (isPodRequired && (!extractedPaperwork.account_pod_pdr || extractedPaperwork.account_pod_pdr.trim() === '')) 
                          ? 'Il campo POD/PDR è obbligatorio per questo tipo di pratica' 
                          : (extractedPaperwork.account_pod_pdr && extractedPaperwork.account_pod_pdr.length !== 14)
                            ? 'Il campo POD/PDR deve essere di 14 caratteri'
                            : ''
                      ) : ''"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="12">
                    <AppAutocomplete
                      v-model="extractedPaperwork.category"
                      label="Categoria *"
                      :readonly="aiPaperwork?.status === 5"
                      :items="getCategoryOptions()"
                      placeholder="Seleziona una categoria"
                      :error="aiPaperwork?.status !== 5 && !extractedPaperwork.category"
                      :error-messages="aiPaperwork?.status !== 5 && !extractedPaperwork.category ? 'Il campo Categoria è obbligatorio' : ''"
                    />
                  </VCol>
                </VRow>

                <!-- Campo Fornitore Precedente per SWITCH, VOLTURA, OTP -->
                <VRow v-if="['SWITCH', 'VOLTURA', 'OTP'].includes(extractedPaperwork.category)">
                  <VCol cols="12">
                    <AppTextField
                      v-model="extractedPaperwork.previous_provider"
                      label="Fornitore Precedente"
                      :readonly="aiPaperwork?.status === 5"
                      placeholder="Es: Enel, Eni, Edison..."
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="12">
                    <AppAutocomplete
                      v-model="extractedPaperwork.contract_type"
                      label="Tipo Contratto"
                      :readonly="aiPaperwork?.status === 5"
                      :items="['Residenziale', 'Business']"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="12">
                    <AppTextField
                      v-model="extractedPaperwork.annual_consumption"
                      label="Consumo Annuo"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="12">
                    <AppTextField
                      v-model="extractedPaperwork.previous_provider"
                      label="Fornitore Precedente"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                </VRow>

                <!-- Sezione Da Appuntamento -->
                <template v-if="extractedCustomer.id">
                  <VRow>
                    <VCol cols="12">
                      <VDivider class="my-4" />
                      <VSubheader class="px-0 text-sm font-weight-medium text-high-emphasis">
                        Informazioni Appuntamento
                      </VSubheader>
                    </VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="12">
                      <VSwitch
                        v-model="extractedPaperwork.is_from_appointment"
                        label="Da appuntamento?"
                        :readonly="aiPaperwork?.status === 5"
                      />
                    </VCol>
                  </VRow>

                  <VRow v-show="extractedPaperwork.is_from_appointment">
                    <VCol cols="12">
                      <AppAutocomplete
                        v-model="extractedPaperwork.appointment_id"
                        :items="appointments"
                        item-title="title"
                        item-value="value"
                        label="Appuntamento"
                        placeholder="Seleziona un Appuntamento"
                        :loading="isLoadingAppointments"
                        :readonly="aiPaperwork?.status === 5"
                        clearable
                      />
                    </VCol>
                  </VRow>
                </template>
              </VForm>
            </VCol>
          </VRow>
        </template>
        <VRow v-else class="justify-center align-center py-8">
          <VCol cols="12" class="text-center">
            <VProgressCircular
              v-if="aiPaperwork?.status === 1"
              indeterminate
              :color="getStatusChipColor(aiPaperwork?.status)"
              size="48"
              width="4"
              class="mb-4"
            />
            <VIcon
              v-else
              :icon="aiPaperwork?.status === 0 ? 'tabler-clock' : 'tabler-x'"
              size="48"
              :color="getStatusChipColor(aiPaperwork?.status)"
              class="mb-4"
            />
            <h3 class="text-h6 mb-2">
              {{ aiPaperwork?.status === 0 ? 'Documento in attesa di elaborazione' :
                 aiPaperwork?.status === 1 ? 'Elaborazione in corso...' :
                 aiPaperwork?.status === 8 ? 'Documento annullato' :
                 'Si è verificato un errore durante l\'elaborazione' }}
            </h3>
            <p class="text-medium-emphasis">
              {{
                 aiPaperwork?.status === 1 ? 'Attendi il completamento dell\'elaborazione' :
                 aiPaperwork?.status === 8 ? 'Questo documento è stato annullato e non può essere modificato' :
                 'Non è possibile visualizzare o modificare i dati estratti' }}
            </p>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <VDialog
      v-model="isDuplicateModalVisible"
      max-width="800"
    >
      <VCard>
        <VCardTitle class="pa-4 d-flex align-center justify-space-between">
          <span class="text-h5">Clienti trovati</span>
          <VBtn
            icon
            variant="text"
            color="default"
            @click="isDuplicateModalVisible = false"
          >
            <VIcon icon="tabler-x" />
          </VBtn>
        </VCardTitle>
        
        <VDivider />
        
        <VCardText class="pa-4">
          <p class="mb-4">Sono stati trovati i seguenti clienti con dati simili:</p>
          
          <VTable>
            <thead>
              <tr>
                <th class="text-left">Nominativo</th>
                <th class="text-left">Tipologia</th>
                <th class="text-left">Email</th>
                <th class="text-left">Telefono</th>
                <th class="text-left">Cellulare</th>
                <th class="text-left">CF</th>
                <th class="text-left">P.IVA</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in duplicateUsers" :key="user.id">
                <td>{{ user.business_name || (user.name + ' ' + user.last_name) }}</td>
                <td>{{ user.category }}</td>
                <td>{{ user.email }}</td>
                <td>{{ user.phone }}</td>
                <td>{{ user.mobile }}</td>
                <td>{{ user.tax_id_code }}</td>
                <td>{{ user.vat_number }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCardText>
        
        <VCardActions class="pa-4 justify-end">
          <VBtn
            variant="tonal"
            color="secondary"
            @click="isDuplicateModalVisible = false"
          >
            Chiudi
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Override Info Dialog -->
    <VDialog
      v-model="isOverrideInfoModalVisible"
      max-width="500"
    >
      <VCard>
        <VCardTitle class="pa-4 d-flex align-center justify-space-between">
          <span class="text-h6">Info Sovrascrittura Duplicati</span>
           <VBtn
            icon
            variant="text"
            color="default"
            @click="isOverrideInfoModalVisible = false"
          >
            <VIcon icon="tabler-x" />
          </VBtn>
        </VCardTitle>
        <VDivider />
        <VCardText class="pa-4">
          Selezionando l'opzione <strong>"Sovrascrivi tutti i duplicati"</strong>, verranno sovrascritti i dati anagrafici di tutti i clienti già esistenti nel sistema che presentano lo stesso <strong>Codice Fiscale</strong> o <strong>Partita IVA</strong> del cliente attuale.
        </VCardText>
        <VCardActions class="pa-4 justify-end">
          <VBtn
            variant="tonal"
            color="secondary"
            @click="isOverrideInfoModalVisible = false"
          >
            Chiudi
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Error Dialog -->
    <GeneralErrorDialog
      v-model="isErrorDialogVisible"
      :title="errorTitle"
      :message="errorMessage"
    />

    <!-- Success Dialog -->
    <GeneralSuccessDialog
      v-model="isSuccessDialogVisible"
      :title="successTitle"
      :message="successMessage"
      @close="handleSuccessDialogClose"
    />

    <!-- Transfer History Table -->
    <TransferTable
      :transfers-history="aiPaperwork?.transfers_history || []"
    />
  </section>
</template> 
