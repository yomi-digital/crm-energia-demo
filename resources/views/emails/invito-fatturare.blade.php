<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invito a Fatturare</title>
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
        .message {
            font-size: 16px;
            color: #333;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="message">
        <p>Gentile {{ $report->user->name ?? '' }},</p>
        
        <p>Le inviamo il documento "Invito a Fatturare" relativo al report <strong>{{ $report->name }}</strong>.</p>
        
        @if($periodoRiferimento)
        <p>Periodo di riferimento: <strong>{{ $periodoRiferimento }}</strong></p>
        @endif
        
        <p>La preghiamo di prendere visione del documento allegato e di scaricarlo per procedere con la fatturazione.</p>
        
        <p>Cordiali saluti,<br>Il team di EasyWork CRM</p>
    </div>

    <div class="footer">
        <p>Documento generato automaticamente il {{ date('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
