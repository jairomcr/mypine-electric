<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\CIAuth;
use App\Models\User;
use App\Libraries\Hash;
use App\Models\Settings;
use App\Models\SocialMedia;

class AdminController extends BaseController
{
    protected $helpers = ['url','form','CIMail','CIFunctions'];
    
    public function index()
    {
        $data = [
            'pageTitle'=>'Adminstracion',
        ];
        return view('backend/pages/home',$data);
    }
    public function logoutHandler() 
    {
        CIAuth::forget();
        return redirect()->route('admin.login.form')->with('fail','Has salido del sistema');
    }
    public function profile()
    {
        $data = [
            'pageTitle'=>'Usuario',

        ];
        return view('backend/pages/profile',$data);
    }
    public function updatePersonalDetails()
    {
        $request = \Config\Services::request();
        $validation = \Config\Services::validation();
        $user_id = CIAuth::id();

        if ($request->isAJAX()) {
            $this->validate([
                'name'=>[
                    'rules'=>'required',
                    'errors'=>[
                        'required'=>'Introdusca el nombre'
                    ],
                ],
                'username'=>[
                    'rules'=>'required|min_length[4]|is_unique[users.username,id,'.$user_id.']',
                    'errors'=>[
                        'required'=>'Introduce el nombre de usuario',
                        'min_length'=>'Por favor. Debe de tener un minimo de 4 caracteres',
                        'is_unique'=>'Debe ser un valor unico',
                    ]
                ]
            ]);
            if ($validation->run()==FALSE) {
                $errors = $validation->getErrors();
                return json_encode(['status'=>0,'error'=>$errors]);
            } else {
                $user = new User();
                $update = $user->where('id',$user_id)->set([
                    'name'=>$request->getVar('name'),
                    'username'=>$request->getVar('username'),
                    'bio'=>$request->getVar('bio'),     
                ])->update();

                if ($update) {
                    $user_info = $user->find($user_id);
                    return json_encode(['status'=>1,'user_info'=>$user_info,'msg'=>'Your personal details have been successfully updated.']);
                }else {
                    return json_encode(['status'=>0,'msg'=>'Something went wrong.']);
                }
            }
            
        }
        
    }

    public function updateProfilePicture()
    {
        $request   = \Config\Services::request();
        $user_id   = CIAuth::id();
        $user      = new User();
        $user_info = $user->asObject()->where('id',$user_id)->first();

        $path         = 'img/';
        $file         = $request->getFile('user_profile_file');
        $old_picture  = $user_info->picture;
        $new_filename = 'UIMG_'.$user_id.$file->getRandomName();

        /*if ($file->move($path,$new_filename)) {
            if ($old_picture != null && file_exists($path.$old_picture)) {
                unlink($path.$old_picture);
            }
            $user->where('id',$user_info->id)->set(['picture'=>$new_filename])->update();

            echo json_encode(['status'=>1,'msg'=>'Done!, Your profile picture has been successfully update.']);
        } else {
            echo json_encode(['status'=>0,'msg'=>'Something went wrong.']);
        }*/

        //Manipulacion de la Imagen
        $upload_image = \Config\Services::image()->withFile($file)->resize(450,450,true,'height')->save($path.$new_filename);

        if ($upload_image) {
            if ($old_picture != null && file_exists($path.$new_filename)) {
                unlink($path.$old_picture);
            }
            $user->where('id',$user_info->id)->set(['picture'=>$new_filename])->update();

            echo json_encode(['status'=>1,'msg'=>'Done!, Your profile picture has been successfully update.']);
        } else {
            echo json_encode(['status'=>0,'msg'=>'Something went wrong.']);
        }
        
        
    }
    public function changePassword()
    {
        
        $request   = \Config\Services::request();

        if ($request->isAJAX() ) {
            
            $validation = \Config\Services::validation();
            $user_id   = CIAuth::id();
            $user      = new User();
            $user_info = $user->asObject()->where('id',$user_id)->first();

            
            $this->validate([
                'current_password'=>[
                    'rules'=>'required|min_length[5]|check_current_password[current_password]',
                    'errors'=>[
                        'required'=>'Introdusca la contraseña',
                        'min_length'=>'Debe de tener al menos 5 caracteres',
                        'check_current_password'=>'Contraseña incorrecta',
                    ],
                ],
                'new_password'=>[
                    'rules'=>'required|min_length[5]|max_length[20]|is_strong_password[new_password]',
                    'errors'=>[
                        'required'=>'Introdusca la contraseña',
                        'min_length'=>'Debe de tener al menos 5 caracteres',
                        'max_length'=>'Debe de tener maximo 20 caracteres',
                        'is_strong_password'=>'La contraseña debe contener al menos 8 caracteres,1 letra mayúscula, 1 minúscula, 1 número y 1 carácter especial.',
                    ],
                ],
                'confirm_new_password'=>[
                    'rules'=>'required|matches[new_password]',
                    'errors'=> [
                        'required'=>'Introduce la contraseña',
                        'matches'=>'No coincide la contraseña.'
                    ],
                ],
            ]);

            
            if ($validation->run()===FALSE ) {
                
                $errors = $validation->getErrors();
                // return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'error'=>$errors]);
                return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'error'=>$errors]);
            } else {
                
            //     return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'Good']);
            //     //actualizando user(admin) password in DB
                $user->where('id',$user_info->id)->set(['password'=>Hash::make($request->getVar('new_password'))])->update();

                return $this->response->setJSON(['status'=>1, 'token'=>csrf_hash(), 'msg'=>'Good']);

                

            //     //Enviadon una notificacion al usuario (admin) por email
            //     /*$mail_data = array(
            //         'user'=>$user_info,
            //         'new_password'=>$request->getVar('new_password')
            //     );

            //     $view = \Config\Services::renderer();
            //     $mail_body = $this->setVar('mail_data',$mail_data)->render('email-templates/password-changed-email-template');

            //     $mailConfig = array(
            //         'mail_from_email'=>env('EMAIL_FROM_ADDRESS'),
            //         'mail_from_name'=>env('EMAIL_FROM_NAME'),
            //         'mail_recipient_email'=>$user_info->email,
            //         'mail_recipient_name'=>$user_info->name,
            //         'mail_subject'=>'Password Changed',
            //         'mail_body'=>$mail_body
            //     );

            //     sendEmail($mailConfig);
            //     return $this->responde->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'Done! Your password has been successfully updated']);*/

            }
            
        }
    }
    public function settings()
    {
        $data = [
            'pageTitle'=>'Settings',

        ];
        return view('backend/pages/settings',$data);
    }
    public function updateGeneralSettings()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {

            $validation = \Config\Services::validation();

            $this->validate([
                'blog_title'=>[
                   'rules'=>'required',
                   'errors'=>[
                     'required'=>'Introduce el titulo',
                    ],
                ],
                'blog_email'=>[
                    'rules'=>'required|valid_email',
                    'errors'=>[
                      'required'=>'Introduce el titulo',
                      'valid_email'=>'Introduce un correo valido. Eje: info@gmail.com',
                    ],
                ],
            ]);
            if ($validation->run()=== FALSE ) {
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'error'=>$errors]);
            } else {
                $settings = new Settings();
                $settings_id = $settings->asObject()->first()->id;
                $update = $settings->where('id',$settings_id)->set([
                    'blog_title'=>$request->getVar('blog_title'),
                    'blog_email'=>$request->getVar('blog_email'),
                    'blog_phone'=>$request->getVar('blog_phone'),
                    'blog_meta_keywords'=>$request->getVar('blog_meta_keywords'),
                    'blog_meta_description'=>$request->getVar('blog_meta_description'),
                ])->update();

                if ($update) {
                    return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'SuccessFully']);
                    
                } else {
                    return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'msg'=>'Something went wrong']);
                }
                
            }
            
        }
        
    }
    public function updateBlogLogo()
    {
        $request = \Config\Services::request();
        
        if ($request->isAJAX()) {
            $settings = new Settings;
            $path = 'img/logo/';

            $file = $request->getFile('blog_logo');
            $settings_data = $settings->asObject()->first();
            $old_blog_logo = $settings_data->blog_logo;
            $new_filename = 'Servicios_electricos'.$file->getRandomName();

            if ($file->move($path, $new_filename)) {
                if ($old_blog_logo != null && file_exists($path.$old_blog_logo) ) {
                    unlink($path.$old_blog_logo);
                }
                $update = $settings->where('id',$settings_data->id)->set(['blog_logo'=>$new_filename])->update();
                
                if ($update) {
                    return $this->response->setJSON(['status'=>1, 'token'=>csrf_hash(), 'msg'=>'Done!']);
                } else {
                    return $this->response->setJSON(['status'=>0, 'token'=>csrf_hash(), 'msg'=>'Something went wrong on updating new logo info.']);
                }
            } else {
                return $this->response->setJSON(['status'=>0, 'token'=>csrf_hash(), 'msg'=>'Something went wrong on uploading new logo.']);
            }
        }
    }
    public function updateBlogFavicon()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $settings = new Settings();
            $path = 'img/blog/';
            $file = $request->getFile('blog_favicon');
            $settings_data = $settings->asObject()->first();
            $old_blog_favicon = $settings_data->blog_favicon;
            $new_filename = 'Favicon_'.$file->getRandomName();

            if ($file->move($path, $new_filename) ) {
                if ($old_blog_favicon != null && file_exists($path.$old_blog_favicon) ) {
                    unlink($path.$old_blog_favicon);
                }
                $update = $settings->where('id',$settings_data->id)->set(['blog_favicon'=>$new_filename])->update();

                if ($update) {
                    return $this->response->setJSON(['status'=>1, 'token'=>csrf_hash(), 'msg'=>'Done!']);
                } else {
                    return $this->response->setJSON(['status'=>0, 'token'=>csrf_hash(), 'msg'=>'Something went wrong on updating new blog favicon.']);
                }

            } else {
                return $this->response->setJSON(['status'=>0, 'token'=>csrf_hash(), 'msg'=>'Something went wrong on uploading new  blog favicon file.']);
            }
            

        }
    }
    public function updateSocialMedia()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();
            $this->validate([
                'facebook_url'=>[
                    'rules'=>'permit_empty|valid_url_strict',
                    'errors'=>[
                        'valid_url_strict'=>'Invalid facebook page URL'
                    ],
                ],
                'twitter_url'=>[
                    'rules'=>'permit_empty|valid_url_strict',
                    'errors'=>[
                        'valid_url_strict'=>'Invalid twitter page URL'
                    ],
                ],
                'instagram_url'=>[
                    'rules'=>'permit_empty|valid_url_strict',
                    'errors'=>[
                        'valid_url_strict'=>'Invalid instagram page URL'
                    ],
                ],
                'youtube_url'=>[
                    'rules'=>'permit_empty|valid_url_strict',
                    'errors'=>[
                        'valid_url_strict'=>'Invalid youtube page URL'
                    ],
                ],
                'whatsapp_url'=>[
                    'rules'=>'permit_empty|valid_url_strict',
                    'errors'=>[
                        'valid_url_strict'=>'Invalid whatsapp page URL'
                    ],
                ],
                'linkedin_url'=>[
                    'rules'=>'permit_empty|valid_url_strict',
                    'errors'=>[
                        'valid_url_strict'=>'Invalid linkedin page URL'
                    ],
                ],
            ]);
            if ($validation->run() === FALSE) {
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status'=>0, 'token'=>csrf_hash(), 'error'=>$errors]);
            } else {
                $social_media = new SocialMedia();
                $social_media_id=$social_media->asObject()->first()->id;
                $update = $social_media->where('id',$social_media_id)->set([
                    'facebook_url'=>$request->getVar('facebook_url'),
                    'twitter_url'=>$request->getVar('twitter_url'),
                    'instagram_url'=>$request->getVar('instagram_url'),
                    'youtube_url'=>$request->getVar('youtube_url'),
                    'whatsapp_url'=>$request->getVar('whatsapp_url'),
                    'linkedin_url'=>$request->getVar('linkedin_url'),     
                ])->update();

                if ($update) {
                    $user_info = $user->find($user_id);
                    return json_encode(['status'=>1,'user_info'=>$user_info,'msg'=>'Done!']);
                }else {
                    return json_encode(['status'=>0,'msg'=>'Something went wrong.']);
                }
            }
        }
    }
}