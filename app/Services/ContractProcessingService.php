<?php

namespace App\Services;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Spatie\PdfToImage\Pdf;
use Illuminate\Support\Facades\Storage;
use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Paperwork;
use App\Models\AIPaperwork;

class ContractProcessingService
{
    public function processContract(AIPaperwork $aiPaperwork)
    {
        try {
            $localPath = storage_path('app/temp/' . basename($aiPaperwork->filepath));
            // Create directory if it doesn't exist
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $aiPaperwork->status = 1;
            $aiPaperwork->save();
            
            // Get contents and put file
            $contents = Storage::disk('do')->get($aiPaperwork->filepath);
            file_put_contents($localPath, $contents);
            
            // Convert PDF and extract text
            $extractedText = $this->extractTextFromPdf($localPath, $aiPaperwork->id);
            
            // Update the AI Paperwork with extracted text
            $aiPaperwork->extracted_text = implode("\n", $extractedText);
            
            // Generate and store prompt
            $prompt = $this->getPrompt($aiPaperwork->extracted_text);
            $aiPaperwork->prompt_input = $prompt;
            
            // Get AI response
            $client = new Client(config('services.gemini.api_key'));
            $response = $client->generativeModel(ModelName::GEMINI_1_5_FLASH)->generateContent(
                new TextPart($prompt),
            );
            
            $aiPaperwork->prompt_output = $response->text();
            
            // Extract structured data
            [$customer, $contract] = $this->extractAIData($aiPaperwork->prompt_output);


            // Check if customer exists
            if ($customer) {
                $customerDb = $this->makeCustomerFromAIData($customer);
            }

            if ($contract) {
                $paperwork = $this->makePaperworkFromAIData($customerDb, $contract);
            }

            if (isset($customerDb)) {
                $aiPaperwork->ai_extracted_customer = json_encode($customerDb);
            }
            if (isset($paperwork)) {
                $aiPaperwork->ai_extracted_paperwork = json_encode($paperwork);
            }
            $aiPaperwork->status = 2; // Processed
            $aiPaperwork->save();

            return $aiPaperwork;

        } catch (\Exception $e) {
            $aiPaperwork->status = 9; // Error
            $aiPaperwork->save();
            throw $e;
        }
    }

    private function extractTextFromPdf($localPath, $id)
    {
        $pdf = new Pdf($localPath);
        $pages = $pdf->pageCount();
        
        $imageAnnotator = new ImageAnnotatorClient([
            'credentials' => [
                'type' => 'service_account',
                'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
                'private_key_id' => env('GOOGLE_CLOUD_PRIVATE_KEY_ID'),
                'private_key' => str_replace('\n', "\n", env('GOOGLE_CLOUD_PRIVATE_KEY')),
                'client_email' => env('GOOGLE_CLOUD_CLIENT_EMAIL'),
                'client_id' => env('GOOGLE_CLOUD_CLIENT_ID'),
                'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
                'token_uri' => 'https://oauth2.googleapis.com/token',
                'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
                'client_x509_cert_url' => env('GOOGLE_CLOUD_CLIENT_X509_CERT_URL'),
            ]
        ]);

        $allExtractedText = [];

        for ($pageNumber = 1; $pageNumber <= $pages; $pageNumber++) {
            $imagePath = Storage::disk('local')->path("temp/{$id}-page-{$pageNumber}.jpg");
            $pdf->selectPage($pageNumber)->save($imagePath);

            $image = file_get_contents($imagePath);
            $response = $imageAnnotator->documentTextDetection($image);
            $annotation = $response->getFullTextAnnotation();

            if ($annotation) {
                $allExtractedText[] = $annotation->getText();
            }

            unlink($imagePath);
        }

        $imageAnnotator->close();
        
        return $allExtractedText;
    }

    private function getPrompt($extractedText)
    {
        return <<<EOF
        Dato il seguente testo estratto da un PDF relativo a una proposta di contratto, che include anche documenti del contraente e bollette precedenti, si prega di estrarre i seguenti dati formattati nei JSON indicati.
        Testo Estratto:
        <testo>{$extractedText}</testo>

        Dati da Estrarre:
        1. Cliente:
        - Nome
        - Cognome
        - Email
        - Telefono (se diverso dal mobile)
        - Mobile
        - Ragione sociale (se applicabile)
        - Codice fiscale
        - Partita IVA (se applicabile)
        - Indirizzo completo (via/street, numero civico, CAP, città, provincia, regione)
        2. Contratto:
        - Tipo di fornitura (ad esempio Energia elettrica)
        - Prodotto offerta specificato nel contratto (ad esempio DYNAMIC LUCE)
        - POD o PDR menzionato nel documento
        - Tipo di contratto stipulato o proposto (ad esempio Allaccio/Switch)
        - Consumo annuo previsto o attuale in kWh - potrebbe essere allegata una bolletta precedente, estrai il consumo
        - Fornitore precedente del servizio - potrebbe essere allegata una bolletta del fornitore precedente, estrai il nome delfornitore

        Formato dei Dati Estratti in JSON:
        " . '
        <cliente>
        {
            "nome": "Nome del Cliente",
            "cognome": "Cognome del Cliente",
            "email": "email.cliente@example.com",
            "telefono": "+39 0123456789",
            "mobile": "+39 3333333333",
            "ragione_sociale": "",
            "codice_fiscale": "",
            "partita_iva": "",
            "indirizzo": "",
            "cap": "",
            "citta": "",
            "provincia": "",
            "regione": ""
        }
        </cliente>

        <contratto>
        {
            "fornitura": "Energia elettrica",
            "brand": "Edison",
            "prodotto_offerta": "DYNAMIC LUCE",
            "pod_pdr": "IT001E12399987",
            "consumo_annuo": null, 
            "tipo_contratto": "ALLACCIO|SUBENTRO|OTP|VOLTURA|SWITCH|NUOVA LINEA|PORTABILITÀ",
            "tipo_fornitura": "ENERGIA|TELEFONIA",
            "tipo_fornitura_mobile": "MOBILE|FISSO|FISSO_MOBILE",
            "tipo_fornitura_energia": "LUCE|GAS",
            "mandato_sepa_iban": "IT60X0542811101000000123456",
            "fornitore_precedente": null
        }
        </contratto>
            
        Si prega di fornire i dati completi ed eventualmente annotare se alcune informazioni non sono presenti nel testo.
        EOF;
    }

    public function extractAIData($text)
    {
        $customer = [];
        $contract = [];
        preg_match('/<cliente>(.*?)<\/cliente>/s', $text, $customerMatches);
        preg_match('/<contratto>(.*?)<\/contratto>/s', $text, $contractMatches);

        if (isset($customerMatches[1])) {
            $customer = json_decode($customerMatches[1], true);
        }

        if (isset($contractMatches[1])) {
            $contract = json_decode($contractMatches[1], true);
        }

        return [$customer, $contract];
    }

    public function makeCustomerFromAIData($customer)
    {
        $customerDb = null;
        if (isset($customer['email']) && $customer['email']) {
            $customerDb = Customer::where('email', $customer['email'])->first();
        }
        if (!$customerDb) {
            $customerDb = new Customer();
            $customerDb->name = $customer['nome'];
            $customerDb->last_name = $customer['cognome'];
            $customerDb->business_name = $customer['ragione_sociale'];
            $customerDb->email = $customer['email'];
            $customerDb->phone = $customer['telefono'];
            $customerDb->mobile = $customer['mobile'];
            $customerDb->address = $customer['indirizzo'];
            $customerDb->city = $customer['citta'];
            $customerDb->province = $customer['provincia'];
            $customerDb->region = $customer['regione'];
            $customerDb->zip_code = $customer['cap'];
            $customerDb->vat_number = $customer['partita_iva'];
            $customerDb->tax_id_code = $customer['codice_fiscale'];
        }

        return $customerDb;
    }

    public function makePaperworkFromAIData($customerDb, $contract)
    {
        $paperwork = new Paperwork();
        if (isset($customerDb)) {
            $paperwork->customer_id = $customerDb->id;
        }
        // FILL THE MANDATE_ID AND MANAGER_ID IF NEEDED
        // Try to find the product
        $offerta = trim(strtoupper($contract['prodotto_offerta'] ?? ''));
        $brand = trim(strtoupper($contract['brand'] ?? ''));
        $offerta = trim(str_replace($brand, '', $offerta));

        $productName = $brand . ' ' . $offerta;
        $matchingProducts = Product::where('name', 'like', '%' . $productName . '%')->get();
        if ($matchingProducts->count() > 0) {
            if (! $contract['mandato_sepa_iban']) {
                // Get the product that has in the name the words "NO SDD"
                $product = $matchingProducts->where('name', 'like', '%NO SDD%')->first();
            } else {
                $product = $matchingProducts->first();
            }
            $paperwork->product_id = $product->id;

            $contract['product'] = $product;
        }
        $paperwork->account_pod_pdr = $contract['pod_pdr'];
        $paperwork->annual_consumption = $contract['consumo_annuo'];
        $paperwork->category = strtoupper($contract['tipo_contratto']);
        $paperwork->type = strtoupper($contract['tipo_fornitura']);

        if (isset($customerDb)) {
            if ($customerDb->vat_number) {
                $paperwork->contract_type = 'BUSINESS';
            } else {
                $paperwork->contract_type = 'RESIDENZIALE';
            }
        }
        $paperwork->energy_type = $contract['tipo_fornitura_energia'] ?? null;
        $paperwork->mobile_type = $contract['tipo_fornitura_mobile'] ?? null;
        $paperwork->previous_provider = $contract['fornitore_precedente'] ?? null;

        return $paperwork;
    }
} 
