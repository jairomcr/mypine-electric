<div class="modal fade" id="edit-sub-category-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true" data-backdrop="static" >
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" action="<?= route_to('update-subcategory') ?>" method="post" id="update_subcategory_form" >
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
               <input type="hidden" name="subcategory_id">
               <div class="form-group">
                    <label for=""><b>Categoría principal</b></label>
                    <select name="parent_cat"  class="form-control">
                        <option value="">Uncategorized</option>
                    </select>
               </div>
               <div class="form-group" >
                    <label for=""><b>Sub Nombre categoria</b></label>
                    <input type="text" class="form-control" name="subcategory_name" placeholder="Introduce el nombre de la subcategoria" >
                    <span class="text-danger error-text subcategory_name_error" ></span>
               </div>
               <div class="form-group">
                    <label for=""><b>Descripción</b></label>
                    <textarea name="description"  cols="30" rows="10" placeholder="Descripción........" class="form-control" ></textarea>
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