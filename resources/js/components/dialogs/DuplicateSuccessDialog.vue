<script setup>
const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  duplicatedPaperworkId: {
    type: [String, Number],
    required: true,
  },
})

const emit = defineEmits([
  'close',
  'viewPaperwork',
  'update:isDialogVisible',
])

const onClose = () => {
  emit('close')
}

const onViewPaperwork = () => {
  emit('viewPaperwork')
}

const updateModelValue = (val) => {
  emit('update:isDialogVisible', val)
}
</script>

<template>
  <VDialog
    :model-value="props.isDialogVisible"
    @update:model-value="updateModelValue"
    max-width="500"
  >
    <VCard class="text-center px-10 py-6">
      <VCardText>
        <VIcon
          icon="tabler-check"
          color="success"
          size="60"
        />
        <h6 class="text-lg font-weight-medium mt-4">
          Pratica duplicata con successo!
        </h6>
        <p class="text-body-2 mt-2">
          Nuova pratica creata con ID: <strong>{{ duplicatedPaperworkId }}</strong>
        </p>
      </VCardText>

      <VCardText class="d-flex align-center justify-center gap-2">
        <VBtn
          color="success"
          @click="onClose"
        >
          Ok
        </VBtn>
        
        <VBtn
          color="primary"
          variant="tonal"
          @click="onViewPaperwork"
        >
          Visualizza Pratica
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template> 
