import React, { useState, useCallback } from 'react';
import type { StepProps, MonthlyBill } from '../../types';
import { ALL_MONTHS } from '../../constants';

const BIMESTER_START_MONTHS = ALL_MONTHS.filter((_, i) => i % 2 === 0);

interface Step2BillDataProps extends StepProps {
    billEntryMode: 'monthly' | 'annual' | 'bimonthly' | null;
    setBillEntryMode: (mode: 'monthly' | 'annual' | 'bimonthly' | null) => void;
}

const Step2BillData: React.FC<Step2BillDataProps> = ({ formData, updateFormData, billEntryMode, setBillEntryMode }) => {
  // State for annual input form
  const [annualData, setAnnualData] = useState({ f1: 0, f2: 0, f3: 0 });

  // State for starting period
  const [startMonth, setStartMonth] = useState(ALL_MONTHS[0]);
  const [startYear, setStartYear] = useState(new Date().getFullYear());

  const handleModeSelect = (mode: 'monthly' | 'annual' | 'bimonthly') => {
    if (mode !== billEntryMode) {
      updateFormData('billData', []); // Clear data when switching modes
      updateFormData('totalBillCost', 0);
      updateFormData('costPerKwh', 0);
      if (mode === 'bimonthly' && !BIMESTER_START_MONTHS.includes(startMonth)){
        setStartMonth(BIMESTER_START_MONTHS[0]);
      }
    }
    setBillEntryMode(mode);
  };
  
  const handleGenerateFromAnnual = useCallback(() => {
    const { f1, f2, f3 } = annualData;
    if (f1 <= 0 && f2 <= 0 && f3 <= 0) return;

    const avgF1 = f1 / 12;
    const avgF2 = f2 / 12;
    const avgF3 = f3 / 12;

    const newBillData: MonthlyBill[] = [];
    const startMonthIndex = ALL_MONTHS.indexOf(startMonth);

    for (let i = 0; i < 12; i++) {
      const monthIndex = (startMonthIndex + i) % 12;
      const currentYear = startMonthIndex + i >= 12 ? startYear + 1 : startYear;
      newBillData.push({
        month: ALL_MONTHS[monthIndex],
        year: currentYear,
        f1: Math.round(avgF1),
        f2: Math.round(avgF2),
        f3: Math.round(avgF3),
      });
    }
    updateFormData('billData', newBillData);
  }, [annualData, startMonth, startYear, updateFormData]);

  const handleAddMonth = useCallback(() => {
    if (formData.billData.length >= 12) return;

    const lastMonthData = formData.billData[formData.billData.length - 1];
    const nextMonthIndex = lastMonthData ? (ALL_MONTHS.indexOf(lastMonthData.month) + 1) % 12 : ALL_MONTHS.indexOf(startMonth);
    const nextYear = lastMonthData ? (nextMonthIndex === 0 ? lastMonthData.year + 1 : lastMonthData.year) : startYear;

    const newBillData = [...formData.billData, {
      month: ALL_MONTHS[nextMonthIndex],
      year: nextYear,
      f1: 0, f2: 0, f3: 0,
    }];
    updateFormData('billData', newBillData);
  }, [formData.billData, startMonth, startYear, updateFormData]);
  
  const handleAddBimestre = useCallback(() => {
      if (formData.billData.length >= 6) return;

      const lastBimesterData = formData.billData[formData.billData.length - 1];
      
      let nextStartMonthIndex;
      let nextYear;

      if (lastBimesterData) {
          const lastBimesterStartMonth = lastBimesterData.month.split('-')[0];
          const lastBimesterStartIndex = ALL_MONTHS.indexOf(lastBimesterStartMonth);
          nextStartMonthIndex = (lastBimesterStartIndex + 2) % 12;
          nextYear = (lastBimesterStartIndex + 2 >= 12 && lastBimesterStartIndex < 10) ? lastBimesterData.year + 1 : lastBimesterData.year;
      } else {
          nextStartMonthIndex = ALL_MONTHS.indexOf(startMonth);
          nextYear = startYear;
      }

      const bimesterLabel = `${ALL_MONTHS[nextStartMonthIndex]}-${ALL_MONTHS[(nextStartMonthIndex + 1) % 12]}`;

      const newBillData = [...formData.billData, {
        month: bimesterLabel,
        year: nextYear,
        f1: 0, f2: 0, f3: 0,
      }];
      updateFormData('billData', newBillData);
  }, [formData.billData, startMonth, startYear, updateFormData]);

  const handleRemoveMonth = (index: number) => {
    updateFormData('billData', formData.billData.filter((_, i) => i !== index));
  };

  const handleBillChange = (index: number, field: 'f1' | 'f2' | 'f3', value: string) => {
    const newBillData = [...formData.billData];
    newBillData[index] = { ...newBillData[index], [field]: Number(value) || 0 };
    updateFormData('billData', newBillData);
  };
  
  const handleAutoGenerate = () => {
        if (formData.billData.length === 0 || formData.billData.length >= 12) return;
        
        const avg = formData.billData.reduce((acc, month) => {
            acc.f1 += month.f1;
            acc.f2 += month.f2;
            acc.f3 += month.f3;
            return acc;
        }, { f1: 0, f2: 0, f3: 0 });
        
        avg.f1 /= formData.billData.length;
        avg.f2 /= formData.billData.length;
        avg.f3 /= formData.billData.length;

        const filledData = [...formData.billData];
        let lastMonthData = filledData[filledData.length - 1];

        while (filledData.length < 12) {
            const nextMonthIndex = (ALL_MONTHS.indexOf(lastMonthData.month) + 1) % 12;
            const nextYear = nextMonthIndex === 0 ? lastMonthData.year + 1 : lastMonthData.year;
            const newMonth: MonthlyBill = {
                month: ALL_MONTHS[nextMonthIndex],
                year: nextYear,
                f1: Math.round(avg.f1),
                f2: Math.round(avg.f2),
                f3: Math.round(avg.f3),
            };
            filledData.push(newMonth);
            lastMonthData = newMonth;
        }
        updateFormData('billData', filledData);
  };

  const handleAutoGenerateBimestri = useCallback(() => {
    if (formData.billData.length === 0 || formData.billData.length >= 6) return;
    
    const avg = formData.billData.reduce((acc, bimester) => {
        acc.f1 += bimester.f1;
        acc.f2 += bimester.f2;
        acc.f3 += bimester.f3;
        return acc;
    }, { f1: 0, f2: 0, f3: 0 });
    
    avg.f1 /= formData.billData.length;
    avg.f2 /= formData.billData.length;
    avg.f3 /= formData.billData.length;

    const filledData = [...formData.billData];
    let lastBimesterData = filledData[filledData.length - 1];

    while (filledData.length < 6) {
        const lastBimesterStartMonth = lastBimesterData.month.split('-')[0];
        const lastBimesterStartIndex = ALL_MONTHS.indexOf(lastBimesterStartMonth);
        const nextStartMonthIndex = (lastBimesterStartIndex + 2) % 12;
        const nextYear = (lastBimesterStartIndex + 2 >= 12 && lastBimesterStartIndex < 10) ? lastBimesterData.year + 1 : lastBimesterData.year;
        const bimesterLabel = `${ALL_MONTHS[nextStartMonthIndex]}-${ALL_MONTHS[(nextStartMonthIndex + 1) % 12]}`;

        const newBimester: MonthlyBill = {
            month: bimesterLabel,
            year: nextYear,
            f1: Math.round(avg.f1),
            f2: Math.round(avg.f2),
            f3: Math.round(avg.f3),
        };
        filledData.push(newBimester);
        lastBimesterData = newBimester;
    }
    updateFormData('billData', filledData);
  }, [formData.billData, updateFormData]);

  const handleTotalCostChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const totalCost = Number(e.target.value) || 0;
    updateFormData('totalBillCost', totalCost); // Update the input field value

    let totalKwhForPeriod = 0;
    if (billEntryMode === 'annual') {
        totalKwhForPeriod = formData.billData.reduce((sum, item) => sum + item.f1 + item.f2 + item.f3, 0);
    } else if ((billEntryMode === 'monthly' || billEntryMode === 'bimonthly') && formData.billData.length > 0) {
        const lastPeriod = formData.billData[formData.billData.length - 1];
        if (lastPeriod) {
            totalKwhForPeriod = lastPeriod.f1 + lastPeriod.f2 + lastPeriod.f3;
        }
    }

    if (totalCost > 0 && totalKwhForPeriod > 0) {
        const newCostPerKwh = totalCost / totalKwhForPeriod;
        updateFormData('costPerKwh', newCostPerKwh);
    } else {
        updateFormData('costPerKwh', 0); // Reset if cost or kWh is zero
    }
  };

  const isDataComplete = billEntryMode && (
    (billEntryMode === 'monthly' && formData.billData.length === 12) ||
    (billEntryMode === 'bimonthly' && formData.billData.length === 6) ||
    (billEntryMode === 'annual' && formData.billData.length === 12)
  );

  const costInputLabels = {
    monthly: 'Costo totale bolletta (mensile)',
    bimonthly: 'Costo totale bolletta (bimestrale)',
    annual: 'Costo totale bolletta (annuale)',
  };

  return (
    <div>
      <h2 className="text-xl font-semibold text-gray-800 mb-2">Dati Bolletta Energetica</h2>
      <p className="text-sm text-gray-500 mb-6">Indica se possiedi i dati di consumo su base mensile, bimestrale o solo il totale annuale.</p>
      
      <div className="grid grid-cols-3 items-center justify-center gap-x-2 rounded-lg bg-gray-100 p-1 mb-6">
        <button onClick={() => handleModeSelect('monthly')} className={`w-full px-4 py-2 text-sm font-medium rounded-md transition-colors ${billEntryMode === 'monthly' ? 'bg-white text-blue-600 shadow' : 'text-gray-600 hover:bg-gray-200'}`}>
          Ho dati Mensili
        </button>
        <button onClick={() => handleModeSelect('bimonthly')} className={`w-full px-4 py-2 text-sm font-medium rounded-md transition-colors ${billEntryMode === 'bimonthly' ? 'bg-white text-blue-600 shadow' : 'text-gray-600 hover:bg-gray-200'}`}>
          Ho dati Bimestrali
        </button>
        <button onClick={() => handleModeSelect('annual')} className={`w-full px-4 py-2 text-sm font-medium rounded-md transition-colors ${billEntryMode === 'annual' ? 'bg-white text-blue-600 shadow' : 'text-gray-600 hover:bg-gray-200'}`}>
          Ho dati Annuali
        </button>
      </div>

      {billEntryMode === 'annual' && (
        <div className="space-y-4 p-4 border rounded-lg bg-gray-50/50">
          <p className="text-sm text-gray-600">Inserisci i consumi totali dell'ultimo anno e il periodo di riferimento.</p>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
             <input type="number" placeholder="Consumo F1 annuale (kWh)" value={annualData.f1 || ''} onChange={e => setAnnualData({...annualData, f1: Number(e.target.value)})} className="w-full text-sm p-2 border rounded-md bg-white text-gray-900"/>
             <input type="number" placeholder="Consumo F2 annuale (kWh)" value={annualData.f2 || ''} onChange={e => setAnnualData({...annualData, f2: Number(e.target.value)})} className="w-full text-sm p-2 border rounded-md bg-white text-gray-900"/>
             <input type="number" placeholder="Consumo F3 annuale (kWh)" value={annualData.f3 || ''} onChange={e => setAnnualData({...annualData, f3: Number(e.target.value)})} className="w-full text-sm p-2 border rounded-md bg-white text-gray-900"/>
          </div>
          <div className="flex items-center space-x-4">
             <div className="flex-grow">
                 <label className="text-xs font-medium text-gray-600 block mb-1">Mese di partenza</label>
                 <select value={startMonth} onChange={e => setStartMonth(e.target.value)} className="w-full text-sm p-2 border rounded-md bg-white text-gray-900">
                    {ALL_MONTHS.map(m => <option key={m} value={m}>{m}</option>)}
                 </select>
            </div>
            <div className="flex-grow">
                 <label className="text-xs font-medium text-gray-600 block mb-1">Anno di partenza</label>
                 <select value={startYear} onChange={e => setStartYear(Number(e.target.value))} className="w-full text-sm p-2 border rounded-md bg-white text-gray-900">
                    {Array.from({length: 5}, (_, i) => new Date().getFullYear() - i).map(y => <option key={y} value={y}>{y}</option>)}
                </select>
            </div>
          </div>
          <button onClick={handleGenerateFromAnnual} className="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
            Genera Dati Mensili
          </button>
        </div>
      )}

      {(billEntryMode === 'monthly' || billEntryMode === 'bimonthly') && (
        <div className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                 <label className="text-xs font-medium text-gray-600 block mb-1">Mese di partenza</label>
                 <select value={startMonth} onChange={e => setStartMonth(e.target.value)} disabled={formData.billData.length > 0} className="w-full text-sm p-2 border rounded-md bg-white text-gray-900 disabled:bg-gray-100">
                    {billEntryMode === 'monthly' 
                      ? ALL_MONTHS.map(m => <option key={m} value={m}>{m}</option>)
                      : BIMESTER_START_MONTHS.map(m => <option key={m} value={m}>{m}</option>)
                    }
                 </select>
            </div>
            <div>
                 <label className="text-xs font-medium text-gray-600 block mb-1">Anno di partenza</label>
                 <select value={startYear} onChange={e => setStartYear(Number(e.target.value))} disabled={formData.billData.length > 0} className="w-full text-sm p-2 border rounded-md bg-white text-gray-900 disabled:bg-gray-100">
                    {Array.from({length: 5}, (_, i) => new Date().getFullYear() - i).map(y => <option key={y} value={y}>{y}</option>)}
                </select>
            </div>
          </div>
           <div className="flex space-x-2">
                {billEntryMode === 'monthly' && (
                  <>
                    <button onClick={handleAddMonth} disabled={formData.billData.length >= 12} className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">
                        Aggiungi Mese
                    </button>
                    {formData.billData.length > 0 && formData.billData.length < 12 && (
                        <button onClick={handleAutoGenerate} className="px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200">
                            Usa media per mesi restanti
                        </button>
                    )}
                  </>
                )}
                {billEntryMode === 'bimonthly' && (
                  <>
                    <button onClick={handleAddBimestre} disabled={formData.billData.length >= 6} className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">
                        Aggiungi Bimestre
                    </button>
                    {formData.billData.length > 0 && formData.billData.length < 6 && (
                        <button onClick={handleAutoGenerateBimestri} className="px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200">
                            Usa media per bimestri restanti
                        </button>
                    )}
                  </>
                )}
           </div>
        </div>
      )}

      {isDataComplete && billEntryMode && (
        <div className="mt-6 p-4 border rounded-lg bg-gray-50/50">
            <label htmlFor="totalBillCost" className="text-sm font-medium text-gray-700 block mb-1">
                {costInputLabels[billEntryMode]}
            </label>
            <div className="flex items-center space-x-4">
                <input 
                    type="number" 
                    id="totalBillCost" 
                    value={formData.totalBillCost || ''} 
                    onChange={handleTotalCostChange} 
                    step="0.01" 
                    min="0" 
                    placeholder="es. 85.50" 
                    className="block w-full max-w-xs px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md"
                />
                {formData.costPerKwh > 0 && (
                    <div className="bg-white p-2 border rounded-md">
                        <p className="text-xs text-gray-500">Costo medio calcolato</p>
                        <p className="text-sm font-bold text-gray-800">{formData.costPerKwh.toFixed(4)} €/kWh</p>
                    </div>
                )}
            </div>
            <p className="text-xs text-gray-500 mt-2">
                {billEntryMode === 'annual' 
                    ? 'Inserisci il costo totale comprensivo di tutte le voci della bolletta annuale.' 
                    : `Inserisci il costo totale dell'ultima bolletta ${billEntryMode === 'monthly' ? 'mensile' : 'bimestrale'}.`
                }
            </p>
        </div>
      )}

      {formData.billData.length > 0 && (
         <div className="mt-4 border rounded-lg overflow-hidden">
             <div className="max-h-[350px] overflow-y-auto">
                 <table className="w-full text-sm text-left text-gray-500">
                     <thead className="text-xs text-gray-700 uppercase bg-gray-100 sticky top-0">
                        <tr>
                            <th className="px-4 py-3">Periodo</th>
                            <th className="px-4 py-3 text-right">Consumo F1</th>
                            <th className="px-4 py-3 text-right">Consumo F2</th>
                            <th className="px-4 py-3 text-right">Consumo F3</th>
                            {(billEntryMode === 'monthly' || billEntryMode === 'bimonthly') && <th className="px-4 py-3 text-center">Azione</th>}
                        </tr>
                     </thead>
                     <tbody className="bg-white">
                        {formData.billData.map((item, index) => (
                            <tr key={`${item.month}-${index}`} className="border-b hover:bg-gray-50">
                                <th className="px-4 py-2 font-medium text-gray-900">{item.month} {item.year}</th>
                                <td className="px-2 py-2"><input type="number" value={item.f1 || ''} onChange={e => handleBillChange(index, 'f1', e.target.value)} readOnly={billEntryMode === 'annual'} className={`w-full text-right p-1 border rounded-md ${billEntryMode === 'annual' ? 'bg-gray-100 cursor-not-allowed' : 'bg-white text-gray-900'}`}/></td>
                                <td className="px-2 py-2"><input type="number" value={item.f2 || ''} onChange={e => handleBillChange(index, 'f2', e.target.value)} readOnly={billEntryMode === 'annual'} className={`w-full text-right p-1 border rounded-md ${billEntryMode === 'annual' ? 'bg-gray-100 cursor-not-allowed' : 'bg-white text-gray-900'}`}/></td>
                                <td className="px-2 py-2"><input type="number" value={item.f3 || ''} onChange={e => handleBillChange(index, 'f3', e.target.value)} readOnly={billEntryMode === 'annual'} className={`w-full text-right p-1 border rounded-md ${billEntryMode === 'annual' ? 'bg-gray-100 cursor-not-allowed' : 'bg-white text-gray-900'}`}/></td>
                                {(billEntryMode === 'monthly' || billEntryMode === 'bimonthly') && (
                                    <td className="px-4 py-2 text-center">
                                        <button onClick={() => handleRemoveMonth(index)} className="text-red-500 hover:text-red-700 font-bold text-lg">&times;</button>
                                    </td>
                                )}
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

export default Step2BillData;