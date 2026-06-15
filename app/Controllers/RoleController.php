<?php
namespace App\Controllers;
use App\Core\Database; use App\Support\Auth; use App\Support\Flash;
class RoleController extends BaseCrudController
{ protected string $table='roles'; protected string $module='roles'; protected array $fields=['name'=>['label'=>'Role Name','required'=>true]]; protected array $search=['name'];
  public function edit(int $id): string { Auth::requirePermission('roles.edit'); $permissions=Database::connect()->query('SELECT * FROM permissions ORDER BY name')->fetchAll(); $assigned=Database::connect()->query('SELECT permission_id FROM permission_role WHERE role_id='.(int)$id)->fetchAll(\PDO::FETCH_COLUMN); return view('roles.form',['row'=>$this->repo()->find($id),'permissions'=>$permissions,'assigned'=>$assigned,'title'=>'Edit Role']); }
  public function update(int $id): never { Auth::requirePermission('roles.edit'); $this->repo()->update($id,['name'=>$_POST['name']]); $db=Database::connect(); $db->prepare('DELETE FROM permission_role WHERE role_id=?')->execute([$id]); foreach(($_POST['permissions']??[]) as $pid){ $db->prepare('INSERT INTO permission_role (permission_id,role_id) VALUES (?,?)')->execute([(int)$pid,$id]); } Flash::set('success','Role updated.'); redirect('/roles'); }
}
