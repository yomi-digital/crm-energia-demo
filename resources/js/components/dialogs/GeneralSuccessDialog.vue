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
  buttonText: {
    type: String,
    default: 'Ho capito',
  },
})

const emit = defineEmits(['update:modelValue', 'close'])

const closeDialog = () => {
  emit('close')
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
          {{ title }}
        </h6>
        <p class="text-body-2 mt-2">
          {{ message }}
        </p>
      </VCardText>

      <VCardText class="d-flex align-center justify-center">
        <VBtn
          color="success"
          @click="closeDialog"
        >
          {{ buttonText }}
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>
