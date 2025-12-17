export const STEPS = [
  { title: 'Cliente', description: 'Selezione del cliente' },
  { title: 'Dati Bolletta', description: 'Inserimento dei dati di consumo' },
  { title: 'Riepilogo Consumi', description: 'Analisi dei consumi' },
  { title: 'Configurazione', description: 'Selezione impianto e opzioni' },
  { title: 'Business Plan', description: 'Proiezione ventennale' },
  { title: 'Genera Offerta', description: 'Finalizzazione del preventivo' },
];

export const COEFFICIENTS = {
  'SUD': { 'Nord': 1400, 'Centro': 1500, 'Sud': 1600, 'Isole': 1700 },
  'SUD-EST': { 'Nord': 1350, 'Centro': 1450, 'Sud': 1550, 'Isole': 1650 },
  'SUD-OVEST': { 'Nord': 1350, 'Centro': 1450, 'Sud': 1550, 'Isole': 1650 },
  'EST': { 'Nord': 1300, 'Centro': 1400, 'Sud': 1500, 'Isole': 1600 },
  'OVEST': { 'Nord': 1300, 'Centro': 1400, 'Sud': 1500, 'Isole': 1600 },
  'NORD-EST': { 'Nord': 1250, 'Centro': 1350, 'Sud': 1450, 'Isole': 1550 },
  'NORD-OVEST': { 'Nord': 1250, 'Centro': 1350, 'Sud': 1450, 'Isole': 1550 },
};

export const SAMPLE_INCENTIVES = [
];

export const SAMPLE_DISCOUNTS = [

];

export const SAMPLE_ADDITIONAL_COSTS = [

];

export const PRODUCTS = [
  { name: 'Standard 3kWp', price: 6000 },
  { name: 'Standard 6kWp', price: 10000 },
  { name: 'Premium 3kWp', price: 8000 },
  { name: 'Premium 6kWp', price: 14000 },
];

export const BATTERY_OPTIONS_KWH = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50];
export const POWER_OPTIONS_KW = [3, 4.5, 6, 8, 10, 15, 20, 25, 30];

export const calculateBatteryPrice = (capacity) => {
  return 0;
  if (capacity <= 0) return 0;
  if (capacity <= 5) return 4000;
  if (capacity <= 10) return 7000;
  if (capacity <= 15) return 10000;
  return 12000;
};

export const PRICE_RITIRO_DEDICATO = 0.11;


