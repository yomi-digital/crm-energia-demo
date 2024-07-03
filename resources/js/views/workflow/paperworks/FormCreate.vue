<script setup>
import { ref } from 'vue';

const category = ref('ALLACCIO')
const type = ref('ENERGIA')
const customer = ref('')
const accountPodPdr = ref('')
const brand = ref('')
const product = ref('')
const orderCode = ref('')
const orderStatus = ref('INSERITO')
const pda = ref(0)
const agent = ref('')
const notes = ref('')
const ownerNotes = ref('')
const annualConsumpion = ref(0)
const partnerOutcome = ref('')
const partnerOutcomeDate = ref('')
const cancelDate = ref('')
const expirationDate = ref('')

const refForm = ref()
const isSaving = ref(false)

const emit = defineEmits([
  'paperworkData',
])

const categories = ref([
  { title: 'ALLACCIO', value: 'ALLACCIO' },
  { title: 'OTP', value: 'OTP' },
  { title: 'SUBENTRO', value: 'SUBENTRO' },
  { title: 'VOLTURA', value: 'VOLTURA' },
  { title: 'SWITCH', value: 'SWITCH' },
])

const types = ref([
  { title: 'ENERGIA', value: 'ENERGIA' },
  { title: 'TELEFONIA', value: 'TELEFONIA' },
])

const statuses = ref([
  { title: 'ACCANTONATO', value: 'ACCANTONATO' },
  { title: 'ATTIVO', value: 'ATTIVO' },
  { title: 'IN PROVISIONING', value: 'IN PROVISIONING' },
  { title: 'INSERITO', value: 'INSERITO' },
  { title: 'INVIATO', value: 'INVIATO' },
])

const customers = ref([])

const getCustomerName = (customer) => {
  let name = ''
  if (customer.name) {
    name = [customer.name, customer.last_name].join(' ').trim()
  } else {
    name = customer.business_name
  }
  if (customer.vat_number) {
    name += ` - ${customer.vat_number}`
  }
  if (customer.tax_id_code) {
    name += ` - ${customer.tax_id_code}`
  }

  return name
}

const fetchCustomers = async () => {
  customers.value = []
  const response = await $api('/customers?itemsPerPage=99999999&select=1')
  for (const customer of response.customers) {
    customers.value.push({
      title: getCustomerName(customer),
      value: customer.id,
    })
  }
}
await fetchCustomers()


const agents = ref([])

const fetchAgents = async () => {
  agents.value = []
  const response = await $api('/agents?itemsPerPage=99999999&select=1')
  for (let i = 0; i < response.agents.length; i++) {
    agents.value.push({
      title: [response.agents[i].name, response.agents[i].last_name].join(' '),
      value: response.agents[i].id,
    })
  }
}

const brands = ref([])
const searchBrand = ref()
const loadingBrands = ref(false)
const fetchBrands = async (query) => {
  const response = await $api('/brands?itemsPerPage=100&select=1&q=' + query)
  brands.value = response.brands.map(brand => ({
    title: brand.name,
    value: brand.id,
  }))
}
watch(searchBrand, query => {
  query && query !== brand.value && fetchBrands(query)
})

const products = ref([])

const fetchProducts = async () => {
  products.value = []
  const response = await $api('/products?itemsPerPage=99999999&select=1')
  for (const product of response.products) {
    products.value.push({
      title: product.name,
      value: product.id,
    })
  }
}




const createUser = async () => {
  let customerData = {
    category: category.value === 'all' ? null : category.value,
    email: email.value,
    phone: phone.value,
    mobile: mobile.value,
    address: address.value,
    region: region.value,
    province: province.value,
    city: city.value,
    zip: zip.value,
  }
  if (category.value === 'Residenziale' || category.value === 'all') {
    customerData.name = name.value
    customerData.last_name = lastName.value
    customerData.tax_id_code = taxIdCode.value
  }
  if (category.value === 'Business' || category.value === 'all') {
    customerData.business_name = businessName.value
    customerData.vat_number = vatNumber.value
    customerData.pec = pec.value
    customerData.ateco_code = atecoCode.value
    customerData.unique_code = uniqueCode.value
  }

  isSaving.value = true
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
}
</script>

<template>
  <VForm ref="refForm" @submit.prevent="createUser">
    <VRow>
      <!-- 👉 Category -->
      <VCol
        cols="12"
        md="6"
      >
        <AppSelect
          v-model="category"
          label="Tipologia"
          placeholder="Seleziona"
          :items="categories"
        />
      </VCol>

      <!-- 👉 Type -->
      <VCol
        cols="12"
        md="6"
      >
        <AppSelect
          v-model="type"
          label="Tipo"
          placeholder="Seleziona"
          :items="types"
        />
      </VCol>

      <!-- 👉 Brand -->
      <VCol
        cols="12"
        md="6"
      >
        <AppAutocomplete
          v-model="brand"
          v-model:search="searchBrand"
          :loading="loadingBrands"
          label="Brand"
          :items="brands"
          placeholder="Seleziona un brand"
        />
      </VCol>

      <!-- 👉 Product -->
      <VCol
        cols="12"
        md="6"
      >
        <AppSelect
          v-model="product"
          label="Prodotto"
          placeholder="Seleziona"
          :items="products"
        />
      </VCol>

      <!-- 👉 Agente -->
      <VCol
        cols="12"
        md="12"
      >
        <AppSelect
          v-model="agent"
          label="Agente"
          placeholder="Seleziona"
          :items="agents"
        />
      </VCol>

      <VCol
        cols="12"
        md="12"
      >
        <h5 class="text-h5 mt-6">
          INFORMAZIONI CLIENTE
        </h5>
      </VCol>

      <!-- 👉 Customer -->
      <VCol
        cols="12"
        md="12"
      >
        <AppSelect
          v-model="customer"
          label="Cliente"
          placeholder="Seleziona"
          :items="customers"
        />
      </VCol>

      <!-- 👉 Account / POD / PDR -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="accountPodPdr"
          label="Accoutn / POD / PDR"
          placeholder="09886655"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Consumo Annuale -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="annualConsumpion"
          label="Consumo Annuale"
          placeholder="0"
        />
      </VCol>

      <!-- 👉 PDA -->
      <VCol
        cols="12"
        md="6"
      >
        <AppSelect
          v-model="pda"
          label="PDA Originale"
          placeholder="Seleziona"
          :items="[ { title: 'Si', value: 1 }, { title: 'No', value: 0 } ]"
        />
      </VCol>

      <VCol
        cols="12"
        md="12"
      >
        <h5 class="text-h5 mt-6">
          ORDINE
        </h5>
      </VCol>

      <!-- 👉 Stato Ordine -->
      <VCol
        cols="12"
        md="6"
      >
        <AppSelect
          v-model="orderStatus"
          label="Stato Ordine"
          placeholder="Seleziona"
          :items="statuses"
        />
      </VCol>

      <!-- 👉 Codice Ordine -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="orderCode"
          label="Codice Ordine"
          placeholder="09886655"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Note -->
      <VCol
        cols="12"
        md="12"
      >
        <AppTextarea
          v-model="notes"
          label="Note"
          placeholder="Note"
        />
      </VCol>

      <!-- 👉 Note Alfacom -->
      <VCol
        cols="12"
        md="12"
      >
        <AppTextarea
          v-model="ownerNotes"
          label="Note Alfacom"
          placeholder="Note"
        />
      </VCol>

      <!-- 👉 Data Annullamento -->
      <VCol
        cols="12"
        md="6"
      >
        <AppDateTimePicker
          v-model="cancelDate"
          label="Data Annullamento"
          placeholder="Data Annullamento"
        />
      </VCol>

      <!-- 👉 Data Scadenza -->
      <VCol
        cols="12"
        md="6"
      >
        <AppDateTimePicker
          v-model="expirationDate"
          label="Data Scadenza"
          placeholder="Data Scadenza"
        />
      </VCol>

      <VCol
        cols="12"
        md="12"
      >
        <h5 class="text-h5 mt-6">
          PARTNER
        </h5>
      </VCol>

      <!-- 👉 Esito Partner -->
      <VCol
        cols="12"
        md="6"
      >
        <AppSelect
          v-model="partnerOutcome"
          label="Esito Partner"
          placeholder="Seleziona"
          :items="statuses"
        />
      </VCol>

      <!-- 👉 Data Esito Partner -->
      <VCol
        cols="12"
        md="6"
      >
        <AppDateTimePicker
          v-model="partnerOutcomeDate"
          label="Data Esito Partner"
          placeholder="Data Esito Partner"
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
  </VForm>
</template>
