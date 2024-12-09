<script setup>
import { watch } from 'vue';

const props = defineProps({
  userData: {
    type: Object,
    required: true,
  },
})

watch(() => props.userData, () => {
  console.log('props.userData', props.userData)
})

const emit = defineEmits([
  'updateUserData',
])


const isUserInfoEditDialogVisible = ref(false)

const updateUserInfo = async (data) => {
  const response = await $api(`/users/${ props.userData.id }`, {
    method: 'PUT',
    body: data,
  })
  emit('updateUserData', response)
}

</script>

<template>
  <VRow>
    <!-- SECTION User Details -->
    <VCol cols="12">
      <VCard v-if="props.userData">
        <VCardText>
          <!-- 👉 Details -->
          <h5 class="text-h5">
            Informazioni Account
          </h5>

          <VDivider class="my-4" />

          <!-- 👉 Customer Details list -->
          <VList class="card-list mt-2">
            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Ruolo:
                  <div class="d-inline-block text-body-1">
                    {{ props.userData.role.name }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem v-if="props.userData.name">
              <VListItemTitle>
                <h6 class="text-h6">
                  Nome:
                  <div class="d-inline-block text-body-1">
                    {{ props.userData.name }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem v-if="props.userData.last_name">
              <VListItemTitle>
                <h6 class="text-h6">
                  Cognome:
                  <div class="d-inline-block text-body-1">
                    {{ props.userData.last_name }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem v-if="props.userData.email">
              <VListItemTitle>
                <h6 class="text-h6">
                  Email:
                  <div class="d-inline-block text-body-1">
                    {{ props.userData.email }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem v-if="props.userData.email_verified_at">
              <VListItemTitle>
                <h6 class="text-h6">
                  Email Verificata il:
                  <div class="d-inline-block text-body-1">
                    {{ props.userData.email_verified_at }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem v-if="props.userData.phone">
              <VListItemTitle>
                <h6 class="text-h6">
                  Telefono:
                  <div class="d-inline-block text-body-1">
                    {{ props.userData.phone }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem v-if="props.userData.agent_code">
              <VListItemTitle>
                <h6 class="text-h6">
                  Codice Agente:
                  <div class="d-inline-block text-body-1">
                    {{ props.userData.agent_code }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem v-if="props.userData.area">
              <VListItemTitle>
                <h6 class="text-h6">
                  Area:
                  <div class="d-inline-block text-body-1">
                    {{ props.userData.area }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Created at -->
            <VListItem v-if="props.userData.created_at">
              <VListItemTitle>
                <h6 class="text-h6">
                  Creato il:
                  <div class="d-inline-block text-body-1">
                    {{ props.userData.created_at }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Updated at -->
            <VListItem v-if="props.userData.updated_at">
              <VListItemTitle>
                <h6 class="text-h6">
                  Aggiornato il:
                  <div class="d-inline-block text-body-1">
                    {{ props.userData.updated_at }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Team Leader -->
            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Team Leader:
                  <div class="d-inline-block text-body-1">
                    {{ props.userData.team_leader ? 'Sì' : 'No' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Agenzia di Fatturazione:
                  <div class="d-inline-block text-body-1">
                    {{ props.userData.agency ? props.userData.agency.name : 'N/A' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Enabled -->
            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Abilitato:
                  <div class="d-inline-block text-body-1">
                    {{ props.userData.enabled ? 'Sì' : 'No' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Structure -->
            <VListItem v-if="props.userData.structure">
              <VListItemTitle>
                <h6 class="text-h6">
                  Agenzia di Fatturazione:
                  <div class="d-inline-block text-body-1">
                      {{ props.userData.structure.name }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>
          </VList>
        </VCardText>


        <!-- 👉 Edit and Suspend button -->
        <VCardText class="d-flex justify-center gap-x-4">
          <VBtn
            v-if="$can('edit', 'users')"
            variant="elevated"
            @click="isUserInfoEditDialogVisible = true"
          >
            Modifica
          </VBtn>

          <VBtn
            v-if="$can('delete', 'users')"
            variant="tonal"
            color="error"
            disabled="disabled"
          >
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>
    <!-- !SECTION -->
  </VRow>

  <!-- 👉 Edit customer info dialog -->
  <UserInfoEditDialog
    v-if="$can('edit', 'users')"
    v-model:isDialogVisible="isUserInfoEditDialogVisible"
    :user-data="props.userData"
    @submit="updateUserInfo"
  />
</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 0.5rem;
}

.text-capitalize {
  text-transform: capitalize !important;
}
</style>
