<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'voce-economica-data',
])

const isFormValid = ref(false)
const refForm = ref()
const nomeVoce = ref('')
const tipoVoce = ref('')
const tipoValore = ref('')
const valoreDefault = ref('')
const tipoCliente = ref(['RESIDENZIALE', 'BUSINESS'])

const tipoVoceOptions = [
  { title: 'Incentivo', value: 'incentivo' },
  { title: 'Sconto', value: 'sconto' },
  { title: 'Costo', value: 'costo' },
]

const tipoValoreOptions = [
  { title: 'Percentuale (%)', value: '%' },
  { title: 'Fisso (€)', value: '€' },
]

const tipoClienteOptions = [
  { title: 'Residenziale', value: 'RESIDENZIALE' },
  { title: 'Business', value: 'BUSINESS' },
]

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
        nome_voce: nomeVoce.value,
        tipo_voce: tipoVoce.value,
        tipo_valore: tipoValore.value,
        valore_default: valoreDefault.value,
        tipo_cliente: tipoCliente.value,
      }
      
      emit('voce-economica-data', body)
      emit('update:isDrawerOpen', false)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
        nomeVoce.value = ''
        tipoVoce.value = ''
        tipoValore.value = ''
        valoreDefault.value = ''
        tipoCliente.value = ['RESIDENZIALE', 'BUSINESS']
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
      title="Aggiungi Voce Economica"
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
              <!-- 👉 Nome Voce -->
              <VCol cols="12">
                <AppTextField
                  v-model="nomeVoce"
                  :rules="[requiredValidator]"
                  label="Nome Voce"
                  placeholder="Incentivo PNNR"
                />
              </VCol>

              <!-- 👉 Tipo Voce -->
              <VCol cols="12">
                <AppSelect
                  v-model="tipoVoce"
                  :rules="[requiredValidator]"
                  :items="tipoVoceOptions"
                  label="Tipo Voce"
                />
              </VCol>

              <!-- 👉 Tipo Valore -->
              <VCol cols="12">
                <AppSelect
                  v-model="tipoValore"
                  :rules="[requiredValidator]"
                  :items="tipoValoreOptions"
                  label="Tipo Valore"
                />
              </VCol>

              <!-- 👉 Valore Default -->
              <VCol cols="12">
                <AppTextField
                  v-model="valoreDefault"
                  :rules="[requiredValidator]"
                  label="Valore Default"
                  placeholder="20"
                  type="number"
                />
              </VCol>

              <!-- 👉 Tipo Cliente -->
              <VCol cols="12">
                <AppSelect
                  v-model="tipoCliente"
                  :items="tipoClienteOptions"
                  label="Tipo Cliente"
                  multiple
                  chips
                />
              </VCol>

              <!-- 👉 Submit and Cancel -->
              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-3"
                >
                  Aggiungi
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

