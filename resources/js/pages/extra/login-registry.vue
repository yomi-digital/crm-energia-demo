<script setup>
import { getAvatar, resolveUserRoleVariant } from '@/utils/userRole'

definePage({
  meta: {
    layout: 'default',
    requiresAuth: true,
    action: 'access',
    subject: 'extra-login-registry',
  },
})

// Controllo permessi admin
const loggedInUser = useCookie('userData').value
const isAdmin = loggedInUser.roles.some(role => role.name === 'gestione' || role.name === 'backoffice' || role.name === 'amministrazione')

// Redirect se non è admin
if (!isAdmin) {
  await navigateTo('/not-authorized')
}

// Data table options
const itemsPerPage = ref(100)
const maxItemsPerPage = 100
const page = ref(1)
const sortBy = ref('last_login_at')
const orderBy = ref('desc')

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key || 'last_login_at'
  orderBy.value = options.sortBy[0]?.order || 'desc'
  
  // Limita il numero di elementi per pagina
  if (options.itemsPerPage > maxItemsPerPage) {
    itemsPerPage.value = maxItemsPerPage
  }
}

// Headers
const headers = [
  {
    title: 'Username',
    key: 'username',
    sortable: false,
  },
  {
    title: 'Ultimo Login',
    key: 'last_login_at',
    sortable: true,
  },
  {
    title: 'Ultimo Logout',
    key: 'last_logout_at',
    sortable: true,
  },
  {
    title: 'Indirizzo IP',
    key: 'ip',
    sortable: true,
  },
]

const {
  data: loginLogsData,
  execute: fetchLoginLogs,
} = await useApi(createUrl('/login-logs', {
  query: {
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const sortedLoginLogs = computed(() => {
  const logs = loginLogsData.value?.users || []
  
  if (!sortBy.value) return logs
  
  return [...logs].sort((a, b) => {
    let aValue = a[sortBy.value]
    let bValue = b[sortBy.value]
    
    // Gestione valori null/undefined
    if (!aValue && !bValue) return 0
    if (!aValue) return orderBy.value === 'asc' ? -1 : 1
    if (!bValue) return orderBy.value === 'asc' ? 1 : -1
    
    // Ordinamento per date
    if (sortBy.value === 'last_login_at' || sortBy.value === 'last_logout_at') {
      aValue = new Date(aValue)
      bValue = new Date(bValue)
    }
    
    // Ordinamento per IP (stringa)
    if (sortBy.value === 'ip') {
      aValue = aValue || ''
      bValue = bValue || ''
    }
    
    if (orderBy.value === 'asc') {
      return aValue > bValue ? 1 : -1
    } else {
      return aValue < bValue ? 1 : -1
    }
  })
})

const loginLogs = computed(() => sortedLoginLogs.value)
const totalLoginLogs = computed(() => loginLogsData.value?.totalUsers || 0)

// Format date function
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Intl.DateTimeFormat('it-IT', { 
    day: '2-digit', 
    month: '2-digit', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date))
}
</script>

<template>
  <VCard>
    <VCardText>
      <h1 class="text-h3 mb-4">
        Registro Accessi
      </h1>
      
      <!-- Data Table -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="loginLogs"
        :headers="headers"
        :items-length="totalLoginLogs"
        :loading="false"
        :items-per-page-options="[10, 25, 50, 100]"
        @update:options="updateOptions"
      >
        <!-- Username Column -->
        <template #item.username="{ item }">
          <div class="d-flex align-center">
            <VAvatar
              size="32"
              class="me-3"
              :variant="!item.avatar ? 'tonal' : undefined"
              :color="!item.avatar ? resolveUserRoleVariant(item.roles?.[0]).color : undefined"
            >
              <VImg
                v-if="item.avatar"
                :src="getAvatar(item)"
              />
              <VIcon
                v-else
                :icon="resolveUserRoleVariant(item.roles?.[0]).icon"
              />
            </VAvatar>
            <div class="d-flex flex-column">
              <span class="font-weight-medium">
                {{ item.username }}
              </span>
              <div class="text-sm text-medium-emphasis">
                {{ item.roles?.[0]?.name }}
              </div>
            </div>
          </div>
        </template>

        <!-- Header personalizzato per Ultimo Login -->
        <template #header.last_login_at>
          <div class="d-flex align-center">
            <span>Ultimo Login</span>
            <VIcon
              v-if="sortBy === 'last_login_at'"
              :icon="orderBy === 'asc' ? 'tabler-chevron-up' : 'tabler-chevron-down'"
              size="16"
              class="ms-1"
            />
          </div>
        </template>

        <!-- Header personalizzato per Ultimo Logout -->
        <template #header.last_logout_at>
          <div class="d-flex align-center">
            <span>Ultimo Logout</span>
            <VIcon
              v-if="sortBy === 'last_logout_at'"
              :icon="orderBy === 'asc' ? 'tabler-chevron-up' : 'tabler-chevron-down'"
              size="16"
              class="ms-1"
            />
          </div>
        </template>

        <!-- Header personalizzato per IP -->
        <template #header.ip>
          <div class="d-flex align-center">
            <span>Indirizzo IP</span>
            <VIcon
              v-if="sortBy === 'ip'"
              :icon="orderBy === 'asc' ? 'tabler-chevron-up' : 'tabler-chevron-down'"
              size="16"
              class="ms-1"
            />
          </div>
        </template>

        <!-- Last Login Column -->
        <template #item.last_login_at="{ item }">
          <VChip
            :color="item.last_login_at ? 'success' : 'error'"
            size="small"
            variant="tonal"
          >
            {{ formatDate(item.last_login_at) }}
          </VChip>
        </template>

        <!-- Last Logout Column -->
        <template #item.last_logout_at="{ item }">
          <VChip
            :color="item.last_logout_at ? 'info' : 'warning'"
            size="small"
            variant="tonal"
          >
            {{ formatDate(item.last_logout_at) }}
          </VChip>
        </template>

        <!-- IP Column -->
        <template #item.ip="{ item }">
          <VChip
            :color="item.ip ? 'primary' : 'error'"
            size="small"
            variant="tonal"
          >
            {{ item.ip || 'N/A' }}
          </VChip>
        </template>
      </VDataTableServer>
    </VCardText>
  </VCard>
</template> 
