<script type="text/javascript">
$(document).ready( function(){

//Date range as a button
$('#sell_list_filter_date_range').daterangepicker(
    dateRangeSettings,
    function (start, end) {
        $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
        sell_table.ajax.reload();
    }
);
$('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
    $('#sell_list_filter_date_range').val('');
    sell_table.ajax.reload();
});

$(document).on('change', '#sell_list_filter_location_id, #sell_list_filter_customer_id, #sell_list_filter_payment_status, #created_by, #sales_cmsn_agnt, #service_staffs, #shipping_status',  function() {
    sell_table.ajax.reload();
});
update_statistics();

sell_table = $('#sell_table').DataTable({
        processing: true,
        serverSide: true,
        fixedHeader:false,
        fixedHeader:false,
        aaSorting: [[1, 'desc']],
        scrollY: "75vh",
        scrollX:        true,
        scrollCollapse: true,
        "ajax": {
            "url": "/sells",
            "data": function ( d ) {
                if($('#sell_list_filter_date_range').val()) {
                    var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }
                if ($('#is_direct_sale').length) {
                    d.is_direct_sale = $('#is_direct_sale').val();
                }

                if($('#sell_list_filter_location_id').length) {
                    d.location_id = $('#sell_list_filter_location_id').val();
                }
                d.customer_id = $('#sell_list_filter_customer_id').val();

                if($('#sell_list_filter_payment_status').length) {
                    d.payment_status = $('#sell_list_filter_payment_status').val();
                }
                if($('#created_by').length) {
                    d.created_by = $('#created_by').val();
                }
                if($('#sales_cmsn_agnt').length) {
                    d.sales_cmsn_agnt = $('#sales_cmsn_agnt').val();
                }
                if($('#service_staffs').length) {
                    d.service_staffs = $('#service_staffs').val();
                }

                if($('#shipping_status').length) {
                    d.shipping_status = $('#shipping_status').val();
                }

                if($('#only_subscriptions').length && $('#only_subscriptions').is(':checked')) {
                    d.only_subscriptions = 1;
                }

                d = __datatable_ajax_callback(d);
            }
        },
        columns: [
            { data: 'action', name: 'action', orderable: false, "searchable": false},
            { data: 'transaction_date', name: 'transaction_date'  },
            { data: 'invoice_no', name: 'invoice_no'},
            { data: 'is_valid', name: 'is_valid'},
            { data: 'conatct_name', name: 'conatct_name'},
            { data: 'mobile', name: 'contacts.mobile'},
            { data: 'business_location', name: 'bl.name'},
            { data: 'payment_status', name: 'payment_status'},
            { data: 'payment_methods', orderable: false, "searchable": false},
            { data: 'final_total', name: 'final_total'},
            { data: 'total_paid', name: 'total_paid', "searchable": false},
            { data: 'utility', name: 'utility', orderable: false, searchable: false},
            { data: 'total_remaining', name: 'total_remaining'},
            { data: 'return_due', orderable: false, "searchable": false},
            { data: 'shipping_status', name: 'shipping_status'},
            { data: 'total_items', name: 'total_items', "searchable": false},
            { data: 'types_of_service_name', name: 'tos.name', @if(empty($is_types_service_enabled)) visible: false @endif},
            { data: 'service_custom_field_1', name: 'service_custom_field_1', @if(empty($is_types_service_enabled)) visible: false @endif},
            { data: 'added_by', name: 'u.first_name'},
            { data: 'additional_notes', name: 'additional_notes'},
            { data: 'staff_note', name: 'staff_note'},
            { data: 'shipping_details', name: 'shipping_details'},
            { data: 'table_name', name: 'tables.name', @if(empty($is_tables_enabled)) visible: false @endif },
            { data: 'waiter', name: 'ss.first_name', @if(empty($is_service_staff_enabled)) visible: false @endif }
        ],
        "fnDrawCallback": function (oSettings) {
            __currency_convert_recursively($('#sell_table'));
        },
        "footerCallback": function ( row, data, start, end, display ) {
            var footer_sale_total = 0;
            var footer_total_paid = 0;
            var footer_total_remaining = 0;
            var footer_total_sell_return_due = 0;
            var footer_total_utility = 0;
            for (var r in data){
                footer_sale_total += $(data[r].final_total).data('orig-value') ? parseFloat($(data[r].final_total).data('orig-value')) : 0;
                footer_total_paid += $(data[r].total_paid).data('orig-value') ? parseFloat($(data[r].total_paid).data('orig-value')) : 0;
                footer_total_remaining += $(data[r].total_remaining).data('orig-value') ? parseFloat($(data[r].total_remaining).data('orig-value')) : 0;
                footer_total_sell_return_due += $(data[r].return_due).find('.sell_return_due').data('orig-value') ? parseFloat($(data[r].return_due).find('.sell_return_due').data('orig-value')) : 0;
                footer_total_utility += $(data[r].utility).data('orig-value') ? parseFloat($(data[r].utility).data('orig-value')) : 0;
            }

            $('.footer_total_sell_return_due').html(__currency_trans_from_en(footer_total_sell_return_due));
            $('.footer_total_remaining').html(__currency_trans_from_en(footer_total_remaining));
            $('.footer_total_paid').html(__currency_trans_from_en(footer_total_paid));
            $('.footer_sale_total').html(__currency_trans_from_en(footer_sale_total));
            $('.footer_total_utility').html(__currency_trans_from_en(footer_total_utility));

            $('.footer_payment_status_count').html(__count_status(data, 'payment_status'));
            $('.service_type_count').html(__count_status(data, 'types_of_service_name'));
            $('.payment_method_count').html(__count_status(data, 'payment_methods'));
        },
        createdRow: function( row, data, dataIndex ) {
            $( row ).find('td:eq(6)').attr('class', 'clickable_td');
        }
    });
    
    $('#only_subscriptions').on('ifChanged', function(event){
        sell_table.ajax.reload();
    });
});

function update_statistics() {
    get_stock_value();
        // console.log('------------------------------>', start, end)
    var location_id = '';
    if ($('#dashboard_location').length > 0) {
        location_id = $('#dashboard_location').val();
    }
    var data = { location_id: location_id };
    //get purchase details
    var loader = '<i class="fas fa-sync fa-spin fa-fw margin-bottom"></i>';
    $('.total_sell').html(loader);
    $('.total_expense').html(loader);
    $('.gross_profit').html(loader);
    $('.total_inventory').html(loader);
    $.ajax({
        method: 'get',
        url: '/home/get-totals',
        dataType: 'json',
        data: data,
        success: function(data) {
            
            //purchase details
            //sell details
            $('.total_sell').html(__currency_trans_from_en(data.total_sell, true));
            //expense details
            $('.total_expense').html(__currency_trans_from_en(data.total_expense, true));
            $('.gross_profit').html(__currency_trans_from_en(data.gross_profit, true));
            // $('.total_inventory').html(__currency_trans_from_en(data.total_inventory, true));
           },
    });

    function get_stock_value() {
    var loader = __fa_awesome();
    $('#closing_stock_by_pp').html(loader);
    var data = {
        location_id: $('#location_id').val(),
        category_id: $('#category_id').val(),
        sub_category_id: $('#sub_category_id').val(),
        brand_id: $('#brand').val(),
        unit_id: $('#unit').val(),
    }
    $.ajax({
        url: '/reports/get-stock-value',
        data: data,
        success: function(data) {
            $('#closing_stock_by_pp').text(__currency_trans_from_en(data.closing_stock_by_pp));
        },
    });
}
}




    $(document).on('click', '.btn-send-dian', function() {
        let button = $(this);
        let id = $(this).data('id');

        let originalContent = button.html();
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...');

        $.ajax({
            url: "{{ route('resend_invoice_data', ':id') }}".replace(':id', id), 
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.msg);
                    sell_table.ajax.reload();
                }else{
                    toastr.error(response.msg);
                    sell_table.ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al enviar:", error);
                button.prop('disabled', false).html(originalContent);
            }
        });
    });

</script>
