<script setup>
const props = defineProps({
  customerData: {
    type: Object,
    required: false,
    default: () => ({
      id: 0,
      name: '',
      last_name: '',
      business_name: '',
      tax_code_id: '',
      vat_number: '',
      email: '',
      phone: '',
      mobile: '',
      ateco_code: '',
      pec: '',
      unique_code: '',
      category: '',
      address: '',
      region: '',
      province: '',
      city: '',
      zip: '',
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

const customerData = ref(structuredClone(toRaw(props.customerData)))
const isUseAsBillingAddress = ref(false)

watch(props, () => {
  customerData.value = structuredClone(toRaw(props.customerData))
})

const onFormSubmit = () => {
  emit('update:isDialogVisible', false)
  emit('submit', customerData.value)
}

const onFormReset = () => {
  customerData.value = structuredClone(toRaw(props.customerData))
  emit('update:isDialogVisible', false)
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}

const categories = ref([
  { title: 'N/A', value: 'all' },
  { title: 'Residenziale', value: 'Residenziale' },
  { title: 'Business', value: 'Business' },
])

const regions = [
  { title: 'Abruzzo', value: 'Abruzzo' },
  { title: 'Basilicata', value: 'Basilicata' },
  { title: 'Calabria', value: 'Calabria' },
  { title: 'Campania', value: 'Campania' },
  { title: 'Emilia-Romagna', value: 'Emilia-Romagna' },
  { title: 'Friuli-Venezia Giulia', value: 'Friuli-Venezia Giulia' },
  { title: 'Lazio', value: 'Lazio' },
  { title: 'Liguria', value: 'Liguria' },
  { title: 'Lombardia', value: 'Lombardia' },
  { title: 'Marche', value: 'Marche' },
  { title: 'Molise', value: 'Molise' },
  { title: 'Piemonte', value: 'Piemonte' },
  { title: 'Puglia', value: 'Puglia' },
  { title: 'Sardegna', value: 'Sardegna' },
  { title: 'Sicilia', value: 'Sicilia' },
  { title: 'Toscana', value: 'Toscana' },
  { title: 'Trentino-Alto Adige', value: 'Trentino-Alto Adige' },
  { title: 'Umbria', value: 'Umbria' },
  { title: 'Valle d\'Aosta', value: 'Valle d\'Aosta' },
  { title: 'Veneto', value: 'Veneto' },
]

const provinces = [
  { title: 'Agrigento (AG)', value: 'AG' },
  { title: 'Alessandria (AL)', value: 'AL' },
  { title: 'Ancona (AN)', value: 'AN' },
  { title: 'Aosta (AO)', value: 'AO' },
  { title: 'Arezzo (AR)', value: 'AR' },
  { title: 'Ascoli Piceno (AP)', value: 'AP' },
  { title: 'Asti (AT)', value: 'AT' },
  { title: 'Avellino (AV)', value: 'AV' },
  { title: 'Bari (BA)', value: 'BA' },
  { title: 'Barletta-Andria-Trani (BT)', value: 'BT' },
  { title: 'Belluno (BL)', value: 'BL' },
  { title: 'Benevento (BN)', value: 'BN' },
  { title: 'Bergamo (BG)', value: 'BG' },
  { title: 'Biella (BI)', value: 'BI' },
  { title: 'Bologna (BO)', value: 'BO' },
  { title: 'Bolzano (BZ)', value: 'BZ' },
  { title: 'Brescia (BS)', value: 'BS' },
  { title: 'Brindisi (BR)', value: 'BR' },
  { title: 'Cagliari (CA)', value: 'CA' },
  { title: 'Caltanissetta (CL)', value: 'CL' },
  { title: 'Campobasso (CB)', value: 'CB' },
  { title: 'Carbonia-Iglesias (CI)', value: 'CI' },
  { title: 'Caserta (CE)', value: 'CE' },
  { title: 'Catania (CT)', value: 'CT' },
  { title: 'Catanzaro (CZ)', value: 'CZ' },
  { title: 'Chieti (CH)', value: 'CH' },
  { title: 'Como (CO)', value: 'CO' },
  { title: 'Cosenza (CS)', value: 'CS' },
  { title: 'Cremona (CR)', value: 'CR' },
  { title: 'Crotone (KR)', value: 'KR' },
  { title: 'Cuneo (CN)', value: 'CN' },
  { title: 'Enna (EN)', value: 'EN' },
  { title: 'Fermo (FM)', value: 'FM' },
  { title: 'Ferrara (FE)', value: 'FE' },
  { title: 'Firenze (FI)', value: 'FI' },
  { title: 'Foggia (FG)', value: 'FG' },
  { title: 'Forlì-Cesena (FC)', value: 'FC' },
  { title: 'Frosinone (FR)', value: 'FR' },
  { title: 'Genova (GE)', value: 'GE' },
  { title: 'Gorizia (GO)', value: 'GO' },
  { title: 'Grosseto (GR)', value: 'GR' },
  { title: 'Imperia (IM)', value: 'IM' },
  { title: 'Isernia (IS)', value: 'IS' },
  { title: 'La Spezia (SP)', value: 'SP' },
  { title: 'L\'Aquila (AQ)', value: 'AQ' },
  { title: 'Latina (LT)', value: 'LT' },
  { title: 'Lecce (LE)', value: 'LE' },
  { title: 'Lecco (LC)', value: 'LC' },
  { title: 'Livorno (LI)', value: 'LI' },
  { title: 'Lodi (LO)', value: 'LO' },
  { title: 'Lucca (LU)', value: 'LU' },
  { title: 'Macerata (MC)', value: 'MC' },
  { title: 'Mantova (MN)', value: 'MN' },
  { title: 'Massa-Carrara (MS)', value: 'MS' },
  { title: 'Matera (MT)', value: 'MT' },
  { title: 'Medio Campidano (VS)', value: 'VS' },
  { title: 'Messina (ME)', value: 'ME' },
  { title: 'Milano (MI)', value: 'MI' },
  { title: 'Modena (MO)', value: 'MO' },
  { title: 'Monza e della Brianza (MB)', value: 'MB' },
  { title: 'Napoli (NA)', value: 'NA' },
  { title: 'Novara (NO)', value: 'NO' },
  { title: 'Nuoro (NU)', value: 'NU' },
  { title: 'Ogliastra (OG)', value: 'OG' },
  { title: 'Olbia-Tempio (OT)', value: 'OT' },
  { title: 'Oristano (OR)', value: 'OR' },
  { title: 'Padova (PD)', value: 'PD' },
  { title: 'Palermo (PA)', value: 'PA' },
  { title: 'Parma (PR)', value: 'PR' },
  { title: 'Pavia (PV)', value: 'PV' },
  { title: 'Perugia (PG)', value: 'PG' },
  { title: 'Pesaro e Urbino (PU)', value: 'PU' },
  { title: 'Pescara (PE)', value: 'PE' },
  { title: 'Piacenza (PC)', value: 'PC' },
  { title: 'Pisa (PI)', value: 'PI' },
  { title: 'Pistoia (PT)', value: 'PT' },
  { title: 'Pordenone (PN)', value: 'PN' },
  { title: 'Potenza (PZ)', value: 'PZ' },
  { title: 'Prato (PO)', value: 'PO' },
  { title: 'Ragusa (RG)', value: 'RG' },
  { title: 'Ravenna (RA)', value: 'RA' },
  { title: 'Reggio Calabria (RC)', value: 'RC' },
  { title: 'Reggio Emilia (RE)', value: 'RE' },
  { title: 'Rieti (RI)', value: 'RI' },
  { title: 'Rimini (RN)', value: 'RN' },
  { title: 'Roma (RM)', value: 'RM' },
  { title: 'Rovigo (RO)', value: 'RO' },
  { title: 'Salerno (SA)', value: 'SA' },
  { title: 'Sassari (SS)', value: 'SS' },
  { title: 'Savona (SV)', value: 'SV' },
  { title: 'Siena (SI)', value: 'SI' },
  { title: 'Siracusa (SR)', value: 'SR' },
  { title: 'Sondrio (SO)', value: 'SO' },
  { title: 'Taranto (TA)', value: 'TA' },
  { title: 'Teramo (TE)', value: 'TE' },
  { title: 'Terni (TR)', value: 'TR' },
  { title: 'Torino (TO)', value: 'TO' },
  { title: 'Trapani (TP)', value: 'TP' },
  { title: 'Trento (TN)', value: 'TN' },
  { title: 'Treviso (TV)', value: 'TV' },
  { title: 'Trieste (TS)', value: 'TS' },
  { title: 'Udine (UD)', value: 'UD' },
  { title: 'Varese (VA)', value: 'VA' },
  { title: 'Venezia (VE)', value: 'VE' },
  { title: 'Verbano-Cusio-Ossola (VB)', value: 'VB' },
  { title: 'Vercelli (VC)', value: 'VC' },
  { title: 'Verona (VR)', value: 'VR' },
  { title: 'Vibo Valentia (VV)', value: 'VV' },
  { title: 'Vicenza (VI)', value: 'VI' },
  { title: 'Viterbo (VT)', value: 'VT' },
]
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
          Modifica Dettagli Cliente
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
            <!-- 👉 Customer category -->
            <VCol
              cols="12"
              md="12"
            >
              <AppSelect
                v-model="customerData.category"
                label="Tipologia"
                placeholder="Seleziona"
                :items="categories"
              />
            </VCol>

            <!-- 👉 Name -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="customerData.name"
                label="Nome"
                placeholder="Mario"
              />
            </VCol>

            <!-- 👉 Last Name -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="customerData.last_name"
                label="Cognome"
                placeholder="Rossi"
              />
            </VCol>

            <!-- 👉 Business Name -->
            <VCol
              cols="12"
              md="12"
              v-if="customerData.category !== 'Residenziale'"
            >
              <AppTextField
                v-model="customerData.business_name"
                label="Ragione Sociale"
                placeholder="Società SRL"
              />
            </VCol>

            <!-- 👉 Tax Code -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="customerData.tax_id_code"
                label="Codice Fiscale"
                placeholder="ABCDEF12G34H567I"
              />
            </VCol>

            <!-- 👉 VAT Number -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="customerData.vat_number"
                label="Partita IVA"
                placeholder="12345678901"
              />
            </VCol>

            <!-- 👉 Ateco Code -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="customerData.ateco_code"
                label="Codice Ateco"
                placeholder="123456"
              />
            </VCol>

            <!-- 👉 Unique Code -->
            <!-- <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="customerData.unique_code"
                label="Codice Unico"
                placeholder="123456"
              />
            </VCol> -->

            <!-- 👉 Phone -->
            <VCol
              cols="12"
              md="6"
            >
              <CustomerTelPhoneInput
                v-model="customerData.phone"
                :type="'phone'"
                :customerId="customerData.id"
                label="Telefono"
                placeholder="1234567890"
              />
            </VCol>

            <!-- 👉 Mobile -->
            <VCol
              cols="12"
              md="6"
            >
              <CustomerTelPhoneInput
                v-model="customerData.mobile"
                :type="'mobile'"
                :customerId="customerData.id"
                label="Cellulare"
                placeholder="1234567890"
              />
            </VCol>

            <!-- 👉 Email -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="customerData.email"
                label="Email"
                placeholder="mail@mail.com"
              />
            </VCol>

            <!-- 👉 PEC -->
            <!-- <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="customerData.pec"
                label="PEC"
                placeholder="mail@pec.com"
              />
            </VCol> -->

            <!-- 👉 Address -->
            <VCol
              cols="12"
              md="12"
            >
              <AppTextField
                v-model="customerData.address"
                label="Indirizzo"
                placeholder="Via Roma 123"
              />
            </VCol>

            <!-- 👉 Region -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="customerData.region"
                label="Regione"
                placeholder="Seleziona"
                :rules="[requiredValidator]"
                :items="regions"
              />
              <!-- <AppTextField
                v-model="customerData.region"
                label="Regione"
                placeholder="Lazio"
              /> -->
            </VCol>

            <!-- 👉 Province -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="customerData.province"
                label="Provincia"
                placeholder="Seleziona"
                :rules="[requiredValidator]"
                :items="provinces"
              />
              <!-- <AppTextField
                v-model="customerData.province"
                label="Provincia"
                placeholder="RM"
              /> -->
            </VCol>

            <!-- 👉 City -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="customerData.city"
                label="Città"
                placeholder="Roma"
              />
            </VCol>

            <!-- 👉 Zip -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="customerData.zip"
                label="CAP"
                placeholder="00100"
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
