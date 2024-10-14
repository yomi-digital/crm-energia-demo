<script setup>
const props = defineProps({
  paperworkId: {
    required: true,
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

const ticketData = ref({
  paperwork_id: props.paperworkId,
  title: '',
  description: '',
})


const onFormSubmit = async () => {
  await $api('/tickets', {
      method: 'POST',
      body: ticketData.value,
  }).then(response => {
    emit('update:isDialogVisible', false)
    emit('submit', response)
  })
}

const onFormReset = () => {
  emit('update:isDialogVisible', false)
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}

</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 600"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />

    <VCard class="pa-sm-10 pa-2">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-2">
          Nuovo Ticket
        </h4>

        <!-- 👉 Form -->
        <VForm
          class="mt-6"
          @submit.prevent="onFormSubmit"
        >
          <VRow>
            <!-- 👉 Title -->
            <VCol
              cols="12"
              md="12"
            >
              <AppTextField
                v-model="ticketData.title"
                :rules="[requiredValidator]"
                label="Oggetto"
                placeholder=""
              />
            </VCol>

            <!-- 👉 Description -->
            <VCol
              cols="12"
              md="12"
            >
              <AppTextarea
                v-model="ticketData.description"
                :rules="[requiredValidator]"
                label="Descrizione"
                placeholder=""
              />
            </VCol>

            <!-- 👉 Submit and Cancel -->
            <VCol
              cols="12"
              class="d-flex flex-wrap justify-center gap-4"
            >
              <VBtn type="submit">
                Crea
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
