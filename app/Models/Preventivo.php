<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preventivo extends Model
{
    use HasFactory;

    protected $table = 'preventivi';
    
    protected $primaryKey = 'id_preventivo';

    protected $fillable = [
        'numero_preventivo',
        'data_preventivo',
        'fk_cliente',
        'fk_agente',
        'created_by',
        'stato',
        'tetto_salvato',
        'area_geografica_salvata',
        'esposizione_salvata',
        'modalita_pagamento_salvata',
        'bonifico_data_json',
        'finanziamento_data_json',
        'opzione_manutenzione_salvata',
        'costo_annuo_manutenzione_salvato',
        'opzione_assicurazione_salvata',
        'costo_annuo_assicurazione_salvato',
        'pdf_url',
        'produzione_annua_stimata',
        'risparmio_autoconsumo_annuo',
        'vendita_eccedenze_rid_annua',
        'incentivo_cer_annuo',
        'detrazione_fiscale_annua',
    ];

    protected $casts = [
        'data_preventivo' => 'date',
        'costo_annuo_manutenzione_salvato' => 'float',
        'costo_annuo_assicurazione_salvato' => 'float',
        'produzione_annua_stimata' => 'float',
        'risparmio_autoconsumo_annuo' => 'float',
        'vendita_eccedenze_rid_annua' => 'float',
        'incentivo_cer_annuo' => 'float',
        'detrazione_fiscale_annua' => 'float',
    ];

    public function cliente()
    {
        return $this->belongsTo(Customer::class, 'fk_cliente', 'id');
    }

    public function agente()
    {
        return $this->belongsTo(User::class, 'fk_agente', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function consumi()
    {
        return $this->hasMany(ConsumoPreventivo::class, 'fk_preventivo', 'id_preventivo');
    }

    public function dettagliProdotti()
    {
        return $this->hasMany(DettaglioProdottoPreventivo::class, 'fk_preventivo', 'id_preventivo');
    }

    public function vociEconomiche()
    {
        return $this->hasMany(PreventivoVoceEconomica::class, 'fk_preventivo', 'id_preventivo');
    }

    public function dettagliBusinessPlan()
    {
        return $this->hasMany(DettaglioBusinessPlan::class, 'fk_preventivo', 'id_preventivo');
    }
}
