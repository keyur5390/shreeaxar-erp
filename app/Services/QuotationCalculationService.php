<?php
namespace App\Services;

final class QuotationCalculationService
{
    public function calculate(array $items, string $globalType = 'fixed', float $globalValue = 0): array
    {
        $subTotal = $itemDiscountTotal = $taxTotal = 0.0; $calculated = [];
        foreach ($items as $i => $item) {
            $qty = max(0, (float)($item['quantity'] ?? 0)); $rate = max(0, (float)($item['rate'] ?? 0)); $base = $qty * $rate;
            $discountValue = max(0, (float)($item['discount_value'] ?? 0)); $discountType = $item['discount_type'] ?? 'fixed';
            $discount = $discountType === 'percent' ? $base * min($discountValue, 100) / 100 : min($discountValue, $base);
            $taxable = max(0, $base - $discount); $taxRate = max(0, (float)($item['tax_rate_snapshot'] ?? 0)); $tax = $taxable * $taxRate / 100; $line = $taxable + $tax;
            $subTotal += $base; $itemDiscountTotal += $discount; $taxTotal += $tax;
            $calculated[] = $item + ['discount_amount'=>round($discount,2), 'tax_amount'=>round($tax,2), 'line_total'=>round($line,2), 'sort_order'=>$i+1];
        }
        $afterItemDiscount = max(0, $subTotal - $itemDiscountTotal);
        $globalDiscount = $globalType === 'percent' ? $afterItemDiscount * min($globalValue, 100) / 100 : min($globalValue, $afterItemDiscount);
        $grandTotal = $afterItemDiscount - $globalDiscount + $taxTotal;
        return ['items'=>$calculated, 'sub_total'=>round($subTotal,2), 'item_discount_total'=>round($itemDiscountTotal,2), 'global_discount_amount'=>round($globalDiscount,2), 'tax_total'=>round($taxTotal,2), 'grand_total'=>round($grandTotal,2)];
    }
}
