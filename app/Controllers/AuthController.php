<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\CIAuth;
use App\Libraries\Hash;
use App\Models\User;
use App\Models\PasswordResetToken;
use Carbon\Carbon;

class AuthController extends BaseController
{

    protected $helpers = ['url', 'form','CIMail','CIFunctions'];

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
        $isValid = $this->validate([
            'email'=>[
                'rules'=>'required|valid_email|is_not_unique[users.email]',
                'errors'=>[
                    'required'=>'Email es requerido',
                    'valid_email'=>'Por favor introdusca un email valido',
                    'is_not_unique'=>'Este email no existe en el sistema',
                ], 
            ],
        ]);
        if (!$isValid) {
            return view('backend/pages/auth/forgot',[
                'pageTitle'=>'Forgot password',
                'validation'=>$this->validator,
            ]);
        } else {
            $user = new User();
            $user_info = $user->asObject()->where('email',$this->request->getVar('email'))->first();

            //Genera un token
            $token = bin2hex(openssl_random_pseudo_bytes(65));

            //Para resetear el token
            $password_reset_token = new PasswordResetToken();
            $isOldTokenExists = $password_reset_token->asObject()->where('email',$user_info->email)->first();

            if ($isOldTokenExists) {
                //Actualiza el token existente
                $password_reset_token->where('email',$user_info->email)->set(['token'=>$token,'created_at'=>Carbon::now()])->update();
            } else {
                $password_reset_token->insert([
                    'email'=>$user_info->email,
                    'token'=>$token,
                    'created_at'=>Carbon::now()
                ]);
            }

            //$actionLink = route_to('admin.reset-password',$token);
            $actionLink = base_url(route_to('admin.reset-password',$token));

            $mail_data = array(
                'actionLink'=>$actionLink,
                'user'=>$user_info,
            );
            $view = \Config\Services::renderer();
            $mail_body = $view->setVar('mail_data',$mail_data)->render('email-templates/forgot-email-template');

            $mailConfig = array(
                'mail_from_email'=>env('EMAIL_FROM_ADDRESS'),
                'mail_from_name'=>env('EMAIL_FROM_NAME'),
                'mail_recipient_email'=>$user_info->email,
                'mail_recipient_name'=>$user_info->name,
                'mail_subject'=>'Reset Password',
                'mail_body'=>$mail_body,
            );

            //Enviando email
            if (sendEmail($mailConfig)) {
                return redirect()->route('admin.forgot.form')->with('success','Se ha restablecido su contraseña');
            } else {
                return redirect()->route('admin.forgot.form')->with('fail','No se apododo restablecer su contraseña');
            }
        }
    }
    public function resetPassword($token)
    {
        $passwordResetPassword = new PasswordResetToken();
        $check_token = $passwordResetPassword->asObject()->where('token',$token)->first();

        if ($check_token) {
            return redirect()->route('admin.forgot.form')->with('fail','Invalid token. Request another reset password link');
        } else {
            $diffMins = Carbon::createFromFormat('Y-m-d H:i:s',$check_token->create_at)->diffInMinutes(Carbon::now());

            if ($diffMins > 15) {
                return redirect()->route('admin.forgot.form')->with('fail','Token expired. Request another reset password link.');
            } else {
                return view('backend/pages/auth/reset',[
                    'pageTitle'=>'Resetear contraseña',
                    'validation'=>null,
                    'token'=>$token,
                ]);
            }
            
        }
    }
    public function resetPasswordHandler($token)
    {
        $isValid = $this->validation([
            'new_password'=>[
                'rules'=>'required|min_length[5]|max_length[20]|is_password_strong[new_password]',
                'errors'=>[
                    'required'=>'Enter new password',
                    'min_length'=>'New password must have atleast mininum of 5 characters.',
                    'max_length'=>'New password must have atleast maximum of 20 characters.',
                    'is_password_strong'=>'New password must contains atleast 1 uppercase, 1 lowercase,1 number and 1 special character.',
                ]
            ],
            'confirm_new_password'=>[
                'rules'=>'required|matches[new_password]',
                'errors'=>[
                    'required'=>'Confirm new password',
                    'matches'=>'Password not matches',
                ]
            ],
        ]);
        if (!$isValid) {
            return view('backend/pages/auth/reset',[
                'pageTitle'=>'Reset password',
                'validation'=>null,
                'token'=>$token,
            ]);
        } else {
            //Detalles del token
            $passwordResetPassword = new PasswordResetToken();
            $get_token = $passwordResetPassword->asObject()->where('token',$token)->first();

            //Detalles del usuario (admin)
            if (!$get_token) {
                return redirect()->back()->with('fail','Invalid token')->withInput();
            }else {
                //Actualizando la contraseña de  admin en DB
                $user->where('email',$user_info->email)->set(['password'=>Hash::make($this->request->getVar('new_password'))])->update();

                //Enviadon una notificacion al usuario (admin) por email
                $mail_data = array(
                    'user'=>$user_info,
                    'new_password'=>$this->request->getVar('new_password')
                );

                $view = \Config\Services::renderer();
                $mail_body = $this->setVar('mail_data',$mail_data)->render('email-templates/password-changed-email-template');

                $mailConfig = array(
                    'mail_from_email'=>env('EMAIL_FROM_ADDRESS'),
                    'mail_from_name'=>env('EMAIL_FROM_NAME'),
                    'mail_recipient_email'=>$user_info->email,
                    'mail_recipient_name'=>$user_info->name,
                    'mail_subject'=>'Password Changed',
                    'mail_body'=>$mail_body
                );
                if (sendEmail($mailConfig)) {
                    //Eliminar token
                    $passwordResetPassword->where('email',$user_info->email)->delete();

                    //Redireccionando el mensaje a la pagina del login
                    return redirect()->route('admin.login.form')->with('success','Dane!, your password has been change. Use new password to login into system.');

                }else {
                    return redirect()->back()->with('fail','Something went wrong')->withInput();
                }
            }
        }
    }
}
