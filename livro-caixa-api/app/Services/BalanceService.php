<?php

namespace App\Services;

use App\Models\Movement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Replaces the legacy dados_pormes/dados_pordata/dados_porfolha/bal_*
 * queries — deeply nested correlated subqueries in raw SQL strings — with
 * a single running-balance window function plus small aggregate queries.
 * Same figures the original dashboard/filters/reports showed, computed in
 * a way that is testable and doesn't silently break when a column drifts.
 */
class BalanceService
{
    /**
     * A ledger of every movement for the account with a running balance
     * column, ordered chronologically (movement_date, then insertion order).
     *
     * @return Builder<Movement>
     */
    public function ledgerByDate(int $accountId): Builder
    {
        return $this->ledger($accountId, 'movement_date, movements.id');
    }

    /**
     * Same ledger, but ordered by where the movement actually sits in the
     * physical book (livro/folha), matching how the legacy bal_porfolha /
     * dados_porfolha reports accumulated balances.
     *
     * @return Builder<Movement>
     */
    public function ledgerByBookPage(int $accountId): Builder
    {
        return $this->ledger($accountId, 'book_id, page_number, movement_date, movements.id');
    }

    /**
     * @return Builder<Movement>
     */
    private function ledger(int $accountId, string $orderBy): Builder
    {
        $sub = Movement::query()
            ->where('account_id', $accountId)
            ->select('movements.*')
            ->selectRaw(
                "SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END) OVER (ORDER BY {$orderBy}) as running_balance"
            );

        return Movement::query()->fromSub($sub, 'movements');
    }

    /**
     * The account's balance strictly before $date (the "saldo anterior"
     * carried into whatever window is being displayed).
     */
    public function balanceBefore(int $accountId, Carbon $date): float
    {
        $row = $this->ledgerByDate($accountId)
            ->where('movement_date', '<', $date->toDateString())
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->first();

        return (float) ($row->running_balance ?? 0);
    }

    /**
     * Credit/debit/balance summary for an arbitrary date range, with the
     * opening balance carried from everything before $from.
     *
     * @return array{opening_balance: float, credits: float, debits: float, balance: float, closing_balance: float}
     */
    public function summaryForRange(int $accountId, Carbon $from, Carbon $to): array
    {
        $opening = $this->balanceBefore($accountId, $from);

        $totals = Movement::query()
            ->where('account_id', $accountId)
            ->whereBetween('movement_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw("SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as credits")
            ->selectRaw("SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as debits")
            ->first();

        $credits = (float) ($totals->credits ?? 0);
        $debits = (float) ($totals->debits ?? 0);

        return [
            'opening_balance' => $opening,
            'credits' => $credits,
            'debits' => $debits,
            'balance' => $credits - $debits,
            'closing_balance' => $opening + $credits - $debits,
        ];
    }

    /**
     * The monthly + year-to-date summary shown on the dashboard — the
     * legacy "BALANCO MENSAL" and "BALANCO ANUAL" panels.
     *
     * @return array{month: array<string, float>, year: array<string, float>}
     */
    public function summaryForMonth(int $accountId, int $month, int $year): array
    {
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $yearStart = Carbon::create($year, 1, 1)->startOfYear();

        return [
            'month' => $this->summaryForRange($accountId, $monthStart, $monthEnd),
            'year' => $this->summaryForRange($accountId, $yearStart, $monthEnd),
        ];
    }
}
