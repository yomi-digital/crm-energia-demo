<script setup>
const props = defineProps({
  matchedProduct: {
    type: String,
    required: true,
  },
  matchedBrand: {
    type: String,
    required: true,
  },
  originalBrandId: {
    type: [String, Number],
    default: null,
  },
})

const icon = 'tabler-refresh'
</script>

<template>
  <VAlert
    type="success"
    variant="tonal"
    class="mb-4 brand-override-alert"
    border="start"
    border-width="4"
    :icon="icon"
  >
    <template #title>
      <div class="d-flex align-center">
        <VIcon
          :icon="icon"
          size="20"
          class="me-2"
        />
        <span class="text-h6 font-weight-medium">
          Brand Aggiornato Automaticamente
        </span>
      </div>
    </template>

    <div class="mt-3">
      <div class="text-body-1 mb-2">
        Il sistema ha rilevato e corretto automaticamente il brand per questa pratica:
      </div>
      
      <VCard
        variant="outlined"
        class="pa-3 bg-surface"
      >
        <div class="d-flex flex-column gap-2">
          <div class="d-flex align-center">
            <VIcon
              icon="tabler-package"
              size="16"
              class="me-2 text-primary"
            />
            <span class="text-body-2 text-medium-emphasis me-2">
              Prodotto trovato:
            </span>
            <VChip
              size="small"
              color="primary"
              variant="tonal"
            >
              {{ matchedProduct }}
            </VChip>
          </div>
          
          <VDivider class="my-1" />
          
          <div class="d-flex align-center">
            <VIcon
              icon="tabler-building-store"
              size="16"
              class="me-2 text-success"
            />
            <span class="text-body-2 text-medium-emphasis me-2">
              Brand aggiornato a:
            </span>
            <VChip
              size="small"
              color="success"
              variant="tonal"
            >
              {{ matchedBrand }}
            </VChip>
          </div>
        </div>
      </VCard>

      <div class="text-body-2 text-medium-emphasis mt-3">
        <VIcon
          icon="tabler-info-circle"
          size="14"
          class="me-1"
        />
        Il prodotto è stato selezionato automaticamente nel form sottostante.
      </div>
    </div>
  </VAlert>
</template>

<style lang="scss" scoped>
.brand-override-alert {
  .v-alert__content {
    padding: 0;
  }
  
  .v-alert__title {
    margin-bottom: 0;
    padding-bottom: 0;
  }
  
  .v-card {
    border-color: rgb(var(--v-theme-primary), 0.12);
  }
}
</style>
