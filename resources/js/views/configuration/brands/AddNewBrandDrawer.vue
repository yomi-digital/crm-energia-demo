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
  'brandData',
])

const isFormValid = ref(false)
const refForm = ref()
const name = ref('')
const enabled = ref(true)
const category = ref('')
const type = ref('')
const notes = ref('')

const isLoading = ref(false)
const isSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')


// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(async ({ valid }) => {
    if (valid) {
      isLoading.value = true
      try {
        await $api('/brands', {
          method: 'POST',
          body: {
            name: name.value,
            enabled: enabled.value === true ? 1 : 0,
            category: category.value,
            type: type.value,
            notes: notes.value,
          },
        })

        snackbarMessage.value = 'Brand aggiunto con successo'
        snackbarColor.value = 'success'
        isSnackbarVisible.value = true
        
        emit('brandData')
        
        setTimeout(() => {
          emit('update:isDrawerOpen', false)
          nextTick(() => {
            refForm.value?.reset()
            refForm.value?.resetValidation()
          })
        }, 500)

      } catch (error) {
        console.error(error)
        snackbarMessage.value = 'Errore durante l\'aggiunta del brand'
        snackbarColor.value = 'error'
        isSnackbarVisible.value = true
      } finally {
        isLoading.value = false
      }
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
      title="Aggiungi Brand"
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
                  v-model="name"
                  :rules="[requiredValidator]"
                  label="Nome"
                  placeholder="TIM"
                />
              </VCol>

              <!-- 👉 Abilitato -->
              <VCol cols="12">
                <VSwitch
                  v-model="enabled"
                  label="Abilitato"
                />
              </VCol>

              <!-- 👉 Tipo -->
              <VCol cols="12">
                <AppSelect
                  v-model="type"
                  label="Tipo"
                  :items="['Residenziale', 'Business']"
                />
              </VCol>

              <!-- 👉 Categoria -->
              <VCol cols="12">
                <AppSelect
                  v-model="category"
                  label="Tipologia"
                  :items="['Telefonia', 'Energia', 'Altro']"
                />
              </VCol>

              <!-- 👉 Note -->
              <VCol cols="12">
                <AppTextarea
                  v-model="notes"
                  label="Note"
                  placeholder="Note"
                />
              </VCol>

              <!-- 👉 Submit and Cancel -->
              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-3"
                  :loading="isLoading"
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

  <VSnackbar
    v-model="isSnackbarVisible"
    :color="snackbarColor"
    location="top end"
    variant="flat"
  >
    {{ snackbarMessage }}
  </VSnackbar>
</template>
