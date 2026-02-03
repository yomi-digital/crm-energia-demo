<script setup>
import StatusChip from '@/components/StatusChip.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)

watch(() => props.modelValue, (newVal) => {
  isOpen.value = newVal
  if (newVal) {
    fetchPaperworks()
  }
})

watch(isOpen, (newVal) => {
  if (!newVal) {
    emit('update:modelValue', false)
  }
})

const itemsPerPage = ref(10)
const page = ref(1)
const paperworks = ref([])
const totalPaperworks = ref(0)
const isLoading = ref(false)

const fetchPaperworks = async () => {
  isLoading.value = true
  try {
    const response = await $api('/dashboard/paperworks', {
      params: {
        page: page.value,
        itemsPerPage: itemsPerPage.value,
      },
    })
    paperworks.value = response.data.data
    totalPaperworks.value = response.data.total
  } catch (error) {
    console.error('Error fetching paperworks:', error)
  } finally {
    isLoading.value = false
  }
}

const updateOptions = async ({ page: newPage, itemsPerPage: newItemsPerPage }) => {
  page.value = newPage
  itemsPerPage.value = newItemsPerPage
  await fetchPaperworks()
}

const close = () => {
  isOpen.value = false
}
</script>

<template>
  <VDialog
    v-model="isOpen"
    max-width="1200"
  >
    <VCard>
      <VCardItem class="d-flex justify-space-between align-center">
        <VCardTitle>Pratiche Sospese</VCardTitle>
        <template #append>
          <VBtn
            icon="tabler-x"
            variant="text"
            color="default"
            @click="close"
          />
        </template>
      </VCardItem>
      
      <VCardText>
        <VDataTableServer
          v-model:items-per-page="itemsPerPage"
          v-model:page="page"
          :items="paperworks"
          :items-length="totalPaperworks"
          :loading="isLoading"
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
            <StatusChip 
              :status="item.state" 
              size="small"
              fallback-style="text"
              class="compact-chip"
            />
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
              :total-items="totalPaperworks"
            />
          </template>
        </VDataTableServer>
      </VCardText>
    </VCard>
  </VDialog>
</template>
