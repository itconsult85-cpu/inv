<?php

namespace App\Models;

use CodeIgniter\Model;

class Modeluser extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields    = ['userid', 'usernama', 'userpassword', 'useraktif', 'userlevelid'];

    public function getUserByUserid($userid)
    {
        return $this->where('userid', $userid)->first();
    }
}
