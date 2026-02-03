<script setup>
definePage({
  meta: {
    action: 'access',
    subject: 'users',
  },
})

const loggedInUser = useCookie('userData').value
const isAdmin = loggedInUser.roles.some(role => role.name === 'gestione' || role.name === 'backoffice' || role.name === 'amministrazione')

import { AREAS } from '@/utils/constants'
import AddNewUserDrawer from '@/views/admin/users/AddNewUserDrawer.vue'

// 👉 Store
const route = useRoute()
const searchQuery = ref(route.query.q || '')
const selectedRole = ref(route.query.role)
const selectedTeamLeader = ref(route.query.isTeamLeader)
const selectedStatus = ref(route.query.enabled)
const selectedArea = ref(route.query.area ?? '')

// Data table options
const itemsPerPage = ref(Number(route.query.itemsPerPage) || 25)
const page = ref(Number(route.query.page) || 1)
const sortBy = ref(route.query.sortBy)
const orderBy = ref(route.query.orderBy)

const router = useRouter()

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Update URL on filter change
watch([searchQuery, selectedRole, selectedTeamLeader, selectedStatus, selectedArea, itemsPerPage, page, sortBy, orderBy], () => {
  router.replace({
    query: {
      ...route.query,
      q: searchQuery.value,
      role: selectedRole.value,
      isTeamLeader: selectedTeamLeader.value,
      enabled: selectedStatus.value,
      area: selectedArea.value || undefined,
      itemsPerPage: itemsPerPage.value,
      page: page.value,
      sortBy: sortBy.value,
      orderBy: orderBy.value,
    }
  })
})

function ucfirst(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

// Headers
const headers = [
  {
    title: 'Account',
    key: 'name',
  },
  {
    title: 'Area',
    key: 'area',
  },
  {
    title: 'Ruolo',
    key: 'role',
    sortable: false,
  },
  {
    title: 'Codice Agente',
    key: 'agent_code',
  },
  {
    title: 'Abilitato',
    key: 'enabled',
  },
  {
    title: 'Agenzia',
    key: 'agency',
    sortable: false,
  },
  {
    title: 'Team Leader',
    key: 'team_leader',
  },
  // {
  //   title: '',
  //   key: 'actions',
  //   sortable: false,
  // },
]

const {
  data: usersData,
  execute: fetchUsers,
} = await useApi(createUrl('/users', {
  query: {
    q: searchQuery,
    isTeamLeader: selectedTeamLeader,
    enabled: selectedStatus,
    role: selectedRole,
    area: selectedArea,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const users = computed(() => usersData.value.users)
const totalUsers = computed(() => usersData.value.totalUsers)

// 👉 search filters
const roles = [
  {
    title: 'Tutti',
    value: '',
  },
]

if (isAdmin) {
  await $api('/roles').then(response => {
    for (let i = 0; i < response.roles.length; i++) {
    roles.push({
      title: ucfirst(response.roles[i].name),
      value: response.roles[i].name,
    })
    }
  })
}

const enabled = [
  {
    title: 'Tutti',
    value: '',
  },
  {
    title: 'SI',
    value: 1,
  },
  {
    title: 'NO',
    value: 0,
  },
]

const isTeamLeader = [
  {
    title: 'Tutti',
    value: '',
  },
  {
    title: 'SI',
    value: 1,
  },
  {
    title: 'NO',
    value: 0,
  },
]

import { getAvatar, resolveUserRoleVariant, resolveUserStatusVariant } from '@/utils/userRole'

const isAddNewUserDrawerVisible = ref(false)

// Toast variables
const isSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const areaOptions = AREAS.filter(a => a.value !== '').map(a => a.title)
const areaLoadingUserId = ref(null)

const addNewUser = async userData => {
  try {
    const response = await $api('/users', {
      method: 'POST',
      body: userData,
    })

    // Redirect to user profile with success query parameter
    router.push({ 
      name: 'admin-users-id', 
      params: { id: response.id },
      query: { created: 'true' }
    })

    // refetch User
    // fetchUsers()
  } catch (error) {
    console.error(error)
    
    // Extract error message from API response
    let errorMessage = 'Errore durante la creazione dell\'utente'
    
    if (error?.data) {
      // Check for validation errors (422)
      if (error.data.errors && typeof error.data.errors === 'object') {
        const errorMessages = Object.values(error.data.errors).flat()
        errorMessage = errorMessages.join(', ')
      } 
      // Check for error message
      else if (error.data.message) {
        errorMessage = error.data.message
      }
      // Check for error field
      else if (error.data.error) {
        errorMessage = error.data.error
      }
    }
    
    snackbarMessage.value = errorMessage
    snackbarColor.value = 'error'
    isSnackbarVisible.value = true
  }
}

const deleteUser = async id => {
  await $api(`/apps/users/${ id }`, { method: 'DELETE' })

  // refetch User
  fetchUsers()
}

const updateUserArea = async (user, area) => {
  try {
    areaLoadingUserId.value = user.id

    await $api(`/users/${ user.id }`, {
      method: 'PUT',
      body: { area },
    })

    user.area = area

    snackbarMessage.value = 'Area aggiornata con successo'
    snackbarColor.value = 'success'
    isSnackbarVisible.value = true
  } catch (error) {
    console.error(error)

    snackbarMessage.value = 'Errore durante l\'aggiornamento dell\'area'
    snackbarColor.value = 'error'
    isSnackbarVisible.value = true
  } finally {
    areaLoadingUserId.value = null
  }
}


</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardItem class="pb-4" v-if="isAdmin">
        <VCardTitle>Filtri</VCardTitle>
      </VCardItem>

      <VCardText v-if="isAdmin">
        <VRow>
          <!-- 👉 Select Role -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedRole"
              placeholder="Filtra per Ruolo"
              :items="roles"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
          <!-- 👉 Select Plan -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedStatus"
              placeholder="Filtra per Abilitati"
              :items="enabled"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
          <!-- 👉 Select Status -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedTeamLeader"
              placeholder="Team Leader"
              :items="isTeamLeader"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
          <!-- 👉 Select Area -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedArea"
              placeholder="Filtra per Area"
              :items="AREAS"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3 d-flex gap-3">
          <AppSelect
            :model-value="itemsPerPage"
            :items="[
              { value: 10, title: '10' },
              { value: 25, title: '25' },
              { value: 50, title: '50' },
              { value: 100, title: '100' },
              { value: 9999999, title: 'All' },
            ]"
            style="inline-size: 6.25rem;"
            @update:model-value="itemsPerPage = parseInt($event, 10)"
          />
        </div>
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
          <!-- 👉 Search  -->
          <div style="inline-size: 15.625rem;">
            <AppTextField
              v-model="searchQuery"
              placeholder="Cerca"
            />
          </div>

          <!-- 👉 Export button -->
          <!-- <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="tabler-upload"
          >
            Esporta
          </VBtn> -->

          <!-- 👉 Add user button -->
          <VBtn
            prepend-icon="tabler-plus"
            @click="isAddNewUserDrawerVisible = true"
            v-if="$can('create', 'users')"
          >
            Crea Account
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="users"
        :items-length="totalUsers"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- User -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <VAvatar
              size="34"
              :variant="!item.avatar ? 'tonal' : undefined"
              :color="!item.avatar ? resolveUserRoleVariant(item.role).color : undefined"
            >
              <VImg
                :src="getAvatar(item)"
              />
            </VAvatar>
            <div class="d-flex flex-column">
              <h6 class="text-base">
                <RouterLink
                  :to="{ name: 'admin-users-id', params: { id: item.id } }"
                  class="font-weight-medium text-link"
                >
                  {{ [item.name, item.last_name].join(' ') }}
                </RouterLink>
              </h6>
              <div class="text-sm">
                {{ item.email }}
              </div>
            </div>
          </div>
        </template>

        <!-- Area -->
        <template #item.area="{ item }">
          <VMenu>
            <template #activator="{ props }">
              <VChip
                v-bind="props"
                color="primary"
                size="small"
                label
                class="text-uppercase cursor-pointer"
              >
                <VProgressCircular
                  v-if="areaLoadingUserId === item.id"
                  indeterminate
                  size="16"
                  width="2"
                  color="white"
                />
                <span v-else>
                  {{ item.area || 'Seleziona' }}
                </span>
              </VChip>
            </template>

            <VList>
              <VListItem
                v-for="option in areaOptions"
                :key="option"
                @click="updateUserArea(item, option)"
              >
                <VListItemTitle>{{ option }}</VListItemTitle>
              </VListItem>
            </VList>
          </VMenu>
        </template>

        <!-- 👉 Role -->
        <template #item.role="{ item }">
          <div class="d-flex align-center gap-x-2">
            <VIcon
              :size="22"
              :icon="resolveUserRoleVariant(item.role).icon"
              :color="resolveUserRoleVariant(item.role).color"
            />

            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.role?.name }}
            </div>
          </div>
        </template>

        <!-- Status -->
        <template #item.enabled="{ item }">
          <VIcon
              :size="22"
              :icon="item.enabled ? 'tabler-check' : 'tabler-x'"
              :color="resolveUserStatusVariant(item.enabled)"
            />
        </template>

        <!-- Agency -->
        <template #item.agency="{ item }">
          <div class="text-body-1 text-high-emphasis">
            {{ item.agency?.name }}
          </div>
        </template>

        <!-- Team Leader -->
        <template #item.team_leader="{ item }">
          <VChip
            :color="item.team_leader ? 'success' : 'error'"
            size="small"
            label
            class="text-capitalize"
          >
            {{ item.team_leader ? 'SI' : 'NO' }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="deleteUser(item.id)">
            <VIcon icon="tabler-trash" />
          </IconBtn>

          <IconBtn>
            <VIcon icon="tabler-eye" />
          </IconBtn>

          <VBtn
            icon
            variant="text"
            color="medium-emphasis"
          >
            <VIcon icon="tabler-dots-vertical" />
            <VMenu activator="parent">
              <VList>
                <VListItem :to="{ name: 'apps-user-view-id', params: { id: item.id } }">
                  <template #prepend>
                    <VIcon icon="tabler-eye" />
                  </template>

                  <VListItemTitle>View</VListItemTitle>
                </VListItem>

                <VListItem link>
                  <template #prepend>
                    <VIcon icon="tabler-pencil" />
                  </template>
                  <VListItemTitle>Edit</VListItemTitle>
                </VListItem>

                <VListItem @click="deleteUser(item.id)">
                  <template #prepend>
                    <VIcon icon="tabler-trash" />
                  </template>
                  <VListItemTitle>Delete</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalUsers"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    <!-- 👉 Add New User -->
    <AddNewUserDrawer
      v-model:isDrawerOpen="isAddNewUserDrawerVisible"
      :roles="roles.filter(r => r.value.length)"
      @user-data="addNewUser"
    />

    <!-- 👉 Toast Notification (solo per errori) -->
    <VSnackbar
      v-model="isSnackbarVisible"
      :color="snackbarColor"
      location="top end"
      variant="flat"
    >
      {{ snackbarMessage }}
    </VSnackbar>
  </section>
</template>
