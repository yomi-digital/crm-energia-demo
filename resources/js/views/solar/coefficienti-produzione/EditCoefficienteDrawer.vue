<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  coefficiente: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'coefficiente-data',
])

const isFormValid = ref(false)
const refForm = ref()
const coefficienteKwhKwp = ref()

coefficienteKwhKwp.value = props.coefficiente.coefficiente_kwh_kwp

watch(() => props.isDrawerOpen, (val) => {
  if (val) {
    coefficienteKwhKwp.value = props.coefficiente.coefficiente_kwh_kwp
  }
})

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      const body = {
        id: props.coefficiente.id_coeff,
        coefficiente_kwh_kwp: coefficienteKwhKwp.value,
      }
      
      emit('coefficiente-data', body)
      emit('update:isDrawerOpen', false)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
      })
    }
  })
}

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}
</script>

<template>
  <VNavigationDrawer
    temporary
    :width="400"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <!-- 👉 Title -->
    <AppDrawerHeaderSection
      title="Modifica Coefficiente Produzione"
      @cancel="closeNavigationDrawer"
    />

    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <!-- 👉 Form -->
          <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <!-- 👉 Area Geografica (readonly) -->
              <VCol cols="12">
                <AppTextField
                  :model-value="coefficiente.area_geografica"
                  label="Area Geografica"
                  readonly
                />
              </VCol>

              <!-- 👉 Esposizione (readonly) -->
              <VCol cols="12">
                <AppTextField
                  :model-value="coefficiente.esposizione"
                  label="Esposizione"
                  readonly
                />
              </VCol>

              <!-- 👉 Coefficiente kWh/kWp -->
              <VCol cols="12">
                <AppTextField
                  v-model="coefficienteKwhKwp"
                  :rules="[requiredValidator]"
                  label="Coefficiente kWh/kWp"
                  placeholder="1.2"
                  type="number"
                  step="0.01"
                />
              </VCol>

              <!-- 👉 Submit and Cancel -->
              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-3"
                >
                  Salva
                </VBtn>
                <VBtn
                  type="reset"
                  variant="tonal"
                  color="error"
                  @click="closeNavigationDrawer"
                >
                  Annulla
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>

