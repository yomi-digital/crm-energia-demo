<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdottoFotovoltaico extends Model
{
    use HasFactory;

    protected $table = 'prodotti_fotovoltaico';
    
    protected $primaryKey = 'id_prodotto';

    protected $fillable = [
        'fk_categoria',
        'codice_prodotto',
        'descrizione',
        'potenza_kwp_pannelli',
        'capacita_kwh',
        'prezzo_base',
        'potenza_inverter',
        'marca_inverter',
        'quantita_inverter',
        'marca_batteria',
        'potenza_batteria',
        'quantita_batterie',
        'quantita_pannelli',
        'marca_pannelli',
        'finanziamento_rate_standard',
        'link_scheda_prodotto_tecnica',
        'is_active',
    ];

    protected $casts = [
        'potenza_kwp_pannelli' => 'float',
        'capacita_kwh' => 'float',
        'prezzo_base' => 'float',
        'potenza_inverter' => 'float',
        'potenza_batteria' => 'float',
        'quantita_inverter' => 'integer',
        'quantita_batterie' => 'integer',
        'quantita_pannelli' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the finanziamento_rate_standard attribute, ensuring it's always decoded from JSON if needed.
     */
    public function getFinanziamentoRateStandardAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        // Se è già un array, restituiscilo direttamente
        if (is_array($value)) {
            return $value;
        }

        // Se è una stringa JSON, decodificala
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            // Se il decode ha successo, restituisci l'array/oggetto decodificato
            if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
                return $decoded;
            }
            // Se il decode non ha funzionato, potrebbe essere una stringa JSON doppia
            // Proviamo a decodificare di nuovo
            if (is_string($decoded)) {
                $doubleDecoded = json_decode($decoded, true);
                if (json_last_error() === JSON_ERROR_NONE && $doubleDecoded !== null) {
                    return $doubleDecoded;
                }
            }
        }

        return $value;
    }

    /**
     * Set the finanziamento_rate_standard attribute, encoding it as JSON string.
     */
    public function setFinanziamentoRateStandardAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['finanziamento_rate_standard'] = null;
        } elseif (is_array($value) || is_object($value)) {
            $this->attributes['finanziamento_rate_standard'] = json_encode($value);
        } else {
            $this->attributes['finanziamento_rate_standard'] = $value;
        }
    }

    /**
     * Convert the model instance to an array.
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        // Assicuriamoci che finanziamento_rate_standard sia decodificato
        if (isset($array['finanziamento_rate_standard']) && is_string($array['finanziamento_rate_standard'])) {
            $decoded = json_decode($array['finanziamento_rate_standard'], true);
            if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
                $array['finanziamento_rate_standard'] = $decoded;
            }
        }
        
        return $array;
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaProdottoFotovoltaico::class, 'fk_categoria', 'id_categoria');
    }

    public function dettagliPreventivi()
    {
        return $this->hasMany(DettaglioProdottoPreventivo::class, 'fk_prodotto', 'id_prodotto');
    }

    public function listini()
    {
        return $this->belongsToMany(
            Listino::class, 
            'listino_prodotto_fotovoltaico', 
            'fk_prodotto', 
            'fk_listino'
        )->withTimestamps();
    }
}
