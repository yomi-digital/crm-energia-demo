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
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }
        .main-content {
            padding: 20px 0;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        .periodo-riferimento {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
        }
        .totale-compenso-box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            text-align: right;
            margin-bottom: 30px;
        }
        .totale-compenso-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .totale-compenso-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background-color: #f5f5f5;
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
            font-size: 14px;
        }
        td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            font-size: 14px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
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
    <div class="main-content">
        <div class="title">Invito a Fatturare</div>
        
        @if($periodoRiferimento)
        <div class="periodo-riferimento">
            Periodo di riferimento: {{ $periodoRiferimento }}
        </div>
        @endif

        <div class="totale-compenso-box">
            <div class="totale-compenso-label">Totale Compenso</div>
            <div class="totale-compenso-value">€ {{ number_format($totalCompenso, 2, ',', '.') }}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Account</th>
                    <th>Data</th>
                    <th>Offerta</th>
                    <th>Compenso</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                <tr>
                    <td>{{ $entry->id }}</td>
                    <td>{{ $entry->customer }}</td>
                    <td>{{ $entry->account }}</td>
                    <td>{{ $entry->activated_at }}</td>
                    <td>{{ $entry->product }}</td>
                    <td style="text-align: right;">€ {{ number_format($entry->payout_confirmed, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Documento generato automaticamente il {{ date('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
