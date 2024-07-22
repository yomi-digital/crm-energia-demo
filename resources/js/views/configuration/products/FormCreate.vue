<script setup>
import { ref } from 'vue';

const brand = ref('')
const name = ref('')
const notes = ref('')
const price = ref(0)
const discountPercentage = ref(0)
const feeType = ref('FISSO')
const getterFee = ref(0)
const agentFee = ref(0)
const structureFee = ref(0)
const salespersonFee = ref(0)
const structureTopFee = ref(0)
const managementFee = ref(0)
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
    fee_type: feeType.value,
    getter_fee: getterFee.value,
    agent_fee: agentFee.value,
    structure_fee: structureFee.value,
    salesperson_fee: salespersonFee.value,
    structure_top_fee: structureTopFee.value,
    management_fee: managementFee.value,
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

const feeTypes = [
  { title: 'Fisso', value: 'FISSO' },
  { title: 'Percentuale', value: 'PERCENTUALE' },
  { title: 'Mensilità', value: 'MESE' },
  { title: 'Consumo', value: 'CONSUMO' },
]
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
        md="12"
      >
        <h5 class="text-h5 mt-6">
          Compensi
        </h5>
      </VCol>

      <!-- 👉 Fee type -->
      <VCol
        cols="12"
        md="12"
      >
        <AppSelect
          v-model="feeType"
          label="Tipo di compenso"
          placeholder="Fisso"
          :rules="[requiredValidator]"
          :items="feeTypes"
        />
      </VCol>

      <!-- 👉 Management fee -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="managementFee"
          label="Gestione"
          placeholder="0"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Getter fee -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="getterFee"
          label="Procacciatore"
          placeholder="0"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Agent fee -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="agentFee"
          label="Agente"
          placeholder="0"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Structure fee -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="structureFee"
          label="Struttura"
          placeholder="0"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Structure top fee -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="structureTopFee"
          label="Struttura Top"
          placeholder="0"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Salesperson fee -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="salespersonFee"
          label="Venditore"
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
