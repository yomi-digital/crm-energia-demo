import React, { useMemo } from 'react';
import type { StepProps } from '../../types';

const StatCard: React.FC<{title: string, value: string, unit: string}> = ({ title, value, unit }) => (
    <div className="bg-white p-4 rounded-lg border border-gray-200">
        <p className="text-sm text-gray-500">{title}</p>
        <p className="text-2xl font-bold text-gray-800">{value} <span className="text-lg font-medium text-gray-600">{unit}</span></p>
    </div>
);

const Step3ConsumptionSummary: React.FC<StepProps> = ({ formData }) => {
    const { billData, costPerKwh } = formData;

    const totals = useMemo(() => {
        return billData.reduce((acc, month) => {
            acc.f1 += month.f1;
            acc.f2 += month.f2;
            acc.f3 += month.f3;
            return acc;
        }, { f1: 0, f2: 0, f3: 0 });
    }, [billData]);

    const daytimeConsumption = useMemo(() => totals.f1 * 0.83 + totals.f2 * 0.26 + totals.f3 * 0.17, [totals]);
    const nighttimeConsumption = useMemo(() => totals.f1 * 0.17 + totals.f2 * 0.74 + totals.f3 * 0.83, [totals]);
    
    const totalAnnualConsumption = useMemo(() => daytimeConsumption + nighttimeConsumption, [daytimeConsumption, nighttimeConsumption]);
    const totalAnnualCost = useMemo(() => totalAnnualConsumption * (costPerKwh || 0), [totalAnnualConsumption, costPerKwh]);

    const recommendedBattery = useMemo(() => nighttimeConsumption / 365, [nighttimeConsumption]);

    return (
        <div>
            <h2 className="text-xl font-semibold text-gray-800 mb-2">Analisi Consumi e Risparmio</h2>
            <p className="text-sm text-gray-500 mb-6">Questa sezione mostra i calcoli automatici basati sui dati forniti. È puramente informativa.</p>
            
            <div className="grid grid-cols-2 gap-4 mb-6">
                <StatCard 
                    title="Totale Consumo Annuo" 
                    value={totalAnnualConsumption.toLocaleString('it-IT', { maximumFractionDigits: 0 })} 
                    unit="kWh" 
                />
                <StatCard 
                    title="Totale Costi Annui" 
                    value={totalAnnualCost.toLocaleString('it-IT', { style: 'currency', currency: 'EUR', minimumFractionDigits: 2, maximumFractionDigits: 2 })} 
                    unit="" 
                />
                <StatCard 
                    title="Consumo Diurno Annuo" 
                    value={daytimeConsumption.toLocaleString('it-IT', { maximumFractionDigits: 0 })} 
                    unit="kWh" 
                />
                <StatCard 
                    title="Consumo Notturno Annuo" 
                    value={nighttimeConsumption.toLocaleString('it-IT', { maximumFractionDigits: 0 })} 
                    unit="kWh" 
                />
                <StatCard 
                    title="Capacità Batteria Consigliata" 
                    value={recommendedBattery.toLocaleString('it-IT', { maximumFractionDigits: 1 })} 
                    unit="kWh" 
                />
            </div>
        </div>
    );
};

export default Step3ConsumptionSummary;