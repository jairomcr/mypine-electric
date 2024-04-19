<?= $this->extend('backend/layout/pages-layout') ?>
<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Ver post</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home') ?>">Inicio</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Ver post
                    </li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <a href="<?= route_to('new-post') ?>" class="btn btn-primary">Agregar una publicación</a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card card-box">
            <div class="card-header">
                <div class="clearfix">
                    <div class="pull-left">Ver posts</div>
                    <div class="pull-right"></div>
                </div>
            </div>
            <div class="card-body">
                <table class="data-table table stripe hover nowrap dataTable no-footer dtr-inline collapsed" id="posts-table" >
                    <thead>
                        <tr>
                            <th scope="col" >#</th>
                            <th scope="col" >Featured image</th>
                            <th scope="col" >Titulo</th>
                            <th scope="col" >Categoria</th>
                            <th scope="col" >Visibilidad</th>
                            <th scope="col" >Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('stylesheets') ?>
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/responsive.bootstrap4.min.css">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/backend/src/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.responsive.min.js"></script>
<script src="/backend/src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>

<script>
    //Retrieve posts
    var posts_DT = $('table#posts-table').DataTable({
        scrollCollapse:true,
        responsive:true,
        autoWidth:false,
        processing: true,
        serverSide: true,
        ajax:"<?= route_to('get-posts') ?>",
        "dom":"IBfrtip",
        info:true,
        fnCreatedRow:function(row,data,index){
            $('td',row).eq(0).html(index+1);
        },
        columDefs:[
            {orderable:false, targets:[0,1,2,3,4,5]}
        ]
    });
</script>

<?= $this->endSection() ?>
