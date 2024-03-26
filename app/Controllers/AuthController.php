<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\CIAuth;
use App\Libraries\Hash;
use App\Models\User;

class AuthController extends BaseController
{

    protected $helpers = ['url', 'form'];

    public function loginFrom()
    {
        $data = [
            'pageTitle'=>'Login',
            'validation'=>null
        ];
        return view('backend/pages/auth/login',$data);
    }
    public function loginHandler()
    {
        $fieldType = filter_var($this->request->getVar('login_id'),FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        if ($fieldType == 'email') {
             $isValid = $this->validate([
                'login_id'=>[
                    'rules'=>'required|valid_email|is_not_unique[users.email]',
                    'errors'=>[
                        'required'=> 'Email is required',
                        'valid_email'=>'Por favor, revise de que el correo sea valido',
                        'is_not_unique'=>'No se apodido encontrar el Email'
                    ]
                ],
                'password'=>[
                    'rules'=>'required|min_length[5]|max_length[45]|is_not_unique[users.password]',
                    'errors'=>[
                        'required'=> 'Introdusca la contraseña',
                        'min_length'=>'Su contraseña debe de tener minimo 5 caracteres',
                        'max_length'=>'El maximo de carecteres permitido es 45',
                        //'is_not_unique'=>'Contraseña no valida'
                    ]
                ]
             ]);
        } else {
            $isValid = $this->validate([
                'login_id'=>[
                    'rules'=>'required|is_not_unique[users.username]',
                    'errors'=>[
                        'required'=> 'Introdusca el nombre de usuario o email',
                        'is_not_unique'=>'No se apodido encontrar el Usuario'
                    ]
                ],
                'password'=>[
                    'rules'=>'required|min_length[5]|max_length[45]',
                    'errors'=>[
                        'required'=> 'Introdusca la contraseña',
                        'min_length'=>'Su contraseña debe de tener minimo 5 caracteres',
                        'max_length'=>'El maximo de carecteres permitido es 45',
                        //'is_not_unique'=>'Contraseña no valida'
                    ]
                ]
             ]);

        }
        if (!$isValid) {
            return view('backend/pages/auth/login',[
               'pageTitle'=>'Login',
               'validation'=>$this->validator
            ]);
        }
        else {
            $user = new User();
            $userInfo = $user->where($fieldType,$this->request->getVar('login_id'))->first();
            $check_password = Hash::check($this->request->getVar('password'),$userInfo['password']);

            if (!$check_password) {
                return redirect()->route('admin.login.form')->with('fail','Incorrecta la contraseña')->withInput();
            }else {
                CIAuth::setCIAuth($userInfo);
                return redirect()->route('admin.home');
            }
            
        }
    }
    public function forgotForm() 
    {
        $data = array(
            'pageTitle'=>'Forgot password',
            'validation'=>null,
        );
        return view('backend/pages/auth/forgot',$data);
    }
    public function sendPasswordReset()
    {
        
    }
}
