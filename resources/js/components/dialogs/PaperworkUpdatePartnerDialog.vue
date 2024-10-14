<script setup>

const props = defineProps({
  paperworkData: {
    type: Object,
    required: true,
    // default: () => ({
    //   id: 0,
    //   name: '',
    //   last_name: '',
    //   business_name: '',
    //   tax_code_id: '',
    //   vat_number: '',
    //   email: '',
    //   phone: '',
    //   mobile: '',
    //   ateco_code: '',
    //   pec: '',
    //   unique_code: '',
    //   category: '',
    //   address: '',
    //   region: '',
    //   province: '',
    //   city: '',
    //   zip: '',
    // }),
  },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'submit',
  'update:isDialogVisible',
])

const onFormSubmit = async () => {
  await $api(`/paperworks/${ props.paperworkId }/update-partner`, {
    method: 'PUT',
    body: {
      order_status: orderStatus,
    },
  })
  emit('update:isDialogVisible', false)
  emit('submit', null)
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}

const orderStatus = ref('')

const onFormReset = () => {
  orderCode.value = ''
}
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 500"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />

    <VCard class="pa-sm-10 pa-2">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-2">
          Conferma Inserimento Pratica
        </h4>

        <!-- 👉 Form -->
        <VForm
          class="mt-6"
          @submit.prevent="onFormSubmit"
        >
          <VRow>
            <VCol
              cols="12"
              sm="12"
            >
              <AppTextField
                v-model="orderCode"
                label="ID Pratica"
                placeholder="000111222"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- 👉 Submit and Cancel -->
            <VCol
              cols="12"
              class="d-flex flex-wrap justify-center gap-4"
            >
              <VBtn type="submit">
                Conferma
              </VBtn>

              <VBtn
                color="secondary"
                variant="tonal"
                @click="onFormReset"
              >
                Annulla
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>
