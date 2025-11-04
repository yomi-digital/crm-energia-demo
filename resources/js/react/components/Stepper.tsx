
import React from 'react';
import type { Step } from '../types';

interface StepperProps {
  steps: Step[];
  currentStep: number;
  children: React.ReactNode;
  onStepClick: (index: number) => void;
  onNext: () => void;
  onBack: () => void;
  isNextDisabled?: boolean;
}

const SidebarItem: React.FC<{
  step: Step;
  index: number;
  isActive: boolean;
  isCompleted: boolean;
  onClick: (index: number) => void;
}> = ({ step, index, isActive, isCompleted, onClick }) => {
  const activeClasses = 'text-blue-600 font-semibold';
  const inactiveClasses = 'text-gray-500';
  const completedClasses = 'text-gray-900 font-medium';

  const iconActiveClasses = 'bg-blue-600 text-white';
  const iconInactiveClasses = 'bg-gray-200 text-gray-500';
  const iconCompletedClasses = 'bg-blue-50 text-blue-600';


  return (
    <li
      className={`flex items-center p-3 rounded-lg transition-colors duration-200 ${isActive ? 'bg-blue-50' : isCompleted ? 'hover:bg-gray-50' : 'hover:bg-gray-50'} ${isCompleted ? 'cursor-pointer' : 'cursor-default'}`}
      onClick={() => onClick(index)}
      role="button"
      aria-current={isActive ? 'step' : undefined}
    >
      <div className={`flex items-center justify-center w-10 h-10 rounded-md mr-4 ${isActive ? iconActiveClasses : isCompleted ? iconCompletedClasses : iconInactiveClasses}`}>
        {step.icon}
      </div>
      <div>
        <h3 className={`text-sm ${isActive ? activeClasses : isCompleted ? completedClasses : inactiveClasses}`}>{step.title}</h3>
        <p className={`text-xs ${isActive ? 'text-blue-500' : 'text-gray-400'}`}>{step.subtitle}</p>
      </div>
    </li>
  );
};

export const Stepper: React.FC<StepperProps> = ({
  steps,
  currentStep,
  children,
  onStepClick,
  onNext,
  onBack,
  isNextDisabled = false,
}) => {
  const isLastStep = currentStep === steps.length - 1;
  const isFirstStep = currentStep === 0;

  return (
    <div className="bg-white rounded-xl shadow-lg w-full max-w-6xl flex min-h-[650px]">
      {/* Sidebar */}
      <aside className="w-1/3 border-r border-gray-200 p-6">
        <nav aria-label="Progress">
            <ul className="space-y-2">
            {steps.map((step, index) => (
                <SidebarItem
                key={index}
                step={step}
                index={index}
                isActive={currentStep === index}
                isCompleted={currentStep > index}
                onClick={onStepClick}
                />
            ))}
            </ul>
        </nav>
      </aside>

      {/* Main Content */}
      <main className="w-2/3 p-10 flex flex-col justify-between">
        <div className="flex-grow">{children}</div>
        
        {/* Navigation */}
        <div className="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
          <button
            onClick={onBack}
            disabled={isFirstStep}
            className="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            aria-label="Previous step"
          >
            &larr; Indietro
          </button>
          <button
            onClick={onNext}
            disabled={isNextDisabled}
            className="px-8 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center shadow-sm"
            aria-label="Next step"
          >
            {isLastStep ? 'Genera Preventivo' : 'Avanti'} &rarr;
          </button>
        </div>
      </main>
    </div>
  );
};
