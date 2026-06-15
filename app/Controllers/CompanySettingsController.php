<?php
namespace App\Controllers;
use App\Core\Database; use App\Support\Auth; use App\Support\Flash;
class CompanySettingsController
{ public function edit(): string { Auth::requirePermission('company-settings.view'); $row=Database::connect()->query('SELECT * FROM company_settings ORDER BY id DESC LIMIT 1')->fetch() ?: []; return view('company.form', compact('row')); }
 public function update(): never { Auth::requirePermission('company-settings.edit'); $fields=['company_name','gst_number','pan_number','email','mobile','website','address_line_1','address_line_2','postal_code']; $data=[]; foreach($fields as $f)$data[$f]=$_POST[$f]??''; $db=Database::connect(); $id=(int)($_POST['id']??0); if($id){$sets=implode(',',array_map(fn($f)=>"$f=?",array_keys($data))); $v=array_values($data);$v[]=$id;$db->prepare("UPDATE company_settings SET $sets, updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute($v);} Flash::set('success','Company settings updated.'); redirect('/company-settings'); }}
