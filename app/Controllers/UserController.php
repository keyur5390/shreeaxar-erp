<?php
namespace App\Controllers;
use App\Core\Database; use App\Support\Auth; use App\Support\Audit; use App\Support\Flash;
class UserController extends BaseCrudController
{ protected string $table='users'; protected string $module='users'; protected array $fields=['name'=>['label'=>'Name'],'email'=>['label'=>'Email','type'=>'email'],'employee_code'=>['label'=>'Employee Code'],'phone'=>['label'=>'Phone'],'is_active'=>['label'=>'Active','type'=>'select','options'=>[1=>'Active',0=>'Inactive']]]; protected array $search=['name','email','employee_code'];
 protected function payload(): array { $d=parent::payload(); if(!empty($_POST['password'])) $d['password']=password_hash($_POST['password'], PASSWORD_DEFAULT); elseif(empty($d['password'])) unset($d['password']); return $d; }
 public function create(): string { Auth::requirePermission('users.create'); $roles=Database::connect()->query('SELECT * FROM roles ORDER BY name')->fetchAll(); return view('users.form',['row'=>[],'roles'=>$roles,'assigned'=>[],'title'=>'Create User']); }
 public function edit(int $id): string { Auth::requirePermission('users.edit'); $roles=Database::connect()->query('SELECT * FROM roles ORDER BY name')->fetchAll(); $assigned=Database::connect()->query('SELECT role_id FROM role_user WHERE user_id='.(int)$id)->fetchAll(\PDO::FETCH_COLUMN); return view('users.form',['row'=>$this->repo()->find($id),'roles'=>$roles,'assigned'=>$assigned,'title'=>'Edit User']); }
 public function store(): never { Auth::requirePermission('users.create'); $data=$this->payload(); $data['password'] ??= password_hash('Password@123', PASSWORD_DEFAULT); $id=$this->repo()->create($data); $this->syncRoles($id); Audit::log('created','users',$id,[],$data); Flash::set('success','User saved.'); redirect('/users'); }
 public function update(int $id): never { Auth::requirePermission('users.edit'); $data=$this->payload(); $this->repo()->update($id,$data); $this->syncRoles($id); Audit::log('updated','users',$id,[],$data); Flash::set('success','User saved.'); redirect('/users'); }
 private function syncRoles(int $id): void { $db=Database::connect(); $db->prepare('DELETE FROM role_user WHERE user_id=?')->execute([$id]); foreach(($_POST['roles']??[]) as $rid) $db->prepare('INSERT INTO role_user (role_id,user_id) VALUES (?,?)')->execute([(int)$rid,$id]); }
}
