<?php
namespace App\Controllers;
class CustomerController extends BaseCrudController
{ protected string $table='customers'; protected string $module='customers'; protected array $fields=['customer_code'=>['label'=>'Customer Code'],'company_name'=>['label'=>'Company Name'],'contact_person'=>['label'=>'Contact Person'],'email'=>['label'=>'Email','type'=>'email'],'mobile'=>['label'=>'Mobile'],'gst_number'=>['label'=>'GST Number'],'pan_number'=>['label'=>'PAN Number'],'status'=>['label'=>'Status','type'=>'select','options'=>[1=>'Active',0=>'Inactive']]]; protected array $search=['customer_code','company_name','email','mobile']; }
