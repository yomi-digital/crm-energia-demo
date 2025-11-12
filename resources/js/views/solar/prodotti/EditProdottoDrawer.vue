<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  prodotto: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'prodottoData',
])

const isFormValid = ref(false)
const refForm = ref()
const fkCategoria = ref()
const codiceProdotto = ref()
const descrizione = ref()
const potenzaKwp = ref()
const capacitaKwh = ref()
const prezzoBase = ref()
const linkSchedaProdottoTecnica = ref('')

const categorie = ref([])

const loadCategorie = async () => {
  try {
    const response = await $api('/product-categories?itemsPerPage=99999&is_active=true')
    const data = Array.isArray(response) ? response : (response.data || [])
    categorie.value = data.map(cat => ({
      title: cat.nome_categoria,
      value: cat.id_categoria || cat.id,
    }))
  } catch (error) {
    console.error('Errore nel caricamento delle categorie:', error)
  }
}

fkCategoria.value = props.prodotto.fk_categoria
codiceProdotto.value = props.prodotto.codice_prodotto
descrizione.value = props.prodotto.descrizione
potenzaKwp.value = props.prodotto.potenza_kwp
capacitaKwh.value = props.prodotto.capacita_kwh
prezzoBase.value = props.prodotto.prezzo_base
linkSchedaProdottoTecnica.value = props.prodotto.link_scheda_prodotto_tecnica || ''

watch(() => props.isDrawerOpen, (val) => {
  if (val) {
    loadCategorie()
    fkCategoria.value = props.prodotto.fk_categoria
    codiceProdotto.value = props.prodotto.codice_prodotto
    descrizione.value = props.prodotto.descrizione
    potenzaKwp.value = props.prodotto.potenza_kwp
    capacitaKwh.value = props.prodotto.capacita_kwh
    prezzoBase.value = props.prodotto.prezzo_base
    linkSchedaProdottoTecnica.value = props.prodotto.link_scheda_prodotto_tecnica || ''
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
      const body = {
        id: props.prodotto.id_prodotto || props.prodotto.id,
        fk_categoria: parseInt(fkCategoria.value, 10),
        codice_prodotto: codiceProdotto.value,
        descrizione: descrizione.value,
        potenza_kwp: potenzaKwp.value,
        capacita_kwh: capacitaKwh.value,
        prezzo_base: prezzoBase.value,
      }
      
      if (linkSchedaProdottoTecnica.value) {
        body.link_scheda_prodotto_tecnica = linkSchedaProdottoTecnica.value
      }
      
      emit('prodottoData', body)
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
      title="Modifica Prodotto"
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
              <!-- 👉 Categoria -->
              <VCol cols="12">
                <AppAutocomplete
                  v-model="fkCategoria"
                  :rules="[requiredValidator]"
                  label="Categoria"
                  :items="categorie"
                  placeholder="Seleziona categoria"
                />
              </VCol>

              <!-- 👉 Codice Prodotto -->
              <VCol cols="12">
                <AppTextField
                  v-model="codiceProdotto"
                  :rules="[requiredValidator]"
                  label="Codice Prodotto"
                  placeholder="Impianto 3+5"
                />
              </VCol>

              <!-- 👉 Descrizione -->
              <VCol cols="12">
                <AppTextField
                  v-model="descrizione"
                  :rules="[requiredValidator]"
                  label="Descrizione"
                  placeholder="Impianto 3+5"
                />
              </VCol>

              <!-- 👉 Potenza kWp -->
              <VCol cols="12">
                <AppTextField
                  v-model="potenzaKwp"
                  :rules="[requiredValidator]"
                  label="Potenza kWp"
                  placeholder="1000"
                  type="number"
                />
              </VCol>

              <!-- 👉 Capacità kWh -->
              <VCol cols="12">
                <AppTextField
                  v-model="capacitaKwh"
                  :rules="[requiredValidator]"
                  label="Capacità kWh"
                  placeholder="1000"
                  type="number"
                />
              </VCol>

              <!-- 👉 Prezzo Base -->
              <VCol cols="12">
                <AppTextField
                  v-model="prezzoBase"
                  :rules="[requiredValidator]"
                  label="Prezzo Base"
                  placeholder="20000"
                  type="number"
                />
              </VCol>

              <!-- 👉 Link Scheda Prodotto Tecnica -->
              <VCol cols="12">
                <AppTextField
                  v-model="linkSchedaProdottoTecnica"
                  label="Link Scheda Prodotto Tecnica"
                  placeholder="https://www.example.com"
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

