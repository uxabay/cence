<!doctype html>
<html lang="el">
<head>
    <meta charset="utf-8">
    <title>Επισκόπηση Σύμβασης – {{ $contract->display_title }}</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        body { color: #172033; font: 9pt/1.4 "DejaVu Sans", sans-serif; }
        h1 { margin: 0 0 4px; font-size: 17pt; }
        p { margin: 3px 0; }
        .muted { color: #596579; }
        .summary { width: 100%; margin: 16px 0; border-spacing: 6px; }
        .summary td { width: 33.33%; padding: 9px; border: 1px solid #d7dde6; vertical-align: top; }
        .label { color: #596579; font-size: 8pt; }
        .value { margin-top: 4px; font-size: 12pt; font-weight: bold; }
        .note { margin-top: 3px; color: #596579; font-size: 7.5pt; }
        table.data { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .data thead { display: table-header-group; }
        .data th, .data td { padding: 6px; border: 1px solid #cbd2dc; text-align: left; vertical-align: top; overflow-wrap: break-word; }
        .data th { background: #edf1f6; font-weight: bold; }
        .data tr { page-break-inside: avoid; }
        .data tr.total { background: #edf1f6; font-weight: bold; }
        .right { text-align: right !important; }
        .footer { margin-top: 10px; color: #596579; font-size: 7.5pt; }
    </style>
</head>
<body>
    <h1>Επισκόπηση σύμβασης</h1>
    <p><strong>{{ $contract->display_title }}</strong></p>
    <p>Πελάτης: {{ $contract->customer?->name ?? '—' }}</p>
    <p class="muted">{{ $periodLabel }} · Εκτυπώθηκε {{ $generatedAt->format('d/m/Y H:i') }}</p>
    <p class="muted">Ο προϋπολογισμός αφορά όλη τη διάρκεια της σύμβασης.</p>

    <table class="summary">
        <tr>
            <td><div class="label">Προϋπολογισμός σύμβασης</div><div class="value">{{ number_format($summary['forecasted_amount'], 2, ',', '.') }} €</div><div class="note">Συνολικός, μετά τις ενεργές αναθεωρήσεις</div></td>
            <td><div class="label">Υλοποιημένο ποσό</div><div class="value">{{ number_format($summary['actual_amount'], 2, ',', '.') }} €</div><div class="note">{{ $periodLabel }}</div></td>
            <td><div class="label">Οικονομικό υπόλοιπο / υπέρβαση</div><div class="value">{{ $summary['amount_balance'] }}</div><div class="note">Υλοποίηση {{ number_format($summary['progress'], 1, ',', '.') }}%</div></td>
        </tr>
        <tr>
            <td><div class="label">Προβλεπόμενα δείγματα</div><div class="value">{{ number_format($summary['forecasted_samples'], 0, ',', '.') }}</div><div class="note">Συνολικά για τη σύμβαση</div></td>
            <td><div class="label">Υλοποιημένα δείγματα</div><div class="value">{{ number_format($summary['actual_samples'], 0, ',', '.') }}</div><div class="note">{{ $periodLabel }}</div></td>
            <td><div class="label">Υπόλοιπο / υπέρβαση δειγμάτων</div><div class="value">{{ $summary['samples_balance'] }}</div><div class="note">Σε σύγκριση με το σύνολο της σύμβασης</div></td>
        </tr>
    </table>

    <table class="data">
        <thead><tr><th style="width:24%">Κατηγορία</th><th style="width:19%">Δείγματα (πρόβλεψη / υλοποίηση)</th><th style="width:20%">Προϋπολογισμός / υλοποιημένο</th><th style="width:23%">Υπόλοιπο / υπέρβαση</th><th style="width:14%">Υλοποίηση</th></tr></thead>
        <tbody>
        @foreach ($rows as $row)
            <tr @class(['total' => $row['is_total']])>
                <td>{{ $row['category_name'] }}</td>
                <td class="right">{{ number_format($row['forecasted_samples'], 0, ',', '.') }}<br><span class="muted">Υλοπ. {{ number_format($row['actual_samples'], 0, ',', '.') }}</span></td>
                <td class="right">{{ number_format($row['forecasted_amount'], 2, ',', '.') }} €<br><span class="muted">Υλοπ. {{ number_format($row['actual_amount'], 2, ',', '.') }} €</span></td>
                <td class="right">{{ $row['amount_balance'] }}<br><span class="muted">Δείγματα: {{ str_replace(' δείγματα', '', $row['samples_balance']) }}</span></td>
                <td class="right">{{ number_format($row['progress'], 1, ',', '.') }}%</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <p class="footer">Η αναφορά δημιουργήθηκε από το Cence.</p>
</body>
</html>
