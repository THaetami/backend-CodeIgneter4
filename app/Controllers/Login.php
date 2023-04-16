<?php

namespace App\Controllers;

use App\Models\UsersModel;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;

class Login extends BaseController
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
        $password = $this->request->getVar('password');

        $user = $this->usersModel->where('email', $email)->first();

        if (!$user) {
            return $this->fail([
                'message' => 'email tidak terdaftar'
            ], 402);
        }

        if (!password_verify($password, $user['password'])) {
            return $this->fail([
                'message' => 'email atau password salah'
            ], 400);
        }

        $key = getenv("JWT_TOKEN");
        $iat = time();
        $exp = $iat + (3600);

        $payload = [
            'iss' => 'auth-jwt',
            'sub' => 'logintoken',
            'iat' => $iat,
            'exp' => $exp,
            'id' => $user['id'],
            'email' => $user['email']
        ];

        $accessToken =  JWT::encode($payload, $key, "HS256");

        return $this->respondCreated([
            'accessToken' => $accessToken
        ], 'success');
    }
}
