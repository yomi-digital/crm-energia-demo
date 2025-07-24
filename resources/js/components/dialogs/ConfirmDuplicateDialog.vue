<script setup>
const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  paperworkData: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits([
  'confirm',
  'update:isDialogVisible',
])

const onConfirm = () => {
  emit('confirm')
}

const onCancel = () => {
  emit('update:isDialogVisible', false)
}

const updateModelValue = (val) => {
  emit('update:isDialogVisible', val)
}

const getCustomerName = (customer) => {
  if (!customer) return 'N/A'
  if (customer.name) {
    return [customer.name, customer.last_name].join(' ')
  } else if (customer.business_name) {
    return customer.business_name
  } else {
    return '#' + customer.id
  }
}
</script>

<template>
  <VDialog
    :model-value="props.isDialogVisible"
    @update:model-value="updateModelValue"
    max-width="600"
  >
    <VCard class="text-center px-10 py-6">
      <VCardText>
        <VBtn
          icon
          variant="outlined"
          color="warning"
          class="my-4"
          style="block-size: 88px; inline-size: 88px; pointer-events: none;"
        >
          <span class="text-5xl">!</span>
        </VBtn>

        <h6 class="text-lg font-weight-medium">
          Duplicare la pratica #{{ paperworkData.id }}?
        </h6>
        <p class="text-body-2 mt-2 mb-4">
          Verrà creata una nuova pratica con gli stessi dati.
        </p>

        <!-- Dettagli pratica -->
        <VCard variant="tonal" class="pa-4 mb-4">
          <VCardText class="pa-0">
            <div class="text-start">
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-medium-emphasis">Cliente:</span>
                <span class="font-weight-medium">{{ getCustomerName(paperworkData.customer) }}</span>
              </div>
              
              <div class="d-flex justify-space-between align-center mb-2" v-if="paperworkData.product?.name">
                <span class="text-body-2 text-medium-emphasis">Prodotto:</span>
                <span class="font-weight-medium">{{ paperworkData.product.name }}</span>
              </div>
              
              <div class="d-flex justify-space-between align-center mb-2" v-if="paperworkData.category">
                <span class="text-body-2 text-medium-emphasis">Categoria:</span>
                <span class="font-weight-medium">{{ paperworkData.category }}</span>
              </div>
              
              <div class="d-flex justify-space-between align-center mb-2" v-if="paperworkData.user">
                <span class="text-body-2 text-medium-emphasis">Agente:</span>
                <span class="font-weight-medium">{{ [paperworkData.user.name, paperworkData.user.last_name].join(' ') }}</span>
              </div>
              
              <div class="d-flex justify-space-between align-center" v-if="paperworkData.account_pod_pdr">
                <span class="text-body-2 text-medium-emphasis">POD/PDR:</span>
                <span class="font-weight-medium">{{ paperworkData.account_pod_pdr }}</span>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCardText>

      <VCardText class="d-flex align-center justify-center gap-2">
        <VBtn
          color="warning"
          variant="elevated"
          @click="onConfirm"
        >
          Duplica
        </VBtn>

        <VBtn
          color="secondary"
          variant="tonal"
          @click="onCancel"
        >
          Annulla
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template> 
