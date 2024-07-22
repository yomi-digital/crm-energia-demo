<script setup>

const props = defineProps({
  formData: {
    type: null,
    required: true,
  },
  details: {
    type: null,
    required: true,
  },
})

const emit = defineEmits(['update:formData'])

const formData = ref(props.formData)

watch(formData, () => {
  emit('update:formData', formData.value)
})
</script>

<template>
  <VRow>
    <VCol
      cols="12"
      class="pb-4 pb-sm-0"
    >
      <h4 class="text-h4 mb-4">
        Rivedi e completa la pratica
      </h4>

      <p>Conferma che le informazioni siano corrette e conferma la creazione della pratica.</p>

      <table class="text-base">
        <tr>
          <td style="inline-size: 150px;">
            <p class="font-weight-medium mb-2">
              Agente
            </p>
          </td>
          <td>
            <p class="mb-2">
              {{ props.details.agent.name }}
            </p>
          </td>
        </tr>

        <tr>
          <td>
            <p class="font-weight-medium mb-2">
              Cliente
            </p>
          </td>
          <td>
            <p class="mb-2">
              {{ props.details.customer.name }}
            </p>
          </td>
        </tr>

        <tr>
          <td>
            <p class="font-weight-medium mb-2">
              Appuntamento
            </p>
          </td>
          <td>
            <p class="mb-2">
              {{ props.details.customer.appointment_id ? 'Si ' + props.details.customer.appointment_title : 'No' }}
            </p>
          </td>
        </tr>

        <tr>
          <td>
            <p class="font-weight-medium mb-2">
              Tipo di Contratto
            </p>
          </td>
          <td>
            <p class="mb-2">
              {{ props.details.paperworkType.category }}
              {{ props.details.paperworkType.type }}
              {{ props.details.paperworkType.energy_type }}
              {{ props.details.paperworkType.mobile_type }}
              <span v-if="props.details.paperworkType.previous_provider">{{ props.details.paperworkType.previous_provider }}</span>
            </p>
          </td>
        </tr>

        <tr>
          <td>
            <p class="font-weight-medium mb-2">
              Prodotto
            </p>
          </td>
          <td>
            <p class="mb-2">
              {{ props.details.product.product_name }} ({{ props.details.product.brand_name }})
            </p>
          </td>
        </tr>

        <tr>
          <td>
            <p class="font-weight-medium mb-2">
              Account / POD / PDR
            </p>
          </td>
          <td>
            <p class="mb-2">
              {{ props.details.customer.account_pod_pdr || 'N/A' }}
            </p>
          </td>
        </tr>

        <tr>
          <td>
            <p class="font-weight-medium mb-2">
              Consumo Annuale
            </p>
          </td>
          <td>
            <p class="mb-2">
              {{ props.details.customer.annual_consumption || 'N/A' }}
            </p>
          </td>
        </tr>
      </table>

      <AppTextarea
        v-model="formData.notes"
        label="Note"
        placeholder="Note"
      />

      <VSwitch
        v-model="formData.isPaperworkDetailsConfirmed"
        label="Confermo che le informazioni sono corrette."
        class="mb-3"
      />
    </VCol>
  </VRow>
</template>
