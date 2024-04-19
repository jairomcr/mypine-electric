<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\CIAuth;
use App\Models\User;
use App\Libraries\Hash;
use App\Models\Settings;
use App\Models\SocialMedia;
use App\Models\Category;
use SSP;
use \Mberecall\CI_Slugify\SlugService;
use App\Models\SubCategory;
use App\Models\Post;

class AdminController extends BaseController
{
    protected $helpers = ['url','form','CIMail','CIFunctions'];
    protected $db;

    public function __construct()
    {
        require_once APPPATH.'ThirdParty/ssp.php';
        $this->db = db_connect();
    }
    
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
                    return json_encode(['status'=>1,'token'=>csrf_hash(),'msg'=>'Done!']);
                }else {
                    return json_encode(['status'=>0,'token'=>csrf_hash(),'msg'=>'Something went wrong.']);
                }
            }
        }
    }
    public function categories()
    {
        $data= array(
            'pageTitle'=>'Categories',
        );
        return view('backend/pages/categories',$data);
    }
    public function addCategory()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();

            $this->validate([
                'category_name'=> [
                    'rules'=>'required|is_unique[categories.name]',
                    'errors'=>[
                        'required'=>'Introduce una categoria',
                        'is_unique'=>'Ya esta categoria existe',
                    ],
                ],
            ]);

            if ($validation->run()=== FALSE ) {
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'error'=>$errors]);
            } else {

                $category = new Category();
                $save = $category->save(['name'=>$request->getVar('category_name')]);

                if ($save) {
                    return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'New categorie has bee successfully added.']);
                } else {
                    return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'msg'=>'Something went wrong']);
                }
                
            }
            
        }
    }
    public function getCategories()
    {
        //DB Details
        $dbDetails = array(
            "host"=>$this->db->hostname,
            "user"=>$this->db->username,
            "pass"=>$this->db->password,
            "db"  =>$this->db->database
        );

        $table = "categories";
        $primaryKey = "id";
        $columns = array(
            array(
                "db"=>"id",
                "dt"=>0
            ),
            array(
                "db"=>"name",
                "dt"=>1
            ),
            array(
                "db"=>"id",
                "dt"=>2,
                "formatter"=>function($d,$row){
                    $subcategory = new SubCategory();
                    $subcategories = $subcategory->where(['parent_cat'=>$row['id']])->findAll();
                    return count($subcategories);
                }
            ),
            array(
                "db"=>"id",
                "dt"=>3,
                "formatter"=>function($d,$row){
                    return "<div class='btn-group' >
                        <button class='btn btn-sm btn-link p-0 mx-1 editCategoryBtn' data-id='".$row['id']."' >Editar</button>
                        <button class='btn btn-sm btn-link p-0 mx-1 deleteCategoryBtn' data-id='".$row['id']."' >Delete</button>
                    </div>";
                }
            ),
            array(
                "db"=>"ordering",
                "dt"=>4,
            ),
        );
        return json_encode(
            SSP::simple($_GET,$dbDetails,$table,$primaryKey,$columns)
        );
    }
    public function getCategory()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $id = $request->getVar('category_id');
            $category = new Category();
            $category_data = $category->find($id);
            return $this->response->setJSON(['data'=>$category_data]);
        }
    }
    public function updateCategory()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $id = $request->getVar('category_id');
            $validation = \Config\Services::validation();
                  

            $this->validate([
                'category_name'=> [
                    'rules'=>'required|is_unique[categories.name,id,'.$id.']',
                    'errors'=>[
                        'required'=>'Introduce una categoria',
                        'is_unique'=>'Ya existe esta categoria',
                    ],
                ],
            ]);

            if ($validation->run()=== FALSE ) {
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'error'=>$errors]);
            } else {
                $category = new Category();
                $update = $category->where('id',$id)->set(['name'=>$request->getVar('category_name')])->update();

                if($update)
                {
                    return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'New categorie has bee successfully added.']);
                } else {
                    return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'msg'=>'Something went wrong']);
                }
            }
        }

    }
    public function deleteCategory()
    {
        $request = \Config\Services::request();
        
        if ($request->isAJAX()) {
            $id = $request->getVar('category_id');
            $category = new Category();

            $delete = $category->delete($id);

            if ($delete) {
                return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'Category has bee successfully deleted.']);
            } else {
                return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'msg'=>'Something went wrong']);
            }
            
        }
    }

    public function reorderCategories()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $positions = $request->getVar('positions');
            $category = new Category();

            foreach ($positions as $position) {
                $index = $position[0];
                $newPosition = $position[1];
                $category->where('id',$index)->set(['ordering'=>$newPosition])->update();
            }

            return $this->response->setJSON(['status'=>1,'msg'=>'Categories ordering has been successfully updated.']);
        }

    }

    public function getParentCategories()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {

            $id = $request->getVar('parent_category_id');
            $options = '<option value="0">Uncategorized</option>';
            $category = new Category();
            $parent_categories = $category->findAll();

            if (count($parent_categories)) {
                $added_options = '';

                foreach ($parent_categories as $parent_category) {
                    $isSelected = $parent_category['id'] == $id ? 'selected' : '';
                    $added_options.='<option value="'.$parent_category['id'].'" '.$isSelected.' >'.$parent_category['name'].'</option>';
                }
                $options = $options.$added_options;
                return $this->response->setJSON(['status'=>1,'data'=>$options]);

            } else {
                return $this->response->setJSON(['status'=>1,'data'=>$options]);
            }
            
        }
    }

    public function addSubCategory()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();
                  

            $this->validate([
                'subcategory_name'=> [
                    'rules'=>'required|is_unique[sub_categories.name]',
                    'errors'=>[
                        'required'=>'Introduce una subcategoria',
                        'is_unique'=>'Ya existe esta subcategoria',
                    ],
                ],
            ]);

            if ($validation->run()=== FALSE ) {
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'error'=>$errors]);
            } else {
                $subcategory = new SubCategory();
                $subcategory_name = $request->getVar('subcategory_name');
                $subcategory_description = $request->getVar('description');
                $subcategory_parent_category = $request->getVar('parent_cat');
                $subcategory_slug = SlugService::model(SubCategory::class)->make($subcategory_name);

                $save = $subcategory->save([
                    'name'=>$subcategory_name,
                    'parent_cat'=>$subcategory_parent_category,
                    'slug'=>$subcategory_slug,
                    'description'=>$subcategory_description,
                ]);

                if ($save) {
                    return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'New sub categorie has bee successfully added.']);
                } else {
                    return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'msg'=>'Something went wrong']);
                }
                
            }
        }

    }
    public function getSubCategories()
    {
        $category = new Category();
        $subcategory=new SubCategory();

        //DB Details
        $dbDetails = array(
            "host"=>$this->db->hostname,
            "user"=>$this->db->username,
            "pass"=>$this->db->password,
            "db"  =>$this->db->database
        );
        $table = "sub_categories";
        $primaryKey = "id";
        $columns = array(
            array(
                "db"=>"id",
                "dt"=>0
            ),
            array(
                "db"=>"name",
                "dt"=>1
            ),
            array(
                "db"=>"id",
                "dt"=>2,
                "formatter"=>function($d,$row) use ($category,$subcategory){
                    $parent_cat_id = $subcategory->asObject()->where("id",$row['id'])->first()->parent_cat;
                    $parent_cat_name = ' - ';
                    if ($parent_cat_id != 0) {
                        $parent_cat_name = $category->asObject()->where('id',$parent_cat_id)->first()->name;
                    }
                    return $parent_cat_name;
                }
            ),
            array(
                "db"=>"id",
                "dt"=>3,
                "formatter"=>function($d,$row){
                    return "(x) will be added later";
                }
            ),
            array(
                "db"=>"id",
                "dt"=>4,
                "formatter"=>function($d,$row){
                    return "<div class='btn btn-group'>
                      <button class='btn btn-sm btn-link p-0 mx-1 editSubCategoryBtn' data-id='".$row['id']."' >Editar</button>
                      <button class='btn btn-sm btn-link p-0 mx-1 deleteSubCategoryBtn' data-id='".$row['id']."' >Delete</button>
                    </div>";
                }
            ),
            array(
                "db"=>"ordering",
                "dt"=>5,
            ),
        );
        return json_encode(
            SSP::simple($_GET,$dbDetails,$table,$primaryKey,$columns)
        ); 
    }

    public function getSubCategory()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $id = $request->getVar('subcategory_id');
            $subcategory = new SubCategory();
            $subcategory_data=$subcategory->find($id);
            return $this->response->setJSON(['data'=>$subcategory_data]);
        }
    }
    public function updateSubCategory()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $id = $request->getVar('subcategory_id');
            $validation = \Config\Services::validation(); 

            $this->validate([
                'subcategory_name'=> [
                    'rules'=>'required|is_unique[sub_categories.name]',
                    'errors'=>[
                        'required'=>'Introduce una subcategoria',
                        'is_unique'=>'Ya existe esta subcategoria',
                    ],
                ],
            ]);

            if ($validation->run()=== FALSE) {
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'error'=>$errors]);
            } else {
                $subcategory = new SubCategory();
                $data = array(
                    'name'=>$request->getVar('subcategory_name'),
                    'parent_cat'=>$request->getVar('parent_cat'),
                    'description'=>$request->getVar('description'),
                );
                $save = $subcategory->update($id,$data);
                
                if( $save )
                {
                    return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>' Sub categorie has bee successfully added.']);
                } else {
                    return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'msg'=>'Something went wrong']);
                }

            }
            

        }
    }
    public function reorderSubCategories()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $positions = $request->getVar('positions');
            $subcategory = new SubCategory();

            foreach ($positions as $position) {
                $index = $position[0];
                $newPosition = $position[1];
                $subcategory->where('id',$index)->set(['ordering'=>$newPosition])->update();
            }

            return $this->response->setJSON(['status'=>1,'msg'=>'Sub categories order has been successfully changed.']);
        }
        
    }
    public function deleteSubCategory()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $id = $request->getVar('subcategory_id');
            $subcategory = new SubCategory;

            //Delete sub category
            $delete = $subcategory->where('id',$id)->delete();

            if ($delete) {
                return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>' Sub Category has bee successfully deleted.']);
            } else {
                return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'msg'=>'Something went wrong']);
            }
            
        }

    }
    public function addPost()
    {
        $subcategory = new SubCategory();
        $data = [
            'pageTitle'=>'Agregar nuevos post',
            'categories'=>$subcategory->asObject()->findAll(),
        ];
        return view('backend/pages/new-post',$data);
    }
    public function createPost()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();

            $this->validate([
                'title'=> [
                    'rules'=>'required',
                    'errors'=>[
                        'required'=>'Post title is required',
                       // 'is_unique'=>'This post title is already exists',
                    ],
                ],
                'content'=> [
                    'rules'=>'required|min_length[20]',
                    'errors'=>[
                        'required'=>'Post content is required',
                        'min_length'=>'Post content must have atleast 20 characters',
                    ],
                ],
                'category'=> [
                    'rules'=>'required',
                    'errors'=>[
                        'required'=>'Select post category',
                    ],
                ],
                'featured_image'=> [
                    'rules'=>'uploaded[featured_image]|is_image[featured_image]|max_size[featured_image,2048]',
                    'errors'=>[
                        'uploaded'=>'Featured image is required',
                        'is_image'=>'Select an image file type',
                        'max_size'=>'Select image that not excess 2MB is size'
                    ],
                ],
            ]);

            if ($validation->run()=== FALSE ) {
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'error'=>$errors]);
            } else {

                $user_id = CIAuth::id();
                $path = 'img/posts/';
                $file = $request->getFile('featured_image');
                $filename=$file->getClientName();

                //Make post featured images folder is not exists
                if (!is_dir($path)) {
                    mkdir($path,0777,true);
                }

                //Uploade featured image
                if ($file->move($path,$filename)) {
                    //Create thumb image
                    \Config\Services::image()->withFile($path.$filename)->fit(150,150,'center')->save($path.'thumb_'.$filename);

                    //Create resized image
                    \Config\Services::image()->withFile($path.$filename)->resize(450,450,'width')->save($path.'resize_'.$filename);

                    //Save new post details
                    $post = new Post();

                    $data = array(
                        'author_id'=>$user_id,
                        'category_id'=>$request->getVar('category'),
                        'title'=>$request->getVar('title'),
                        'slug'=>SlugService::model(Post::class)->make($request->getVar('title')),
                        'content'=>$request->getVar('content'),
                        'featured_image'=>$filename,
                        'tags'=>$request->getVar('tags'),
                        'meta_keywords'=>$request->getVar('meta_keywords'),
                        'meta_description'=>$request->getVar('meta_description'),
                        'visibility'=>$request->getVar('visibility'),

                    );
                    $save = $post->insert($data);
                    $last_id = $post->getInsertID();

                    if ($save) {
                        return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'New post has bee successfully created.']);
                    } else {
                        return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'msg'=>'Something went wrong.']);
                    }
                    

                } else {
                    return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'msg'=>'Error on uploading featured image.']);
                }
                

            }
            
        }

    }
    public function allPosts()
    {
        $data = [
            'pageTitle'=>'Ver publicaciones'
        ];
        return view('backend/pages/all-posts',$data);
    }
    public function getPosts()
    {
        //DB Details
        $dbDetails = array(
            "host"=>$this->db->hostname,
            "user"=>$this->db->username,
            "pass"=>$this->db->password,
            "db"  =>$this->db->database
        );
        $table = "posts";
        $primaryKey = "id";
        $columns = array(
            array(
                "db"=>"id",
                "dt"=>0
            ),
            array(
                "db"=>"id",
                "dt"=>1,
                "formatter"=>function($d,$row){
                    $post = new Post();
                    $image = $post->asObject()->find($row['id'])->featured_image;
                    return "<img src='/img/posts/thumb_$image' class='img-thumbnail' style='max-width:70px' >";
                }
            ),
            array(
                "db"=>"title",
                "dt"=>2
            ),
            array(
                "db"=>"id",
                "dt"=>3,
                "formatter"=>function($d,$row){
                    $post = new Post();
                    $category_id = $post->asObject()->find($row['id'])->category_id;
                    $subcategory = new SubCategory();
                    $category_name =  $subcategory->asObject()->find($category_id)->name;

                    return $category_name;
                }
            ),
            array(
                "db"=>"id",
                "dt"=>4,
                "formatter"=>function($d,$row){
                    $post = new Post();
                    $visibility = $post->asObject()->find($row['id'])->visibility;

                    return $visibility == 1 ? 'Public' : 'Private' ;
                }
            ),
            array(
                "db"=>"id",
                "dt"=>5,
                "formatter"=>function($d,$row){
                   return "<div class='btn-group' >
                        <a href='' class='btn btn-sm btn-link p-0 mx-1' >Ver</a>
                        <a href='".route_to('edit-post',$row['id'])."' class='btn btn-sm btn-link p-0 mx-1' >Editar</a>
                        <button class='btn btn-sm btn-link p-0 mx-1 deletePostBtn' data-id='".$row['id']."'>Eliminar</button>
                   </div>";
                }
            ),
        );
        return json_encode(
            SSP::simple($_GET,$dbDetails,$table,$primaryKey,$columns)
        );
    }
    public function editPost($id)
    {
       $subcategory = new SubCategory();
       $post = new Post();
       $data = [
            'pageTitle'=>'Editar post',
            'categories'=>$subcategory->asObject()->findAll(),
            'post'=>$post->asObject()->find($id),
       ]; 
       return view('backend/pages/edit-post',$data);
    }
}
