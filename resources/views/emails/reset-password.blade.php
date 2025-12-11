<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 20px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px 20px;
        }
        .content p {
            margin-bottom: 15px;
            color: #555;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 14px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
        }
        .button:hover {
            opacity: 0.9;
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
            <h1>🔒 Reset Password</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">EasyWork CRM</p>
        </div>

        <div class="content">
            <p>Ciao,</p>
            
            <p>Abbiamo ricevuto una richiesta di reset password per il tuo account EasyWork CRM.</p>
            
            <p>Se sei stato tu a richiederla, clicca sul pulsante qui sotto per reimpostare la tua password:</p>

            <div class="button-container">
                <a href="{{ $resetUrl }}" class="button">Reimposta Password</a>
            </div>

            <div class="info-box">
                <p><strong>⏰ Importante:</strong></p>
                <p>Questo link di reset password scadrà tra <strong>{{ $expireMinutes }} minuti</strong>.</p>
                <p>Se il pulsante non funziona, copia e incolla questo link nel tuo browser:</p>
                <p style="word-break: break-all; font-size: 12px; color: #667eea;">{{ $resetUrl }}</p>
            </div>

            <p>Se non hai richiesto il reset password, puoi ignorare questa email. La tua password rimarrà invariata.</p>

            <p>Per motivi di sicurezza, ti consigliamo di:</p>
            <ul style="color: #555; padding-left: 20px;">
                <li>Non condividere questo link con nessuno</li>
                <li>Usare una password forte e unica</li>
                <li>Cambiare la password regolarmente</li>
            </ul>

            <p>Se hai domande o problemi, contatta il supporto tecnico.</p>

            <p>Saluti,<br><strong>Team EasyWork CRM</strong></p>
        </div>

        <div class="footer">
            <p>Questo messaggio è stato inviato automaticamente da EasyWork CRM.</p>
            <p>Per favore non rispondere a questa email.</p>
        </div>
    </div>
</body>
</html>
