<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;
use CodeIgniter\API\ResponseTrait;

class ForgotPassword extends BaseController
{
    protected $usersModel;
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
        $email = $this->request->getVar('email');

        $rules = [
            'email' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => '{field} harus diisi',
                    'valid_email' => '{field} tidak valid',
                ]
            ]
        ];

        if ($this->validate($rules)) {
            $user = $this->usersModel->where('email', $email)->first();

            if (!$user) {
                $email = array('email' => 'email tidak terdaftar');
                $response = [
                    'status' => 'fail',
                    'errors' => $email
                ];
                return $this->respond($response, 404);
            } else {
                $token = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

                $mailMe = getenv('MAIL_USERNAME');
                $gmail = service('email');
                $gmail->setTo($email);
                $gmail->setFrom($mailMe, 'forget password');

                $gmail->setSubject('New Password');
                $gmail->setMessage('password baru kamu = ' . $token);

                if ($gmail->send()) {
                    $options = [
                        'cost' => 10,
                    ];
                    $password = password_hash($token, PASSWORD_BCRYPT, $options);

                    $this->usersModel->set('password', $password);
                    $this->usersModel->where('email', $email);
                    $this->usersModel->update();
                    return $this->respond([
                        'status' => 'succes',
                        'message' => 'password baru telah dikirim'
                    ], 201);
                }
            }
        } else {
            $response = [
                'status' => 'fail',
                'errors' => $this->validator->getErrors()
            ];

            return $this->respond($response, 422);
        }
    }
}
