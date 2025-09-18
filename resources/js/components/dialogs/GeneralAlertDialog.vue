<script setup>
const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  message: {
    type: String,
    required: true,
  },
  confirmText: {
    type: String,
    default: 'Conferma',
  },
  cancelText: {
    type: String,
    default: 'Annulla',
  },
})

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

const closeDialog = () => {
  emit('update:modelValue', false)
}

const onConfirm = () => {
  emit('confirm')
  closeDialog()
}

const onCancel = () => {
  emit('cancel')
  closeDialog()
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    @update:model-value="(value) => emit('update:modelValue', value)"
    max-width="500"
  >
    <VCard>
      <VCardTitle class="d-flex align-center">
        <VIcon
          icon="tabler-alert-triangle"
          color="warning"
          class="me-2"
        />
        {{ title }}
      </VCardTitle>
      
      <VCardText>
        <p class="mb-4">
          {{ message }}
        </p>
        <p class="text-sm text-medium-emphasis mb-0">
          Questa azione non può essere annullata.
        </p>
      </VCardText>
      
      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="outlined"
          @click="onCancel"
        >
          {{ cancelText }}
        </VBtn>
        <VBtn
          color="error"
          @click="onConfirm"
        >
          {{ confirmText }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
