<script setup>
const props = defineProps({
  customerData: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits([
  'updateCustomerData',
])


const isCustomerInfoEditDialogVisible = ref(false)

const updateCustomerInfo = async (data) => {
  const response = await $api(`/customers/${ props.customerData.id }`, {
    method: 'PUT',
    body: data,
  })
  emit('updateCustomerData', response)
}

const confirmCustomer = async () => {
  const response = await $api(`/customers/${ props.customerData.id }/confirm`, {
    method: 'PUT',
  })
  emit('updateCustomerData', response)
}

</script>

<template>
  <VRow>
    <!-- SECTION User Details -->
    <VCol cols="12">
      <VCard v-if="props.customerData">
        <VCardText>
          <!-- 👉 Details -->
          <h5 class="text-h5">
            Informazioni Cliente
          </h5>

          <VDivider class="my-4" />

          <!-- 👉 Customer Details list -->
          <VList class="card-list mt-2">
            <VAlert class="mb-4" icon="tabler-alert-triangle" color="warning" v-if="! props.customerData.confirmed_at">
              Non confermato
            </VAlert>
            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Tipologia:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.category || 'N/A' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem v-if="props.customerData.name">
              <VListItemTitle>
                <h6 class="text-h6">
                  Nome:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.name }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem v-if="props.customerData.last_name">
              <VListItemTitle>
                <h6 class="text-h6">
                  Cognome:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.last_name }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem v-if="props.customerData.business_name">
              <VListItemTitle>
                <h6 class="text-h6">
                  Ragione Sociale:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.business_name }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem v-if="props.customerData.tax_id_code">
              <VListItemTitle>
                <h6 class="text-h6">
                  Codice Fiscale:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.tax_id_code }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem v-if="props.customerData.vat_number">
              <VListItemTitle>
                <h6 class="text-h6">
                  Partita IVA:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.vat_number }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem v-if="props.customerData.email">
              <VListItemTitle>
                <h6 class="text-h6">
                  Email:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.email }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem v-if="props.customerData.phone">
              <VListItemTitle>
                <h6 class="text-h6">
                  Telefono:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.phone }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem v-if="props.customerData.mobile">
              <VListItemTitle>
                <h6 class="text-h6">
                  Cellulare:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.mobile }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem v-if="props.customerData.address">
              <VListItemTitle>
                <h6 class="text-h6">
                  Indirizzo:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.address }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- City -->
            <VListItem v-if="props.customerData.city">
              <VListItemTitle>
                <h6 class="text-h6">
                  Città:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.city }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Province -->
            <VListItem v-if="props.customerData.province">
              <VListItemTitle>
                <h6 class="text-h6">
                  Provincia:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.province }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Region -->
            <VListItem v-if="props.customerData.region">
              <VListItemTitle>
                <h6 class="text-h6">
                  Regione:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.region }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Postal Code -->
            <VListItem v-if="props.customerData.zip">
              <VListItemTitle>
                <h6 class="text-h6">
                  CAP:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.zip }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Pec -->
            <VListItem v-if="props.customerData.pec">
              <VListItemTitle>
                <h6 class="text-h6">
                  PEC:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.pec }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Ateco code -->
            <VListItem v-if="props.customerData.ateco_code">
              <VListItemTitle>
                <h6 class="text-h6">
                  Codice Ateco:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.ateco_code }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Unique code -->
            <VListItem v-if="props.customerData.unique_code">
              <VListItemTitle>
                <h6 class="text-h6">
                  Codice Unico:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.unique_code }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Added at -->
            <VListItem v-if="props.customerData.added_at">
              <VListItemTitle>
                <h6 class="text-h6">
                  Aggiunto il:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.added_at }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Added by -->
            <VListItem v-if="props.customerData.added_by">
              <VListItemTitle>
                <h6 class="text-h6">
                  Aggiunto da:
                  <div class="d-inline-block text-body-1">
                    <RouterLink
                      v-if="$can('view', 'users')"
                      :to="{ name: 'admin-users-id', params: { id: props.customerData.added_by } }"
                      class="font-weight-medium text-link"
                    >
                      {{ [props.customerData.added_by_user?.name, props.customerData.added_by_user?.last_name].join(' ').trim() }}
                    </RouterLink>
                    <template v-else>
                      {{ [props.customerData.added_by_user?.name, props.customerData.added_by_user?.last_name].join(' ').trim() }}
                    </template>
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Confirmed -->
            <VListItem v-if="props.customerData.confirmed_at">
              <VListItemTitle>
                <h6 class="text-h6">
                  Confermato il:
                  <div class="d-inline-block text-body-1">
                    {{ props.customerData.confirmed_at }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Confirmed by -->
            <VListItem v-if="props.customerData.confirmed_by">
              <VListItemTitle>
                <h6 class="text-h6">
                  Confermato da:
                  <div class="d-inline-block text-body-1">
                    <RouterLink
                      v-if="$can('view', 'users')"
                      :to="{ name: 'admin-users-id', params: { id: props.customerData.confirmed_by } }"
                      class="font-weight-medium text-link"
                    >
                      {{ [props.customerData.confirmed_by_user?.name, props.customerData.confirmed_by_user?.last_name].join(' ').trim() }}
                    </RouterLink>
                    <template v-else>
                      {{ [props.customerData.confirmed_by_user?.name, props.customerData.confirmed_by_user?.last_name].join(' ').trim() }}
                    </template>
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>
          </VList>
        </VCardText>

        <!-- 👉 Edit and Suspend button -->
        <VCardText class="d-flex justify-center gap-x-4">
          <VBtn
            v-if="! props.customerData.confirmed_at && $can('edit', 'customers')"
            variant="elevated"
            color="success"
            @click="confirmCustomer"
          >
            Conferma
          </VBtn>

          <VBtn
            v-if="$can('edit', 'customers')"
            variant="elevated"
            @click="isCustomerInfoEditDialogVisible = true"
          >
            Modifica
          </VBtn>

          <VBtn
            v-if="$can('delete', 'customers')"
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
  <CustomerInfoEditDialog
    v-if="$can('edit', 'customers')"
    v-model:isDialogVisible="isCustomerInfoEditDialogVisible"
    :customer-data="props.customerData"
    @submit="updateCustomerInfo"
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
