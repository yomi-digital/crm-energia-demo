<template>
  <div>
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
      :error-message="errorMessage"
      @phone="handlePhoneChange"
      @country="handleCountryChange"
      @phoneData="handlePhoneData"
    />
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
  customerId: {type: [String, Number],default: null}
})

const emit = defineEmits(['update:modelValue', 'onCheckUpdate', 'onValue'])

// Stato interno
const phoneNumber = ref('')
const errorMessage = ref('')

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
  if (!number || number.length < 10) {
    errorMessage.value = ''
    emit('onCheckUpdate', { available: true, message: '' })
    return
  }

  try {
    const url = `/customers/mobile/${props.type}/check/${encodeURIComponent(number)}`
    const params = {}
    
    if (props.customerId) {
      params.customerId = props.customerId
    }
    
    const response = await $api(url, {
      method: 'GET',
      params: params
    })
    
    const result = {
      available: response.available,
      message: response.message
    }
    
    errorMessage.value = response.available ? '' : response.message
    emit('onCheckUpdate', result)
    
  } catch (error) {
    errorMessage.value = 'Errore durante la verifica'
    emit('onCheckUpdate', { available: false, message: 'Errore durante la verifica' })
  }
}

// Gestione cambio telefono
const handlePhoneChange = (phone) => {
  // Se il phone è vuoto e abbiamo già un valore, non aggiornare
  if (!phone && phoneNumber.value) {
    return
  }
  
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
      errorMessage.value = ''
      emit('onCheckUpdate', { available: true, message: '' })
    }
  }, 500)
}

</script>

<style>
.error-message {
  color: #d32f2f;
  font-size: 0.75rem;
  margin-top: 0.5rem;
}

.baseinput-core {
  height: 38px !important;
}

.baseinput-label {
  margin: 3px !important;
  font-weight: normal !important;
}
</style> 
