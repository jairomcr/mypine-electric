<div class="modal fade" id="category-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true" data-backdrop="static" >
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" action="<?= route_to('add-category') ?>" method="post" id="add_category_form" >
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">
                    Large modal
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    ×
                </button>
            </div>
            <div class="modal-body">
               <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" >
               <div class="form-group" >
                    <label for=""><b>Nombre categoria</b></label>
                    <input type="text" class="form-control" name="category_name" placeholder="Introduce el nombre de la categoria" >
                    <span class="text-danger error-text category_name_error" ></span>
               </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Close
                </button>
                <button type="submit" class="btn btn-primary action">
                    Save changes
                </button>
            </div>
        </form>
    </div>
</div>