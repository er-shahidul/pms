function advancedPaymentValidation() {

    var advancePayment = document.forms["purchaseorder"]["purchaseorder[advancePaymentAmount]"].value;
    if (advancePayment == null || advancePayment == "") {
        alert("advance Payment Must Be fill Out");
        return false;
    } else {
        $('#purchase-form').submit()
    }
}

$('#purchaseorder_submit').click( function (){

    if($("#purchaseorder_paymentType option:selected").text() == 'Advance payment'){
        $("#table_order tfoot tr.advancePayment").show();
        advancedPaymentValidation();
        return false;
    } else {
        $("#table_order tfoot tr.advancePayment").hide();
    }
});

if($("#purchaseorder_paymentType option:selected").text() == 'Advance payment'){
    $("#table_order tfoot tr.advancePayment").show();

} else {
    $("#table_order tfoot tr.advancePayment").hide();
}


$('#purchaseorder_poNonpo').change( function (){
    var value = $("#purchaseorder_poNonpo option:selected").text();

    if(value == 'Local Purchase' ||  value == 'Project Purchase'){
        $("#purchaseorder_paymentFrom").val('local-office');
    } else {
        $("#purchaseorder_paymentFrom").val('head-office');
    }
});
$('#purchaseorder_paymentType').change( function (){
    var value = $("#purchaseorder_paymentType option:selected").text();

    if(value == 'Advance payment'){
        $("#table_order tfoot tr.advancePayment").show();
    } else {
        $("#table_order tfoot tr.advancePayment").hide();
    }
});

$(".confirm").easyconfirm();





$('#ajax').on('hidden.bs.modal',function(){
    $(this).removeData('bs.modal');
});

//var quantityArr = {{ quantityArr|json_encode|raw }};

for(var i = 0; i < quantityArr.length; i++) {
    inputFieldValue(i);
}

function inputFieldValue(x) {
    document.getElementById("purchaseorder_purchaseOrderItems_" + x + "_quantity").onkeyup = function () {
        var input = parseInt(this.value);
        if (input < 0 || input > quantityArr[x])
            alert("Value should be between 0 - " + quantityArr[x]);
        return false;
    }
}

var subTotal= 0;

var poNonpo =$("#purchaseorder_poNonpo").val();

purchaseType(poNonpo);

function purchaseType(poNonpo){
    if(poNonpo == 10){
        $('.buyer-group').show();
        $('.vendor-group').hide();
        $('#purchaseorder_vendor').val('');

        $( "span#vendorAddress" ).html('');
        $( "span#vendorEmail" ).html('');
        $( "span#vendorContactPerson" ).html('');
        $( "span#vendorContactNo" ).html('');
    }else if(poNonpo != 10){
        $('.buyer-group').hide();
        $('.vendor-group').show();
        $('#purchaseorder_buyer').val('');
    }else if(poNonpo == ''){
        $('.buyer-group').hide();
        $('.vendor-group').hide();
        $('#purchaseorder_buyer').val('');
        $('#purchaseorder_vendor').val('');

        $( "span#vendorAddress" ).html('');
        $( "span#vendorEmail" ).html('');
        $( "span#vendorContactPerson" ).html('');
        $( "span#vendorContactNo" ).html('');
    }else{
        $('.buyer-group').hide();
        $('.vendor-group').hide();
        $('#purchaseorder_buyer').val('');
        $('#purchaseorder_vendor').val('');

        $( "span#vendorAddress" ).html('');
        $( "span#vendorEmail" ).html('');
        $( "span#vendorContactPerson" ).html('');
        $( "span#vendorContactNo" ).html('');
    }
}

$("#purchaseorder_poNonpo").change(function () {
    var poNonpo = $("#purchaseorder_poNonpo").val();

    $.ajax({
        type: "post",
        url: Routing.generate('find_terms_and_conditions'),
        data: "poNonpo=" + poNonpo,
        success: function (response, status) {
            $('.tocD').html( response );
        }
    });

    purchaseType(poNonpo);
});

$("#purchaseorder_vendor").change(function () {

    var vendorName = $(this).val();

    $.ajax({
        type: "post",
        url: Routing.generate('vendor_find_address'),
        data: "vendorName=" + vendorName,
        dataType: 'json',
        success: function (response) {
            if (response.responseCode == 200) {

                var address = response.vendorAddress;
                var email = response.email;
                var contactPerson = response.contractPerson;
                var contactNo = response.contractNo;

                $( "span#vendorAddress" ).html(address);
                $( "span#vendorEmail" ).html(email);
                $( "span#vendorContactPerson" ).html(contactPerson);
                $( "span#vendorContactNo" ).html(contactNo);

            }
        }
    });
});

$('.price, .quantity').live("click keyup", (PurchaseOrderQuantityCalculation));

function PurchaseOrderQuantityCalculation() {

    var subTotal = 0;
    var price = parseFloat($(this).closest('td').parent('tr').find('.price').val());
    var quantity = parseFloat($(this).closest('td').parent('tr').find('.quantity').val());

    if (!price) {
        price = 0;
    }
    if (!quantity) {
        quantity = 0;
    }

    $(this).closest('td').parent('tr').find('.amount').val(price * quantity);
    $('.amount').each(function(){
        amount = parseFloat($(this).val());
        if (amount){
            subTotal += amount;
        }
    });

    $('.subtotal').val(subTotal);
    $('.netTotal').val(subTotal);
}