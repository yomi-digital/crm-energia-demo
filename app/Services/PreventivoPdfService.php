<?php

namespace App\Services;

use App\Models\Preventivo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PreventivoPdfService
{
    /**
     * Genera il PDF del preventivo e lo salva su DigitalOcean Spaces
     *
     * @param Preventivo $preventivo
     * @return string URL del PDF salvato
     */
    public function generateAndSavePdf(Preventivo $preventivo): string
    {
        // Genera il PDF dal template Blade
        $pdf = Pdf::loadView('pdf.preventivo', [
            'preventivo' => $preventivo,
        ]);

        // Configura opzioni PDF
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable-local-file-access', true);

        // Genera il contenuto PDF
        $pdfContent = $pdf->output();

        // Genera percorso file con struttura: preventivi/id_agente/preventivo-[id_preventivo]
        $filename = 'preventivi/' . $preventivo->fk_agente . '/preventivo-' . $preventivo->id_preventivo . '.pdf';

        // Salva su DigitalOcean Spaces come file privato
        Storage::disk('do')->put($filename, $pdfContent, 'private');

        // Restituisci il percorso relativo (non un URL pubblico)
        // L'URL temporaneo dovrà essere generato quando necessario tramite getTemporaryUrl()
        return $filename;
    }

    /**
     * Genera solo il PDF senza salvarlo (per download diretto)
     *
     * @param Preventivo $preventivo
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generatePdf(Preventivo $preventivo)
    {
        $pdf = Pdf::loadView('pdf.preventivo', [
            'preventivo' => $preventivo,
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable-local-file-access', true);

        return $pdf;
    }

    /**
     * Genera un URL temporaneo firmato per il PDF del preventivo
     * 
     * @param Preventivo $preventivo Durata in minuti (default: 60 minuti)
     * @param int $expirationMinutes Durata in minuti del link temporaneo
     * @return string URL temporaneo firmato
     */
    public function getTemporaryUrl(Preventivo $preventivo, int $expirationMinutes = 60): string
    {
        $filename = 'preventivi/' . $preventivo->fk_agente . '/preventivo-' . $preventivo->id_preventivo . '.pdf';
        
        // Genera URL temporaneo firmato valido per X minuti
        return Storage::disk('do')->temporaryUrl($filename, now()->addMinutes($expirationMinutes));
    }

    /**
     * Genera un URL temporaneo firmato da un percorso file
     * 
     * @param string $filepath Percorso del file relativo al bucket
     * @param int $expirationMinutes Durata in minuti del link temporaneo
     * @return string URL temporaneo firmato
     */
    public function getTemporaryUrlFromPath(string $filepath, int $expirationMinutes = 60): string
    {
        return Storage::disk('do')->temporaryUrl($filepath, now()->addMinutes($expirationMinutes));
    }
}

