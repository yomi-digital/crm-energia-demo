<script setup>
import { watch } from 'vue';

const props = defineProps({
  productData: {
    type: Object,
    required: true,
  },
})

watch(() => props.productData, () => {
  console.log('props.productData', props.productData)
})

const emit = defineEmits([
  'updateProductData',
])


const isProductInfoEditDialogVisible = ref(false)

const updateProductInfo = async (data) => {
  const response = await $api(`/products/${ props.productData.id }`, {
    method: 'PUT',
    body: data,
  })
  emit('updateProductData', response)
}

</script>

<template>
  <VRow>
    <!-- SECTION Product Details -->
    <VCol cols="12">
      <VCard v-if="props.productData">
        <VCardText>
          <!-- 👉 Details -->
          <h5 class="text-h5">
            Informazioni Prodotto
          </h5>

          <VDivider class="my-4" />

          <!-- 👉 Customer Details list -->
          <VList class="card-list mt-2">
            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Nome:
                  <div class="d-inline-block text-body-1">
                    {{ props.productData.name }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Brand -->
            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Brand:
                  <div class="d-inline-block text-body-1">
                    {{ props.productData.brand.name || 'N/A'}}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Price -->
            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Prezzo:
                  <div class="d-inline-block text-body-1">
                    {{ props.productData.price || 'N/A' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Discount Percent -->
            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Sconto Appuntamento:
                  <div class="d-inline-block text-body-1">
                    {{ props.productData.discount_percent || '0' }}%
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Created at -->
            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Creato il:
                  <div class="d-inline-block text-body-1">
                    {{ props.productData.created_at }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Updated at -->
            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Aggiornato il:
                  <div class="d-inline-block text-body-1">
                    {{ props.productData.updated_at }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Enabled -->
            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Abilitato:
                  <div class="d-inline-block text-body-1">
                    {{ props.productData.enabled ? 'Sì' : 'No' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <!-- Notes -->
            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Note:
                  <div class="d-inline-block text-body-1">
                    {{ props.productData.notes || 'N/A' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>
          </VList>

          <!-- 👉 Details -->
          <h5 class="text-h5 mt-4">
            Compensi
          </h5>

          <VDivider class="my-4" />

          <VList class="card-list mt-2">
            <!-- Type -->
            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Tipo compenso
                  <div class="d-inline-block text-body-1">
                    {{ props.productData.fee_type || 'N/A' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Gestione
                  <div class="d-inline-block text-body-1">
                    {{ props.productData.management_fee || 'N/A' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Procacciatore
                  <div class="d-inline-block text-body-1">
                    {{ props.productData.getter_fee || 'N/A' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Agente
                  <div class="d-inline-block text-body-1">
                    {{ props.productData.agent_fee || 'N/A' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Struttura
                  <div class="d-inline-block text-body-1">
                    {{ props.productData.structure_fee || 'N/A' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Struttura Top
                  <div class="d-inline-block text-body-1">
                    {{ props.productData.structure_top_fee || 'N/A' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle>
                <h6 class="text-h6">
                  Venditore
                  <div class="d-inline-block text-body-1">
                    {{ props.productData.salesperson_fee || 'N/A' }}
                  </div>
                </h6>
              </VListItemTitle>
            </VListItem>
          </VList>

        </VCardText>


        <!-- 👉 Edit and Suspend button -->
        <VCardText class="d-flex justify-center gap-x-4">
          <VBtn
            v-if="$can('edit', 'products')"
            variant="elevated"
            @click="isProductInfoEditDialogVisible = true"
          >
            Modifica
          </VBtn>

          <VBtn
            v-if="$can('delete', 'products')"
            variant="tonal"
            color="error"
          >
            Elimina
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>
    <!-- !SECTION -->
  </VRow>

  <!-- 👉 Edit product info dialog -->
  <ProductInfoEditDialog
    v-if="$can('edit', 'products')"
    v-model:isDialogVisible="isProductInfoEditDialogVisible"
    :product-data="props.productData"
    @submit="updateProductInfo"
  />
</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 0.5rem;
}

.text-capitalize {
  text-transform: capitalize !important;
}
</style>
