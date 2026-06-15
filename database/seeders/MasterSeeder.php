<?php

namespace Database\Seeders;

final class MasterSeeder extends AbstractSeeder
{
    public function run(): void
    {
        foreach ([['India','IND'],['United States','USA']] as $row) {
            $this->insertIgnore('countries', ['name' => $row[0], 'iso_code' => $row[1], 'status' => 1]);
        }
        $india = $this->idBy('countries', 'iso_code', 'IND');
        foreach (['Gujarat','Maharashtra','Rajasthan'] as $state) {
            $this->insertIgnore('states', ['country_id' => $india, 'name' => $state, 'status' => 1]);
        }
        $gujarat = $this->idBy('states', 'name', 'Gujarat');
        foreach (['Ahmedabad','Surat','Vadodara','Rajkot'] as $city) {
            $this->insertIgnore('cities', ['country_id' => $india, 'state_id' => $gujarat, 'name' => $city, 'status' => 1]);
        }
        foreach (['Billing','Shipping','Office','Factory'] as $name) {
            $this->insertIgnore('address_types', ['name' => $name, 'status' => 1]);
        }
        foreach ([['Piece','PCS'],['Square Feet','SQFT'],['Running Feet','RFT'],['Set','SET']] as $row) {
            $this->insertIgnore('units', ['name' => $row[0], 'code' => $row[1], 'status' => 1]);
        }
        foreach ([['GST 0%',0],['GST 5%',5],['GST 12%',12],['GST 18%',18],['GST 28%',28]] as $row) {
            $this->insertIgnore('taxes', ['name' => $row[0], 'rate' => $row[1], 'status' => 1]);
        }
        foreach ([['Indian Rupee','INR','₹',1],['US Dollar','USD','$',83.5]] as $row) {
            $this->insertIgnore('currencies', ['name' => $row[0], 'code' => $row[1], 'symbol' => $row[2], 'exchange_rate' => $row[3], 'status' => 1]);
        }
        foreach ([['Draft','draft','#6c757d',1,0],['Sent','sent','#0d6efd',2,0],['Approved','approved','#198754',3,1],['Rejected','rejected','#dc3545',4,1]] as $row) {
            $this->insertIgnore('quotation_statuses', ['name' => $row[0], 'slug' => $row[1], 'color' => $row[2], 'sort_order' => $row[3], 'is_final' => $row[4], 'status' => 1]);
        }
    }
}
