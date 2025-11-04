import React, { useMemo } from 'react';
import type { StepProps, AdjustmentItem, RoofExposure } from '../../types';
import { COEFFICIENTS, SAMPLE_INCENTIVES, SAMPLE_DISCOUNTS, SAMPLE_ADDITIONAL_COSTS, PRODUCTS, BATTERY_OPTIONS_KWH, POWER_OPTIONS_KW, calculateBatteryPrice, PRICE_RITIRO_DEDICATO } from '../../constants';

type ListName = 'incentives' | 'discounts' | 'additionalCosts';

const AdjustmentList: React.FC<{
    title: string;
    listName: ListName;
    items: AdjustmentItem[];
    updateFormData: (field: ListName, value: AdjustmentItem[]) => void;
}> = ({ title, listName, items, updateFormData }) => {

    const optionsMap = {
        incentives: SAMPLE_INCENTIVES,
        discounts: SAMPLE_DISCOUNTS,
        additionalCosts: SAMPLE_ADDITIONAL_COSTS,
    };

    const currentOptions = optionsMap[listName];

    const handleDescriptionChange = (index: number, newDescription: string) => {
        const newList = [...items];
        const selectedOption = currentOptions.find(opt => opt.description === newDescription);
        if (selectedOption) {
            newList[index] = { description: selectedOption.description, amount: selectedOption.amount };
            updateFormData(listName, newList);
        }
    };

    const handleAddItem = () => {
        const firstOption = currentOptions[0];
        if (firstOption) {
            updateFormData(listName, [...items, { description: firstOption.description, amount: firstOption.amount }]);
        }
    };

    const handleRemoveItem = (index: number) => {
        updateFormData(listName, items.filter((_, i) => i !== index));
    };

    return (
        <div className="p-3 border rounded-md">
            <h4 className="text-sm font-semibold text-gray-700 mb-2">{title}</h4>
            <div className="space-y-2">
                {items.map((item, index) => (
                    <div key={index} className="flex items-center space-x-2">
                        <select 
                            value={item.description} 
                            onChange={e => handleDescriptionChange(index, e.target.value)} 
                            className="w-full text-sm p-2 border rounded-md bg-white text-gray-900 appearance-none"
                        >
                           {currentOptions.map(option => (
                                <option key={option.description} value={option.description}>{option.description}</option>
                            ))}
                        </select>
                        <input 
                            type="number" 
                            placeholder="Importo (€)" 
                            value={item.amount || ''} 
                            readOnly
                            className="w-32 text-sm p-1.5 border rounded-md bg-gray-100 text-gray-500 cursor-not-allowed"
                        />
                        <button onClick={() => handleRemoveItem(index)} className="text-red-500 hover:text-red-700 p-1.5 font-bold text-lg">&times;</button>
                    </div>
                ))}
            </div>
            <button onClick={handleAddItem} className="mt-2 text-xs font-medium text-blue-600 hover:text-blue-800">+ Aggiungi {title.slice(0, -1)}</button>
        </div>
    )
}

const EarningsInfoCard: React.FC<{title: string, value: string, description: string}> = ({ title, value, description }) => (
    <div className="bg-white p-4 rounded-lg border border-gray-200 h-full">
        <p className="text-sm text-gray-500">{title}</p>
        <p className="text-2xl font-bold text-green-600">{value}</p>
        <p className="text-xs text-gray-400 mt-1">{description}</p>
    </div>
);


const Step4SystemSelection: React.FC<StepProps> = ({ formData, updateFormData }) => {
    
    const requiredSystemSize = useMemo(() => {
        const totalConsumption = formData.billData.reduce((acc, month) => acc + month.f1 + month.f2 + month.f3, 0);
        if (!totalConsumption) return 0;

        const coefficient = COEFFICIENTS[formData.roofExposure]?.[formData.geographicArea];
        
        if (!coefficient) {
            return totalConsumption / 1350;
        }

        return totalConsumption / coefficient;
    }, [formData.billData, formData.geographicArea, formData.roofExposure]);

    const simulationResults = useMemo(() => {
        const fallbackResult = {
            risparmioAutoconsumo: 0,
            venditaEccedenza: 0,
            incentivoCer: 0,
            detrazioneFiscale: 0,
        };

        // 1. System Production
        const kwpMatch = formData.selectedProduct.match(/(\d+(\.\d+)?)kWp/);
        const systemSizeKwp = kwpMatch ? parseFloat(kwpMatch[1]) : 0;
        
        if (systemSizeKwp === 0 || !formData.billData.length || formData.costPerKwh <= 0) {
            return fallbackResult;
        }
    
        const coefficient = COEFFICIENTS[formData.roofExposure]?.[formData.geographicArea] || 1350;
        const annualProductionKwh = systemSizeKwp * coefficient;
    
        // 2. Consumption Data
        const totals = formData.billData.reduce((acc, month) => {
            acc.f1 += month.f1;
            acc.f2 += month.f2;
            acc.f3 += month.f3;
            return acc;
        }, { f1: 0, f2: 0, f3: 0 });

        const daytimeConsumptionKwh = totals.f1 * 0.83 + totals.f2 * 0.26 + totals.f3 * 0.17;
        const nighttimeConsumptionKwh = totals.f1 * 0.17 + totals.f2 * 0.74 + totals.f3 * 0.83;
        const totalAnnualConsumptionKwh = daytimeConsumptionKwh + nighttimeConsumptionKwh;

        // 3. Risparmio da Autoconsumo (Formula da utente)
        const batteryCapacityKwh = formData.selectedBatteryCapacity;
        const totalAutoconsumoKwh = daytimeConsumptionKwh + (batteryCapacityKwh * 365);
        const risparmioAutoconsumo = totalAutoconsumoKwh * formData.costPerKwh;


        // 4. Vendita Eccedenza (RID) - Following user formula
        const dato1Kwh = Math.max(0, annualProductionKwh - totalAnnualConsumptionKwh);
        const dato2Kwh = Math.max(0, nighttimeConsumptionKwh - (batteryCapacityKwh * 365));
        const venditaEccedenza = (dato1Kwh + dato2Kwh) * PRICE_RITIRO_DEDICATO;

        // 5. Incentivo CER - Y * 0.108 where Y is venditaEccedenza
        const incentiveRatio = PRICE_RITIRO_DEDICATO > 0 ? 0.108 / PRICE_RITIRO_DEDICATO : 0;
        const incentivoCer = venditaEccedenza * incentiveRatio;

        // 6. Detrazione Fiscale
        const productPrice = PRODUCTS.find(p => p.name === formData.selectedProduct)?.price || 0;
        const batteryPrice = calculateBatteryPrice(formData.selectedBatteryCapacity);
        const additionalCostsTotal = formData.additionalCosts.reduce((sum, item) => sum + item.amount, 0);
        const discountsTotal = formData.discounts.reduce((sum, item) => sum + item.amount, 0);
        const totalSystemCost = productPrice + batteryPrice + additionalCostsTotal - discountsTotal;

        let deductionPercentage = 0;
        if (formData.fiscalDeductionType === 'prima_casa') {
            deductionPercentage = 0.50;
        } else if (formData.fiscalDeductionType === 'seconda_casa') {
            deductionPercentage = 0.36;
        }
        const detrazioneFiscale = (totalSystemCost * deductionPercentage) / 10;
        
        return { risparmioAutoconsumo, venditaEccedenza, incentivoCer, detrazioneFiscale };
    
    }, [formData]);
    
    const isResidential = formData.client.toLowerCase().includes('residenziale');

    const { paymentBonifico } = formData;
    const primaRata = paymentBonifico?.primaRata || 0;
    const secondaRata = paymentBonifico?.secondaRata || 0;

    const handleBonificoChange = (field: 'primaRata' | 'secondaRata', value: string) => {
        const numValue = Number(value) || 0;
        const updatedBonifico = {
            primaRata: 0,
            secondaRata: 0,
            ...formData.paymentBonifico,
            [field]: numValue < 0 ? 0 : numValue,
        };
        updateFormData('paymentBonifico', updatedBonifico);
    };
    
    const selectedProductData = useMemo(() => {
        return PRODUCTS.find(p => p.name === formData.selectedProduct);
    }, [formData.selectedProduct]);

    return (
        <div className="space-y-6 max-h-[500px] overflow-y-auto pr-2">
            <div>
                <h2 className="text-xl font-semibold text-gray-800 mb-2">Selezione Impianto e Simulazione</h2>
                <p className="text-sm text-gray-500">Configura i dettagli dell'installazione per calcolare l'impianto ideale e scegli l'offerta.</p>
            </div>

            <div className="grid grid-cols-3 gap-4">
                 <div>
                    <label htmlFor="geo-area" className="text-xs font-medium text-gray-600 mb-1 block">Area geografica</label>
                    <select id="geo-area" value={formData.geographicArea} onChange={e => updateFormData('geographicArea', e.target.value as any)} className="w-full text-sm p-2 border rounded-md bg-white text-gray-900">
                        <option>Nord</option>
                        <option>Centro</option>
                        <option>Sud</option>
                        <option>Isole</option>
                    </select>
                 </div>
                 <div>
                    <label htmlFor="roof-exp" className="text-xs font-medium text-gray-600 mb-1 block">Esposizione tetto</label>
                    <select id="roof-exp" value={formData.roofExposure} onChange={e => updateFormData('roofExposure', e.target.value as RoofExposure)} className="w-full text-sm p-2 border rounded-md bg-white text-gray-900">
                        {Object.keys(COEFFICIENTS).map(exposure => (
                            <option key={exposure} value={exposure}>
                                {exposure.split(' ').map(word => word.charAt(0) + word.slice(1).toLowerCase()).join(' ')}
                            </option>
                        ))}
                    </select>
                 </div>
                 <div>
                    <label htmlFor="roof-type" className="text-xs font-medium text-gray-600 mb-1 block">Tipologia tetto</label>
                    <select id="roof-type" value={formData.roofType} onChange={e => updateFormData('roofType', e.target.value)} className="w-full text-sm p-2 border rounded-md bg-white text-gray-900">
                        <option>A falda</option>
                        <option>Piano</option>
                        <option>Lamiera grecata</option>
                    </select>
                 </div>
            </div>

            <div className="bg-blue-50 border-l-4 border-blue-500 text-blue-800 p-4 rounded-r-lg">
                <h3 className="font-bold">Impianto Fotovoltaico Consigliato</h3>
                <p>Per pareggiare i tuoi consumi, l'impianto ideale è da: <strong className="text-lg">{requiredSystemSize.toFixed(2)} kWp</strong></p>
            </div>
            
            <div className="space-y-4">
                <div>
                    <label htmlFor="power" className="text-sm font-medium text-gray-700 mb-1 block">Potenza kWh</label>
                    <select
                        id="power"
                        value={formData.selectedPowerKw}
                        onChange={e => updateFormData('selectedPowerKw', Number(e.target.value))}
                        className="w-full text-sm p-2 border rounded-md bg-white text-gray-900"
                    >
                        <option value={0}>Seleziona potenza</option>
                        {POWER_OPTIONS_KW.map(kw => (
                            <option key={kw} value={kw}>
                                {kw} kW
                            </option>
                        ))}
                    </select>
                </div>

                <div>
                    <label htmlFor="battery" className="text-sm font-medium text-gray-700 mb-1 block">Scegli una batteria</label>
                    <select
                        id="battery"
                        value={formData.selectedBatteryCapacity}
                        onChange={e => updateFormData('selectedBatteryCapacity', Number(e.target.value))}
                        className="w-full text-sm p-2 border rounded-md bg-white text-gray-900"
                    >
                        <option value={0}>Nessuna batteria</option>
                        {BATTERY_OPTIONS_KWH.map(kwh => (
                            <option key={kwh} value={kwh}>
                                Batteria {kwh} kWh
                            </option>
                        ))}
                    </select>
                </div>

                <div>
                    <label htmlFor="product" className="text-sm font-medium text-gray-700 mb-1 block">Selezione Prodotto/Offerta</label>
                     <select id="product" value={formData.selectedProduct} onChange={e => updateFormData('selectedProduct', e.target.value)} className="w-full text-sm p-2 border rounded-md bg-white text-gray-900">
                        <option value="">Scegli un prodotto</option>
                        {PRODUCTS.map(product => (
                          <option key={product.name} value={product.name}>
                            {product.name}
                          </option>
                        ))}
                    </select>
                </div>
            </div>

            {formData.selectedProduct && (
                <div className="space-y-4">
                    <h3 className="text-md font-semibold text-gray-700">Simulazione Guadagni Annuali Stimati</h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <EarningsInfoCard 
                            title="RISPARMIO DA AUTOCONSUMO" 
                            value={simulationResults.risparmioAutoconsumo.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 })}
                            description="Risparmio annuale generato dall'energia prodotta e consumata."
                        />
                        <EarningsInfoCard 
                            title="VENDITA ECCEDENZA (RID)" 
                            value={simulationResults.venditaEccedenza.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 })}
                            description="Guadagno annuale dalla vendita dell'energia non consumata."
                        />
                        <EarningsInfoCard 
                            title="INCENTIVO CER" 
                            value={simulationResults.incentivoCer.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 })}
                            description="Incentivo annuale per l'energia condivisa nella Comunità Energetica."
                        />
                        <EarningsInfoCard 
                            title="DETRAZIONE FISCALE" 
                            value={simulationResults.detrazioneFiscale.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 })}
                            description="Importo annuale della detrazione fiscale per 10 anni."
                        />
                    </div>
                </div>
            )}

            <div>
                <h3 className="text-md font-semibold text-gray-700 mb-3">Pagamento e Opzioni</h3>
                 <div className="space-y-3">
                    <div className="p-3 border rounded-md">
                        <span className="text-sm font-medium text-gray-700 mb-2 block">Metodo di Pagamento</span>
                        <div className="flex space-x-4">
                            <label className="flex items-center text-gray-700"><input type="radio" name="payment" value="Bonifico" checked={formData.paymentMethod === 'Bonifico'} onChange={e => updateFormData('paymentMethod', e.target.value as any)} className="mr-2 h-4 w-4 rounded border-gray-300 accent-blue-600 focus:ring-blue-500"/>Bonifico</label>
                            <label className="flex items-center text-gray-700"><input type="radio" name="payment" value="Finanziamento" checked={formData.paymentMethod === 'Finanziamento'} onChange={e => updateFormData('paymentMethod', e.target.value as any)} className="mr-2 h-4 w-4 rounded border-gray-300 accent-blue-600 focus:ring-blue-500"/>Finanziamento</label>
                        </div>
                         {formData.paymentMethod === 'Finanziamento' && (
                            <div className="grid grid-cols-2 gap-4 mt-3 pt-3 border-t">
                                <div>
                                    <label className="text-xs text-gray-600" htmlFor="installmentAmount">Importo rata (€)</label>
                                    <input type="number" id="installmentAmount" value={formData.installmentAmount || ''} onChange={e => updateFormData('installmentAmount', Number(e.target.value))} className="w-full text-sm p-1.5 border rounded-md bg-white text-gray-900"/>
                                </div>
                                <div>
                                    <label className="text-xs text-gray-600" htmlFor="installments">Numero di rate</label>
                                    <input type="number" id="installments" value={formData.installments || ''} onChange={e => updateFormData('installments', Number(e.target.value))} className="w-full text-sm p-1.5 border rounded-md bg-white text-gray-900"/>
                                </div>
                            </div>
                         )}
                         {formData.paymentMethod === 'Bonifico' && (
                            <div className="mt-3 pt-3 border-t">
                                <div className="grid grid-cols-3 gap-4">
                                    <div>
                                        <label className="text-xs text-gray-600" htmlFor="primaRata">Prima rata (%)</label>
                                        <input type="number" id="primaRata" value={primaRata || ''} onChange={e => handleBonificoChange('primaRata', e.target.value)} className="w-full text-sm p-1.5 border rounded-md bg-white text-gray-900"/>
                                    </div>
                                    <div>
                                        <label className="text-xs text-gray-600" htmlFor="secondaRata">Seconda rata (%)</label>
                                        <input type="number" id="secondaRata" value={secondaRata || ''} onChange={e => handleBonificoChange('secondaRata', e.target.value)} className="w-full text-sm p-1.5 border rounded-md bg-white text-gray-900"/>
                                    </div>
                                    <div>
                                        <label className="text-xs text-gray-600" htmlFor="terzaRata">Terza rata (%)</label>
                                        <input type="number" id="terzaRata" value="20" readOnly className="w-full text-sm p-1.5 border rounded-md bg-gray-100 text-gray-500 cursor-not-allowed"/>
                                    </div>
                                </div>
                                {primaRata + secondaRata !== 80 && (
                                    <p className="text-xs text-red-600 mt-2">La somma delle prime due rate deve essere uguale a 80%.</p>
                                )}
                            </div>
                         )}
                    </div>
                    <div className="flex items-center justify-between p-3 border rounded-md">
                        <label htmlFor="maintenance-check" className="text-sm font-medium text-gray-700 flex items-center">
                         <input type="checkbox" id="maintenance-check" checked={formData.maintenance.enabled} onChange={e => updateFormData('maintenance', {...formData.maintenance, enabled: e.target.checked})} className="h-4 w-4 rounded border-gray-300 accent-blue-600 focus:ring-blue-500 mr-2"/>
                            Manutenzione
                        </label>
                        {formData.maintenance.enabled && <input type="number" placeholder="Importo" value={formData.maintenance.cost || ''} onChange={e => updateFormData('maintenance', {...formData.maintenance, cost: Number(e.target.value)})} className="w-32 text-sm p-1.5 border rounded-md bg-white text-gray-900"/>}
                    </div>
                     <div className="flex items-center justify-between p-3 border rounded-md">
                        <label htmlFor="insurance-check" className="text-sm font-medium text-gray-700 flex items-center">
                         <input type="checkbox" id="insurance-check" checked={formData.insurance.enabled} onChange={e => updateFormData('insurance', {...formData.insurance, enabled: e.target.checked})} className="h-4 w-4 rounded border-gray-300 accent-blue-600 focus:ring-blue-500 mr-2"/>
                            Assicurazione
                        </label>
                        {formData.insurance.enabled && <input type="number" placeholder="Importo" value={formData.insurance.cost || ''} onChange={e => updateFormData('insurance', {...formData.insurance, cost: Number(e.target.value)})} className="w-32 text-sm p-1.5 border rounded-md bg-white text-gray-900"/>}
                    </div>
                </div>
            </div>

            <div>
                 <h3 className="text-md font-semibold text-gray-700 mb-3">Agevolazioni e Costi Aggiuntivi</h3>
                 <p className="text-xs text-gray-500 mb-2">Configurazione per cliente: <span className="font-semibold">{isResidential ? 'Residenziale' : 'Business'}</span></p>
                 <div className="space-y-3">
                    <div className="p-3 border rounded-md">
                        <h4 className="text-sm font-semibold text-gray-700 mb-2">Detrazione Fiscale</h4>
                        <div className="grid grid-cols-3 items-center justify-center gap-x-2 rounded-lg bg-gray-100 p-1">
                            <button
                                onClick={() => updateFormData('fiscalDeductionType', 'prima_casa')}
                                className={`w-full px-3 py-2 text-xs font-medium rounded-md transition-colors ${formData.fiscalDeductionType === 'prima_casa' ? 'bg-white text-blue-600 shadow' : 'text-gray-600 hover:bg-gray-200'}`}
                            >Prima Casa (50%)</button>
                            <button
                                onClick={() => updateFormData('fiscalDeductionType', 'seconda_casa')}
                                className={`w-full px-3 py-2 text-xs font-medium rounded-md transition-colors ${formData.fiscalDeductionType === 'seconda_casa' ? 'bg-white text-blue-600 shadow' : 'text-gray-600 hover:bg-gray-200'}`}
                            >Seconda Casa (36%)</button>
                            <button
                                onClick={() => updateFormData('fiscalDeductionType', 'nessuna')}
                                className={`w-full px-3 py-2 text-xs font-medium rounded-md transition-colors ${formData.fiscalDeductionType === 'nessuna' ? 'bg-white text-blue-600 shadow' : 'text-gray-600 hover:bg-gray-200'}`}
                            >Nessuna</button>
                        </div>
                    </div>
                    <AdjustmentList title="Incentivi" listName="incentives" items={formData.incentives} updateFormData={(field, value) => updateFormData(field, value)} />
                    <AdjustmentList title="Sconti" listName="discounts" items={formData.discounts} updateFormData={(field, value) => updateFormData(field, value)} />
                    <AdjustmentList title="Costi Aggiuntivi" listName="additionalCosts" items={formData.additionalCosts} updateFormData={(field, value) => updateFormData(field, value)} />
                 </div>
            </div>
        </div>
    );
};

export default Step4SystemSelection;