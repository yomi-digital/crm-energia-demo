<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuova Comunicazione</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
        }
        .footer {
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .title {
            color: #495057;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Nuova Comunicazione</h1>
        <p>Demo CRM</p>
    </div>

    <div class="content">
        <h2 class="title">{{ $communication->title }}</h2>
        
        @if($communication->body)
            <div>
                {!! $communication->body !!}
            </div>
        @endif

        @if($communication->created_at)
            <p><strong>Data:</strong> {{ $communication->created_at }}</p>
        @endif
    </div>

    <div class="footer">
        <p>Questo messaggio è stato inviato automaticamente da Demo CRM.</p>
        <p>Per favore non rispondere a questa email.</p>
    </div>
</body>
</html>
