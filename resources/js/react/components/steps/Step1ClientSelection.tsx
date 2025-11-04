
import React from 'react';
import type { StepProps } from '../../types';

const Step1ClientSelection: React.FC<StepProps> = ({ formData, updateFormData }) => {
  return (
    <div>
      <h2 className="text-xl font-semibold text-gray-800 mb-2">Selezione Cliente</h2>
      <p className="text-sm text-gray-500 mb-6">Seleziona il cliente dal menù a tendina o creane uno nuovo per avviare la simulazione.</p>
      
      <div className="flex items-center space-x-4">
        <div className="relative flex-grow">
            <label htmlFor="client" className="text-xs text-gray-600 mb-1 block absolute -top-2 left-3 bg-white px-1">Cliente</label>
            <select
            id="client"
            value={formData.client}
            onChange={(e) => updateFormData('client', e.target.value)}
            className="block w-full px-4 py-3 text-sm text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none"
            >
            <option value="">Seleziona un cliente</option>
            <option value="Azienda Alfa S.p.A.">Azienda Alfa S.p.A.</option>
            <option value="Beta S.r.l.">Beta S.r.l.</option>
            <option value="Gamma Industries">Gamma Industries</option>
            <option value="Mario Rossi (Residenziale)">Mario Rossi (Residenziale)</option>
            </select>
            <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg className="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
            </div>
        </div>
        <button className="px-4 py-3 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 transition-colors">
            Crea Cliente
        </button>
      </div>

    </div>
  );
};

export default Step1ClientSelection;
