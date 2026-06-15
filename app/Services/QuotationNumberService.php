<?php
namespace App\Services;
use App\Core\Database;
final class QuotationNumberService
{
    public function next(): string
    {
        $prefix = 'QTN-'.date('Ym').'-';
        $stmt = Database::connect()->prepare('SELECT quotation_number FROM quotations WHERE quotation_number LIKE ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$prefix.'%']);
        $last = $stmt->fetchColumn(); $n = $last ? ((int)substr($last, -4) + 1) : 1;
        return $prefix.str_pad((string)$n, 4, '0', STR_PAD_LEFT);
    }
}
