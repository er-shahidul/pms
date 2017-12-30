$(".confirm").easyconfirm();

window.items;
window.category;
var itemPrice;

function priceT() {

    var lineTotal = 0;
    var row = $('table tbody tr');

    row.find('.totalPriceAll').each(function () {
        lineTotal += parseFloat($(this).val());
    });

    var itemCost = $("#purchaserequisition_prCost").val(lineTotal);

    var projectName = $("#purchaserequisition_project").val();
    var subCategory = $("#purchaserequisition_subCategory").val();

    var today = new Date();
    var dd = 1;
    var mm = today.getMonth() + 1;
    var yyyy = today.getFullYear();
    var month = yyyy + '-' + mm + '-' + dd;

    var budgetArray = new Array(month, projectName, subCategory);
    $.ajax({
        type: "post",
        url: Routing.generate('find_budget'),
        data: "budgetArray=" + budgetArray,
        dataType: 'json',
        success: function (response) {
            var amount = response.netTotal;
            var total = response.total;
            if(total == null){
                total = 0;
            }
            var itemCost = $("#purchaserequisition_prCost").val();
            var totalCost = parseFloat(total) + parseFloat(itemCost);

            if (amount < totalCost) {
                $("span#amountCheck").html("Projects monthly budget cross that amount");
            } else if (amount > totalCost) {
                $("span#amountCheck").html("");
            } else {
                $("span#amountCheck").html("Not Find Budget");
            }
        }
    });
}

var projectName = $("#purchaserequisition_project").val();
if (projectName > 0) {
    projectRequest(projectName);
}

var x = document.getElementById("tableRowCount").rows.length;

for (var index = 0; x > index; index++) {
    var item = $("#purchaserequisition_purchaseRequisitionItems_" + index + "_item").val();
    findType(item, index);
}

itemAutoComplete();

function findType(item, index) {

    $.ajax({
        type: "post",
        url: Routing.generate('find_item_type'),
        data: "item=" + item,
        dataType: 'json',
        success: function (response) {

            var itemType = response.itemType;
            var itemUnit = response.itemUnit;
            var itemPrice = response.price;

            $("#purchaserequisition_purchaseRequisitionItems_" + index + "_itemType").val(itemType);
            $("#purchaserequisition_purchaseRequisitionItems_" + index + "_UOM").val(itemUnit);

            $("#purchaserequisition_purchaseRequisitionItems_" + index + "_quantity").change(function () {
                var totalQty = $(this).val();
                var x = calculation(itemPrice, totalQty);
                $("#purchaserequisition_purchaseRequisitionItems_" + index + "_totalPrice").val(x);
                priceT();
            });
        }
    });
}

$('#projectGroup').hide();

$("#purchaserequisition_project").change(function () {
    var projectName = $(this).val();
    $('#projectGroup').show();
    projectRequest(projectName);
});

function projectRequest(projectName) {
    $.ajax({
        type: "post",
        url: Routing.generate('project_find_all'),
        data: "projectName=" + projectName,
        dataType: 'json',
        success: function (response) {
            var projects = response.projectFindAll;
            $("#area").val(projects['projectArea']);
            $("#address").val(projects['projectAddress']);
            $("#type").val(projects['projectCategory']);
            $("#head").val(projects['projectHead']);
            $("#costCenterNumber").val(projects['costCenterNumber']);
            $("#projectHeadFull").val(projects['projectHeadFull']);
        }
    });
}


function addTagForm($collectionHolder, $newLinkLi) {


    var prototype = $collectionHolder.data('prototype');
    var index = $collectionHolder.data('index');
    var newForm = prototype.replace(/__name__/g, index);
    $collectionHolder.data('index', index + 1);
    var $newFormLi = $('<tr></tr>').append(newForm);
    $newLinkLi.before($newFormLi);

    itemAutoComplete($newFormLi.find('.select2ItemAutoComplete'));

    $('.date-picker').datepicker();

    $('#purchaserequisition_purchaseRequisitionItems_0_remove').hide();
    $("#purchaserequisition_purchaseRequisitionItems_0_item").change(function () {
        var item = $(this).val();

        purchaseRequisitionCategory(item);
    });

    function calculation(itemPrice, totalQty) {
        var totalPrice = 0;
        totalPrice = totalPrice + (itemPrice * totalQty);
        return totalPrice;
    }

    $("#purchaserequisition_purchaseRequisitionItems_" + index + "_item").change(function () {

        var item = $(this).val();
        $.ajax({
            type: "post",
            url: Routing.generate('find_item_type'),
            data: "item=" + item,
            dataType: 'json',
            success: function (response) {
                var itemType = response.itemType;
                var itemUnit = response.itemUnit;
                var itemPrice = response.price;

                $("#purchaserequisition_purchaseRequisitionItems_" + index + "_itemType").val(itemType);
                $("#purchaserequisition_purchaseRequisitionItems_" + index + "_UOM").val(itemUnit);

                $("#purchaserequisition_purchaseRequisitionItems_" + index + "_quantity").change(function () {
                    var totalQty = $(this).val();
                    var x = calculation(itemPrice, totalQty);
                    $("#purchaserequisition_purchaseRequisitionItems_" + index + "_totalPrice").val(x);
                    priceT();
                });
            }
        });
    });

    $("#purchaserequisition_purchaseRequisitionItems_" + index + "_remove").click(function () {
        var parent = $(this).closest('tr');
        parent.remove();
        priceT();
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
                var ic = $("#purchaserequisition_category");
                ic.find('option').remove();
                $.each(category, function (index, category) {
                    $('<option>').attr('value', category.id).text(category.categoryName).appendTo(ic);
                });
            }
          //  $("#purchaserequisition_category").find('option:eq(1)').attr('selected', true);

            var categoryId = $("#purchaserequisition_category").val();
            purchaseRequisitionSubCategory(categoryId);
            //purchaseRequisitionItem(categoryId);
        }
    });
}

function purchaseRequisitionItem(category) {
    $.ajax({
        type: "post",
        url: Routing.generate('category_wise_item'),
        data: "category=" + category,
        dataType: 'json',
        success: function (response) {
            window.items = response.categoryWiseItem;
        }
    });
}

$("#purchaserequisition_subCategory").change(function () {
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
                var psc = $("#purchaserequisition_subCategory");
                psc.find('option').remove();
                $.each(subCategory, function (index, subCategory) {
                    $('<option>').attr('value', subCategory.id).text(subCategory.subCategoryName).appendTo(psc);
                });
            }

            var subCategoryId = $("#purchaserequisition_subCategory").val();
            purchaseRequisitionCostHeader(subCategoryId);
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
                var pch = $("#purchaserequisition_costHeader");
                pch.find('option').remove();
                $.each(costHeader, function (index, item) {
                    $('<option>').attr('value', item.id).text(item.title).appendTo(pch);
                });
            }
        }
    });
}

var item = $('#purchaserequisition_purchaseRequisitionItems_0_item').val();

purchaseRequisitionCategory(item);
var $collectionHolder;
if (approveStatus != 3){
var $addTagLink = $('<td colspan ="7"><div style="text-align: left;margin-left: 20px;"><a href="#" style="background: #4b8df8;color: white !important;" class="btn btn-xs add_tag_link"><i class="fa fa-plus"></i> Add Item</a></div></td>');
var $newLinkLi = $('<tr></tr>').append($addTagLink);
}
$collectionHolder = $('#purchaseItems');
$collectionHolder.append($newLinkLi);
$collectionHolder.data('index', $collectionHolder.find(':input').length);

handleDeleteAction();

$addTagLink.on('click', function (e) {
    e.preventDefault();
    addTagForm($collectionHolder, $newLinkLi);

    var trLength = $('tbody#purchaseItems').find('tr').length;
    if (trLength > 2 && window.items) {
        var lastSelect = $('tbody#purchaseItems').find('tr:eq(' + (trLength - 2) + ')').find('select:first');
        lastSelect.html('');
        for (var i = 0; i < window.items.length; i++) {
            lastSelect.append('<option value="' + window.items[i].id + '">' + window.items[i].itemName + '</option>');
        }
    }
});

$("#purchaserequisition_category").change(function () {
    var $container = $('#purchaseItems');
    purchaseRequisitionSubCategory(this.value)
    $('#purchaseItems').data('index', 1);
    $container.find('tr').not(':first').not(':last').remove();
});

function handleDeleteAction() {

    $("#tableRowCount").on('click', '.closeTd', function(e) {
        e.preventDefault();

        var $tagFormLi = $(this).closest('tr');

        $tagFormLi.remove();

    });
}

function itemAutoComplete($el) {

    if(typeof $el == 'undefined') {
        $el = $(".select2ItemAutoComplete");
    }
    var $url = Routing.generate('item_auto_complete_for_pr');

    $el.select2({

        ajax: {

            url: $url,
            dataType: 'json',
            delay: 250,
            data: function (params, page) {
                return {
                    q: params,
                    categoryId:$('#purchaserequisition_category').val(),
                    page_limit: 100
                };
            },
            results: function (data, page) {
                return {
                    results: data
                };
            },
            cache: true
        },
        escapeMarkup: function (m) {

            return m;
        },
        formatResult: function (item) {
            var originalText = item.text;
            // return item.text
            return "<div title ='" + originalText + "'>" + originalText + "</div>";
        }, // omitted for brevity, see the source of this page
        formatSelection: function (item) {
            var originalText = item.text;
            // return item.text
            return "<div title ='" + originalText + "'>" + originalText + "</div>";
        }, // omitted for brevity, see the source of this page
        initSelection: function (element, callback) {
            $.ajax({
                url: $url,
                data: 'item=' + element.val(),
                dataType: 'json'
            }).done(function (data) {
                callback(data);
            });
        },
        allowClear: true,
        minimumInputLength: 1
    });




    $('.popover-link').hover(function () {

        var e = $(this);
        e.off('hover');
        $.get(e.data('url'), function (d) {
            e.popover({content: d}).popover('show');

        });
    });
}