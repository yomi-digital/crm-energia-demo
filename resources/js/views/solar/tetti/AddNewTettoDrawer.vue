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
  'tettoData',
])

const isFormValid = ref(false)
const refForm = ref()
const nomeTipologia = ref('')
const note = ref('')
const costoExtraKwp = ref('')

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
        nome_tipologia: nomeTipologia.value,
      }
      
      if (note.value) {
        body.note = note.value
      }
      
      if (costoExtraKwp.value) {
        body.costo_extra_kwp = costoExtraKwp.value
      }
      
      emit('tettoData', body)
      emit('update:isDrawerOpen', false)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
        nomeTipologia.value = ''
        note.value = ''
        costoExtraKwp.value = ''
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
      title="Aggiungi Tetto"
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
              <!-- 👉 Nome Tipologia -->
              <VCol cols="12">
                <AppTextField
                  v-model="nomeTipologia"
                  :rules="[requiredValidator]"
                  label="Nome Tipologia"
                  placeholder="Tetto nuovo"
                />
              </VCol>

              <!-- 👉 Costo Extra kWp -->
              <VCol cols="12">
                <AppTextField
                  v-model="costoExtraKwp"
                  label="Costo Extra kWp"
                  placeholder="200"
                  type="number"
                />
              </VCol>

              <!-- 👉 Note -->
              <VCol cols="12">
                <AppTextarea
                  v-model="note"
                  label="Note"
                  placeholder="Note opzionali"
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

