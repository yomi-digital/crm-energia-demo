<?php

namespace App\Observers;

use App\Models\PaperworkDocument;

class PaperworkDocumentObserver
{
    /**
     * Handle the PaperworkDocument "deleting" event.
     * GDPR Compliance: Elimina i file fisici collegati al documento
     */
    public function deleting(PaperworkDocument $document): void
    {
        \Log::info("GDPR: Cancellazione documento PaperworkDocument ID: {$document->id}", [
            'paperwork_id' => $document->paperwork_id,
            'file_name' => $document->file_name ?? 'N/A',
            'file_path' => $document->file_path ?? 'N/A'
        ]);

        // Elimina il file fisico se esiste
        if ($document->file_path && \Storage::exists($document->file_path)) {
            \Storage::delete($document->file_path);
            \Log::info("GDPR: File fisico eliminato: {$document->file_path}");
        }
    }

    /**
     * Handle the PaperworkDocument "deleted" event.
     */
    public function deleted(PaperworkDocument $document): void
    {
        \Log::info("GDPR: Documento PaperworkDocument {$document->id} eliminato definitivamente", [
            'paperwork_id' => $document->paperwork_id,
            'file_name' => $document->file_name ?? 'N/A'
        ]);
    }
}
