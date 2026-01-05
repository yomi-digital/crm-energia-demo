<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reimpostata</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            padding: 30px 20px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .success-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px 20px;
        }
        .content p {
            margin-bottom: 15px;
            color: #555;
        }
        .success-box {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success-box p {
            margin: 5px 0;
            font-size: 14px;
            color: #155724;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="success-icon">✅</div>
            <h1>Password Reimpostata con Successo</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Alfacom CRM</p>
        </div>

        <div class="content">
            <p>Ciao,</p>
            
            <div class="success-box">
                <p><strong>✅ Conferma:</strong></p>
                <p>La tua password è stata reimpostata con successo!</p>
            </div>

            <p>Il tuo account Alfacom CRM è ora protetto con la nuova password che hai scelto.</p>

            <div class="info-box">
                <p><strong>🔒 Informazioni di sicurezza:</strong></p>
                <p>• La password è stata modificata il: <strong>{{ now()->format('d/m/Y H:i') }}</strong></p>
                <p>• Se non sei stato tu a reimpostare la password, contatta immediatamente il supporto tecnico.</p>
            </div>

            <p>Per motivi di sicurezza, ti consigliamo di:</p>
            <ul style="color: #555; padding-left: 20px;">
                <li>Non condividere la tua password con nessuno</li>
                <li>Usare una password forte e unica</li>
                <li>Cambiare la password regolarmente</li>
                <li>Non riutilizzare password vecchie</li>
            </ul>

            <p>Ora puoi accedere al tuo account con la nuova password.</p>

            <p>Se hai domande o problemi, contatta il supporto tecnico.</p>

            <p>Saluti,<br><strong>Team Alfacom CRM</strong></p>
        </div>

        <div class="footer">
            <p>Questo messaggio è stato inviato automaticamente da Alfacom CRM.</p>
            <p>Per favore non rispondere a questa email.</p>
        </div>
    </div>
</body>
</html>
