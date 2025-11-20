// Purchase Form Data Session Management Functions
function savePurchaseFormDataToSession() {
    var form_data = {
        supplier_id: $('#supplier_id').val(),
        supplier_name: $('#supplier_id option:selected').text(),
        location_id: $('#location_id').val(),
        ref_no: $('#ref_no').val(),
        transaction_date: $('#transaction_date').val(),
        status: $('#status').val(),
        exchange_rate: $('input[name="exchange_rate"]').val(),
        pay_term_number: $('input[name="pay_term_number"]').val(),
        pay_term_type: $('#pay_term_type').val(),
        cufe: $('input[name="cufe"]').val(),
        custom_field_1: $('input[name="custom_field_1"]').val(),
        custom_field_2: $('input[name="custom_field_2"]').val(),
        custom_field_3: $('input[name="custom_field_3"]').val(),
        custom_field_4: $('input[name="custom_field_4"]').val(),
        discount_type: $('#discount_type').val(),
        discount_amount: $('#discount_amount').val(),
        tax_id: $('#tax_id').val(),
        shipping_details: $('#shipping_details').val(),
        shipping_charges: $('#shipping_charges').val(),
        shipping_custom_field_1: $('input[name="shipping_custom_field_1"]').val(),
        shipping_custom_field_2: $('input[name="shipping_custom_field_2"]').val(),
        shipping_custom_field_3: $('input[name="shipping_custom_field_3"]').val(),
        shipping_custom_field_4: $('input[name="shipping_custom_field_4"]').val(),
        shipping_custom_field_5: $('input[name="shipping_custom_field_5"]').val(),
        additional_expense_key_1: $('#additional_expense_key_1').val(),
        additional_expense_value_1: $('#additional_expense_value_1').val(),
        additional_expense_key_2: $('#additional_expense_key_2').val(),
        additional_expense_value_2: $('#additional_expense_value_2').val(),
        additional_expense_key_3: $('#additional_expense_key_3').val(),
        additional_expense_value_3: $('#additional_expense_value_3').val(),
        additional_expense_key_4: $('#additional_expense_key_4').val(),
        additional_expense_value_4: $('#additional_expense_value_4').val(),
        additional_notes: $('#additional_notes').val(),
        payment_type: $('.payment_types_dropdown').val(),
        payment_amount: $('.payment-amount').val()
    };

    $.ajax({
        method: 'POST',
        url: '/purchases/save-form-session',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            form_data: form_data
        },
        dataType: 'json',
        async: true
    });
}

function loadPurchaseFormDataFromSession() {
    $.ajax({
        method: 'POST',
        url: '/purchases/load-form-session',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        async: false,
        success: function(result) {
            if (result.success && result.form_data) {
                var data = result.form_data;

                // Restore supplier
                console.log('Restoring supplier from session:', data);
                if (data.supplier_id && data.supplier_name) {
                    // Use the get_suppliers ajax call to fetch and select the supplier
                    $.ajax({
                        url: '/purchases/get_suppliers',
                        dataType: 'json',
                        data: {
                            q: data.supplier_name
                        },
                        success: function(response) {
                            console.log('Suppliers fetched:', response);

                            // The response is an array directly, not wrapped in results
                            var suppliers = Array.isArray(response) ? response : (response.results || []);

                            if (suppliers && suppliers.length > 0) {
                                // Find the matching supplier by ID
                                var matchedSupplier = suppliers.find(function(supplier) {
                                    return supplier.id == data.supplier_id;
                                });

                                if (matchedSupplier) {
                                    // Create the option with the full text format
                                    var optionText = matchedSupplier.text;
                                    if (matchedSupplier.business_name && matchedSupplier.contact_id) {
                                        optionText = matchedSupplier.text + ' - ' + matchedSupplier.business_name + ' (' + matchedSupplier.contact_id + ')';
                                    }

                                    var newOption = new Option(optionText, matchedSupplier.id, true, true);
                                    $('#supplier_id').html('').append(newOption);

                                    // Manually trigger the select2:select event with full data
                                    $('#supplier_id').trigger({
                                        type: 'select2:select',
                                        params: {
                                            data: matchedSupplier
                                        }
                                    });

                                    console.log('Supplier restored:', optionText);
                                } else {
                                    console.warn('Supplier ID not found in results');
                                }
                            } else {
                                console.warn('No suppliers found for:', data.supplier_name);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching suppliers:', error);
                        }
                    });
                }

                // Restore ref_no
                if (data.ref_no) {
                    $('#ref_no').val(data.ref_no);
                }

                // Restore status
                if (data.status) {
                    $('#status').val(data.status).trigger('change');
                }

                // Restore exchange rate
                if (data.exchange_rate) {
                    $('input[name="exchange_rate"]').val(data.exchange_rate);
                }

                // Restore pay term
                if (data.pay_term_number) {
                    $('input[name="pay_term_number"]').val(data.pay_term_number);
                }
                if (data.pay_term_type) {
                    $('#pay_term_type').val(data.pay_term_type).trigger('change');
                }

                // Restore cufe
                if (data.cufe) {
                    $('input[name="cufe"]').val(data.cufe);
                }

                // Restore custom fields
                if (data.custom_field_1) {
                    $('input[name="custom_field_1"]').val(data.custom_field_1);
                }
                if (data.custom_field_2) {
                    $('input[name="custom_field_2"]').val(data.custom_field_2);
                }
                if (data.custom_field_3) {
                    $('input[name="custom_field_3"]').val(data.custom_field_3);
                }
                if (data.custom_field_4) {
                    $('input[name="custom_field_4"]').val(data.custom_field_4);
                }

                // Restore discount
                if (data.discount_type) {
                    $('#discount_type').val(data.discount_type).trigger('change');
                }
                if (data.discount_amount) {
                    __write_number($('#discount_amount'), data.discount_amount);
                }

                // Restore tax
                if (data.tax_id) {
                    $('#tax_id').val(data.tax_id).trigger('change');
                }

                // Restore shipping
                if (data.shipping_charges) {
                    __write_number($('#shipping_charges'), data.shipping_charges);
                }
                if (data.shipping_details) {
                    $('#shipping_details').val(data.shipping_details);
                }

                // Restore shipping custom fields
                if (data.shipping_custom_field_1) {
                    $('input[name="shipping_custom_field_1"]').val(data.shipping_custom_field_1);
                }
                if (data.shipping_custom_field_2) {
                    $('input[name="shipping_custom_field_2"]').val(data.shipping_custom_field_2);
                }
                if (data.shipping_custom_field_3) {
                    $('input[name="shipping_custom_field_3"]').val(data.shipping_custom_field_3);
                }
                if (data.shipping_custom_field_4) {
                    $('input[name="shipping_custom_field_4"]').val(data.shipping_custom_field_4);
                }
                if (data.shipping_custom_field_5) {
                    $('input[name="shipping_custom_field_5"]').val(data.shipping_custom_field_5);
                }

                // Restore additional expenses
                if (data.additional_expense_key_1) {
                    $('#additional_expense_key_1').val(data.additional_expense_key_1);
                }
                if (data.additional_expense_value_1) {
                    __write_number($('#additional_expense_value_1'), data.additional_expense_value_1);
                }
                if (data.additional_expense_key_2) {
                    $('#additional_expense_key_2').val(data.additional_expense_key_2);
                }
                if (data.additional_expense_value_2) {
                    __write_number($('#additional_expense_value_2'), data.additional_expense_value_2);
                }
                if (data.additional_expense_key_3) {
                    $('#additional_expense_key_3').val(data.additional_expense_key_3);
                }
                if (data.additional_expense_value_3) {
                    __write_number($('#additional_expense_value_3'), data.additional_expense_value_3);
                }
                if (data.additional_expense_key_4) {
                    $('#additional_expense_key_4').val(data.additional_expense_key_4);
                }
                if (data.additional_expense_value_4) {
                    __write_number($('#additional_expense_value_4'), data.additional_expense_value_4);
                }

                // Restore notes
                if (data.additional_notes) {
                    $('#additional_notes').val(data.additional_notes);
                }

                // Restore payment
                if (data.payment_type) {
                    $('.payment_types_dropdown').val(data.payment_type).trigger('change');
                }
                if (data.payment_amount) {
                    __write_number($('.payment-amount'), data.payment_amount);
                }
            }
        }
    });
}

// Purchase Cart Session Management Functions
function savePurchaseCartToSession() {
    var location_id = $('#location_id').val();
    if (!location_id) {
        return;
    }

    var cart_data = [];
    $('#purchase_entry_table tbody tr').each(function() {
        var $row = $(this);
        // Find product_id and variation_id by name attribute since they don't have specific classes
        var product_id = $row.find('input[name*="[product_id]"]').val();
        var variation_id = $row.find('input.hidden_variation_id, input[name*="[variation_id]"]').val();

        if (product_id) {
            var item = {
                product_id: product_id,
                variation_id: variation_id,
                quantity: __read_number($row.find('.purchase_quantity'), true)
            };
            cart_data.push(item);
        }
    });
    console.log('Purchase cart data:', cart_data);
    $.ajax({
        method: 'POST',
        url: '/purchases/save-cart-session',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            location_id: location_id,
            cart_data: cart_data
        },
        dataType: 'json',
        async: true
    });
}

function loadPurchaseCartFromSession() {
    var location_id = $('#location_id').val();
    if (!location_id) {
        return;
    }

    $.ajax({
        method: 'POST',
        url: '/purchases/load-cart-session',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            location_id: location_id
        },
        dataType: 'json',
        async: false,
        success: function(result) {
            if (result.success && result.cart_data && result.cart_data.length > 0) {
                // Load products from session
                result.cart_data.forEach(function(item) {
                    get_purchase_entry_row(item.product_id, item.variation_id);
                });
            }
        }
    });
}

function clearPurchaseCartSession() {
    var location_id = $('#location_id').val();
    if (!location_id) {
        return;
    }

    $.ajax({
        method: 'POST',
        url: '/purchases/clear-cart-session',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            location_id: location_id
        },
        dataType: 'json',
        async: true
    });
}

$(document).ready(function() {
    if ($('input#iraqi_selling_price_adjustment').length > 0) {
        iraqi_selling_price_adjustment = true;
    } else {
        iraqi_selling_price_adjustment = false;
    }

    //Date picker
    $('#transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });

    $('#delivery_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });

    //get suppliers
    $('#supplier_id').select2({
        ajax: {
            url: '/purchases/get_suppliers',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                };
            },
            processResults: function(data) {
                return {
                    results: data,
                };
            },
        },
        minimumInputLength: 1,
        escapeMarkup: function(m) {
            return m;
        },
        templateResult: function(data) {
            if (!data.id) {
                return data.text;
            }
            var html = data.text + ' - ' + data.business_name + ' (' + data.contact_id + ')';
            return html;
        },
        language: {
            noResults: function() {
                var name = $('#supplier_id')
                    .data('select2')
                    .dropdown.$search.val();
                return (
                    '<button type="button" data-name="' +
                    name +
                    '" class="btn btn-link add_new_supplier"><i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i>&nbsp; ' +
                    __translate('add_name_as_new_supplier', { name: name }) +
                    '</button>'
                );
            },
        },
    }).on('select2:select', function (e) {
        var data = e.params.data;
        $('#pay_term_number').val(data.pay_term_number);
        $('#pay_term_type').val(data.pay_term_type);
        $('#advance_balance_text').text(__currency_trans_from_en(data.balance), true);
        $('#advance_balance').val(data.balance);
        set_supplier_address(data);
    });

    //Quick add supplier
    $(document).on('click', '.add_new_supplier', function() {
        $('#supplier_id').select2('close');
        var name = $(this).data('name');
        $('.contact_modal')
            .find('input#name')
            .val(name);
        $('.contact_modal')
            .find('select#contact_type')
            .val('supplier')
            .closest('div.contact_type_div')
            .addClass('hide');
        $('.contact_modal').modal('show');
    });

    $('form#quick_add_contact')
        .submit(function(e) {
            e.preventDefault();
        })
        .validate({
            rules: {
                contact_id: {
                    remote: {
                        url: '/contacts/check-contacts-id',
                        type: 'post',
                        data: {
                            contact_id: function() {
                                return $('#contact_id').val();
                            },
                            hidden_id: function() {
                                if ($('#hidden_id').length) {
                                    return $('#hidden_id').val();
                                } else {
                                    return '';
                                }
                            },
                        },
                    },
                },
            },
            messages: {
                contact_id: {
                    remote: LANG.contact_id_already_exists,
                },
            },
            submitHandler: function(form) {
                $.ajax({
                    method: 'POST',
                    url: base_path + '/check-mobile',
                    dataType: 'json',
                    data: {
                        contact_id: function() {
                            return $('#hidden_id').val();
                        },
                        mobile_number: function() {
                            return $('#mobile').val();
                        },
                    },
                    beforeSend: function(xhr) {
                        __disable_submit_button($(form).find('button[type="submit"]'));
                    },
                    success: function(result) {
                        if (result.is_mobile_exists == true) {
                            swal({
                                title: LANG.sure,
                                text: result.msg,
                                icon: 'warning',
                                buttons: true,
                                dangerMode: true,
                            }).then(willContinue => {
                                if (willContinue) {
                                    submitQuickAddPurchaseContactForm(form);
                                } else {
                                    $('#mobile').select();
                                }
                            });
                            
                        } else {
                            submitQuickAddPurchaseContactForm(form);
                        }
                    },
                });
            },
        });
    $('.contact_modal').on('hidden.bs.modal', function() {
        $('form#quick_add_contact')
            .find('button[type="submit"]')
            .removeAttr('disabled');
        $('form#quick_add_contact')[0].reset();
    });

    //Add products
    if ($('#search_product').length > 0) {
        $('#search_product')
            .autocomplete({
                source: function(request, response) {
                    $.getJSON(
                        '/purchases/get_products',
                        { location_id: $('#location_id').val(), term: request.term },
                        response
                    );
                },
                minLength: 2,
                response: function(event, ui) {
                    if (ui.content.length == 1) {
                        ui.item = ui.content[0];
                        $(this)
                            .data('ui-autocomplete')
                            ._trigger('select', 'autocompleteselect', ui);
                        $(this).autocomplete('close');
                    } else if (ui.content.length == 0) {
                        var term = $(this).data('ui-autocomplete').term;
                        swal({
                            title: LANG.no_products_found,
                            text: __translate('add_name_as_new_product', { term: term }),
                            buttons: [LANG.cancel, LANG.ok],
                        }).then(value => {
                            if (value) {
                                var container = $('.quick_add_product_modal');
                                $.ajax({
                                    url: '/products/quick_add?product_name=' + term,
                                    dataType: 'html',
                                    success: function(result) {
                                        $(container)
                                            .html(result)
                                            .modal('show');
                                    },
                                });
                            }
                        });
                    }
                },
                select: function(event, ui) {
                    $(this).val(null);
                    should_prepend_purchase_row = true;
                    get_purchase_entry_row(ui.item.product_id, ui.item.variation_id);
                },
            })
            .autocomplete('instance')._renderItem = function(ul, item) {
            return $('<li>')
                .append('<div>' + item.text + '</div>')
                .appendTo(ul);
        };
    }

    $(document).on('click', '.remove_purchase_entry_row', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(value => {
            if (value) {
                $(this)
                    .closest('tr')
                    .remove();
                update_table_total();
                update_grand_total();
                update_table_sr_number();

                // Save cart to session after removing product
                savePurchaseCartToSession();
            }
        });
    });

    //On Change of quantity
    $(document).on('change', '.purchase_quantity', function() {
        var row = $(this).closest('tr');
        var quantity = __read_number($(this), true);
        var purchase_before_tax = __read_number(row.find('input.purchase_unit_cost'), true);
        var purchase_after_tax = __read_number(
            row.find('input.purchase_unit_cost_after_tax'),
            true
        );

        //Calculate sub totals
        var sub_total_before_tax = quantity * purchase_before_tax;
        var sub_total_after_tax = quantity * purchase_after_tax;

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(sub_total_before_tax, false, true)
        );
        __write_number(
            row.find('input.row_subtotal_before_tax_hidden'),
            sub_total_before_tax,
            true
        );

        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        );
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);

        update_table_total();
        update_grand_total();
    });

    $(document).on('keyup', '.purchase_quantity', function() {
        var row = $(this).closest('tr');
        var quantity = __read_number($(this), true);

        // Update total stock badge
        updateTotalStockBadge(row, quantity);

    });

    $(document).on('keyup', '.purchase_unit_cost_without_discount', function() {
        __write_number($(this), $(this).val());        
        var purchase_before_discount = __read_number($(this), true);

        var row = $(this).closest('tr');
        var discount_percent = __read_number(row.find('input.inline_discounts'), true);
        var quantity = __read_number(row.find('input.purchase_quantity'), true);

        //Calculations.
        var purchase_before_tax =
            parseFloat(purchase_before_discount) -
            __calculate_amount('percentage', discount_percent, purchase_before_discount);

        __write_number(row.find('input.purchase_unit_cost'), purchase_before_tax, true);

        var sub_total_before_tax = quantity * purchase_before_tax;

        //Tax
        var tax_rate = parseFloat(
            row
                .find('select.purchase_line_tax_id')
                .find(':selected')
                .data('tax_amount')
        );
        var tax = __calculate_amount('percentage', tax_rate, purchase_before_tax);

        var purchase_after_tax = purchase_before_tax + tax;
        var sub_total_after_tax = quantity * purchase_after_tax;

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(sub_total_before_tax, false, true)
        );
        __write_number(
            row.find('input.row_subtotal_before_tax_hidden'),
            sub_total_before_tax,
            true
        );

        __write_number(row.find('input.purchase_unit_cost_after_tax'), purchase_after_tax, true);
        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        );
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);

        row.find('.purchase_product_unit_tax_text').text(
            __currency_trans_from_en(tax, false, true)
        );
        __write_number(row.find('input.purchase_product_unit_tax'), tax, true);

        update_inline_profit_percentage(row);
        update_table_total();
        update_grand_total();
    });

    $(document).on('change', '.inline_discounts', function() {
        var row = $(this).closest('tr');

        var discount_percent = __read_number($(this), true);

        var quantity = __read_number(row.find('input.purchase_quantity'), true);
        var purchase_before_discount = __read_number(
            row.find('input.purchase_unit_cost_without_discount'),
            true
        );

        //Calculations.
        var purchase_before_tax =
            parseFloat(purchase_before_discount) -
            __calculate_amount('percentage', discount_percent, purchase_before_discount);

        __write_number(row.find('input.purchase_unit_cost'), purchase_before_tax, true);

        var sub_total_before_tax = quantity * purchase_before_tax;

        //Tax
        var tax_rate = parseFloat(
            row
                .find('select.purchase_line_tax_id')
                .find(':selected')
                .data('tax_amount')
        );
        var tax = __calculate_amount('percentage', tax_rate, purchase_before_tax);

        var purchase_after_tax = purchase_before_tax + tax;
        var sub_total_after_tax = quantity * purchase_after_tax;

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(sub_total_before_tax, false, true)
        );
        __write_number(
            row.find('input.row_subtotal_before_tax_hidden'),
            sub_total_before_tax,
            true
        );

        __write_number(row.find('input.purchase_unit_cost_after_tax'), purchase_after_tax, true);
        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        );
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);
        row.find('.purchase_product_unit_tax_text').text(
            __currency_trans_from_en(tax, false, true)
        );
        __write_number(row.find('input.purchase_product_unit_tax'), tax, true);

        update_inline_profit_percentage(row);
        update_table_total();
        update_grand_total();
    });

    $(document).on('change', '.purchase_unit_cost', function() {
        var row = $(this).closest('tr');
        var quantity = __read_number(row.find('input.purchase_quantity'), true);
        var purchase_before_tax = __read_number($(this), true);

        var sub_total_before_tax = quantity * purchase_before_tax;

        //Update unit cost price before discount
        var discount_percent = __read_number(row.find('input.inline_discounts'), true);
        var purchase_before_discount = __get_principle(purchase_before_tax, discount_percent, true);
        __write_number(
            row.find('input.purchase_unit_cost_without_discount'),
            purchase_before_discount,
            true
        );

        //Tax
        var tax_rate = parseFloat(
            row
                .find('select.purchase_line_tax_id')
                .find(':selected')
                .data('tax_amount')
        );
        var tax = __calculate_amount('percentage', tax_rate, purchase_before_tax);

        var purchase_after_tax = purchase_before_tax + tax;
        var sub_total_after_tax = quantity * purchase_after_tax;

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(sub_total_before_tax, false, true)
        );
        __write_number(
            row.find('input.row_subtotal_before_tax_hidden'),
            sub_total_before_tax,
            true
        );

        row.find('.purchase_product_unit_tax_text').text(
            __currency_trans_from_en(tax, false, true)
        );
        __write_number(row.find('input.purchase_product_unit_tax'), tax, true);

        //row.find('.purchase_product_unit_tax_text').text( tax );
        __write_number(row.find('input.purchase_unit_cost_after_tax'), purchase_after_tax, true);
        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        );
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);

        update_inline_profit_percentage(row);
        update_table_total();
        update_grand_total();
    });

    $(document).on('change', 'select.purchase_line_tax_id', function() {
        var row = $(this).closest('tr');
        var purchase_before_tax = __read_number(row.find('.purchase_unit_cost'), true);
        var quantity = __read_number(row.find('input.purchase_quantity'), true);

        //Tax
        var tax_rate = parseFloat(
            $(this)
                .find(':selected')
                .data('tax_amount')
        );
        var tax = __calculate_amount('percentage', tax_rate, purchase_before_tax);

        //Purchase price
        var purchase_after_tax = purchase_before_tax + tax;
        var sub_total_after_tax = quantity * purchase_after_tax;

        row.find('.purchase_product_unit_tax_text').text(
            __currency_trans_from_en(tax, false, true)
        );
        __write_number(row.find('input.purchase_product_unit_tax'), tax, true);

        __write_number(row.find('input.purchase_unit_cost_after_tax'), purchase_after_tax, true);

        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        );
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);

        update_table_total();
        update_grand_total();
    });

    $(document).on('keyup', '.purchase_unit_cost_after_tax', function() {
        __write_number($(this), $(this).val());        
        var row = $(this).closest('tr');
        var purchase_after_tax = __read_number($(this), true);
        var quantity = __read_number(row.find('input.purchase_quantity'), true);

        var sub_total_after_tax = purchase_after_tax * quantity;

        //Tax
        var tax_rate = parseFloat(
            row
                .find('select.purchase_line_tax_id')
                .find(':selected')
                .data('tax_amount')
        );
        var purchase_before_tax = __get_principle(purchase_after_tax, tax_rate);
        var sub_total_before_tax = quantity * purchase_before_tax;
        var tax = __calculate_amount('percentage', tax_rate, purchase_before_tax);

        //Update unit cost price before discount
        var discount_percent = __read_number(row.find('input.inline_discounts'), true);
        var purchase_before_discount = __get_principle(purchase_before_tax, discount_percent, true);
        __write_number(
            row.find('input.purchase_unit_cost_without_discount'),
            purchase_before_discount,
            true
        );

        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(sub_total_after_tax, false, true)
        );
        __write_number(row.find('input.row_subtotal_after_tax_hidden'), sub_total_after_tax, true);

        __write_number(row.find('.purchase_unit_cost'), purchase_before_tax, true);

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(sub_total_before_tax, false, true)
        );
        __write_number(
            row.find('input.row_subtotal_before_tax_hidden'),
            sub_total_before_tax,
            true
        );

        row.find('.purchase_product_unit_tax_text').text(__currency_trans_from_en(tax, true, true));
        __write_number(row.find('input.purchase_product_unit_tax'), tax);

        update_table_total();
        update_grand_total();
    });

    $('#tax_id, #discount_type, #discount_amount, input#shipping_charges, \
        #additional_expense_value_1, #additional_expense_value_2, \
        #additional_expense_value_3, #additional_expense_value_4').change(function() {
        update_grand_total();
    });

    //Purchase table
    purchase_table = $('#purchase_table').DataTable({
        processing: true,
        serverSide: true,
        fixedHeader:false,
        scrollY: "75vh",
        scrollX:        true,
        scrollCollapse: true,
        ajax: {
            url: '/purchases',
            data: function(d) {
                if ($('#purchase_list_filter_location_id').length) {
                    d.location_id = $('#purchase_list_filter_location_id').val();
                }
                if ($('#purchase_list_filter_supplier_id').length) {
                    d.supplier_id = $('#purchase_list_filter_supplier_id').val();
                }
                if ($('#purchase_list_filter_payment_status').length) {
                    d.payment_status = $('#purchase_list_filter_payment_status').val();
                }
                if ($('#purchase_list_filter_status').length) {
                    d.status = $('#purchase_list_filter_status').val();
                }

                var start = '';
                var end = '';
                if ($('#purchase_list_filter_date_range').val()) {
                    start = $('input#purchase_list_filter_date_range')
                        .data('daterangepicker')
                        .startDate.format('YYYY-MM-DD');
                    end = $('input#purchase_list_filter_date_range')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD');
                }
                d.start_date = start;
                d.end_date = end;

                d = __datatable_ajax_callback(d);
            },
        },
        aaSorting: [[1, 'desc']],
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'transaction_date', name: 'transaction_date' },
            { data: 'ref_no', name: 'ref_no' },
            { data: 'location_name', name: 'BS.name' },
            { data: 'name', name: 'contacts.name' },
            { data: 'status', name: 'status' },
            { data: 'payment_status', name: 'payment_status' },
            { data: 'final_total', name: 'final_total' },
            { data: 'payment_due', name: 'payment_due', orderable: false, searchable: false },
            { data: 'added_by', name: 'u.first_name' },
        ],
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#purchase_table'));
        },
        "footerCallback": function ( row, data, start, end, display ) {
            var total_purchase = 0;
            var total_due = 0;
            var total_purchase_return_due = 0;
            for (var r in data){
                total_purchase += $(data[r].final_total).data('orig-value') ? 
                parseFloat($(data[r].final_total).data('orig-value')) : 0;
                var payment_due_obj = $('<div>' + data[r].payment_due + '</div>');
                total_due += payment_due_obj.find('.payment_due').data('orig-value') ? 
                parseFloat(payment_due_obj.find('.payment_due').data('orig-value')) : 0;

                total_purchase_return_due += payment_due_obj.find('.purchase_return').data('orig-value') ? 
                parseFloat(payment_due_obj.find('.purchase_return').data('orig-value')) : 0;
            }

            $('.footer_purchase_total').html(__currency_trans_from_en(total_purchase));
            $('.footer_total_due').html(__currency_trans_from_en(total_due));
            $('.footer_total_purchase_return_due').html(__currency_trans_from_en(total_purchase_return_due));
            $('.footer_status_count').html(__count_status(data, 'status'));
            $('.footer_payment_status_count').html(__count_status(data, 'payment_status'));
        },
        createdRow: function(row, data, dataIndex) {
            $(row)
                .find('td:eq(5)')
                .attr('class', 'clickable_td');
        },
    });

    $(document).on(
        'change',
        '#purchase_list_filter_location_id, \
                    #purchase_list_filter_supplier_id, #purchase_list_filter_payment_status,\
                     #purchase_list_filter_status',
        function() {
            purchase_table.ajax.reload();
        }
    );

    update_table_sr_number();

    $(document).on('change', '.mfg_date', function() {
        var this_date = $(this).val();
        var this_moment = moment(this_date, moment_date_format);
        var expiry_period = parseFloat(
            $(this)
                .closest('td')
                .find('.row_product_expiry')
                .val()
        );
        var expiry_period_type = $(this)
            .closest('td')
            .find('.row_product_expiry_type')
            .val();
        if (this_date) {
            if (expiry_period && expiry_period_type) {
                exp_date = this_moment
                    .add(expiry_period, expiry_period_type)
                    .format(moment_date_format);
                $(this)
                    .closest('td')
                    .find('.exp_date')
                    .datepicker('update', exp_date);
            } else {
                $(this)
                    .closest('td')
                    .find('.exp_date')
                    .datepicker('update', '');
            }
        } else {
            $(this)
                .closest('td')
                .find('.exp_date')
                .datepicker('update', '');
        }
    });

    $('#purchase_entry_table tbody')
        .find('.expiry_datepicker')
        .each(function() {
            $(this).datepicker({
                autoclose: true,
                format: datepicker_date_format,
            });
        });

    $(document).on('change', '.profit_percent', function() {
        var row = $(this).closest('tr');
        var profit_percent = __read_number($(this), true);

        var purchase_unit_cost = __read_number(row.find('input.purchase_unit_cost_after_tax'), true);
        var default_sell_price =
            parseFloat(purchase_unit_cost) +
            __calculate_amount('percentage', profit_percent, purchase_unit_cost);
        var exchange_rate = $('input#exchange_rate').val();
        __write_number(
            row.find('input.default_sell_price'),
            default_sell_price * exchange_rate,
            true
        );
    });

    $(document).on('keyup', '.default_sell_price', function() {
        __write_number($(this), $(this).val());        
        var row = $(this).closest('tr');
        update_inline_profit_percentage(row);
    });

    $(document).on('click', 'a.delete-purchase', function(e) {
        e.preventDefault();
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var href = $(this).attr('href');
                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            purchase_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });

    $('table#purchase_entry_table').on('change', 'select.sub_unit', function() {
        var tr = $(this).closest('tr');
        var base_unit_cost = tr.find('input.base_unit_cost').val();
        var base_unit_selling_price = tr.find('input.base_unit_selling_price').val();

        var multiplier = parseFloat(
            $(this)
                .find(':selected')
                .data('multiplier')
        );

        var unit_sp = base_unit_selling_price * multiplier;
        var unit_cost = base_unit_cost * multiplier;

        var sp_element = tr.find('input.default_sell_price');
        __write_number(sp_element, unit_sp);

        var cp_element = tr.find('input.purchase_unit_cost_without_discount');
        __write_number(cp_element, unit_cost);
        cp_element.change();
    });
    toggle_search();

    // Load form data and purchase cart from session only if not in edit mode
    if ($('form#add_purchase_form').length > 0) {
        loadPurchaseFormDataFromSession();

        if ($('#purchase_entry_table tbody tr').length === 0) {
            var location_id = $('#location_id').val();
            if (location_id) {
                // Location is already selected (from session), load the cart
                loadPurchaseCartFromSession();
            }
        }
    }

    // Save form data when fields change
    $(document).on('change',
        '#supplier_id, #ref_no, #status, input[name="exchange_rate"], input[name="pay_term_number"], #pay_term_type, ' +
        'input[name="cufe"], input[name="custom_field_1"], input[name="custom_field_2"], input[name="custom_field_3"], input[name="custom_field_4"], ' +
        '#discount_type, #discount_amount, #tax_id, #shipping_charges, #shipping_details, ' +
        'input[name="shipping_custom_field_1"], input[name="shipping_custom_field_2"], input[name="shipping_custom_field_3"], ' +
        'input[name="shipping_custom_field_4"], input[name="shipping_custom_field_5"], ' +
        '#additional_expense_key_1, #additional_expense_value_1, #additional_expense_key_2, #additional_expense_value_2, ' +
        '#additional_expense_key_3, #additional_expense_value_3, #additional_expense_key_4, #additional_expense_value_4, ' +
        '#additional_notes, .payment_types_dropdown, .payment-amount',
    function() {
        if ($('form#add_purchase_form').length > 0) {
            savePurchaseFormDataToSession();
        }
    });

    // Also save on blur for text inputs to catch manual typing
    $(document).on('blur',
        '#ref_no, input[name="exchange_rate"], input[name="pay_term_number"], input[name="cufe"], ' +
        'input[name="custom_field_1"], input[name="custom_field_2"], input[name="custom_field_3"], input[name="custom_field_4"], ' +
        '#discount_amount, #shipping_charges, #shipping_details, ' +
        'input[name="shipping_custom_field_1"], input[name="shipping_custom_field_2"], input[name="shipping_custom_field_3"], ' +
        'input[name="shipping_custom_field_4"], input[name="shipping_custom_field_5"], ' +
        '#additional_expense_key_1, #additional_expense_value_1, #additional_expense_key_2, #additional_expense_value_2, ' +
        '#additional_expense_key_3, #additional_expense_value_3, #additional_expense_key_4, #additional_expense_value_4, ' +
        '#additional_notes, .payment-amount',
    function() {
        if ($('form#add_purchase_form').length > 0) {
            savePurchaseFormDataToSession();
        }
    });
});

var global_brand_id = null;
var global_p_category_id = null;
var global_is_clear_local_storage = false;
// Controls whether the next purchase product insertion should go to the top
var should_prepend_purchase_row = false;

function get_purchase_entry_row(product_id, variation_id) {
    if (product_id) {
        var row_count = $('#row_count').val();
        var location_id = $('#location_id').val();
        var supplier_id = $('#supplier_id').val();
        var data = { 
            product_id: product_id, 
            row_count: row_count, 
            variation_id: variation_id,
            location_id: location_id,
            supplier_id: supplier_id
        };

        if ($('#is_purchase_order').length) {
            data.is_purchase_order = true;
        }
        $.ajax({
            method: 'POST',
            url: '/purchases/get_purchase_entry_row',
            dataType: 'html',
            data: data,
            success: function(result) {
                append_purchase_lines(result, row_count);
            },
        });
    }
}

function append_purchase_lines(data, row_count, trigger_change = false) {
    // Capture insertion intent for this specific request and reset the global flag
    var insertAtTop = (typeof should_prepend_purchase_row !== 'undefined' && should_prepend_purchase_row);
    should_prepend_purchase_row = false;

    $(data)
        .find('.purchase_quantity')
        .each(function() {
            row = $(this).closest('tr');

            if (insertAtTop) {
                $('#purchase_entry_table tbody').prepend(
                    update_purchase_entry_row_values(row)
                );
            } else {
                $('#purchase_entry_table tbody').append(
                    update_purchase_entry_row_values(row)
                );
            }
            update_row_price_for_exchange_rate(row);

            update_inline_profit_percentage(row);

            // Update total stock badge for new rows
            var quantity = __read_number(row.find('.purchase_quantity'), true);
            updateTotalStockBadge(row, quantity);

            update_table_total();
            update_grand_total();
            update_table_sr_number();

            //Check if multipler is present then multiply it when a new row is added.
            if(__getUnitMultiplier(row) > 1){
                row.find('select.sub_unit').trigger('change');
            }

            if (trigger_change && row.find('.purchase_unit_cost_without_discount').length) {
                row.find('.purchase_unit_cost_without_discount').trigger('change');
            }
        });
    if ($(data).find('.purchase_quantity').length) {
        $('#row_count').val(
            $(data).find('.purchase_quantity').length + parseInt(row_count)
        );
    }

    // Save cart to session after adding product
    savePurchaseCartToSession();
}

function update_purchase_entry_row_values(row) {
    if (typeof row != 'undefined') {
        var quantity = __read_number(row.find('.purchase_quantity'), true);
        var unit_cost_price = __read_number(row.find('.purchase_unit_cost'), true);
        var row_subtotal_before_tax = quantity * unit_cost_price;

        var tax_rate = parseFloat(
            $('option:selected', row.find('.purchase_line_tax_id')).attr('data-tax_amount')
        );

        var unit_product_tax = __calculate_amount('percentage', tax_rate, unit_cost_price);

        var unit_cost_price_after_tax = unit_cost_price + unit_product_tax;
        var row_subtotal_after_tax = quantity * unit_cost_price_after_tax;

        row.find('.row_subtotal_before_tax').text(
            __currency_trans_from_en(row_subtotal_before_tax, false, true)
        );
        __write_number(row.find('.row_subtotal_before_tax_hidden'), row_subtotal_before_tax, true);
        __write_number(row.find('.purchase_product_unit_tax'), unit_product_tax, true);
        row.find('.purchase_product_unit_tax_text').text(
            __currency_trans_from_en(unit_product_tax, false, true)
        );
        row.find('.purchase_unit_cost_after_tax').text(
            __currency_trans_from_en(unit_cost_price_after_tax, true)
        );
        row.find('.row_subtotal_after_tax').text(
            __currency_trans_from_en(row_subtotal_after_tax, false, true)
        );
        __write_number(row.find('.row_subtotal_after_tax_hidden'), row_subtotal_after_tax, true);

        row.find('.expiry_datepicker').each(function() {
            $(this).datepicker({
                autoclose: true,
                format: datepicker_date_format,
            });
        });
        return row;
    }
}

function update_row_price_for_exchange_rate(row) {
    var exchange_rate = $('input#exchange_rate').val();

    if (exchange_rate == 1) {
        return true;
    }

    var purchase_unit_cost_without_discount =
        __read_number(row.find('.purchase_unit_cost_without_discount'), true) / exchange_rate;
    __write_number(
        row.find('.purchase_unit_cost_without_discount'),
        purchase_unit_cost_without_discount,
        true
    );

    var purchase_unit_cost = __read_number(row.find('.purchase_unit_cost'), true) / exchange_rate;
    __write_number(row.find('.purchase_unit_cost'), purchase_unit_cost, true);

    var row_subtotal_before_tax_hidden =
        __read_number(row.find('.row_subtotal_before_tax_hidden'), true) / exchange_rate;
    row.find('.row_subtotal_before_tax').text(
        __currency_trans_from_en(row_subtotal_before_tax_hidden, false, true)
    );
    __write_number(
        row.find('input.row_subtotal_before_tax_hidden'),
        row_subtotal_before_tax_hidden,
        true
    );

    var purchase_product_unit_tax =
        __read_number(row.find('.purchase_product_unit_tax'), true) / exchange_rate;
    __write_number(row.find('input.purchase_product_unit_tax'), purchase_product_unit_tax, true);
    row.find('.purchase_product_unit_tax_text').text(
        __currency_trans_from_en(purchase_product_unit_tax, false, true)
    );

    var purchase_unit_cost_after_tax =
        __read_number(row.find('.purchase_unit_cost_after_tax'), true) / exchange_rate;
    __write_number(
        row.find('input.purchase_unit_cost_after_tax'),
        purchase_unit_cost_after_tax,
        true
    );

    var row_subtotal_after_tax_hidden =
        __read_number(row.find('.row_subtotal_after_tax_hidden'), true) / exchange_rate;
    __write_number(
        row.find('input.row_subtotal_after_tax_hidden'),
        row_subtotal_after_tax_hidden,
        true
    );
    row.find('.row_subtotal_after_tax').text(
        __currency_trans_from_en(row_subtotal_after_tax_hidden, false, true)
    );
}

function iraqi_dinnar_selling_price_adjustment(row) {
    var default_sell_price = __read_number(row.find('input.default_sell_price'), true);

    //Adjsustment
    var remaining = default_sell_price % 250;
    if (remaining >= 125) {
        default_sell_price += 250 - remaining;
    } else {
        default_sell_price -= remaining;
    }

    __write_number(row.find('input.default_sell_price'), default_sell_price, true);

    update_inline_profit_percentage(row);
}

function update_inline_profit_percentage(row) {
    //Update Profit percentage
    var default_sell_price = __read_number(row.find('input.default_sell_price'), true);
    var exchange_rate = $('input#exchange_rate').val();
    default_sell_price_in_base_currency = default_sell_price / parseFloat(exchange_rate);

    var purchase_after_tax = __read_number(row.find('input.purchase_unit_cost_after_tax'), true);
    var profit_percent = __get_rate(purchase_after_tax, default_sell_price_in_base_currency);
    __write_number(row.find('input.profit_percent'), profit_percent, true);
}

function update_table_total() {
    var total_quantity = 0;
    var total_st_before_tax = 0;
    var total_subtotal = 0;

    $('#purchase_entry_table tbody')
        .find('tr')
        .each(function() {
            total_quantity += __read_number($(this).find('.purchase_quantity'), true);
            total_st_before_tax += __read_number(
                $(this).find('.row_subtotal_before_tax_hidden'),
                true
            );
            total_subtotal += __read_number($(this).find('.row_subtotal_after_tax_hidden'), true);
        });

    $('#total_quantity').text(__number_f(total_quantity, false));
    $('#total_st_before_tax').text(__currency_trans_from_en(total_st_before_tax, true, true));
    __write_number($('input#st_before_tax_input'), total_st_before_tax, true);

    $('#total_subtotal').text(__currency_trans_from_en(total_subtotal, true, true));
    __write_number($('input#total_subtotal_input'), total_subtotal, true);
}

function update_grand_total() {
    var st_before_tax = __read_number($('input#st_before_tax_input'), true);
    var total_subtotal = __read_number($('input#total_subtotal_input'), true);

    //Calculate Discount
    var discount_type = $('select#discount_type').val();
    var discount_amount = __read_number($('input#discount_amount'), true);
    var discount = __calculate_amount(discount_type, discount_amount, total_subtotal);
    $('#discount_calculated_amount').text(__currency_trans_from_en(discount, true, true));

    //Calculate Tax
    var tax_rate = parseFloat($('option:selected', $('#tax_id')).data('tax_amount'));
    var tax = __calculate_amount('percentage', tax_rate, total_subtotal - discount);
    __write_number($('input#tax_amount'), tax);
    $('#tax_calculated_amount').text(__currency_trans_from_en(tax, true, true));

    //Calculate shipping
    var shipping_charges = __read_number($('input#shipping_charges'), true);

    //calculate additional expenses
    var additional_expense_1 = __read_number($('input#additional_expense_value_1'), true);
    var additional_expense_2 = __read_number($('input#additional_expense_value_2'), true);
    var additional_expense_3 = __read_number($('input#additional_expense_value_3'), true);
    var additional_expense_4 = __read_number($('input#additional_expense_value_4'), true);

    //Calculate Final total
    grand_total = total_subtotal - discount + tax + shipping_charges + 
    additional_expense_1 + additional_expense_2 + additional_expense_3 + additional_expense_4;

    __write_number($('input#grand_total_hidden'), grand_total, true);

    var payment = __read_number($('input.payment-amount'), true);

    var due = grand_total - payment;
    // __write_number($('input.payment-amount'), grand_total, true);

    $('#grand_total').text(__currency_trans_from_en(grand_total, true, true));

    $('#payment_due').text(__currency_trans_from_en(due, true, true));

    //__currency_convert_recursively($(document));
}
$(document).on('keyup', 'input.payment-amount', function() {
    __write_number($(this), $(this).val(), true);
});

$(document).on('change', 'input.payment-amount', function() {
    var payment = __read_number($(this), true);
    var grand_total = __read_number($('input#grand_total_hidden'), true);
    var bal = grand_total - payment;
    $('#payment_due').text(__currency_trans_from_en(bal, true, true));
});

function update_table_sr_number() {
    var sr_number = 1;
    $('table#purchase_entry_table tbody')
        .find('.sr_number')
        .each(function() {
            $(this).text(sr_number);
            sr_number++;
        });
}

$(document).on('click', 'button#submit_purchase_form', function(e) {
    e.preventDefault();

    //Check if product is present or not.
    if ($('table#purchase_entry_table tbody tr').length <= 0) {
        toastr.warning(LANG.no_products_added);
        $('input#search_product').select();
        return false;
    }

    $('form#add_purchase_form').validate({
        rules: {
            ref_no: {
                remote: {
                    url: '/purchases/check_ref_number',
                    type: 'post',
                    data: {
                        ref_no: function() {
                            return $('#ref_no').val();
                        },
                        contact_id: function() {
                            return $('#supplier_id').val();
                        },
                        purchase_id: function() {
                            if ($('#purchase_id').length > 0) {
                                return $('#purchase_id').val();
                            } else {
                                return '';
                            }
                        },
                    },
                },
            },
        },
        messages: {
            ref_no: {
                remote: LANG.ref_no_already_exists,
            },
        },
    });
    var payment_types_dropdown = $('.payment_types_dropdown')
    var payment_type = payment_types_dropdown.val();
    var payment_row = payment_types_dropdown.closest('.payment_row');
    amount_element = payment_row.find('.payment-amount');
    account_dropdown = payment_row.find('.account-dropdown');
    if (payment_type == 'advance') {
        max_value = $('#advance_balance').val();
        msg = $('#advance_balance').data('error-msg');
        amount_element.rules('add', {
            'max-value': max_value,
            messages: {
                'max-value': msg,
            },
        });
        if (account_dropdown) {
            account_dropdown.prop('disabled', true);
        }
        
    } else {
        amount_element.rules("remove", "max-value");
        if (account_dropdown) {
            account_dropdown.prop('disabled', false); 
        }    
    }

    if ($('.enable_cash_denomination_for_payment_methods').length) {
        var payment_row = $('.enable_cash_denomination_for_payment_methods').closest('.payment_row');
        var is_valid = true;
        var payment_type = payment_row.find('.payment_types_dropdown').val();
        var denomination_for_payment_types = JSON.parse($('.enable_cash_denomination_for_payment_methods').val());
        if (denomination_for_payment_types.includes(payment_type) && payment_row.find('.is_strict').length && payment_row.find('.is_strict').val() === '1' ) {
            var payment_amount = __read_number(payment_row.find('.payment-amount'));
            var total_denomination = payment_row.find('input.denomination_total_amount').val();
            if (payment_amount != total_denomination ) {
                is_valid = false;
            }
        }

        if (!is_valid) {
            payment_row.find('.cash_denomination_error').removeClass('hide');
            toastr.error(payment_row.find('.cash_denomination_error').text());
            e.preventDefault();
            return false;
        } else {
            payment_row.find('.cash_denomination_error').addClass('hide');
        }
    }

    if ($('form#add_purchase_form').valid()) {
        $(this).attr('disabled', true);
        $('form#add_purchase_form').submit();
    }
});

function toggle_search() {
    if ($('#location_id').val()) {
        $('#search_product').removeAttr('disabled');
        $('#search_product').focus();
    } else {
        $('#search_product').attr('disabled', true);
    }
}

$(document).on('change', '#location_id', function() {
    get_purchase_requisitions();
    toggle_search();
    $('#purchase_entry_table tbody').html('');
    update_table_total();
    update_grand_total();
    update_table_sr_number();

    // Save selected location to session and clear cart for old location
    var new_location_id = $(this).val();
    if (new_location_id) {
        // Save the new location
        savePurchaseCartToSession();
    }
});

$(document).on('shown.bs.modal', '.quick_add_product_modal', function(){
    var selected_location = $('#location_id').val();
    if (selected_location) {
        $('.quick_add_product_modal').find('#product_locations').val([selected_location]).trigger("change");
    }
});

function set_supplier_address(data) {
    var address = [];
    if (data.supplier_business_name) {
        address.push(data.supplier_business_name);
    }
    if (data.business_name) {
        address.push(data.business_name);
    }
    if (data.name) {
        address.push('<br>' + data.name);
    }
    if (data.text) {
        address.push('<br>' + data.text);
    }
    if (data.address_line_1) {
        address.push('<br>' + data.address_line_1);
    }
    if (data.address_line_2) {
        address.push('<br>' + data.address_line_2);
    }
    if (data.city) {
        address.push('<br>' + data.city);
    }
    if (data.state) {
        address.push(data.state);
    }
    if (data.country) {
        address.push(data.country);
    }
    if (data.zip_code) {
        address.push('<br>' + data.zip_code);
    }
    var supplier_address = address.join(', ');
    $('#supplier_address_div').html(supplier_address);
}

$(document).on('change', '#supplier_id', function(){
    if ($('#purchase_order_ids').length) {
        contact_id = $(this).val();
        $.ajax({
            url: '/get-purchase-orders/' + contact_id,
            dataType: 'json',
            success: function(data) {
                $('#purchase_order_ids').select2('destroy').empty().select2({data: data});
                $('#purchase_entry_table tbody').find('tr').each( function(){
                    if (typeof($(this).data('purchase_order_id')) !== 'undefined') {
                        $(this).remove();
                    }
                });
            },
        });
    }
});

$("#purchase_order_ids").on("select2:select", function (e) {
    var purchase_order_id = e.params.data.id;
    var row_count = $('#row_count').val();
    $.ajax({
        url: '/get-purchase-order-lines/' + purchase_order_id + '?row_count=' + row_count,
        dataType: 'json',
        success: function(data) {
            set_po_values(data.po);
            should_prepend_purchase_row = true;
            append_purchase_lines(data.html, row_count);
        },
    });

});

$("#purchase_order_ids").on("select2:unselect", function (e) {
    var purchase_order_id = e.params.data.id;
    $('#purchase_entry_table tbody').find('tr').each( function(){
        if (typeof($(this).data('purchase_order_id')) !== 'undefined' 
            && $(this).data('purchase_order_id') == purchase_order_id) {
            $(this).remove();
        }
    });
});

function set_po_values(po) {
    $('#shipping_details').val(po.shipping_details);
    $('#shipping_charges').val( __number_f(po.shipping_charges));
    if ($('#shipping_custom_field_1').length) {
        $('#shipping_custom_field_1').val(po.shipping_custom_field_1);
    }
    if ($('#shipping_custom_field_2').length) {
        $('#shipping_custom_field_2').val(po.shipping_custom_field_2);
    }
    if ($('#shipping_custom_field_3').length) {
        $('#shipping_custom_field_3').val(po.shipping_custom_field_3);
    }
    if ($('#shipping_custom_field_4').length) {
        $('#shipping_custom_field_4').val(po.shipping_custom_field_4);
    }
    if ($('#shipping_custom_field_5').length) {
        $('#shipping_custom_field_5').val(po.shipping_custom_field_5);
    }

    $('#additional_expense_key_1').val(po.additional_expense_key_1);
    $('#additional_expense_key_2').val(po.additional_expense_key_2);
    $('#additional_expense_key_3').val(po.additional_expense_key_3);
    $('#additional_expense_key_4').val(po.additional_expense_key_4);

    $('#additional_expense_value_1').val( __number_f(po.additional_expense_value_1));
    $('#additional_expense_value_2').val( __number_f(po.additional_expense_value_2));
    $('#additional_expense_value_3').val( __number_f(po.additional_expense_value_3));
    $('#additional_expense_value_4').val( __number_f(po.additional_expense_value_4));
}

if ($("div#import_product_dz").length) {
    $("div#import_product_dz").dropzone({
        url: base_path + '/import-purchase-products',
        paramName: 'file',
        autoProcessQueue: false,
        addRemoveLinks: true,
        uploadMultiple: false,
        maxFiles:1,
        init: function() {
            this.on("addedfile", function(file) {
                if ($('#location_id').val() == '') {
                    this.removeFile(file);
                    toastr.error('select location first');
                }
            });
            this.on("maxfilesexceeded", function(file) {
                this.removeAllFiles();
                this.addFile(file);
            });
            this.on("sending", function(file, xhr, formData){
                formData.append("location_id", $('#location_id').val());
                formData.append("row_count", $('#row_count').val());
            });
        },   
        acceptedFiles: '.csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(file, response) {
            if (response.success) {
                toastr.success(response.msg);
                var row_count = $('#row_count').val();
                should_prepend_purchase_row = true;
                append_purchase_lines(response.html, row_count, true);

                this.removeAllFiles();

                $('#import_purchase_products_modal').modal('hide');
            } else {
                toastr.error(response.msg);
            }
        },
    });
}

$(document).on('click', '#import_purchase_products', function(){
    var productDz = Dropzone.forElement("#import_product_dz");
    productDz.processQueue();
})

function submitQuickAddPurchaseContactForm(form) {
    var data = $(form).serialize();
    $.ajax({
        method: 'POST',
        url: $(form).attr('action'),
        dataType: 'json',
        data: data,
        beforeSend: function(xhr) {
            __disable_submit_button($(form).find('button[type="submit"]'));
        },
        success: function(result) {
            if (result.success == true) {
                var name = result.data.name;

                if (result.data.supplier_business_name) {
                    name += result.data.supplier_business_name;
                }
                $('select#supplier_id').append(
                    $('<option>', { value: result.data.id, text: name })
                );
                $('select#supplier_id')
                    .val(result.data.id)
                    .trigger('change');
                $('div.contact_modal').modal('hide');
                set_supplier_address(result.data);
                toastr.success(result.msg);
            } else {
                toastr.error(result.msg);
            }
        },
    });
}

function get_purchase_requisitions() {
    var location_id = $('#location_id').val();
    if ($('#purchase_requisition_ids').length) {
        if (location_id !== '') {
            $.ajax({
                url: '/get-purchase-requisitions/' + location_id,
                dataType: 'json',
                success: function(data) {
                    $('#purchase_requisition_ids').select2('destroy').empty().select2({data: data});
                    $('#purchase_entry_table tbody').find('tr').each( function(){
                        if (typeof($(this).data('purchase_requisition_id')) !== 'undefined') {
                            $(this).remove();
                        }
                    });
                },
            });
        }
    }
}

$("#purchase_requisition_ids").on("select2:select", function (e) {
    var purchase_requisition_id = e.params.data.id;
    var row_count = $('#row_count').val();
    $.ajax({
        url: '/get-purchase-requisition-lines/' + purchase_requisition_id + '?row_count=' + row_count,
        dataType: 'json',
        success: function(data) {
            should_prepend_purchase_row = true;
            append_purchase_lines(data.html, row_count);
        },
    });

});

$("#purchase_requisition_ids").on("select2:unselect", function (e) {
    var purchase_requisition_id = e.params.data.id;
    $('#purchase_entry_table tbody').find('tr').each( function(){
        if (typeof($(this).data('purchase_requisition_id')) !== 'undefined' 
            && $(this).data('purchase_requisition_id') == purchase_requisition_id) {
            $(this).remove();
        }
    });
});

// Function to update total stock badge
function updateTotalStockBadge(row, purchaseQuantity) {
    var badge = row.find('.total-stock-badge');
    if (badge.length > 0) {
        var currentStock = parseFloat(badge.data('current-stock')) || 0;
        var totalQuantity = currentStock + purchaseQuantity;
        
        // Update the badge text
        badge.find('.total-quantity').text(__number_f(totalQuantity, false));
        
        // Change badge color based on total quantity
        if (totalQuantity > 0) {
            badge.css('background-color', '#28a745'); // Green for positive
        } else {
            badge.css('background-color', '#dc3545'); // Red for zero or negative
        }
        
        // Ensure proper styling is maintained
        badge.css({
            'display': 'flex',
            'align-items': 'center',
            'justify-content': 'center',
            'width': '60px',
            'height': '60px',
            'border-radius': '50%',
            'font-weight': 'bold',
            'font-size': '12px',
            'min-width': '60px',
            'max-width': '60px',
            'text-align': 'center',
            'line-height': '1'
        });
        
        // Ensure text styling is maintained - allow text to wrap and show full quantity
        badge.find('.total-quantity').css({
            'display': 'block',
            'width': '100%',
            'text-align': 'center',
            'word-wrap': 'break-word',
            'hyphens': 'auto'
        });
    }
}

// Initialize total stock badges on page load
$(document).ready(function() {
    $('#purchase_entry_table tbody tr').each(function() {
        var row = $(this);
        var quantityInput = row.find('.purchase_quantity');
        if (quantityInput.length > 0) {
            var quantity = __read_number(quantityInput, true);
            updateTotalStockBadge(row, quantity);
        }
    });
});
