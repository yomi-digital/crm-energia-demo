<script setup>
import { ref } from 'vue';

const name = ref('')
const lastName = ref('')
const businessName = ref('')
const taxIdCode = ref('')
const vatNumber = ref('')
const email = ref('')
const phone = ref('')
const mobile = ref('')
const atecoCode = ref('')
const pec = ref('')
const uniqueCode = ref('')
const category = ref('Residenziale')
const address = ref('')
const region = ref('')
const province = ref('')
const city = ref('')
const zip = ref('')

const refForm = ref()
const isSaving = ref(false)

const emit = defineEmits([
  'customerData',
])

const errors = ref({
  name: [],
  lastName: [],
  businessName: [],
  taxIdCode: [],
  vatNumber: [],
  email: [],
  phone: [],
  mobile: [],
  atecoCode: [],
  pec: [],
  uniqueCode: [],
  category: [],
  address: [],
  region: [],
  province: [],
  city: [],
  zip: [],
})

const categories = ref([
  { title: 'Ditta Individuale', value: 'all' },
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

const createUser = async () => {
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
  <VForm ref="refForm" @submit.prevent="createUser">
    <VRow>
      <!-- 👉 Customer category -->
      <VCol
        cols="12"
        md="12"
      >
        <AppSelect
          v-model="category"
          label="Tipologia"
          placeholder="Seleziona"
          :items="categories"
          :rules="[requiredValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="12"
      >
        <h5 class="text-h5 mt-6">
          INFORMAZIONI
        </h5>
      </VCol>

      <!-- 👉 First Name -->
      <VCol
        cols="12"
        md="6"
        v-if="category !== 'Business'"
      >
        <AppTextField
          v-model="name"
          label="Nome"
          placeholder="Mario"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Last Name -->
      <VCol
        cols="12"
        md="6"
        v-if="category !== 'Business'"
      >
        <AppTextField
        v-model="lastName"
        label="Cognome"
        placeholder="Rossi"
        :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Business Name -->
      <VCol
        cols="12"
        md="12"
        v-if="category !== 'Residenziale'"
      >
        <AppTextField
        v-model="businessName"
        label="Ragione Sociale"
        placeholder="Società SRL"
        :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Tax Code -->
      <VCol
        cols="12"
        md="6"
        v-if="category !== 'Business'"
      >
        <AppTextField
          v-model="taxIdCode"
          label="Codice Fiscale"
          placeholder="ABCDEF12G34H567I"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 VAT Number -->
      <VCol
        cols="12"
        md="6"
        v-if="category !== 'Residenziale'"
      >
        <AppTextField
          v-model="vatNumber"
          label="Partita IVA"
          placeholder="12345678901"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Ateco Code -->
      <VCol
        cols="12"
        md="6"
        v-if="category !== 'Residenziale'"
      >
        <AppTextField
          v-model="atecoCode"
          label="Codice Ateco"
          placeholder="123456"
        />
      </VCol>

      <!-- 👉 Unique Code -->
      <!-- <VCol
        cols="12"
        md="6"
        v-if="category !== 'Residenziale'"
      >
        <AppTextField
          v-model="uniqueCode"
          label="Codice Unico"
          placeholder="123456"
        />
      </VCol> -->

      <VCol
        cols="12"
        md="12"
      >
        <h5 class="text-h5 mt-6">
          CONTATTI
        </h5>
      </VCol>

      <!-- 👉 Phone -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="phone"
          label="Telefono"
          placeholder="1234567890"
        />
      </VCol>

      <!-- 👉 Mobile -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="mobile"
          label="Cellulare"
          placeholder="1234567890"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Email -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="email"
          label="Email"
          placeholder="mail@mail.com"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 PEC -->
      <!-- <VCol
        cols="12"
        md="6"
        v-if="category !== 'Residenziale'"
      >
        <AppTextField
          v-model="pec"
          label="PEC"
          placeholder="mail@pec.com"
        />
      </VCol> -->

      <VCol
        cols="12"
        md="12"
      >
        <h5 class="text-h5 mt-6">
          INDIRIZZO
        </h5>
      </VCol>

      <!-- 👉 Address -->
      <VCol
        cols="12"
        md="12"
      >
        <AppTextField
          v-model="address"
          label="Indirizzo"
          placeholder="Via Roma 123"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Region -->
      <VCol
        cols="12"
        md="6"
      >
        <AppSelect
          v-model="region"
          label="Regione"
          placeholder="Seleziona"
          :rules="[requiredValidator]"
          :items="regions"
        />
      </VCol>

      <!-- 👉 Province -->
      <VCol
        cols="12"
        md="6"
      >
        <AppSelect
          v-model="province"
          label="Provincia"
          placeholder="Seleziona"
          :rules="[requiredValidator]"
          :items="provinces"
        />
      </VCol>

      <!-- 👉 City -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="city"
          label="Città"
          placeholder="Roma"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Zip -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="zip"
          label="CAP"
          placeholder="00100"
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
