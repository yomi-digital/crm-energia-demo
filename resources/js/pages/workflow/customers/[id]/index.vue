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
  {
    icon: 'tabler-calendar',
    title: 'Appuntamenti',
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
      <VTabs
        v-model="customerTab"
        class="v-tabs-pill"
      >
        <VTab
          v-for="tab in tabs"
          :key="tab.icon"
        >
          <VIcon
            :size="18"
            :icon="tab.icon"
            class="me-1"
          />
          <span>{{ tab.title }}</span>
        </VTab>
      </VTabs>

      <VWindow
        v-model="customerTab"
        class="mt-6 disable-tab-transition"
        :touch="false"
      >
        <VWindowItem>
          <CustomerTabPaperworks />
        </VWindowItem>
      </VWindow>
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
