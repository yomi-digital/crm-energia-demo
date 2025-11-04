import React, { useMemo } from 'react';
import type { FormData, AdjustmentItem } from '../../types';
import { PRODUCTS, calculateBatteryPrice } from '../../constants';

interface Step6Props {
    formData: FormData;
}

const SummaryItem: React.FC<{label: string, value: React.ReactNode}> = ({ label, value }) => (
    <div className="flex justify-between items-start py-2 border-b border-gray-100">
      <span className="text-sm font-medium text-gray-600">{label}:</span>
      <span className="text-sm text-gray-800 font-semibold text-right">{value || 'Non specificato'}</span>
    </div>
);

const AdjustmentListSummary: React.FC<{title: string, items: AdjustmentItem[]}> = ({ title, items }) => {
    if (items.length === 0) return null;
    return (
        <div className="py-2 border-b border-gray-100">
             <span className="text-sm font-medium text-gray-600">{title}:</span>
             <div className="mt-1 text-right">
                {items.map((item, index) => (
                    <div key={index} className="text-sm text-gray-800">
                        <span>{item.description || 'N/D'}: </span>
                        <span className="font-semibold">{item.amount.toLocaleString('it-IT', { style: 'currency', currency: 'EUR' })}</span>
                    </div>
                ))}
            </div>
        </div>
    )
}

const Step6GenerateQuote: React.FC<Step6Props> = ({ formData }) => {
    const selectedProductData = useMemo(() => {
        return PRODUCTS.find(p => p.name === formData.selectedProduct);
    }, [formData.selectedProduct]);

    const batteryPrice = calculateBatteryPrice(formData.selectedBatteryCapacity);

  return (
    <div>
      <h2 className="text-xl font-semibold text-gray-800 mb-2">Generazione Preventivo</h2>
      <p className="text-sm text-gray-500 mb-8">Rivedi i dettagli finali. Cliccando su "Genera Preventivo" creerai il documento PDF da inviare al cliente.</p>
      
      <div className="space-y-1 rounded-lg border border-gray-200 p-6 bg-gray-50/50 max-h-96 overflow-y-auto">
        <SummaryItem label="Cliente" value={formData.client} />
        <SummaryItem 
            label="Prodotto Selezionato" 
            value={
                selectedProductData 
                ? selectedProductData.name
                : 'Non specificato'
            } 
        />
        <SummaryItem 
            label="Batteria Selezionata" 
            value={
                formData.selectedBatteryCapacity > 0
                ? `Batteria ${formData.selectedBatteryCapacity} kWh` 
                : 'Nessuna'
            } 
        />
        <SummaryItem label="Area Geografica" value={formData.geographicArea} />
        <SummaryItem 
            label="Pagamento" 
            value={
                formData.paymentMethod === 'Finanziamento' 
                ? `Finanziamento (${formData.installments} rate da ${formData.installmentAmount.toLocaleString('it-IT', { style: 'currency', currency: 'EUR' })})`
                : `Bonifico (${formData.paymentBonifico?.primaRata || 0}% - ${formData.paymentBonifico?.secondaRata || 0}% - 20%)`
            } 
        />
        <SummaryItem label="Manutenzione" value={formData.maintenance.enabled ? `Sì (${formData.maintenance.cost.toLocaleString('it-IT', { style: 'currency', currency: 'EUR' })})` : 'No'} />
        <SummaryItem label="Assicurazione" value={formData.insurance.enabled ? `Sì (${formData.insurance.cost.toLocaleString('it-IT', { style: 'currency', currency: 'EUR' })})` : 'No'} />
        <SummaryItem label="Business Plan" value={formData.generateBusinessPlan ? 'Incluso' : 'Non incluso'} />
        <AdjustmentListSummary title="Incentivi" items={formData.incentives} />
        <AdjustmentListSummary title="Sconti" items={formData.discounts} />
        <AdjustmentListSummary title="Costi Aggiuntivi" items={formData.additionalCosts} />
      </div>
      
       <p className="text-xs text-gray-500 mt-6 text-center">
        Confermando, attesti la correttezza dei dati inseriti e procedi con la creazione dell'offerta commerciale.
      </p>
    </div>
  );
};

export default Step6GenerateQuote;