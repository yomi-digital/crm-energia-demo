<script setup>
definePage({
  meta: {
    action: 'view',
    subject: 'customers',
  },
})

import CustomerBioPanel from '@/views/workflow/customers/CustomerBioPanel.vue';
import CustomerTabPaperworks from '@/views/workflow/customers/CustomerTabPaperworks.vue';

const route = useRoute('workflow-customers-id')
const customerTab = ref(null)

const tabs = [
  {
    icon: 'tabler-file-text',
    title: 'Pratiche',
  },
]

const {
  data: customerData,
  execute: fetchCustomer,
} = await useApi(createUrl(`/customers/${ route.params.id }`))

const updatedCustomerData = (newData) => {
  console.log(newData)
  fetchCustomer()
}
</script>

<template>
  <VRow v-if="customerData">
    <VCol
      cols="12"
      md="5"
      lg="4"
    >
      <CustomerBioPanel :customer-data="customerData" @update-customer-data="updatedCustomerData" />
    </VCol>

    <VCol
      cols="12"
      md="7"
      lg="8"
    >
      <CustomerTabPaperworks />
    </VCol>
  </VRow>
  <div v-else>
    <VAlert
      type="error"
      variant="tonal"
    >
      Cliente ID  {{ route.params.id }} non trovato!
    </VAlert>
  </div>
</template>
