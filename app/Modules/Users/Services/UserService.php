<?php
namespace App\Modules\Users\Services;

use App\Models\User;
use App\Modules\Core\Services\Service;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserService extends Service
{
    public function __construct(User $model) {
        Parent::__construct($model);
    }  

    protected array $rules = [
        'username' => 'required|string|max:100|unique:users',
        'password' => 'required|string|min:6',
    ];

    private array $credentialRules = [
        'username' => 'required|string',
        'password' => 'required|string',
    ];
    

    public function registerUser($data)
    {
        $validator = $this->validate($data);
    
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
    
        $data['password'] = Hash::make($data['password']);
    
        $user = $this->_model->create($data);
    
        return $user ? true : false;
    }

    function login($data) : ?string {

        $validator = Validator::make($data->all(), $this->credentialRules);
        if ($validator->fails()) return null;
    
        $credentials = $data->only('username', 'password');

        $token = auth()->attempt($credentials);

        return $token;
    }
}