<script setup>
definePage({
  meta: {
    action: 'view',
    subject: 'users',
  },
})

import UserBioPanel from '@/views/admin/users/UserBioPanel.vue';
import UserTabAppointments from '@/views/admin/users/UserTabAppointments.vue';
import UserTabBrands from '@/views/admin/users/UserTabBrands.vue';
import UserTabPaperworks from '@/views/admin/users/UserTabPaperworks.vue';
import UserTabRelationships from '@/views/admin/users/UserTabRelationships.vue';

const route = useRoute('admin-users-id')
const router = useRouter()
const userTab = ref(null)

// Toast variables
const isSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

let tabs = [
  {
    icon: 'tabler-users',
    title: 'Account Collegati',
  },
  {
    icon: 'tabler-badge-tm',
    title: 'Brands',
  },
  {
    icon: 'tabler-calendar',
    title: 'Appuntamenti',
  },
  {
    icon: 'tabler-file-text',
    title: 'Pratiche',
  },
]

watch(() => route.params.id, () => {
  router.go()
})

const {
  data: userData,
  execute: fetchUser,
} = await useApi(createUrl(`/users/${ route.params.id }`))

const updatedUserData = (newData) => {
  fetchUser()
}

const isMarketing = userData.value.role?.name === 'telemarketing'
const isTeamLeader = userData.value.role?.name === 'team leader'

if (isMarketing) {
  tabs = []
} else if (isTeamLeader) {
  tabs = [
    {
      icon: 'tabler-users',
      title: 'Account Collegati',
    },
  ]
}

// Check if user was just created and show success toast
onMounted(() => {
  if (route.query.created === 'true') {
    snackbarMessage.value = 'Utente creato con successo'
    snackbarColor.value = 'success'
    isSnackbarVisible.value = true
    
    // Remove query parameter to avoid showing toast on refresh
    router.replace({ 
      name: 'admin-users-id', 
      params: { id: route.params.id }
    })
  }
})
</script>

<template>
  <VRow v-if="userData">
    <VCol
      cols="12"
      md="5"
      lg="4"
    >
      <UserBioPanel :user-data="userData" @update-user-data="updatedUserData" />
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
        <VWindowItem v-if="$can('access', 'users') && !isMarketing">
          <UserTabRelationships :user-data="userData" />
        </VWindowItem>

        <VWindowItem v-if="$can('access', 'brands') && (!isMarketing && !isTeamLeader)">
          <UserTabBrands />
        </VWindowItem>

        <VWindowItem v-if="$can('access', 'calendar') && (!isMarketing && !isTeamLeader)">
          <UserTabAppointments />
        </VWindowItem>

        <VWindowItem v-if="$can('access', 'paperworks') && (!isMarketing && !isTeamLeader)">
          <UserTabPaperworks />
        </VWindowItem>
      </VWindow>
    </VCol>
  </VRow>
  <div v-else>
    <VAlert
      type="error"
      variant="tonal"
    >
      Account ID  {{ route.params.id }} non trovato!
    </VAlert>
  </div>

  <!-- 👉 Toast Notification -->
  <VSnackbar
    v-model="isSnackbarVisible"
    :color="snackbarColor"
    location="top end"
    variant="flat"
  >
    {{ snackbarMessage }}
  </VSnackbar>
</template>
