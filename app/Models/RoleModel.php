<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends BaseModel
{
    protected $table         = 'roles';
    protected $primaryKey    = 'id_role';
    protected $allowedFields = ['nama_role'];
    protected $returnType    = 'array';
}