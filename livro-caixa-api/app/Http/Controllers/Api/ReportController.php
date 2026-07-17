<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movement;
use App\Services\BalanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

/**
 * Replaces the legacy rel_pdf.php / rel_date_pdf.php (near-duplicate files,
 * unified here into one "monthly" report) and rel_cx_periodo.php (period
 * report). Both reuse BalanceService, so the totals here can never drift
 * from what the dashboard/filters show — the legacy versions recomputed
 * balances with their own separate copy of the SQL each time.
 */
class ReportController extends Controller
{
    public function __construct(private readonly BalanceService $balances) {}

    public function monthly(Request $request): Response
    {
        $account = $request->currentAccount();
        $month = $request->integer('month', now()->month);
        $year = $request->integer('year', now()->year);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $pdf = Pdf::loadView('reports.movements', [
            'account' => $account,
            'title' => 'Relatório de Caixa — Demonstrativo Mensal',
            'reference' => $start->locale('pt_BR')->translatedFormat('F \d\e Y'),
            'movements' => $this->movementsBetween($account->id, $start, $end),
            'summary' => $this->balances->summaryForRange($account->id, $start, $end),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("relatorio-mensal-{$month}-{$year}.pdf");
    }

    public function period(Request $request): Response
    {
        $account = $request->currentAccount();

        $dateFrom = Carbon::parse($request->string('date_from'))->startOfDay();
        $dateTo = Carbon::parse($request->string('date_to'))->endOfDay();
        $search = $request->string('q')->value() ?: null;

        $pdf = Pdf::loadView('reports.movements', [
            'account' => $account,
            'title' => 'Relatório de Caixa — Período',
            'reference' => $dateFrom->format('d/m/Y').' a '.$dateTo->format('d/m/Y'),
            'movements' => $this->movementsBetween($account->id, $dateFrom, $dateTo, $search),
            'summary' => $this->balances->summaryForRange($account->id, $dateFrom, $dateTo),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('relatorio-periodo.pdf');
    }

    /**
     * @return Collection<int, Movement>
     */
    private function movementsBetween(int $accountId, Carbon $from, Carbon $to, ?string $search = null): Collection
    {
        $query = $this->balances->ledgerByDate($accountId)
            ->whereBetween('movement_date', [$from->toDateString(), $to->toDateString()]);

        if ($search) {
            $query->where('description', 'like', "%{$search}%");
        }

        return $query->with(['book', 'category'])
            ->orderBy('movement_date')->orderBy('movements.id')
            ->get();
    }
}
