<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;
use CodeIgniter\API\ResponseTrait;

class Register extends BaseController
{
    protected UsersModel $usersModel;
    use ResponseTrait;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
        Header('Access-Control-Allow-Origin: *');
        Header('Access-Control-Allow-Headers: *');
        Header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    }

    public function index()
    {
        $rules = [
            'name' => [
                'rules' => 'required|min_length[3]|trim|regex_match[/^[a-zA-Z0-9\s]+$/]',
                'errors' => [
                    'required' => '{field} harus diisi',
                    'min_length' => '{field} minimal 3 karakter',
                    'regex_match' => '{field} harus menggunakan karakter alphanumerik'
                ]
            ],
            'email' => [
                'rules' => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required' => '{field} harus diisi',
                    'valid_email' => '{field} tidak valid',
                    'is_unique' => '{field} sudah digunakan'
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[6]|alpha_numeric',
                'errors' => [
                    'required' => '{field} harus diisi',
                    'min_length' => '{field} minimal 6 karakter',
                    'alpha_numeric' => '{field} harus menggunakan karakter alphanumerik'
                ],
            ],
            'profile' => [
                'rules' => 'uploaded[profile]|max_size[profile,1024]|is_image[profile]|mime_in[profile,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'uploaded' => 'gambar tidak ditemukan',
                    'max_size' => 'Ukuran gambar terlalu besar',
                    'is_image' => 'File harus gambar',
                    'mime_in' => 'File harus gambar'
                ]
            ]
        ];

        if ($this->validate($rules)) {

            $profile = $this->request->getFile('profile');
            $profileName = $profile->getRandomName();
            $profile->move('gambar', $profileName);
            $options = [
                'cost' => 10,
            ];

            $this->usersModel->save([
                'name' => $this->request->getVar('name'),
                'email' => $this->request->getVar('email'),
                'password' => password_hash($this->request->getVar('password'), PASSWORD_BCRYPT, $options),
                'profile' => $profileName
            ]);

            return $this->respond([
                'status' => 'success',
                'message' => 'Registrasi berhasil !!'
            ], 200);
        } else {
            $response = [
                'status' => 'fail',
                'errors' => $this->validator->getErrors()
            ];

            return $this->respond($response, 422);
        }
    }
}
