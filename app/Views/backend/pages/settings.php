<?= $this->extend('backend/layout/pages-layout') ?>
<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="title">
                <h4>Settings</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home') ?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Setttings
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<div class="pd-20 card-box mb-4">
    
    <div class="tab">
        <ul class="nav nav-tabs customtab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#general_settings" role="tab" aria-selected="true">General settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#logo_favicon" role="tab" aria-selected="false">Logo & Favicon</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#social_media" role="tab" aria-selected="false">Socil media</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade active show" id="general_settings" role="tabpanel">
                <div class="pd-20">
                    <form action="" method="POST" id="general_settings_form" >
                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data" >
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Blog title</label>
                                    <input type="text" name="blog_title" class="form-control" placeholder="Nombre del blog" >
                                    <span class="text-danger error-text blog_title_error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Blog Email</label>
                                    <input type="text" name="blog_email" class="form-control" placeholder="El email del blog" >
                                    <span class="text-danger error-text blog_email_error"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Blog phone no</label>
                                    <input type="text" name="blog_phone" class="form-control" placeholder="Nombre del blog" >
                                    <span class="text-danger error-text blog_title_error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Blog meta keywords</label>
                                    <input type="text" name="blog_email" class="form-control" placeholder="El email del blog" >
                                    <span class="text-danger error-text blog_email_error"></span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="logo_favicon" role="tabpanel">
                <div class="pd-20">
                    --------------Logo & favicon------------
                </div>
            </div>
            <div class="tab-pane fade" id="social_media" role="tabpanel">
                <div class="pd-20">
                    ------------Social media-----------------
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>