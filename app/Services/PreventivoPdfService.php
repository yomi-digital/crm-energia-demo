<?php

namespace App\Services;

use App\Models\Preventivo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PreventivoPdfService
{
    /**
     * Array per tracciare i file temporanei da pulire dopo la generazione del PDF
     */
    private array $tempFiles = [];

    /**
     * Genera un'immagine PNG del grafico a torta donut per i consumi
     *
     * @param float $percentualeF1 Percentuale F1 (0-100)
     * @param float $percentualeF2 Percentuale F2 (0-100)
     * @param float $percentualeF3 Percentuale F3 (0-100)
     * @return string Percorso dell'immagine generata
     */
    public function generateDonutChartImage(float $percentualeF1, float $percentualeF2, float $percentualeF3): string
    {
        // Aumentiamo la risoluzione per evitare sgranature (2x)
        $scale = 2;
        $width = 250 * $scale;
        $height = 250 * $scale;
        $centerX = $width / 2;
        $centerY = $height / 2;
        $radius = 100 * $scale;
        $innerRadius = 60 * $scale;
        
        // Crea l'immagine
        $image = imagecreatetruecolor($width, $height);
        
        // Colori
        $white = imagecolorallocate($image, 255, 255, 255);
        $red = imagecolorallocate($image, 231, 76, 60); // #e74c3c
        $green = imagecolorallocate($image, 75, 174, 102); // #4BAE66
        $blue = imagecolorallocate($image, 26, 35, 59); // #1A233B
        
        // Sfondo bianco
        imagefill($image, 0, 0, $white);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        
        // Calcola angoli
        $angoloF1 = ($percentualeF1 / 100) * 360;
        $angoloF2 = ($percentualeF2 / 100) * 360;
        $angoloF3 = ($percentualeF3 / 100) * 360;
        
        // Angoli di inizio (partendo da -90 gradi = alto)
        $startF1 = -90;
        $endF1 = $startF1 + $angoloF1;
        $startF2 = $endF1;
        $endF2 = $startF2 + $angoloF2;
        $startF3 = $endF2;
        
        // Disegna F1 (rosso) - con piccolo overlap per evitare gap
        $this->drawPieSegment($image, $centerX, $centerY, $radius, $innerRadius, $startF1, $endF1 + 0.1, $red);
        
        // Disegna F2 (verde) - con piccolo overlap per evitare gap
        $this->drawPieSegment($image, $centerX, $centerY, $radius, $innerRadius, $startF2 - 0.1, $endF2 + 0.1, $green);
        
        // Disegna F3 (blu) - con piccolo overlap per evitare gap
        $this->drawPieSegment($image, $centerX, $centerY, $radius, $innerRadius, $startF3 - 0.1, $startF3 + $angoloF3, $blue);
        
        // Aggiungi testo delle percentuali al centro (scalato)
        $textColor = imagecolorallocate($image, 26, 35, 59); // #1A233B
        $textColorGray = imagecolorallocate($image, 102, 102, 102); // #666
        
        // Prova a usare un font TrueType se disponibile, altrimenti usa font built-in più grande
        $fontPath = null;
        $possibleFontPaths = [
            resource_path('fonts/arial.ttf'),
            resource_path('fonts/DejaVuSans.ttf'),
            public_path('fonts/arial.ttf'),
            public_path('fonts/DejaVuSans.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf', // Linux
            'C:/Windows/Fonts/arial.ttf', // Windows
        ];
        
        foreach ($possibleFontPaths as $path) {
            if (file_exists($path)) {
                $fontPath = $path;
                break;
            }
        }
        
        // Testo principale (F1) - font più grande
        $text1 = number_format($percentualeF1, 1) . '%';
        $fontSize1 = 20 * $scale; // Dimensione font scalata (ridotta)
        
        if ($fontPath && function_exists('imagettftext')) {
            // Usa font TrueType per testo più grande e migliore qualità
            $bbox1 = imagettfbbox($fontSize1, 0, $fontPath, $text1);
            $text1Width = abs($bbox1[4] - $bbox1[0]);
            $text1Height = abs($bbox1[5] - $bbox1[1]);
            imagettftext($image, $fontSize1, 0, $centerX - $text1Width / 2, $centerY - (20 * $scale) + $text1Height / 2, $textColor, $fontPath, $text1);
        } else {
            // Fallback: usa font built-in più grande con dimensioni aumentate
            // Simula font più grande usando multipli
            $fontSize = 5; // Massimo disponibile
            $text1Width = imagefontwidth($fontSize) * strlen($text1);
            // Disegna il testo più volte per renderlo più spesso/visibile
            for ($i = -1; $i <= 1; $i++) {
                for ($j = -1; $j <= 1; $j++) {
                    imagestring($image, $fontSize, $centerX - $text1Width / 2 + $i, $centerY - (15 * $scale) + $j, $text1, $textColor);
                }
            }
        }
        
        // Testo secondario (F2 / F3)
        $text2 = number_format($percentualeF2, 1) . '% / ' . number_format($percentualeF3, 1) . '%';
        $fontSize2 = 10 * $scale; // Dimensione font scalata (ridotta)
        
        if ($fontPath && function_exists('imagettftext')) {
            // Usa font TrueType
            $bbox2 = imagettfbbox($fontSize2, 0, $fontPath, $text2);
            $text2Width = abs($bbox2[4] - $bbox2[0]);
            $text2Height = abs($bbox2[5] - $bbox2[1]);
            imagettftext($image, $fontSize2, 0, $centerX - $text2Width / 2, $centerY + (10 * $scale) + $text2Height / 2, $textColorGray, $fontPath, $text2);
        } else {
            // Fallback: usa font built-in
            $fontSize = 4;
            $text2Width = imagefontwidth($fontSize) * strlen($text2);
            imagestring($image, $fontSize, $centerX - $text2Width / 2, $centerY + (5 * $scale), $text2, $textColorGray);
        }
        
        // Salva l'immagine temporaneamente
        $tempPath = storage_path('app/temp/chart_' . uniqid() . '.png');
        $tempDir = dirname($tempPath);
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        
        imagepng($image, $tempPath, 9); // Qualità massima (0-9)
        imagedestroy($image);
        
        // Registra il file temporaneo per la pulizia successiva
        $this->tempFiles[] = $tempPath;
        
        return $tempPath;
    }
    
    /**
     * Disegna un segmento del grafico a torta donut
     */
    private function drawPieSegment($image, $centerX, $centerY, $outerRadius, $innerRadius, $startAngle, $endAngle, $color)
    {
        // Converti angoli da gradi a radianti
        $startRad = deg2rad($startAngle);
        $endRad = deg2rad($endAngle);
        
        // Aumenta il numero di step per una curva più liscia e per evitare gap
        $steps = max(50, abs($endAngle - $startAngle) * 2);
        
        // Crea un array di punti per il segmento
        $points = [];
        
        // Punto iniziale sul cerchio esterno
        $points[] = round($centerX + $outerRadius * cos($startRad));
        $points[] = round($centerY + $outerRadius * sin($startRad));
        
        // Arco esterno con più punti per maggiore precisione
        for ($i = 0; $i <= $steps; $i++) {
            $angle = $startAngle + ($endAngle - $startAngle) * ($i / $steps);
            $rad = deg2rad($angle);
            $points[] = round($centerX + $outerRadius * cos($rad));
            $points[] = round($centerY + $outerRadius * sin($rad));
        }
        
        // Punto finale sul cerchio interno
        $points[] = round($centerX + $innerRadius * cos($endRad));
        $points[] = round($centerY + $innerRadius * sin($endRad));
        
        // Arco interno (inverso) con più punti per maggiore precisione
        for ($i = $steps; $i >= 0; $i--) {
            $angle = $startAngle + ($endAngle - $startAngle) * ($i / $steps);
            $rad = deg2rad($angle);
            $points[] = round($centerX + $innerRadius * cos($rad));
            $points[] = round($centerY + $innerRadius * sin($rad));
        }
        
        // Chiudi il poligono tornando al punto iniziale
        $points[] = round($centerX + $outerRadius * cos($startRad));
        $points[] = round($centerY + $outerRadius * sin($startRad));
        
        // Disegna il poligono con antialiasing disabilitato per evitare gap
        imagefilledpolygon($image, $points, count($points) / 2, $color);
        
        // Disegna anche i bordi per garantire continuità
        imagesetthickness($image, 1);
        imageline($image, 
            round($centerX + $outerRadius * cos($startRad)), 
            round($centerY + $outerRadius * sin($startRad)),
            round($centerX + $innerRadius * cos($startRad)),
            round($centerY + $innerRadius * sin($startRad)),
            $color
        );
        imageline($image,
            round($centerX + $outerRadius * cos($endRad)),
            round($centerY + $outerRadius * sin($endRad)),
            round($centerX + $innerRadius * cos($endRad)),
            round($centerY + $innerRadius * sin($endRad)),
            $color
        );
    }
    /**
     * Genera il PDF del preventivo e lo salva su DigitalOcean Spaces
     *
     * @param Preventivo $preventivo
     * @return string URL del PDF salvato
     */
    public function generateAndSavePdf(Preventivo $preventivo): string
    {
        // Reset array file temporanei per questa generazione
        $this->tempFiles = [];
        
        // Genera il PDF dal template Blade
        // Passa anche l'istanza del servizio per permettere la pulizia dei file temporanei
        $pdf = Pdf::loadView('pdf.preventivo', [
            'preventivo' => $preventivo,
            'pdfService' => $this,
        ]);

        // Configura opzioni PDF
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable-local-file-access', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isFontSubsettingEnabled', true);

        // Genera il contenuto PDF
        $pdfContent = $pdf->output();

        // Pulisci i file temporanei dopo la generazione del PDF
        $this->cleanupTempFiles();

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
        // Reset array file temporanei per questa generazione
        $this->tempFiles = [];
        
        // Passa anche l'istanza del servizio per permettere la pulizia dei file temporanei
        $pdf = Pdf::loadView('pdf.preventivo', [
            'preventivo' => $preventivo,
            'pdfService' => $this,
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable-local-file-access', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isFontSubsettingEnabled', true);

        // Pulisci i file temporanei dopo la generazione del PDF
        // Nota: questo viene chiamato dopo che il PDF è stato generato dall'utente
        // Per sicurezza, possiamo anche pulire nel destructor o dopo l'output
        
        return $pdf;
    }
    
    /**
     * Pulisce i file temporanei generati durante la creazione del PDF
     */
    private function cleanupTempFiles(): void
    {
        foreach ($this->tempFiles as $filePath) {
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        $this->tempFiles = [];
    }
    
    /**
     * Destructor per pulire i file temporanei se non sono stati puliti manualmente
     */
    public function __destruct()
    {
        $this->cleanupTempFiles();
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

