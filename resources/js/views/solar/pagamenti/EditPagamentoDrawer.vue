<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  pagamento: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'pagamentoData',
])

const isFormValid = ref(false)
const refForm = ref()
const nomeModalita = ref()
const descrizione = ref()
const tipoCliente = ref([])

const tipoClienteOptions = [
  { title: 'Residenziale', value: 'RESIDENZIALE' },
  { title: 'Business', value: 'BUSINESS' },
]

nomeModalita.value = props.pagamento.nome_modalita
descrizione.value = props.pagamento.descrizione || ''
tipoCliente.value = (props.pagamento.applicabilita || []).map(app => app.tipo_cliente)

watch(() => props.isDrawerOpen, (val) => {
  if (val) {
    nomeModalita.value = props.pagamento.nome_modalita
    descrizione.value = props.pagamento.descrizione || ''
    tipoCliente.value = (props.pagamento.applicabilita || []).map(app => app.tipo_cliente)
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
        id: props.pagamento.id_modalita || props.pagamento.id,
        nome_modalita: nomeModalita.value,
        descrizione: descrizione.value || '-',
        tipo_cliente: tipoCliente.value,
      }
      
      emit('pagamentoData', body)
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
      title="Modifica Modalità Pagamento"
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
              <!-- 👉 Nome Modalità -->
              <VCol cols="12">
                <AppTextField
                  v-model="nomeModalita"
                  :rules="[requiredValidator]"
                  label="Nome Modalità"
                  placeholder="Bonifico"
                />
              </VCol>

              <!-- 👉 Descrizione -->
              <VCol cols="12">
                <AppTextarea
                  v-model="descrizione"
                  label="Descrizione"
                  placeholder="-"
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

