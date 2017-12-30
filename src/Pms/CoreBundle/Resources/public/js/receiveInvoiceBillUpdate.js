function invoiceBillAutoComplete() {


    var $url = Routing.generate('invoice_bill_auto_complete_for_receive');

    $(".select2InvoiceBillAutoComplete").select2({

        ajax: {

            url: $url,
            dataType: 'json',
            delay: 250,
            data: function (params, page) {
                return {
                    q: params,
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
            return item.text
        }, // omitted for brevity, see the source of this page
        formatSelection: function (item) {
            return item.text
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
invoiceBillAutoComplete();