<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Notifications\TicketCreated;
use App\Notifications\PaperworkCreated;
use App\Models\Ticket;
use App\Models\Paperwork;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email : Email address to send test to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invia una email di test usando Resend';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("🚀 Invio email di test a: {$email}");
        
        try {
            // Test email semplice
            Mail::raw('Ciao! Questa è una email di test da Demo CRM usando Resend! 🎉', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email - Demo CRM');
            });
            
            $this->info("✅ Email di test inviata con successo!");
            $this->info("📧 Controlla la tua casella email: {$email}");
            
            // Mostra configurazione attuale
            $this->newLine();
            $this->info("📋 Configurazione attuale:");
            $this->line("   MAIL_MAILER: " . config('mail.default'));
            $this->line("   MAIL_FROM_ADDRESS: " . config('mail.from.address'));
            $this->line("   MAIL_FROM_NAME: " . config('mail.from.name'));
            
        } catch (\Exception $e) {
            $this->error("❌ Errore nell'invio email:");
            $this->error($e->getMessage());
            
            $this->newLine();
            $this->warn("🔧 Verifica:");
            $this->warn("   1. RESEND_API_KEY è configurata correttamente nel .env");
            $this->warn("   2. MAIL_FROM_ADDRESS è verificata su Resend");
            $this->warn("   3. Il dominio è verificato su Resend");
        }
    }
}
