<?php
namespace App\Modules\Users\Services;

use App\Models\User;
use App\Modules\Core\Services\Service;
use Illuminate\Support\Facades\Hash;


class UserService extends Service
{
    public function __construct(User $model) {
        Parent::__construct($model);
    }  

    protected array $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
    ];
    

    public function registerUser($data) {
        $data['password'] = Hash::make($data['password']);

    }
    
}