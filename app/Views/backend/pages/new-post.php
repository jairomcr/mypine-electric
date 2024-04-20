<?= $this->extend('backend/layout/pages-layout') ?>
<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Agregar post</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home') ?>">Inicio</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Agregar post
                    </li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <a href="<?= route_to('all-posts') ?>" class="btn btn-primary">Ver publicaciones</a>
        </div>
    </div>
</div>
<form action="<?= route_to('create-post') ?>" method="post" autocomplete="off" enctype="multipart/form-data"
    id="addPostForm">
    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
    <div class="row">
        <div class="col-md-9">
            <div class="card card-box mb-2">
                <div class="card-body">
                    <div class="form-group">
                        <label for=""><b>Titulo publicaciones </b></label>
                        <input type="text" class="form-control" name="title"
                            placeholder="Introduce el titulo de la publicación">
                        <span class="text-danger error-text title_error"></span>
                    </div>
                    <div class="form-group">
                        <label for=""><b>Contenido</b></label>
                        <textarea name="content" cols="30" rows="10" class="form-control" id="content"
                            placeholder="Type........."></textarea>
                        <span class="text-danger error-text content_error"></span>
                    </div>
                </div>
            </div>
            <div class="card card-box mb-2">
                <h5 class="card-header weight-500">SEO</h5>
                <div class="card-body">
                    <div class="form-group">
                        <label for=""><b>Publicar meta palabras clave</b><small>(Separado por coma)</small></label>
                        <input type="text" class="form-control" placeholder="Introduce la pubicion meta palabras clave."
                            name="meta_keywords">
                    </div>
                    <div class="form-group">
                        <label for=""><b>Descripción de la pubicación</b></label>
                        <textarea name="meta_description" cols="30" rows="10" class="form-control"
                            placeholder="Descripción......."></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-box mb-2">
                <div class="card-body">
                    <div class="form-group">
                        <label for=""><b>Post category</b></label>
                        <select name="category" class="custom-select form-control">
                            <option value="">Choose....</option>
                            <?php foreach($categories as $category): ?>
                            <option value="<?= $category->id ?>"><?= $category->name ?></option>
                            <?php endforeach ?>
                        </select>
                        <span class="text-danger error-text category_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="">Publicar imagen destacada</label>
                        <input type="file" name="featured_image" class="form-control-file form-control" height="auto"
                            id="file">
                        <span class="text-danger error-text featured_image_error"></span>
                    </div>
                    <div class="d-block mb-3" style="max-width: 250px;">
                        <img src="" alt="" class="img-thumbnail" id="image-previewer" data-ijabo-default-img="">
                    </div>
                    <div class="form-group">
                        <label for=""><b>Tags</b></label>
                        <input type="text" name="tags" class="form-control" placeholder="Inroduce el tags"
                            data-role="tagsinput">
                        <span class="text-danger error-text tags_error"></span>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for=""><b>Visibility</b></label>
                        <div class="custom-control custom-radio mb-5">
                            <input type="radio" name="visibility" id="customRadio1" class="custom-control-input"
                                value="1" checked>
                            <label for="customRadio1" class="custom-control-label">Public</label>
                        </div>
                        <div class="custom-control custom-radio mb-5">
                            <input type="radio" name="visibility" id="customRadio2" class="custom-control-input"
                                value="0">
                            <label for="customRadio2" class="custom-control-label">Private</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <button type="submit" class="btn btn-primary">Crear publicación</button>
    </div>
</form>
<?= $this->endSection() ?>
<?= $this->section('stylesheets') ?>
<link rel="stylesheet" href="/backend/src/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css">
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="/backend/src/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js"></script>
<script src="/backend/src/plugins/sweetalert2/sweet-alert.init.js"></script>
<script src="/backend/src/plugins/sweetalert2/sweetalert2.all.js"></script>
<script src="/extra-assets/ckeditor/ckeditor.js"></script>
<script>
$(function() {
    CKEDITOR.replace('content');
});
document.getElementById('change',cambiarImagen);
function cambiarImagen(e){
    var file = e.target.files[0];
    var reader = new FileReader();

    reader.onload = (e) => {
        document.getElementById('image-previewer').setAttribute('src',e.target.result);
    }
    reader.readAsDataUrl(file);
}
$('#addPostForm').on('submit', function(e) {
    e.preventDefault();

    var csrfName = $('.ci_csrf_data').attr('name');
    var csrfHash = $('.ci_csrf_data').val();
    var form = this;
    var content = CKEDITOR.instances.content.getData();
    var formdata = new FormData(form);
    formdata.append(csrfName, csrfHash);
    formdata.append('content', content);

    $.ajax({

        url: $(form).attr('action'),
        method: $(form).attr('method'),
        data: formdata,
        processData: false,
        dataType: 'json',
        contentType: false,
        cache: false,
        beforeSend: function() {
            //toastr.remove();
            $(form).find('span.error-text').text('');
        },
        success: function(response) {

            $('.ci_csrf_data').val(response.token);

            if ($.isEmptyObject(response.error)) {
                if (response.status == 1) {
                    $(form)[0].reset();
                    CKEDITOR.instances.content.setData('');

                    $('img#image-previewer').attr('src', '');
                    $('input[name="tags"]').tagsinput('removeAll');

                    swal({
                        text: 'Se ha creado una publicación .',
                        type: 'success',
                        showConfirmButton: false,
                        timer: 1500,

                    });
                    //toastr.success(response.msg);
                } else {
                    //toastr.error(response.msg);
                    console.log("ERROR");
                }
            } else {
                $.each(response.error, function(prefix, val) {
                    $(form).find('span.' + prefix + '_error').text(val);
                });
            }
        }

    });

});
</script>
<?= $this->endSection() ?>