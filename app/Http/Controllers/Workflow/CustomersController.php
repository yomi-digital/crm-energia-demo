<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('itemsPerPage', 10);

        $customers = new \App\Models\Customer;

        if ($request->get('id')) {
            $customers = $customers->where('id', $request->get('id'));
        }

        if ($request->filled('brand')) {
            // Need to get all customers that have a paperwork with products of the selected brand
            $customers = $customers->whereHas('paperworks', function ($query) use ($request) {
                $query->whereHas('product', function ($query) use ($request) {
                    $query->where('brand_id', $request->get('brand'));
                });
            });
        }

        if ($request->filled('city')) {
            $customers = $customers->where('city', $request->get('city'));
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $customers = $customers->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%")
                    ->orWhere('tax_id_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // If the looged in user has role 'agente', filter for only his customers
        if ($request->user()->hasRole('agente') || $request->user()->hasRole('struttura')) {
            $customers = $customers->where(function ($query) use ($request) {
                $query->whereHas('paperworks', function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                })->orWhere('added_by', $request->user()->id);
            });
        }

        if ($request->get('sortBy')) {
            $customers = $customers->orderBy($request->get('sortBy'), $request->get('orderBy', 'desc'));
        }

        if ($request->has('export')) {
            $allCustomers = $customers->get();
            $csvPath = $this->transformEntriesToCSV($allCustomers);

            // Transform csv to excel
            $data = array_map('str_getcsv', file($csvPath));

            return Excel::download(new class($data) implements FromCollection {
                private $data;
    
                public function __construct($data)
                {
                    $this->data = $data;
                }
    
                public function collection()
                {
                    return collect($this->data);
                }
            }, 'customers_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
            
            return response()->download($csvPath, 'customers_' . now()->format('Y-m-d_H-i-s') . '.csv');
        }

        if ($request->get('select') === '1') {
            $customers = $customers->select('id', 'name', 'last_name', 'business_name', 'vat_number', 'tax_id_code');
        }

        $customers = $customers->paginate($perPage);

        return response()->json([
            'customers' => $customers->getCollection(),
            'totalPages' => $customers->lastPage(),
            'totalCustomers' => $customers->total(),
            'page' => $customers->currentPage()
        ]);
    }

    public function show(Request $request, $id)
    {
        $customer = \App\Models\Customer::with(['addedByUser', 'confirmedByUser'])->whereId($id);

        if ($request->user()->hasRole('agente') || $request->user()->hasRole('struttura')) {
            $customer = $customer->where(function ($query) use ($request) {
                $query->whereHas('paperworks', function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                })->orWhere('added_by', $request->user()->id);
            });
        }

        $customer = $customer->first();

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        return response()->json($customer);
    }

    public function store(Request $request)
    {
        // Validazione incrociata di unicità telefono e cellulare
        $errors = [];
        
        // Controllo telefono fisso
        if ($request->filled('phone')) {
            $phoneNumber = $request->phone;
            
            // Controllo se il numero esiste già come telefono fisso
            $existingPhone = \App\Models\Customer::where('phone', $phoneNumber)
                ->whereNull('deleted_at')
                ->first();
                
            if ($existingPhone) {
                $errors['phone'] = 'Questo telefono fisso è già associato a un altro cliente';
            }
            
            // Controllo incrociato: se il numero esiste già come cellulare
            $existingMobile = \App\Models\Customer::where('mobile', $phoneNumber)
                ->whereNull('deleted_at')
                ->first();
                
            if ($existingMobile) {
                $errors['phone'] = 'Questo numero è già registrato come cellulare di un altro cliente';
            }
        }
        
        // Controllo cellulare
        if ($request->filled('mobile')) {
            $mobileNumber = $request->mobile;
            
            // Controllo se il numero esiste già come cellulare
            $existingMobile = \App\Models\Customer::where('mobile', $mobileNumber)
                ->whereNull('deleted_at')
                ->first();
                
            if ($existingMobile) {
                $errors['mobile'] = 'Questo cellulare è già associato a un altro cliente';
            }
            
            // Controllo incrociato: se il numero esiste già come telefono fisso
            $existingPhone = \App\Models\Customer::where('phone', $mobileNumber)
                ->whereNull('deleted_at')
                ->first();
                
            if ($existingPhone) {
                $errors['mobile'] = 'Questo numero è già registrato come telefono fisso di un altro cliente';
            }
        }
        
        // Controllo che telefono e cellulare non siano uguali
        if ($request->filled('phone') && $request->filled('mobile') && $request->phone === $request->mobile) {
            $errors['mobile'] = 'Telefono fisso e cellulare non possono essere uguali';
        }
        
        // Se ci sono errori, restituisci errore 422
        if (!empty($errors)) {
            return response()->json([
                'message' => 'I dati forniti non sono validi.',
                'errors' => $errors
            ], 422);
        }

        $customer = new \App\Models\Customer;

        $customer->fill($request->all());
        $customer->added_at = now()->format('Y-m-d');
        $customer->added_by = $request->user()->id;

        $customer->save();

        return response()->json($customer, 201);
    }

    public function update(Request $request, $id)
    {
        $customer = \App\Models\Customer::find($id);

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        // Validazione unicità telefono e cellulare (escludendo il customer corrente)
        $errors = [];
        
        // Controllo telefono
        if ($request->filled('phone')) {
            $existingPhone = \App\Models\Customer::where('phone', $request->phone)
                ->where('id', '!=', $id)
                ->whereNull('deleted_at')
                ->first();
                
            if ($existingPhone) {
                $errors['phone'] = 'Questo telefono è già associato a un altro cliente';
            }
        }
        
        // Controllo cellulare
        if ($request->filled('mobile')) {
            $existingMobile = \App\Models\Customer::where('mobile', $request->mobile)
                ->where('id', '!=', $id)
                ->whereNull('deleted_at')
                ->first();
                
            if ($existingMobile) {
                $errors['mobile'] = 'Questo cellulare è già associato a un altro cliente';
            }
        }
        
        // Se ci sono errori, restituisci errore 422
        if (!empty($errors)) {
            return response()->json([
                'message' => 'I dati forniti non sono validi.',
                'errors' => $errors
            ], 422);
        }

        $customer->fill($request->all());

        $customer->save();

        return response()->json($customer);
    }

    public function cities(Request $request)
    {
        $cities = \App\Models\Customer::select('city')
            ->groupBy('city')->orderBy('city', 'asc')
            ->get();

        return response()->json($cities);
    }

    public function confirm(Request $request, $id)
    {
        $customer = \App\Models\Customer::find($id);

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        $customer->confirmed_by = $request->user()->id;
        $customer->confirmed_at = now()->format('Y-m-d H:i:s');

        $customer->save();

        return response()->json($customer);
    }

    public function checkMobile(Request $request, $type, $number)
    {
        // Validazione del tipo
        if (!in_array($type, ['phone', 'mobile'])) {
            return response()->json([
                'error' => 'Tipo non valido. Deve essere "phone" o "mobile"'
            ], 400);
        }

        // Validazione formato numero base
        if (empty($number)) {
            return response()->json([
                'available' => false,
                'message' => 'Numero non valido'
            ]);
        }

        // Controllo incrociato: verifica se il numero esiste in entrambi i campi
        $existingInPhone = \App\Models\Customer::where('phone', $number)
            ->whereNull('deleted_at')
            ->first();
            
        $existingInMobile = \App\Models\Customer::where('mobile', $number)
            ->whereNull('deleted_at')
            ->first();

        // Se non esiste nessun numero, è disponibile
        if (!$existingInPhone && !$existingInMobile) {
            return response()->json([
                'available' => true,
                'message' => 'Numero disponibile'
            ]);
        }

        // Se siamo in modalità aggiornamento (customerId presente)
        $customerId = $request->query('customerId');
        if ($customerId) {
            // Verifica se il numero trovato appartiene al cliente che sta aggiornando
            $customer = \App\Models\Customer::find($customerId);
            
            if ($customer) {
                // Se il numero appartiene al cliente che sta aggiornando, è disponibile
                if (($existingInPhone && $existingInPhone->id == $customerId) || 
                    ($existingInMobile && $existingInMobile->id == $customerId)) {
                    return response()->json([
                        'available' => true,
                        'message' => 'Numero disponibile per aggiornamento'
                    ]);
                }
            }
        }

        // Se arriviamo qui, il numero è occupato da un altro cliente
        $typeLabel = $type === 'phone' ? 'telefono fisso' : 'cellulare';
        
        // Messaggio specifico per controllo incrociato
        if ($type === 'phone' && $existingInMobile) {
            return response()->json([
                'available' => false,
                'message' => 'Questo numero è già registrato come cellulare di un altro cliente'
            ]);
        } elseif ($type === 'mobile' && $existingInPhone) {
            return response()->json([
                'available' => false,
                'message' => 'Questo numero è già registrato come telefono fisso di un altro cliente'
            ]);
        } else {
            // Controllo normale (stesso campo)
            return response()->json([
                'available' => false,
                'message' => "Questo {$typeLabel} è già associato a un altro cliente"
            ]);
        }
    }

    public function export(Request $request)
    {
        $customers = new \App\Models\Customer;

        if ($request->get('id')) {
            $customers = $customers->where('id', $request->get('id'));
        }

        if ($request->filled('brand')) {
            // Need to get all customers that have a paperwork with products of the selected brand
            $customers = $customers->whereHas('paperworks', function ($query) use ($request) {
                $query->whereHas('product', function ($query) use ($request) {
                    $query->where('brand_id', $request->get('brand'));
                });
            });
        }

        if ($request->filled('city')) {
            $customers = $customers->where('city', $request->get('city'));
        }

        if ($request->get('q')) {
            $search = $request->get('q');
            $customers = $customers->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%")
                    ->orWhere('tax_id_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $customers->get();

        $filename = 'customers_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Nome',
                'Cognome',
                'Ragione Sociale',
                'P.IVA',
                'CF',
                'Email',
                'Telefono',
                'Città',
                'Indirizzo',
                'CAP',
                'Aggiunto da',
                'Aggiunto il',
                'Confermato da',
                'Confermato il',
            ]);

            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->id,
                    $customer->name,
                    $customer->last_name,
                    $customer->business_name,
                    $customer->vat_number,
                    $customer->tax_id_code,
                    $customer->email,
                    $customer->phone,
                    $customer->city,
                    $customer->address,
                    $customer->zip_code,
                    $customer->addedByUser ? $customer->addedByUser->name . ' ' . $customer->addedByUser->last_name : '',
                    $customer->added_at,
                    $customer->confirmedByUser ? $customer->confirmedByUser->name . ' ' . $customer->confirmedByUser->last_name : '',
                    $customer->confirmed_at,
                ]);
            }
        };

        return response()->stream($callback, 200, $headers);
    }

    private function transformEntriesToCSV($entries)
    {
        $headers = [
            'ID',
            'Nome',
            'Cognome',
            'Ragione Sociale',
            'P.IVA',
            'CF',
            'Email',
            'Telefono',
            'Città',
            'Indirizzo',
            'CAP',
            'Aggiunto da',
            'Aggiunto il',
            'Confermato da',
            'Confermato il',
        ];

        // Save csv to /tmp 
        $csvPath = tempnam(sys_get_temp_dir(), 'csv');
        $fp = fopen($csvPath, 'w');
        fputcsv($fp, $headers);

        foreach ($entries as $customer) {
            fputcsv($fp, [
                $customer->id,
                $customer->name,
                $customer->last_name,
                $customer->business_name,
                $customer->vat_number,
                $customer->tax_id_code,
                $customer->email,
                $customer->phone,
                $customer->city,
                $customer->address,
                $customer->zip_code,
                $customer->addedByUser ? $customer->addedByUser->name . ' ' . $customer->addedByUser->last_name : '',
                $customer->added_at,
                $customer->confirmedByUser ? $customer->confirmedByUser->name . ' ' . $customer->confirmedByUser->last_name : '',
                $customer->confirmed_at,
            ]);
        }

        fclose($fp);

        return $csvPath;
    }

    // Rimossa la logica di normalizzazione - ora gestita nel Model Customer
}
