<script setup>
const props = defineProps({
  customerData: {
    type: Object,
    required: true,
  },
  showActions: {
    type: Boolean,
    default: true,
  },
  showConfirmButton: {
    type: Boolean,
    default: true,
  },
  showEditButton: {
    type: Boolean,
    default: true,
  },
  compact: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits([
  'confirm',
  'edit',
])

const isAdmin = useCookie('userData').value.roles.some(role => role.name === 'gestione' || role.name === 'backoffice' || role.name === 'amministrazione')
const isAgent = useCookie('userData').value.roles.some(role => role.name === 'agente')

const handleConfirm = () => {
  emit('confirm', props.customerData)
}

const handleEdit = () => {
  emit('edit', props.customerData)
}
</script>

<template>
  <VCard>
    <VCardText>
      <!-- 👉 Title -->
      <h5 class="text-h5 mb-4">
        Dettagli cliente
      </h5>

      <!-- 👉 Warning Alert for unconfirmed customers -->
      <VAlert 
        v-if="!props.customerData.confirmed_at" 
        icon="tabler-alert-triangle" 
        color="warning" 
        class="mb-4"
      >
        Non confermato
      </VAlert>

      <!-- 👉 Customer Details -->
      <div class="d-flex flex-column gap-y-3">
        <!-- Numero Pratiche -->
        <div class="d-flex gap-x-3 align-center">
          <VAvatar
            variant="tonal"
            color="success"
          >
            <VIcon icon="tabler-shopping-cart" />
          </VAvatar>
          <h6 class="text-h6">
            {{ props.customerData.paperworks?.length || 0 }}
            <template v-if="(props.customerData.paperworks?.length || 0) > 1">Pratiche</template>
            <template v-else>Pratica</template>
          </h6>
        </div>

        <!-- Tipologia -->
        <div class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Tipologia:</span>
          <span>{{ props.customerData.category || 'Ditta Individuale' }}</span>
        </div>

        <!-- Nome -->
        <div v-if="props.customerData.name" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Nome:</span>
          <span>{{ props.customerData.name }}</span>
        </div>

        <!-- Cognome -->
        <div v-if="props.customerData.last_name" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Cognome:</span>
          <span>{{ props.customerData.last_name }}</span>
        </div>

        <!-- Ragione Sociale -->
        <div v-if="props.customerData.business_name" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Ragione Sociale:</span>
          <span>{{ props.customerData.business_name }}</span>
        </div>

        <!-- Codice Fiscale -->
        <div v-if="props.customerData.tax_id_code" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Codice Fiscale:</span>
          <span>{{ props.customerData.tax_id_code }}</span>
        </div>

        <!-- Partita IVA -->
        <div v-if="props.customerData.vat_number" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Partita IVA:</span>
          <span>{{ props.customerData.vat_number }}</span>
        </div>

        <!-- Email -->
        <div v-if="props.customerData.email" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Email:</span>
          <span>{{ props.customerData.email }}</span>
        </div>

        <!-- Telefono -->
        <div v-if="props.customerData.phone" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Telefono:</span>
          <span>{{ props.customerData.phone }}</span>
        </div>

        <!-- Cellulare -->
        <div v-if="props.customerData.mobile" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Cellulare:</span>
          <span>{{ props.customerData.mobile }}</span>
        </div>

        <!-- Privacy -->
        <div class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Privacy:</span>
          <VChip 
            :color="props.customerData.privacy ? 'success' : 'warning'"
            size="small"
            variant="flat"
          >
            {{ props.customerData.privacy ? 'Accettata' : 'Non accettata' }}
          </VChip>
        </div>

        <!-- Indirizzo -->
        <div v-if="props.customerData.address" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Indirizzo:</span>
          <span>{{ props.customerData.address }}</span>
        </div>

        <!-- Città -->
        <div v-if="props.customerData.city" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Città:</span>
          <span>{{ props.customerData.city }}</span>
        </div>

        <!-- Provincia -->
        <div v-if="props.customerData.province" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Provincia:</span>
          <span>{{ props.customerData.province }}</span>
        </div>

        <!-- Regione -->
        <div v-if="props.customerData.region" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Regione:</span>
          <span>{{ props.customerData.region }}</span>
        </div>

        <!-- CAP -->
        <div v-if="props.customerData.zip" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">CAP:</span>
          <span>{{ props.customerData.zip }}</span>
        </div>

        <!-- Codice Ateco -->
        <div v-if="props.customerData.ateco_code" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Codice Ateco:</span>
          <span>{{ props.customerData.ateco_code }}</span>
        </div>

        <!-- Aggiunto il -->
        <div v-if="props.customerData.added_at" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Aggiunto il:</span>
          <span>{{ props.customerData.added_at }}</span>
        </div>

        <!-- Aggiunto da -->
        <div v-if="props.customerData.added_by" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Aggiunto da:</span>
          <span>
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
          </span>
        </div>

        <!-- Confermato il -->
        <div v-if="props.customerData.confirmed_at" class="d-flex justify-space-between align-center">
          <span class="font-weight-medium">Confermato il:</span>
          <span>{{ props.customerData.confirmed_at }}</span>
        </div>

                    <!-- Confermato da -->
            <div v-if="props.customerData.confirmed_by" class="d-flex justify-space-between align-center">
              <span class="font-weight-medium">Confermato da:</span>
              <span>
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
              </span>
            </div>
          </div>
        </VCardText>

    <!-- 👉 Action Buttons -->
    <VCardText v-if="showActions" class="d-flex justify-center gap-x-4">
      <VBtn
        v-if="showConfirmButton && !props.customerData.confirmed_at && $can('edit', 'customers') && isAdmin"
        variant="elevated"
        color="success"
        @click="handleConfirm"
      >
        Conferma
      </VBtn>

      <VBtn
        v-if="showEditButton && $can('edit', 'customers') && (!isAgent || !props.customerData.confirmed_at)"
        variant="elevated"
        @click="handleEdit"
      >
        Modifica
      </VBtn>
    </VCardText>
  </VCard>
</template>

<style scoped>
/* Stili per il layout responsive */
@media (max-width: 768px) {
  .d-flex.justify-space-between {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }
}
</style> 
