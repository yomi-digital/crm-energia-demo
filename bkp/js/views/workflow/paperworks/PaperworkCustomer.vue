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

const fetchCustomers = async (query) => {
  const response = await $api('/customers?itemsPerPage=20&select=1&q=' + query)
  customers.value = response.customers.map(customer => ({
    title: getCustomerName(customer),
    value: customer.id,
  }))
}

watch(search, query => {
  query && query !== formData.value.id && fetchCustomers(query)
})

const isAppointment = ref(false)

const appointments = ref([])
const searchAppointment = ref()
const loadingAppointment = ref(false)

const fetchAppointments = async (query) => {
  const response = await $api('/appointments?select=1&q=' + query)
  appointments.value = response.map(appointment => ({
    title: appointment.start + ' - ' + appointment.title,
    value: appointment.id,
  }))
}

watch(searchAppointment, query => {
  query && query !== formData.value.appointment_id && fetchAppointments(query)
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
        />
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
    </VRow>
  </VForm>
</template>
