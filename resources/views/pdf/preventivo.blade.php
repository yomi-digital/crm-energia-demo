<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preventivo {{ $preventivo->numero_preventivo ?? 'N.' . $preventivo->id_preventivo }}</title>
    <style>
        @page {
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
            background: #fff;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 0;
            position: relative;
            background: #fff;
            display: flex;
        }

        /* Seconda pagina con margine sinistro verde */
        .page-content {
            flex: 1;
            padding: 20mm 15mm;
            position: relative;
        }

        /* Pagina privacy con padding ridotto */
        .privacy-page-content {
            flex: 1;
            padding: 10mm 15mm;
            position: relative;
        }

        .page-left-margin {
            width: 20mm;
            background-color: #4BAE66;
            flex-shrink: 0;
        }

        .page-left-margin-blue {
            width: 20mm;
            background-color: #1A233B;
            flex-shrink: 0;
        }

        /* Header blu scuro per privacy */
        .section-header-blue {
            background-color: #1A233B;
            color: white;
            padding: 15px 20px;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
            margin-bottom: 20px;
            margin-top: -10mm;
            width: calc(100% + 30mm);
            margin-left: -15mm;
            margin-right: -15mm;
            padding-left: 15mm;
            padding-right: 15mm;
        }

        /* Titolo sezione verde */
        .section-title-green {
            color: #4BAE66;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        /* Sottotitoli tabelle */
        .table-subtitle {
            font-size: 14px;
            color: #333;
            margin: 20px 0 10px 0;
            font-weight: normal;
            font-family: sans-serif;
        }

        /* Tabella mensile con header blu scuro */
        .table-monthly {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: none;
        }

        .table-monthly th {
            background-color: #1a2b4d;
            color: white;
            padding: 4px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 15px;
            text-transform: uppercase;
            border: none;
        }

        .table-monthly th:first-child {
            border-top-left-radius: 10px;
        }

        .table-monthly th:last-child {
            border-top-right-radius: 10px;
        }

        .table-monthly td {
            padding: 3px 6px;
            border-bottom: 2px solid white;
            border-right: 2px solid white;
            border-left: none;
            border-top: none;
            font-size: 11px;
            background-color: rgba(247, 247, 247, 1);
        }

        .table-monthly td:last-child {
            border-right: none;
        }

        .table-monthly tr:last-child td {
            border-bottom: none;
        }
        
        .table-monthly td *,
        .table-monthly th * {
            padding: 0;
            margin: 0;
        }

        /* Tabella bimestrale con header verde */
        .table-bimonthly {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 20px;
            border: none;
        }

        .table-bimonthly th {
            background-color: #4CAF50;
            color: white;
            padding: 4px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 15px;
            text-transform: uppercase;
            border: none;
        }

        .table-bimonthly th:first-child {
            border-top-left-radius: 10px;
        }

        .table-bimonthly th:last-child {
            border-top-right-radius: 10px;
        }

        .table-bimonthly td {
            padding: 3px 6px;
            border-bottom: 2px solid white;
            border-right: 2px solid white;
            border-left: none;
            border-top: none;
            font-size: 11px;
            background-color: rgba(247, 247, 247, 1);
        }

        .table-bimonthly td:last-child {
            border-right: none;
        }

        .table-bimonthly tr:last-child td {
            border-bottom: none;
        }
        
        .table-bimonthly td *,
        .table-bimonthly th * {
            padding: 0;
            margin: 0;
        }

        /* Header verde per sezioni */
        .section-header-green {
            background-color: #4BAE66;
            color: white;
            padding: 3px 0px 8px 0px;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
            margin-bottom: 10px;
            margin-top: -10mm;
            width: calc(100% + 30mm);
            margin-left: -15mm;
            margin-right: -15mm;
            padding-left: 15mm;
        }

        .section-header-green span {
            display: inline-block;
        }

        /* Box verde costi complessivi */
        .costi-box {
            position: absolute;
            bottom: 20mm;
            right: 15mm;
            width: 180mm;
            background-color: #4BAE66;
            border-radius: 20px 20px 0 0;
            padding: 25px;
            color: white;
        }

        .costi-box-title {
            text-align: left;
            font-size: 18px;
            font-weight: bolder;
            margin-bottom: 20px;
            color: white;
        }

        .costi-item {
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            font-size: 13px;
        }

        .costi-item:last-child {
            border-bottom: none;
        }

        .costi-totale {
            font-weight: bold;
            font-size: 15px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid rgba(255, 255, 255, 0.5);
        }

        /* Logo piccolo in basso a destra */
        .page-logo-small {
            position: absolute;
            bottom: 10mm;
            right: 15mm;
            width: 40mm;
            height: auto;
            z-index: 10;
            display: block;
        }

        .page-logo-small img {
            width: 100%;
            height: auto;
            display: block;
            max-width: 100%;
        }

        /* Prima pagina con layout a due pannelli */
        .first-page {
            width: 210mm;
            height: 297mm; /* Altezza fissa per accomodare i blocchi */
            position: relative;
            overflow: hidden;
            background: white;
        }

        /* Il pannello sinistro e destro non saranno più usati in questo modo */
        /* first-page-left non sarà più una colonna a sé stante */
        .first-page-left {
            display: none; /* Rimuovo la visualizzazione */
        }

        /* first-page-right non sarà più una colonna a sé stante */
        .first-page-right {
            display: none; /* Rimuovo la visualizzazione */
        }

        /* Pannello sinistro */
        .first-page-left {
            width: 45%;
            position: relative;
            z-index: 2;
        }

        /* Linea verticale blu a sinistra */
        .first-page-left::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 25px;
            background-color: rgba(15, 17, 55, 1);
            z-index: 10;
        }

        /* Blocco verde che va dall'alto al fondo */
        .first-page-green-block {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 3px;
            right: 0;
            background-color: #4BAE66;
            padding: 20mm 15mm 40mm 15mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            overflow: hidden;
        }

        /* Logo nel blocco verde */
        .first-page-logo {
            width: 100%;
            height: auto;
            margin-bottom: 0;
        }

        .first-page-logo img {
            width: 100%;
            height: auto;
            object-fit: contain;
        }

        /* Blocco blu scuro sopra il verde in basso */
        .first-page-blue-block {
            position: absolute;
            bottom: 0;
            left: 3px;
            right: 0;
            background-color: rgba(15, 17, 55, 1);
            color: white;
            padding: 10mm 10mm;
            border-radius: 10mm 10mm 0 0;
            display: flex;
            flex-direction: column;
            z-index: 3;
        }

        /* Immagine kit singola posizionata sopra il blocco blu */
        .solar-kit-image {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%) translateY(-190%);
            width: 60mm;
            height: auto;
            z-index: 4;
            pointer-events: none;
        }

        .solar-kit-image img {
            width: 100%;
            height: auto;
            display: block;
            max-width: 100%;
        }

        .excellence-title {
            font-size: 25px;
            font-weight: bolder;
            text-transform: uppercase;
            margin-top: 10mm;
            margin-bottom: 3mm;
            line-height: 1.3;
            transform: rotate(-3deg);
        }

        .preventivo-number {
            font-size: 16px;
            font-weight: bold;
        }

        /* Pannello destro bianco con foto */
        .first-page-right {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 55%;
            background: white;
            margin: 0;
            padding: 0;
        }

        .first-page-image {
            width: 100%;
            height: 100%;
            min-height: 297mm;
            object-fit: cover;
            border-radius: 0;
            display: block;
            margin: 0;
            padding: 0;
        }

        .preventivo-title {
            font-size: 35px;
            font-weight: bold;
            margin-bottom: 40px;
            color: white;
            line-height: 1.1;
        }

        .client-container {
            display: table;
            width: 100%;
            margin-top: 25px;
            position: relative;
        }

        .client-label-vertical {
            display: table-cell;
            vertical-align: top;
            width: 50px;
            padding-right: 5px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            line-height: 1.4;
            position: relative;
        }

        .client-label-vertical::after {
            content: '';
            position: absolute;
            right: 0;
            top: 100%;
            bottom: 0;
            width: 3px;
            background-color: white;
        }

        .client-label-vertical span {
            display: block;
            margin: 0;
            padding: 0;
            line-height: 1;
        }

        .client-details {
            display: table-cell;
            vertical-align: top;
            padding-left: 10px;
        }

        .client-detail-item {
            margin-bottom: 16px;
            font-size: 7.5px;
        }

        .client-detail-item .label {
            font-weight: bold;
            display: inline;
            font-size: 7.5px;
            margin: 0;
            padding: 0;
        }

        .client-detail-item .value {
            font-weight: normal;
            font-size: 7.5px;
            white-space: nowrap;
            display: block;
            margin: 0;
            padding: 0;
        }

        .client-detail-item .value .label {
            font-weight: bold;
            display: inline;
            margin: 0;
            padding: 0;
            margin-right: 5px;
        }

        /* Tabelle con header verde o blu scuro */
        .data-table-green {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 20px;
            overflow: hidden;
            border: none;
        }

        .data-table-green td {
            padding: 12px 15px;
            font-size: 11px;
            border-bottom: 2px solid white;
            border-left: none;
            border-top: none;
            border-right: none;
        }

        .data-table-green td:first-child {
            background-color: rgba(15, 17, 55, 1);
            color: white;
            font-weight: normal;
            width: 60%;
            border-left: none;
            border-top: none;
        }

        .data-table-green tr:first-child td:first-child {
            border-top-left-radius: 8px;
        }

        .data-table-green tr:last-child td:first-child {
            border-bottom: none;
        }

        .data-table-green td:last-child {
            background-color: #F8F8F8;
            color: #333;
            text-align: right;
            width: 40%;
            border-left: none;
            border-top: none;
            border-right: none;
        }

        .data-table-green tr:first-child td:last-child {
            border-top-right-radius: 8px;
        }

        .data-table-green tr:last-child td:last-child {
            border-bottom: none;
        }

        .data-table-blue {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table-blue td:first-child {
            background-color: #1a2b4d;
            color: white;
            padding: 12px 15px;
            font-weight: bold;
            font-size: 11px;
            width: 60%;
        }

        .data-table-blue td:last-child {
            background-color: white;
            padding: 12px 15px;
            font-size: 11px;
            text-align: right;
            border-bottom: 1px solid #e0e0e0;
        }

        /* Tabella con header rosso-arancio */
        .data-table-orange {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 20px;
            overflow: hidden;
            border: none;
        }

        .data-table-orange td {
            padding: 12px 15px;
            font-size: 11px;
            border-bottom: 2px solid white;
            border-left: none;
            border-top: none;
            border-right: none;
        }

        .data-table-orange td:first-child {
            background-color: rgba(255, 87, 64, 1);
            color: white;
            padding: 12px 15px;
            font-weight: normal;
            font-size: 11px;
            width: 60%;
            border-left: none;
            border-top: none;
        }

        .data-table-orange td:last-child {
            background-color: #F8F8F8;
            color: #333;
            padding: 12px 15px;
            font-size: 11px;
            text-align: right;
            width: 40%;
            border-left: none;
            border-top: none;
            border-right: none;
        }

        .data-table-orange tr:first-child td:first-child {
            border-top-left-radius: 8px;
        }

        .data-table-orange tr:last-child td:first-child {
            border-bottom: none;
        }

        .data-table-orange tr:first-child td:last-child {
            border-top-right-radius: 8px;
        }

        .data-table-orange tr:last-child td:last-child {
            border-bottom: none;
        }

        /* Sezione grafico Consumi Vs Produzione */
        .chart-section {
            overflow: hidden;
            margin-top: 30px;
            width: 100%;
        }

        .chart-container {
            float: right;
            width: calc(100% - 230px);
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            position: relative;
        }

        .chart-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .chart-bars {
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
            height: 200px;
            gap: 15px;
            margin-bottom: 15px;
        }

        .chart-bar {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            height: 100%;
        }

        .bar-segment-consumi {
            background-color: #e74c3c;
            min-height: 30px;
            border-radius: 4px 4px 0 0;
        }

        .bar-segment-produzione {
            background-color: #27ae60;
            min-height: 30px;
            border-radius: 0 0 4px 4px;
        }

        .chart-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 10px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .legend-color-red {
            background-color: #e74c3c;
        }

        .legend-color-green {
            background-color: #27ae60;
        }

        /* Metriche con icone */
        .metrics-sidebar {
            float: left;
            width: 200px;
            margin-right: 30px;
        }

        .metric-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .metric-icon {
            width: 24px;
            height: 24px;
            background-color: #4BAE66;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .metric-label {
            font-size: 12px;
            color: #333;
            font-weight: 500;
        }

        /* Grafico a barre orizzontali */
        .horizontal-bar-chart {
            margin: 10px 0;
        }

        .bar-item {
            margin-bottom: 8px;
        }

        .bar-label {
            font-size: 10px;
            margin-bottom: 3px;
            color: #333;
        }

        .bar-container {
            position: relative;
            height: 18px;
            background: #f0f0f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background-color: #e74c3c;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 8px;
            color: white;
            font-size: 10px;
            font-weight: bold;
        }

        .bar-grid {
            background-image: linear-gradient(to right, #ddd 1px, transparent 1px);
            background-size: 20% 100%;
        }

        /* Grafico a torta con SVG */
        .pie-chart-container {
            width: 200px;
            height: 200px;
            position: relative;
            margin: 20px auto;
        }

        .pie-chart {
            width: 100%;
            height: 100%;
        }

        .pie-legend {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .pie-legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 10px;
        }

        .pie-legend-color {
            width: 12px;
            height: 12px;
        }

        /* Grafico a torta donut per TOTALE CONSUMI */
        .donut-chart-container {
            width: 300px;
            height: 300px;
            position: relative;
            margin: 0px auto 5px auto;
            display: block;
            overflow: visible;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .pie-chart-wrapper {
            width: 100%;
            height: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pie-chart-base {
            width: 200px;
            height: 200px;
            position: relative;
            border-radius: 50%;
            overflow: hidden;
        }

        .pie-segment {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            border-radius: 50%;
        }

        .pie-segment-wrapper {
            position: absolute;
            width: 200px;
            height: 200px;
            top: 0;
            left: 0;
            overflow: hidden;
        }

        .pie-segment {
            position: absolute;
            width: 200px;
            height: 200px;
            top: 0;
            left: 0;
            border-radius: 50%;
        }
        
        .pie-segment-top {
            position: absolute;
            width: 200px;
            height: 100px;
            top: 0;
            left: 0;
            border-radius: 200px 200px 0 0;
            overflow: hidden;
            background-color: transparent;
        }
        
        .pie-segment-bottom {
            position: absolute;
            width: 200px;
            height: 100px;
            top: 100px;
            left: 0;
            border-radius: 0 0 200px 200px;
            overflow: hidden;
            background-color: transparent;
        }
        
        .pie-segment-fill {
            position: absolute;
            width: 200px;
            height: 200px;
            top: 0;
            left: 0;
            border-radius: 50%;
        }
        
        .pie-segment-top .pie-segment-fill {
            top: 0;
            transform-origin: 100px 100px;
        }
        
        .pie-segment-bottom .pie-segment-fill {
            top: -100px;
            transform-origin: 100px 100px;
        }

        .pie-chart-inner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 120px;
            height: 120px;
            background-color: white;
            border-radius: 50%;
            z-index: 5;
        }

        .pie-chart-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 10;
            width: 120px;
        }

        .donut-chart {
            width: 100%;
            height: 100%;
            display: block;
        }

        .donut-legend {
            text-align: center;
            margin-top: 20px;
            white-space: nowrap;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .donut-legend-item {
            display: inline-block;
            vertical-align: middle;
            margin: 0 10px;
            font-size: 11px;
            white-space: nowrap;
        }

        .donut-legend-square {
            width: 45px;
            height: 20px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        /* Display risparmio annuo */
        .savings-circle {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 8px solid #1a2b4d;
            border-top: 8px solid #4BAE66;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px auto;
            position: relative;
        }

        .savings-value {
            font-size: 32px;
            font-weight: bold;
            color: #4BAE66;
        }

        /* Footer giallo con grafico a linee */
        .yellow-footer {
            background-color: #fdd835;
            padding: 20px 30px;
            margin-top: 30px;
            position: relative;
            border-top-left-radius: 25px;
            border-top-right-radius: 25px;
        }

        .yellow-footer-title {
            font-size: 18px;
            font-weight: bold;
            color: #1A233B;
            text-transform: uppercase;
            margin-bottom: 15px;
            text-align: center;
        }

        .yellow-footer-line-chart {
            height: 150px;
            position: relative;
            background-image: 
                repeating-linear-gradient(to bottom, transparent, transparent 19px, #ddd 19px, #ddd 20px),
                repeating-linear-gradient(to right, transparent, transparent 19px, #ddd 19px, #ddd 20px);
            background-size: 100% 20px, 20px 100%;
            border-top-right-radius: 8px;
            border-top-left-radius: 8px;
        }

        .line-chart-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }

        .line-chart {
            height: 200px;
            position: relative;
            background-image: 
                repeating-linear-gradient(to bottom, transparent, transparent 19px, #ddd 19px, #ddd 20px),
                repeating-linear-gradient(to right, transparent, transparent 19px, #ddd 19px, #ddd 20px);
            background-size: 100% 20px, 20px 100%;
        }

        .line-path {
            stroke: #e74c3c;
            stroke-width: 3;
            fill: none;
        }

        /* Card offerta economica */
        .offer-cards-container {
            display: flex;
            gap: 30px;
            margin: 30px 0;
        }

        .offer-card {
            flex: 1;
            border-radius: 15px;
            padding: 30px;
            min-height: 400px;
            display: flex;
            flex-direction: column;
        }

        .offer-card-white {
            background-color: white;
            border: 2px solid #e0e0e0;
        }

        .offer-card-green {
            background-color: #4BAE66;
            color: white;
        }

        .offer-card-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .offer-price {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .offer-vat {
            font-size: 14px;
            margin-bottom: 30px;
            opacity: 0.8;
        }

        .offer-benefits {
            list-style: none;
            padding: 0;
            margin: 0;
            flex: 1;
        }

        .offer-benefit-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .checkmark {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
        }

        .checkmark-red {
            color: #e74c3c;
        }

        .checkmark-green {
            color: white;
        }

        /* Footer verde con overlay per OFFERTA ECONOMICA */
        .offer-footer-green {
            background-color: #1A233B;
            padding: 20px 30px;
            margin-top: 30px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: white;
        }

        .offer-footer-text {
            font-size: 24px;
            font-weight: bold;
        }

        .offer-footer-price {
            color: #4BAE66;
            font-size: 32px;
            font-weight: bold;
        }

        .offer-footer-vat {
            font-size: 14px;
            margin-left: 10px;
        }

        /* Blocchi manutenzione e assicurazione */
        .service-blocks-container {
            margin: 15px 0;
        }

        .service-block {
            border-radius: 10mm 0 0 10mm;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
            min-height: 250px;
        }

        .service-block-green {
            background-color: #4BAE66;
            color: white;
        }

        .service-block-blue {
            background-color: #1A233B;
            color: white;
        }

        /* Overlay verde in basso a destra per pagina manutenzione */
        .service-overlay-green {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 80mm;
            height: 80mm;
            background-color: #4BAE66;
            border-radius: 0 0 0 40mm;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .service-overlay-logo {
            width: 40px;
            height: 40px;
        }

        .service-overlay-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .service-header {
            overflow: hidden;
            margin-bottom: 12px;

            padding-bottom: 2px;
        }

        .service-icon {
            width: 40px;
            height: 40px;
            float: left;
            display: block;
            margin-right: 15px;
            margin-top: 10px;
            padding: 0;
        }

        .service-icon img {
            width: 100%;
            height: 100%;
            display: block;
        }

        .service-icon-black {
            color: #333;
        }

        .service-icon-white {
            color: white;
        }

        .service-title {
            font-size: 24px;
            font-weight: bold;
            white-space: nowrap;
            display: block;
            padding: 0;
            margin: 0;
        }

        .service-description {
            font-size: 12px;
            line-height: 1.6;
            margin-bottom: 12px;
            opacity: 0.9;
        }

        .service-benefits {
            list-style: none;
            padding: 0;
            margin: 0 0 12px 0;
        }

        .service-benefit-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .service-checkmark {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .service-checkmark-black {
            color: #333;
        }

        .service-checkmark-white {
            color: white;
        }

        .service-price-container {
            margin-top: auto;
            padding-top: 12px;
            border-top: 2px solid rgba(0,0,0,0.1);
        }

        .service-price-container-red {
            border-top-color: rgba(255,255,255,0.3);
        }

        .service-price {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .service-vat {
            font-size: 13px;
            opacity: 0.8;
        }

        /* Stili per informativa privacy - compatta */
        .privacy-section {
            margin-bottom: 4px;
        }

        .privacy-section-number {
            font-size: 8px;
            font-weight: bold;
            color: #4BAE66;
            margin-bottom: 2px;
        }

        .privacy-section-title {
            font-size: 8px;
            font-weight: bold;
            color: #333;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .privacy-section-subtitle {
            font-size: 7px;
            font-weight: bold;
            color: #333;
            margin: 3px 0 2px 0;
        }

        .privacy-text {
            font-size: 6px;
            line-height: 1.2;
            color: #333;
            text-align: justify;
            margin-bottom: 3px;
        }

        .privacy-list {
            margin: 2px 0;
            padding-left: 10px;
        }

        .privacy-list-item {
            font-size: 6px;
            line-height: 1.2;
            color: #333;
            margin-bottom: 1px;
        }

        /* Layout a colonne per pagina privacy */
        .privacy-columns-container {
            display: block;
            overflow: hidden;
            margin-bottom: 5px;
        }

        .privacy-columns-container::after {
            content: "";
            display: table;
            clear: both;
        }

        .privacy-column {
            width: 48%;
            float: left;
            padding-right: 2%;
            box-sizing: border-box;
        }

        .privacy-column:last-child {
            float: right;
            padding-right: 0;
            padding-left: 2%;
        }

        .privacy-bottom-blocks {
            display: block;
            overflow: hidden;
            margin-top: 5px;
        }

        .privacy-bottom-block {
            width: 100%;
            margin-bottom: 5px;
        }

        .privacy-signature-section {
            margin-top: 5px;
            padding: 5px;
            border: 2px solid rgba(15, 17, 55, 1);
        }

        .signature-row {
            margin-bottom: 5px;
        }

        .signature-row::after {
            content: "";
            display: table;
            clear: both;
        }

        .signature-field {
            float: left;
            width: 32%;
            margin-right: 2%;
            box-sizing: border-box;
        }

        .signature-field:last-child {
            margin-right: 0;
        }

        /* Per signature-row con 2 campi */
        .signature-row .signature-field:nth-child(1):nth-last-child(2),
        .signature-row .signature-field:nth-child(2):nth-last-child(1) {
            width: 49%;
        }

        .signature-label {
            font-size: 7px;
            font-weight: bold;
            color: #333;
            margin-bottom: 1px;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            min-height: 12px;
            margin-bottom: 2px;
        }

        /* Ultima pagina con footer verde */
        .last-page-container {
            width: 100%;
            height: 100vh;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .last-page-image {
            flex: 1;
            width: 100%;
            object-fit: cover;
            object-position: center;
        }

        .last-page-footer {
            background-color: #1A233B;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            gap: 20px;
            min-height: 120px;
        }

        .last-page-logo {
            height: 60px;
            width: auto;
        }

        .last-page-divider {
            width: 2px;
            height: 60px;
            background-color: white;
            flex-shrink: 0;
        }

        .last-page-contact {
            flex: 1;
            color: white;
            font-size: 11px;
            line-height: 1.6;
        }

        .last-page-company {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .section-title {
            background: linear-gradient(135deg, #0066cc 0%, #004499 100%);
            color: white;
            padding: 12px 15px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
            border-radius: 4px;
        }

        .section-with-image {
            position: relative;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .section-image {
            width: 100%;
            max-height: 150px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        /* Tabelle */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: #fff;
        }

        table th {
            background-color: #0066cc;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #004499;
        }

        table td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 10px;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        table tbody tr:hover {
            background-color: #e9ecef;
        }

        .total-row {
            font-weight: bold;
            background-color: #e9ecef !important;
            border-top: 2px solid #0066cc;
        }

        /* Informazioni impianto con immagini */
        .impianto-info {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .impianto-image {
            flex: 0 0 200px;
        }

        .impianto-image img {
            width: 100%;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .impianto-details {
            flex: 1;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .info-item {
            padding: 8px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .info-item .label {
            font-weight: bold;
            color: #666;
            font-size: 9px;
            display: block;
            margin-bottom: 3px;
        }

        .info-item .value {
            color: #0066cc;
            font-size: 12px;
            font-weight: bold;
        }

        /* Footer con ultima pagina */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        .footer-image {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        /* Footer verde per BUSINESS PLAN */
        .business-plan-footer {
            background-color: #4BAE66;
            padding: 20px 20px;
        }

        .business-plan-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
        }

        .business-plan-table th {
            background-color: #4BAE66;
            color: white;
            padding: 4px 3px;
            text-align: left;
            font-weight: bold;
            font-size: 7px;
            border: 1px solid #1a5d3f;
        }

        .business-plan-table td {
            padding: 3px 3px;
            border: 1px solid #e0e0e0;
            font-size: 7px;
            background-color: white;
            line-height: 1.2;
        }

        .business-plan-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        /* Footer per pagina Simulazione Impianto */
        .page-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            min-height: 40px;
            height: 40px;
        }

        .page-footer-green-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 120px;
            height: 40px;
            background-color: #4BAE66;
        }

        .page-footer-logo {
            position: absolute;
            bottom: 0;
            right: 10px;
            height: 30px;
            width: auto;
            padding: 5px;
            z-index: 10;
        }

        /* Footer blu per pagina Privacy */
        .page-footer-blue {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            margin-left: -15mm;
            margin-right: -15mm;
            padding-left: 30mm;
            padding-right: 15mm;
            background-color: #1A233B;
            padding: 20px 30px;
            min-height: 80px;
        }

        .page-footer-blue-bar {
            display: none;
        }

        .page-footer-blue-logo {
            float: left;
            height: 60px;
            width: auto;
            transform: translateX(50px) translateY(25px);
        }

        .page-footer-blue-divider {
            display: none;
        }

        .page-footer-blue-contact {
            float: right;
            color: white;
            font-size: 11px;
            line-height: 1.6;
            text-align: right;
            padding-top: 10px;
        }

        .page-footer-blue-company {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 5px;
        }

        /* Break page */
        .page-break {
            page-break-before: always;
        }

        /* Utility classes */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-bold {
            font-weight: bold;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .mt-10 {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <!-- 297mm , 210mm -->
    <!-- Prima Pagina -->
    <div class="first-page">
        <!-- Blocco Superiore: Immagine e Testo -->
        <div style="position: relative; width: 100%; height: 162mm; overflow: hidden; background-image: url('{{ public_path('images/pdf/HomePage4-min.webp') }}'); background-size: cover; background-position: center;">
            <div style="position: absolute; bottom: 10mm; left: 25mm; color: white; font-size: 20px; line-height: 1.2; font-weight: bold;">
                LA SIMULAZIONE PERFETTA<br>
                PER PROGETTARE<br>
                IL TUO IMPIANTO FOTOVOLTAICO
            </div>
        </div>

        <!-- Blocco Inferiore: Dati Preventivo/Cliente e Logo/Agente -->
        <div style="width: 100%; height: 117mm; position: relative;">
            <!-- Colonna Sinistra: Dati Preventivo e Cliente -->
            <div style="width:88mm; height: 117mm; display:inline-block; vertical-align: top;">
                <!-- Barra verde con alfacomsolar.it -->
                <div style="width:55mm; height: 15mm; background-color: #4BAE66; padding: 0 18mm; border-radius: 0mm 5mm 5mm 0mm;">
                    <div style="color: white; font-size: 16px; font-weight: bold; transform: translateY(2.5mm)">
                        alfacomsolar.it
                    </div>
                </div>

                <div style="padding-left: 18mm; padding-top: 10mm;">
                    <div style="margin-bottom: 20px; line-height: 1.3;">
                        <div style="font-size: 14px; color: #666;">P R E V E N T I V O</div>
                        <div style="font-size: 16px; color: #4BAE66; font-weight: bold;">#{{ $preventivo->numero_preventivo ?? str_pad($preventivo->id_preventivo, 3, '0', STR_PAD_LEFT) }}</div>
                        <div style="font-size: 12px; color: #666;">Data: {{ \Carbon\Carbon::parse($preventivo->data_creazione)->format('d/m/Y') }}</div>
                    </div>
                    <div style="font-size: 14px; color: #666; margin-top: 30px; margin-bottom: 1px;">C L I E N T E</div>
                    @if($preventivo->cliente)
                        <div style="font-size: 12px; line-height: 1.3;">
                            <span>Name:</span> <span style="font-weight: bold;">{{ $preventivo->cliente->name ?? '' }} {{ $preventivo->cliente->last_name ?? '' }} @if($preventivo->cliente->business_name) {{ $preventivo->cliente->business_name }} @endif</span><br>
                            @if($preventivo->cliente->address)<span>Via:</span> <span style="font-weight: bold;">{{ $preventivo->cliente->address }}</span> @endif<br>
                            @if($preventivo->cliente->phone || $preventivo->cliente->mobile)<span>Telefono:</span> <span style="font-weight: bold;">{{ $preventivo->cliente->phone ?? $preventivo->cliente->mobile }}</span> @endif<br>
                            @if($preventivo->cliente->email)<span>E-mail:</span> <span style="font-weight: bold;">{{ $preventivo->cliente->email }}</span> @endif<br>
                            @if($preventivo->tetto_salvato)<span>Tipologia tetto:</span> <span style="font-weight: bold;">{{ $preventivo->tetto_salvato ?? '' }}</span> @endif<br>
                            @if($preventivo->area_geografica_salvata)<span>Area:</span> <span style="font-weight: bold;">{{ $preventivo->area_geografica_salvata }}</span> @endif<br>
                            @if($preventivo->esposizione_salvata)<span>Esposizione:</span> <span style="font-weight: bold;">{{ $preventivo->esposizione_salvata }}</span> @endif
                        </div>
                    @endif
                </div>

            </div>

            <!-- Colonna Destra: Logo e Agente -->
            <div style="width:110mm; height: 117mm; display:inline-block; vertical-align: top;">
                @if($preventivo->agente)
                    <div style="font-size: 12px; color: #333; margin-top: 18px; margin-left: 30px;">
                        <span>Agente: <b>{{ $preventivo->agente->name ?? '' }} {{ $preventivo->agente->last_name ?? '' }}</b></span>
                    </div>
                @endif
                <div style="transform: translateY(45mm) translateX(20mm)">
                    <img src="{{ public_path('images/pdf/alfacom-logo.png') }}" alt="Alfacom Solar Logo" style="width: 65mm; height: auto;">
                </div>
                <div style="absolute; bottom: 0; left: 0; border-left: 1px solid gray; width: 0px; height: 70mm;">
                </div>
            </div>
        </div>

        <!-- Footer Aziendale -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 5mm 15mm; text-align: center; font-size: 9px; transform: translateX(-20mm);">
        ALFACOM S.R.L. | Viale Leonardo da Vinci, 8 | 95128 Catania (CT) | P.IVA: 05466900874 | Tel.: 095/8185744 | E-mail: info@gruppoalfacom.it
        </div>
    </div>

    <!-- Seconda Pagina - Contenuto -->
    <div style="height: 297mm; width: 200mm;">
        <!-- Header -->
        <div style="width: 210mm; padding-top: 10mm; padding-bottom: 10mm; position: relative; height: 20mm;">
            
            <div style="width:55mm; height: 15mm; background-color: #4BAE66; padding: 0 18mm; border-radius: 0mm 5mm 5mm 0mm;">
                <div style="color: white; font-size: 16px; font-weight: bold; transform: translateY(2.5mm)">
                    alfacomsolar.it
                </div>
            </div>

            <div style="position: absolute; top: 35%; left: 50%;">
                P R E V E N T I V O
            </div>

            <div style="position: absolute; top: 30%; right: 15mm;">
                <img src="{{ public_path('images/pdf/alfacom-logo.png') }}" alt="Alfacom Solar Logo" style="width: auto; height: 15mm;">
            </div>
        </div>

        <div style="height: 250mm; width: 200mm;">
            <!-- Oggetto -->
            <div style="width: 90%; height: 100mm; padding-left: 18mm; padding-right: 18mm;">
                @php
                    $potenza_totale_kwp = 0;
                    if ($preventivo->dettagliProdotti) {
                        foreach ($preventivo->dettagliProdotti as $dettaglioProdotto) {
                            // Usa kWp_salvato se disponibile, altrimenti calcola da prodotto
                            if (isset($dettaglioProdotto->kWp_salvato) && $dettaglioProdotto->kWp_salvato > 0) {
                                $potenza_totale_kwp += $dettaglioProdotto->kWp_salvato;
                            } elseif ($dettaglioProdotto->prodotto && isset($dettaglioProdotto->prodotto->potenza_kwp)) {
                                // Fallback al calcolo originale per retrocompatibilità
                                $potenza_totale_kwp += $dettaglioProdotto->quantita * $dettaglioProdotto->prodotto->potenza_kwp;
                            }
                        }
                    }
                @endphp
                <h3 style="margin-bottom: 10px;">OGGETTO:</h3>
                <p style="margin-bottom: 10px;"><b>Offerta per la realizzazione "chiavi in mano" di un impianto fotovoltaico per un totale di circa {{ number_format($potenza_totale_kwp, 1, ',', '.') }} kWp sulla copertura esistente.</b></p>
                <p><i>Spett.le Cliente</i></p>
                <p style="margin-bottom: 10px;">in riferimento ai colloqui intercorsi ed alla Sua richiesta, Le formuliamo la nostra proposta per l'esecuzione di quanto in oggetto alle seguenti principali condizioni. L'Azienda rimane a disposizione per ulteriori chiarimenti.</p>
                <p>La presente proposta commerciale comprende:</p>
                <ul style="padding-left: 20px;">
                    <li>
                        servizi di ingegneria relativi alla progettazione, al conseguimento di tutti i pareri necessari presso gli enti d'interesse, alla direzione lavori, alla sicurezza, al collaudo dell'impianto ed all'espletamento della pratica per 
                        <span style="border-bottom: 1px solid black; display: inline-block; padding-bottom: 1px;">l'accesso tramite il GSE alla convenzione "Scambio sul posto e/o Ritiro dedicato"</span>;
                    </li>
                    <li>fornitura dei componenti costituenti gli impianti offerti;</li>
                    <li>installazione dell'impianto a regola d'arte;</li>
                </ul>
            </div>

            <!-- Tabella Componenti -->
            <div style="width: 100%; height: auto; padding-left: 18mm; padding-right: 18mm;">
                <h3 style="margin-top: 20px; text-align: left;">Più in dettaglio:</h3>
                <h2 style="text-align: left; margin-bottom: 20px;">DESCRIZIONE DEI COMPONENTI DELL'IMPIANTO STANDARD:</h2>

                <table style="width: calc(210mm - 35mm); margin: 0 auto; border-collapse: collapse; font-size: 12px; transform: translateX(-12mm);">
                    <thead>
                        <tr style="background-color: #4BAE66; color: white;">
                            <th style="background-color: #4BAE66; padding: 8px; border: 1px solid #ddd; text-align: center; width: 15%;">QUANTITÀ</th>
                            <th style="background-color: #4BAE66; padding: 8px; border: 1px solid #ddd; text-align: center; width: 30%;">PRODOTTO</th>
                            <th style="background-color: #4BAE66; padding: 8px; border: 1px solid #ddd; text-align: center; width: 55%;">MARCA E DESCRIZIONE PRODOTTO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Righe dinamiche dai prodotti del preventivo -->
                        @if($preventivo->dettagliProdotti && $preventivo->dettagliProdotti->count() > 0)
                            @foreach($preventivo->dettagliProdotti as $dettaglioProdotto)
                        <tr style="background-color: #f9f9f9;">
                            <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">{{ $dettaglioProdotto->quantita ?? 1 }}</td>
                            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">{{ $dettaglioProdotto->nome_prodotto_salvato ?? 'Prodotto' }}</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">Modulo fotovoltaico: {{ $dettaglioProdotto->nome_prodotto_salvato ?? '' }}. Compreso di cavi e connettori.</td>
                        </tr>
                            @endforeach
                        @endif
                        <tr style="background-color: #f9f9f9;">
                            <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">1</td>
                            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Inverter</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">Inverter trifase da 10kWp. Marca HUAWEI, ZCS</td>
                        </tr>
                        <tr style="background-color: #f9f9f9;">
                            <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">1</td>
                            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Quadro elettrico DC/AC</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">Conforme alla legge italiana</td>
                        </tr>
                        <tr style="background-color: #f9f9f9;">
                            <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">1</td>
                            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Cavi solari di collegamento e cablaggi</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">Cavi solari lato DC e cavi AC di collegamento incluso cablaggi e tubazione apposita (entro 30mt)</td>
                        </tr>
                        <tr style="background-color: #f9f9f9;">
                            <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">1</td>
                            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Sistema di fissaggio</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">In alluminio complanare alla copertura del tetto. Marca Wurth e o similare.</td>
                        </tr>
                        <tr style="background-color: #f9f9f9;">
                            <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">1</td>
                            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Installazione</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">A regola d'arte</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        <div>
        <!-- Footer Aziendale -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 5mm 15mm; text-align: center; font-size: 9px; transform: translateX(-20mm);">
            ALFACOM S.R.L. | Viale Leonardo da Vinci, 8 | 95128 Catania (CT) | P.IVA: 05466900874 | Tel.: 095/8185744 | E-mail: info@gruppoalfacom.it
        </div>
        <!-- Contenuto principale -->
        <!-- <div class="page-content" style="position: relative; padding: 20mm 15mm; border:1px solid red;">
            <div style="page-break-inside: avoid; break-inside: avoid; position: relative; min-height: 257mm;">
            
             <div style="width: 100%; border:1px solid blue;">
                <div style="width:55mm; height: 18mm; background-color: #4BAE66; padding: 0 18mm; border-radius: 0mm 5mm 5mm 0mm;">
                        <div style="color: white; font-size: 16px; font-weight: bold; transform: translateY(4mm)">
                            alfacomsolar.it
                        </div>
                </div>
            </div>

            @if($preventivo->consumi && $preventivo->consumi->count() > 0)
            
            @php
                $consumo = $preventivo->consumi->first();
                $tipologiaBolletta = $consumo->tipologia_bolletta ?? 'mensile';
                $dettagli = [];
                if($consumo->dettagli_consumo_json) {
                    $decoded = is_string($consumo->dettagli_consumo_json) 
                        ? json_decode($consumo->dettagli_consumo_json, true) 
                        : $consumo->dettagli_consumo_json;
                    
                    if(is_array($decoded)) {
                        $dettagli = $decoded;
                    }
                }
            @endphp
            
            @if(strtolower($tipologiaBolletta) === 'mensile')
            <div class="table-subtitle">{{ ucfirst($tipologiaBolletta) }}:</div>
            @if($dettagli && count($dettagli) > 0)
            <table class="table-monthly">
                <thead>
                    <tr>
                        <th>Mese</th>
                        <th>F1</th>
                        <th>F2</th>
                        <th>F3</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dettagli as $item)
                    <tr>
                        <td>{{ $item['periodo'] ?? 'n' }}</td>
                        <td>{{ isset($item['f1_kwh']) ? number_format($item['f1_kwh'], 0, ',', '.') : 'n' }}</td>
                        <td>{{ isset($item['f2_kwh']) ? number_format($item['f2_kwh'], 0, ',', '.') : 'n' }}</td>
                        <td>{{ isset($item['f3_kwh']) ? number_format($item['f3_kwh'], 0, ',', '.') : 'n' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <table class="table-monthly">
                <thead>
                    <tr>
                        <th>Mese</th>
                        <th>F1</th>
                        <th>F2</th>
                        <th>F3</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Gennaio 2026</td>
                        <td>n</td>
                        <td>n</td>
                        <td>n</td>
                    </tr>
                    <tr>
                        <td>Febbraio 2026</td>
                        <td>n</td>
                        <td>n</td>
                        <td>n</td>
                    </tr>
                </tbody>
            </table>
            @endif
            @elseif(strtolower($tipologiaBolletta) === 'bimestrale')
            <div class="table-subtitle">{{ ucfirst($tipologiaBolletta) }}:</div>
            @if($dettagli && count($dettagli) > 0)
            <table class="table-bimonthly">
                <thead>
                    <tr>
                        <th>Mese</th>
                        <th>F1</th>
                        <th>F2</th>
                        <th>F3</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dettagli as $item)
                    <tr>
                        <td>{{ $item['periodo'] ?? 'n' }}</td>
                        <td>{{ isset($item['f1_kwh']) ? number_format($item['f1_kwh'], 0, ',', '.') : 'n' }}</td>
                        <td>{{ isset($item['f2_kwh']) ? number_format($item['f2_kwh'], 0, ',', '.') : 'n' }}</td>
                        <td>{{ isset($item['f3_kwh']) ? number_format($item['f3_kwh'], 0, ',', '.') : 'n' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <table class="table-bimonthly">
                <thead>
                    <tr>
                        <th>Mese</th>
                        <th>F1</th>
                        <th>F2</th>
                        <th>F3</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Gennaio 2026</td>
                        <td>n</td>
                        <td>n</td>
                        <td>n</td>
                    </tr>
                    <tr>
                        <td>Febbraio 2026</td>
                        <td>n</td>
                        <td>n</td>
                        <td>n</td>
                    </tr>
                </tbody>
            </table>
            @endif
            @endif
            </div> 

            
            <div style="position: absolute; bottom: -20mm; left: -15mm; right: -15mm; width: calc(100% + 30mm); page-break-inside: avoid; break-inside: avoid;">
            <div style="overflow: hidden; position: relative; width: 100%; min-height: 150mm;">
                <div style="display: inline-block; width: 48%; vertical-align: top;">
                    <div style="text-align: center; font-size: 16px; font-weight: bolder; margin-bottom: 5px; font-color: black; padding-left: 50px; text-align: left;">TOTALE <br>CONSUMI</div>
                    <div class="donut-chart-container" style="text-align: center;">
                        @php
                            // $consumo è già definito sopra nel blocco @if
                            $totaleF1 = 0;
                            $totaleF2 = 0;
                            $totaleF3 = 0;
                            
                            if($consumo && $consumo->dettagli_consumo_json) {
                                $decoded = is_string($consumo->dettagli_consumo_json) 
                                    ? json_decode($consumo->dettagli_consumo_json, true) 
                                    : $consumo->dettagli_consumo_json;
                                
                                if(is_array($decoded)) {
                                    foreach($decoded as $item) {
                                        $totaleF1 += isset($item['f1_kwh']) ? (float)$item['f1_kwh'] : 0;
                                        $totaleF2 += isset($item['f2_kwh']) ? (float)$item['f2_kwh'] : 0;
                                        $totaleF3 += isset($item['f3_kwh']) ? (float)$item['f3_kwh'] : 0;
                                    }
                                }
                            }
                            
                            $totale = $totaleF1 + $totaleF2 + $totaleF3;
                            $percentualeF1 = $totale > 0 ? ($totaleF1 / $totale) * 100 : 33.33;
                            $percentualeF2 = $totale > 0 ? ($totaleF2 / $totale) * 100 : 33.33;
                            $percentualeF3 = $totale > 0 ? ($totaleF3 / $totale) * 100 : 33.34;
                            
                            // Genera l'immagine del grafico usando l'istanza del servizio passata dal controller
                            // Il servizio traccia automaticamente i file temporanei e li pulisce dopo la generazione del PDF
                            $chartImagePath = isset($pdfService) ? $pdfService->generateDonutChartImage($percentualeF1, $percentualeF2, $percentualeF3) : (new \App\Services\PreventivoPdfService())->generateDonutChartImage($percentualeF1, $percentualeF2, $percentualeF3);
                        @endphp
                        <img src="{{ $chartImagePath }}" alt="Grafico consumi" style="width: 300px; height: 300px; display: block; margin: 0 auto;">
                    </div>
                    <div class="donut-legend" style="transform: translateY(35px) translateX(0px);">
                        <div class="donut-legend-item">
                            <div class="donut-legend-square" style="background-color: #e74c3c;"></div>
                            <span>F1: {{ number_format($percentualeF1, 1) }}%</span>
                        </div>
                        <div class="donut-legend-item">
                            <div class="donut-legend-square" style="background-color: #4BAE66;"></div>
                            <span>F2: {{ number_format($percentualeF2, 1) }}%</span>
                        </div>
                        <div class="donut-legend-item">
                            <div class="donut-legend-square" style="background-color: #1A233B;"></div>
                            <span>F3: {{ number_format($percentualeF3, 1) }}%</span>
                        </div>
                    </div>
                </div>

                @if($preventivo->dettagliProdotti && $preventivo->dettagliProdotti->count() > 0)
                <div style="display: inline-block; width: 48%; vertical-align: top; text-align: right; position: relative;">
                    <div class="costi-box" style="position: absolute; bottom: 0; right: 0; width: 65%; border-radius: 30px 30px 0 0; top: 0; padding-right: 15mm;">
                        <div class="costi-box-title">
                            LISTA COSTI<br>COMPLESSIVI
                        </div>
                        @php $totaleProdotti = 0; @endphp
                        @foreach($preventivo->dettagliProdotti as $prodotto)
                        @php 
                            $totaleProdotto = $prodotto->quantita * $prodotto->prezzo_unitario_salvato;
                            $totaleProdotti += $totaleProdotto;
                        @endphp
                        <div class="costi-item">
                            {{ $prodotto->nome_prodotto_salvato ?? 'Prodotto' }}: € {{ number_format($totaleProdotto, 2, ',', '.') }}
                        </div>
                        @endforeach
                        <div class="costi-totale">
                            TOTALE: € {{ number_format($totaleProdotti, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif
            </div>
        </div> -->
    </div>

    <!-- Terza Pagina - Simulazione Impianto e Produzione -->
    <div class="page page-break"  style="height: 297mm; width: 210mm;">
        <!-- Header -->
        <div style="width: 210mm; padding-top: 10mm; padding-bottom: 10mm; position: relative; height: 20mm;">
            
            <div style="width:55mm; height: 15mm; background-color: #4BAE66; padding: 0 18mm; border-radius: 0mm 5mm 5mm 0mm;">
                <div style="color: white; font-size: 16px; font-weight: bold; transform: translateY(2.5mm)">
                    alfacomsolar.it
                </div>
            </div>

            <div style="position: absolute; top: 35%; left: 50%;">
                P R E V E N T I V O
            </div>

            <div style="position: absolute; top: 30%; right: 15mm;">
                <img src="{{ public_path('images/pdf/alfacom-logo.png') }}" alt="Alfacom Solar Logo" style="width: auto; height: 15mm;">
            </div>
        </div>

        <div style="height: 250mm; width: 100%;">

            <!-- Box 1 -->
            <div style="height: 55mm; padding-left: 18mm; padding-right: 18mm;">
                <p style="margin-bottom: 10px; font-size: 12px;"><b>Le prestazioni da eseguirsi consistono nelle seguenti attività:</b></p>
                <ul style="padding-left: 20px; margin: 0; font-size: 11px; line-height: 1.6;">
                    <li>Sopralluogo tecnico assieme al Committente con conseguente redazione progetto preliminare;</li>
                    <li>Definizione materiali e progettazione esecutiva dell'impianto;</li>
                    <li>Elaborazione della domanda di connessione in rete sul portale E-DISTRIBUZIONE;</li>
                    <li>Assistenza tecnica in cantiere, direzione lavori e coordinamento della sicurezza in fase di realizzazione;</li>
                    <li>Montaggio a regola d'arte dell'impianto e collaudo finale;</li>
                </ul>
            </div>

            <!-- Tabella -->
            <div style="height: 45mm; padding-left: 25mm;">
                <table style="width: 178mm; margin: 0 auto; border-collapse: collapse; font-size: 12px; transform: translateX(-12mm);">
                    <thead>
                        <tr style="background-color: #4BAE66; color: white;">
                            <th colspan="2" style="background-color: #4BAE66; padding: 8px; border: 1px solid #ddd; text-align: center;">GARANZIA DEI COMPONENTI DELL'IMPIANTO (come da schede tecniche dei fornitori)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background-color: #f9f9f9;">
                            <td style="padding: 8px; border: 1px solid #ddd; width: 50%;">Moduli fotovoltaici / Inverter / Batterie</td>
                            <td style="padding: 8px; border: 1px solid #ddd; width: 50%;">Garanzia come da casa madre</td>
                        </tr>
                        <tr style="background-color: #f9f9f9;">
                            <td style="padding: 8px; border: 1px solid #ddd;">Assistenza tecnica</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">Come indicata della Legge italiana</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Banner immagine Tecnico -->
            <div style="height: 142mm; background-image: url('{{ public_path('images/pdf/Tecnico-min.webp') }}'); background-size: cover; background-position: center;">
            </div>
        <div>

        <!-- Footer Aziendale -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 5mm 15mm; text-align: center; font-size: 9px; transform: translateX(-20mm);">
            ALFACOM S.R.L. | Viale Leonardo da Vinci, 8 | 95128 Catania (CT) | P.IVA: 05466900874 | Tel.: 095/8185744 | E-mail: info@gruppoalfacom.it
        </div>
    </div>

    <!-- Quarta Pagina - Risparmi e Benefici Economici -->
    <div class="page page-break" style="height: 297mm; width: 210mm;">
        <!-- Header -->
        <div style="width: 210mm; padding-top: 10mm; padding-bottom: 10mm; position: relative; height: 20mm;">
            
            <div style="width:55mm; height: 15mm; background-color: #4BAE66; padding: 0 18mm; border-radius: 0mm 5mm 5mm 0mm;">
                <div style="color: white; font-size: 16px; font-weight: bold; transform: translateY(2.5mm)">
                    alfacomsolar.it
                </div>
            </div>

            <div style="position: absolute; top: 35%; left: 50%;">
                P R E V E N T I V O
            </div>

            <div style="position: absolute; top: 30%; right: 15mm;">
                <img src="{{ public_path('images/pdf/alfacom-logo.png') }}" alt="Alfacom Solar Logo" style="width: auto; height: 15mm;">
            </div>
        </div>

        <div style="height: 250mm; width: 100%;">
            <!-- Analisi e consumi -->
            <div style="height: 140mm; padding-left: 18mm; padding-right: 18mm;">
                <div style="height: 20mm; position: relative;">
                    <div style="position: absolute;  left: 0;">
                        <p style="font-size: 18px; font-weight: bold;">Analisi Consumi e Risparmio</p>
                    </div>
                    <div style="position: absolute; top:3mm; right: 0;">
                        <p style="font-size: 10px;">Questa sezione mostra i calcoli automatici basati sui dati</br>
                        forniti. È puramente informativa.</p>
                    </div>
                </div>

                <!-- Totale consumo annuo & Totale costi annui -->
                @php
                    $consumo = $preventivo->consumi ? $preventivo->consumi->first() : null;
                    $totaleConsumoAnnuo = $consumo->totale_consumo_annuo ?? 0;
                    $totaleCostiAnnuo = $consumo->totale_costi_annui ?? 0;
                    $consumoDiurnoAnnuo = $consumo->consumo_diurno_annuo ?? 0;
                    $consumoNotturnoAnnuo = $consumo->consumo_notturno_annuo ?? 0;
                    $capacitaBatteriaConsigliata = $consumo->capacita_batteria_consigliata ?? 0;
                    $potenzaImpiantoConsigliata = $consumo->potenza_impianto_consigliata ?? 0;
                @endphp
                <div style="width: 100%; height: 37mm; position: relative;">
                    
                    <div style="position: absolute; top: 4mm; left: 0;">
                        <div style="position: absolute; top: 5.8mm; left: 4.8mm; height: 10mm; width: 10mm; background-image: url('{{ public_path('images/pdf/Arrow_2.png') }}'); background-size: contain; background-position: center; background-repeat: no-repeat;"></div>
                        <span style="font-size: 12px; font-weight: bold; transform: translateX(5mm);">TOTALE CONSUMO <span style="color: #4BAE66; font-weight: bold;">ANNUO</span></span>
                        <div style="border:1px solid #e0e0e0; height: 22mm; width: 84mm; border-radius: 15px; margin-top: 2.5mm; background: linear-gradient(to bottom, #ffffff 0%,rgb(116, 13, 13) 100%); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <p style="font-size: 18px; font-weight: bold; transform: translateY(5mm) translateX(5mm);">{{ number_format($totaleConsumoAnnuo, 2, ',', '.') }} <span style="color: #999;">kWh</span></p>
                        </div>
                    </div>

                    <div style="position: absolute; top: 4mm; right: 0;">
                        <div style="position: absolute; top: 5.8mm; right: 70mm; height: 10mm; width: 10mm; background-image: url('{{ public_path('images/pdf/Arrow_1.png') }}'); background-size: contain; background-position: center; background-repeat: no-repeat;"></div>
                        <span style="font-size: 12px; font-weight: bold; transform: translateX(5mm);">TOTALE COSTI <span style="color: #4BAE66; font-weight: bold;">ANNUI</span></span>
                        <div style="border:1px solid #e0e0e0; height: 22mm; width: 84mm; border-radius: 15px; margin-top: 2.5mm; background: linear-gradient(to bottom, #ffffff 0%,rgb(206, 206, 206) 100%); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <p style="font-size: 18px; font-weight: bold; transform: translateY(5mm) translateX(5mm);">{{ number_format($totaleCostiAnnuo, 2, ',', '.') }} <span style="color: #999;">€</span></p>
                        </div>
                    </div>

                </div>
                <div style="width: 100%; height: 37mm; position: relative;">
                    
                    <div style="position: absolute; top: 4mm; left: 0;">
                        <div style="position: absolute; top: 5.8mm; left: 4.8mm; height: 10mm; width: 10mm; background-image: url('{{ public_path('images/pdf/Arrow_2.png') }}'); background-size: contain; background-position: center; background-repeat: no-repeat;"></div>
                        <span style="font-size: 12px; font-weight: bold; transform: translateX(5mm);">CONSUMO <span style="color: #4BAE66; font-weight: bold;">DIURNO ANNUO</span></span>
                        <div style="border:1px solid #e0e0e0; height: 22mm; width: 84mm; border-radius: 15px; margin-top: 2.5mm; background: linear-gradient(to bottom, #ffffff 0%,rgb(116, 13, 13) 100%); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <p style="font-size: 18px; font-weight: bold; transform: translateY(5mm) translateX(5mm);">{{ number_format($consumoDiurnoAnnuo, 2, ',', '.') }} <span style="color: #999;">kWh</span></p>
                        </div>
                    </div>

                    <div style="position: absolute; top: 4mm; right: 0;">
                        <div style="position: absolute; top: 5.8mm; right: 70mm; height: 10mm; width: 10mm; background-image: url('{{ public_path('images/pdf/Arrow_1.png') }}'); background-size: contain; background-position: center; background-repeat: no-repeat;"></div>
                        <span style="font-size: 12px; font-weight: bold; transform: translateX(5mm);">CONSUMO <span style="color: #4BAE66; font-weight: bold;">NOTTURNO ANNUO</span></span>
                        <div style="border:1px solid #e0e0e0; height: 22mm; width: 84mm; border-radius: 15px; margin-top: 2.5mm; background: linear-gradient(to bottom, #ffffff 0%,rgb(206, 206, 206) 100%); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <p style="font-size: 18px; font-weight: bold; transform: translateY(5mm) translateX(5mm);">{{ number_format($consumoNotturnoAnnuo, 2, ',', '.') }} <span style="color: #999;">kWh</span></p>
                        </div>
                    </div>

                </div>
                <div style="width: 100%; height: 37mm; position: relative;">
                    
                    <div style="position: absolute; top: 4mm; left: 0;">
                        <div style="position: absolute; top: 5.8mm; left: 4.8mm; height: 10mm; width: 10mm; background-image: url('{{ public_path('images/pdf/Arrow_3.png') }}'); background-size: contain; background-position: center; background-repeat: no-repeat;"></div>
                        <span style="font-size: 12px; font-weight: bold; transform: translateX(5mm);">CAPACITÀ BATTERIA <span style="color: #4BAE66; font-weight: bold;">CONSIGLIATA</span></span>
                        <div style="border:1px solid #e0e0e0; height: 22mm; width: 84mm; border-radius: 15px; margin-top: 2.5mm; background: linear-gradient(to bottom, #ffffff 0%,rgb(116, 13, 13) 100%); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <p style="font-size: 18px; font-weight: bold; transform: translateY(5mm) translateX(5mm);">{{ number_format($capacitaBatteriaConsigliata, 2, ',', '.') }} <span style="color: #999;">kWh</span></p>
                        </div>
                    </div>

                    <div style="position: absolute; top: 4mm; right: 0;">
                        <div style="position: absolute; top: 5.8mm; right: 70mm; height: 10mm; width: 10mm; background-image: url('{{ public_path('images/pdf/Arrow_3.png') }}'); background-size: contain; background-position: center; background-repeat: no-repeat;"></div>
                        <span style="font-size: 12px; font-weight: bold; transform: translateX(5mm);">IMPIANTO CHE <span style="color: #4BAE66; font-weight: bold;">PAREGGIA I CONSUMI</span></span>
                        <div style="border:1px solid #e0e0e0; height: 22mm; width: 84mm; border-radius: 15px; margin-top: 2.5mm; background: linear-gradient(to bottom, #ffffff 0%,rgb(206, 206, 206) 100%); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <p style="font-size: 18px; font-weight: bold; transform: translateY(5mm) translateX(5mm);">{{ number_format($potenzaImpiantoConsigliata, 2, ',', '.') }} <span style="color: #999;">kWp</span></p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Grafico -->
            <div style="height: 100mm; padding-left: 18mm; padding-right: 18mm;">
                <div style=" width: 100%; height: 100%; background-image: url('{{ public_path('images/pdf/Serie.png') }}'); background-size: contain; background-position: center; background-repeat: no-repeat;">
                    
                </div>
            </div>
        </div>

       <!-- Footer Aziendale -->
       <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 5mm 15mm; text-align: center; font-size: 9px; transform: translateX(-20mm);">
            ALFACOM S.R.L. | Viale Leonardo da Vinci, 8 | 95128 Catania (CT) | P.IVA: 05466900874 | Tel.: 095/8185744 | E-mail: info@gruppoalfacom.it
        </div>
    </div>

    <!-- Quinta Pagina - Offerta Economica -->
    <div class="page page-break" style="height: 297mm; width: 210mm;">
        <!-- Header -->
        <div style="width: 210mm; padding-top: 10mm; padding-bottom: 10mm; position: relative; height: 20mm;">
            
            <div style="width:55mm; height: 15mm; background-color: #4BAE66; padding: 0 18mm; border-radius: 0mm 5mm 5mm 0mm;">
                <div style="color: white; font-size: 16px; font-weight: bold; transform: translateY(2.5mm)">
                    alfacomsolar.it
                </div>
            </div>

            <div style="position: absolute; top: 35%; left: 50%;">
                P R E V E N T I V O
            </div>

            <div style="position: absolute; top: 30%; right: 15mm;">
                <img src="{{ public_path('images/pdf/alfacom-logo.png') }}" alt="Alfacom Solar Logo" style="width: auto; height: 15mm;">
            </div>
        </div>

        <!-- Contenuto principale -->
        <div style="height: 250mm; width: 100%;">
        <div style="height: 65mm; padding-left: 18mm; padding-right: 18mm;">
            <div style="width: 100%; height: 28mm; position: relative;">
                        
                <div style="position: absolute; top: 0mm; left: 0;">
                    <span style="font-size: 12px; font-weight: bold; transform: translateX(5mm);">RISPARMIO DA AUTOCONSUMO</span>
                    <div style="border:1px solid #e0e0e0; height: 18mm; width: 84mm; border-radius: 15px; margin-top: 2.5mm; background: linear-gradient(to bottom, #ffffff 0%,rgb(116, 13, 13) 100%); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <p style="font-size: 18px; font-weight: bold; transform: translateY(4mm) translateX(5mm);">{{ number_format($preventivo->risparmio_autoconsumo_annuo ?? 0, 2, ',', '.') }} <span style="color: #999;">€</span></p>
                    </div>
                </div>

                <div style="position: absolute; top: 0mm; right: 0;">
                    <span style="font-size: 12px; font-weight: bold; transform: translateX(5mm);">VENDITA ECCEDENZE (RID)</span>
                    <div style="border:1px solid #e0e0e0; height: 18mm; width: 84mm; border-radius: 15px; margin-top: 2.5mm; background: linear-gradient(to bottom, #ffffff 0%,rgb(206, 206, 206) 100%); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <p style="font-size: 18px; font-weight: bold; transform: translateY(4mm) translateX(5mm);">{{ number_format($preventivo->vendita_eccedenze_rid_annua ?? 0, 2, ',', '.') }} <span style="color: #999;">€</span></p>
                    </div>
                </div>

            </div>
            <div style="width: 100%; height: 28mm; position: relative;">
                
                <div style="position: absolute; top: 0mm; left: 0;">
                    <span style="font-size: 12px; font-weight: bold; transform: translateX(5mm);">INCENTIVO CER</span>
                    <div style="border:1px solid #e0e0e0; height: 18mm; width: 84mm; border-radius: 15px; margin-top: 2.5mm; background: linear-gradient(to bottom, #ffffff 0%,rgb(116, 13, 13) 100%); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <p style="font-size: 18px; font-weight: bold; transform: translateY(4mm) translateX(5mm);">{{ number_format($preventivo->incentivo_cer_annuo ?? 0, 2, ',', '.') }} <span style="color: #999;">€</span></p>
                    </div>
                </div>

                <div style="position: absolute; top: 0mm; right: 0;">
                    <span style="font-size: 12px; font-weight: bold; transform: translateX(5mm);">DETRAZIONE FISCALE</span>
                    <div style="border:1px solid #e0e0e0; height: 18mm; width: 84mm; border-radius: 15px; margin-top: 2.5mm; background: linear-gradient(to bottom, #ffffff 0%,rgb(206, 206, 206) 100%); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <p style="font-size: 18px; font-weight: bold; transform: translateY(4mm) translateX(5mm);">{{ number_format($preventivo->detrazione_fiscale_annua ?? 0, 2, ',', '.') }} <span style="color: #999;">€</span></p>
                    </div>
                </div>

                </div>
        </div>

        <!-- BUSINESS PLAN -->
        <div style="width: 100%; height: 160mm; position: relative; padding-left: 18mm; padding-right: 18mm;">
            <span style="font-size: 20px; font-weight: bold; margin-bottom: 3mm;">
                BUSINESS <span style="color: #4BAE66;">PLAN</span>
            </span>
            <table class="business-plan-table" style="width: 175mm;">
                <thead>
                    <tr>
                        <th style="text-align: center; border: 1px solid #e0e0e0;">Anno</th>
                        <th style="text-align: center; border: 1px solid #e0e0e0;">Rata</th>
                        <th style="text-align: center; border: 1px solid #e0e0e0;">Costo Ass.</th>
                        <th style="text-align: center; border: 1px solid #e0e0e0;">Costo Man.</th>
                        <th style="text-align: center; border: 1px solid #e0e0e0;">Risparmio <br>Bolletta</th>
                        <th style="text-align: center; border: 1px solid #e0e0e0;">Eccedenze <br> (RID)</th>
                        <th style="text-align: center; border: 1px solid #e0e0e0;">Incentivo   CER</th>
                        <th style="text-align: center; border: 1px solid #e0e0e0;">Incentivo <br> PNNR</th>
                        <th style="text-align: center; border: 1px solid #e0e0e0;">Detrazione</th>
                        <th style="text-align: center; border: 1px solid #e0e0e0;">Sconto</th>
                        <th style="text-align: center; border: 1px solid #e0e0e0;">Flussi <br> cassa</th>
                        <th style="text-align: center; border: 1px solid #e0e0e0;">Flussi <br> cumulati</th>
                    </tr>
                </thead>
                <tbody>
                    @if($preventivo->dettagliBusinessPlan && $preventivo->dettagliBusinessPlan->count() > 0)
                        @foreach($preventivo->dettagliBusinessPlan as $bp)
                        <tr>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">{{ $bp->anno_simulazione }}</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">€ {{ number_format($bp->costo_annuo_investimento, 2, ',', '.') }}</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">€ {{ number_format($bp->costo_annuo_assicurazione, 2, ',', '.') }}</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">€ {{ number_format($bp->costo_annuo_manutenzione, 2, ',', '.') }}</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">€ {{ number_format($bp->ricavo_risparmio_bolletta, 2, ',', '.') }}</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">€ {{ number_format($bp->ricavo_vendita_eccedenze, 2, ',', '.') }}</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">€ {{ number_format($bp->ricavo_incentivo_cer, 2, ',', '.') }}</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">€ {{ number_format($bp->incentivo_pnnr ?? 0, 2, ',', '.') }}</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">€ {{ number_format($bp->detrazione_fiscale ?? 0, 2, ',', '.') }}</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">€ {{ number_format($bp->sconto ?? 0, 2, ',', '.') }}</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">€ {{ number_format($bp->flusso_cassa_annuo, 2, ',', '.') }}</td>
                            <td class="text-center text-bold" style="padding: 7.2px 3px; text-align: center;">€ {{ number_format($bp->flusso_cassa_cumulato, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    @else
                        @for($i = 1; $i <= 20; $i++)
                        <tr>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">{{ $i }}</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">-</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">-</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">-</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">-</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">-</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">-</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">-</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">-</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">-</td>
                            <td class="text-center" style="padding: 7.2px 3px; text-align: center;">-</td>
                            <td class="text-center text-bold" style="padding: 7.2px 3px; text-align: center;">-</td>
                        </tr>
                        @endfor
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Footer Aziendale -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 5mm 15mm; text-align: center; font-size: 9px; transform: translateX(-20mm);">
            ALFACOM S.R.L. | Viale Leonardo da Vinci, 8 | 95128 Catania (CT) | P.IVA: 05466900874 | Tel.: 095/8185744 | E-mail: info@gruppoalfacom.it
        </div>
    </div>

    <!-- Sesta Pagina - Manutenzione e Assicurazione -->
    <div class="page page-break" style="height: 297mm; width: 210mm;">
        <!-- Header -->
        <div style="width: 210mm; padding-top: 10mm; padding-bottom: 10mm; position: relative; height: 20mm;">
            
            <div style="width:55mm; height: 15mm; background-color: #4BAE66; padding: 0 18mm; border-radius: 0mm 5mm 5mm 0mm;">
                <div style="color: white; font-size: 16px; font-weight: bold; transform: translateY(2.5mm)">
                    alfacomsolar.it
                </div>
            </div>

            <div style="position: absolute; top: 35%; left: 50%;">
                P R E V E N T I V O
            </div>

            <div style="position: absolute; top: 30%; right: 15mm;">
                <img src="{{ public_path('images/pdf/alfacom-logo.png') }}" alt="Alfacom Solar Logo" style="width: auto; height: 15mm;">
            </div>
        </div>

        <!-- Contenuto principale -->
        <div style="height: 250mm; width: 100%;">
            <div style="width:100%; height: 78mm; position: relative; padding-left: 18mm; padding-right: 18mm;">
                <span style="font-size: 24px; font-weight: bold; margin-bottom: 8px; transform: translateY(-10mm);">
                    la nostra OFFERTA <span style="color: #4BAE66;">ECONOMICA</span>
                <div style="width:60mm; height:40mm; position: absolute; top: 25mm; left: 18mm; border-radius: 4mm; background-image: url('{{ public_path('images/pdf/rectangular-gradient.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">

                </div>
                <div style="width:70mm; height:80mm; position: absolute; top: 10mm; left: 55mm; background-image: url('{{ public_path('images/pdf/Contatore-min.png') }}'); background-size: contain; background-position: center; background-repeat: no-repeat;">

                </div>
                <div style="width:65mm; height:60mm; position: absolute; top: 10mm; right: 50mm; background-image: url('{{ public_path('images/pdf/Sopralluogo_gratuito.png') }}'); background-size: contain; background-position: center; background-repeat: no-repeat;">

                </div>

            </div>
            <div style="width:100%; height: 60mm; position: relative;">
                <!-- Cosa è incluso -->
                <div style="position: absolute; top: 5mm; left: 18mm; width: 83mm;">
                    <p style="font-size: 20px; font-weight: bold; margin-bottom: 8px;">
                        Cosa è <span style="color: #4BAE66;">incluso</span>
                    </p>
                    <ul style="font-size: 12px; margin: 0; padding-left: 20px;">
                        <li style="line-height: 1.0;">Installazione a regola d'arte</li>
                        <li style="line-height: 1.0;">Progettazione dell'impianto</li>
                        <li style="line-height: 1.0;">Pratiche autorizzative comunali e distributore di rete</li>
                        <li style="line-height: 1.0;">Fornitura e posa in opera di materiali</li>
                        <li style="line-height: 1.0;">Collaudo e certificazione L.81/10</li>
                        <li style="line-height: 1.0;">Attivazione sistema di monitoraggio</li>
                        <li style="line-height: 1.0;">Dichiarazione di conformità (D.M. 37/08)</li>
                        <li style="line-height: 1.0;">Pratiche convenzione Ritiro Dedicato GSE</li>
                    </ul>
                </div>
                <!-- Cosa è escluso -->
                <div style="position: absolute; top: 5mm; right: 18mm; width: 83mm;">
                    <p style="font-size: 20px; font-weight: bold; margin-bottom: 8px;">
                        Cosa è <span style="color: #e74c3c;">escluso</span>
                    </p>
                    <ul style="font-size: 12px; margin: 0; padding-left: 20px;">
                        <li style="line-height: 1.0;">Linea vita ed eventuale noleggio Gru</li>
                        <li style="line-height: 1.0;">Distanza zona inverter/contatore oltre i 15mt</li>
                        <li style="line-height: 1.0;">Eventuali opere murarie</li>
                        <li style="line-height: 1.0;">Integrazioni e/o modifiche sulle coperture</li>
                        <li style="line-height: 1.0;">Oneri di gestione per pratiche comunali</li>
                        <li style="line-height: 1.0;">Corrispettivo per ottenimento e accettazione del preventivo di connessione del Distributore di Rete</li>
                    </ul>
                </div>
            </div>
            <div style="width:100%; height: 75mm; position: relative;background-image: url('{{ public_path('images/pdf/rectangular-gradient-large.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
                @php
                    // Recupera il nome del prodotto selezionato
                    $nome_prodotto_selezionato = '';
                    if ($preventivo->dettagliProdotti && $preventivo->dettagliProdotti->count() > 0) {
                        $primoProdotto = $preventivo->dettagliProdotti->first();
                        $nome_prodotto_selezionato = $primoProdotto->nome_prodotto_salvato ?? '';
                    }
                    
                    // Decodifica dati bonifico
                    $bonificoData = [];
                    if ($preventivo->bonifico_data_json) {
                        $decoded = is_string($preventivo->bonifico_data_json) 
                            ? json_decode($preventivo->bonifico_data_json, true) 
                            : $preventivo->bonifico_data_json;
                        if (is_array($decoded)) {
                            $bonificoData = $decoded;
                        }
                    }
                    
                    // Decodifica dati finanziamento
                    $finanziamentoData = [];
                    if ($preventivo->finanziamento_data_json) {
                        $decoded = is_string($preventivo->finanziamento_data_json) 
                            ? json_decode($preventivo->finanziamento_data_json, true) 
                            : $preventivo->finanziamento_data_json;
                        if (is_array($decoded)) {
                            $finanziamentoData = $decoded;
                        }
                    }
                    
                    // Calcola totale offerta economica
                    // Formula: bonifico_data_json.amount + finanziamento_data_json.rate_import * number_of_rate
                    $totale_offerta = 0;
                    
                    // Aggiungi importo bonifico se presente
                    if (isset($bonificoData['amount'])) {
                        $totale_offerta += floatval($bonificoData['amount']);
                    }
                    
                    // Aggiungi importo finanziamento se presente
                    if (isset($finanziamentoData['rate_import']) && isset($finanziamentoData['number_of_rate'])) {
                        $rateImport = floatval($finanziamentoData['rate_import']);
                        $numberOfRate = intval($finanziamentoData['number_of_rate']);
                        $totale_offerta += $rateImport * $numberOfRate;
                    }
                    
                    // Calcolo rate basato su rate_import e number_of_rate
                    $rata36 = 0;
                    $rata60 = 0;
                    $rata120 = 0;
                    $importoFinanziamento = $totale_offerta;
                    
                    if (isset($finanziamentoData['rate_import']) && isset($finanziamentoData['number_of_rate'])) {
                        $rateImport = floatval($finanziamentoData['rate_import']);
                        $numberOfRate = intval($finanziamentoData['number_of_rate']);
                        
                        // Calcola il capitale totale
                        $capitale = $numberOfRate * $rateImport;
                        
                        // Calcola le rate per 36, 60, 120
                        $rata36 = $capitale / 36;
                        $rata60 = $capitale / 60;
                        $rata120 = $capitale / 120;
                        
                        $importoFinanziamento = $capitale;
                    } else {
                        // Fallback ai valori precedenti se non ci sono i nuovi campi
                        $rata36 = $finanziamentoData['rata_36'] ?? $finanziamentoData['36'] ?? 0;
                        $rata60 = $finanziamentoData['rata_60'] ?? $finanziamentoData['60'] ?? 0;
                        $rata120 = $finanziamentoData['rata_120'] ?? $finanziamentoData['120'] ?? 0;
                        $importoFinanziamento = $finanziamentoData['importo'] ?? $totale_offerta;
                    }
                @endphp
                
                <!-- Titolo -->
                <div style="position: absolute; top: 0.5mm; left: 18mm;">
                    <h2 style="font-size: 30px; font-weight: bold; color: white; margin: 0; font-family: sans-serif;">SIMULAZIONE COSTI</h2>
                </div>
                
                <!-- Testo esplicativo -->
                <div style="position: absolute; top: 3mm; right: 18mm; width: 80mm; line-height: 1.0mm;">
                    <p style="font-size: 8px; color: white; margin: 0; line-height: 1.4;">I dati e i risultati riportati nella presente analisi e nel business plan allegato, seppure inerenti per quanto possibile ai consumi reali del cliente in oggetto, sono sviluppati nell'ambito di una simulazione di producibilità basate su medie tabellari e non su analisi specifiche sui luoghi di installazione e quindi sono da ritenersi puramente indicativi e in alcun modo vincolanti vicino al prezzo finale della simulazione.</p>
                </div>
                
                <!-- Prodotto - Titolo -->
                <div style="position: absolute; top: 25mm; left: 18mm; padding-left: 2mm;">
                    <p style="font-size: 12px; color: white; font-weight: bold; margin: 0;">PRODOTTO</p>
                </div>
                <!-- Prodotto - Box -->
                <div style="position: absolute; top: 31mm; left: 18mm; width: 110mm; height: 10mm; background-color: white; border-radius: 16px; padding: 2mm; text-align: left;">
                    <p style="font-size: 14px; font-weight: bold; color: black; margin: 0; line-height: 10mm; transform: translateY(-4mm); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $nome_prodotto_selezionato }}</p>
                </div>
                
                <!-- OFFERTA ECONOMICA - Titolo -->
                <div style="position: absolute; top: 25mm; right: 18mm; width: 55mm; padding-left: 2mm;">
                    <p style="font-size: 12px; color: white; font-weight: bold; margin: 0; transform: translateX(4mm);">OFFERTA ECONOMICA</p>
                </div>
                <!-- OFFERTA ECONOMICA - Box -->
                <div style="position: absolute; top: 31mm; right: 18mm; width: 50mm; height: 10mm; background-color: white; border-radius: 16px; padding: 2mm; text-align: left;">
                    <p style="font-size: 18px; font-weight: bold; color: black; margin: 0; line-height: 10mm; transform: translateY(-3mm);">{{ number_format($totale_offerta, 2, ',', '.') }} <span style="font-weight: normal;">€</span></p>
                </div>
                
                @if($preventivo->finanziamento_data_json)
                <!-- Importo FINANZIAMENTO - Titolo -->
                <div style="position: absolute; top: 54mm; left: 20mm;">
                    <p style="font-size: 14px; color: white; font-weight: bold; margin: 0; ">Importo FINANZIAMENTO</p>
                </div>
                
                <!-- 36 RATE - Titolo -->
                <div style="position: absolute; top: 48mm; left: 65mm; width: 40mm;">
                    <p style="font-size: 10px; color: white; margin: 0 0 2mm 0; text-align: center; font-weight: bold;">36 RATE</p>
                </div>
                <!-- 36 RATE - Box -->
                <div style="position: absolute; top: 53mm; left: 77mm; width: 32mm; height: 6mm; background-color: white; border-radius: 16px; padding: 2mm; text-align: left;">
                    <p style="font-size: 14px; font-weight: bold; color: black; margin: 0; line-height: 8mm; transform: translateY(-4mm);">{{ number_format($rata36, 2, ',', '.') }} <span style="font-weight: normal;">€</span></p>
                </div>
                
                <!-- 60 RATE - Titolo -->
                <div style="position: absolute; top: 48mm; left: 105mm; width: 40mm;">
                    <p style="font-size: 10px; color: white; margin: 0 0 2mm 0; text-align: center; font-weight: bold;">60 RATE</p>
                </div>
                <!-- 60 RATE - Box -->
                <div style="position: absolute; top: 53mm; left: 117mm; width: 32mm; height: 6mm; background-color: white; border-radius: 16px; padding: 2mm; text-align: left;">
                    <p style="font-size: 14px; font-weight: bold; color: black; margin: 0; line-height: 8mm; transform: translateY(-4mm);">{{ number_format($rata60, 2, ',', '.') }} <span style="font-weight: normal;">€</span></p>
                </div>
                
                <!-- 120 RATE - Titolo -->
                <div style="position: absolute; top: 48mm; left: 144mm; width: 46mm;">
                    <p style="font-size: 10px; color: white; margin: 0 0 2mm 0; text-align: center; font-weight: bold;">120 RATE</p>
                </div>
                <!-- 120 RATE - Box -->
                <div style="position: absolute; top: 53mm; left: 157mm; width: 32mm; height: 6mm; background-color: white; border-radius: 16px; padding: 2mm; text-align: left;">
                    <p style="font-size: 14px; font-weight: bold; color: black; margin: 0; line-height: 8mm; transform: translateY(-4mm);">{{ number_format($rata120, 2, ',', '.') }} <span style="font-weight: normal;">€</span></p>
                </div>
                @endif
            </div>
        </div>
        <!-- <span style="font-size: 8px; margin-bottom: 8px; padding-left: 18mm; transform: translateY(-32mm);">
            *In caso di KO tecnico e/o amministrativo o di non accettazione della finanziaria
        </span> -->
        <!-- Footer Aziendale -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 5mm 15mm; text-align: center; font-size: 9px; transform: translateX(-20mm);">
            ALFACOM S.R.L. | Viale Leonardo da Vinci, 8 | 95128 Catania (CT) | P.IVA: 05466900874 | Tel.: 095/8185744 | E-mail: info@gruppoalfacom.it
        </div>
    </div>

    <!-- Settima Pagina - Business Plan -->
    <div class="page page-break" style="height: 297mm; width: 210mm;">
         <!-- Header -->
         <div style="width: 210mm; padding-top: 10mm; padding-bottom: 10mm; position: relative; height: 20mm;">
            
            <div style="width:55mm; height: 15mm; background-color: #4BAE66; padding: 0 18mm; border-radius: 0mm 5mm 5mm 0mm;">
                <div style="color: white; font-size: 16px; font-weight: bold; transform: translateY(2.5mm)">
                    alfacomsolar.it
                </div>
            </div>

            <div style="position: absolute; top: 35%; left: 50%;">
                P R E V E N T I V O
            </div>

            <div style="position: absolute; top: 30%; right: 15mm;">
                <img src="{{ public_path('images/pdf/alfacom-logo.png') }}" alt="Alfacom Solar Logo" style="width: auto; height: 15mm;">
            </div>
        </div>

        <!-- Contenuto principale -->
        <div style="height: 250mm; width: 100%;">
            <div style="height: 120mm; width: 100%; position: relative;">
                <!-- CONDIZIONI GENERALI -->
                <div style="position: absolute; top: 5mm; left: 18mm; padding-right: 18mm;">
                    <h3 style="font-size: 18px; font-weight: bold; margin: 0 0 5mm 0;">CONDIZIONI GENERALI</h3>
                    <div style="margin-bottom: 2mm;">
                        <p style="font-size: 13px; font-weight: bold; margin: 0 0 0mm 0;">Validità e Tempistica:</p>
                        <p style="font-size: 12px; margin: 0 0 0mm 0; line-height: 1.2;">
                            L'offerta è valida per <strong>30 giorni</strong> dalla data di emissione.
                        </p>
                        <p style="font-size: 12px; margin: 0; line-height: 1.2;">
                            L'installazione è garantita entro <strong>30 giorni lavorativi</strong>. Tale garanzia è subordinata alla disponibilità dei materiali da parte dei distributori e al puntuale rispetto degli accordi di pagamento.
                        </p>
                    </div>
                </div>

                <!-- MODALITA' DI PAGAMENTO -->
                <div style="position: absolute; top: 47mm; left: 18mm;">
                    <h3 style="font-size: 13px; font-weight: bold;">MODALITÀ DI PAGAMENTO</h3>
                    <div style="margin-bottom: 2mm;">
                        <p style="font-size: 12px; margin: 0; line-height: 1.4;">
                            La modalità di pagamento accettata per i servizi è il <strong>Bonifico Bancario</strong>.
                        </p>
                    </div>
                </div>

                <!-- PAGAMENTI -->
                <div style="position: absolute; top: 65mm; left: 18mm; padding-right: 18mm;">
                    <h3 style="font-size: 13px; font-weight: bold; margin: 0 0 2mm 0;">PAGAMENTI</h3>
                    <div style="margin-bottom: 2mm;">
                        <p style="font-size: 12px; margin: 0 0 2mm 0; line-height: 1.4;">
                            I pagamenti devono essere effettuati secondo le seguenti modalità:
                        </p>
                        <ul style="font-size: 12px; margin: 0; padding-left: 20px;">
                            <li style="line-height: 1.0;"><strong>100%</strong> del contributo per l'iscrizione alla CER è dovuto alla firma dell'accordo.</li>
                            <li style="line-height: 1.0;"><strong>30%</strong> dell'importo totale dell'impianto fotovoltaico è dovuto all'ottenimento e accettazione del preventivo di connessione di E-Distribuzione.</li>
                            <li style="line-height: 1.0;"><strong>50%</strong> dell'importo totale dell'impianto fotovoltaico è dovuto all'ordine del materiale, prima della consegna del materiale in cantiere.</li>
                            <li style="line-height: 1.0;"><strong>20%</strong> dell'importo totale dell'impianto fotovoltaico è dovuto al completamento dell'installazione, al collaudo finale e prima del collegamento a ENEL.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div style="height: 120mm; width: 100%; background-image: url('{{ public_path('images/pdf/FreeEnergy-min.webp') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;" ></div>
            
        </div>

        <!-- Footer Aziendale -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 5mm 15mm; text-align: center; font-size: 9px; transform: translateX(-20mm);">
            ALFACOM S.R.L. | Viale Leonardo da Vinci, 8 | 95128 Catania (CT) | P.IVA: 05466900874 | Tel.: 095/8185744 | E-mail: info@gruppoalfacom.it
        </div>
    </div>
    
    <!-- Ottava Pagina - Informativa Privacy (Parte 1) -->
    <div class="page page-break" style="height: 297mm; width: 210mm;">
         <!-- Header -->
         <div style="width: 210mm; padding-top: 10mm; padding-bottom: 10mm; position: relative; height: 20mm;">
            
            <div style="width:55mm; height: 15mm; background-color: #4BAE66; padding: 0 18mm; border-radius: 0mm 5mm 5mm 0mm;">
                <div style="color: white; font-size: 16px; font-weight: bold; transform: translateY(2.5mm)">
                    alfacomsolar.it
                </div>
            </div>

            <div style="position: absolute; top: 35%; left: 50%;">
                P R E V E N T I V O
            </div>

            <div style="position: absolute; top: 30%; right: 15mm;">
                <img src="{{ public_path('images/pdf/alfacom-logo.png') }}" alt="Alfacom Solar Logo" style="width: auto; height: 15mm;">
            </div>
        </div>

        <!-- Contenuto principale -->
        <div style="height: 250mm; width: 100%; padding: 0 18mm;">
            <!-- Titolo -->
            <div style="width: 100%; margin-bottom: 3mm;">
                <div style="font-size: 14px; font-weight: bold; margin-bottom: 1mm;">INFORMATIVA SUL TRATTAMENTO DEI DATI PERSONALI</div>
                <div style="font-size: 10px;">(Regolamento UE 2016/679, di seguito Regolamento)</div>
            </div>
            
            <!-- Box container principale -->
            <div style="width: 178mm; height: 220mm; position: relative;">
                
                <!-- Colonna sinistra -->
                <div style="position: absolute; top: 0; left: 0; width: 85mm; height: 220mm; font-size: 8.5px; line-height: 1.8mm; text-align: justify; padding-right: 2mm;">
                    <p style="margin-bottom: 1.5mm;">Energia Italia S.p.A., con sede in C.da Grotta Affumata snc - 92024 Canicattì (AG), dedica particolare impegno alla tutela dei Suoi dati personali. Questo impegno si riflette nel valore e nella fiducia con le quali gestisce le relazioni che intercorrono con i propri Clienti, Dipendenti, Fornitori e Partner Commerciali.</p>
                    <p style="margin-bottom: 1.5mm;">Con la presente informativa Energia Italia S.p.A. desidera offrire una visione chiara e trasparente di quali informazioni raccoglie e tratta nell'ambito dei rapporti pre-contrattuali o contrattuali con i nostri Clienti in ottemperanza al Regolamento Generale UE 2016/679 relativo alla protezione delle persone fisiche con riguardo al trattamento dei dati personali (di seguito Regolamento).</p>
                    
                    <p style="font-weight: bold; margin: 1.5mm 0 0.5mm 0;">1. TITOLARE DEL TRATTAMENTO</p>
                    <p style="margin-bottom: 1.5mm;">Il Titolare del trattamento dei Suoi dati personali è Energia Italia S.p.A, C.da Grotta Affumata snc – 92024 Canicattì (AG) – Tel. +39 0922 858410, Fax +39 0922 737398, Email info@energiaitaliaspa.it, Pec energiaitaliaspa@pec.it.</p>
                    
                    <p style="font-weight: bold; margin: 1.5mm 0 0.5mm 0;">2. DEFINIZIONE DI DATO PERSONALE E CATEGORIE DI DATI PERSONALI TRATTATI</p>
                    <p style="margin-bottom: 0.5mm;">Per "Dato Personale" si intende ogni informazione idonea a identificare, direttamente o indirettamente, una persona fisica, in questo caso Lei, nostro Cliente, che utilizza i Servizi e prodotti offerti da Energia Italia S.p.A.</p>
                    <p style="margin-bottom: 0.5mm;">1 - In particolare, la Società tratta Dati Personali non biometrici:</p>
                    <p style="margin-left: 2mm; margin-bottom: 0.3mm;">• dati anagrafici (es. nome, cognome, indirizzo);</p>
                    <p style="margin-left: 2mm; margin-bottom: 0.3mm;">• dati di contatto (es. telefono, indirizzo mail);</p>
                    <p style="margin-left: 2mm; margin-bottom: 0.3mm;">• dati relativi alla Sua posizione lavorativa e previdenziale;</p>
                    <p style="margin-left: 2mm; margin-bottom: 0.3mm;">• informazioni finanziarie;</p>
                    <p style="margin-left: 2mm; margin-bottom: 0.3mm;">• dati urbanistici-catastali;</p>
                    <p style="margin-bottom: 0.5mm;">in generale, ogni altro dato e informazione necessaria per la conclusione ed esecuzione del contratto (es. Codice Iban, Partita Iva).</p>
                    <p style="margin-bottom: 0.5mm;">2 - Dati Personali Biometrici:</p>
                    <p style="margin-bottom: 0.5mm;">Energia Italia S.p.A. intende altresì utilizzare – con lo specifico consenso dell'interessato, revocabile in qualsiasi momento – una innovativa tecnologia che consente di sottoscrivere la documentazione in formato elettronico tramite Firma Elettronica Avanzata (c.d. FEA), mediante l'utilizzo della firma "grafometrica". Il consenso e la sua revoca vengono raccolti da Energia Italia S.p.A. anche nell'interesse di eventuali soggetti terzi che offriranno al Cliente i propri servizi per il tramite di Energia Italia S.p.A. e che agiranno come autonomi Titolari del trattamento, nel rispetto del regolamento (UE) 2016/679 in materia di protezione dei dati personali.</p>
                    <p style="margin-bottom: 0.5mm;">La suddetta soluzione FEA soddisfa i requisiti prevista dal Codice dell'Amministrazione Digitale (D.lgs. n. 82/2005) e dal DPCM 22 febbraio 2013, nonché le misure di sicurezza previste dal Regolamento (UE) 2016/679 in materia di protezione dei dati personali. Tale sistema garantisce una maggiore certezza giuridica nei rapporti intercorrenti con i clienti con riferimento alla rigorosa identificazione dell'Interessato firmatario e alla sua connessione univoca alla firma, nonché una maggiore sicurezza nel processo di gestione elettronica dei documenti informatici e contribuisce a prevenire e contrastare fenomeni fraudolenti, quali il furto d'identità e la contraffazione della firma.</p>
                    <p style="margin-bottom: 0.5mm;">Per le suddette finalità il conferimento dei dati personali ivi inclusi quelli di tipo biometrico (o caratteristiche "grafometriche" della firma) è necessario. Tuttavia, qualora l'interessato non manifesti il proprio consenso al trattamento dei dati biometrici o lo revochi successivamente, egli stesso potrà, in qualsiasi momento, sottoscrivere i documenti in formato cartaceo, secondo le procedure tradizionali.</p>
                    <p style="margin-bottom: 1.5mm;">I dati grafometrici non verranno analizzati, bensì verranno incorporati e registrati nel rispetto di idonee misure di sicurezza previste dalla legge e in modalità inintelligibile e indecifrabile, all'interno del singolo documento elettronico sottoscritto: questi dati non saranno, quindi, riproducibili e non saranno in alcun modo utilizzati da Energia Italia S.p.A. per altri fini.</p>
                    
                    <p style="font-weight: bold; margin: 1.5mm 0 0.5mm 0;">3. BASE GIURIDICA DEL TRATTAMENTO</p>
                    <p style="margin-bottom: 1.5mm;">Il Trattamento è necessario all'esecuzione di un contratto di cui Lei, nostro Cliente, è parte o all'esecuzione di misure precontrattuali o post contrattuali adottate su Sua richiesta o su richiesta di Energia Italia S.p.A. ai sensi dell'art. 6.1, lett. b) del GDPR, ovvero per l'adempimento di un obbligo legale ai sensi dell'art. 6.1, lett. c) del GDPR.</p>
                    
                    <p style="font-weight: bold; margin: 1.5mm 0 0.5mm 0;">4. FINALITÀ DEL TRATTAMENTO</p>
                    <p style="margin-bottom: 0.5mm;">Il Titolare raccoglie e tratta i Suoi dati personali nell'ambito della propria attività sulla base di:</p>
                    <p style="margin-left: 2mm; margin-bottom: 0.3mm;">• per finalità necessarie sia alle attività pre-contrattuali, sia alla gestione ed esecuzione del rapporto contrattuale con Lei instaurato (attività amministrative e contabili, assistenza al Cliente, gestione reclami) e all'erogazione dei servizi ad esso strettamente connessi e strumentali; tali dati, infatti, sono necessari per dare seguito all'acquisto dei Prodotti e/o ai Servizi da Lei richiesti;</p>
                    <p style="margin-left: 2mm; margin-bottom: 0.3mm;">• formulare richieste o evadere richieste pervenute;</p>
                    <p style="margin-left: 2mm; margin-bottom: 0.3mm;">• per trasmissioni e transazioni successive all'ordine di fornitura del servizio o del bene (fornito/acquistato);</p>
                    <p style="margin-left: 2mm; margin-bottom: 0.3mm;">• inoltrare comunicazioni con diversi mezzi di comunicazione (telefono, telefono cellulare, sms, email, fax, posta cartacea, ecc.)</p>
                    <p style="margin-left: 2mm; margin-bottom: 0.3mm;">• per adempiere agli obblighi previsti dalla legge (di natura contabile, fiscale, amministrativa) ai quali è soggetto il Titolare e chieste dalle Autorità;</p>
                    <p style="margin-left: 2mm; margin-bottom: 1.5mm;">• per finalità di promozione commerciale e marketing</p>
                </div>
                
                <!-- Colonna destra -->
                <div style="position: absolute; top: 0; right: 0; width: 85mm; height: 220mm; font-size: 8.5px; text-align: justify; line-height: 1.8mm; padding-left: 2mm; padding-right: 1mm;">
                    <p style="font-weight: bold; margin: 0 0 0.5mm 0;">5. MODALITÀ DEL TRATTAMENTO</p>
                    <p style="margin-bottom: 1.5mm;">Energia Italia S.p.A., nella qualità di Titolare del trattamento, raccoglie i Suoi dati personali direttamente, nonché, in taluni casi, per mezzo di soggetti Terzi (Agenzie e Agenti commerciali) con i quali il Titolare ha sottoscritto apposito contratto. Il trattamento dei dati per le finalità esposte ha luogo sia su supporto elettronico e magnetico, sia su supporto cartaceo, nel rispetto delle regole di riservatezza e sicurezza previste dall'art. 32 del Regolamento.</p>
                    
                    <p style="font-weight: bold; margin: 1.5mm 0 0.5mm 0;">6. DESTINATARI DEI DATI</p>
                    <p style="margin-bottom: 1.5mm;">I dati personali trattati dal Titolare non saranno diffusi, ovvero non ne verrà data conoscenza a soggetti indeterminati, in nessuna forma possibile, inclusa quella della loro messa a disposizione o semplice consultazione. Potranno, invece, essere comunicati ai lavoratori del Titolare e ad alcuni soggetti esterni che con essi collaborano, sempre nel rispetto delle finalità indicate. In particolare, si tratta di dipendenti/collaboratori che, sulla base dei ruoli e delle mansioni lavorative espletate, sono stati legittimati a trattare i dati personali, formati a farlo nei limiti delle loro competenze ed in conformità alle istruzioni ad essi impartite dal Titolare. Potranno, inoltre, essere comunicati, nei limiti strettamente necessari, ai soggetti che per finalità di emissione dei nostri ordini o richieste di informazioni e preventivi o formulazioni di offerte nostre prestazioni, debbano fornire/consegnare beni e/o eseguire/ricevere su nostro/vostro incarico prestazioni o servizi. Ai dati potrebbero accedere (per finalità di assistenza) nostri tecnici incaricati o consulenti esterni o incaricati di società che forniscono tale servizio. Ed ancora, i dati potranno essere comunicati agli Istituti di Credito, Imprese di assicurazioni ed autorità pubbliche (quale Agenzia delle Entrate). Infine, potranno essere comunicati ai soggetti legittimati ad accedervi in forza di disposizioni di legge, regolamenti, normative comunitarie.</p>
                    
                    <p style="font-weight: bold; margin: 1.5mm 0 0.5mm 0;">7. CONSERVAZIONE DEI DATI</p>
                    <p style="margin-bottom: 1.5mm;">Energia Italia S.p.A. si impegna a mantenere aggiornati i dati dell'interessato, a conservarli secondo gli standard e le prassi del settore adottando misure di sicurezza ritenute appropriate per la protezione dei dati personali. I dati saranno conservati esclusivamente per il tempo necessario per perseguire finalità definite o come richiesto dal contratto o dalla legge. Energia Italia S.p.a ad eliminare dagli archivi i Suoi dati Personali quando non siano più necessari per le finalità stabilite o diventino obsoleti. Il tempo di conservazione, in linea generale, è di 10 anni a decorrere dalla cessazione del rapporto contrattuale di cui lei è parte; tuttavia, il tempo di conservazione potrebbe variare in base alla finalità e a specifiche situazioni.</p>
                    
                    <p style="font-weight: bold; margin: 1.5mm 0 0.5mm 0;">8. TRASFERIMENTO DATI</p>
                    <p style="margin-bottom: 1.5mm;">I Dati Personali dell'Interessato saranno trattati all'interno dei confini dell'Unione Europea. Se necessario, il Titolare trasferirà i Dati Personali al di fuori dell'Unione Europea garantendo che il trasferimento degli stessi avvenga nel rispetto di misure tecniche ed organizzative conformi alla normativa applicabile ai fini del trasferimento dei Dati Personali ai sensi degli artt. 45 e 46 del Regolamento UE 2016/679.</p>
                    
                    <p style="font-weight: bold; margin: 1.5mm 0 0.5mm 0;">9. DIRITTI DELL'INTERESSATO</p>
                    <p style="margin-bottom: 1.5mm;">L'Interessato, con riguardo ai suoi Dati Personali, può in ogni momento – tramite l'invio di una comunicazione agli indirizzi di cui al punto 2 – esercitare i propri diritti di cui al regolamento Privacy: (a) ottenere la conferma dell'esistenza o meno dei Dati Personali che lo riguardano ed averne comunicazione; (b) conoscere l'origine dei Dati Personali, le finalità del trattamento e le sue modalità, nonché la logica applicata al trattamento effettuato mediante strumenti elettronici; (c) chiedere l'aggiornamento, la rettifica o – se ne ha interesse – l'integrazione dei Dati Personali, (d) ottenere la cancellazione, la trasformazione in forma anonima o il blocco dei Dati eventualmente trattati in violazione della legge, nonché di opporsi, per motivi legittimi, al trattamento; (e) revocare, in qualsiasi momento, il consenso al trattamento dei Dati personali, senza che ciò pregiudichi in alcun modo la liceità del trattamento basata sul consenso prestato prima della revoca; (f) chiedere al Titolare la limitazione del trattamento dei Dati; (g) opporsi in qualsiasi momento al trattamento dei Suoi Dati; (h) chiedere la cancellazione dei Dati che lo riguardano senza ingiustificato ritardo e (i) ottenere la portabilità dei dati che lo riguardano.</p>
                    
                    <p style="font-weight: bold; margin: 1.5mm 0 0.5mm 0;">10. MODIFICA ED AGGIORNAMENTO INFORMATIVA SULLA PRIVACY</p>
                    <p style="margin-bottom: 3mm;">Il Titolare del Trattamento si riserva il diritto di modificare e/o implementare la presente informativa, anche in ragione di modifiche legislative successive al rapporto pre-contrattuale o contrattuale con Lei instaurato, ovvero di raccomandazioni, autorizzazioni generali, linee guida, ulteriori misure di garanzia indicate dal Garante della Privacy italiana o europeo, ma sempre al fine di fornire una maggiore tutela per il trattamento dei Suoi dati personali.</p>
                    
                    <p style="margin-bottom: 5mm;">Luogo e data _______________________________</p>
                    <p style="margin-bottom: 0;">Firma del Cliente _______________________________</p>
                </div>
                
            </div>
        </div>

        <!-- Footer Aziendale -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 5mm 15mm; text-align: center; font-size: 9px; transform: translateX(-20mm);">
            ALFACOM S.R.L. | Viale Leonardo da Vinci, 8 | 95128 Catania (CT) | P.IVA: 05466900874 | Tel.: 095/8185744 | E-mail: info@gruppoalfacom.it
        </div>
    </div>

    <!-- Nona Pagina - Pagina finale -->
    <div class="page page-break" style="height: 297mm; width: 210mm;">
      
            <div style="width:100%; height: 115mm; background-image: url('{{ public_path('images/pdf/House-min.webp') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
            </div>
            <div style="width:100%; height: 80mm; text-align: center; position: relative;">
                <img src="{{ public_path('images/pdf/alfacom-logo.png') }}" alt="Alfacom Solar Logo" style="width: 65mm; height: auto; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
            </div>
            <div style="width:100%; height: 80mm; position: relative; text-align: center;">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                    <p style="font-size: 14px; font-weight: bold; margin: 0 0 3mm 0;">ALFACOM S.R.L.</p>
                    <p style="font-size: 12px; margin: 0mm; line-height: 1.0;">Viale Leonardo da Vinci, 8</p>
                    <p style="font-size: 12px; margin: 0mm; line-height: 1.0;">95128 Catania (CT)</p>
                    <p style="font-size: 12px; margin: 0mm; line-height: 1.0;">P.IVA: 05466900874</p>
                    <p style="font-size: 12px; margin: 0mm; line-height: 1.0;">Tel.: 095/8185744</p>
                    <p style="font-size: 12px; margin: 0mm 0mm 3mm 0mm; line-height: 1.0;">E-mail: info@gruppoalfacom.it</p>
                    <p style="font-size: 16px; color: #4BAE66; font-weight: bold; margin: 0;">alfacomsolar.it</p>
                </div>
            </div>
        
    </div>

</body>
</html>
