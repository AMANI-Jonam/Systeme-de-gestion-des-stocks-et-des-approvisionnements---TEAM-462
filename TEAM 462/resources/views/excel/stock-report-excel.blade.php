<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fiche de Stock - {{ $product->name }}</title>
</head>
<body>
    <table>
        <tr>
            <td colspan="11" style="text-align: center; font-size: 16px; font-weight: bold;">
                FICHE DE STOCK
            </td>
        </tr>
        <tr>
            <td colspan="11" style="text-align: center;">
                Entrepôt: {{ $warehouse->name }} | Période: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </td>
        </tr>
        <tr></tr>
        
        <!-- En-têtes -->
        <tr style="background-color: #f0f0f0; font-weight: bold;">
            <td style="background-color: #007bff; color: white;">Date</td>
            <td style="background-color: #007bff; color: white;">Nom du produit</td>
            <td style="background-color: #007bff; color: white;">Stock initial</td>
            <td style="background-color: #007bff; color: white;">Entrée</td>
            <td style="background-color: #007bff; color: white;">Total</td>
            <td style="background-color: #007bff; color: white;">Sortie</td>
            <td style="background-color: #007bff; color: white;">Stock final</td>
            <td style="background-color: #007bff; color: white;">Prix unitaire</td>
            <td style="background-color: #007bff; color: white;">Prix total</td>
            <td style="background-color: #007bff; color: white;">Type de mouvement</td>
            <td style="background-color: #007bff; color: white;">Notes</td>
        </tr>
        
        <!-- Données -->
        @foreach($movements as $movement)
        <tr>
            <td style="background-color: #e3f2fd;">{{ $movement['date'] }}</td>
            <td style="background-color: #e3f2fd;">{{ $movement['product_name'] }}</td>
            <td style="text-align: right; background-color: #e3f2fd;">{{ number_format($movement['stock_initial'], 2) }}</td>
            <td style="text-align: right; background-color: #e3f2fd;">{{ number_format($movement['entree'], 2) }}</td>
            <td style="text-align: right; background-color: #e3f2fd;">{{ number_format($movement['total'], 2) }}</td>
            <td style="text-align: right; background-color: #e3f2fd;">{{ number_format($movement['sortie'], 2) }}</td>
            <td style="text-align: right; background-color: #e3f2fd;">{{ number_format($movement['stock_final'], 2) }}</td>
            <td style="text-align: right; background-color: #e3f2fd;">{{ number_format($movement['prix_unitaire'], 2) }}</td>
            <td style="text-align: right; background-color: #e3f2fd;">{{ number_format($movement['prix_total'], 2) }}</td>
            <td style="background-color: #e3f2fd;">{{ $movement['movement_type'] }}</td>
            <td style="background-color: #e3f2fd;">{{ $movement['notes'] }}</td>
        </tr>
        @endforeach
        
        <tr></tr>
        
    </table>
</body>
</html>