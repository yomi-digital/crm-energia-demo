<script setup>
import { computed, onMounted, ref, watch } from 'vue'

const props = defineProps({
  transfersHistory: {
    type: Array,
    default: () => [],
  },
})

const brands = ref([])
const users = ref([])
const isLoadingBrands = ref(false)
const isLoadingUsers = ref(false)

// Carica i brand
const fetchBrands = async () => {
  isLoadingBrands.value = true
  try {
    const response = await $api('/brands?itemsPerPage=999999')
    brands.value = response.brands || []
  } catch (error) {
    console.error('Failed to load brands:', error)
  } finally {
    isLoadingBrands.value = false
  }
}

// Carica gli utenti (solo se necessario)
const fetchUsers = async (userIds) => {
  if (!userIds || userIds.length === 0) return
  
  isLoadingUsers.value = true
  try {
    // Normalizza gli ID a Number per il confronto
    const uniqueUserIds = [...new Set(userIds.filter(id => id !== null).map(id => Number(id)))]
    if (uniqueUserIds.length === 0) return
    
    const response = await $api(`/users?itemsPerPage=999999`)
    const allUsers = response.users || []
    // Normalizza anche gli ID degli utenti per il confronto
    users.value = allUsers.filter(user => uniqueUserIds.includes(Number(user.id)))
  } catch (error) {
    console.error('Failed to load users:', error)
  } finally {
    isLoadingUsers.value = false
  }
}

// Ottieni il nome del brand dall'ID
const getBrandName = (brandId) => {
  if (!brandId) return 'N/A'
  const brand = brands.value.find(b => b.id === brandId)
  return brand ? brand.name : `Brand #${brandId}`
}

// Ottieni il nome dell'utente dall'ID
const getUserName = (userId) => {
  if (!userId) return 'N/A'
  // Normalizza gli ID per il confronto (gestisce sia string che number)
  const user = users.value.find(u => Number(u.id) === Number(userId))
  if (user) {
    const name = user.name || ''
    const lastName = user.last_name || ''
    return `${name} ${lastName}`.trim() || 'Nome non disponibile'
  }
  return `Utente #${userId}`
}

// Formatta la data
const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  try {
    const date = new Date(dateString)
    return new Intl.DateTimeFormat('it-IT', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }).format(date)
  } catch (error) {
    return dateString
  }
}

// Ordina i trasferimenti per data (più recenti prima)
const sortedTransfers = computed(() => {
  if (!props.transfersHistory || props.transfersHistory.length === 0) return []
  
  return [...props.transfersHistory].sort((a, b) => {
    const dateA = new Date(a.transferred_at)
    const dateB = new Date(b.transferred_at)
    return dateB - dateA // Più recenti prima
  })
})

// Estrai gli ID degli utenti per caricarli
const userIds = computed(() => {
  if (!props.transfersHistory) return []
  return props.transfersHistory.map(t => t.transferred_by).filter(id => id !== null)
})

onMounted(async () => {
  await fetchBrands()
  await fetchUsers(userIds.value)
})

// Watch per ricaricare gli utenti quando transfersHistory cambia
watch(() => props.transfersHistory, async (newHistory) => {
  if (newHistory && newHistory.length > 0) {
    await fetchUsers(userIds.value)
  }
}, { deep: true })
</script>

<template>
  <VCard
    variant="outlined"
    class="mt-6"
  >
    <VCardTitle class="d-flex align-center pa-4">
      <VIcon
        icon="tabler-transfer"
        class="me-3"
        size="24"
        color="primary"
      />
      <span class="text-h6">Cronologia Trasferimenti ad altro brand</span>
    </VCardTitle>

    <VDivider />

    <VCardText class="pa-0">
      <VTable v-if="sortedTransfers.length > 0">
        <thead>
          <tr>
            <th class="text-left pa-4">Brand di Partenza</th>
            <th class="text-left pa-4">Brand di Arrivo</th>
            <th class="text-left pa-4">Data e Ora</th>
            <th class="text-left pa-4">Trasferito da</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(transfer, index) in sortedTransfers"
            :key="index"
          >
            <td class="pa-4">
              <VChip
                size="small"
                color="warning"
                variant="tonal"
              >
                {{ getBrandName(transfer.from_brand_id) }}
              </VChip>
            </td>
            <td class="pa-4">
              <VChip
                size="small"
                color="success"
                variant="tonal"
              >
                {{ getBrandName(transfer.to_brand_id) }}
              </VChip>
            </td>
            <td class="pa-4">
              <div class="d-flex align-center">
                <VIcon
                  icon="tabler-clock"
                  size="16"
                  class="me-2 text-medium-emphasis"
                />
                <span class="text-body-2">{{ formatDateTime(transfer.transferred_at) }}</span>
              </div>
            </td>
            <td class="pa-4">
              <div class="d-flex align-center">
                <VIcon
                  icon="tabler-user"
                  size="16"
                  class="me-2 text-medium-emphasis"
                />
                <span class="text-body-2">{{ getUserName(transfer.transferred_by) }}</span>
              </div>
            </td>
          </tr>
        </tbody>
      </VTable>
      <div
        v-else
        class="pa-8 text-center"
      >
        <VIcon
          icon="tabler-transfer"
          size="48"
          class="mb-4 text-medium-emphasis"
        />
        <p class="text-body-1 text-medium-emphasis mb-0">
          Nessun trasferimento registrato
        </p>
      </div>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.v-table {
  thead {
    background-color: rgb(var(--v-theme-surface));
    
    th {
      font-weight: 600;
      color: rgb(var(--v-theme-on-surface));
    }
  }
  
  tbody {
    tr {
      &:hover {
        background-color: rgb(var(--v-theme-surface));
      }
    }
  }
}
</style>

