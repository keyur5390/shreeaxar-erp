<?php

namespace Database\Seeders;

final class CompanySettingSeeder extends AbstractSeeder
{
    public function run(): void
    {
        $db = $this->db();
        if ((int) $db->query('SELECT COUNT(*) FROM company_settings')->fetchColumn() === 0) {
            $india = $this->idBy('countries', 'iso_code', 'IND');
            $gujarat = $this->idBy('states', 'name', 'Gujarat');
            $db->prepare('INSERT INTO company_settings (company_name,gst_number,pan_number,email,mobile,website,address_line_1,address_line_2,country_id,state_id,city_id,postal_code,is_active,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)')
                ->execute(['Shree Axar Furniture','24ABCDE1234F1Z5','ABCDE1234F','info@shreeaxar.local','+91 98765 43210','https://shreeaxar.local','Furniture Market Road','Ahmedabad',$india,$gujarat,$this->idBy('cities','name','Ahmedabad'),'380001']);
        }
    }
}
