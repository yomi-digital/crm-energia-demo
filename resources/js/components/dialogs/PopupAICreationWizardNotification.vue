<script setup>
import { VBtn, VCard, VCardText, VDialog } from 'vuetify/components'

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits(['update:modelValue'])

const closeDialog = () => {
  emit('update:modelValue', false)
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    @update:model-value="(value) => emit('update:modelValue', value)"
    max-width="500"
    persistent
  >
    <VCard class="text-center px-10 py-6">
      <VCardText>
        <VIcon
          icon="tabler-check"
          color="success"
          size="60"
        />
        <h6 class="text-lg font-weight-medium mt-4">
          Pratica AI inviata con successo
        </h6>
        <p class="text-body-2 mt-2">
          Hai appena inviato una pratica AI. Quando essa verrà confermata come pratica standard potrai visualizzarla nella sezione delle pratiche.
        </p>
      </VCardText>

      <VCardText class="d-flex align-center justify-center">
        <VBtn
          color="success"
          @click="closeDialog"
        >
          Ho capito
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template> 
