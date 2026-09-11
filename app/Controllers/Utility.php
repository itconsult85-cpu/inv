<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modeluser;
use Ifsnop\Mysqldump\Mysqldump;

class Utility extends BaseController
{
    public function index()
    {
        return view('utility/index');
    }

    public function doBackup()
    {
        try {
            $tglSekarang = date('dym');
            $backupFilePath = 'database/backup/dbbackup-' . $tglSekarang . '.sql';

            $dump = new Mysqldump('mysql:host=localhost;dbname=samplestock;port=3306', 'root', '');
            $dump->start($backupFilePath);

            // Mendapatkan URL lengkap untuk file backup
            $backupUrl = base_url($backupFilePath);

            // Mengatur tautan unduhan dalam pesan flash
            $pesan = "Backup Berhasil. <a href='" . $backupUrl . "'>Klik di sini</a> untuk mengunduh file backup.";
            session()->setFlashdata('pesan', $pesan);

            return redirect()->to('/utility/index');
        } catch (\Exception $e) {
            $pesan = "mysqldump-php error: " . $e->getMessage();
            session()->setFlashdata('pesan', $pesan);

            return redirect()->to('/utility/index');
        }
    }

    public function doRestore()
    {
        try {
            if ($this->request->getMethod() === 'post') {
                $file = $this->request->getFile('restore_file');
                if ($file !== null && $file->isValid() && !$file->hasMoved()) {
                    // Buat direktori jika belum ada
                    $backupPath = WRITEPATH . 'database/backup/';
                    if (!is_dir($backupPath)) {
                        mkdir($backupPath, 0755, true);
                    }

                    $restoreFilePath = $backupPath . $file->getName();
                    $file->move($backupPath);

                    // Konfigurasi database
                    $dbHost = 'localhost';
                    $dbUsername = 'root';
                    $dbPassword = ''; // Ganti dengan password Anda
                    $dbName = 'samplestock';

                    // Path lengkap ke mysql.exe
                    $mysqlPath = 'D:\xampp\mysql\bin\mysql.exe';

                    // Gunakan escapeshellarg untuk keamanan argumen
                    $command = sprintf(
                        '%s -h%s -u%s -p%s %s < %s',
                        escapeshellarg($mysqlPath),
                        escapeshellarg($dbHost),
                        escapeshellarg($dbUsername),
                        escapeshellarg($dbPassword),
                        escapeshellarg($dbName),
                        escapeshellarg($restoreFilePath)
                    );

                    // Logging perintah untuk debug
                    log_message('error', 'Restore command: ' . $command);

                    // Eksekusi perintah restore menggunakan exec untuk menangkap status dan output
                    $output = [];
                    $returnVar = 0;
                    exec($command, $output, $returnVar);

                    // Logging output untuk debug
                    log_message('error', 'Restore output: ' . implode("\n", $output));
                    log_message('error', 'Restore return status: ' . $returnVar);

                    if ($returnVar === 0) { // 0 berarti perintah dieksekusi tanpa error
                        $pesan = "Restore Database Berhasil.";
                    } else {
                        $pesan = "Error: Gagal merestore database. Output: " . implode("\n", $output);
                    }
                    session()->setFlashdata('pesan', $pesan);
                } else {
                    $pesan = "Error: Gagal mengunggah file backup.";
                    session()->setFlashdata('pesan', $pesan);
                }
            } else {
                $pesan = "Error: Metode HTTP tidak valid.";
                session()->setFlashdata('pesan', $pesan);
            }
        } catch (\Exception $e) {
            $pesan = "Error saat melakukan restore database: " . $e->getMessage();
            session()->setFlashdata('pesan', $pesan);
        }

        return redirect()->to('/utility/index');
    }

    public function gantipassword()
    {
        return view('utility/formgantipassword');
    }


    public function updatepassword()
    {
        if ($this->request->isAJAX()) {
            // Ambil data dari request
            $iduser = session()->get('userid');
            $passlama = $this->request->getPost('passlama');
            $passbaru = $this->request->getPost('passbaru');
            $confirmpassbaru = $this->request->getPost('confirmpassbaru');

            // Debug output: Cetak data yang diterima dari request
            // echo "<pre>";
            // echo "ID User: " . $iduser . "\n";
            // echo "Password Lama: " . $passlama . "\n";
            // echo "Password Baru: " . $passbaru . "\n";
            // echo "Konfirmasi Password Baru: " . $confirmpassbaru . "\n";
            // echo "</pre>";

            $validation = \Config\Services::validation();
            $valid = $this->validate([
                'passlama' => [
                    'rules' => 'required',
                    'label' => 'Password Lama',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong'
                    ]
                ],
                'passbaru' => [
                    'rules' => 'required|min_length[8]',
                    'label' => 'Password Baru',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'min_length' => '{field} harus memiliki minimal {param} karakter',
                    ]
                ],
                'confirmpassbaru' => [
                    'rules' => 'required|matches[passbaru]',
                    'label' => 'Konfirmasi Password',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'matches' => '{field} harus sama'
                    ]
                ]
            ]);

            if (!$valid) {
                $error = [
                    'passlama' => $validation->getError('passlama'),
                    'passbaru' => $validation->getError('passbaru'),
                    'confirmpassbaru' => $validation->getError('confirmpassbaru')
                ];

                $json = [
                    'error' => $error
                ];

                // Debug output: Cetak error validasi
                // echo "<pre>";
                // echo "Error Validasi:\n";
                // print_r($error);
                // echo "</pre>";
            } else {
                $modelUser = new Modeluser();
                $rowData = $modelUser->getUserByUserid($iduser);

                // Debug output: Cetak hasil query
                // echo "<pre>";
                // echo "Data Pengguna:\n";
                // print_r($rowData);
                // echo "</pre>";

                // Cek apakah $rowData null
                if ($rowData === null) {
                    $json = [
                        'error' => [
                            'passlama' => 'User tidak ditemukan'
                        ]
                    ];

                    // Debug output: User tidak ditemukan
                    // echo "<pre>";
                    // echo "User tidak ditemukan\n";
                    // echo "</pre>";
                } else {
                    $passUser = $rowData['userpassword'];

                    // Debug output: Cetak data pengguna dari database
                    // echo "<pre>";
                    // echo "Data Pengguna dari Database:\n";
                    // echo "Password Lama dari Database: " . $passUser . "\n";
                    // echo "Password Lama yang Dikirim: " . $passlama . "\n";
                    // echo "</pre>";

                    // Debug output: Cek apakah password lama cocok
                    if (password_verify($passlama, $passUser)) {
                        $hashPasswordBaru = password_hash($passbaru, PASSWORD_DEFAULT);
                        $modelUser->update($rowData['id'], [
                            'userpassword' => $hashPasswordBaru
                        ]);

                        $json = [
                            'sukses' => 'Password Berhasil diganti'
                        ];
                    } else {
                        $error = [
                            'passlama' => 'Password Lama tidak sama'
                        ];

                        $json = [
                            'error' => $error
                        ];
                    }
                }
            }

            // Debug output: Cetak JSON response sebelum dikirim
            // echo "<pre>";
            // echo "JSON Response:\n";
            // print_r($json);
            // echo "</pre>";

            echo json_encode($json);
        }
    }
}
