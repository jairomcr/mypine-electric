<?= $this->extend('backend/layout/pages-layout') ?>
<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="title">
                <h4>Profile</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home'); ?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Profile
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 mb-30">
        <div class="pd-20 card-box height-100-p">
            <div class="profile-photo">
                <a href="javascript:;" 
                onclick="event.preventDefault(); document.getElementById('user_profile_file').click();" class="edit-avatar">
                  <i class="fa fa-pencil"></i>
                </a>

                <input type="file" name="user_profile_file" id="user_profile_file" class="d-none" style="opacity: 0;" >

                <img src="<?= get_user()->picture == null ? '/img/user.jpg' : '/img/'.get_user()->picture ?>" alt="" class="avatar-photo ci-avatar-photo">
            </div>
            <h5 class="text-center h5 mb-0 ci-user-name"><?= get_user()->name ?></h5>
            <p class="text-center text-muted font-14 ci-user-email">
                <?= get_user()->email ?>
            </p>
        </div>
    </div>
    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-12 mb-30">
        <div class="card-box height-100-p overflow-hidden">
            <div class="profile-tab height-100-p">
                <div class="tab height-100-p">
                    <ul class="nav nav-tabs customtab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#details_personal" role="tab">Detalles personales</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#change_password" role="tab">Change password</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <!-- Timeline Tab start -->
                        <div class="tab-pane fade show active" id="details_personal" role="tabpanel">
                            <div class="pd-20">
                                <form action="<?= route_to('update-personal-details'); ?>" method="POST" id="personal_details_from">
                                    <?= csrf_field(); ?>
                                    <div class="row" >
                                        <div class="col-md-6">
                                            <div class="form-group" >
                                                <label for="">Nombre</label>
                                                <input type="text" name="name" id="" class="form-control" placeholder="Nombre...." value="<?= get_user()->name ?>">
                                                <span class="text-danger error-text name_error" ></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group" >
                                                <label for="">Nombre de Usuario</label>
                                                <input type="text" name="username" id="" class="form-control" placeholder="Nombre de Usuario.." value="<?= get_user()->username ?>">
                                                <span class="text-danger error-text username_error" ></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Bio</label>
                                        <textarea name="bio" id="" cols="30" rows="10" class="form-control" placeholder="Bio....." value="<?= get_user()->bio ?>"></textarea>
                                        <span class="text-danger error-text bio_error" ></span>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- Timeline Tab End -->
                        <!-- Tasks Tab start -->
                        <div class="tab-pane fade" id="change_password" role="tabpanel">
                            <div class="pd-20 profile-task-wrap">
                                <form action="<?= route_to('change-password') ?>" method="POST" id="change_password_form">
                                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data" />
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="">Contraseña</label>
                                                <input type="password" name="current_password" class="form-control" placeholder="Introduce la contraseña." />
                                                <span class="text-danger error-text current_password_error" ></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="">Nueva contraseña</label>
                                                <input type="password" name="new_password" class="form-control" placeholder="Nueva contraseña." />
                                                <span class="text-danger error-text new_password_error" ></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="">Confirmar contraseña</label>
                                                <input type="password" name="confirm_new_password" class="form-control" placeholder="Confirmar contraseña." />
                                                <span class="text-danger error-text confirm_new_password_error" ></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary" >Guardar</button>
                                    </div>  
                                </form>
                            </div>
                        </div>
                        <!-- Tasks Tab End -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scprits') ?>
   <script>
        $('#personal_details_from').on('submit',function(e){
            e.preventDefault();
            //alert('sumbit');
            var form = this;
            var formdata = new FormData(form);

            $.ajax({
                url:$(form).attr('action'),
                method:$(form).attr('method'),
                data:formdata,
                processData:false,
                dataType:'json',
                contentType:false,
                beforeSend:function(){
                    //toastr.remove();
                    $(form).find('span.error-text').text('');
                },
                success:function(response){
                    if ($.isEmptyObject(response.error)) {
                        if (response.status == 1) {
                            $('.ci-user-name').each(function(){
                                $(this).html(response.user_info.name);
                            });
                            //toastr.success(response.msg);
                            console.log("OK");
                        } else {
                            //toastr.error(response.msg);
                            console.log("ERROR");
                        }
                    } else {
                        $.each(response.error,function(prefix, val){
                            $(form).find('span.'+prefix+'_error').text(val);
                        });
                    }
                }
            });
        });
      $('#user_profile_file').ijaboCropTool({
          preview : '.ci-avatar-photo',
          setRatio:1,
          allowedExtensions: ['jpg', 'jpeg','png'],
          processUrl:'<?= route_to('update-profile-picture')?>',
          withCSRF:['<?= csrf_token() ?>','<?= csrf_hash() ?>'],
          onSuccess:function(message, element, status){
            //alert(message);
            if (status == 1) {
                toastr.success(message);
            } else {
                toastr.error(message);
            }
          },
          onError:function(message, element, status){
            alert(message);
          }
      });   
      $('#change_password_form').on('submit',function(e){
            e.preventDefault();
            //alert('submit');
            var csrfName = $('.ci_csrf_data').attr('name');
            var csrfHash = $('.ci_csrf_data').val();
            var form     = this;
            var formdata = new FormData(form);
                formdata.append(csrfName,csrfHash);
            
            $.ajax({
                url:$(form).attr('action'),
                method:$(form).attr('method'),
                data:formdata,
                processData:false,
                dataType:'json',
                contentType:false,
                cache:false,
                beforeSend:function(){
                    //toastr.remove();
                    $(form).find('span.error-text').text('');
                },
                success:function(response){
                    $('.ci_csrf_data').val(response.token);

                    if ($.isEmptyObject(response.error)) {
                        if (response.status == 1) {
                            $(form)[0].reset();
                            //toastr.success(response.msg);
                        } else {
                            //toastr.error(response.msg);
                        }
                    } else {
                        $.each(response.error,function(prefix, val){
                            $(form).find('span.'+prefix+'_error').text(val);
                        });
                    }
                }
            });
      });
  </script>
<?= $this->endSection() ?>