<script setup>
defineOptions({
  name: 'AppTextField',
  inheritAttrs: false,
})

const elementId = computed(() => {
  const attrs = useAttrs()
  const _elementIdToken = attrs.id || attrs.label
  
  return _elementIdToken ? `app-text-field-${ _elementIdToken }-${ Math.random().toString(36).slice(2, 7) }` : undefined
})

const label = computed(() => useAttrs().label)

const props = defineProps({
  customError: {
    type: String,
    default: ''
  },
  showErrorDetails: {
    type: Boolean,
    default: false
  }
})
</script>

<template>
  <div
    class="app-text-field flex-grow-1"
    :class="$attrs.class"
  >
    <VLabel
      v-if="label"
      :for="elementId"
      class="mb-1 text-body-2 text-wrap"
      style="line-height: 15px;"
      :text="label"
    />
    <VTextField
      v-bind="{
        ...$attrs,
        class: null,
        label: undefined,
        variant: 'outlined',
        id: elementId,
      }"
    >
      <template
        v-for="(_, name) in $slots"
        #[name]="slotProps"
      >
        <slot
          :name="name"
          v-bind="slotProps || {}"
        />
      </template>
    </VTextField>
    
    <div v-if="props.customError" class="custom-error-message d-flex align-center justify-space-between mt-1">
      <div class="d-flex align-center">
        <VIcon icon="tabler-alert-circle" size="18" class="me-2" />
        <span>{{ props.customError }}</span>
      </div>
      <VBtn 
        v-if="props.showErrorDetails" 
        size="x-small" 
        variant="text" 
        color="error" 
        @click="$emit('click:errorDetails')"
        style="height: 24px; padding: 0 8px;"
      >
        Dettagli
      </VBtn>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.custom-error-message {
  background-color: #FFEBEE;
  color: #D32F2F;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 0.8125rem;
  border: 1px solid #FFCDD2;
  line-height: 1.2;
}
</style>
