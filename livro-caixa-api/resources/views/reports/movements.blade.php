<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $title }}</title>
<style>
    body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #222; }

    h1 {
        font-size: 14px;
        text-align: center;
        background: #a0a0a0;
        color: #222;
        padding: 8px;
        margin: 0 0 14px;
    }

    .meta { margin-bottom: 12px; }
    .meta div { margin-bottom: 2px; }
    .meta strong { display: inline-block; width: 110px; }

    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #999; padding: 4px 6px; font-size: 9px; }
    th { background: #ccc; text-align: center; }

    .credit { color: #0000c0; }
    .debit { color: #b00000; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }

    tfoot td { background: #ddd; font-weight: bold; }
</style>
</head>
<body>
    <h1>{{ $title }}</h1>

    <div class="meta">
        <div><strong>Proprietário:</strong> {{ $account->owner_name }}</div>
        <div><strong>Conta:</strong> {{ $account->name }}</div>
        <div><strong>Referência:</strong> {{ $reference }}</div>
        <div><strong>Saldo Anterior:</strong> R$ {{ number_format($summary['opening_balance'], 2, ',', '.') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Seq.</th>
                <th>Data</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Livro/Folha</th>
                <th>Entradas</th>
                <th>Saídas</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $index => $movement)
                <tr class="{{ $movement->type->value }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $movement->movement_date->format('d/m/Y') }}</td>
                    <td>{{ $movement->description }}</td>
                    <td>{{ $movement->category?->name ?? '—' }}</td>
                    <td class="text-center">{{ $movement->book->number }}/{{ $movement->page_number }}</td>
                    <td class="text-right">
                        @if ($movement->type->value === 'credit')
                            R$ {{ number_format($movement->amount, 2, ',', '.') }}
                        @endif
                    </td>
                    <td class="text-right">
                        @if ($movement->type->value === 'debit')
                            R$ {{ number_format($movement->amount, 2, ',', '.') }}
                        @endif
                    </td>
                    <td class="text-right">R$ {{ number_format($movement->running_balance, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Nenhum lançamento neste período.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right">Totais:</td>
                <td class="text-right">R$ {{ number_format($summary['credits'], 2, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format($summary['debits'], 2, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format($summary['closing_balance'], 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
