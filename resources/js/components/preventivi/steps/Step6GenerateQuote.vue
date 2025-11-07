<template>
    <div>
      <h2 class="section-title">Generazione Preventivo</h2>
      <p class="muted" style="margin-bottom:16px;">Rivedi i dettagli finali. Cliccando su "Genera Preventivo" creerai il documento PDF da inviare al cliente.</p>
      
      <div class="card" style="padding:16px;background:#f9fafb;max-height:384px;overflow:auto;display:flex;flex-direction:column;gap:4px;">
        <SummaryItem label="Cliente" :value="formData.client" />
        <SummaryItem 
            label="Prodotto Selezionato" 
            :value="selectedProductData?.name" 
        />
        <SummaryItem 
            label="Batteria Selezionata" 
            :value="formData.selectedBatteryCapacity > 0 ? `Batteria ${formData.selectedBatteryCapacity} kWh` : 'Nessuna'" 
        />
        <SummaryItem label="Area Geografica" :value="formData.geographicArea" />
        <SummaryItem 
            label="Pagamento" 
            :value="paymentSummary" 
        />
        <SummaryItem label="Manutenzione" :value="formData.maintenance.enabled ? `Sì (${formData.maintenance.cost.toLocaleString('it-IT', { style: 'currency', currency: 'EUR' })})` : 'No'" />
        <SummaryItem label="Assicurazione" :value="formData.insurance.enabled ? `Sì (${formData.insurance.cost.toLocaleString('it-IT', { style: 'currency', currency: 'EUR' })})` : 'No'" />
        <SummaryItem label="Business Plan" :value="formData.generateBusinessPlan ? 'Incluso' : 'Non incluso'" />
        <AdjustmentListSummary title="Incentivi" :items="formData.incentives" />
        <AdjustmentListSummary title="Sconti" :items="formData.discounts" />
        <AdjustmentListSummary title="Costi Aggiuntivi" :items="formData.additionalCosts" />
      </div>
      
      <div v-if="errorMessage" class="error-message" style="margin-top:16px;padding:12px;background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;color:#991b1b;">
        {{ errorMessage }}
      </div>
      
      <div v-if="successMessage" class="success-message" style="margin-top:16px;padding:12px;background:#d1fae5;border:1px solid #86efac;border-radius:8px;color:#065f46;">
        {{ successMessage }}
      </div>
      
      <div v-if="pdfUrl" style="margin-top:16px;padding:12px;background:#dbeafe;border:1px solid #93c5fd;border-radius:8px;text-align:center;">
        <p style="margin-bottom:8px;color:#1e40af;font-weight:600;">Il PDF non si è aperto automaticamente?</p>
        <a 
          :href="pdfUrl" 
          target="_blank" 
          rel="noopener noreferrer"
          class="btn btn-primary"
          style="display:inline-block;text-decoration:none;color:white;padding:8px 16px;border-radius:6px;font-weight:600;"
        >
          Apri PDF Preventivo
        </a>
      </div>
      
      <div style="margin-top:24px;display:flex;justify-content:center;">
        <button 
          @click="handleGenerateQuote" 
          :disabled="isGenerating"
          class="btn btn-primary"
          style="min-width:200px;padding:12px 24px;font-size:16px;font-weight:600;"
        >
          <span v-if="isGenerating">Generazione in corso...</span>
          <span v-else>Genera Preventivo PDF</span>
        </button>
      </div>
      
       <p class="help-text" style="margin-top:16px;text-align:center;">
        Confermando, attesti la correttezza dei dati inseriti e procedi con la creazione dell'offerta commerciale.
      </p>
    </div>
</template>

<script setup lang="js">
import { usePreventiviApi } from '@/composables/usePreventiviApi';
import { computed, defineProps, onMounted, ref } from 'vue';
import AdjustmentListSummary from '../AdjustmentListSummary.vue';
import { PRICE_RITIRO_DEDICATO, PRODUCTS } from '../constants';
import SummaryItem from '../SummaryItem.vue';

const props = defineProps({
  formData: Object,
});

const { loadProdottiFotovoltaico, loadCoefficientiProduzione } = usePreventiviApi();
const prodottiFotovoltaico = ref([]);
const coefficientsMap = ref({});
const isGenerating = ref(false);
const errorMessage = ref('');
const successMessage = ref(''); // Messaggio di successo verde
const pdfUrl = ref(''); // URL del PDF da mostrare se non si apre automaticamente

// Recupera l'ID dell'agente/utente loggato dalla sessione
const loggedInUser = useCookie('userData')?.value;
const agentId = loggedInUser?.id || null;

onMounted(async () => {
  try {
    const prodotti = await loadProdottiFotovoltaico();
    prodottiFotovoltaico.value = Array.isArray(prodotti) ? prodotti : (prodotti?.data || []);
    
    const coefficienti = await loadCoefficientiProduzione();
    if (coefficienti && coefficienti.length > 0) {
      const map = {};
      coefficienti.forEach(coeff => {
        const esposizione = coeff.esposizione;
        const area = coeff.area_geografica;
        const valore = coeff.coefficiente_kwh_kwp;
        if (esposizione && area && valore !== undefined && valore !== null) {
          if (!map[esposizione]) {
            map[esposizione] = {};
          }
          map[esposizione][area] = valore;
        }
      });
      coefficientsMap.value = Object.keys(map).length > 0 ? map : {};
    }
  } catch (error) {
    console.error('Errore nel caricamento dati:', error);
  }
});

const selectedProductData = computed(() => {
  // Cerca prima nei prodotti caricati dall'API
  if (prodottiFotovoltaico.value.length > 0) {
    const prodotto = prodottiFotovoltaico.value.find(p => p.id_prodotto === Number(props.formData.selectedProduct));
    if (prodotto) {
      return {
        name: prodotto.codice_prodotto,
        price: prodotto.prezzo_base ? prodotto.prezzo_base : 0,
        potenzaKwp: prodotto.potenza_kwp ? prodotto.potenza_kwp / 1000 : 0,
        categoria: prodotto.categoria?.nome_categoria || 'Fotovoltaico',
      };
    }
  }
  // Fallback ai prodotti hardcoded
  return PRODUCTS.find(p => p.name === props.formData.selectedProduct);
});

const paymentSummary = computed(() => {
    const paymentMethod = props.formData.paymentMethod || '';
    const isFinanziamento = paymentMethod.toLowerCase().includes('finanziamento');
    const isMisto = paymentMethod === 'Misto';
    
    if (isMisto) {
      const misto = props.formData.paymentMisto || {};
      return `Misto (Bonifico: ${misto.bonificoAmount || 0}€, Finanziamento: ${misto.finanziamentoAmount || 0}€)`;
    }
    
    if (isFinanziamento) {
        return `Finanziamento (${props.formData.installments} rate da ${props.formData.installmentAmount.toLocaleString('it-IT', { style: 'currency', currency: 'EUR' })})`;
    }
    
    // Altrimenti assume Bonifico
    return `Bonifico (${props.formData.paymentBonifico?.primaRata || 0}% - ${props.formData.paymentBonifico?.secondaRata || 0}% - 20%)`;
});

// Calcola i dati di simulazione (stessa logica di Step4SystemSelection)
const simulationData = computed(() => {
    const systemSizeKwp = props.formData.selectedPowerKw || 0;
    if (systemSizeKwp === 0 || !props.formData.billData || props.formData.billData.length === 0 || props.formData.costPerKwh <= 0) {
        return {
            produzioneAnnua: 0,
            risparmioAutoconsumo: 0,
            venditaEccedenza: 0,
            incentivoCer: 0,
            detrazioneFiscale: 0,
        };
    }

    const coefficient = coefficientsMap.value[props.formData.roofExposure]?.[props.formData.geographicArea] || 1350;
    const annualProductionKwh = systemSizeKwp * coefficient;

    const totals = props.formData.billData.reduce((acc, month) => {
        acc.f1 += month.f1;
        acc.f2 += month.f2;
        acc.f3 += month.f3;
        return acc;
    }, { f1: 0, f2: 0, f3: 0 });

    const daytimeConsumptionKwh = totals.f1 * 0.83 + totals.f2 * 0.26 + totals.f3 * 0.17;
    const nighttimeConsumptionKwh = totals.f1 * 0.17 + totals.f2 * 0.74 + totals.f3 * 0.83;
    const batteryCapacityKwh = props.formData.selectedBatteryCapacity || 0;
    const batteryEnergyPerYear = batteryCapacityKwh * 365;
    const totalAutoconsumoKwh = daytimeConsumptionKwh + Math.min(batteryEnergyPerYear, nighttimeConsumptionKwh);
    const risparmioAutoconsumo = totalAutoconsumoKwh * props.formData.costPerKwh;

    const energiaImmessaInRete = Math.max(0, annualProductionKwh - totalAutoconsumoKwh);
    const venditaEccedenza = energiaImmessaInRete * PRICE_RITIRO_DEDICATO;

    // CALCOLO INCENTIVO CER - eccedenza × 0.108 €/kWh (solo se abilitato)
    const incentivoCer = props.formData.enableCer ? energiaImmessaInRete * 0.108 : 0;

    // Calcola costo totale sistema per detrazione fiscale
    let productPrice = 0;
    if (props.formData.selectedProduct && prodottiFotovoltaico.value.length > 0) {
        const selectedProduct = prodottiFotovoltaico.value.find(p => p.id_prodotto === Number(props.formData.selectedProduct));
        if (selectedProduct && selectedProduct.prezzo_base) {
            productPrice = selectedProduct.prezzo_base;
        }
    }
    // Il prezzo batteria è già incluso nel prezzo prodotto
    const roofTypePrice = props.formData.roofTypePrice || 0;
    
    const calculateAdjustmentAmount = (item) => {
        if (!item) return 0;
        if (item.tipo_valore === '%') {
            const baseAmount = productPrice + roofTypePrice;
            return (baseAmount * item.valore_default) / 100;
        }
        return item.amount || item.valore_default || 0;
    };
    
    const additionalCostsTotal = (props.formData.additionalCosts || []).reduce((sum, item) => sum + calculateAdjustmentAmount(item), 0);
    const discountsTotal = (props.formData.discounts || []).reduce((sum, item) => sum + calculateAdjustmentAmount(item), 0);
    const totalSystemCost = productPrice + roofTypePrice + additionalCostsTotal - discountsTotal;

    let deductionPercentage = 0;
    if (props.formData.fiscalDeductionType === 'prima_casa') {
        deductionPercentage = 50; // Percentuale per API
    } else if (props.formData.fiscalDeductionType === 'seconda_casa') {
        deductionPercentage = 36; // Percentuale per API
    }
    const detrazioneFiscaleAnnua = (totalSystemCost * deductionPercentage) / 100 / 10; // Distribuita su 10 anni

    return {
        produzioneAnnua: Math.round(annualProductionKwh),
        risparmioAutoconsumo: Math.round(risparmioAutoconsumo),
        venditaEccedenza: Math.round(venditaEccedenza),
        incentivoCer: Math.round(incentivoCer),
        detrazioneFiscaleAnnua: Math.round(detrazioneFiscaleAnnua),
        detrazioneFiscalePercentuale: deductionPercentage,
    };
});

// Formatta i dati bolletta nel formato richiesto
const formatBillData = () => {
    if (!props.formData.billData || props.formData.billData.length === 0) {
        return [];
    }

    const monthNames = ['gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 
                       'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'];
    const monthNamesMap = {
        'Gennaio': 'gennaio', 'Febbraio': 'febbraio', 'Marzo': 'marzo', 'Aprile': 'aprile',
        'Maggio': 'maggio', 'Giugno': 'giugno', 'Luglio': 'luglio', 'Agosto': 'agosto',
        'Settembre': 'settembre', 'Ottobre': 'ottobre', 'Novembre': 'novembre', 'Dicembre': 'dicembre'
    };

    const toCapitalizedMonth = (name) => {
        if (!name) return '';
        const trimmed = name.trim();
        const normalizedLower = monthNamesMap[trimmed]
            || monthNamesMap[`${trimmed.charAt(0).toUpperCase()}${trimmed.slice(1).toLowerCase()}`]
            || trimmed.toLowerCase();
        return `${normalizedLower.charAt(0).toUpperCase()}${normalizedLower.slice(1)}`;
    };
    
    return props.formData.billData.map((bill) => {
        // Estrai il nome del mese dal campo month (può essere "Gennaio" o "Gennaio-Febbraio" per bimestri)
        let monthName = bill.month;
        let periodo;
        
        if (monthName && monthName.includes('-')) {
            // Se è un bimestre, capitalizza entrambi i mesi e uniscili con spazio
            const months = monthName.split('-').map(m => toCapitalizedMonth(m));
            const year = bill.year || new Date().getFullYear();
            periodo = `${months.join(' ')} ${year}`;
        } else {
            // Se è un mese singolo, capitalizza il nome
            monthName = toCapitalizedMonth(monthName);
            const year = bill.year || new Date().getFullYear();
            periodo = `${monthName} ${year}`;
        }
        
        return {
            periodo: periodo,
            f1_kwh: bill.f1 || 0,
            f2_kwh: bill.f2 || 0,
            f3_kwh: bill.f3 || 0,
        };
    });
};

// Prepara il payload per l'API
const preparePayload = () => {
    const totals = props.formData.billData.reduce((acc, month) => {
        acc.f1 += month.f1 || 0;
        acc.f2 += month.f2 || 0;
        acc.f3 += month.f3 || 0;
        return acc;
    }, { f1: 0, f2: 0, f3: 0 });

    const daytimeConsumption = totals.f1 * 0.83 + totals.f2 * 0.26 + totals.f3 * 0.17;
    const nighttimeConsumption = totals.f1 * 0.17 + totals.f2 * 0.74 + totals.f3 * 0.83;
    const totalConsumption = totals.f1 + totals.f2 + totals.f3;
    const totalCost = totalConsumption * props.formData.costPerKwh;

    // Determina tipo bolletta
    let tipologiaBolletta = 'mensile';
    if (props.formData.billEntryMode === 'bimonthly') {
        tipologiaBolletta = 'bimestrale';
    }
    // Trova anno e mese di partenza
    const firstBill = props.formData.billData[0];
    const startYear = firstBill?.year || new Date().getFullYear();
    
    // Converte il nome del mese in numero (1-12)
    const monthNamesToNumber = {
        'Gennaio': 1, 'Febbraio': 2, 'Marzo': 3, 'Aprile': 4,
        'Maggio': 5, 'Giugno': 6, 'Luglio': 7, 'Agosto': 8,
        'Settembre': 9, 'Ottobre': 10, 'Novembre': 11, 'Dicembre': 12
    };
    let startMonth = 1;
    if (firstBill?.month) {
        const monthName = firstBill.month.includes('-') 
            ? firstBill.month.split('-')[0].trim() 
            : firstBill.month;
        startMonth = monthNamesToNumber[monthName] || 1;
    }
    

    // Formatta modalità pagamento
    const paymentMethod = props.formData.paymentMethod || '';
    let modalitaPagamentoSalvata = '';
    if (paymentMethod === 'Misto') {
        modalitaPagamentoSalvata = 'bonifico,finanziamento';
    } else if (paymentMethod.toLowerCase().includes('finanziamento')) {
        modalitaPagamentoSalvata = 'finanziamento';
    } else {
        modalitaPagamentoSalvata = 'bonifico';
    }

    // Calcola totale sistema per bonifico amount
    let productPriceForTotal = 0;
    if (props.formData.selectedProduct && prodottiFotovoltaico.value.length > 0) {
        const selectedProduct = prodottiFotovoltaico.value.find(p => p.id_prodotto === Number(props.formData.selectedProduct));
        if (selectedProduct && selectedProduct.prezzo_base) {
            productPriceForTotal = selectedProduct.prezzo_base;
        }
    }
    const roofTypePriceForTotal = props.formData.roofTypePrice || 0;
    
    const calculateAdjustmentAmountForTotal = (item) => {
        if (!item) return 0;
        if (item.tipo_valore === '%') {
            const baseAmount = productPriceForTotal + roofTypePriceForTotal;
            return (baseAmount * item.valore_default) / 100;
        }
        return item.amount || item.valore_default || 0;
    };
    
    const additionalCostsTotalForTotal = (props.formData.additionalCosts || []).reduce((sum, item) => sum + calculateAdjustmentAmountForTotal(item), 0);
    const discountsTotalForTotal = (props.formData.discounts || []).reduce((sum, item) => sum + calculateAdjustmentAmountForTotal(item), 0);
    const totalSystemCostForBonifico = productPriceForTotal + roofTypePriceForTotal + additionalCostsTotalForTotal - discountsTotalForTotal;

    // Prepara dati bonifico
    let bonificoData = null;
    if (modalitaPagamentoSalvata.includes('bonifico')) {
        if (props.formData.paymentMethod === 'Misto' && props.formData.paymentMisto) {
            const misto = props.formData.paymentMisto;
            bonificoData = {
                first_rate: misto.primaRata || 0,
                second_rate: misto.secondaRata || 0,
                third_rate: 100 - (misto.primaRata || 0) - (misto.secondaRata || 0),
                amount: misto.bonificoAmount || 0,
            };
        } else if (props.formData.paymentBonifico) {
            bonificoData = {
                first_rate: props.formData.paymentBonifico.primaRata || 0,
                second_rate: props.formData.paymentBonifico.secondaRata || 0,
                third_rate: 100 - (props.formData.paymentBonifico.primaRata || 0) - (props.formData.paymentBonifico.secondaRata || 0),
                amount: totalSystemCostForBonifico,
            };
        }
    }

    // Prepara dati finanziamento
    let finanziamentoData = null;
    if (modalitaPagamentoSalvata.includes('finanziamento')) {
        if (props.formData.paymentMethod === 'Misto' && props.formData.paymentMisto) {
            finanziamentoData = {
                rate_import: props.formData.paymentMisto.installmentAmount || 0,
                number_of_rate: props.formData.paymentMisto.installments || 0,
            };
        } else {
            finanziamentoData = {
                rate_import: props.formData.installmentAmount || 0,
                number_of_rate: props.formData.installments || 0,
            };
        }
    }

    // Prepara prodotti
    const dettagliProdotto = [];
    
    // Aggiungi prodotto fotovoltaico
    if (props.formData.selectedProduct && selectedProductData.value) {
        const prodotto = prodottiFotovoltaico.value.find(p => p.id_prodotto === Number(props.formData.selectedProduct));
        if (prodotto) {
            dettagliProdotto.push({
                nome_prodotto_salvato: prodotto.codice_prodotto,
                categoria_prodotto_salvata: prodotto.categoria?.nome_categoria || 'Fotovoltaico',
                quantita: 1,
                prezzo_unitario_salvato: prodotto.prezzo_base ? prodotto.prezzo_base : 0,
                capacita_batteria_salvata: null,
                potenza_impianto_consigliata: prodotto.potenza_kwp ? prodotto.potenza_kwp / 1000 : 0,
            });
        }
    }

    // Prepara voci economiche
    const vociEconomiche = [];
    
    // Aggiungi incentivi
    (props.formData.incentives || []).forEach(inc => {
        const valoreApplicato = inc.tipo_valore === '%' 
            ? (inc.valore_default || inc.amount || 0)
            : (inc.amount || inc.valore_default || 0);
        
        vociEconomiche.push({
            nome_voce_salvato: inc.nome_voce || inc.description || '',
            tipo_voce_salvata: 'incentivo',
            valore_applicato: valoreApplicato,
            tipo_valore_salvato: inc.tipo_valore === '%' ? '%' : '€',
            anni_durata_agevolazione_salvata: inc.anni_durata_default || 0,
            anno_inizio_salvato: inc.anno_inizio || 1,
            anno_fine_salvato: inc.anno_fine || 1,
        });
    });

    // Aggiungi sconti
    (props.formData.discounts || []).forEach(sconto => {
        const valoreApplicato = sconto.tipo_valore === '%' 
            ? (sconto.valore_default || sconto.amount || 0)
            : (sconto.amount || sconto.valore_default || 0);
        
        vociEconomiche.push({
            nome_voce_salvato: sconto.nome_voce || sconto.description || '',
            tipo_voce_salvata: 'sconto',
            valore_applicato: valoreApplicato,
            tipo_valore_salvato: sconto.tipo_valore === '%' ? '%' : '€',
            anni_durata_agevolazione_salvata: sconto.anni_durata_default || 0,
            anno_inizio_salvato: sconto.anno_inizio || 1,
            anno_fine_salvato: sconto.anno_fine || 1,
        });
    });

    // Aggiungi costi aggiuntivi
    (props.formData.additionalCosts || []).forEach(costo => {
        const valoreApplicato = costo.tipo_valore === '%' 
            ? (costo.valore_default || costo.amount || 0)
            : (costo.amount || costo.valore_default || 0);
        
        vociEconomiche.push({
            nome_voce_salvato: costo.nome_voce || costo.description || '',
            tipo_voce_salvata: 'costo',
            valore_applicato: valoreApplicato,
            tipo_valore_salvato: costo.tipo_valore === '%' ? '%' : '€',
            anni_durata_agevolazione_salvata: costo.anni_durata_default || 0,
            anno_inizio_salvato: costo.anno_inizio || 1,
            anno_fine_salvato: costo.anno_fine || 1,
        });
    });

    // Prepara business plan se richiesto
    const businessPlan = [];
    if (props.formData.generateBusinessPlan) {
        // Calcola costo totale sistema per investimento iniziale
        let productPrice = 0;
        if (props.formData.selectedProduct && prodottiFotovoltaico.value.length > 0) {
            const selectedProduct = prodottiFotovoltaico.value.find(p => p.id_prodotto === Number(props.formData.selectedProduct));
            if (selectedProduct && selectedProduct.prezzo_base) {
                productPrice = selectedProduct.prezzo_base;
            }
        }
        // Il prezzo batteria è già incluso nel prezzo prodotto
        const roofTypePrice = props.formData.roofTypePrice || 0;
        
        const calculateAdjustmentAmount = (item) => {
            if (!item) return 0;
            if (item.tipo_valore === '%') {
                const baseAmount = productPrice + roofTypePrice;
                return (baseAmount * item.valore_default) / 100;
            }
            return item.amount || item.valore_default || 0;
        };
        
        const additionalCostsTotal = (props.formData.additionalCosts || []).reduce((sum, item) => sum + calculateAdjustmentAmount(item), 0);
        const discountsTotal = (props.formData.discounts || []).reduce((sum, item) => sum + calculateAdjustmentAmount(item), 0);
        const totalSystemCost = productPrice + roofTypePrice + additionalCostsTotal - discountsTotal;
        
        const initialInvestment = -totalSystemCost;
        let cumulativeCashFlow = 0; // Inizializza a 0, sarà calcolato nell'anno 1

        // Calcola pagamento finanziamento
        const annualLoanPayment = props.formData.paymentMethod === 'Misto' && props.formData.paymentMisto
            ? (props.formData.paymentMisto.installmentAmount || 0) * 12
            : (props.formData.paymentMethod?.toLowerCase().includes('finanziamento') ? (props.formData.installmentAmount || 0) * 12 : 0);
        
        const loanYears = props.formData.paymentMethod === 'Misto' && props.formData.paymentMisto
            ? Math.ceil((props.formData.paymentMisto.installments || 0) / 12)
            : (props.formData.paymentMethod?.toLowerCase().includes('finanziamento') ? Math.ceil((props.formData.installments || 0) / 12) : 0);

        // Calcola detrazione fiscale annua (spalmata per 10 anni)
        let fiscalDeductionAnnual = 0;
        if (props.formData.fiscalDeductionType === 'prima_casa' || props.formData.fiscalDeductionType === 'seconda_casa') {
            const deductionPercentage = props.formData.fiscalDeductionType === 'prima_casa' ? 0.50 : 0.36;
            fiscalDeductionAnnual = (totalSystemCost * deductionPercentage) / 10;
        }

        for (let year = 1; year <= 20; year++) {
            const loanPayment = year <= loanYears ? annualLoanPayment : 0;
            const insuranceCost = props.formData.insurance?.enabled ? (props.formData.insurance.cost || 0) : 0;
            const maintenanceCost = props.formData.maintenance?.enabled ? (props.formData.maintenance.cost || 0) : 0;
            
            const risparmioAnnuale = simulationData.value.risparmioAutoconsumo * Math.pow(1.02, year - 1);
            const venditaEnergia = simulationData.value.venditaEccedenza * Math.pow(1.02, year - 1);
            
            // Incentivo CER - sempre lo stesso valore per 20 anni se abilitato (eccedenza × 0.108)
            const ricavoIncentivoCer = props.formData.enableCer ? simulationData.value.incentivoCer : 0;
            
            // Detrazione fiscale solo per i primi 10 anni
            const fiscalDeduction = year <= 10 ? fiscalDeductionAnnual : 0;
            
            // Incentivi PNRR (diversi dal CER) - per altri incentivi PNRR specifici
            let incentivoPnrr = 0;
            (props.formData.incentives || []).forEach(inc => {
                const annoInizio = inc.anno_inizio || 1;
                const annoFine = inc.anno_fine || 1;
                if (year >= annoInizio && year <= annoFine) {
                    incentivoPnrr += calculateAdjustmentAmount(inc);
                }
            });
            
            // Sconti: calcola solo se l'anno è compreso tra anno_inizio e anno_fine
            let sconto = 0;
            (props.formData.discounts || []).forEach(scontoItem => {
                const annoInizio = scontoItem.anno_inizio || 1;
                const annoFine = scontoItem.anno_fine || 1;
                if (year >= annoInizio && year <= annoFine) {
                    sconto += calculateAdjustmentAmount(scontoItem);
                }
            });

            let cashFlow = 0;
            if (year === 1) {
                // L'anno 1 include l'investimento iniziale negativo
                const cashIn = risparmioAnnuale + venditaEnergia + ricavoIncentivoCer + fiscalDeduction + incentivoPnrr;
                const cashOut = totalSystemCost + loanPayment + insuranceCost + maintenanceCost + sconto;
                cashFlow = cashIn - cashOut;
            } else {
                const cashIn = risparmioAnnuale + venditaEnergia + ricavoIncentivoCer + fiscalDeduction + incentivoPnrr;
                const cashOut = loanPayment + insuranceCost + maintenanceCost + sconto;
                cashFlow = cashIn - cashOut;
            }

            cumulativeCashFlow += cashFlow;

            businessPlan.push({
                anno_simulazione: year,
                costo_annuo_investimento: loanPayment,
                costo_annuo_assicurazione: insuranceCost,
                costo_annuo_manutenzione: maintenanceCost,
                ricavo_risparmio_bolletta: Math.round(risparmioAnnuale),
                ricavo_vendita_eccedenze: Math.round(venditaEnergia),
                ricavo_incentivo_cer: Math.round(ricavoIncentivoCer),
                ricavo_fondo_perduto: 0, // Placeholder per eventuali fondi perduti
                flusso_cassa_annuo: Math.round(cashFlow),
                flusso_cassa_cumulato: Math.round(cumulativeCashFlow),
                incentivo_pnnr: Math.round(incentivoPnrr),
                detrazione_fiscale: Math.round(fiscalDeduction),
                sconto: Math.round(sconto),
            });
        }
    }

    const payload = {
        PREVENTIVI: {
            fk_cliente: props.formData.client || null,
            fk_agente: agentId,
            stato: 'protocollato',
            tetto_salvato: props.formData.roofType || '',
            area_geografica_salvata: (props.formData.geographicArea || '').toLowerCase(),
            esposizione_salvata: (props.formData.roofExposure || '').toLowerCase(),
            produzione_annua_stimata: simulationData.value.produzioneAnnua,
            risparmio_autoconsumo_annuo: simulationData.value.risparmioAutoconsumo,
            vendita_eccedenze_rid_annua: simulationData.value.venditaEccedenza,
            incentivo_cer_annuo: simulationData.value.incentivoCer,
            detrazione_fiscale_annua: simulationData.value.detrazioneFiscalePercentuale,
            modalita_pagamento_salvata: modalitaPagamentoSalvata,
            bonifico_data_json: bonificoData,
            finanziamento_data_json: finanziamentoData,
            opzione_manutenzione_salvata: (props.formData.maintenance?.enabled && props.formData.maintenance.cost > 0) ? 'si' : 'no',
            costo_annuo_manutenzione_salvato: (props.formData.maintenance?.enabled && props.formData.maintenance.cost > 0) ? (props.formData.maintenance.cost || 0) : 0,
            opzione_assicurazione_salvata: (props.formData.insurance?.enabled && props.formData.insurance.cost > 0) ? 'si' : 'no',
            costo_annuo_assicurazione_salvato: (props.formData.insurance?.enabled && props.formData.insurance.cost > 0) ? (props.formData.insurance.cost || 0) : 0,
        },
        CONSUMI_PREVENTIVO: {
            anno_partenza: startYear,
            mese_partenza: startMonth,
            costo_kwh_bolletta_medio: props.formData.costPerKwh || 0,
            costo_kwh_bolletta_totale: props.formData.totalBillCost || 0,
            totale_consumo_annuo: Math.round(totalConsumption),
            totale_costi_annui: Math.round(totalCost),
            tipologia_bolletta: tipologiaBolletta,
            dettagli_consumo_json: formatBillData(),
            consumo_diurno_annuo: Math.round(daytimeConsumption),
            consumo_notturno_annuo: Math.round(nighttimeConsumption),
            capacita_batteria_consigliata: nighttimeConsumption / 365,
            potenza_impianto_consigliata: props.formData.selectedPowerKw || 0,
        },
        DETTAGLI_PRODOTTO_PREVENTIVO: dettagliProdotto,
        PREVENTIVI_VOCE_ECONOMICHE: vociEconomiche,
    };

    if (businessPlan.length > 0) {
        payload.DETTAGLIO_BUSINESS_PLAN = businessPlan;
    }

    return payload;
};

// Gestisce la generazione del preventivo
const handleGenerateQuote = async () => {
    if (isGenerating.value) return;
    
    // Validazione dati essenziali
    if (!props.formData.client) {
        errorMessage.value = 'Seleziona un cliente prima di generare il preventivo.';
        return;
    }
    
    if (!props.formData.selectedProduct || !props.formData.selectedPowerKw) {
        errorMessage.value = 'Seleziona un prodotto e una potenza prima di generare il preventivo.';
        return;
    }

    isGenerating.value = true;
    errorMessage.value = '';
    successMessage.value = ''; // Reset messaggio di successo
    pdfUrl.value = ''; // Reset URL all'inizio

    try {
        const payload = preparePayload();
        console.log('Payload da inviare:', payload);

        const response = await $api('/api/preventivi-add', {
            method: 'POST',
            body: payload,
        });

        // Gestione risposta: blob o URL
        if (response instanceof Blob) {
            // Se la risposta è un blob PDF, scaricalo
            const url = window.URL.createObjectURL(response);
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `preventivo_${new Date().toISOString().slice(0, 10)}.pdf`);
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
            successMessage.value = 'Preventivo creato con successo! Il PDF è stato scaricato.';
            pdfUrl.value = ''; // Non serve URL per blob
        } else if (response?.data?.pdf_temporary_url) {
            // Se la risposta contiene un URL temporaneo del PDF dentro data
            // Usa un link temporaneo che funziona meglio su Safari
            const url = response.data.pdf_temporary_url;
            pdfUrl.value = url; // Imposta sempre l'URL per il pulsante di fallback
            
            const link = document.createElement('a');
            link.href = url;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            successMessage.value = response?.message || 'Preventivo creato con successo! Il PDF è stato aperto in una nuova scheda.';
        } else if (response?.pdf_temporary_url || response?.pdf_url || response?.url) {
            // Fallback per altri formati di risposta
            const url = response.pdf_temporary_url || response.pdf_url || response.url;
            pdfUrl.value = url; // Imposta sempre l'URL per il pulsante di fallback
            
            const link = document.createElement('a');
            link.href = url;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            successMessage.value = 'Preventivo creato con successo! Il PDF è stato aperto in una nuova scheda.';
        } else {
            // Se la risposta contiene i dati del preventivo creato
            console.log('Preventivo creato:', response);
            successMessage.value = response?.message || 'Preventivo creato con successo!';
            pdfUrl.value = ''; // Nessun URL disponibile
        }
    } catch (error) {
        console.error('Errore nella generazione del preventivo:', error);
        errorMessage.value = error.response?.data?.message || error.message || 'Errore durante la generazione del preventivo. Riprova più tardi.';
        successMessage.value = ''; // Reset messaggio di successo in caso di errore
        pdfUrl.value = ''; // Reset URL in caso di errore
    } finally {
        isGenerating.value = false;
    }
};

</script>
