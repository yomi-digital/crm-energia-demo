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
            min-height: 297mm;
            position: relative;
            display: flex;
            overflow: hidden;
            background: white;
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
            font-size: 15px;
        }

        .client-detail-item .label {
            font-weight: bold;
            display: inline;
            font-size: 15px;
            margin: 0;
            padding: 0;
        }

        .client-detail-item .value {
            font-weight: normal;
            font-size: 15px;
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
    <!-- Prima Pagina -->
    <div class="first-page">
        <!-- Pannello sinistro -->
        <div class="first-page-left">
            <!-- Immagine kit singola posizionata sopra il blocco blu -->
            <div class="solar-kit-image">
                <img src="{{ public_path('images/pdf/preventivi_kit.png') }}" alt="Kit solare" onerror="this.style.display='none'">
            </div>
            
            <!-- Blocco verde superiore -->
            <div class="first-page-green-block">
                <!-- Logo -->
                <div class="first-page-logo">
                    <img src="{{ public_path('images/pdf/preventivi_logo.svg') }}" alt="Logo alfacom SOLAR" onerror="this.style.display='none'">
                </div>
            </div>
            
            <!-- Blocco blu scuro sopra il verde in basso -->
            <div class="first-page-blue-block">
                <div class="excellence-title">
                    UN PARTNER DI<br>
                    ECCELLENZA.
                </div>
                <div class="preventivo-number">Preventivo #{{ $preventivo->numero_preventivo ?? str_pad($preventivo->id_preventivo, 3, '0', STR_PAD_LEFT) }}</div>
                
                <div class="client-container">
                    <div class="client-label-vertical">
                        <span style="display: block; transform: rotate(-90deg); font-size: 10px;">T</span>
                        <span style="display: block; transform: rotate(-90deg); font-size: 10px;">N</span>
                        <span style="display: block; transform: rotate(-90deg); font-size: 10px;">E</span>
                        <span style="display: block; transform: rotate(-90deg); font-size: 10px;">I</span>
                        <span style="display: block; transform: rotate(-90deg); font-size: 10px;">L</span>
                        <span style="display: block; transform: rotate(-90deg); font-size: 10px;">C</span>
                    </div>
                    <div class="client-details">
                        @if($preventivo->cliente)
                            <div class="client-detail-item">
                                <span class="value"><span class="label font-bold">NAME:</span>{{ $preventivo->cliente->name ?? '' }} {{ $preventivo->cliente->last_name ?? '' }}
                                    @if($preventivo->cliente->business_name)
                                        {{ $preventivo->cliente->business_name }}
                                    @endif
                                </span>
                            </div>
                            @if($preventivo->cliente->address)
                            <div class="client-detail-item">
                                <span class="value"><span class="label font-bold">VIA:</span>{{ $preventivo->cliente->address }}</span>
                            </div>
                            @endif
                            @if($preventivo->cliente->phone || $preventivo->cliente->mobile)
                            <div class="client-detail-item">
                                <span class="value"><span class="label font-bold">TELEFONO:</span>{{ $preventivo->cliente->phone ?? $preventivo->cliente->mobile }}</span>
                            </div>
                            @endif
                            @if($preventivo->cliente->email)
                            <div class="client-detail-item">
                                <span class="value"><span class="label font-bold">E-MAIL:</span>{{ $preventivo->cliente->email }}</span>
                            </div>
                            @endif
                        @else
                            <div class="client-detail-item">
                                <span class="value"><span class="label font-bold">Cliente ID:</span>{{ $preventivo->fk_cliente }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            </div>
            <!-- Fine Box Inferiore -->
        </div>
        
        <!-- Pannello destro bianco con foto -->
        <div class="first-page-right">
            <img src="{{ public_path('images/pdf/preventivi_house.png') }}" alt="Casa con pannelli solari" class="first-page-image" onerror="this.style.display='none'">
        </div>
    </div>

    <!-- Seconda Pagina - Contenuto -->
    <div class="page" style="page-break-before: auto;">
        <!-- Contenuto principale -->
        <div class="page-content" style="position: relative; padding: 20mm 15mm;">
            <!-- Contenitore per tutto il contenuto sulla stessa pagina -->
            <div style="page-break-inside: avoid; break-inside: avoid; position: relative; min-height: 257mm;">
            <!-- Header verde ANALISI DEI CONSUMI -->
            <div class="section-header-green"><span>ANALISI DEI CONSUMI</span></div>
            
            <!-- Analisi Consumi -->
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
            
            <!-- Blocco 1: Tabella dei costi (superiore) -->
            <div style="margin-bottom: 20px;">
            @if(strtolower($tipologiaBolletta) === 'mensile')
            <!-- Tabella Mensile -->
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
            <!-- Tabella Bimestrale -->
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
            <!-- Fine Blocco 1 -->
            
            <!-- Blocco 2: Grafico e Lista costi (inferiore) -->
            <div style="position: absolute; bottom: -20mm; left: -15mm; right: -15mm; width: calc(100% + 30mm); page-break-inside: avoid; break-inside: avoid;">
            <!-- Contenitore per grafico e costi affiancati con space-between -->
            <div style="overflow: hidden; position: relative; width: 100%; min-height: 150mm;">
                <!-- Blocco 2.1: Grafico e legenda (sinistra) -->
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

                <!-- Blocco 2.2: Box lista costi complessivi (destra) -->
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
            <!-- Fine Blocco 2 -->
            @endif
            </div>
            <!-- Fine contenitore tutto il contenuto sulla stessa pagina -->
        </div>
    </div>

    <!-- Terza Pagina - Simulazione Impianto e Produzione -->
    <div class="page page-break">
        <!-- Margine sinistro verde -->
        <div class="page-left-margin"></div>
        
        <!-- Contenuto principale -->
        <div class="page-content" style=" padding-bottom: 0mm; min-height: calc(297mm - 40mm); position: relative;">
            <!-- Blocco 1: Header e Tabelle (in alto) -->
            <div style="position: relative;">
            <div class="section-header-green"><span>SIMULAZIONE IMPIANTO</span></div>
            
            @php
                $consumo = $preventivo->consumi ? $preventivo->consumi->first() : null;
                $costoMedioKwh = $consumo->costo_kwh_bolletta_medio;
                $costoTotaleBolletta = $consumo->costo_kwh_bolletta_totale;
            @endphp

            <!-- Prima Tabella - Costi e Consumi (Header Verde) -->
            <div style=""> 
                <div class="table-subtitle">Simulazione costi e consumi:</div>
                <table class="data-table-green">
                    <tr>
                        <td>Totale dei costi annui in euro</td>
                        <td>{{ number_format($consumo->totale_costi_annui ?? 0, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Totale dei consumi annui in kWh</td>
                        <td>{{ number_format($consumo->totale_consumo_annuo ?? 0, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Il costo medio bolletta in kWh</td>
                        <td>{{ number_format($costoMedioKwh, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Il costo totale della bolletta in kWh</td>
                        <td>{{ number_format($costoTotaleBolletta, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Totale del consumo annuo</td>
                        <td>{{ number_format($consumo->totale_consumo_annuo ?? 0, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Consumo diurno</td>
                        <td>{{ number_format($consumo->consumo_diurno_annuo ?? 0, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Consumo notturno</td>
                        <td>{{ number_format($consumo->consumo_notturno_annuo ?? 0, 2, ',', '.') }}</td>
                    </tr>
                </table>

                <!-- Seconda Tabella - Risparmi e Benefici (Header Rosso-Arancio) -->
                <div class="table-subtitle">Simulazione costi e consumi:</div>
                <table class="data-table-orange">
                    <tr>
                        <td>Totale dei costi annui in euro</td>
                        <td>{{ number_format($consumo->totale_costi_annui ?? 0, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Totale dei consumi annui in kWh</td>
                        <td>{{ number_format($consumo->totale_consumo_annuo ?? 0, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Il costo medio bolletta in kWh</td>
                        <td>{{ number_format($costoMedioKwh, 2, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
            </div>
            <!-- Fine Blocco 1 -->

            <!-- Blocco 2: Sezione Grafico (in basso) -->
            <div class="chart-section" style="box-sizing: border-box; position: absolute; bottom: 0; left: 0; right: 0; width: 100%; padding-bottom: 20mm;">
                @php
                    $consumoAnnuo = $consumo->totale_consumo_annuo ?? 0;
                    $produzioneAnnuo = $preventivo->produzione_annua_stimata ?? 0;
                    $maxValue = max($consumoAnnuo, $produzioneAnnuo);
                    
                    // Valori monetari dai campi del preventivo
                    $incentivoCerAnnuo = $preventivo->incentivo_cer_annuo ?? 0;
                    $venditaEccedenzeRidAnnuo = $preventivo->vendita_eccedenze_rid_annua ?? 0;
                    $risparmioAutoconsumoAnnuo = $preventivo->risparmio_autoconsumo_annuo ?? 0;
                    $detrazioneFiscaleAnnuo = $preventivo->detrazione_fiscale_annua ?? 0;
                    
                    // Totale benefici/ricavi annui per calcolare le percentuali monetarie
                    $totaleBeneficiAnnuo = $risparmioAutoconsumoAnnuo + $venditaEccedenzeRidAnnuo + $incentivoCerAnnuo + $detrazioneFiscaleAnnuo;
                    
                    // Energia accumulata (somma capacità batterie in kWh)
                    $energiaAccumulata = $preventivo->dettagliProdotti 
                        ? $preventivo->dettagliProdotti->sum('capacita_batteria_salvata') 
                        : 0;
                    
                    
                    // Energia accumulata: percentuale rispetto alla produzione annua stimata (in kWh)
                    $percentualeEnergiaAccumulata = $produzioneAnnuo > 0 ? ($energiaAccumulata / $produzioneAnnuo) * 100 : 0;
                    
                    $quarters = [
                        ['consumi' => $consumoAnnuo * 0.25, 'produzione' => $produzioneAnnuo * 0.25],
                        ['consumi' => $consumoAnnuo * 0.30, 'produzione' => $produzioneAnnuo * 0.30],
                        ['consumi' => $consumoAnnuo * 0.20, 'produzione' => $produzioneAnnuo * 0.20],
                        ['consumi' => $consumoAnnuo * 0.25, 'produzione' => $produzioneAnnuo * 0.25],
                    ];
                @endphp
                <!-- Grafico consumi vs produzione -->
                <div style="height:200px; width: 48%; box-sizing: border-box; float: left; position: relative; margin-right: 2%; text-align: center;">
                    <img src="{{ public_path('images/pdf/preventivi_consumo_produzione.png') }}" alt="Grafico Consumi vs Produzione" style="max-width: 100%; max-height: 100%; width: auto; height: auto; vertical-align: middle;">
                </div>

                <!-- Metriche con icone -->
                <div style="height:200px; width: 48%; box-sizing: border-box; float: right; padding: 20px; margin-left: 2%;">
                    <div style="margin-bottom: 20px;">
                        <div style="width: 24px; height: 24px; display: inline-block; vertical-align: middle; margin-right: 10px;">
                            <img src="{{ public_path('images/pdf/preventivi_sun.svg') }}" alt="Sole" style="width: 24px; height: 24px;">
                        </div>
                        <div style="display: inline-block; vertical-align: middle;">
                            <span style="font-size: 16px; font-weight: bold; color: #0F1137;"> € {{ number_format($incentivoCerAnnuo, 0) }}</span>
                            <span style="font-size: 12px; color: #0F1137; margin-left: 5px;">INCENTIVO CER</span>
                        </div>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <div style="width: 24px; height: 24px; display: inline-block; vertical-align: middle; margin-right: 10px;">
                            <img src="{{ public_path('images/pdf/preventivi_battery_low.svg') }}" alt="Batteria" style="width: 24px; height: 24px;">
                        </div>
                        <div style="display: inline-block; vertical-align: middle;">
                            <span style="font-size: 16px; font-weight: bold; color: #0F1137;"> € {{ number_format($risparmioAutoconsumoAnnuo, 0) }}</span>
                            <span style="font-size: 12px; color: #0F1137; margin-left: 5px;">RISPARMIO AUTOCONSUMO ANNUO</span>
                        </div>
                    </div>
                    <div>
                        <div style="width: 24px; height: 24px; display: inline-block; vertical-align: middle; margin-right: 10px;">
                            <img src="{{ public_path('images/pdf/preventivi_lightning.svg') }}" alt="Fulmine" style="width: 24px; height: 24px;">
                        </div>
                        <div style="display: inline-block; vertical-align: middle;">
                            <span style="font-size: 16px; font-weight: bold; color: #0F1137;"> € {{ number_format($venditaEccedenzeRidAnnuo, 0) }}</span>
                            <span style="font-size: 12px; color: #0F1137; margin-left: 5px;">ECCEDENZE VENDUTE</span>
                        </div>
                    </div>
                </div>

                
                <div style="clear: both;"></div>
            </div>
            <!-- Fine Blocco 2 -->

        </div>
        
        <!-- Footer verde con logo -->
        <div class="page-footer">
            <div class="page-footer-green-bar"></div>
            <img src="{{ public_path('images/pdf/preventivi_logo_verde.svg') }}" style="height: 100px; width: auto;" alt="Logo" class="page-footer-logo" onerror="this.style.display='none'">
        </div>
    </div>

    <!-- Quarta Pagina - Risparmi e Benefici Economici -->
    <div class="page page-break">
        <!-- Margine sinistro verde -->
        <div class="page-left-margin"></div>
        
        <!-- Contenuto principale -->
        <div class="page-content" style="padding-bottom: 200px;">
            <div class="section-header-green"><span>RISPARMI E BENEFICI</span></div>
            
            @php
                $risparmioAutoconsumo = $preventivo->risparmio_autoconsumo_annuo ?? 0;
                $venditaEccedenze = $preventivo->vendita_eccedenze_rid_annua ?? 0;
                $incentivoCer = $preventivo->incentivo_cer_annuo ?? 0;
                $detrazioneFiscale = $preventivo->detrazione_fiscale_annua ?? 0;
                $totaleBenefici = $risparmioAutoconsumo + $venditaEccedenze + $incentivoCer + $detrazioneFiscale;
                
                // Calcolo ROI e tempo di rientro (esempio)
                $investimentoTotale = $preventivo->dettagliProdotti 
                    ? $preventivo->dettagliProdotti->sum(function($p) { return $p->quantita * $p->prezzo_unitario_salvato; })
                    : 0;
                $roiPercentuale = $investimentoTotale > 0 ? ($totaleBenefici / $investimentoTotale) * 100 : 0;
                $tempoRientro = $totaleBenefici > 0 ? $investimentoTotale / $totaleBenefici : 0;
                
                $maxValue = max($risparmioAutoconsumo, $venditaEccedenze, $incentivoCer, $detrazioneFiscale, $totaleBenefici, $roiPercentuale);
            @endphp

            <!-- Box Superiore -->
            <div style="page-break-inside: avoid; break-inside: avoid; padding: 10px;">
            <!-- Grafico a barre orizzontali HTML+CSS -->
            @php
                $categories = [
                    ['label' => 'Risparmio da autoconsumo', 'value' => $risparmioAutoconsumo],
                    ['label' => 'Vendita eccedenze RID', 'value' => $venditaEccedenze],
                    ['label' => 'Incentivo CER', 'value' => $incentivoCer],
                    ['label' => 'Detrazione fiscale', 'value' => $detrazioneFiscale],
                    ['label' => 'Totale benefici annuali', 'value' => $totaleBenefici],
                ];
                
                $maxBarValue = max($risparmioAutoconsumo, $venditaEccedenze, $incentivoCer, $detrazioneFiscale, $totaleBenefici);
                // Arrotonda il valore massimo al multiplo di 1000 più vicino per le linee della griglia
                $maxGridValue = ceil($maxBarValue / 1000) * 1000;
                if ($maxGridValue == 0) $maxGridValue = 1000;
                
                $barHeight = 25;
                $barSpacing = 20;
                $gridHeight = (count($categories) * ($barHeight + $barSpacing)) - $barSpacing;
                
                // Calcola i valori per le linee della griglia (5 linee: 0, 25%, 50%, 75%, 100%)
                $gridValues = [];
                for ($i = 0; $i <= 4; $i++) {
                    $gridValues[] = ($maxGridValue / 4) * $i;
                }
            @endphp
            
            <div style="position: relative; width: 100%; max-width: 600px; margin: 0 auto; padding: 20px 0;">
                <!-- Griglia di sfondo -->
                <div style="position: absolute; left: 180px; right: 50px; top: 40px; height: {{ $gridHeight }}px; background-image: repeating-linear-gradient(to right, transparent, transparent 73px, #ddd 73px, #ddd 74px), repeating-linear-gradient(to bottom, transparent, transparent 33px, #ddd 33px, #ddd 34px); background-size: 100% 100%; opacity: 0.5;"></div>
                
                <!-- Linee verticali della griglia e etichette valori economici -->
                <div style="position: absolute; left: 180px; right: 50px; top: 40px; height: {{ $gridHeight }}px;">
                    @foreach($gridValues as $gridValue)
                        @php
                            $leftPos = $maxGridValue > 0 ? ($gridValue / $maxGridValue) * 100 : 0;
                        @endphp
                        <div style="position: absolute; left: {{ $leftPos }}%; top: 0; bottom: 0; width: 1px; border-left: 2px dashed rgba(189, 189, 189, 1);"></div>
                        <div style="position: absolute; left: {{ $leftPos }}%; bottom: -20px; transform: translateX(-50%); font-size: 10px; color: #666; font-family: Arial, sans-serif;">€ {{ number_format($gridValue, 0, ',', '.') }}</div>
                    @endforeach
                </div>
                
                <!-- Barre -->
                @foreach($categories as $index => $category)
                    @php
                        $barWidthPercent = $maxGridValue > 0 ? ($category['value'] / $maxGridValue) * 100 : 0;
                        $topPos = 40 + ($index * ($barHeight + $barSpacing));
                    @endphp
                    <div style="position: relative; margin-bottom: {{ $barSpacing }}px; height: {{ $barHeight }}px;">
                        <!-- Etichetta categoria -->
                        <div style="position: absolute; left: 0; width: 170px; top: 50%; transform: translateY(-50%); font-size: 10px; color: #333; font-family: Arial, sans-serif; text-align: right; padding-right: 10px;">{{ $category['label'] }}</div>
                        
                        <!-- Contenitore barra -->
                        <div style="position: absolute; left: 180px; right: 50px; height: {{ $barHeight }}px; background-color: transparent; border-radius: 2px;">
                            <!-- Barra -->
                            <div style="position: absolute; left: 0; top: 0; height: 100%; width: {{ $barWidthPercent }}%; background-color: #1A233B; border-radius: 2px;">
                                @if($barWidthPercent > 5)
                                    <span style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); font-size: 9px; color: #fff; font-weight: bold; font-family: Arial, sans-serif;">€ {{ number_format($category['value'], 0, ',', '.') }}</span>
                                @endif
                            </div>
                            @if($barWidthPercent <= 5)
                                <span style="position: absolute; left: {{ $barWidthPercent }}%; margin-left: 5px; top: 50%; transform: translateY(-50%); font-size: 9px; color: #333; font-weight: bold; font-family: Arial, sans-serif;">€ {{ number_format($category['value'], 0, ',', '.') }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            </div>
            <!-- Fine Box Superiore -->

            <!-- Box Centrale -->
            <div style="page-break-inside: avoid; break-inside: avoid;">
            <!-- Sezione centrale con grafico a torta e risparmio annuo -->
            <div style="margin-top: 30px; overflow: hidden;">
                <!-- Blocco Sinistro -->
                <div style="width: 48%; float: left; padding: 20px; text-align: center; margin-right: 2%;">
                    <div style="font-size: 14px; font-weight: bold; margin-bottom: 15px; color: #333; font-family: Arial, sans-serif;">Ottimizzazione dei consumi</div>
                    <img src="{{ public_path('images/pdf/preventivo_torta.svg') }}" alt="Grafico a torta" style="width: 225px; height: auto; display: block; margin: 0 auto;">
                    <div class="donut-legend" style="text-align: center; margin-top: 20px; white-space: nowrap; height: 30px; line-height: 30px;">
                        <div class="donut-legend-item" style="display: inline-block; vertical-align: middle; margin: 0 10px; font-size: 11px; white-space: nowrap;">
                            <div class="donut-legend-square" style="width: 45px; height: 20px; border-top-left-radius: 8px; border-top-right-radius: 8px; background-color: #e74c3c; display: inline-block; vertical-align: middle; margin-right: 5px;"></div>
                            <span>F1</span>
                        </div>
                        <div class="donut-legend-item" style="display: inline-block; vertical-align: middle; margin: 0 10px; font-size: 11px; white-space: nowrap;">
                            <div class="donut-legend-square" style="width: 45px; height: 20px; border-top-left-radius: 8px; border-top-right-radius: 8px; background-color: #4BAE66; display: inline-block; vertical-align: middle; margin-right: 5px;"></div>
                            <span>F2</span>
                        </div>
                        <div class="donut-legend-item" style="display: inline-block; vertical-align: middle; margin: 0 10px; font-size: 11px; white-space: nowrap;">
                            <div class="donut-legend-square" style="width: 45px; height: 20px; border-top-left-radius: 8px; border-top-right-radius: 8px; background-color: #1A233B; display: inline-block; vertical-align: middle; margin-right: 5px;"></div>
                            <span>F3</span>
                        </div>
                    </div>
                </div>
                
                <!-- Blocco Destro -->
                <div style="width: 48%; float: right; padding: 20px; text-align: center; margin-left: 2%;">
                    <div style="font-size: 14px; font-weight: bold; margin-bottom: 15px; color: #333; font-family: Arial, sans-serif;">Totale benefici annuo</div>
                    <img src="{{ public_path('images/pdf/preventivi_annuo_stimato.svg') }}" alt="Risparmio annuo stimato" style="width: 180px; height: auto; display: block; margin: 0 auto;">
                    @php
                        $risparmioAutoconsumo = $preventivo->risparmio_autoconsumo_annuo ?? 0;
                        $venditaEccedenze = $preventivo->vendita_eccedenze_rid_annua ?? 0;
                        $incentivoCer = $preventivo->incentivo_cer_annuo ?? 0;
                        $detrazioneFiscale = $preventivo->detrazione_fiscale_annua ?? 0;
                        $totaleBenefici = $risparmioAutoconsumo + $venditaEccedenze + $incentivoCer + $detrazioneFiscale;
                        $investimentoTotale = $preventivo->dettagliProdotti 
                            ? $preventivo->dettagliProdotti->sum(function($p) { return $p->quantita * $p->prezzo_unitario_salvato; })
                            : 0;
                        $roiPercentuale = $investimentoTotale > 0 ? ($totaleBenefici / $investimentoTotale) * 100 : 0;
                            // Valori monetari dai campi del preventivo
                        $incentivoCerAnnuo = $preventivo->incentivo_cer_annuo ?? 0;
                        $venditaEccedenzeRidAnnuo = $preventivo->vendita_eccedenze_rid_annua ?? 0;
                        $risparmioAutoconsumoAnnuo = $preventivo->risparmio_autoconsumo_annuo ?? 0;
                        $detrazioneFiscaleAnnuo = $preventivo->detrazione_fiscale_annua ?? 0;
                        
                        // Totale benefici/ricavi annui per calcolare le percentuali monetarie
                        $totaleBeneficiAnnuo = $risparmioAutoconsumoAnnuo + $venditaEccedenzeRidAnnuo + $incentivoCerAnnuo + $detrazioneFiscaleAnnuo;

                    @endphp
                    <div style="font-size: 18px; font-weight: bold; margin-top: 20px; height: 30px; line-height: 30px; color: #333; font-family: Arial, sans-serif;"> € {{ number_format($totaleBeneficiAnnuo, 1) }}</div>
                </div>
                
                <!-- Clear float -->
                <div style="clear: both;"></div>
            </div>
            </div>
            <!-- Fine Box Centrale -->

            <!-- Box Inferiore -->
            <div style="page-break-inside: avoid; break-inside: avoid; position: absolute; bottom: 0; left: 0; right: 0; width: 100%; margin-left: -15mm; margin-right: -15mm; padding-left: 15mm; padding-right: 15mm; border-top-left-radius: 8px; border-top-right-radius: 8px;">
            <!-- Footer giallo con grafico GUADAGNO ACCUMULATO -->
            <div class="yellow-footer" style="margin-top: 0;">
                <div class="yellow-footer-title">GUADAGNO ACCUMULATO</div>
                <div class="yellow-footer-line-chart">
                    <div style="height: 20px;"></div>
                    <h2 style="text-align: center; font-weight: bolder; font-size: 30px;">€ {{ number_format($totaleBeneficiAnnuo, 1) }}</h2>
                </div>
                </div>
            </div>
            </div>
            <!-- Fine Box Inferiore -->

        </div>
    </div>

    <!-- Quinta Pagina - Offerta Economica -->
    <div class="page page-break">
        <!-- Margine sinistro verde -->
        <div class="page-left-margin"></div>
        
        <!-- Contenuto principale -->
        <div class="page-content" style="position: relative; padding-bottom: 200px;">
            <div class="section-header-green"><span>OFFERTA ECONOMICA</span></div>
            
            @php
                // Calcolo totale investimento
                $investimentoTotale = $preventivo->dettagliProdotti 
                    ? $preventivo->dettagliProdotti->sum(function($p) { return $p->quantita * $p->prezzo_unitario_salvato; })
                    : 0;
                
                // Dati bonifico
                $bonificoData = null;
                $prezzoBonifico = 0;
                if($preventivo->bonifico_data_json) {
                    $bonificoData = is_string($preventivo->bonifico_data_json) 
                        ? json_decode($preventivo->bonifico_data_json, true) 
                        : $preventivo->bonifico_data_json;
                    if(
                        $bonificoData 
                        && isset($bonificoData['first_rate'], $bonificoData['second_rate'], $bonificoData['third_rate'])
                    ) {
                        $prezzoBonifico = 
                            (float)$bonificoData['first_rate'] 
                            + (float)$bonificoData['second_rate'] 
                            + (float)$bonificoData['third_rate'];
                    }
                }
                
                // Dati finanziamento/offerta
                $finanziamentoData = null;
                $prezzoOfferta = $investimentoTotale;
                if($preventivo->finanziamento_data_json) {
                    $finanziamentoData = is_string($preventivo->finanziamento_data_json) 
                        ? json_decode($preventivo->finanziamento_data_json, true) 
                        : $preventivo->finanziamento_data_json;
                    if($finanziamentoData && isset($finanziamentoData['importo'])) {
                        $prezzoOfferta = $finanziamentoData['importo'];
                    }
                }
                
                // Prezzo con IVA per il banner
                $prezzoConIva = $prezzoOfferta * 1.22; // IVA 22%
            @endphp

            <!-- Blocco Superiore: Card Offerte -->
            <div style="margin-top: 30px; overflow: hidden; page-break-inside: avoid; break-inside: avoid;">
                <!-- Card Offerte Container -->
                <div style="overflow: hidden;">
                    <!-- Card Bonifico (Bianca) -->
                    <div style="width: 40%; float: left; background-color: rgba(251, 251, 251, 1); border: 1px solid grey; border-radius: 8px; padding: 30px; margin-right: 2%; box-shadow: 0 2px 4px rgba(0,0,0,0.1); box-sizing: border-box;">
                        <div style="font-size: 24px; font-weight: bold; color: #333; margin-bottom: 15px; font-family: Arial, sans-serif;">Bonifico</div>
                        <div style="font-size: 32px; font-weight: bold; color: #333; margin-bottom: 5px; font-family: Arial, sans-serif;">€ {{ number_format($prezzoBonifico, 2, ',', '.') }}</div>
                        <div style="font-size: 14px; color: #666; margin-bottom: 30px; font-family: Arial, sans-serif;">IVA esclusa</div>
                        <div style="border-top: 1px solid #e0e0e0; padding-top: 20px;">
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <li style="margin-bottom: 15px; font-size: 13px; color: #333; font-family: Arial, sans-serif; overflow: hidden;">
                                    <img src="{{ public_path('images/pdf/preventivi_bonifico_check.svg') }}" alt="Check" style="width: 18px; height: 18px; float: left; margin-right: 10px;">
                                    <span style="margin-left: 25px; transform: translateY(-2px);">Prima rata: € {{ number_format(($bonificoData ?? [])['first_rate'] ?? 0, 2, ',', '.') }}</span>
                                </li>
                                <li style="margin-bottom: 15px; font-size: 13px; color: #333; font-family: Arial, sans-serif; overflow: hidden;">
                                    <img src="{{ public_path('images/pdf/preventivi_bonifico_check.svg') }}" alt="Check" style="width: 18px; height: 18px; float: left; margin-right: 10px;">
                                    <span style="margin-left: 25px; transform: translateY(-2px);">Seconda rata: € {{ number_format(($bonificoData ?? [])['second_rate'] ?? 0, 2, ',', '.') }}</span>
                                </li>
                                <li style="margin-bottom: 15px; font-size: 13px; color: #333; font-family: Arial, sans-serif; overflow: hidden;">
                                    <img src="{{ public_path('images/pdf/preventivi_bonifico_check.svg') }}" alt="Check" style="width: 18px; height: 18px; float: left; margin-right: 10px;">
                                    <span style="margin-left: 25px; transform: translateY(-2px);">Terza rata: € {{ number_format(($bonificoData ?? [])['third_rate'] ?? 0, 2, ',', '.') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Card Finanziamento (Blu Scuro) -->
                    <div style="width: 40%; float: right; background-color: #1A233B; border-radius: 8px; padding: 30px; margin-left: 2%; box-shadow: 0 2px 4px rgba(0,0,0,0.1); box-sizing: border-box;">
                        <div style="font-size: 24px; font-weight: bold; color: #ffffff; margin-bottom: 15px; font-family: Arial, sans-serif;">Finanziamento</div>
                        <div style="font-size: 32px; font-weight: bold; color: #ffffff; margin-bottom: 5px; font-family: Arial, sans-serif;">€ {{ number_format(($finanziamentoData ?? [])['rate_import'] ?? 0, 2, ',', '.') }}</div>
                        <div style="font-size: 14px; color: #ffffff; opacity: 0.8; margin-bottom: 30px; font-family: Arial, sans-serif;">IVA esclusa</div>
                        <div style="border-top: 1px solid rgba(255,255,255,0.3); padding-top: 20px;">
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <li style="margin-bottom: 15px; font-size: 13px; color: #ffffff; font-family: Arial, sans-serif; overflow: hidden;">
                                    <img src="{{ public_path('images/pdf/preventivi_finanziamento_check.svg') }}" alt="Check" style="width: 18px; height: 18px; float: left; margin-right: 10px;">
                                    <span style="margin-left: 25px; transform: translateY(-2px);">Importo rata 1: € {{ number_format((($finanziamentoData ?? [])['rate_import'] ?? 0) / ((($finanziamentoData ?? [])['number_of_rate'] ?? 1) > 0 ? (($finanziamentoData ?? [])['number_of_rate'] ?? 1) : 1), 2, ',', '.') }}</span>
                                </li>
                                <li style="margin-bottom: 15px; font-size: 13px; color: #ffffff; font-family: Arial, sans-serif; overflow: hidden;">
                                    <img src="{{ public_path('images/pdf/preventivi_finanziamento_check.svg') }}" alt="Check" style="width: 18px; height: 18px; float: left; margin-right: 10px;">
                                    <span style="margin-left: 25px; transform: translateY(-2px);">Numero di rate: {{ ($finanziamentoData ?? [])['number_of_rate'] ?? 0 }}</span>
                                </li>
                                <li style="margin-bottom: 15px; font-size: 13px; color: #ffffff; font-family: Arial, sans-serif; overflow: hidden;">
                                    <img src="{{ public_path('images/pdf/preventivi_finanziamento_check.svg') }}" alt="Check" style="width: 18px; height: 18px; float: left; margin-right: 10px;">
                                    <span style="margin-left: 25px; transform: translateY(-2px);">Durata totale del finanziamento: {{ ($finanziamentoData ?? [])['number_of_rate'] ?? 0 }} mesi</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div style="clear: both;"></div>
                </div>
            </div>

            <!-- Blocco Inferiore: Banner e Foto (Footer) -->
            <div style="position: absolute; bottom: 0; left: 0; right: 0; width: 100%; margin-left: -15mm; margin-right: -15mm; padding-left: 15mm; padding-right: 15mm; page-break-inside: avoid; break-inside: avoid;">
                <!-- Banner Blu Scuro con Prezzo -->
                <div style="background-color: #1A233B; padding: 20px 30px; overflow: hidden;">
                    <div style="float: left; color: #ffffff; font-family: Arial, sans-serif;">
                        <span style="font-size: 24px; font-weight: bold; visibility: hidden;">a soli </span>
                        <span style="color: #4BAE66; font-size: 32px; font-weight: bold; visibility: hidden;">{{ number_format($prezzoConIva, 0, ',', '.') }} €</span>
                        <span style="font-size: 14px; margin-left: 10px; visibility: hidden;">IVA INCLUSA</span>
                    </div>
                    <div style="float: right; color: #ffffff; font-size: 14px; font-weight: bold; font-family: Arial, sans-serif; line-height: 40px; visibility: hidden;">
                        POSSIBILITÀ DI RATEIZZAZIONE
                    </div>
                    <div style="clear: both;"></div>
                </div>
                
                <!-- Immagine Pannelli Solari -->
                <img src="{{ public_path('images/pdf/preventivi_solare_2.png') }}" alt="Pannelli solari" style="width: 100%; height: 120mm; object-fit: cover; display: block;" onerror="this.style.display='none'">
            </div>

            <!-- Logo piccolo in basso a destra -->
            <div class="page-logo-small">
                <img src="{{ public_path('images/pdf/preventivi_logo_min.svg') }}" alt="Logo" onerror="this.style.display='none'">
            </div>
        </div>
    </div>

    <!-- Settima Pagina - Manutenzione e Assicurazione -->
    <div class="page page-break">
        <!-- Margine sinistro verde -->
        <div class="page-left-margin"></div>
        
        <!-- Contenuto principale -->
        <div class="page-content" style="position: relative;">
            <div class="section-header-green"><span>MANUTENZIONE E ASSICURAZIONE</span></div>
            
            <div class="service-blocks-container">
                <div style="width: 100%; height: 200px;"></div>

                <!-- Blocco Manutenzione (Verde) -->
                <div class="service-block service-block-green">
                    <div class="service-header">
                        <div class="service-icon service-icon-white">
                            <img src="{{ public_path('images/pdf/preventivi_settings.svg') }}" alt="Settings">
                        </div>
                        <div class="service-title">MANUTENZIONE {{ $preventivo->opzione_manutenzione_salvata == 'si' ? '(INCLUSA)' : '(NON INCLUSA)' }}</div>
                    </div>
                    <div class="service-description">
                        Servizio di manutenzione completo per garantire il massimo rendimento del tuo impianto fotovoltaico. Include controlli periodici, pulizia dei pannelli e assistenza tecnica dedicata.
                    </div>

                    <div class="service-price-container service-price-container-red" style="{{ $preventivo->opzione_manutenzione_salvata == 'no' ? 'border: none;' : '' }}">
                        <div class="service-price" style="{{ $preventivo->opzione_manutenzione_salvata == 'no' ? 'visibility: hidden;' : '' }}">
                            € {{ number_format($preventivo->costo_annuo_manutenzione_salvato ?? 0, 2, ',', '.') }}
                        </div>
                        <div class="service-vat" style="{{ $preventivo->opzione_manutenzione_salvata == 'no' ? 'visibility: hidden;' : '' }}">IVA INCLUSA</div>
                    </div>
                </div>

                <!-- Blocco Assicurazione (Blu Scuro) -->
                <div class="service-block service-block-blue">
                    <div class="service-header">
                        <div class="service-icon service-icon-white" style="margin-top: 5px;">
                            <img src="{{ public_path('images/pdf/preventivi_check.svg') }}" alt="Check">
                        </div>
                        <div class="service-title">ASSICURAZIONE {{ $preventivo->opzione_assicurazione_salvata == 'si' ? '(INCLUSA)' : '(NON INCLUSA)' }}</div>
                    </div>
                    <div class="service-description">
                        Copertura assicurativa completa per proteggere il tuo investimento. Include danni accidentali, furto, eventi atmosferici e responsabilità civile per garantire la massima tranquillità.
                    </div>
                    <div class="service-price-container service-price-container-red" style="{{ $preventivo->opzione_assicurazione_salvata == 'no' ? 'border: none;' : '' }}">
                        <div class="service-price" style="{{ $preventivo->opzione_assicurazione_salvata == 'no' ? 'visibility: hidden;' : '' }}">
                            € {{ number_format($preventivo->costo_annuo_assicurazione_salvato ?? 0, 2, ',', '.') }}
                        </div>
                        <div class="service-vat" style="{{ $preventivo->opzione_assicurazione_salvata == 'no' ? 'visibility: hidden;' : '' }}">IVA INCLUSA</div>
                    </div>
                </div>
            </div>
            
            <!-- Footer verde con logo -->
            <div class="page-footer">
                <div class="page-footer-green-bar"></div>
                <img src="{{ public_path('images/pdf/preventivi_logo_verde.svg') }}" style="height: 100px; width: auto;" alt="Logo" class="page-footer-logo" onerror="this.style.display='none'">
            </div>
        </div>
    </div>

    <!-- Sesta Pagina - Business Plan -->
        @if($preventivo->dettagliBusinessPlan && $preventivo->dettagliBusinessPlan->count() > 0)
    <div class="page page-break">
        <!-- Margine sinistro verde -->
        <div class="page-left-margin"></div>
        
        <!-- Contenuto principale -->
        <div class="page-content" style="padding: 20mm 15mm; position: relative; padding-bottom: 150mm;">
            <div class="section-header-green" style="margin-bottom: 5px;"><span>BUSINESS PLAN</span></div>
            <div style="height: 50px;"></div>
            <table class="business-plan-table">
                <thead>
                    <tr>
                        <th>Anno</th>
                        <th>Rata</th>
                        <th>Costo Ass.</th>
                        <th>Costo Man.</th>
                        <th>Risparmio Bolletta</th>
                        <th>Eccedenze</th>
                        <th>CER</th>
                        <th>Incentivo PNNR</th>
                        <th>Detrazione</th>
                        <th>Sconto</th>
                        <th>Flussi di cassa</th>
                        <th>Flussi cumulati</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($preventivo->dettagliBusinessPlan as $bp)
                    <tr>
                        <td class="text-center">{{ $bp->anno_simulazione }}</td>
                        <td class="text-right">€ {{ number_format($bp->costo_annuo_investimento, 2, ',', '.') }}</td>
                        <td class="text-right">€ {{ number_format($bp->costo_annuo_assicurazione, 2, ',', '.') }}</td>
                        <td class="text-right">€ {{ number_format($bp->costo_annuo_manutenzione, 2, ',', '.') }}</td>
                        <td class="text-right">€ {{ number_format($bp->ricavo_risparmio_bolletta, 2, ',', '.') }}</td>
                        <td class="text-right">€ {{ number_format($bp->ricavo_vendita_eccedenze, 2, ',', '.') }}</td>
                        <td class="text-right">€ {{ number_format($bp->ricavo_incentivo_cer, 2, ',', '.') }}</td>
                        <td class="text-right">€ {{ number_format($bp->incentivo_pnnr ?? 0, 2, ',', '.') }}</td>
                        <td class="text-right">€ {{ number_format($bp->detrazione_fiscale ?? 0, 2, ',', '.') }}</td>
                        <td class="text-right">€ {{ number_format($bp->sconto ?? 0, 2, ',', '.') }}</td>
                        <td class="text-right">€ {{ number_format($bp->flusso_cassa_annuo, 2, ',', '.') }}</td>
                        <td class="text-right text-bold">€ {{ number_format($bp->flusso_cassa_cumulato, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Footer con immagine solare -->
            <div style="position: absolute; bottom: 0; left: 0; right: 0; width: 100%; margin-left: -15mm; margin-right: -15mm; padding-left: 15mm; padding-right: 15mm; page-break-inside: avoid; break-inside: avoid;">
                <!-- Immagine Pannelli Solari -->
                <img src="{{ public_path('images/pdf/preventivo_solare.png') }}" alt="Pannelli solari" style="width: 100%; height: 120mm; object-fit: cover; display: block;" onerror="this.style.display='none'">
                <!-- Barra verde sotto l'immagine -->
                <div class="business-plan-footer"></div>
            </div>
        </div>
        </div>
        @endif

    <!-- Ottava Pagina - Informativa Privacy -->
    <div class="page page-break">
        <!-- Margine sinistro blu scuro -->
        <div class="page-left-margin-blue"></div>
        
        <!-- Contenuto principale -->
        <div class="privacy-page-content" style="padding-bottom: 60mm;">
            <div class="section-header-blue">
                INFORMATIVA SUL TRATTAMENTO DEI DATI PERSONALI<br>
                <span style="font-size: 14px; font-weight: normal; display: block; margin-top: 1px;">
                    (Regolamento UE 2016/679, di seguito Regolamento)
                </span>
            </div>

            <!-- Container due colonne superiori -->
            <div class="privacy-columns-container">
                <!-- Colonna sinistra -->
                <div class="privacy-column">
                    <!-- Sezione 1 -->
                    <div class="privacy-section">
                        <div class="privacy-section-number">1. TITOLARE DEL TRATTAMENTO</div>
                        <div class="privacy-text">
                            Il titolare del trattamento è Energia Italia S.p.A., con sede in C.da Grotta Affumata snc - 92024 Canicatti (AG), Tel. +39 0922 858410, Fax +39 0922 737398, Email info@energiaitaliaspa.it, Pec energiaitaliaspa@pec.it.
                        </div>
                    </div>

                    <!-- Sezione 2 -->
                    <div class="privacy-section">
                        <div class="privacy-section-number">2. DEFINIZIONE DI DATO PERSONALE E CATEGORIE DI DATI PERSONALI TRATTATI</div>
                        <div class="privacy-text">
                            Per "Dato Personale" si intende qualsiasi informazione idonea a identificare, direttamente o indirettamente, una persona fisica (il cliente).
                        </div>
                        <div class="privacy-section-subtitle">2.1 Dati Personali non biometrici</div>
                        <ul class="privacy-list">
                            <li class="privacy-list-item">Dati anagrafici (es. nome, cognome, indirizzo)</li>
                            <li class="privacy-list-item">Dati di contatto (es. telefono, indirizzo email)</li>
                            <li class="privacy-list-item">Informazioni relative all'attività lavorativa e previdenziale</li>
                            <li class="privacy-list-item">Informazioni finanziarie</li>
                            <li class="privacy-list-item">Dati urbanistici-catastali</li>
                            <li class="privacy-list-item">Qualsiasi altro dato e informazione necessari per la conclusione e l'esecuzione del contratto (es. IBAN, partita IVA)</li>
                        </ul>
                        <div class="privacy-section-subtitle">2.2 Dati Personali Biometrici</div>
                        <div class="privacy-text">
                            Energia Italia S.p.A. intende utilizzare, previo consenso specifico e revocabile, una tecnologia innovativa per la sottoscrizione elettronica dei documenti mediante Firma Elettronica Avanzata (FEA) utilizzando la firma "grafometrica". Tale consenso viene raccolto anche per conto di terzi che erogano servizi tramite Energia Italia S.p.A., in qualità di titolari autonomi del trattamento. La soluzione FEA è conforme al Codice dell'Amministrazione Digitale (D.lgs. n. 82/2005) e al DPCM 22 febbraio 2013, nonché alle misure di sicurezza del Regolamento UE 2016/679. Tale sistema garantisce maggiore certezza giuridica, rigorosa identificazione del sottoscrittore, collegamento univoco alla firma, maggiore sicurezza nella gestione dei documenti elettronici e contribuisce a prevenire frodi (furto di identità, contraffazione di firma). La prestazione dei dati biometrici è necessaria per tali finalità. Tuttavia, in caso di mancato consenso o revoca dello stesso, i documenti potranno essere sottoscritti in formato cartaceo. I dati grafometrici non verranno analizzati ma incorporati e registrati in modo non intelligibile e indecifrabile all'interno del documento elettronico, nel rispetto delle misure di sicurezza. Tali dati non saranno riproducibili né utilizzabili da Energia Italia S.p.A. per altre finalità.
                        </div>
                    </div>

                    <!-- Sezione 3 -->
                    <div class="privacy-section">
                        <div class="privacy-section-number">3. BASE GIURIDICA DEL TRATTAMENTO</div>
                        <div class="privacy-text">
                            Il trattamento è necessario per l'esecuzione di un contratto con il cliente o per misure pre/post contrattuali adottate su richiesta del cliente o di Energia Italia S.p.A. (Art. 6.1, lett. b) GDPR). Il trattamento è altresì necessario per adempiere a un obbligo legale (Art. 6.1, lett. c) GDPR).
                        </div>
                    </div>

                    <!-- Sezione 4 -->
                    <div class="privacy-section">
                        <div class="privacy-section-number">4. FINALITÀ DEL TRATTAMENTO</div>
                        <div class="privacy-text">
                            Il titolare raccoglie e tratta i dati personali per:
                        </div>
                        <ul class="privacy-list">
                            <li class="privacy-list-item">Attività precontrattuali, gestione ed esecuzione del rapporto contrattuale (attività amministrative e contabili, assistenza clienti, gestione reclami) e prestazione di servizi connessi. Tali dati sono necessari per l'acquisto di prodotti/servizi.</li>
                            <li class="privacy-list-item">Formulazione o adempimento di richieste.</li>
                            <li class="privacy-list-item">Trasmissioni e operazioni successive relative alla fornitura di servizi/beni (forniti/acquistati).</li>
                            <li class="privacy-list-item">Invio di comunicazioni attraverso vari mezzi (telefono, cellulare, SMS, email, fax, posta, ecc.).</li>
                            <li class="privacy-list-item">Adempimento degli obblighi di legge (contabili, fiscali, amministrativi) ai quali il titolare è soggetto, e quelli richiesti dalle autorità.</li>
                            <li class="privacy-list-item">Finalità di promozione commerciale e marketing.</li>
                        </ul>
                    </div>

                    <!-- Sezione 5 -->
                    <div class="privacy-section">
                        <div class="privacy-section-number">5. MODALITÀ DEL TRATTAMENTO</div>
                        <div class="privacy-text">
                            Energia Italia S.p.A. raccoglie i dati personali direttamente e, in alcuni casi, tramite terzi (Agenzie e Agenti Commerciali) con i quali è stato sottoscritto un contratto. Il trattamento avviene su supporto elettronico, magnetico e cartaceo, nel rispetto delle regole di riservatezza e sicurezza (Art. 32 GDPR).
                        </div>
                    </div>
                </div>

                <!-- Colonna destra -->
                <div class="privacy-column">
                    <!-- Sezione 6 -->
                    <div class="privacy-section">
                        <div class="privacy-section-number">6. DESTINATARI DEI DATI</div>
                        <div class="privacy-text">
                            I dati personali non verranno diffusi a soggetti indeterminati. I dati potranno essere comunicati a dipendenti e collaboratori esterni del titolare, nel rispetto delle finalità indicate. In particolare, dipendenti/collaboratori autorizzati al trattamento dei dati in base al ruolo e alle mansioni svolte, formati e istruiti dal titolare. I dati potranno essere comunicati, strettamente come necessario, a soggetti che devono fornire/consegnare beni o eseguire/ricevere servizi per conto di Energia Italia S.p.A. per l'emissione di ordini, richieste di informazioni, preventivi o prestazione di servizi. Il personale tecnico o consulenti/aziende esterne che forniscono assistenza potranno accedere ai dati. I dati potranno essere comunicati a Istituti di Credito, Compagnie Assicurative e autorità pubbliche (es. Agenzia delle Entrate). Infine, i dati potranno essere comunicati a soggetti autorizzati dalla legge, dalle norme o dalle disposizioni comunitarie.
                        </div>
                    </div>

                    <!-- Sezione 7 -->
                    <div class="privacy-section">
                        <div class="privacy-section-number">7. CONSERVAZIONE DEI DATI</div>
                        <div class="privacy-text">
                            Energia Italia S.p.A. si impegna a mantenere i dati aggiornati, conservandoli secondo standard e prassi del settore e adottando misure di sicurezza appropriate. I dati saranno conservati solo per il tempo necessario al perseguimento delle finalità definite o come richiesto dal contratto o dalla legge. Energia Italia S.p.A. cancellerà i dati personali dagli archivi quando non saranno più necessari per le finalità stabilite o quando diventeranno obsoleti. Generalmente, il periodo di conservazione è di 10 anni dalla cessazione del rapporto contrattuale; tuttavia, può variare in base a finalità e situazioni specifiche.
                        </div>
                    </div>

                    <!-- Sezione 8 -->
                    <div class="privacy-section">
                        <div class="privacy-section-number">8. TRASFERIMENTO DATI</div>
                        <div class="privacy-text">
                            I dati personali saranno trattati all'interno dell'Unione Europea. Se necessario, il titolare trasferirà i dati personali al di fuori dell'Unione Europea, assicurando che tali trasferimenti rispettino misure tecniche e organizzative conformi alle normative applicabili (Art. 45 e 46 del Regolamento UE 2016/679).
                        </div>
                    </div>

                    <!-- Sezione 9 -->
                    <div class="privacy-section">
                        <div class="privacy-section-number">9. DIRITTI DELL'INTERESSATO</div>
                        <div class="privacy-text">
                            L'interessato può, in qualsiasi momento, inviando una comunicazione agli indirizzi di cui al punto 2, esercitare i propri diritti previsti dal Regolamento Privacy:
                        </div>
                        <ul class="privacy-list">
                            <li class="privacy-list-item">(a) Ottenere la conferma dell'esistenza o meno di dati personali e riceverne comunicazione.</li>
                            <li class="privacy-list-item">(b) Conoscere l'origine dei dati personali, le finalità e le modalità del trattamento e la logica applicata al trattamento elettronico.</li>
                            <li class="privacy-list-item">(c) Richiedere l'aggiornamento, la rettifica o, se di interesse, l'integrazione dei dati personali.</li>
                            <li class="privacy-list-item">(d) Ottenere la cancellazione, l'anonimizzazione o il blocco dei dati trattati in violazione di legge, e opporsi al trattamento per motivi legittimi.</li>
                            <li class="privacy-list-item">(e) Revocare il consenso al trattamento dei dati personali in qualsiasi momento, senza pregiudicare la liceità del trattamento basato sul consenso prestato prima della revoca.</li>
                            <li class="privacy-list-item">(f) Richiedere al titolare la limitazione del trattamento dei dati.</li>
                            <li class="privacy-list-item">(g) Opporsi al trattamento dei propri dati in qualsiasi momento.</li>
                            <li class="privacy-list-item">(h) Richiedere la cancellazione dei propri dati senza indebito ritardo.</li>
                            <li class="privacy-list-item">(i) Ottenere la portabilità dei dati.</li>
                        </ul>
                    </div>

                    <!-- Sezione 10 -->
                    <div class="privacy-section">
                        <div class="privacy-section-number">10. MODIFICA ED AGGIORNAMENTO INFORMATIVA SULLA PRIVACY</div>
                        <div class="privacy-text">
                            Il titolare del trattamento si riserva il diritto di modificare e/o implementare la presente informativa, anche in seguito a successive modifiche legislative relative al rapporto precontrattuale o contrattuale, o a raccomandazioni, autorizzazioni generali, linee guida e ulteriori misure di garanzia indicate dal Garante della Privacy italiano o europeo, sempre al fine di fornire una maggiore protezione al trattamento dei dati personali.
                        </div>
                    </div>

                    <!-- Firma Cliente -->
                    <div class="" style="margin-top: 10px; border-top: 1px solid #e0e0e0; padding-top: 5px;">
                        <div class="">
                            <div class="">
                                <div class="signature-label">Luogo e data</div>
                                <div class="signature-line"></div>
                            </div>
                            <div class="">
                                <div class="signature-label">Firma del Cliente</div>
                                <div class="signature-line"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Container due blocchi inferiori -->
            <div class="privacy-bottom-blocks">
                <!-- Blocco: Sezione Firma -->
                <div class="privacy-bottom-block">
                    <!-- Sezione Firma -->
                    <div class="privacy-signature-section">
                        <div class="privacy-footer-title" style="font-size: 14px; font-weight: bolder;">SIMULAZIONE GENERATA DA</div>
                        <div class="signature-row">
                            <div class="signature-field">
                                <div class="signature-label">Agenzia</div>
                                <div class="signature-line"></div>
                            </div>
                            <div class="signature-field">
                                <div class="signature-label">Agente</div>
                                <div class="signature-line"></div>
                            </div>
                            <div class="signature-field">
                                <div class="signature-label">Firma Agente</div>
                                <div class="signature-line"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer blu con logo e contatti -->
            <div class="page-footer-blue">
                <!-- Logo -->
                <img src="{{ public_path('images/pdf/preventivi_logo_white.svg') }}" alt="Logo Gruppo Alfacom" class="page-footer-blue-logo" onerror="this.style.display='none'">
                
                <!-- Informazioni di contatto -->
                <div class="page-footer-blue-contact">
                    <div class="page-footer-blue-company">Gruppo Alfacom S.r.l</div>
                    <div>Viale Leonardo Da Vinci, 8,</div>
                    <div>95128 Catania CT</div>
                    <div>095 818 5744 gruppoalfacom@gmail.com</div>
                </div>
                
                <div style="clear: both;"></div>
            </div>
        </div>
    </div>
</body>
</html>
