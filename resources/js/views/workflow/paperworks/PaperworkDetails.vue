<script setup>
const props = defineProps({
  formData: {
    type: null,
    required: true,
  },
})

const emit = defineEmits(['update:formData'])

const formData = ref(props.formData)

const statuses = ref([
  { title: 'ACCANTONATO', value: 'ACCANTONATO' },
  { title: 'ATTIVO', value: 'ATTIVO' },
  { title: 'IN PROVISIONING', value: 'IN PROVISIONING' },
  { title: 'INSERITO', value: 'INSERITO' },
  { title: 'INVIATO', value: 'INVIATO' },
])

watch(formData, () => {
  emit('update:formData', formData.value)
})
</script>

<template>
  <VForm>
    <VRow>


      <!-- 👉 Note -->
      <VCol
        cols="12"
        md="12"
      >
        <AppTextarea
          v-model="formData.notes"
          label="Note"
          placeholder="Note"
        />
      </VCol>

      <!-- 👉 Note Demo -->
      <VCol
        cols="12"
        md="12"
      >
        <AppTextarea
          v-model="formData.owner_notes"
          label="Note Demo"
          placeholder="Note"
        />
      </VCol>

      <VCol
        cols="12"
        md="12"
      >
        <h5 class="text-h5 mt-6">
          ORDINE
        </h5>
      </VCol>

      <!-- 👉 Stato Ordine -->
      <VCol
        cols="12"
        md="6"
      >
        <AppSelect
          v-model="formData.order_status"
          label="Stato Ordine"
          placeholder="Seleziona"
          :items="statuses"
        />
      </VCol>

      <!-- 👉 Codice Ordine -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="formData.order_code"
          label="Codice Ordine"
          placeholder="09886655"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Data Inserimento -->
      <VCol
        cols="12"
        md="6"
      >
        <AppDateTimePicker
          v-model="formData.partner_sent_at"
          label="Data Inserimento"
          placeholder="Data Inserimento"
        />
      </VCol>

      <!-- 👉 Data Annullamento -->
      <VCol
        cols="12"
        md="6"
      >
        <AppDateTimePicker
          v-model="formData.canceled_at"
          label="Data Annullamento"
          placeholder="Data Annullamento"
        />
      </VCol>

      <!-- 👉 Data Scadenza -->
      <VCol
        cols="12"
        md="6"
      >
        <AppDateTimePicker
          v-model="formData.expired_at"
          label="Data Scadenza"
          placeholder="Data Scadenza"
        />
      </VCol>

      <VCol
        cols="12"
        md="12"
      >
        <h5 class="text-h5 mt-6">
          PARTNER
        </h5>
      </VCol>

      <!-- 👉 Esito Partner -->
      <VCol
        cols="12"
        md="6"
      >
        <AppSelect
          v-model="formData.partner_outcome"
          label="Esito Partner"
          placeholder="Seleziona"
          :items="statuses"
        />
      </VCol>

      <!-- 👉 Data Esito Partner -->
      <VCol
        cols="12"
        md="6"
      >
        <AppDateTimePicker
          v-model="formData.partner_outcome_at"
          label="Data Esito Partner"
          placeholder="Data Esito Partner"
        />
      </VCol>

    </VRow>
  </VForm>
</template>
