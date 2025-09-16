<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fiche de Stock</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            background-color: #fff; 
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #007bff;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff; 
        }
        .header-table td {
            border: none;
            padding: 10px;
            vertical-align: middle; 
            background-color: #fff; 
        }
        .subtitle {
            font-size: 14px;
            color: #666;
        }
        .info {
            margin-bottom: 20px;
        }
        .info table {
            width: 100%;
            border-collapse: collapse;
        }
        .info td {
            padding: 8px;
            border: 1px solid #ddd;
            background-color: #F5F3F3;
            font-family: 'Times New Roman', serif;
        }
        .info .label {
            font-weight: bold;
            width: 150px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            background-color: #F5F3F3;
        }
        th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .number {
            text-align: right;
        }
        .summary {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td width="30%">
                    @if(isset($companyLogo) && $companyLogo)
                        <img src="{{ $companyLogo }}" alt="Company Logo" width="150px">
                    @endif
                </td>
                <td align="center" width="40%">
                    <div class="title">FICHE DE STOCK</div>
                </td>
                <td width="30%"></td>
            </tr>
        </table>
    </div>

    <div class="info">
        <table>
            <tr>
                <td class="label">Produit:</td>
                <td>{{ $product['name'] ?? 'N/A' }} ({{ $product['code'] ?? 'N/A' }})</td>
                <td class="label">Entrepôt:</td>
                <td>{{ $warehouse['name'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Catégorie:</td>
                <td>{{ $product['category'] ?? 'N/A' }}</td>
                <td class="label">Marque:</td>
                <td>{{ $product['brand'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Période:</td>
                <td>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</td>
                <td class="label">Généré le:</td>
                <td>{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Nom du produit</th>
                <th class="number">Stock initial</th>
                <th class="number">Entrée</th>
                <th class="number">Total</th>
                <th class="number">Sortie</th>
                <th class="number">Stock final</th>
                <th class="number">Prix unitaire</th>
                <th class="number">Prix total</th>
                <th>Type de mouvement</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($movements) && count($movements) > 0)
                @foreach($movements as $movement)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($movement['date'])->format('d/m/Y') }}</td>
                    <td>{{ $movement['product_name'] ?? 'N/A' }}</td>
                    <td class="number">{{ number_format($movement['stock_initial'] ?? 0, 2) }}</td>
                    <td class="number">{{ number_format($movement['entree'] ?? 0, 2) }}</td>
                    <td class="number">{{ number_format($movement['total'] ?? 0, 2) }}</td>
                    <td class="number">{{ number_format($movement['sortie'] ?? 0, 2) }}</td>
                    <td class="number">{{ number_format($movement['stock_final'] ?? 0, 2) }}</td>
                    <td class="number">{{ number_format($movement['prix_unitaire'] ?? 0, 2) }}</td>
                    <td class="number">{{ number_format($movement['prix_total'] ?? 0, 2) }}</td>
                    <td>{{ $movement['movement_type'] ?? 'N/A' }}</td>
                    <td>{{ $movement['notes'] ?? '' }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="11" style="text-align: center;">Aucun mouvement trouvé pour cette période</td>
                </tr>
            @endif
        </tbody>
    </table>

</body>
</html>