<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calcolo Incentivo Fotovoltaico</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }
        .header-logos {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px 0;
        }
        .logo-center {
            display: inline-block;
        }
        .main-content {
            text-align: center;
            padding: 20px 0;
        }
        .incentivo-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }
        .incentivo-amount {
            font-size: 32px;
            font-weight: bold;
            color: #2c5aa0;
            margin: 10px 0;
        }
        .incentivo-period {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
        }
        .divider {
            border-top: 1px solid #ddd;
            margin: 30px 0;
        }
        .dati-tecnici {
            text-align: center;
            margin: 20px 0;
        }
        .dati-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 15px;
        }
        .dati-item {
            margin: 8px 0;
            color: #2c5aa0;
        }
        .disclaimer {
            font-size: 12px;
            color: #666;
            line-height: 1.4;
            margin: 30px 0;
        }
        .cta-button {
            background-color: #ff6b9d;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header-logos">
        <div class="logo-center">
            <img src="https://easywork-crm.fra1.digitaloceanspaces.com/logos/LOGO_nobg.png" alt="Logo Alfacom" style="height: 60px;">
        </div>
    </div>

    <div class="main-content">
        @if($hasPanels === 'has')
            <!-- Se ha pannelli: mostra entrambi gli incentivi -->
            <div class="incentivo-title">Incentivi calcolati:</div>
            <div style="margin: 20px 0;">
                <div style="margin-bottom: 15px;">
                    <div style="font-size: 16px; color: #2c5aa0; font-weight: bold;">Incentivo CER:</div>
                    <div class="incentivo-amount">{{ number_format($incentivo_cer, 2, ',', '.') }} €</div>
                </div>
                <div style="margin-bottom: 15px;">
                    <div style="font-size: 16px; color: #2c5aa0; font-weight: bold;">Ritiro dedicato:</div>
                    <div class="incentivo-amount">{{ number_format($incentivo_dedicated, 2, ',', '.') }} €</div>
                </div>
            </div>
            <div class="incentivo-period">in 20 anni</div>
        @else
            <!-- Se non ha pannelli: mostra solo incentivo POD -->
            <div class="incentivo-title">Incentivi titolari di un Pod:</div>
            <div class="incentivo-amount">{{ number_format($incentivo_pod, 2, ',', '.') }} €</div>
            <div class="incentivo-period">in 20 anni</div>
        @endif

        <div class="divider"></div>

        <div class="dati-tecnici">
            <div class="dati-title">Dati tecnici indicati:</div>
            <div class="dati-item">Spesa bolletta {{ $periodoBolletta }}: {{ number_format($spesaBollettaMensile, 1, ',', '.') }} €</div>
            <div class="dati-item">Consumo energetico: {{ $kwhSpesi }} kWh</div>
            <div class="dati-item">Pannelli esistenti: {{ $hasPanels === 'has' ? 'Sì' : 'No' }}</div>
        </div>

        <div class="disclaimer">
            Il calcolo mostrato è una stima indicativa basata sui dati inseriti e su ipotesi medie di funzionamento di una Comunità Energetica Rinnovabile in equilibrio tra produzione e consumo. Gli incentivi possono variare nel tempo in base alle tariffe stabilite da GSE e ARERA, alla zona geografica e all'effettivo funzionamento della CER. Il risultato ha scopo puramente informativo.
        </div>

    </div>

    <div class="footer">
        <p>Il team di Alfacom</p>
        <p style="font-size: 12px; margin-top: 10px;">
            Questa email è stata inviata a {{ $email }} in seguito al calcolo dell'incentivo fotovoltaico.<br>
            © {{ date('Y') }} - Tutti i diritti riservati
        </p>
    </div>
</body>
</html>
