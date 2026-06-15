<?php
namespace App\Controllers;
use App\Repositories\TableRepository; use App\Support\Audit; use App\Support\Auth; use App\Support\Flash;
abstract class BaseCrudController
{
    protected string $table; protected string $module; protected array $fields=[]; protected array $search=['name']; protected string $view='partials.crud';
    protected function repo(): TableRepository { return new TableRepository($this->table); }
    public function index(): string { Auth::requirePermission($this->module.'.view'); $rows=$this->repo()->paginate($_GET['search']??'', $this->search); return view($this->view, ['mode'=>'index','rows'=>$rows,'fields'=>$this->fields,'module'=>$this->module,'title'=>$this->title()]); }
    public function create(): string { Auth::requirePermission($this->module.'.create'); return view($this->view, ['mode'=>'form','row'=>[],'fields'=>$this->fields,'module'=>$this->module,'title'=>'Create '.$this->title()]); }
    public function store(): never { Auth::requirePermission($this->module.'.create'); $data=$this->payload(); $id=$this->repo()->create($data); Audit::log('created', $this->table, $id, [], $data); Flash::set('success', $this->title().' created.'); redirect('/'.$this->module); }
    public function edit(int $id): string { Auth::requirePermission($this->module.'.edit'); return view($this->view, ['mode'=>'form','row'=>$this->repo()->find($id),'fields'=>$this->fields,'module'=>$this->module,'title'=>'Edit '.$this->title()]); }
    public function update(int $id): never { Auth::requirePermission($this->module.'.edit'); $old=$this->repo()->find($id)??[]; $data=$this->payload(); $this->repo()->update($id,$data); Audit::log('updated', $this->table, $id, $old, $data); Flash::set('success', $this->title().' updated.'); redirect('/'.$this->module); }
    public function delete(int $id): never { Auth::requirePermission($this->module.'.delete'); $this->repo()->softDelete($id); Audit::log('deleted', $this->table, $id); Flash::set('success', $this->title().' deleted.'); redirect('/'.$this->module); }
    protected function payload(): array { $data=[]; foreach($this->fields as $name=>$meta) if(($meta['type']??'text')!=='readonly') $data[$name]=$_POST[$name] ?? ($meta['default'] ?? null); return $data; }
    protected function title(): string { return ucwords(str_replace('-', ' ', $this->module)); }
}
