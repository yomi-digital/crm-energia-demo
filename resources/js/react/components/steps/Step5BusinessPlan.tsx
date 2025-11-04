import React, { useMemo } from 'react';
import type { StepProps } from '../../types';

const Step5BusinessPlan: React.FC<StepProps> = ({ formData, updateFormData }) => {
  const annualSavings = useMemo(() => {
    if (!formData.billData || formData.billData.length === 0) return 0;
    const totals = formData.billData.reduce((acc, month) => {
        acc.f1 += month.f1;
        acc.f2 += month.f2;
        acc.f3 += month.f3;
        return acc;
    }, { f1: 0, f2: 0, f3: 0 });
    const daytimeConsumption = totals.f1 * 0.83 + totals.f2 * 0.26 + totals.f3 * 0.17;
    return daytimeConsumption * formData.costPerKwh;
  }, [formData.billData, formData.costPerKwh]);

  const businessPlanData = useMemo(() => {
    const data = [];
    let cumulativeCashFlow = 0;

    const annualLoanPayment = formData.paymentMethod === 'Finanziamento' ? formData.installmentAmount * 12 : 0;
    const loanYears = formData.paymentMethod === 'Finanziamento' ? Math.ceil(formData.installments / 12) : 0;
    
    // Initial investment (placeholder)
    const initialInvestment = -18000;

    for (let year = 0; year <= 20; year++) {
        const loanPayment = year > 0 && year <= loanYears ? annualLoanPayment : 0;
        const insuranceCost = year > 0 && formData.insurance.enabled ? formData.insurance.cost : 0;
        const maintenanceCost = year > 0 && formData.maintenance.enabled ? formData.maintenance.cost : 0;
        
        // Simulate energy price inflation (e.g., 2% per year)
        const currentAnnualSavings = annualSavings * Math.pow(1.02, year - 1);
        const energySale = (currentAnnualSavings * 0.15); // Simulate selling 15% of savings value
        const cer80 = 800; // Placeholder fixed value
        const fpPnrr = year === 1 ? 1000 : 0; // One-time bonus in year 1

        let cashFlow = 0;
        if (year === 0) {
            cashFlow = initialInvestment;
        } else {
            const cashIn = currentAnnualSavings + energySale + cer80 + fpPnrr;
            const cashOut = loanPayment + insuranceCost + maintenanceCost;
            cashFlow = cashIn - cashOut;
        }

        cumulativeCashFlow += cashFlow;
        
        data.push({
            year,
            loanPayment,
            insuranceCost,
            maintenanceCost,
            annualSavings: year > 0 ? currentAnnualSavings : 0,
            energySale: year > 0 ? energySale : 0,
            cer80: year > 0 ? cer80 : 0,
            fpPnrr,
            cashFlow,
            cumulativeCashFlow,
        });
    }
    return data;
  }, [formData, annualSavings]);

  const formatCurrency = (value: number) => value.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', minimumFractionDigits: 0, maximumFractionDigits: 0 });

  return (
    <div>
      <h2 className="text-xl font-semibold text-gray-800 mb-2">Business Plan (Opzionale)</h2>
      <p className="text-sm text-gray-500 mb-6">Scegli se desideri generare e includere il business plan ventennale nel preventivo finale.</p>
      
      <fieldset className="space-y-2">
        <legend className="sr-only">Opzioni Business Plan</legend>
        <div 
          onClick={() => updateFormData('generateBusinessPlan', true)}
          className={`relative flex items-center p-4 border rounded-lg cursor-pointer ${formData.generateBusinessPlan ? 'bg-blue-50 border-blue-500 ring-2 ring-blue-500' : 'border-gray-300'}`}
        >
          <input type="radio" name="business-plan" className="h-4 w-4 accent-blue-600 border-gray-300 focus:ring-blue-500" checked={formData.generateBusinessPlan} readOnly/>
          <label className="ml-3 block text-sm font-medium text-gray-800">
            Sì, genera il Business Plan
            <span className="block text-xs text-gray-500">Verrà mostrata una tabella con la proiezione a 20 anni dei costi e dei benefici.</span>
          </label>
        </div>
        <div 
          onClick={() => updateFormData('generateBusinessPlan', false)}
          className={`relative flex items-center p-4 border rounded-lg cursor-pointer ${!formData.generateBusinessPlan ? 'bg-blue-50 border-blue-500 ring-2 ring-blue-500' : 'border-gray-300'}`}
        >
          <input type="radio" name="business-plan" className="h-4 w-4 accent-blue-600 border-gray-300 focus:ring-blue-500" checked={!formData.generateBusinessPlan} readOnly/>
          <label className="ml-3 block text-sm font-medium text-gray-800">
            No, non generare il Business Plan
             <span className="block text-xs text-gray-500">Si procederà direttamente alla generazione del preventivo standard.</span>
          </label>
        </div>
      </fieldset>

      {formData.generateBusinessPlan && (
        <div className="mt-6">
          <h3 className="text-md font-semibold text-gray-700 mb-2">Anteprima Business Plan</h3>
            <div className="border rounded-lg overflow-auto max-h-80 relative">
                <table className="w-full min-w-[1200px] text-sm text-left">
                    <thead className="text-xs text-gray-700 uppercase bg-gray-100 sticky top-0 z-10">
                        <tr>
                            <th className="px-3 py-2">Anno</th>
                            <th className="px-3 py-2 text-right">Rata mutuo</th>
                            <th className="px-3 py-2 text-right">Costo assicuraz.</th>
                            <th className="px-3 py-2 text-right">Costo manutenzione</th>
                            <th className="px-3 py-2 text-right">Risparmio bolletta</th>
                            <th className="px-3 py-2 text-right">Vendita energia</th>
                            <th className="px-3 py-2 text-right">CER 80%</th>
                            <th className="px-3 py-2 text-right">F.P. PNRR</th>
                            <th className="px-3 py-2 text-right font-bold">Flussi di cassa</th>
                            <th className="px-3 py-2 text-right font-bold">Flussi cumulati</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white">
                        {businessPlanData.map((row) => (
                            <tr key={row.year} className="border-b hover:bg-gray-50 text-gray-800">
                                <td className="px-3 py-2 font-medium text-center">{row.year}</td>
                                <td className="px-3 py-2 text-right">{formatCurrency(row.loanPayment)}</td>
                                <td className="px-3 py-2 text-right">{formatCurrency(row.insuranceCost)}</td>
                                <td className="px-3 py-2 text-right">{formatCurrency(row.maintenanceCost)}</td>
                                <td className="px-3 py-2 text-right text-green-600">{formatCurrency(row.annualSavings)}</td>
                                <td className="px-3 py-2 text-right text-green-600">{formatCurrency(row.energySale)}</td>
                                <td className="px-3 py-2 text-right text-green-600">{formatCurrency(row.cer80)}</td>
                                <td className="px-3 py-2 text-right text-green-600">{formatCurrency(row.fpPnrr)}</td>
                                <td className={`px-3 py-2 text-right font-bold ${row.cashFlow >= 0 ? 'text-green-700' : 'text-red-600'}`}>{formatCurrency(row.cashFlow)}</td>
                                <td className={`px-3 py-2 text-right font-bold ${row.cumulativeCashFlow >= 0 ? 'text-gray-800' : 'text-red-700'}`}>{formatCurrency(row.cumulativeCashFlow)}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
      )}
    </div>
  );
};

export default Step5BusinessPlan;