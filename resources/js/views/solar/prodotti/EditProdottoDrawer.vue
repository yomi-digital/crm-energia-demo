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
const potenzaInverter = ref()
const marca = ref()
const linkSchedaProdottoTecnica = ref('')

// Nuovi campi
const quantitaInverter = ref()
const marcaBatteria = ref()
const potenzaBatteria = ref()
const quantitaBatterie = ref()
const quantitaPannelli = ref()
const marcaPannelli = ref()
const listini = ref([])

const categorie = ref([])
const availableListini = ref([])

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

const loadListini = async () => {
  try {
    const response = await $api('/listini?itemsPerPage=99999&is_active=true')
    const data = Array.isArray(response) ? response : (response.data || [])
    availableListini.value = data.map(l => ({
      title: l.nome,
      value: l.id,
    }))
  } catch (error) {
    console.error('Errore nel caricamento dei listini:', error)
  }
}

fkCategoria.value = props.prodotto.fk_categoria
codiceProdotto.value = props.prodotto.codice_prodotto
descrizione.value = props.prodotto.descrizione
potenzaKwp.value = props.prodotto.potenza_kwp_pannelli
capacitaKwh.value = props.prodotto.capacita_kwh
prezzoBase.value = props.prodotto.prezzo_base
potenzaInverter.value = props.prodotto.potenza_inverter
marca.value = props.prodotto.marca_inverter
linkSchedaProdottoTecnica.value = props.prodotto.link_scheda_prodotto_tecnica || ''
quantitaInverter.value = props.prodotto.quantita_inverter
marcaBatteria.value = props.prodotto.marca_batteria
potenzaBatteria.value = props.prodotto.potenza_batteria
quantitaBatterie.value = props.prodotto.quantita_batterie
quantitaPannelli.value = props.prodotto.quantita_pannelli
marcaPannelli.value = props.prodotto.marca_pannelli
listini.value = (props.prodotto.listini || []).map(l => l.id)

watch(() => props.isDrawerOpen, (val) => {
  if (val) {
    loadCategorie()
    loadListini()
    fkCategoria.value = props.prodotto.fk_categoria
    codiceProdotto.value = props.prodotto.codice_prodotto
    descrizione.value = props.prodotto.descrizione
    potenzaKwp.value = props.prodotto.potenza_kwp_pannelli
    capacitaKwh.value = props.prodotto.capacita_kwh
    prezzoBase.value = props.prodotto.prezzo_base
    potenzaInverter.value = props.prodotto.potenza_inverter
    marca.value = props.prodotto.marca_inverter
    linkSchedaProdottoTecnica.value = props.prodotto.link_scheda_prodotto_tecnica || ''
    quantitaInverter.value = props.prodotto.quantita_inverter
    marcaBatteria.value = props.prodotto.marca_batteria
    potenzaBatteria.value = props.prodotto.potenza_batteria
    quantitaBatterie.value = props.prodotto.quantita_batterie
    quantitaPannelli.value = props.prodotto.quantita_pannelli
    marcaPannelli.value = props.prodotto.marca_pannelli
    listini.value = (props.prodotto.listini || []).map(l => l.id)
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
        potenza_kwp_pannelli: potenzaKwp.value,
        capacita_kwh: capacitaKwh.value,
        prezzo_base: prezzoBase.value,
        potenza_inverter: potenzaInverter.value,
        marca_inverter: marca.value,
      }
      
      if (linkSchedaProdottoTecnica.value) {
        body.link_scheda_prodotto_tecnica = linkSchedaProdottoTecnica.value
      }

      // Campi opzionali - solo se valorizzati (in edit li includiamo comunque se non undefined)
      if (quantitaInverter.value !== undefined)
        body.quantita_inverter = quantitaInverter.value

      if (marcaBatteria.value !== undefined)
        body.marca_batteria = marcaBatteria.value

      if (potenzaBatteria.value !== undefined)
        body.potenza_batteria = potenzaBatteria.value

      if (quantitaBatterie.value !== undefined)
        body.quantita_batterie = quantitaBatterie.value

      if (quantitaPannelli.value !== undefined)
        body.quantita_pannelli = quantitaPannelli.value

      if (marcaPannelli.value !== undefined)
        body.marca_pannelli = marcaPannelli.value
      
      if (listini.value !== undefined) {
        body.listini = listini.value
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

              <!-- 👉 Listini -->
              <VCol cols="12">
                <AppAutocomplete
                  v-model="listini"
                  label="Listini"
                  :items="availableListini"
                  placeholder="Seleziona listini"
                  multiple
                  chips
                  closable-chips
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

              <!-- Sezione Pannelli -->
              <VCol cols="12">
                <VDivider class="my-2" />
                <div class="d-flex align-center gap-2 mb-2">
                  <VIcon icon="tabler-solar-panel" size="20" />
                  <span class="text-subtitle-2">Pannelli</span>
                </div>
              </VCol>

              <!-- 👉 Potenza kWp pannelli -->
              <VCol cols="12">
                <AppTextField
                  v-model="potenzaKwp"
                  :rules="[requiredValidator]"
                  label="Potenza kWp pannelli"
                  placeholder="1000"
                  type="number"
                />
              </VCol>

              <!-- 👉 Quantità Pannelli -->
              <VCol cols="12">
                <AppTextField
                  v-model="quantitaPannelli"
                  label="Quantità pannelli"
                  placeholder="10"
                  type="number"
                />
              </VCol>

              <!-- 👉 Marca Pannelli -->
              <VCol cols="12">
                <AppTextField
                  v-model="marcaPannelli"
                  label="Marca pannelli"
                  placeholder="Marca pannelli"
                />
              </VCol>

              <!-- Sezione Inverter -->
              <VCol cols="12">
                <VDivider class="my-2" />
                <div class="d-flex align-center gap-2 mb-2">
                  <VIcon icon="tabler-bolt" size="20" />
                  <span class="text-subtitle-2">Inverter</span>
                </div>
              </VCol>

              <!-- 👉 Potenza Inverter -->
              <VCol cols="12">
                <AppTextField
                  v-model="potenzaInverter"
                  :rules="[requiredValidator]"
                  label="Potenza Inverter (kW)"
                  placeholder="0"
                  type="number"
                />
              </VCol>

              <!-- 👉 Quantità Inverter -->
              <VCol cols="12">
                <AppTextField
                  v-model="quantitaInverter"
                  label="Quantità inverter"
                  placeholder="1"
                  type="number"
                />
              </VCol>

              <!-- 👉 Marca inverter -->
              <VCol cols="12">
                <AppTextField
                  v-model="marca"
                  :rules="[requiredValidator]"
                  label="Marca inverter"
                  placeholder="Marca inverter"
                />
              </VCol>

              <!-- Sezione Batteria -->
              <VCol cols="12">
                <VDivider class="my-2" />
                <div class="d-flex align-center gap-2 mb-2">
                  <VIcon icon="tabler-battery" size="20" />
                  <span class="text-subtitle-2">Batteria</span>
                </div>
              </VCol>

              <!-- 👉 Potenza Batteria -->
              <VCol cols="12">
                <AppTextField
                  v-model="potenzaBatteria"
                  label="Potenza batteria (kWh)"
                  placeholder="5"
                  type="number"
                />
              </VCol>

              <!-- 👉 Quantità Batterie -->
              <VCol cols="12">
                <AppTextField
                  v-model="quantitaBatterie"
                  label="Quantità batterie"
                  placeholder="1"
                  type="number"
                />
              </VCol>

              <!-- 👉 Marca Batteria -->
              <VCol cols="12">
                <AppTextField
                  v-model="marcaBatteria"
                  label="Marca batteria"
                  placeholder="Marca batteria"
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

