<?php

namespace Stella\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stella\Clinic\Models\Invoice;
use Stringable;

class GetRevenueReport implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Generates a revenue and financial summary from clinic invoices. Returns total revenue, consultation fees, medicine fees, paid vs unpaid breakdowns, and payment method distribution. Optionally filter by start_date and end_date (YYYY-MM-DD format). Use this for financial overviews and profit tracking.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = Invoice::query();

        if (! empty($request['start_date'])) {
            $query->whereDate('created_at', '>=', $request['start_date']);
        }

        if (! empty($request['end_date'])) {
            $query->whereDate('created_at', '<=', $request['end_date']);
        }

        $invoices = $query->get();

        $paidInvoices = $invoices->where('status', 'paid');
        $unpaidInvoices = $invoices->where('status', 'unpaid');

        $paymentMethodBreakdown = $paidInvoices
            ->groupBy('payment_method')
            ->map(fn ($group) => [
                'count' => $group->count(),
                'total' => (float) $group->sum('total_amount'),
            ])
            ->toArray();

        $report = [
            'period' => [
                'start_date' => $request['start_date'] ?? 'all time',
                'end_date' => $request['end_date'] ?? 'all time',
            ],
            'total_invoices' => $invoices->count(),
            'total_revenue' => (float) $invoices->sum('total_amount'),
            'total_consultation_fees' => (float) $invoices->sum('consultation_fee'),
            'total_medicine_fees' => (float) $invoices->sum('medicine_fee'),
            'paid' => [
                'count' => $paidInvoices->count(),
                'total' => (float) $paidInvoices->sum('total_amount'),
            ],
            'unpaid' => [
                'count' => $unpaidInvoices->count(),
                'total' => (float) $unpaidInvoices->sum('total_amount'),
            ],
            'payment_method_breakdown' => $paymentMethodBreakdown,
        ];

        return json_encode($report);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'start_date' => $schema->string(),
            'end_date' => $schema->string(),
        ];
    }
}
