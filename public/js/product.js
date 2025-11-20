//This file contains all functions used products tab

$(document).ready(function() {
    // When unit changes on product form, reload sub units and rebuild price inputs (create/edit)
    $(document).on('change', '#unit_id', function () {
        var unit_id = $(this).val();
        var subUnitSelect = $('#sub_unit_ids');
        if (subUnitSelect.length > 0) {
            var url = subUnitSelect.data('get-sub-units-url');
            $.ajax({
                method: 'GET',
                url: url,
                data: { unit_id: unit_id },
                dataType: 'html',
                success: function (result) {
                    subUnitSelect.html(result).trigger('change');
                }
            });
        }
    });

     // Build sub unit price inputs when sub units selection changes (append new, keep existing values; default new value to 0)
    $(document).on('change', '#sub_unit_ids', function () {
        var selected = $(this).val() || [];
        var wrapper = $('#sub_unit_prices_wrapper');
        if (!wrapper.length) return;

        // Show/hide container
        if (selected.length === 0) {
            wrapper.closest('.form-group').hide();
        } else {
            wrapper.closest('.form-group').show();
        }

        // Prefilled prices coming from backend
        var prefilled = {};
        var prefilledPurchase = {};
        var prefilledSell = {};
        var prefilledMargins = {};
        
        try { 
            prefilledPurchase = JSON.parse(wrapper.attr('data-prices') || '{}');
            prefilledSell = JSON.parse(wrapper.attr('data-sell-prices') || '{}');
            prefilledMargins = JSON.parse(wrapper.attr('data-margins') || '{}');
        } catch (e) { 
            prefilledPurchase = {};
            prefilledSell = {};
            prefilledMargins = {};
        }

        // Append inputs that don't exist yet for newly selected sub-units
        for (var i = 0; i < selected.length; i++) {
            var id = selected[i];
            if (wrapper.find('.sub-unit-price-input[data-unit-id="' + id + '"]').length === 0) {
                var option = $(this).find('option[value="' + id + '"]');
                var label = option.length ? option.text() : ('Unit ' + id);
                var purchaseVal = (prefilledPurchase[id] !== undefined && prefilledPurchase[id] !== null && prefilledPurchase[id] !== '') ? __number_f(prefilledPurchase[id], false) : '';
                var sellVal = (prefilledSell[id] !== undefined && prefilledSell[id] !== null && prefilledSell[id] !== '') ? __number_f(prefilledSell[id], false) : '';
                var marginVal = (prefilledMargins[id] !== undefined && prefilledMargins[id] !== null && prefilledMargins[id] !== '') ? __number_f(prefilledMargins[id], false) : '';
                
                var html = '<div class="col-sm-12 tw-mb-3 sub-unit-price-input" data-unit-id="' + id + '">'
                    + '<div class="row">'
                    + '<div class="col-sm-12"><h5>' + label + '</h5></div>'
                    + '<div class="col-sm-4">'
                    + '<label>Precio de Compra:</label>'
                    + '<input name="sub_unit_prices[' + id + ']" type="text" class="form-control input_number sub-unit-purchase-price" data-unit-id="' + id + '" value="' + purchaseVal + '" placeholder="Precio de Compra">'
                    + '</div>'
                    + '<div class="col-sm-4">'
                    + '<label>Precio de Venta:</label>'
                    + '<input name="sub_unit_sell_prices[' + id + ']" type="text" class="form-control input_number sub-unit-sell-price" data-unit-id="' + id + '" value="' + sellVal + '" placeholder="Precio de Venta">'
                    + '</div>'
                    + '<div class="col-sm-4">'
                    + '<label>Utilidad %:</label>'
                    + '<input name="sub_unit_margins[' + id + ']" type="text" class="form-control input_number sub-unit-margin" data-unit-id="' + id + '" value="' + marginVal + '" placeholder="Utilidad %">'
                    + '</div>'
                    + '</div>'
                    + '</div>';
                wrapper.append(html);
            }
        }

        // Remove inputs for units that are no longer selected
        wrapper.find('.sub-unit-price-input').each(function () {
            var id = ($(this).data('unit-id') || '').toString();
            if (selected.indexOf(id) === -1) {
                $(this).remove();
            }
        });
    });

    // On load (edit page), build inputs for already selected sub-units
    if ($('#sub_unit_ids').length) {
        $('#sub_unit_ids').trigger('change');
    }

    // Sub-unit pricing calculations
    $(document).on('keyup', '.sub-unit-purchase-price', function() {
        __write_number($(this), $(this).val());
        var unitId = $(this).data('unit-id');
        var purchasePrice = __read_number($(this));
        var marginInput = $('.sub-unit-margin[data-unit-id="' + unitId + '"]');
        var sellPriceInput = $('.sub-unit-sell-price[data-unit-id="' + unitId + '"]');
        
        var margin = __read_number(marginInput);
        if (purchasePrice > 0 && margin > 0) {
            var sellPrice = purchasePrice + (purchasePrice * margin / 100);
            __write_number(sellPriceInput, sellPrice);
        }
    });

    $(document).on('keyup', '.sub-unit-margin', function() {
        var unitId = $(this).data('unit-id');
        var margin = __read_number($(this));
        var purchasePriceInput = $('.sub-unit-purchase-price[data-unit-id="' + unitId + '"]');
        var sellPriceInput = $('.sub-unit-sell-price[data-unit-id="' + unitId + '"]');
        
        var purchasePrice = __read_number(purchasePriceInput);
        if (margin > 0 && purchasePrice > 0) {
            var sellPrice = purchasePrice + (purchasePrice * margin / 100);
            __write_number(sellPriceInput, sellPrice);
        }
    });

    $(document).on('keyup', '.sub-unit-sell-price', function() {
        __write_number($(this), $(this).val());
        var unitId = $(this).data('unit-id');
        var sellPrice = __read_number($(this));
        var purchasePriceInput = $('.sub-unit-purchase-price[data-unit-id="' + unitId + '"]');
        var marginInput = $('.sub-unit-margin[data-unit-id="' + unitId + '"]');
        
        var purchasePrice = __read_number(purchasePriceInput);
        if (sellPrice > 0 && purchasePrice > 0) {
            var margin = ((sellPrice - purchasePrice) / purchasePrice) * 100;
            __write_number(marginInput, margin);
        }
    });
    $(document).on('ifChecked', 'input#enable_stock', function() {
        $('div#alert_quantity_div').show();
        $('div#quick_product_opening_stock_div').show();

        //Enable expiry selection
        if ($('#expiry_period_type').length) {
            $('#expiry_period_type').removeAttr('disabled');
        }

        if ($('#opening_stock_button').length) {
            $('#opening_stock_button').removeAttr('disabled');
        }
    });
    $(document).on('ifUnchecked', 'input#enable_stock', function() {
        $('div#alert_quantity_div').hide();
        $('div#quick_product_opening_stock_div').hide();
        $('input#alert_quantity').val(0);

        //Disable expiry selection
        if ($('#expiry_period_type').length) {
            $('#expiry_period_type')
                .val('')
                .change();
            $('#expiry_period_type').attr('disabled', true);
        }
        if ($('#opening_stock_button').length) {
            $('#opening_stock_button').attr('disabled', true);
        }
    });

    //Start For product type single

    //If purchase price exc tax is changed
    $(document).on('keyup', 'input#single_dpp', function(e) {
        __write_number($(this), $(this).val());
        var purchase_exc_tax = __read_number($('input#single_dpp'));
        purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var purchase_inc_tax = __add_percent(purchase_exc_tax, tax_rate);
        __write_number($('input#single_dpp_inc_tax'), purchase_inc_tax);

        var profit_percent = __read_number($('#profit_percent'));
        var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        __write_number($('input#single_dsp'), selling_price);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
    });

    //If tax rate is changed
    $(document).on('change', 'select#tax', function() {
        if ($('select#type').val() == 'single') {
            var purchase_exc_tax = __read_number($('input#single_dpp'));
            purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

            var tax_rate = $('select#tax')
                .find(':selected')
                .data('rate');
            tax_rate = tax_rate == undefined ? 0 : tax_rate;

            var purchase_inc_tax = __add_percent(purchase_exc_tax, tax_rate);
            __write_number($('input#single_dpp_inc_tax'), purchase_inc_tax);

            var selling_price = __read_number($('input#single_dsp'));
            var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
            __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
        }
    });

    //If purchase price inc tax is changed
    $(document).on('keyup', 'input#single_dpp_inc_tax', function(e) {
        __write_number($(this), $(this).val());

        var purchase_inc_tax = __read_number($('input#single_dpp_inc_tax'));
        purchase_inc_tax = purchase_inc_tax == undefined ? 0 : purchase_inc_tax;

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var purchase_exc_tax = __get_principle(purchase_inc_tax, tax_rate);
        __write_number($('input#single_dpp'), purchase_exc_tax);
        $('input#single_dpp').change();

        var profit_percent = __read_number($('#profit_percent'));
        profit_percent = profit_percent == undefined ? 0 : profit_percent;
        var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        __write_number($('input#single_dsp'), selling_price);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input#profit_percent', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var purchase_inc_tax = __read_number($('input#single_dpp_inc_tax'));
        purchase_inc_tax = purchase_inc_tax == undefined ? 0 : purchase_inc_tax;

        var purchase_exc_tax = __read_number($('input#single_dpp'));
        purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

        var profit_percent = __read_number($('input#profit_percent'));
        var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        __write_number($('input#single_dsp'), selling_price);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input#single_dsp', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var selling_price = __read_number($('input#single_dsp'));
        var purchase_exc_tax = __read_number($('input#single_dpp'));
        var profit_percent = __read_number($('input#profit_percent'));

        //if purchase price not set
        if (purchase_exc_tax == 0) {
            profit_percent = 0;
        } else {
            profit_percent = __get_rate(purchase_exc_tax, selling_price);
        }

        __write_number($('input#profit_percent'), profit_percent);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('keyup', 'input#single_dsp_inc_tax', function(e) {
        __write_number($(this), $(this).val());
        
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;
        var selling_price_inc_tax = __read_number($('input#single_dsp_inc_tax'));

        var selling_price = __get_principle(selling_price_inc_tax, tax_rate);
        __write_number($('input#single_dsp'), selling_price);
        var purchase_exc_tax = __read_number($('input#single_dpp'));
        var profit_percent = __read_number($('input#profit_percent'));

        //if purchase price not set
        if (purchase_exc_tax == 0) {
            profit_percent = 0;
        } else {
            profit_percent = __get_rate(purchase_exc_tax, selling_price);
        }

        __write_number($('input#profit_percent'), profit_percent);
    });

    if ($('#product_add_form').length) {
        $('form#product_add_form').validate({
            rules: {
                sku: {
                    remote: {
                        url: '/products/check_product_sku',
                        type: 'post',
                        data: {
                            sku: function() {
                                return $('#sku').val();
                            },
                            product_id: function() {
                                if ($('#product_id').length > 0) {
                                    return $('#product_id').val();
                                } else {
                                    return '';
                                }
                            },
                        },
                    },
                },
                expiry_period: {
                    required: {
                        depends: function(element) {
                            return (
                                $('#expiry_period_type')
                                    .val()
                                    .trim() != ''
                            );
                        },
                    },
                },
            },
            messages: {
                sku: {
                    remote: LANG.sku_already_exists,
                },
            },
        });
    }

    $(document).on('click', '.submit_product_form', function(e) {
        e.preventDefault();

        var is_valid_product_form = true;

        var variation_skus = [];

        var submit_type  = $(this).attr('value');

        $('#product_form_part').find('.input_sub_sku').each( function(){
            var element = $(this);
            var row_variation_id = '';
            if ($(this).closest('tr').find('.row_variation_id')) {
                row_variation_id = $(this).closest('tr').find('.row_variation_id').val();
            }

            variation_skus.push({sku: element.val(), variation_id: row_variation_id});
            
        });

        if (variation_skus.length > 0) {
            $.ajax({
                method: 'post',
                url: '/products/validate_variation_skus',
                data: { skus: variation_skus},
                success: function(result) {
                    if (result.success == true) {
                        $('#submit_type').val(submit_type);
                        if ($('form#product_add_form').valid()) {
                            $('form#product_add_form').submit();
                        }
                    } else {
                        toastr.error(__translate('skus_already_exists', {sku: result.sku}));
                        return false;
                    }
                },
            });
        } else {
            $('#submit_type').val(submit_type);
            if ($('form#product_add_form').valid()) {
                $('form#product_add_form').submit();
            }
        }
        
    });
    //End for product type single

    //Start for product type Variable
    //If purchase price exc tax is changed
    $(document).on('change', 'input.variable_dpp', function(e) {
        var tr_obj = $(this).closest('tr');

        var purchase_exc_tax = __read_number($(this));
        purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var purchase_inc_tax = __add_percent(purchase_exc_tax, tax_rate);
        __write_number(tr_obj.find('input.variable_dpp_inc_tax'), purchase_inc_tax);

        var profit_percent = __read_number(tr_obj.find('input.variable_profit_percent'));
        var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        __write_number(tr_obj.find('input.variable_dsp'), selling_price);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp_inc_tax'), selling_price_inc_tax);
    });

    //If purchase price inc tax is changed
    $(document).on('change', 'input.variable_dpp_inc_tax', function(e) {
        var tr_obj = $(this).closest('tr');

        var purchase_inc_tax = __read_number($(this));
        purchase_inc_tax = purchase_inc_tax == undefined ? 0 : purchase_inc_tax;

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var purchase_exc_tax = __get_principle(purchase_inc_tax, tax_rate);
        __write_number(tr_obj.find('input.variable_dpp'), purchase_exc_tax);

        var profit_percent = __read_number(tr_obj.find('input.variable_profit_percent'));
        var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        __write_number(tr_obj.find('input.variable_dsp'), selling_price);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input.variable_profit_percent', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var tr_obj = $(this).closest('tr');
        var profit_percent = __read_number($(this));

        var purchase_exc_tax = __read_number(tr_obj.find('input.variable_dpp'));
        purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

        var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        __write_number(tr_obj.find('input.variable_dsp'), selling_price);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input.variable_dsp', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var tr_obj = $(this).closest('tr');
        var selling_price = __read_number($(this));
        var purchase_exc_tax = __read_number(tr_obj.find('input.variable_dpp'));

        var profit_percent = __read_number(tr_obj.find('input.variable_profit_percent'));

        //if purchase price not set
        if (purchase_exc_tax == 0) {
            profit_percent = 0;
        } else {
            profit_percent = __get_rate(purchase_exc_tax, selling_price);
        }

        __write_number(tr_obj.find('input.variable_profit_percent'), profit_percent);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp_inc_tax'), selling_price_inc_tax);
    });
    $(document).on('change', 'input.variable_dsp_inc_tax', function(e) {
        var tr_obj = $(this).closest('tr');
        var selling_price_inc_tax = __read_number($(this));

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var selling_price = __get_principle(selling_price_inc_tax, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp'), selling_price);

        var purchase_exc_tax = __read_number(tr_obj.find('input.variable_dpp'));
        var profit_percent = __read_number(tr_obj.find('input.variable_profit_percent'));
        //if purchase price not set
        if (purchase_exc_tax == 0) {
            profit_percent = 0;
        } else {
            profit_percent = __get_rate(purchase_exc_tax, selling_price);
        }

        __write_number(tr_obj.find('input.variable_profit_percent'), profit_percent);
    });

    $(document).on('click', '.add_variation_value_row', function() {
        var variation_row_index = $(this)
            .closest('.variation_row')
            .find('.row_index')
            .val();
        var variation_value_row_index = $(this)
            .closest('table')
            .find('tr:last .variation_row_index')
            .val();

        if (
            $(this)
                .closest('.variation_row')
                .find('.row_edit').length >= 1
        ) {
            var row_type = 'edit';
        } else {
            var row_type = 'add';
        }

        var table = $(this).closest('table');

        $.ajax({
            method: 'GET',
            url: '/products/get_variation_value_row',
            data: {
                variation_row_index: variation_row_index,
                value_index: variation_value_row_index,
                row_type: row_type,
            },
            dataType: 'html',
            success: function(result) {
                if (result) {
                    table.append(result);
                    toggle_dsp_input();
                }
            },
        });
    });
    $(document).on('change', '.variation_template_values', function() {
        var tr_obj = $(this).closest('tr');
        var val = $(this).val();
        tr_obj.find('.variation_value_row').each(function(){
            if(val.includes($(this).attr('data-variation_value_id'))) {
                $(this).removeClass('hide');
                $(this).find('.is_variation_value_hidden').val(0);
            } else {
                $(this).addClass('hide');
                $(this).find('.is_variation_value_hidden').val(1);
            }
        })
    });
    $(document).on('change', '.variation_template', function() {
        tr_obj = $(this).closest('tr');

        if ($(this).val() !== '') {
            tr_obj.find('input.variation_name').val(
                $(this)
                    .find('option:selected')
                    .text()
            );

            var template_id = $(this).val();
            var row_index = $(this)
                .closest('tr')
                .find('.row_index')
                .val();
            $.ajax({
                method: 'POST',
                url: '/products/get_variation_template',
                dataType: 'json',
                data: { template_id: template_id, row_index: row_index },
                success: function(result) {
                    if (result) {
                        if(result.values.length > 0) {
                            tr_obj.find('.variation_template_values').select2();
                            tr_obj.find('.variation_template_values').empty();
                            tr_obj.find('.variation_template_values').select2({data: result.values, closeOnSelect: false});
                            tr_obj.find('.variation_template_values_div').removeClass('hide');
                            tr_obj.find('.variation_template_values').select2('open');
                        } else {
                            tr_obj.find('.variation_template_values_div').addClass('hide');
                        }
                        tr_obj
                            .find('table.variation_value_table')
                            .find('tbody')
                            .html(result.html);

                        toggle_dsp_input();
                    }
                },
            });
        }
    });

    $(document).on('click','.delete_complete_row', function(){
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                $(this)
                .closest('.variation_row')
                .remove();
            }
        });
    });

    $(document).on('click', '.remove_variation_value_row', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var count = $(this)
                    .closest('table')
                    .find('.remove_variation_value_row').length;
                if (count === 1) {
                    $(this)
                        .closest('.variation_row')
                        .remove();
                } else {
                    $(this)
                        .closest('tr')
                        .remove();
                }
            }
        });
    });

    //If tax rate is changed
    $(document).on('change', 'select#tax', function() {
        if ($('select#type').val() == 'variable') {
            var tax_rate = $('select#tax')
                .find(':selected')
                .data('rate');
            tax_rate = tax_rate == undefined ? 0 : tax_rate;

            $('table.variation_value_table > tbody').each(function() {
                $(this)
                    .find('tr')
                    .each(function() {
                        var purchase_exc_tax = __read_number($(this).find('input.variable_dpp'));
                        purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

                        var purchase_inc_tax = __add_percent(purchase_exc_tax, tax_rate);
                        __write_number(
                            $(this).find('input.variable_dpp_inc_tax'),
                            purchase_inc_tax
                        );

                        var selling_price = __read_number($(this).find('input.variable_dsp'));
                        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
                        __write_number(
                            $(this).find('input.variable_dsp_inc_tax'),
                            selling_price_inc_tax
                        );
                    });
            });
        }
    });
    //End for product type Variable
    $(document).on('change', '#tax_type', function(e) {
        toggle_dsp_input();
    });
    toggle_dsp_input();

    $(document).on('change', '#expiry_period_type', function(e) {
        if ($(this).val()) {
            $('input#expiry_period').prop('disabled', false);
        } else {
            $('input#expiry_period').val('');
            $('input#expiry_period').prop('disabled', true);
        }
    });

    $(document).on('click', 'a.view-product', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'html',
            success: function(result) {
                $('#view_product_modal')
                    .html(result)
                    .modal('show');
                __currency_convert_recursively($('#view_product_modal'));
            },
        });
    });
    var img_fileinput_setting = {
        showUpload: false,
        showPreview: true,
        browseLabel: LANG.file_browse_label,
        removeLabel: LANG.remove,
        previewSettings: {
            image: { width: 'auto', height: 'auto', 'max-width': '100%', 'max-height': '100%' },
        },
    };
    $('#upload_image').fileinput(img_fileinput_setting);

    if ($('textarea#product_description').length > 0) {
        tinymce.init({
            selector: 'textarea#product_description',
            height:250
        });
    }
});

function toggle_dsp_input() {
    var tax_type = $('#tax_type').val();
    if (tax_type == 'inclusive') {
        $('.dsp_label').each(function() {
            $(this).text(LANG.inc_tax);
        });
        $('#single_dsp').addClass('hide');
        $('#single_dsp_inc_tax').removeClass('hide');

        $('.add-product-price-table')
            .find('.variable_dsp_inc_tax')
            .each(function() {
                $(this).removeClass('hide');
            });
        $('.add-product-price-table')
            .find('.variable_dsp')
            .each(function() {
                $(this).addClass('hide');
            });
    } else if (tax_type == 'exclusive') {
        $('.dsp_label').each(function() {
            $(this).text(LANG.exc_tax);
        });
        $('#single_dsp').removeClass('hide');
        $('#single_dsp_inc_tax').addClass('hide');

        $('.add-product-price-table')
            .find('.variable_dsp_inc_tax')
            .each(function() {
                $(this).addClass('hide');
            });
        $('.add-product-price-table')
            .find('.variable_dsp')
            .each(function() {
                $(this).removeClass('hide');
            });
    }
}

function get_product_details(rowData) {
    var div = $('<div/>')
        .addClass('loading')
        .text('Loading...');

    $.ajax({
        url: '/products/' + rowData.id,
        dataType: 'html',
        success: function(data) {
            div.html(data).removeClass('loading');
        },
    });

    return div;
}

//Quick add unit
$(document).on('submit', 'form#quick_add_unit_form', function(e) {
    e.preventDefault();
    var form = $(this);
    var data = form.serialize();

    $.ajax({
        method: 'POST',
        url: $(this).attr('action'),
        dataType: 'json',
        data: data,
        beforeSend: function(xhr) {
            __disable_submit_button(form.find('button[type="submit"]'));
        },
        success: function(result) {
            if (result.success == true) {
                var newOption = new Option(result.data.short_name, result.data.id, true, true);
                // Append it to the select
                $('#unit_id')
                    .append(newOption)
                    .trigger('change');
                $('div.view_modal').modal('hide');
                toastr.success(result.msg);
            } else {
                toastr.error(result.msg);
            }
        },
    });
});

//Quick add brand
$(document).on('submit', 'form#quick_add_brand_form', function(e) {
    e.preventDefault();
    var form = $(this);
    var data = form.serialize();

    $.ajax({
        method: 'POST',
        url: $(this).attr('action'),
        dataType: 'json',
        data: data,
        beforeSend: function(xhr) {
            __disable_submit_button(form.find('button[type="submit"]'));
        },
        success: function(result) {
            if (result.success == true) {
                var newOption = new Option(result.data.name, result.data.id, true, true);
                // Append it to the select
                $('#brand_id')
                    .append(newOption)
                    .trigger('change');
                $('div.view_modal').modal('hide');
                toastr.success(result.msg);
            } else {
                toastr.error(result.msg);
            }
        },
    });
});

$(document).on('click', 'button.apply-all', function(){
    var val = $(this).closest('.input-group').find('input').val();
    var target_class = $(this).data('target-class');
    $(this).closest('tbody').find('tr').each( function(){
        element =  $(this).find(target_class);
        element.val(val);
        element.change();
    });
});
