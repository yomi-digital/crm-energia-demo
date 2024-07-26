<script setup>
import AddNewUserDrawer from '@/views/admin/users/AddNewUserDrawer.vue';

// 👉 Store
const searchQuery = ref('')
const selectedRole = ref()
const selectedTeamLeader = ref()
const selectedStatus = ref()

// Data table options
const itemsPerPage = ref(25)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

const router = useRouter()

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

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
    title: 'Ruolo',
    key: 'role',
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
    title: 'Capo Area',
    key: 'manager',
  },
  {
    title: 'Agenzia',
    key: 'agency',
  },
  {
    title: 'Area',
    key: 'area',
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

await $api('/roles').then(response => {
  for (let i = 0; i < response.roles.length; i++) {
    roles.push({
      title: ucfirst(response.roles[i].name),
      value: response.roles[i].name,
    })
  }
})

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

const resolveUserRoleVariant = role => {
  if (! role) {
    role = { name: 'undefined' }
  }

  const roleLowerCase = role.name.toLowerCase()
  if (roleLowerCase === 'struttura')
    return {
      color: 'success',
      icon: 'tabler-building',
    }
  if (roleLowerCase === 'telemarketing')
    return {
      color: 'info',
      icon: 'tabler-phone',
    }
  if (roleLowerCase === 'agente')
    return {
      color: 'success',
      icon: 'tabler-briefcase',
    }
  if (roleLowerCase === 'backoffice')
    return {
      color: 'warning',
      icon: 'tabler-device-desktop',
    }
  if (roleLowerCase === 'amministrazione')
    return {
      color: 'primary',
      icon: 'tabler-crown',
    }

  return {
    color: 'primary',
    icon: 'tabler-user',
  }
}

const resolveUserStatusVariant = stat => {
  if (stat !== 1)
    return 'error'
  if (stat === 1)
    return 'success'

  return 'primary'
}

const isAddNewUserDrawerVisible = ref(false)

const addNewUser = async userData => {
  const response = await $api('/users', {
    method: 'POST',
    body: userData,
  })

  // Redirect to user profile
  router.push({ name: 'admin-users-id', params: { id: response.id } })

  // refetch User
  // fetchUsers()
}

const deleteUser = async id => {
  await $api(`/apps/users/${ id }`, { method: 'DELETE' })

  // refetch User
  fetchUsers()
}
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filtri</VCardTitle>
      </VCardItem>

      <VCardText>
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
              v-if="item.avatar"
              size="34"
              :variant="!item.avatar ? 'tonal' : undefined"
              :color="!item.avatar ? resolveUserRoleVariant(item.role).color : undefined"
            >
              <VImg
                v-if="item.avatar"
                :src="item.avatar"
              />
              <span v-else>{{ avatarText(item.fullName) }}</span>
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

        <!-- 👉 Role -->
        <template #item.role="{ item }">
          <div class="d-flex align-center gap-x-2">
            <VIcon
              :size="22"
              :icon="resolveUserRoleVariant(item.role).icon"
              :color="resolveUserRoleVariant(item.role).color"
            />

            <div class="text-capitalize text-high-emphasis text-body-1">
              {{ item.role.name }}
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

        <!-- Manager -->
        <template #item.manager="{ item }">
          <div class="text-body-1 text-high-emphasis">
            <RouterLink v-if="item.manager"
              :to="{ name: 'admin-users-id', params: { id: item.manager_id } }"
              class="font-weight-medium text-link"
            >
              {{ [item.manager.name, item.manager.last_name].join(' ') }}
            </RouterLink>
          </div>
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
  </section>
</template>
