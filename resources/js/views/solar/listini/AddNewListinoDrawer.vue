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
  'listinoData',
])

const isFormValid = ref(false)
const refForm = ref()
const nome = ref('')
const descrizione = ref('')
const tipoCliente = ref('Business')
const tipoFase = ref('Monofase')

const tipoClienteItems = ['Business', 'Residenziale']
const tipoFaseItems = ['Monofase', 'Trifase']

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
    tipoCliente.value = 'Business'
    tipoFase.value = 'Monofase'
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      emit('listinoData', {
        nome: nome.value,
        descrizione: descrizione.value,
        tipo_cliente: tipoCliente.value,
        tipo_fase: tipoFase.value,
        is_active: true,
      })
      emit('update:isDrawerOpen', false)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
        tipoCliente.value = 'Business'
        tipoFase.value = 'Monofase'
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
      title="Aggiungi Listino"
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
              <!-- 👉 Nome -->
              <VCol cols="12">
                <AppTextField
                  v-model="nome"
                  :rules="[requiredValidator]"
                  label="Nome Listino"
                  placeholder="Es. Listino Base"
                />
              </VCol>

              <!-- 👉 Tipo Cliente -->
              <VCol cols="12">
                <AppSelect
                  v-model="tipoCliente"
                  :items="tipoClienteItems"
                  label="Tipo Cliente"
                  placeholder="Seleziona Tipo Cliente"
                />
              </VCol>

              <!-- 👉 Tipo Fase -->
              <VCol cols="12">
                <AppSelect
                  v-model="tipoFase"
                  :items="tipoFaseItems"
                  label="Tipo Fase"
                  placeholder="Seleziona Tipo Fase"
                />
              </VCol>

              <!-- 👉 Descrizione -->
              <VCol cols="12">
                <AppTextarea
                  v-model="descrizione"
                  label="Descrizione"
                  placeholder="Descrizione del listino"
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
                  type="button"
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
