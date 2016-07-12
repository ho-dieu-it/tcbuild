/**
 * Created by Jon on 7/6/2016.
 */
function BaseTable(container) {
    this.$container = $(container);

    this.SELECTOR = {
        ITEM_ACTIONS: ".js_item-actions",
        DELETE_ROW: ".js_delete-row",
        MODAL_CLOSE: ".js_modal_close",
        MODAL_DELETE: ".js_modal_delete",
        MODAL: ".js_modal",
        SELECT_ALL: ".js_check_all",
        SELECT_ROW: ".js_check_row",
        DELETE_ALL: ".js_delete_all",
    };

    this.HTML = {
        MODAL_DELETE: "<div class='modal js_modal'>" +
        "   <div class='modal-dialog'>" +
        "       <div class='modal-content'>" +
        "           <div class='modal-header'>" +
        "               <button type='button' class='close js_modal_close' data-dismiss='modal' aria-label='Close'>" +
        "               <span aria-hidden='true'>×</span></button>" +
        "               <h4 class='modal-title'>Thông báo</h4>" +
        "           </div>" +
        "           <div class='modal-body'>" +
        "               <p>Bạn có muốn xóa không ?</p>" +
        "           </div>" +
        "           <div class='modal-footer'>" +
        "               <button type='button' class='btn btn-default js_modal_close' data-dismiss='modal'>Không</button>" +
        "               <button type='button'' class='btn btn-primary js_modal_delete'>Xóa</button>" +
        "           </div>" +
        "       </div>" +
        "   </div>" +
        "</div>"
    }
}

BaseTable.prototype.render = function () {
    this.bindEvents();
}

BaseTable.prototype.bindEvents = function () {
    this.$container.on("click", this.SELECTOR.DELETE_ROW, this.deleteRowConfirm.bind(this));
    this.$container.on("click", this.SELECTOR.SELECT_ALL, this.selectAllRow.bind(this));
    this.$container.closest("section").on("click", this.SELECTOR.DELETE_ALL, this.deleteAllConfirm.bind(this));
}
BaseTable.prototype.deleteRowConfirm = function (e) {
    e.preventDefault();
    var $target = $(e.currentTarget);

    this.renderModal(this.handleDeleteOnModal.bind(this, $target));
}

BaseTable.prototype.deleteAllConfirm = function (e) {
    e.preventDefault();
    this.renderModal(this.deleteRows.bind(this));
}

BaseTable.prototype.deleteRow = function (ids, $checked_rows) {
    // var url = window.location.href.match(/admin(.*)/gi) + "/delete";

    var banner_ids = [];
    if (typeof ids !== "object") {
        banner_ids.push(ids);
    } else {
        banner_ids = ids;
    }
    var url = window.location.href + "/delete";
    $.ajax({
        type: "POST",
        url: url,
        data: {ids: banner_ids},
        dataType: "json"
    }).done(function (response) {
        if (response.success === true) {
            if ($checked_rows.length > 0) {
                for (var i = 0; i < $checked_rows.length; i++) {
                    var $target = $checked_rows[i];
                    $target.closest("tr").remove();
                }
            } else {
                $checked_rows.closest("tr").remove();
            }


        }
    }).fail(function (error) {
        console.log(error.status);
    });
}

BaseTable.prototype.deleteRows = function () {
    var $checked_rows = this.$container.find("tbody tr input:checked");
    var ids = [];
    for (var i = 0; i < $checked_rows.length; i++) {
        var $target = $checked_rows[i];
        ids.push($target.value);
    }
    this.deleteRow(ids, $checked_rows);
    this.closeModal();
}

BaseTable.prototype.renderModal = function (event) {
    this.$container.append($(this.HTML.MODAL_DELETE));
    this.$container.find(this.SELECTOR.MODAL).css('display', 'block');

    this.$container.find(this.SELECTOR.MODAL_CLOSE).on("click", this.closeModal.bind(this));
    this.$container.find(this.SELECTOR.MODAL_DELETE).on("click", event);


}

BaseTable.prototype.handleDeleteOnModal = function ($target) {
    this.closeModal();
    this.deleteRow($target.data("id"), $target);
}

BaseTable.prototype.closeModal = function () {
    this.$container.find(this.SELECTOR.MODAL).remove();
}

//Extend
BaseTable.prototype.selectAllRow = function (e) {
    var $target = $(e.currentTarget);
    var $check_on_rows = $target.closest("table").find(this.SELECTOR.SELECT_ROW);

    if ($target.prop("checked") === true) {
        $check_on_rows.prop("checked", true);
    } else {
        $check_on_rows.prop("checked", false);
    }

}

var table = new BaseTable(".col-xs-12");
table.render();


