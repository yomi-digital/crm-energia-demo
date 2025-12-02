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
  'categoriaData',
])

const isFormValid = ref(false)
const refForm = ref()
const nomeCategoria = ref('')
const descrizione = ref('')

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
        nome_categoria: nomeCategoria.value,
      }
      
      if (descrizione.value) {
        body.descrizione = descrizione.value
      }
      
      emit('categoriaData', body)
      emit('update:isDrawerOpen', false)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
        nomeCategoria.value = ''
        descrizione.value = ''
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
      title="Aggiungi Categoria"
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
              <!-- 👉 Nome Categoria -->
              <VCol cols="12">
                <AppTextField
                  v-model="nomeCategoria"
                  :rules="[requiredValidator]"
                  label="Nome Categoria"
                  placeholder="Fotovoltaico"
                />
              </VCol>

              <!-- 👉 Descrizione -->
              <VCol cols="12">
                <AppTextarea
                  v-model="descrizione"
                  label="Descrizione"
                  placeholder="Descrizione opzionale"
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

