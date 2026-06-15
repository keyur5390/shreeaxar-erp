<?php
use App\Core\Database; use App\Services\SchemaService; use App\Services\QuotationCalculationService; use App\Services\QuotationNumberService;
$_ENV['DB_CONNECTION']='sqlite'; $_ENV['DB_DATABASE']=':memory:'; Database::reset();
$schema=new SchemaService(); $schema->migrate(true); $schema->seed(); $db=Database::connect();
$assert=function(bool $ok,string $name){ if(!$ok){fwrite(STDERR,"FAIL: $name\n"); exit(1);} echo "PASS: $name\n"; };
$assert((int)$db->query('SELECT COUNT(*) FROM permissions')->fetchColumn()>=30,'permission matrix seeded');
$assert((int)$db->query("SELECT COUNT(*) FROM users WHERE email='admin@example.com'")->fetchColumn()===1,'default admin seeded');
$calc=(new QuotationCalculationService())->calculate([['quantity'=>2,'rate'=>100,'discount_type'=>'percent','discount_value'=>10,'tax_rate_snapshot'=>18]],'fixed',5);
$assert($calc['sub_total']===200.0 && $calc['item_discount_total']===20.0 && $calc['tax_total']===32.4 && $calc['grand_total']===207.4,'quotation totals deterministic');
$db->exec("INSERT INTO customers (customer_code,company_name,status,created_at,updated_at) VALUES ('C001','Acme',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
$db->exec("INSERT INTO quotations (quotation_number,customer_id,quotation_date,created_at,updated_at) VALUES ('QTN-".date('Ym')."-0001',1,'".date('Y-m-d')."',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
$assert((new QuotationNumberService())->next()==='QTN-'.date('Ym').'-0002','quotation number increments');
echo "All tests passed.\n";
