<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class TaxReportHistory extends BaseModel
{
    protected static string $table = 'tax_report_history';
    protected static string $pk    = 'report_id';

    public static function listByBranch(int $branchId): array
    {
        return Database::select(
            "SELECT * FROM tax_report_history WHERE branch_id = ? OR branch_id = 0
             ORDER BY generated_date DESC",
            [$branchId]
        );
    }

    public static function create(array $d): int
    {
        return self::insertRow([
            'report_year'   => $d['report_year'],
            'report_month'  => $d['report_month'] ?? 0,
            'branch_id'     => $d['branch_id'] ?? 0,
            'entity_type'   => $d['entity_type'],
            'regime_used'   => $d['regime_used'],
            'total_sales'   => $d['total_sales'],
            'total_expenses'=> $d['total_expenses'],
            'cost_of_goods_sold' => $d['cost_of_goods_sold'],
            'gross_profit'  => $d['gross_profit'],
            'net_profit'    => $d['net_profit'],
            'annual_turnover' => $d['annual_turnover'],
            'estimated_tax' => $d['estimated_tax'],
            'vat_amount'    => $d['vat_amount'] ?? 0,
            'vat_required'  => $d['vat_required'] ?? 0,
            'corporate_rate_used'    => $d['corporate_rate_used'] ?? null,
            'presumptive_rate_used'  => $d['presumptive_rate_used'] ?? null,
            'vat_rate_used'          => $d['vat_rate_used'] ?? null,
            'presumptive_bracket_used'=> $d['presumptive_bracket_used'] ?? null,
            'generated_date' => now(),
            'generated_by'   => $d['generated_by'] ?? null,
            'notes'          => $d['notes'] ?? null,
        ]);
    }
}
