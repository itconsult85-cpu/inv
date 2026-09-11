<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelLogin extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'id', 'userid', 'usernama', 'userpassword', 'userlevelid', 'useraktif'
    ];

    public function getUser($userid)
    {
        return $this->where('userid', $userid)->first();
    }
}
