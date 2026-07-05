<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrganisasiJabatanAuthority extends Model
{
    protected $table = 'organisasi_jabatan_authority';
        protected $primaryKey = 'id';
protected $fillable = ['jabatan_master_id', 'authority_id'];
}
