<?php

namespace App\Http\Controllers;

use App\Models\CashPeriod;
use App\Services\CashboxService;
use App\Services\CashDiagramService;
use Illuminate\Support\Facades\Auth;

class CashDiagramController extends Controller
{
    protected CashboxService $cashboxService;
    protected CashDiagramService $diagramService;

    public function __construct(CashboxService $cashboxService, CashDiagramService $diagramService)
    {
        $this->cashboxService = $cashboxService;
        $this->diagramService = $diagramService;
    }

    /**
     * Get diagram data as JSON
     * Requirement 9.1: Display diagram as tree with participant nodes
     */
    public function getDiagram(CashPeriod $period)
    {
        if (!Auth::user()->can('cashbox_access')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        // Check company ownership
        if ($period->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $tree = $this->diagramService->buildTree($period);
        $summary = $this->diagramService->getPeriodSummary($period);

        return response()->json([
            'success' => true,
            'diagram' => $tree,
            'summary' => $summary,
        ]);
    }

    /**
     * Get current user's balance for a period
     * Requirement 10.1: Display budget block with Received/Sent
     * Requirement 10.2: Auto-update balance on each transaction
     */
    public function getBalance(CashPeriod $period)
    {
        if (!Auth::user()->can('cashbox_access')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        // Check company ownership
        if ($period->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $balance = $this->cashboxService->getBalance($period, Auth::user());

        return response()->json([
            'success' => true,
            'balance' => [
                'received' => $balance['received'],
                'sent' => $balance['sent'],
                'available' => $balance['available'],
            ],
        ]);
    }
}
