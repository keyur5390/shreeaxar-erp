<?php
namespace App\Controllers;
class ProductController extends BaseCrudController
{ protected string $table='products'; protected string $module='products'; protected array $fields=['product_code'=>['label'=>'Product Code'],'product_name'=>['label'=>'Product Name'],'description'=>['label'=>'Description','type'=>'textarea'],'category'=>['label'=>'Category'],'unit_id'=>['label'=>'Unit ID','type'=>'number'],'hsn_code'=>['label'=>'HSN Code'],'tax_id'=>['label'=>'Tax ID','type'=>'number'],'base_price'=>['label'=>'Base Price','type'=>'number'],'status'=>['label'=>'Status','type'=>'select','options'=>[1=>'Active',0=>'Inactive']]]; protected array $search=['product_code','product_name','category']; }
