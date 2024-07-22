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

const tabs = [
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
        <VWindowItem>
          <UserTabRelationships />
        </VWindowItem>

        <VWindowItem>
          <UserTabBrands />
        </VWindowItem>

        <VWindowItem>
          <UserTabAppointments />
        </VWindowItem>

        <VWindowItem>
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
</template>
