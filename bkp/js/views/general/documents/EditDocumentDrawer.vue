<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  document: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'documentData',
])

const isFormValid = ref(false)
const refForm = ref()
const name = ref()
const category = ref('')
const brand = ref('')
const dropZoneRef = ref()
const brands = ref([])
const fileData = ref([])

await $api('/brands?itemsPerPage=999999').then(response => {
  for (let i = 0; i < response.brands.length; i++) {
    brands.value.push({
      title: response.brands[i].name,
      value: response.brands[i].id,
    })
  }
})

name.value = props.document.name
category.value = props.document.category
brand.value = props.document.brand_id

watch(() => props.isDrawerOpen, (val) => {
  if (val) {
    name.value = props.document.name
    category.value = props.document.category
    brand.value = props.document.brand_id
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
      emit('documentData', {
        id: props.document.id,
        name: name.value,
        category: category.value,
        brand_id: brand.value,
      })
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
      title="Modifica Documento"
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
                  placeholder="Documento Wind"
                />
              </VCol>

              <!-- 👉 Category -->
              <VCol cols="12">
                <AppTextField
                  v-model="category"
                  :rules="[requiredValidator]"
                  label="Categoria"
                  placeholder="Info"
                />
              </VCol>

              <!-- 👉 Brand -->
              <VCol cols="12">
                <AppSelect
                  v-model="brand"
                  label="Brand"
                  placeholder="Seleziona un Brand"
                  :rules="[requiredValidator]"
                  :items="brands"
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
