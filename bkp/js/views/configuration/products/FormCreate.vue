<script setup>
import { ref } from 'vue';

const brand = ref('')
const name = ref('')
const notes = ref('')
const price = ref(0)
const discountPercentage = ref(0)
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

// Fake data
// name.value = 'Mario'
// lastName.value = 'Rossi'
// businessName.value = 'Società SRL'
// taxIdCode.value = 'ABCDEF12G34H567I'
// vatNumber.value = '12345678901'
// email.value = 'mail@mail.com'
// phone.value = '1234567890'
// mobile.value = '1234567890'
// atecoCode.value = '123456'
// pec.value = 'pec@mail.com'
// uniqueCode.value = '123456'
// address.value = 'Via Roma 123'
// region.value = 'Lazio'
// province.value = 'RM'
// city.value = 'Roma'
// zip.value = '00100'

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
  let customerData = {
    category: category.value === 'all' ? null : category.value,
    email: email.value,
    phone: phone.value,
    mobile: mobile.value,
    address: address.value,
    region: region.value,
    province: province.value,
    city: city.value,
    zip: zip.value,
  }
  if (category.value === 'Residenziale' || category.value === 'all') {
    customerData.name = name.value
    customerData.last_name = lastName.value
    customerData.tax_id_code = taxIdCode.value
  }
  if (category.value === 'Business' || category.value === 'all') {
    customerData.business_name = businessName.value
    customerData.vat_number = vatNumber.value
    customerData.pec = pec.value
    customerData.ateco_code = atecoCode.value
    customerData.unique_code = uniqueCode.value
  }

  isSaving.value = true
  const response = await $api('/customers', {
    method: 'POST',
    body: customerData,
  })
  isSaving.value = false
  // Redirect to the customer detail page
  if (response.id) {
    emit('customerData', response)
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
        md="12"
      >
        <h5 class="text-h5 mt-6">
          Compensi
        </h5>
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
          label="Partner"
          placeholder="0"
          :rules="[requiredValidator]"
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
