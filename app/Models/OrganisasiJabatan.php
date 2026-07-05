<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrganisasiJabatan extends Model
{
    protected $table = 'organisasi_jabatan';
        protected $primaryKey = 'id';
protected $fillable = ['organisasi_id', 'jabatan_master_id'];
}
