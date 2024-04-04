<?php

use App\Libraries\CIAuth;
use App\Models\User;
use App\Models\Settings;

if (!function_exists('get_user')) {
    function get_user(){
        if (CIAuth::check()) {
            $user = new User();
            return $user->asObject()->where('id',CIAuth::id())->first();
        } else {
            return null;
        }
        
    }
}
if (!function_exists('get_settings')) {
    function get_settings(){
            $settings = new Settings();
            $settings_data = $settings->asObject()->first();

            if (!$settings_data) {
                $data = array(
                    'blog_title'=>'Mypine-Electrica',
                    'blog_email'=>'jairocascaretrodriguez@gmail.com',
                    'blog_phone'=>null,
                    'blog_meta_description'=>null,
                    'blog_meta_keywords'=>null,
                    'blog_logo'=>null,
                    'blog_favicon'=>null,
                );
                $settings->save($data);
                $new_settings_data = $settings->asObject()->first();
                return $new_settings_data;
            } else {
                return $settings_data;
            }
  
    }
}