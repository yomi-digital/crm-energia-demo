<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  agency: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'agencyData',
])

const isFormValid = ref(false)
const refForm = ref()
const name = ref('')
const email = ref('')
const vatNumber = ref('')
const uniqueCode = ref('')
const address = ref('')

name.value = props.agency.name
email.value = props.agency.email
vatNumber.value = props.agency.vat_number
uniqueCode.value = props.agency.unique_code
address.value = props.agency.address

watch(() => props.isDrawerOpen, (val) => {
  if (val) {
    name.value = props.agency.name
    email.value = props.agency.email
    vatNumber.value = props.agency.vat_number
    uniqueCode.value = props.agency.unique_code
    address.value = props.agency.address
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
      emit('agencyData', {
        id: props.agency.id,
        name: name.value,
        email: email.value,
        vat_number: vatNumber.value,
        unique_code: uniqueCode.value,
        address: address.value,
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
      title="Modifica Agenzia"
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
                  label="Ragione Sociale"
                  placeholder="Agenzia s.r.l."
                />
              </VCol>

              <!-- 👉 Email -->
              <VCol cols="12">
                <AppTextField
                  v-model="email"
                  :rules="[requiredValidator, emailValidator]"
                  label="Email"
                  placeholder="agenzia@mail.com"
                />
              </VCol>

              <!-- 👉 Partita IVA -->
              <VCol cols="12">
                <AppTextField
                  v-model="vatNumber"
                  :rules="[requiredValidator]"
                  label="Partita IVA"
                  placeholder="12345678901"
                />
              </VCol>

              <!-- 👉 Codice Unico -->
              <VCol cols="12">
                <AppTextField
                  v-model="uniqueCode"
                  :rules="[requiredValidator]"
                  label="Codice Unico"
                  placeholder="123456"
                />
              </VCol>

              <!-- 👉 Indirizzo -->
              <VCol cols="12">
                <AppTextField
                  v-model="address"
                  :rules="[requiredValidator]"
                  label="Indirizzo"
                  placeholder="Via Roma, 123, Milano (MI) 02345"
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
