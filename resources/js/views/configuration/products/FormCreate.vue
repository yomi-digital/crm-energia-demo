<script setup>
import { ref } from 'vue';

const brand = ref('')
const name = ref('')
const notes = ref('')
const price = ref(0)
const discountPercentage = ref(0)
const enabled = ref(true)

const refForm = ref()
const isSaving = ref(false)

const emit = defineEmits([
  'productData',
])

const brands = ref([])

await $api('/brands?itemsPerPage=99999999').then(responseBrands => {
  for (const brand of responseBrands.brands) {
    brands.value.push({
      title: brand.name,
      value: brand.id,
    })
  }
})

const createProduct = async () => {
  let productData = {
    name: name.value,
    brand_id: brand.value,
    notes: notes.value,
    price: price.value,
    discount_percent: discountPercentage.value,
    enabled: enabled.value,
  }

  isSaving.value = true
  const response = await $api('/products', {
    method: 'POST',
    body: productData,
  })
  isSaving.value = false
  // Redirect to the product detail page
  if (response.id) {
    emit('productData', response)
    nextTick(() => {
      refForm.value?.reset()
      refForm.value?.resetValidation()
    })
  }
}
</script>

<template>
  <VForm ref="refForm" @submit.prevent="createProduct">
    <VRow>
      <!-- 👉 Brand -->
      <VCol
        cols="12"
        md="12"
      >
        <AppSelect
          v-model="brand"
          label="Brand"
          placeholder="Seleziona"
          :items="brands"
        />
      </VCol>

      <!-- 👉 Name -->
      <VCol
        cols="12"
        md="12"
      >
        <AppTextField
          v-model="name"
          label="Nome Prodotto"
          placeholder="ENEL ENERGIA"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Price -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="price"
          label="Prezzo"
          placeholder="0"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Notes -->
      <VCol
        cols="12"
        md="12"
      >
        <AppTextarea
          v-model="notes"
          label="Note"
          placeholder="Note"
        />
      </VCol>

      <!-- 👉 Discount -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="discountPercentage"
          label="% Sconto Appuntamento"
          placeholder="0"
          :rules="[requiredValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex gap-4"
      >
        <VBtn type="submit" :disabled="isSaving">
          Crea
        </VBtn>

        <VBtn
          type="reset"
          color="secondary"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
