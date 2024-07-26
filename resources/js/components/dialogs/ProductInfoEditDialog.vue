<script setup>
const props = defineProps({
  productData: {
    type: Object,
    required: false,
    default: () => ({
      id: 0,
      name: '',
      brand_id: 0,
      price: 0,
      discount_percent: 0,
      enabled: 0,
    }),
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

const productData = ref(structuredClone(toRaw(props.productData)))

watch(props, () => {
  productData.value = structuredClone(toRaw(props.productData))
})

const onFormSubmit = () => {
  emit('update:isDialogVisible', false)
  emit('submit', productData.value)
}

const onFormReset = () => {
  productData.value = structuredClone(toRaw(props.productData))
  emit('update:isDialogVisible', false)
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}

const brands = ref([])
const fetchBrands = async () => {
  brands.value = []
  const response = await $api('/brands?itemsPerPage=99999999&select=1')
  for (let i = 0; i < response.brands.length; i++) {
    brands.value.push({
      title: response.brands[i].name,
      value: response.brands[i].id,
    })
  }
}
await fetchBrands()
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 900"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />

    <VCard class="pa-sm-10 pa-2">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-2">
          Modifica Informazioni Prodotto
        </h4>
        <!-- <p class="text-body-1 text-center mb-6">
          Updating user details will receive a privacy audit.
        </p> -->

        <!-- 👉 Form -->
        <VForm
          class="mt-6"
          @submit.prevent="onFormSubmit"
        >
          <VRow>
            <VCol
              cols="12"
              md="12"
            >
              <AppSelect
                v-model="productData.brand_id"
                label="Brand"
                placeholder="Seleziona un brand"
                :rules="[requiredValidator]"
                :items="brands"
              />
            </VCol>

            <!-- 👉 Name -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="productData.name"
                :rules="[requiredValidator]"
                label="Nome Prodotto"
                placeholder="ENEL ENERGIA"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="productData.price"
                :rules="[requiredValidator]"
                label="Prezzo"
                placeholder="0"
              />
            </VCol>

            <!-- 👉 Notes -->
            <VCol
              cols="12"
              md="12"
            >
              <AppTextarea
                v-model="productData.notes"
                label="Note"
                placeholder="Note"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="productData.discount_percent"
                :rules="[requiredValidator]"
                label="% Sconto Appuntamento"
                placeholder="0"
              />
            </VCol>

            <!-- 👉 Enabled -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="productData.enabled"
                label="Abilitato"
                placeholder="Seleziona"
                :rules="[requiredValidator]"
                :items="[{ title: 'SI', value: 1 }, { title: 'NO', value: 0 }]"
              />
            </VCol>

            <!-- 👉 Submit and Cancel -->
            <VCol
              cols="12"
              class="d-flex flex-wrap justify-center gap-4"
            >
              <VBtn type="submit">
                Salva
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
