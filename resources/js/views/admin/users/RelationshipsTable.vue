<script setup>
const props = defineProps({
  userData: {
    type: Object,
    required: true,
  },
})

const route = useRoute('admin-users-id')
const searchQuery = ref('')
const selectedStatus = ref()

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

const loggedInUser = useCookie('userData').value
const isAdmin = loggedInUser.roles.some(role => role.name === 'gestione' || role.name === 'backoffice' || role.name === 'amministrazione')

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const isLoading = ref(false)
const isAddDialogVisible = ref(false)
const selectedRelationshipsAdd = ref([])
const isRemoveDialogVisible = ref(false)
const selectedRelationshipRemove = ref()

// 👉 headers
const headers = [
  {
    title: 'Account',
    key: 'name',
  },
  {
    title: 'Ruolo',
    key: 'role',
    sortable: false,
  },
  {
    title: 'Abilitato',
    key: 'enabled',
    sortable: false,
  },
  {
    title: 'Area',
    key: 'area',
  },
  {
    title: 'Team Leader',
    key: 'team_leader',
    sortable: false,
  },
  {
    title: '',
    key: 'actions',
    sortable: false,
  },
]

const {
  data: usersData,
  execute: fetchUsers,
} = await useApi(createUrl('/users', {
  query: {
    q: searchQuery,
    relationships: route.params.id,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const users = computed(() => usersData.value.users)
const totalUsers = computed(() => usersData.value.totalUsers)

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

const deleteRelationship = async user => {
  const response = await $api(`/users/${route.params.id}/relationships/${user.id}`, {
    method: 'DELETE',
  })
  isRemoveDialogVisible.value = false

  fetchUsers()
}

const selectRelationshipForRemove = user => {
  selectedRelationshipRemove.value = user
  isRemoveDialogVisible.value = true
}

const addRelationship = async () => {
  const response = await $api(`/users/${route.params.id}/relationships`, {
    method: 'POST',
    body: {
      users: selectedRelationshipsAdd.value,
    },
  })
  isAddDialogVisible.value = false
  selectedRelationshipsAdd.value = []

  fetchUsers()
}

const allUsers = ref([])
const fetchAllUsers = async () => {
  allUsers.value = []
  const response = await $api('/users?itemsPerPage=99999999&select=1')
  for (let i = 0; i < response.users.length; i++) {
    allUsers.value.push({
      title: [response.users[i].name, response.users[i].last_name].join(' ') + ` (${response.users[i].role?.name})`,
      value: response.users[i].id,
    })
  }
}
await fetchAllUsers()
</script>

<template>
  <section v-if="users">
    <VCard id="invoice-list">
      <VCardText>
        <div class="d-flex align-center justify-space-between flex-wrap gap-4">
          <div class="text-h5">
            Account Collegati
          </div>
          <div class="d-flex align-center gap-x-4">
            <AppSelect
              :model-value="itemsPerPage"
              :items="[
                { value: 10, title: '10' },
                { value: 25, title: '25' },
                { value: 50, title: '50' },
                { value: 100, title: '100' },
                { value: -1, title: 'All' },
              ]"
              style="inline-size: 6.25rem;"
              @update:model-value="itemsPerPage = parseInt($event, 10)"
            />

            <!-- 👉 Add user -->
            <VBtn
              prepend-icon="tabler-link"
              @click="isAddDialogVisible = true"
              v-if="$can('edit', 'users')"
              :disabled="!props.userData?.team_leader"
            >
              Collega Account
            </VBtn>
          </div>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION Datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :loading="isLoading"
        :items-length="totalUsers"
        :headers="headers"
        :items="users"
        item-value="total"
        class="text-no-wrap text-sm rounded-0"
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

        <!-- Area -->
        <template #item.area="{ item }">
          <div class="text-body-1 text-high-emphasis">
            {{ item.area }}
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
          <IconBtn @click="selectRelationshipForRemove(item)" v-if="$can('edit', 'users')">
            <VIcon
              color="error"
              icon="tabler-unlink"
            />
          </IconBtn>
        </template>

        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalUsers"
          />
        </template>
      </VDataTableServer>
      <!-- !SECTION -->
    </VCard>

    <VDialog
      v-model="isRemoveDialogVisible"
      width="500"
      v-if="selectedRelationshipRemove"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isRemoveDialogVisible = !isRemoveDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Elimina Account Collegato">
        <VCardText>
          Sei sicuro di voler eliminare <b>{{ [selectedRelationshipRemove.name, selectedRelationshipRemove.last_name].join(' ').trim() }}</b> come account collegato?
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn color="error" @click="deleteRelationship(selectedRelationshipRemove)">
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog
      v-model="isAddDialogVisible"
      width="700"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isAddDialogVisible = !isAddDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Collega Account">
        <VCardText>
          <AppAutocomplete
            v-model="selectedRelationshipsAdd"
            label="Account"
            :items="allUsers"
            clearable
            multiple
            chips
            closable-chips
            placeholder="Seleziona uno o più Account"
          />
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn @click="addRelationship">
            Collega
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>

<style lang="scss">
#invoice-list {
  .invoice-list-actions {
    inline-size: 8rem;
  }

  .invoice-list-search {
    inline-size: 12rem;
  }
}
</style>
