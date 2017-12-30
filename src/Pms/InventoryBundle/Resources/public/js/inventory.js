$(".gatePassNo").keyup(function () {
    var gatePassNo = $(this).val();

    $.ajax({
        type: "post",
        url: Routing.generate('gatePass_check'),
        data: "gatePassNo=" + gatePassNo,
        dataType: 'json',
        success: function (msg) {
            if (msg.responseCode == 200) {
                $('.userMessege').css('color', 'red').html(msg.gate_pass).fadeIn(1000);
            }
            else {
                $('.userMessege').css('color', 'green').html(msg.gate_pass).fadeIn(1000);
            }
        }
    });
});


window.items;
window.category;
var issueQuantityCheck ;
var remainingQty ;

var x = document.getElementById("tableRowCount").rows.length;
var projectId = $("#pms_inventorybundle_delivery_project").val();
for (var index = 0; x > index; index++) {

    var item = $("#pms_inventorybundle_delivery_deliveryItem_" + index + "_item").val();
    findType(item, projectId ,index);
}

function findType(item, projectId ,index) {

    $.ajax({
        type: "post",
        url: Routing.generate('delivery_item'),
        //data: "item=" + item +"project_id="+projectId,
        data: { item:item,project_id:projectId},
        dataType: 'json',
        success: function (response) {
            $("#pms_inventorybundle_delivery_deliveryItem_" + index + "_itemType").val(response.itemType);
            $("#pms_inventorybundle_delivery_deliveryItem_" + index + "_remainingQuantity").val(response.remainingQuantity);
            $("#pms_inventorybundle_delivery_deliveryItem_" + index + "_unit").val(response.itemUnit);
        }
    });
}

function addTagForm($collectionHolder, $newLinkLi) {

   // if(remainingQty > issueQuantityCheck){

   //     return false;
   // }
    var prototype = $collectionHolder.data('prototype');

    var index = $collectionHolder.data('index');

    var newForm = prototype.replace(/__name__/g, index);

    $collectionHolder.data('index', index + 1);

    var $newFormLi = $('<tr></tr>').append(newForm);
    $newLinkLi.before($newFormLi);
    $('.date-picker').datepicker();
    $('#pms_inventorybundle_delivery_deliveryItem_0_remove').hide();
    $('select').select2();

    $("#pms_inventorybundle_delivery_deliveryItem_" + index + "_item").change(function () {
        var item = $(this).val();
        var project_id = $('#pms_inventorybundle_delivery_project').val();
        $.ajax({
            type: "post",
            url: Routing.generate('delivery_item'),
            data: {item: item, project_id: project_id},
            dataType: 'json',
            success: function (response) {
                console.log(response)
                var quantity = response.remainingQuantity;
                $("#pms_inventorybundle_delivery_deliveryItem_" + index + "_quantity").attr('placeholder',quantity);
                $("#pms_inventorybundle_delivery_deliveryItem_" + index + "_itemType").val(response.itemType);
                $("#pms_inventorybundle_delivery_deliveryItem_" + index + "_remainingQuantity").val(response.remainingQuantity);
                $("#pms_inventorybundle_delivery_deliveryItem_" + index + "_unit").val(response.itemUnit);
                issueQuantityCheck = quantity;

            }
        });
    });
    $("#pms_inventorybundle_delivery_deliveryItem_" + index + "_quantity").keyup(function (){
        remainingQty = $(this).val();
        if($(this).val() > Number(issueQuantityCheck)){
            alert('Issued value should  be less then  or equal to  Remaining value '+ issueQuantityCheck);
            return false;
        } else{
            return  ;
        }
    });

    $("#pms_inventorybundle_delivery_deliveryItem_0_item").change(function () {
        var item = $(this).val();
        purchaseRequisitionCategory(item);
        var $container = $('#DeliveryItems');
        $('#DeliveryItems').data('index', 1);
        $container.find('tr').not(':first').not(':last').remove();
    });

    $("#pms_inventorybundle_delivery_deliveryItem_" + index + "_remove").click(function () {
        var parent     = $(this).closest('tr');
        parent.remove();
    });
}

function purchaseRequisitionCategory(item) {
    $.ajax({
        type: "post",
        url: Routing.generate('item_wise_category'),
        data: "item=" + item,
        dataType: 'json',
        success: function (response) {
            category = response.categoryWiseItem;

            if (category.length) {
                var ic = $("#pms_inventorybundle_delivery_category");
                ic.find('option').remove();
                $.each(category, function (index, category) {
                    $('<option>').attr('value', category.id).text(category.categoryName).appendTo(ic);
                });
            }

            var categoryId = $("#pms_inventorybundle_delivery_category").val();

            purchaseRequisitionSubCategory(categoryId);
            purchaseRequisitionItem(category,projectId)
        }
    });
}

function purchaseRequisitionItem(category,projectId) {

    $.ajax({
        type: "post",
        url: Routing.generate('category_wise_item_inventory'),
        data: {category:category,project_id:projectId},
        dataType: 'json',
        success: function (response) {
            console.log(response);
            window.items = response.categoryWiseItem;
        }
    });
}

$("#pms_inventorybundle_delivery_subCategory").change(function () {
    var subCategoryId = $(this).val();
    purchaseRequisitionCostHeader(subCategoryId);
});

function purchaseRequisitionSubCategory(category) {
    $.ajax({
        type: "post",
        url: Routing.generate('category_wise_subCategory'),
        data: "category=" + category,
        dataType: 'json',
        success: function (response) {
            var subCategory = response.subCategory;

            if (subCategory.length) {
                var psc = $("#pms_inventorybundle_delivery_subCategory");
                psc.find('option').remove();
                $.each(subCategory, function (index, subCategory) {
                    $('<option>').attr('value', subCategory.id).text(subCategory.subCategoryName).appendTo(psc);
                });

                var subCategoryId = $("#pms_inventorybundle_delivery_subCategory").val();
                purchaseRequisitionCostHeader(subCategoryId);
            }

            var parent = $(this).closest('tr');
            parent.remove();
        }
    });
}

function purchaseRequisitionCostHeader(subCategory) {
    $.ajax({
        type: "post",
        url: Routing.generate('subCategory_wise_costHeader'),
        data: "subCategory=" + subCategory,
        dataType: 'json',
        success: function (response) {
            var costHeader = response.costHeader;

            if (costHeader.length) {
                var pch = $("#pms_inventorybundle_delivery_costHeader");
                pch.find('option').remove();
                $.each(costHeader, function (index, item) {
                    $('<option>').attr('value', item.id).text(item.title).appendTo(pch);
                });
            }
        }
    });
}
$("#pms_inventorybundle_delivery_issuedToProject").change(function () {

    getIssuedToUserByProject($(this).val());
});
function getIssuedToUserByProject(project) {
    $.ajax({
        type: "post",
        url: Routing.generate('load-user-from-project'),
        data: "project=" + project,
        dataType: 'json',
        success: function (response) {
            var users = response.list;

            if (users.length) {
                var projectUser = $("#pms_inventorybundle_delivery_issuedToCustomer");
                projectUser.find('option').remove();
                $('<option>').attr('value', '').text('Select User').appendTo(projectUser);
                $.each(users, function (index, item) {

                    $('<option>').attr('value', item.id).text(item.username).appendTo(projectUser);
                });
            }
        }
    });
}

var item = $('#pms_inventorybundle_delivery_deliveryItem_0_item').val();

purchaseRequisitionCategory(item);

var $collectionHolder;

var $addTagLink = $('<td colspan ="7"><div style="text-align: left;margin-left: 20px;"><a href="#" style="background: #4b8df8;color: white !important;" class="btn btn-xs add_tag_link"><i class="fa fa-plus"></i> Add Item</a></div></td>');
var $newLinkLi = $('<tr></tr>').append($addTagLink);
$collectionHolder = $('#DeliveryItems');
$collectionHolder.append($newLinkLi);
$collectionHolder.data('index', $collectionHolder.find(':input').length);


$addTagLink.on('click', function (e) {
    e.preventDefault();
    addTagForm($collectionHolder, $newLinkLi);

    var trLength = $('tbody#DeliveryItems').find('tr').length;
    if (trLength > 2) {
        var lastSelect = $('tbody#DeliveryItems').find('tr:eq(' + (trLength - 2) + ')').find('select:first');
        lastSelect.html('');
        for (var i = 0; i < window.items.length; i++) {
            console.log(window.items[i].id);
            console.log(window.items[i].itemName);
            lastSelect.append('<option value="' + window.items[i].id + '">' + window.items[i].itemName + '</option>');
        }
    }
});