import React, { useState, useCallback, useMemo } from 'react';
import { Stepper } from './components/Stepper';
import { STEPS } from './constants';
import type { FormData, RoofExposure } from './types';

// Import new step components
import Step1ClientSelection from './components/steps/Step1ClientSelection';
import Step2BillData from './components/steps/Step2BillData';
import Step3ConsumptionSummary from './components/steps/Step3ConsumptionSummary';
import Step4SystemSelection from './components/steps/Step4SystemSelection';
import Step5BusinessPlan from './components/steps/Step5BusinessPlan';
import Step6GenerateQuote from './components/steps/Step6GenerateQuote';

const App: React.FC = () => {
  const [currentStep, setCurrentStep] = useState<number>(0);
  const [billEntryMode, setBillEntryMode] = useState<'monthly' | 'annual' | 'bimonthly' | null>(null);
  const [formData, setFormData] = useState<FormData>({
    client: '',
    costPerKwh: 0,
    totalBillCost: 0,
    billData: [],
    geographicArea: 'Centro',
    roofExposure: 'SUD',
    roofType: 'A falda',
    selectedProduct: '',
    selectedBatteryCapacity: 0,
    selectedPowerKw: 0,
    paymentMethod: 'Bonifico',
    paymentBonifico: { primaRata: 30, secondaRata: 50 },
    installmentAmount: 0,
    installments: 120,
    maintenance: { enabled: false, cost: 0 },
    insurance: { enabled: false, cost: 0 },
    incentives: [],
    discounts: [],
    additionalCosts: [],
    generateBusinessPlan: false,
    fiscalDeductionType: 'nessuna',
  });

  const handleNext = useCallback(() => {
    setCurrentStep((prev) => Math.min(prev + 1, STEPS.length - 1));
  }, []);

  const handleBack = useCallback(() => {
    setCurrentStep((prev) => Math.max(prev - 1, 0));
  }, []);

  const handleStepClick = useCallback((index: number) => {
    // Allow navigation only to visited steps to maintain flow
    if (index < currentStep) {
        setCurrentStep(index);
    }
  }, [currentStep]);

  const updateFormData = useCallback((field: keyof FormData, value: any) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  }, []);

  const isNextDisabled = useMemo(() => {
    switch (currentStep) {
      case 0:
        return !formData.client;
      case 1:
        const isDataIncomplete = 
            (billEntryMode === 'monthly' && formData.billData.length < 12) ||
            (billEntryMode === 'bimonthly' && formData.billData.length < 6) ||
            (billEntryMode === 'annual' && formData.billData.length < 12) ||
            !billEntryMode;
        
        if (isDataIncomplete) return true;

        // If data is complete, the cost input appears. We need a value for it.
        return !formData.totalBillCost || formData.totalBillCost <= 0;
      case 3:
         let isInvalid = !formData.selectedProduct;
         if (formData.paymentMethod === 'Bonifico') {
            const { primaRata = 0, secondaRata = 0 } = formData.paymentBonifico || {};
            isInvalid = isInvalid || (primaRata + secondaRata !== 80);
         }
         return isInvalid;
      default:
        return false;
    }
  }, [currentStep, formData, billEntryMode]);


  const renderStepContent = () => {
    const props = { formData, updateFormData };
    switch (currentStep) {
      case 0:
        return <Step1ClientSelection {...props} />;
      case 1:
        return <Step2BillData {...props} billEntryMode={billEntryMode} setBillEntryMode={setBillEntryMode} />;
      case 2:
        return <Step3ConsumptionSummary {...props} />;
      case 3:
        return <Step4SystemSelection {...props} />;
      case 4:
        return <Step5BusinessPlan {...props} />;
      case 5:
        return <Step6GenerateQuote formData={formData} />;
      default:
        return null;
    }
  };

  return (
    <div className="bg-gray-100 min-h-screen flex items-center justify-center p-4 font-sans">
      <Stepper
        steps={STEPS}
        currentStep={currentStep}
        onStepClick={handleStepClick}
        onNext={handleNext}
        onBack={handleBack}
        isNextDisabled={isNextDisabled}
      >
        {renderStepContent()}
      </Stepper>
    </div>
  );
};

export default App;