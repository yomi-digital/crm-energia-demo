<script setup>
const props = defineProps({
  formData: {
    type: null,
    required: true,
  },
})

const emit = defineEmits(['update:formData'])

const formData = ref(props.formData)

watch(formData, () => {
  emit('update:formData', formData.value)
})

const customers = ref([])
const search = ref()
const loading = ref(false)

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

const fetchCustomers = async (query, id = null) => {
  try {
    loading.value = true
    const response = await $api('/customers?itemsPerPage=10&select=1&q=' + query + (id ? '&id=' + id : ''))
    customers.value = response.customers.map(customer => ({
      title: getCustomerName(customer),
      value: customer.id,
    }))
    
    // If this was an initial load with ID, set the formData properly
    if (id && customers.value.length === 1) {
      formData.value.id = customers.value[0]
    }
  } finally {
    loading.value = false
  }
}

// Load initial customer if we have an ID
if (formData.value.id) {
  const numericId = typeof formData.value.id === 'object' ? formData.value.id.value : formData.value.id
  await fetchCustomers('', numericId)
}

// Watch for search changes and fetch customers
watch(search, (query) => {
  if (!query && !formData.value.id) {
    customers.value = []
    return
  }
  if (query && query.length >= 2) {
    fetchCustomers(query)
  }
})

const isAppointment = ref(false)

const appointments = ref([])
const searchAppointment = ref()
const loadingAppointment = ref(false)

const fetchAppointments = async (query) => {
  const response = await $api('/appointments?itemsPerPage=999999select=1&q=' + query)
  appointments.value = response.map(appointment => ({
    title: appointment.start + ' - ' + appointment.title,
    value: appointment.id,
  }))
}
// fetchAppointments('')
// When isAppointment changes, then it should fetch the appointments
watch(() => isAppointment.value, () => {
  fetchAppointments('')
})

// watch(searchAppointment, query => {
//   query && query !== formData.value.appointment_id && fetchAppointments(query)
// })
watch(() => formData.value.id, (newVal, oldVal) => {
  if (!newVal) {
    formData.value.name = null
    return
  }
  
  if (typeof newVal === 'object') {
    formData.value.name = newVal.title
  } else if (typeof newVal === 'number' && oldVal && typeof oldVal === 'object') {
    // If we're getting a numeric ID but had an object before, restore the object
    formData.value.id = oldVal
  }
})
watch(() => formData.value.appointment_id, () => {
  const selected = appointments.value.find(appointment => appointment.value === formData.value.appointment_id)
  formData.value.appointment_title = selected.title
})
</script>

<template>
  <VForm>
    <VRow>
      <VCol
        cols="12"
        sm="12"
      >
        <AppAutocomplete
          v-model="formData.id"
          v-model:search="search"
          :loading="loading"
          label="Cliente"
          :items="customers"
          placeholder="Seleziona un Cliente"
          return-object
          item-title="title"
          item-value="value"
          clearable
        />
        <div class="d-flex align-center gap-2 mt-2">
          <RouterLink
            :to="{ name: 'workflow-customers-create' }"
            class="text-sm"
            title="Crea nuovo cliente"
          >
            Crea nuovo cliente <VIcon icon="tabler-external-link" size="small" />
          </RouterLink>
        </div>
      </VCol>

      <VCol
        cols="12"
        sm="12"
      >
        <VSwitch
          :disabled="!formData.id"
          v-model="isAppointment"
          label="Da appuntamento?"
        />
      </VCol>

      <VCol
        cols="12"
        sm="12"
        v-show="isAppointment"
      >
        <AppAutocomplete
          v-model="formData.appointment_id"
          v-model:search="searchAppointment"
          :loading="loadingAppointment"
          label="Appuntamento"
          :items="appointments"
          placeholder="Seleziona un Appuntamento"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
      >
        <AppTextField
          v-model="formData.account_pod_pdr"
          label="Account / POD / PDR (opzionale per ALLACCIO)"
          placeholder="09886655"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
      >
        <AppTextField
          v-model="formData.annual_consumption"
          label="Consumo Annuale (opzionale)"
          placeholder="0"
        />
      </VCol>
    </VRow>
  </VForm>
</template>
