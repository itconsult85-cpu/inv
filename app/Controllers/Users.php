<?php

namespace App\Controllers;

use App\Libraries\AccessControl;
use App\Controllers\BaseController;
use App\Models\Modeluser;
use \Hermawan\DataTables\DataTable;

class Users extends BaseController
{
    public function index()
    {
        return view('users/data');
    }

    private function editableLevelIds(): array
    {
        $db = \Config\Database::connect();

        return array_map('intval', array_column(
            $db->table('levels')->select('levelid')->where('levelid !=', 5)->get()->getResultArray(),
            'levelid'
        ));
    }

    public function formtambahlevel()
    {
        if (!$this->request->isAJAX()) {
            return;
        }

        if (!AccessControl::can('utility.access.manage_level')) {
            return;
        }

        echo view('users/modaltambahlevel');
    }

    public function simpanlevel()
    {
        if (!$this->request->isAJAX()) {
            return;
        }

        if (!AccessControl::can('utility.access.manage_level')) {
            echo json_encode(['error' => 'Anda tidak punya akses untuk menambah role.']);
            return;
        }

        $validation = \Config\Services::validation();

        $valid = $this->validate([
            'levelnama' => [
                'rules' => 'required|is_unique[levels.levelnama]',
                'label' => 'Nama Role',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah ada',
                ]
            ],
        ]);

        if (!$valid) {
            echo json_encode(['error' => ['levelnama' => $validation->getError('levelnama')]]);
            return;
        }

        $levelnama = strtoupper(trim((string) $this->request->getVar('levelnama')));

        $db = \Config\Database::connect();
        $db->table('levels')->insert(['levelnama' => $levelnama]);

        echo json_encode(['sukses' => 'Role baru berhasil ditambahkan']);
    }

    public function akses()
    {
        if (!AccessControl::can('utility.access.view')) {
            return redirect()->to(site_url('main/index'));
        }

        AccessControl::syncPermissionCatalog();

        $db = \Config\Database::connect();
        $users = $db->table('users')
            ->select('userid, usernama, userlevelid, levelnama')
            ->join('levels', 'levelid = userlevelid')
            ->where('userlevelid !=', 5)
            ->orderBy('levelnama', 'ASC')
            ->orderBy('usernama', 'ASC')
            ->get()
            ->getResultArray();

        $selectedPermissionsByUser = [];

        foreach ($users as $user) {
            $selectedPermissionsByUser[$user['userid']] = AccessControl::selectedPermissions($user['userid'], (int) $user['userlevelid']);
        }

        return view('users/akses', [
            'users' => $users,
            'permissionsBySection' => AccessControl::permissionsBySection(),
            'selectedPermissionsByUser' => $selectedPermissionsByUser,
        ]);
    }

    public function simpanakses()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to(site_url('users/akses'));
        }

        if (!AccessControl::can('utility.access.save')) {
            echo json_encode(['error' => 'Anda tidak punya akses untuk mengatur hak akses user.']);
            return;
        }

        AccessControl::syncPermissionCatalog();

        $postedPermissions = (array) $this->request->getPost('permissions');
        $allowedPermissions = AccessControl::permissions();
        $allowedKeys = array_column($allowedPermissions, 'key');
        $sectionByPermission = [];

        foreach ($allowedPermissions as $permission) {
            $sectionByPermission[$permission['key']] = 'section.' . $permission['section_key'];
        }

        $db = \Config\Database::connect();
        $editableUserIds = array_column(
            $db->table('users')
                ->select('userid')
                ->where('userlevelid !=', 5)
                ->get()
                ->getResultArray(),
            'userid'
        );

        $permissionsByUser = [];
        $userid = (string) $this->request->getPost('userid');

        if ($userid !== '') {
            if (!in_array($userid, $editableUserIds, true)) {
                echo json_encode(['error' => 'User tidak valid.']);
                return;
            }

            $permissionsByUser[$userid] = $postedPermissions;
        } else {
            foreach ($editableUserIds as $editableUserId) {
                $permissionsByUser[$editableUserId] = (array) ($postedPermissions[$editableUserId] ?? []);
            }
        }

        if (empty($permissionsByUser)) {
            echo json_encode(['error' => 'Tidak ada user yang bisa disimpan.']);
            return;
        }

        foreach ($permissionsByUser as $permissionUserId => $selected) {
            $selected = array_values(array_unique(array_filter((array) $selected, static fn ($key) => in_array($key, $allowedKeys, true))));

            foreach ($selected as $permissionKey) {
                if (isset($sectionByPermission[$permissionKey])) {
                    $selected[] = $sectionByPermission[$permissionKey];
                }
            }

            $permissionsByUser[$permissionUserId] = array_values(array_unique($selected));
        }

        $db->transStart();
        foreach ($permissionsByUser as $permissionUserId => $selected) {
            $db->table('user_access_permissions')->where('userid', $permissionUserId)->delete();

            foreach ($selected as $permissionKey) {
                $db->table('user_access_permissions')->insert([
                    'userid' => $permissionUserId,
                    'permission_key' => $permissionKey,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        $db->transComplete();

        if ($db->transStatus() === false) {
            echo json_encode(['error' => 'Hak akses gagal disimpan.']);
            return;
        }

        echo json_encode([
            'sukses' => 'Hak akses user berhasil disimpan.',
            'total' => array_sum(array_map('count', $permissionsByUser)),
            csrf_token() => csrf_hash(),
        ]);
    }

    function listData()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();
            $builder = $db->table('users')->select('id, userid, usernama, levelnama, useraktif, userlevelid')
                ->join('levels', 'levelid = userlevelid');

            // Ambil userlevelid dari session
            $userLevelId = session()->get('idlevel');

            // SEMENTARA dimatiin -- semua level bisa lihat semua user dulu
            // (termasuk MANAGEMENT & SUPER ADMINISTRATOR). Tinggal uncomment
            // lagi kalau mau balikin batasan visibility per level.
            // if ($userLevelId == 1) {
            //     $builder->whereNotIn('userlevelid', [4, 5]);
            // } elseif ($userLevelId == 4) {
            //     $builder->whereIn('userlevelid', [1, 2, 3, 4]);
            // } elseif ($userLevelId == 5) {
            //     $builder->whereIn('userlevelid', [1, 2, 3, 4]);
            // } else {
            //     // Jika userlevelid bukan 1, 4, atau 5, maka tidak ada filter tambahan
            // }

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('status', function ($row) {
                    if ($row->useraktif == '1') {
                        return '<span class="badge badge-success">Active</span>';
                    } else {
                        return '<span class="badge badge-danger">Non Active</span>';
                    }
                })
                ->add('aksi', function ($row) {
                    if ($row->userlevelid != '1' && $row->userlevelid != '5') {
                        return "<button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"view('" . $row->id . "','" . $row->userid . "')\">View</button>";
                    }
                    if ($row->userlevelid != '5') {
                        return "<button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"view('" . $row->id . "','" . $row->userid . "')\">View</button>";
                    }
                })
                ->toJson(true);
        }
    }

    public function listDataModal()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();
            $builder = $db->table('users')->select('id,userid,usernama')
                ->join('levels', 'levelid = userlevelid');;

            // Ambil userlevelid dari session
            $userLevelId = session()->get('idlevel');

            // Tambahkan kondisi filter berdasarkan userlevelid
            if ($userLevelId == 1) {
                $builder->whereNotIn('userlevelid', [4, 5]);
            } elseif ($userLevelId == 2) {
                $builder->whereIn('userlevelid', [2, 3]);
            } elseif ($userLevelId == 4) {
                $builder->whereIn('userlevelid', [1, 2, 3, 4]);
            } elseif ($userLevelId == 5) {
                $builder->whereIn('userlevelid', [1, 2, 3, 4]);
            } else {
                // Jika userlevelid bukan 1, 4, atau 5, maka tidak ada filter tambahan
            }

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    return "<button type=\"button\" class=\"btn btn-sm btn-info\" title=\"Pilih Data\" onclick=\"pilih('" . $row->id . "','" . $row->usernama . "')\"><i class=\"fa fa-check\"></i></button>&nbsp<button type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Hapus Data\" onclick=\"hapus('" . $row->id . "','" . $row->usernama . "')\"><i class=\"fa fa-trash-alt\"></i></button>";
                })
                ->toJson(true);
        }
    }

    public function modalData()
    {
        if ($this->request->isAJAX()) {
            $json = [
                'data' => view('users/modaldata')
            ];
            echo json_encode($json);
        }
    }

    function formtambah()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();

            $levels = $db->table('levels')->where('levelid !=', '5')->get()->getResultArray();

            $defaultPermissionsByLevel = [];
            foreach ($levels as $lvl) {
                $defaultPermissionsByLevel[(int) $lvl['levelid']] = AccessControl::defaultPermissionKeysForLevel((int) $lvl['levelid']);
            }

            $data = [
                'datalevel' => $levels,
                'permissionsBySection' => AccessControl::permissionsBySection(),
                'defaultPermissionsByLevel' => $defaultPermissionsByLevel,
            ];
            echo view('users/modaltambah', $data);
        }
    }

    function formedit()
    {
        if ($this->request->isAJAX()) {
            $iduser = $this->request->getPost('iduser');
            $modelUser = new Modeluser();
            $rowUser = $modelUser->find($iduser);

            if ($rowUser) {
                $db = \Config\Database::connect();

                $data = [
                    'datalevel' => $db->table('levels')
                        ->where('levelid !=', '5')
                        ->get(),
                    'iduser' => $iduser,
                    'userid' => $rowUser['userid'],
                    'namalengkap' => $rowUser['usernama'],
                    'level' => $rowUser['userlevelid'],
                    'status' => $rowUser['useraktif']
                ];
                echo view('users/modaledit', $data);
            }
        }
    }

    public function simpan()
    {
        if ($this->request->isAJAX()) {
            $iduser = $this->request->getVar('iduser');
            $namalengkap = $this->request->getVar('namalengkap');
            $level = $this->request->getVar('level');
            $password = $this->request->getVar('password');
            $passwordConfirm = $this->request->getVar('password_confirm');
            $permissions = (array) $this->request->getVar('permissions');

            $validation = \Config\Services::validation();

            $valid = $this->validate([
                'iduser' => [
                    'rules' => 'required|is_unique[users.userid]',
                    'label' => 'ID User',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'is_unique' => '{field} tidak boleh ada yang sama',
                    ]
                ],
                'namalengkap' => [
                    'rules' => 'required',
                    'label' => 'Nama Lengkap',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                    ]
                ],
                'level' => [
                    'rules' => 'required|in_list[' . implode(',', $this->editableLevelIds()) . ']',
                    'label' => 'Level',
                    'errors' => [
                        'required' => '{field} harus di pilih',
                        'in_list' => '{field} tidak valid',
                    ]
                ],
                'password' => [
                    'rules' => 'required|min_length[8]',
                    'label' => 'Password',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'min_length' => '{field} harus memiliki minimal {param} karakter',
                    ]
                ],
                'password_confirm' => [
                    'rules' => 'required|matches[password]',
                    'label' => 'Konfirmasi Password',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'matches' => '{field} tidak sama dengan Password',
                    ]
                ],
            ]);

            if (!$valid) {
                $error = [
                    'iduser' => $validation->getError('iduser'),
                    'namalengkap' => $validation->getError('namalengkap'),
                    'level' => $validation->getError('level'),
                    'password' => $validation->getError('password'),
                    'password_confirm' => $validation->getError('password_confirm'),
                ];

                $json = [
                    'error' => $error
                ];
            } else {
                $modelUser = new Modeluser();
                $hashPassword = password_hash($password, PASSWORD_DEFAULT);

                $modelUser->insert([
                    'userid'  => $iduser,
                    'usernama' => $namalengkap,
                    'userlevelid' => $level,
                    'userpassword' => $hashPassword
                ]);

                if (!empty($permissions)) {
                    AccessControl::saveUserPermissions($iduser, $permissions);
                } else {
                    AccessControl::grantDefaultPermissionsForUser($iduser, (int) $level);
                }

                $json = [
                    'sukses' => 'Simpan data user berhasil'
                ];
            }

            echo json_encode($json);
        }
    }


    function update()
    {
        if ($this->request->isAJAX()) {
            $iduser = $this->request->getVar('iduser');
            $userid = $this->request->getVar('userid');
            $namalengkap = $this->request->getVar('namalengkap');
            $level = $this->request->getVar('level');

            $validation = \Config\Services::validation();
            $valid = $this->validate([
                'userid' => [
                    'rules' => 'required|is_unique[users.userid,id,' . $iduser . ']',
                    'label' => 'ID User',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'is_unique' => '{field} sudah digunakan user lain',
                    ]
                ],
                'namalengkap' => [
                    'rules' => 'required',
                    'label' => 'Nama Lengkap',
                    'errors' => ['required' => '{field} tidak boleh kosong']
                ],
                'level' => [
                    'rules' => 'required|in_list[' . implode(',', $this->editableLevelIds()) . ']',
                    'label' => 'Level',
                    'errors' => [
                        'required' => '{field} harus dipilih',
                        'in_list' => '{field} tidak valid',
                    ]
                ],
            ]);

            if (!$valid) {
                $json = [
                    'error' => [
                        'userid' => $validation->getError('userid'),
                        'namalengkap' => $validation->getError('namalengkap'),
                        'level' => $validation->getError('level'),
                    ]
                ];
            } else {
                $modelUser = new Modeluser();
                $modelUser->update($iduser, [
                    'userid'  => $userid,
                    'usernama' => $namalengkap,
                    'userlevelid' => $level
                ]);

                $json = [
                    'sukses' => 'Update data user berhasil'
                ];
            }


            echo json_encode($json);
        }
    }

    function updateStatus()
    {
        if ($this->request->isAJAX()) {
            $iduser = $this->request->getVar('iduser');
            $modelUser = new Modeluser();
            $rowuser = $modelUser->find($iduser);

            if (!$rowuser) {
                echo json_encode(['error' => 'User tidak ditemukan']);
                return;
            }

            $useraktif = $rowuser['useraktif'];

            if ($useraktif == '1') {
                $modelUser->update($iduser, [
                    'useraktif' => '0'
                ]);
            } else {
                $modelUser->update($iduser, [
                    'useraktif' => '1'
                ]);
            }

            $json = [
                'sukses' => ''
            ];

            echo json_encode($json);
        }
    }

    function hapus()
    {
        if ($this->request->isAJAX()) {
            $iduser = $this->request->getPost('iduser');
            $modelUser = new Modeluser();
            $modelUser->delete($iduser);

            $json = [
                'sukses' => 'ID User berhasil di hapus'
            ];
            echo json_encode($json);
        }
    }

    function resetPassword()
    {
        if ($this->request->isAJAX()) {
            $iduser = $this->request->getPost('iduser');
            $modelUser = new Modeluser();
            $passRandom = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

            $passHashBaru = password_hash($passRandom, PASSWORD_DEFAULT);

            $modelUser->update($iduser, [
                'userpassword' => $passHashBaru
            ]);

            $json = [
                'sukses' => '',
                'passwordBaru' => $passRandom
            ];
            echo json_encode($json);
        }
    }
}
