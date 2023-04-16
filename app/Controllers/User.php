<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class User extends BaseController
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
        $key = getenv("JWT_TOKEN");

        $header = $this->request->getServer('HTTP_AUTHORIZATION');

        if (!$header) {
            return $this->failUnauthorized('Token Required');
        }

        $accessToken = explode(' ', $header)[1];

        try {
            $decoded = JWT::decode($accessToken, new Key($key, 'HS256'));

            $user = $this->usersModel->select(['id', 'name', 'profile', 'created_at', 'updated_at', 'email'])->where('email', $decoded->email)->first();

            return $this->respond([
                'status' => 'success',
                'user' => $user
            ], 200);
        } catch (\Throwable $th) {
            return $this->fail('Invalid accessToken');
        }
    }
}
