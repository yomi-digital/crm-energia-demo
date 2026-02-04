<script setup>
import CustomerBioPanel from '@/views/workflow/customers/CustomerBioPanel.vue';
import { useDebounceFn } from '@vueuse/core';
import { computed, nextTick, ref, watch } from 'vue';

const name = ref('')
const lastName = ref('')
const businessName = ref('')
const taxIdCode = ref('')
const vatNumber = ref('')
const email = ref('')
const phone = ref('')
const mobile = ref('')
const privacy = ref(false)
const atecoCode = ref('')
const pec = ref('')
const uniqueCode = ref('')
const category = ref('Residenziale')
const address = ref('')
const region = ref('')
const province = ref('')
const city = ref('')
const zip = ref('')



const refForm = ref()
const isSaving = ref(false)
const errorMessage = ref('')
const duplicateWarning = ref('')
const isDuplicateWarningVisible = ref(false)
const emailError = ref('')
const vatError = ref('')
const taxIdError = ref('')
const phoneError = ref('')
const mobileError = ref('')
const duplicateUsers = ref([])
const isDuplicateModalVisible = ref(false)
const isModalOpenedFromCreate = ref(false)

// Customer Detail Dialog state
const isCustomerDetailModalVisible = ref(false)
const selectedCustomerForDetail = ref(null)

const openCustomerDetail = (customer) => {
  selectedCustomerForDetail.value = customer
  isCustomerDetailModalVisible.value = true
}

const normalizePhone = (p) => p ? p.replace(/\D/g, '') : ''

const checkDuplicateCustomer = useDebounceFn(async () => {
  // Reset errors
  emailError.value = ''
  vatError.value = ''
  taxIdError.value = ''
  phoneError.value = ''
  mobileError.value = ''
  duplicateUsers.value = []

  // Gather data
  const params = {}
  
  if (phone.value) params.telefono = phone.value
  if (mobile.value) params.telefono = mobile.value
  if (email.value) params.email = email.value
  if (vatNumber.value) params.vat_number = vatNumber.value
  if (taxIdCode.value) params.tax_id_code = taxIdCode.value

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

    if (foundUsers.length > 0) {
        duplicateUsers.value = foundUsers
        for (const user of foundUsers) {
            if (email.value && user.email && user.email.toLowerCase() === email.value.toLowerCase()) {
                emailError.value = 'Questa email è già registrata per un altro cliente'
            }
            if (vatNumber.value && user.vat_number && user.vat_number === vatNumber.value) {
                vatError.value = 'Questa Partita IVA è già registrata per un altro cliente'
            }
            if (taxIdCode.value && user.tax_id_code && user.tax_id_code.toUpperCase() === taxIdCode.value.toUpperCase()) {
                taxIdError.value = 'Questo Codice Fiscale è già registrato per un altro cliente'
            }
            
            // Check phones with normalization
            const pVal = normalizePhone(phone.value)
            const mVal = normalizePhone(mobile.value)
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
    isModalOpenedFromCreate.value = false // Aperto manualmente, non mostrare il pulsante forza creazione
}

const fetchPostalCode = useDebounceFn(async () => {
  if (!city.value || !address.value) return
  
  try {
    const response = await $api('/geocoding/postal-code', {
      params: { 
        city: city.value, 
        street: address.value 
      }
    })
    
    if (response && response.postal_code) {
      zip.value = response.postal_code
    }
  } catch (error) {
    console.error('Error fetching postal code:', error)
  }
}, 800)


// Watchers
watch([city, address], () => {
  if (city.value && address.value) {
    fetchPostalCode()
  }
})
watch(email, () => checkDuplicateCustomer())
watch(vatNumber, () => checkDuplicateCustomer())
watch(taxIdCode, () => checkDuplicateCustomer())
watch(phone, () => checkDuplicateCustomer())
watch(mobile, () => checkDuplicateCustomer())
// Phone is also in the API params, so we should probably watch it too if we want to use this API for phone as well,
// but phone has its own component. The user said "usando questa api ... controlli ... se esiste".
// If I watch phone here, it might duplicate the check or show two warnings.
// The user said "popup uguale a quello che gia esce al telefono". 
// If the phone component already shows a warning, maybe I shouldn't duplicate it for phone.
// But the user listed `telefono` in the API params.
// I'll include phone in the params but trigger primarily on others, OR trigger on phone too but be careful about UI.
// Since `CustomerTelPhoneInput` is already used, I'll stick to watching email, vat, taxId.
// But I will include `phone.value` in the API call if available, to make the check more robust (e.g. combo check).

const emit = defineEmits([
  'customerData',
])

// Funzioni per gestire i callback del componente
const handlePhoneCheckUpdate = (result) => {
  console.log('Phone check result:', result)
}

const handlePhoneValue = (value) => {
  phone.value = value
  console.log('Phone value:', value)
}

const handleMobileCheckUpdate = (result) => {
  console.log('Mobile check result:', result)
}

const handleMobileValue = (value) => {
  mobile.value = value
  console.log('Mobile value:', value)
}

const errors = ref({
  name: [],
  lastName: [],
  businessName: [],
  taxIdCode: [],
  vatNumber: [],
  email: [],
  phone: [],
  mobile: [],
  atecoCode: [],
  pec: [],
  uniqueCode: [],
  category: [],
  address: [],
  region: [],
  province: [],
  city: [],
  zip: [],
})

const categories = ref([
  { title: 'Ditta Individuale', value: 'all' },
  { title: 'Residenziale', value: 'Residenziale' },
  { title: 'Business', value: 'Business' },
])

const regions = [
  { title: 'Abruzzo', value: 'Abruzzo' },
  { title: 'Basilicata', value: 'Basilicata' },
  { title: 'Calabria', value: 'Calabria' },
  { title: 'Campania', value: 'Campania' },
  { title: 'Emilia-Romagna', value: 'Emilia-Romagna' },
  { title: 'Friuli-Venezia Giulia', value: 'Friuli-Venezia Giulia' },
  { title: 'Lazio', value: 'Lazio' },
  { title: 'Liguria', value: 'Liguria' },
  { title: 'Lombardia', value: 'Lombardia' },
  { title: 'Marche', value: 'Marche' },
  { title: 'Molise', value: 'Molise' },
  { title: 'Piemonte', value: 'Piemonte' },
  { title: 'Puglia', value: 'Puglia' },
  { title: 'Sardegna', value: 'Sardegna' },
  { title: 'Sicilia', value: 'Sicilia' },
  { title: 'Toscana', value: 'Toscana' },
  { title: 'Trentino-Alto Adige', value: 'Trentino-Alto Adige' },
  { title: 'Umbria', value: 'Umbria' },
  { title: 'Valle d\'Aosta', value: 'Valle d\'Aosta' },
  { title: 'Veneto', value: 'Veneto' },
]

const provinces = [
  { title: 'Agrigento (AG)', value: 'AG', region: 'Sicilia' },
  { title: 'Alessandria (AL)', value: 'AL', region: 'Piemonte' },
  { title: 'Ancona (AN)', value: 'AN', region: 'Marche' },
  { title: 'Aosta (AO)', value: 'AO', region: 'Valle d\'Aosta' },
  { title: 'Arezzo (AR)', value: 'AR', region: 'Toscana' },
  { title: 'Ascoli Piceno (AP)', value: 'AP', region: 'Marche' },
  { title: 'Asti (AT)', value: 'AT', region: 'Piemonte' },
  { title: 'Avellino (AV)', value: 'AV', region: 'Campania' },
  { title: 'Bari (BA)', value: 'BA', region: 'Puglia' },
  { title: 'Barletta-Andria-Trani (BT)', value: 'BT', region: 'Puglia' },
  { title: 'Belluno (BL)', value: 'BL', region: 'Veneto' },
  { title: 'Benevento (BN)', value: 'BN', region: 'Molise' },
  { title: 'Bergamo (BG)', value: 'BG', region: 'Lombardia' },
  { title: 'Biella (BI)', value: 'BI', region: 'Piemonte' },
  { title: 'Bologna (BO)', value: 'BO', region: 'Emilia-Romagna' },
  { title: 'Bolzano (BZ)', value: 'BZ', region: 'Trentino-Alto Adige' },
  { title: 'Brescia (BS)', value: 'BS', region: 'Lombardia' },
  { title: 'Brindisi (BR)', value: 'BR', region: 'Puglia' },
  { title: 'Cagliari (CA)', value: 'CA', region: 'Sardegna' },
  { title: 'Caltanissetta (CL)', value: 'CL', region: 'Sicilia' },
  { title: 'Campobasso (CB)', value: 'CB', region: 'Molise' },
  { title: 'Carbonia-Iglesias (CI)', value: 'CI', region: 'Sardegna' },
  { title: 'Caserta (CE)', value: 'CE', region: 'Campania' },
  { title: 'Catania (CT)', value: 'CT', region: 'Sicilia' },
  { title: 'Catanzaro (CZ)', value: 'CZ', region: 'Calabria' },
  { title: 'Chieti (CH)', value: 'CH', region: 'Abruzzo' },
  { title: 'Como (CO)', value: 'CO', region: 'Lombardia' },
  { title: 'Cosenza (CS)', value: 'CS', region: 'Calabria' },
  { title: 'Cremona (CR)', value: 'CR', region: 'Lombardia' },
  { title: 'Crotone (KR)', value: 'KR', region: 'Calabria' },
  { title: 'Cuneo (CN)', value: 'CN', region: 'Piemonte' },
  { title: 'Enna (EN)', value: 'EN', region: 'Sicilia' },
  { title: 'Fermo (FM)', value: 'FM', region: 'Puglia' },
  { title: 'Ferrara (FE)', value: 'FE', region: 'Emilia-Romagna' },
  { title: 'Firenze (FI)', value: 'FI', region: 'Toscana' },
  { title: 'Foggia (FG)', value: 'FG', region: 'Puglia' },
  { title: 'Forlì-Cesena (FC)', value: 'FC', region: 'Emilia-Romagna' },
  { title: 'Frosinone (FR)', value: 'FR', region: 'Lazio' },
  { title: 'Genova (GE)', value: 'GE', region: 'Liguria' },
  { title: 'Gorizia (GO)', value: 'GO', region: 'Friuli-Venezia Giulia' },
  { title: 'Grosseto (GR)', value: 'GR', region: 'Toscana' },
  { title: 'Imperia (IM)', value: 'IM', region: 'Liguria' },
  { title: 'Isernia (IS)', value: 'IS', region: 'Molise' },
  { title: 'La Spezia (SP)', value: 'SP', region: 'Liguria' },
  { title: 'L\'Aquila (AQ)', value: 'AQ', region: 'Abruzzo' },
  { title: 'Latina (LT)', value: 'LT', region: 'Lazio' },
  { title: 'Lecce (LE)', value: 'LE', region: 'Puglia' },
  { title: 'Lecco (LC)', value: 'LC', region: 'Lombardia' },
  { title: 'Livorno (LI)', value: 'LI', region: 'Toscana' },
  { title: 'Lodi (LO)', value: 'LO', region: 'Lombardia' },
  { title: 'Lucca (LU)', value: 'LU', region: 'Toscana' },
  { title: 'Macerata (MC)', value: 'MC', region: 'Marche' },
  { title: 'Mantova (MN)', value: 'MN', region: 'Lombardia' },
  { title: 'Massa-Carrara (MS)', value: 'MS', region: 'Toscana' },
  { title: 'Matera (MT)', value: 'MT', region: 'Basilicata' },
  { title: 'Medio Campidano (VS)', value: 'VS', region: 'Sardegna' },
  { title: 'Messina (ME)', value: 'ME', region: 'Sicilia' },
  { title: 'Milano (MI)', value: 'MI', region: 'Lombardia' },
  { title: 'Modena (MO)', value: 'MO', region: 'Emilia-Romagna' },
  { title: 'Monza e della Brianza (MB)', value: 'MB', region: 'Lombardia' },
  { title: 'Napoli (NA)', value: 'NA', region: 'Campania' },
  { title: 'Novara (NO)', value: 'NO', region: 'Piemonte' },
  { title: 'Nuoro (NU)', value: 'NU', region: 'Sardegna' },
  { title: 'Ogliastra (OG)', value: 'OG', region: 'Sardegna' },
  { title: 'Olbia-Tempio (OT)', value: 'OT', region: 'Sardegna' },
  { title: 'Oristano (OR)', value: 'OR', region: 'Sardegna' },
  { title: 'Padova (PD)', value: 'PD', region: 'Veneto' },
  { title: 'Palermo (PA)', value: 'PA', region: 'Sicilia' },
  { title: 'Parma (PR)', value: 'PR', region: 'Emilia-Romagna' },
  { title: 'Pavia (PV)', value: 'PV', region: 'Lombardia' },
  { title: 'Perugia (PG)', value: 'PG', region: 'Umbria' },
  { title: 'Pesaro e Urbino (PU)', value: 'PU', region: 'Marche' },
  { title: 'Pescara (PE)', value: 'PE', region: 'Abruzzo' },
  { title: 'Piacenza (PC)', value: 'PC', region: 'Emilia-Romagna' },
  { title: 'Pisa (PI)', value: 'PI', region: 'Toscana' },
  { title: 'Pistoia (PT)', value: 'PT', region: 'Toscana' },
  { title: 'Pordenone (PN)', value: 'PN', region: 'Friuli-Venezia Giulia' },
  { title: 'Potenza (PZ)', value: 'PZ', region: 'Basilicata' },
  { title: 'Prato (PO)', value: 'PO', region: 'Toscana' },
  { title: 'Ragusa (RG)', value: 'RG', region: 'Sicilia' },
  { title: 'Ravenna (RA)', value: 'RA', region: 'Emilia-Romagna' },
  { title: 'Reggio Calabria (RC)', value: 'RC', region: 'Calabria' },
  { title: 'Reggio Emilia (RE)', value: 'RE', region: 'Emilia-Romagna' },
  { title: 'Rieti (RI)', value: 'RI', region: 'Lazio' },
  { title: 'Rimini (RN)', value: 'RN', region: 'Emilia-Romagna' },
  { title: 'Roma (RM)', value: 'RM', region: 'Lazio' },
  { title: 'Rovigo (RO)', value: 'RO', region: 'Veneto' },
  { title: 'Salerno (SA)', value: 'SA', region: 'Campania' },
  { title: 'Sassari (SS)', value: 'SS', region: 'Sardegna' },
  { title: 'Savona (SV)', value: 'SV', region: 'Liguria' },
  { title: 'Siena (SI)', value: 'SI', region: 'Toscana' },
  { title: 'Siracusa (SR)', value: 'SR', region: 'Sicilia' },
  { title: 'Sondrio (SO)', value: 'SO', region: 'Lombardia' },
  { title: 'Taranto (TA)', value: 'TA', region: 'Puglia' },
  { title: 'Teramo (TE)', value: 'TE', region: 'Abruzzo' },
  { title: 'Terni (TR)', value: 'TR', region: 'Umbria' },
  { title: 'Torino (TO)', value: 'TO', region: 'Piemonte' },
  { title: 'Trapani (TP)', value: 'TP', region: 'Sicilia' },
  { title: 'Trento (TN)', value: 'TN', region: 'Trentino-Alto Adige' },
  { title: 'Treviso (TV)', value: 'TV', region: 'Veneto' },
  { title: 'Trieste (TS)', value: 'TS', region: 'Friuli-Venezia Giulia' },
  { title: 'Udine (UD)', value: 'UD', region: 'Friuli-Venezia Giulia' },
  { title: 'Varese (VA)', value: 'VA', region: 'Lombardia' },
  { title: 'Venezia (VE)', value: 'VE', region: 'Veneto' },
  { title: 'Verbano-Cusio-Ossola (VB)', value: 'VB', region: 'Piemonte' },
  { title: 'Vercelli (VC)', value: 'VC', region: 'Piemonte' },
  { title: 'Verona (VR)', value: 'VR', region: 'Veneto' },
  { title: 'Vibo Valentia (VV)', value: 'VV', region: 'Calabria' },
  { title: 'Vicenza (VI)', value: 'VI', region: 'Veneto' },
  { title: 'Viterbo (VT)', value: 'VT', region: 'Lazio' },
]

const checkDuplicatesBeforeCreate = async () => {
  // Gather data for duplicate check
  const params = {}
  
  if (phone.value) params.telefono = phone.value
  if (mobile.value) params.telefono = mobile.value
  if (email.value) params.email = email.value
  if (vatNumber.value) params.vat_number = vatNumber.value
  if (taxIdCode.value) params.tax_id_code = taxIdCode.value

  const hasData = Object.keys(params).length > 0
  if (!hasData) return false

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

    if (foundUsers.length > 0) {
        duplicateUsers.value = foundUsers
        return true
    }
    return false
  } catch (error) {
    console.error('Error checking duplicate customer:', error)
    return false
  }
}

const selectExistingCustomer = (customer) => {
  emit('customerData', customer)
  isDuplicateModalVisible.value = false
  isModalOpenedFromCreate.value = false
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
  })
}

const forceCreate = async () => {
  isDuplicateModalVisible.value = false
  isModalOpenedFromCreate.value = false
  
  let customerData = {
    category: category.value === 'all' ? null : category.value,
    email: email.value,
    phone: phone.value,
    mobile: mobile.value,
    privacy: privacy.value,
    address: address.value,
    region: typeof region.value === 'object' ? region.value.value : region.value,
    province: typeof province.value === 'object' ? province.value.value : province.value,
    city: city.value,
    zip: zip.value,
    force: true, // Flag per forzare la creazione ignorando i duplicati
  }
  if (category.value === 'Residenziale' || category.value === 'all') {
    customerData.name = name.value
    customerData.last_name = lastName.value
    customerData.tax_id_code = taxIdCode.value.toUpperCase()
  }
  if (category.value === 'Business' || category.value === 'all') {
    customerData.business_name = businessName.value
    customerData.vat_number = vatNumber.value
    customerData.pec = pec.value
    customerData.ateco_code = atecoCode.value
    customerData.unique_code = uniqueCode.value
  }

  isSaving.value = true
  
  try {
    const response = await $api('/customers', {
      method: 'POST',
      body: customerData,
    })
    
    isSaving.value = false
    
    // Redirect to the customer detail page
    if (response.id) {
      emit('customerData', response)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
      })
    }
  } catch (error) {
    isSaving.value = false
    errorMessage.value = error.response?._data?.message || 'Errore durante il salvataggio del cliente. Riprova.'
  }
}

const createUser = async () => {
  // Prima controlla se ci sono duplicati
  const hasDuplicates = await checkDuplicatesBeforeCreate()
  
  if (hasDuplicates) {
    // Mostra il modal con i clienti trovati (aperto automaticamente dal pulsante Crea)
    isModalOpenedFromCreate.value = true
    isDuplicateModalVisible.value = true
    isSaving.value = false
    return
  }

  let customerData = {
    category: category.value === 'all' ? null : category.value,
    email: email.value,
    phone: phone.value,
    mobile: mobile.value,
    privacy: privacy.value,
    address: address.value,
    region: typeof region.value === 'object' ? region.value.value : region.value,
    province: typeof province.value === 'object' ? province.value.value : province.value,
    city: city.value,
    zip: zip.value,
  }
  if (category.value === 'Residenziale' || category.value === 'all') {
    customerData.name = name.value
    customerData.last_name = lastName.value
    customerData.tax_id_code = taxIdCode.value.toUpperCase()
  }
  if (category.value === 'Business' || category.value === 'all') {
    customerData.business_name = businessName.value
    customerData.vat_number = vatNumber.value
    customerData.pec = pec.value
    customerData.ateco_code = atecoCode.value
    customerData.unique_code = uniqueCode.value
  }

  isSaving.value = true
  
  try {
    const response = await $api('/customers', {
      method: 'POST',
      body: customerData,
    })
    
    isSaving.value = false
    
    // Redirect to the customer detail page
    if (response.id) {
      emit('customerData', response)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
      })
    }
  } catch (error) {
    isSaving.value = false
    errorMessage.value = error.response?._data?.message || 'Errore durante il salvataggio del cliente. Riprova.'
  }
}

const filteredProvinces = computed(() => {
  if (!region.value) return []
  
  const regionValue = typeof region.value === 'object' ? region.value.value : region.value
  return provinces.filter(province => province.region === regionValue)
})

</script>

<template>
  <VForm ref="refForm" @submit.prevent="createUser">
    <!-- 👉 Alert per errori -->
    <VAlert
      v-if="errorMessage"
      type="error"
      :text="errorMessage"
      class="mb-4"
    />
    
    <VRow>
      <!-- 👉 Customer category -->
      <VCol
        cols="12"
        md="12"
      >
        <AppSelect
          v-model="category"
          label="Tipologia *"
          placeholder="Seleziona"
          :items="categories"
          :rules="[requiredValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="12"
      >
        <h5 class="text-h5 mt-6">
          INFORMAZIONI
        </h5>
      </VCol>

      <!-- 👉 First Name -->
      <VCol
        cols="12"
        md="6"
        v-if="category !== 'Business'"
      >
        <AppTextField
          v-model="name"
          label="Nome *"
          placeholder="Mario"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Last Name -->
      <VCol
        cols="12"
        md="6"
        v-if="category !== 'Business'"
      >
        <AppTextField
        v-model="lastName"
        label="Cognome"
        placeholder="Rossi"
        :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Business Name -->
      <VCol
        cols="12"
        md="12"
        v-if="category !== 'Residenziale'"
      >
        <AppTextField
        v-model="businessName"
        label="Ragione Sociale *"
        placeholder="Società SRL"
        :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Tax Code -->
      <VCol
        cols="12"
        md="6"
        v-if="category !== 'Business'"
      >
        <AppTextField
          v-model="taxIdCode"
          label="Codice Fiscale"
          placeholder="ABCDEF12G34H567I"
          maxlength="16"
          :rules="[requiredValidator, (v) => lengthValidator(v, 16)]"
          :custom-error="taxIdError"
          :show-error-details="!!taxIdError"
          @click:errorDetails="openDuplicateModal"
          @input="taxIdCode = taxIdCode.toUpperCase()"
        />
      </VCol>

      <!-- 👉 VAT Number -->
      <VCol
        cols="12"
        md="6"
        v-if="category !== 'Residenziale'"
      >
        <AppTextField
          v-model="vatNumber"
          label="Partita IVA *"
          placeholder="12345678901"
          :rules="[requiredValidator]"
          :custom-error="vatError"
          :show-error-details="!!vatError"
          @click:errorDetails="openDuplicateModal"
        />
      </VCol>

      <!-- 👉 Ateco Code -->
      <VCol
        cols="12"
        md="6"
        v-if="category !== 'Residenziale'"
      >
        <AppTextField
          v-model="atecoCode"
          label="Codice Ateco"
          placeholder="123456"
        />
      </VCol>

      <!-- 👉 Unique Code -->
      <!-- <VCol
        cols="12"
        md="6"
        v-if="category !== 'Residenziale'"
      >
        <AppTextField
          v-model="uniqueCode"
          label="Codice Unico"
          placeholder="123456"
        />
      </VCol> -->

      <VCol
        cols="12"
        md="12"
      >
        <h5 class="text-h5 mt-6">
          CONTATTI
        </h5>
      </VCol>

      <!-- 👉 Phone -->
      <VCol
        cols="12"
        md="6"
      >
        <CustomerTelPhoneInput
          v-model="phone"
          type="phone"
          label="Telefono"
          placeholder="Telefono fisso"
          name="phone"
          :custom-error="phoneError"
          :show-error-details="!!phoneError"
          @onCheckUpdate="handlePhoneCheckUpdate"
          @onValue="handlePhoneValue"
          @click:errorDetails="openDuplicateModal"
        />
      </VCol>

      <!-- 👉 Mobile -->
      <VCol
        cols="12"
        md="6"
      >
        <CustomerTelPhoneInput
          v-model="mobile"
          type="mobile"
          label="Cellulare *"
          placeholder="Cellulare"
          name="mobile"
          required
          :custom-error="mobileError"
          :show-error-details="!!mobileError"
          @onCheckUpdate="handleMobileCheckUpdate"
          @onValue="handleMobileValue"
          @click:errorDetails="openDuplicateModal"
        />
      </VCol>

      <!-- 👉 Email -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="email"
          label="Email"
          placeholder="mail@mail.com"
          :rules="[requiredValidator]"
          :custom-error="emailError"
          :show-error-details="!!emailError"
          @click:errorDetails="openDuplicateModal"
        />
      </VCol>


      <!-- 👉 PEC -->
      <!-- <VCol
        cols="12"
        md="6"
        v-if="category !== 'Residenziale'"
      >
        <AppTextField
          v-model="pec"
          label="PEC"
          placeholder="mail@pec.com"
        />
      </VCol> -->

      <VCol
        cols="12"
        md="12"
      >
        <h5 class="text-h5 mt-6">
          INDIRIZZO
        </h5>
      </VCol>

      <!-- 👉 Address -->
      <VCol
        cols="12"
        md="12"
      >
        <AppTextField
          v-model="address"
          label="Indirizzo *"
          placeholder="Via Roma 123"
          :rules="[requiredValidator]"
        />
      </VCol>

      

      <!-- 👉 Region -->
      <VCol
        cols="12"
        md="6"
      >
        <AppCombobox
          v-model="region"
          label="Regione *"
          placeholder="Seleziona"
          :rules="[requiredValidator]"
          :items="regions"
          item-title="title"
          item-value="value"
          clearable
        />
      </VCol>

      <!-- 👉 Province -->
      <VCol
        cols="12"
        md="6"
      >
        <AppCombobox
          v-model="province"
          label="Provincia"
          placeholder="Seleziona"
          :rules="[requiredValidator]"
          :items="filteredProvinces"
          item-title="title"
          item-value="value"
          clearable
        />
      </VCol>

      <!-- 👉 City -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="city"
          label="Città *"
          placeholder="Roma"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Zip -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="zip"
          label="CAP"
          placeholder="00100"
        />
      </VCol>

      <!-- 👉 Privacy -->
      <VCol
        cols="12"
        md="12"
      >
        <VCheckbox
          v-model="privacy"
          label="Privacy"
          color="primary"
          hide-details
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex gap-4"
      >
        <VBtn type="submit" :disabled="isSaving">
          Crea
        </VBtn>

        <VBtn
          type="reset"
          color="secondary"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
    
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
            @click="isDuplicateModalVisible = false; isModalOpenedFromCreate = false"
          >
            <VIcon icon="tabler-x" />
          </VBtn>
        </VCardTitle>
        
        <VDivider />
        
        <VCardText class="pa-4">
          <p class="mb-4">Abbiamo trovato i seguenti clienti con dati simili. Seleziona un cliente esistente oppure chiudi per continuare con la creazione:</p>
          
          <VTable>
            <thead>
              <tr>
                <th class="text-left" style="width: 50px;">
                  Visualizza
                </th>
                <th class="text-left">Azioni</th>
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
                <td>
                  <VBtn
                    icon
                    variant="tonal"
                    color="info"
                    size="small"
                    @click="openCustomerDetail(user)"
                  >
                    <VIcon icon="tabler-eye" />
                    <VTooltip activator="parent" location="top">
                      Visualizza Scheda Cliente
                    </VTooltip>
                  </VBtn>
                </td>
                <td>
                  <VBtn
                    size="small"
                    color="primary"
                    @click="selectExistingCustomer(user)"
                  >
                    Seleziona
                  </VBtn>
                </td>
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
        
        <VCardActions class="pa-4 justify-space-between">
          <VBtn
            v-if="!isModalOpenedFromCreate"
            variant="tonal"
            color="secondary"
            @click="isDuplicateModalVisible = false; isModalOpenedFromCreate = false"
          >
            Chiudi
          </VBtn>
          <div v-else class="d-flex gap-2">
            <VBtn
              variant="tonal"
              color="secondary"
              @click="isDuplicateModalVisible = false; isModalOpenedFromCreate = false"
            >
              Annulla
            </VBtn>
            <VBtn
              color="warning"
              @click="forceCreate"
              :loading="isSaving"
            >
              Forza creazione
            </VBtn>
          </div>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Customer Detail Dialog -->
    <VDialog
      v-model="isCustomerDetailModalVisible"
      max-width="900"
    >
      <VCard>
        <VCardTitle class="pa-4 d-flex align-center justify-space-between">
          <span class="text-h6">Scheda Cliente</span>
          <VBtn
            icon
            variant="text"
            color="default"
            @click="isCustomerDetailModalVisible = false"
          >
            <VIcon icon="tabler-x" />
          </VBtn>
        </VCardTitle>
        <VDivider />
        <VCardText class="pa-4">
          <CustomerBioPanel 
            v-if="selectedCustomerForDetail"
            :customer-data="selectedCustomerForDetail"
            @updateCustomerData="(updatedData) => selectedCustomerForDetail = updatedData"
          />
        </VCardText>
        <VCardActions class="pa-4 justify-end">
          <VBtn
            variant="tonal"
            color="secondary"
            @click="isCustomerDetailModalVisible = false"
          >
            Chiudi
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VForm>
</template>
