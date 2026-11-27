<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Support\Contracts\ContractOverviewData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPdf\Facades\Pdf;

class ContractOverviewPdfController
{
    public function __invoke(Request $request, Contract $contract, ContractOverviewData $data)
    {
        Gate::authorize('view', $contract);

        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $overview = $data->build($contract, $filters['from'] ?? null, $filters['to'] ?? null);

        return Pdf::view('pdf.contracts.overview', [
            'contract' => $contract->load('customer'),
            'summary' => $overview['summary'],
            'rows' => $overview['table_rows'],
            'periodLabel' => $data->periodLabel($filters['from'] ?? null, $filters['to'] ?? null),
            'generatedAt' => now(),
        ])
            ->driver('dompdf')
            ->format('a4')
            ->landscape()
            ->margins(10, 10, 10, 10)
            ->meta(title: 'Επισκόπηση Σύμβασης '.$contract->display_title)
            ->inline('episkopisi-symvasis-'.$contract->id.'.pdf');
    }
}
