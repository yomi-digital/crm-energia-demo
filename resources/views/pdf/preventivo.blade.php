<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preventivo {{ $preventivo->numero_preventivo ?? 'N.' . $preventivo->id_preventivo }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20mm;
        }

        .header {
            border-bottom: 3px solid #0066cc;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #0066cc;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header-info {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            background-color: #0066cc;
            color: white;
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .row {
            display: flex;
            margin-bottom: 10px;
        }

        .col {
            flex: 1;
            padding: 5px;
        }

        .label {
            font-weight: bold;
            color: #666;
        }

        .value {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th {
            background-color: #f0f0f0;
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }

        table td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>PREVENTIVO FOTOVOLTAICO</h1>
            <div class="header-info">
                <div>
                    <p><span class="label">Numero Preventivo:</span> <span class="value">{{ $preventivo->numero_preventivo ?? 'N.' . $preventivo->id_preventivo }}</span></p>
                    <p><span class="label">Data:</span> <span class="value">{{ $preventivo->data_preventivo ? $preventivo->data_preventivo->format('d/m/Y') : now()->format('d/m/Y') }}</span></p>
                    <p><span class="label">Stato:</span> <span class="value">{{ strtoupper($preventivo->stato) }}</span></p>
                </div>
            </div>
        </div>

        <!-- Informazioni Cliente e Agente -->
        <div class="section">
            <div class="section-title">INFORMAZIONI GENERALI</div>
            <div class="row">
                <div class="col">
                    <p><span class="label">Cliente ID:</span> <span class="value">{{ $preventivo->fk_cliente }}</span></p>
                    <p><span class="label">Agente ID:</span> <span class="value">{{ $preventivo->fk_agente }}</span></p>
                    <p><span class="label">Area Geografica:</span> <span class="value">{{ ucfirst($preventivo->area_geografica_salvata) }}</span></p>
                    <p><span class="label">Tetto:</span> <span class="value">{{ $preventivo->tetto_salvato }}</span></p>
                    <p><span class="label">Esposizione:</span> <span class="value">{{ ucfirst($preventivo->esposizione_salvata) }}</span></p>
                </div>
                <div class="col">
                    <p><span class="label">Produzione Annua Stimata:</span> <span class="value">{{ number_format($preventivo->produzione_annua_stimata, 2, ',', '.') }} kWh</span></p>
                    <p><span class="label">Risparmio Autoconsumo Annuo:</span> <span class="value">€ {{ number_format($preventivo->risparmio_autoconsumo_annuo, 2, ',', '.') }}</span></p>
                    <p><span class="label">Vendita Eccedenze RID Annua:</span> <span class="value">€ {{ number_format($preventivo->vendita_eccedenze_rid_annua, 2, ',', '.') }}</span></p>
                    <p><span class="label">Incentivo CER Annuo:</span> <span class="value">€ {{ number_format($preventivo->incentivo_cer_annuo, 2, ',', '.') }}</span></p>
                    <p><span class="label">Detrazione Fiscale Annua:</span> <span class="value">€ {{ number_format($preventivo->detrazione_fiscale_annua, 2, ',', '.') }}</span></p>
                </div>
            </div>
        </div>

        <!-- Modalità Pagamento -->
        <div class="section">
            <div class="section-title">MODALITÀ DI PAGAMENTO</div>
            <p><span class="label">Modalità:</span> <span class="value">{{ ucfirst($preventivo->modalita_pagamento_salvata) }}</span></p>
            @if($preventivo->opzione_manutenzione_salvata === 'si')
                <p><span class="label">Opzione Manutenzione:</span> <span class="value">Sì - € {{ number_format($preventivo->costo_annuo_manutenzione_salvato, 2, ',', '.') }} annuo</span></p>
            @endif
            @if($preventivo->opzione_assicurazione_salvata === 'si')
                <p><span class="label">Opzione Assicurazione:</span> <span class="value">Sì - € {{ number_format($preventivo->costo_annuo_assicurazione_salvato, 2, ',', '.') }} annuo</span></p>
            @endif
        </div>

        <!-- Prodotti -->
        @if($preventivo->dettagliProdotti && $preventivo->dettagliProdotti->count() > 0)
        <div class="section">
            <div class="section-title">PRODOTTI</div>
            <table>
                <thead>
                    <tr>
                        <th>Nome Prodotto</th>
                        <th>Categoria</th>
                        <th>Quantità</th>
                        <th>Prezzo Unitario</th>
                        <th>Capacità Batteria</th>
                        <th>Totale</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($preventivo->dettagliProdotti as $prodotto)
                    <tr>
                        <td>{{ $prodotto->nome_prodotto_salvato }}</td>
                        <td>{{ $prodotto->categoria_prodotto_salvata }}</td>
                        <td>{{ number_format($prodotto->quantita, 0, ',', '.') }}</td>
                        <td>€ {{ number_format($prodotto->prezzo_unitario_salvato, 2, ',', '.') }}</td>
                        <td>{{ $prodotto->capacita_batteria_salvata ? number_format($prodotto->capacita_batteria_salvata, 2, ',', '.') . ' kWh' : '-' }}</td>
                        <td>€ {{ number_format($prodotto->quantita * $prodotto->prezzo_unitario_salvato, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Voci Economiche -->
        @if($preventivo->vociEconomiche && $preventivo->vociEconomiche->count() > 0)
        <div class="section">
            <div class="section-title">VOCI ECONOMICHE</div>
            <table>
                <thead>
                    <tr>
                        <th>Nome Voce</th>
                        <th>Tipo</th>
                        <th>Valore Applicato</th>
                        <th>Anni Durata</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($preventivo->vociEconomiche as $voce)
                    <tr>
                        <td>{{ $voce->nome_voce_salvato }}</td>
                        <td>{{ ucfirst($voce->tipo_voce_salvata) }}</td>
                        <td>{{ $voce->valore_applicato }} {{ $voce->tipo_valore_salvato }}</td>
                        <td>{{ $voce->anni_durata_agevolazione_salvata ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Business Plan -->
        @if($preventivo->dettagliBusinessPlan && $preventivo->dettagliBusinessPlan->count() > 0)
        <div class="section">
            <div class="section-title">BUSINESS PLAN</div>
            <table>
                <thead>
                    <tr>
                        <th>Anno</th>
                        <th>Costo Investimento</th>
                        <th>Costi Assicurazione</th>
                        <th>Costi Manutenzione</th>
                        <th>Ricavi Risparmio</th>
                        <th>Ricavi Eccedenze</th>
                        <th>Ricavi Incentivo CER</th>
                        <th>Ricavi Fondo Perduto</th>
                        <th>Flusso Cassa Annuo</th>
                        <th>Flusso Cassa Cumulato</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($preventivo->dettagliBusinessPlan as $bp)
                    <tr>
                        <td>{{ $bp->anno_simulazione }}</td>
                        <td>€ {{ number_format($bp->costo_annuo_investimento, 2, ',', '.') }}</td>
                        <td>€ {{ number_format($bp->costo_annuo_assicurazione, 2, ',', '.') }}</td>
                        <td>€ {{ number_format($bp->costo_annuo_manutenzione, 2, ',', '.') }}</td>
                        <td>€ {{ number_format($bp->ricavo_risparmio_bolletta, 2, ',', '.') }}</td>
                        <td>€ {{ number_format($bp->ricavo_vendita_eccedenze, 2, ',', '.') }}</td>
                        <td>€ {{ number_format($bp->ricavo_incentivo_cer, 2, ',', '.') }}</td>
                        <td>€ {{ number_format($bp->ricavo_fondo_perduto, 2, ',', '.') }}</td>
                        <td>€ {{ number_format($bp->flusso_cassa_annuo, 2, ',', '.') }}</td>
                        <td>€ {{ number_format($bp->flusso_cassa_cumulato, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Preventivo generato il {{ now()->format('d/m/Y H:i') }}</p>
            <p>Questo documento è generato automaticamente e ha valore informativo.</p>
        </div>
    </div>
</body>
</html>

