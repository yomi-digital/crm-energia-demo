<script setup>
import CustomerBioPanel from '@/views/workflow/customers/CustomerBioPanel.vue'
import CustomerTabPaperworks from '@/views/workflow/customers/CustomerTabPaperworks.vue'
import UserTabBillingsPlans from '@/views/apps/user/view/UserTabBillingsPlans.vue'
import UserTabConnections from '@/views/apps/user/view/UserTabConnections.vue'
import UserTabNotifications from '@/views/apps/user/view/UserTabNotifications.vue'
import UserTabSecurity from '@/views/apps/user/view/UserTabSecurity.vue'

const route = useRoute('workflow-customers-id')
const userTab = ref(null)

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

const { data: userData } = await useApi(`/apps/users/${ route.params.id }`)

const {
  data: customerData,
  execute: fetchCustomer,
} = await useApi(createUrl(`/customers/${ route.params.id }`))

const updatedCustomerData = (newData) => {
  console.log(newData)
  fetchCustomer()
}


if (userData.value) {
  const [firstName, lastName] = userData.value.fullName.split(' ')

  userData.value.firstName = firstName
  userData.value.lastName = lastName
}
</script>

<template>
  <VRow v-if="userData">
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
        v-model="userTab"
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
        v-model="userTab"
        class="mt-6 disable-tab-transition"
        :touch="false"
      >
        <VWindowItem>
          <CustomerTabPaperworks />
        </VWindowItem>

        <VWindowItem>
          <UserTabSecurity />
        </VWindowItem>

        <VWindowItem>
          <UserTabBillingsPlans />
        </VWindowItem>

        <VWindowItem>
          <UserTabNotifications />
        </VWindowItem>

        <VWindowItem>
          <UserTabConnections />
        </VWindowItem>
      </VWindow>
    </VCol>
  </VRow>
  <div v-else>
    <VAlert
      type="error"
      variant="tonal"
    >
      Invoice with ID  {{ route.params.id }} not found!
    </VAlert>
  </div>
</template>
