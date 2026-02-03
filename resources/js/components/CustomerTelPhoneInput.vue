<template>
  <div class="customer-phone-input-wrapper">
    <!-- Mostra il componente solo quando è pronto con il valore corretto -->
    <phone-input
      :id="telephoneInputId"
      :value="formattedValueForLibrary"
      :label="label"
      :placeholder="placeholder"
      :name="name"
      :required="required"
      :default-country="defaultCountry"
      :arrow="arrow"
      :list-height="listHeight"
      :has-error="!!errorMessage"
      :error-message="null"
      :disabled="readonly"
      @phone="handlePhoneChange"
      @country="handleCountryChange"
      @phoneData="handlePhoneData"
    />
    
    <div v-if="displayError" class="custom-error-message d-flex align-center justify-space-between mt-1">
      <div class="d-flex align-center">
        <VIcon icon="tabler-alert-circle" size="18" class="me-2" />
        <span>{{ displayError }}</span>
      </div>
      <VBtn 
        v-if="props.showErrorDetails" 
        size="x-small" 
        variant="text" 
        color="error" 
        @click="$emit('click:errorDetails')"
        style="height: 24px; padding: 0 8px;"
      >
        Dettagli
      </VBtn>
    </div>
  </div>
</template>

<script setup>
import { $api } from '@/utils/api'
import { computed, ref } from 'vue'

const props = defineProps({
  modelValue: {type: String,default: ''},
  type: {type: String,required: true,validator: (value) => ['phone', 'mobile'].includes(value)},
  label: {type: String,default: 'Telefono'},
  placeholder: {type: String,default: 'Inserisci numero'},
  name: {type: String,default: ''},
  required: {type: Boolean,default: false},
  defaultCountry: {type: String,default: 'IT'},
  arrow: {type: Boolean,default: true},
  listHeight: {type: Number,default: 150},
  allowed: {type: Array, default: () => []},
  customerId: {type: [String, Number],default: null},
  readonly: {type: Boolean,default: false},
  customError: {type: String,default: ''},
  showErrorDetails: {type: Boolean,default: false}
})

const emit = defineEmits(['update:modelValue', 'onCheckUpdate', 'onValue', 'click:errorDetails'])

// Stato interno
const phoneNumber = ref('')
const internalErrorMessage = ref('')

// Computed per l'errore da mostrare (priorità a customError esterno, poi errore interno)
const displayError = computed(() => props.customError || internalErrorMessage.value)

// Computed value per la libreria (rimuove il + dal numero)
const formattedValueForLibrary = computed(() => {
  if (!phoneNumber.value) return ''
  const valueWithoutPlus = phoneNumber.value.startsWith('+') 
    ? phoneNumber.value.slice(1) 
    : phoneNumber.value
  return valueWithoutPlus
})

// Inizializza il valore interno
const initializePhoneNumber = () => {
  if (props.modelValue && props.modelValue.trim() !== '') {
    phoneNumber.value = props.modelValue
  } else {
    phoneNumber.value = ''
  }
}

// Inizializzazione immediata
initializePhoneNumber()

// Debounce per evitare troppe chiamate API
let debounceTimer = null

// Funzione per controllare la disponibilità del numero
const checkAvailability = async (number) => {
  return;
  if (!number || number.length < 6) {
    internalErrorMessage.value = ''
    emit('onCheckUpdate', { available: true, message: '' })
    return
  }

  try {
    const params = {}
    
    if (props.type === 'mobile') {
      params.telefono = number
    } else {
      params.telefono = number
    }
    if(params.mobile) {
      params.telefono = params.telefono
      delete params.mobile
    }


    const response = await $api('/customers-search-by-phone-email-tax-iva', {
      method: 'GET',
      params: params
    })
    
    let foundUsers = []
    if (response && response.users && Array.isArray(response.users)) {
        foundUsers = response.users
    } else if (Array.isArray(response)) {
        foundUsers = response
    } else if (response && response.data && Array.isArray(response.data)) {
        foundUsers = response.data
    } else if (response && response.customers && Array.isArray(response.customers)) {
        foundUsers = response.customers
    }

    if (props.customerId && foundUsers.length > 0) {
        foundUsers = foundUsers.filter(u => u.id != props.customerId)
    }

    if (foundUsers.length > 0) {
        const msg = 'Questo numero è già registrato'
        internalErrorMessage.value = msg
        emit('onCheckUpdate', { available: false, message: msg, users: foundUsers })
    } else {
        internalErrorMessage.value = ''
        emit('onCheckUpdate', { available: true, message: '' })
    }
    
  } catch (error) {
    internalErrorMessage.value = 'Errore durante la verifica'
    emit('onCheckUpdate', { available: false, message: 'Errore durante la verifica' })
  }
}

// Gestione cambio telefono
const handlePhoneChange = (phone) => {
  
  // Aggiungi il + se non presente
  let formattedPhone = phone
  if (phone && !phone.startsWith('+')) {
    formattedPhone = '+' + phone
  }
  
  phoneNumber.value = formattedPhone
  
  // Emetti il valore formattato
  emit('update:modelValue', formattedPhone)
  emit('onValue', formattedPhone)
  
  // Debounce per il controllo disponibilità
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }
  
  debounceTimer = setTimeout(() => {
    if (phone) {
      let checkNumber = phone
      if (!phone.startsWith('+')) {
        checkNumber = '+' + phone
      }
      checkAvailability(checkNumber)
    } else {
      internalErrorMessage.value = ''
      emit('onCheckUpdate', { available: true, message: '' })
    }
  }, 500)
}

</script>

<style lang="scss" scoped>
.custom-error-message {
  background-color: #FFEBEE;
  color: #D32F2F;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 0.8125rem;
  border: 1px solid #FFCDD2;
  line-height: 1.2;
}

// Stili globali per il componente interno (se esposti)
.baseinput-core {
  height: 38px !important;
}

.baseinput-label {
  margin: 3px !important;
  font-weight: normal !important;
}

// Override FORZATO per tema scuro usando il wrapper
.customer-phone-input-wrapper {
  // Targetta qualsiasi input dentro il wrapper
  :deep(input) {
    color: rgb(var(--v-theme-on-surface)) !important;
    background-color: transparent !important;
    
    &::placeholder {
      color: rgba(var(--v-theme-on-surface), 0.38) !important;
    }
  }

  // Targetta classi comuni della libreria (e varianti)
  :deep(.baseinput-core),
  :deep(.phone-input),
  :deep(.input-tel__input),
  :deep(.country-selector__toggle),
  :deep(.country-selector__label) {
    color: rgb(var(--v-theme-on-surface)) !important;
    background-color: rgb(var(--v-theme-surface)) !important;
    border-color: rgba(var(--v-theme-on-surface), 0.38) !important;
  }

  // Correzione specifica per la label se presente
  :deep(.baseinput-label) {
    color: rgba(var(--v-theme-on-surface), 0.8) !important;
    background-color: rgb(var(--v-theme-surface)) !important;
  }

  // Gestione lista paesi (spesso è un elemento separato o portale, ma proviamo qui)
  :deep(.country-list),
  :deep(.dots-text-div) {
    background-color: rgb(var(--v-theme-surface)) !important;
    color: rgb(var(--v-theme-on-surface)) !important;
    border: 1px solid rgba(var(--v-theme-on-surface), 0.12) !important;
  }

  :deep(.country-item) {
     background-color: transparent !important;
     color: rgb(var(--v-theme-on-surface)) !important;
     
     &:hover, &.selected {
       background-color: rgba(var(--v-theme-primary), 0.1) !important;
     }
     
     strong {
       color: rgb(var(--v-theme-on-surface)) !important;
       font-weight: 600 !important;
     }
  }
}
</style>
