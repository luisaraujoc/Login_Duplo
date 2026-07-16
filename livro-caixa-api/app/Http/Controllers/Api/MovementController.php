<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MovementResource;
use App\Models\Movement;
use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class MovementController extends Controller
{
    public function __construct(private readonly BalanceService $balances) {}

    /**
     * Lists movements with a running balance per line, replacing the
     * legacy index.php / filtrarpordata.php / filtrarporfolha.php pages.
     *
     * - book_id + page_from/page_to: filtered & ordered by livro/folha
     * - date_from/date_to (or month/year, default current month): by date
     * - q: description search (both modes)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $accountId = $request->currentAccount()->id;

        if ($request->filled('book_id')) {
            $query = $this->balances->ledgerByBookPage($accountId)
                ->where('book_id', $request->integer('book_id'));

            if ($request->filled('page_from')) {
                $query->where('page_number', '>=', $request->integer('page_from'));
            }
            if ($request->filled('page_to')) {
                $query->where('page_number', '<=', $request->integer('page_to'));
            }
        } else {
            $query = $this->balances->ledgerByDate($accountId);

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('movement_date', [$request->string('date_from'), $request->string('date_to')]);
            } else {
                $month = $request->integer('month', now()->month);
                $year = $request->integer('year', now()->year);
                $start = Carbon::create($year, $month, 1)->startOfMonth();
                $query->whereBetween('movement_date', [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()]);
            }
        }

        if ($request->filled('q')) {
            $query->where('description', 'like', '%'.$request->string('q').'%');
        }

        $movements = $query->with(['book', 'category'])
            ->orderBy('movement_date')->orderBy('movements.id')
            ->paginate($request->integer('per_page', 20));

        return MovementResource::collection($movements);
    }

    /**
     * Balance cards for the dashboard/filters — legacy bal_pormes / bal_pordata.
     */
    public function summary(Request $request): JsonResponse
    {
        $accountId = $request->currentAccount()->id;

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $summary = $this->balances->summaryForRange(
                $accountId,
                Carbon::parse($request->string('date_from')),
                Carbon::parse($request->string('date_to')),
            );

            return response()->json(['data' => $summary]);
        }

        $summary = $this->balances->summaryForMonth(
            $accountId,
            $request->integer('month', now()->month),
            $request->integer('year', now()->year),
        );

        return response()->json(['data' => $summary]);
    }

    public function store(Request $request): MovementResource
    {
        $data = $this->validated($request);
        $data['account_id'] = $request->currentAccount()->id;

        $this->authorizeBookOwnership($request, $data['book_id']);

        $movement = Movement::query()->create($data);
        $movement->load(['book', 'category']);

        return new MovementResource($movement);
    }

    public function show(Request $request, Movement $movement): MovementResource
    {
        $this->authorizeAccount($request, $movement);
        $movement->load(['book', 'category']);

        return new MovementResource($movement);
    }

    public function update(Request $request, Movement $movement): MovementResource
    {
        $this->authorizeAccount($request, $movement);

        $data = $this->validated($request);
        $this->authorizeBookOwnership($request, $data['book_id']);

        $movement->update($data);
        $movement->load(['book', 'category']);

        return new MovementResource($movement);
    }

    public function destroy(Request $request, Movement $movement): Response
    {
        $this->authorizeAccount($request, $movement);

        $movement->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'page_number' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'in:credit,debit'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'movement_date' => ['required', 'date'],
        ]);
    }

    private function authorizeAccount(Request $request, Movement $movement): void
    {
        abort_unless($movement->account_id === $request->currentAccount()->id, 404);
    }

    private function authorizeBookOwnership(Request $request, int $bookId): void
    {
        $belongs = $request->currentAccount()->books()->whereKey($bookId)->exists();

        abort_unless($belongs, 422, 'Livro não pertence à conta atual.');
    }
}
