<?php

namespace App\Http\Controllers\Api\V1\Wallet;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Resources\Api\V1\Wallet\WalletResource;
use App\Http\Resources\Api\V1\Wallet\WalletTransactionResource;
use App\Services\Wallet\WalletService;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WalletController extends Controller
{
    use RespondsWithApiResponse;

    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get the authenticated user's wallet details and summary metrics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        return $this->successResponse(
            new WalletResource($wallet),
            'Wallet details retrieved successfully.'
        );
    }

    /**
     * Get paginated transaction history for the user's wallet.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function transactions(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $query = WalletTransaction::where('wallet_id', $wallet->id);

        // Filter by transaction type (CREDIT/DEBIT)
        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->input('date_from')));
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->input('date_to')));
        }

        // Search in reference/description
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('provider_reference', 'like', "%{$search}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        $perPage = $request->integer('per_page', 15);
        $paginated = $query->paginate($perPage);

        return $this->successResponse([
            'items' => WalletTransactionResource::collection($paginated->items()),
            'pagination' => [
                'total' => $paginated->total(),
                'count' => $paginated->count(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'total_pages' => $paginated->lastPage(),
            ]
        ], 'Wallet transactions retrieved successfully.');
    }

    /**
     * Retrieve a single transaction with ownership verification.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function showTransaction(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $transaction = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('id', $id)
            ->first();

        if (!$transaction) {
            return $this->errorResponse('Transaction not found or access denied.', [], 404);
        }

        return $this->successResponse(
            new WalletTransactionResource($transaction),
            'Transaction details retrieved successfully.'
        );
    }

    /**
     * Get active funding methods directly from payment config.
     *
     * @return JsonResponse
     */
    public function fundingMethods(): JsonResponse
    {
        $gateways = config('payment.gateways', []);
        $methods = [];

        foreach ($gateways as $name => $config) {
            $methods[] = [
                'gateway' => $name,
                'enabled' => filter_var($config['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        return $this->successResponse($methods, 'Funding gateways retrieved successfully.');
    }
}
