var global_brand_id = null;
var global_p_category_id = null;
var global_is_clear_local_storage = false;

// POS Form Data Session Management Functions
function savePosFormDataToSession() {
    let customerName =  $('#customer_id option:selected').text().split( ' (');
    // If the customer name contains a phone number in parentheses, extract only the name part.
    // Example: "Customer Name (1234567890)" -> "Customer Name"
    if (customerName.length > 1) {
        customerName = customerName[0];
    } else {
        customerName = customerName[0];
    }(' (',$('#customer_id option:selected').text());
    console.log('Selected customer name:', customerName);
    var form_data = {
        customer_id: $('#customer_id').val(),
        customer_name: customerName,
        location_id: $('#location_id').val(),
        price_group: $('#price_group').val(),
        commission_agent: $('#commission_agent').val(),
        transaction_date: $('#transaction_date').val(),
        discount_type: $('#discount_type').val(),
        discount_amount: $('#discount_amount').val(),
        tax_rate_id: $('#tax_rate_id').val(),
        tax_calculation_amount: $('#tax_calculation_amount').val(),
        shipping_details: $('#shipping_details').val(),
        shipping_charges: $('#shipping_charges').val(),
        shipping_address: $('#shipping_address').val(),
        additional_notes: $('#sale_note').val(),
        invoice_scheme_id: $('#invoice_scheme_id').val(),
        types_of_service_id: $('#types_of_service_id').val(),
        invoice_layout_id: $('#invoice_layout_id').val()
    };

    $.ajax({
        method: 'POST',
        url: '/sells/pos/save-form-session',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            form_data: form_data
        },
        dataType: 'json',
        async: true
    });
}

function loadPosFormDataFromSession() {
    $.ajax({
        method: 'POST',
        url: '/sells/pos/load-form-session',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        async: false,
        success: function(result) {
            if (result.success && result.form_data) {
                var data = result.form_data;

                // Restore customer
                console.log('Restoring customer from session:', data);
                if (data.customer_id && data.customer_name) {
                    // Use the get customers ajax call to fetch and select the customer
                    $.ajax({
                        url: '/contacts/customers',
                        dataType: 'json',
                        data: {
                            q: data.customer_name
                        },
                        success: function(response) {
                            console.log('Customers fetched:', response);

                            // The response is an array directly
                            var customers = Array.isArray(response) ? response : (response.results || []);

                            if (customers && customers.length > 0) {
                                // Find the matching customer by ID
                                var matchedCustomer = customers.find(function(customer) {
                                    return customer.id == data.customer_id;
                                });

                                if (matchedCustomer) {
                                    // Create the option with the full text format
                                    var optionText = matchedCustomer.text;

                                    var newOption = new Option(optionText, matchedCustomer.id, true, true);
                                    $('#customer_id').html('').append(newOption);

                                    // Manually trigger the select2:select event with full data
                                    $('#customer_id').trigger({
                                        type: 'select2:select',
                                        params: {
                                            data: matchedCustomer
                                        }
                                    });

                                    console.log('Customer restored:', optionText);
                                } else {
                                    console.warn('Customer ID not found in results');
                                }
                            } else {
                                console.warn('No customers found for:', data.customer_name);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching customers:', error);
                        }
                    });
                }

                // Restore price group
                if (data.price_group) {
                    $('#price_group').val(data.price_group).trigger('change');
                }

                // Restore commission agent
                if (data.commission_agent) {
                    $('#commission_agent').val(data.commission_agent).trigger('change');
                }

                // Restore discount
                if (data.discount_type) {
                    $('#discount_type').val(data.discount_type);
                }
                if (data.discount_amount) {
                    __write_number($('#discount_amount'), data.discount_amount);
                }

                // Restore tax
                if (data.tax_rate_id) {
                    $('#tax_rate_id').val(data.tax_rate_id);
                }

                // Restore shipping
                if (data.shipping_charges) {
                    __write_number($('#shipping_charges'), data.shipping_charges);
                }
                if (data.shipping_details) {
                    $('#shipping_details').val(data.shipping_details);
                }
                if (data.shipping_address) {
                    $('#shipping_address').val(data.shipping_address);
                }

                // Restore notes
                if (data.additional_notes) {
                    $('#sale_note').val(data.additional_notes);
                }

                // Restore invoice settings
                if (data.invoice_scheme_id) {
                    $('#invoice_scheme_id').val(data.invoice_scheme_id).trigger('change');
                }
                if (data.types_of_service_id) {
                    $('#types_of_service_id').val(data.types_of_service_id).trigger('change');
                }
                if (data.invoice_layout_id) {
                    $('#invoice_layout_id').val(data.invoice_layout_id).trigger('change');
                }
            }
        }
    });
}

// POS Cart Session Management Functions
function saveCartToSession() {
    var default_location_id = $('#location_id').val();
    if (!default_location_id) {
        return;
    }

    var cart_data = [];
    $('#pos_table tbody tr.product_row').each(function() {
        var $row = $(this);
        var item = {
            variation_id: $row.find('.row_variation_id').val(),
            quantity: __read_number($row.find('.pos_quantity')),
            // Store the specific location for this product line
            location_id: $row.find('.line_location_id').val() || default_location_id
        };
        cart_data.push(item);
    });

    $.ajax({
        method: 'POST',
        url: '/sells/pos/save-cart-session',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            cart_data: cart_data
        },
        dataType: 'json',
        async: true
    });
}

function loadCartFromSession() {
    $.ajax({
        method: 'POST',
        url: '/sells/pos/load-cart-session',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        async: false,
        success: function(result) {
            if (result.success && result.cart_data && result.cart_data.length > 0) {
                // Load products from session with their specific locations
                result.cart_data.forEach(function(item) {
                    // Set the location for this specific product before adding it
                    if (item.location_id) {
                        $('#select_location_id').val(item.location_id).trigger('change');
                    }
                    pos_product_row(item.variation_id, null, null, item.quantity);
                });
            }
        }
    });
}

function clearCartSession() {
    $.ajax({
        method: 'POST',
        url: '/sells/pos/clear-cart-session',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        async: true
    });
}

// Function to check cost price validation when quantity changes (only if enable_msp exists and value is 1)
function checkCostPriceValidation(tr) {
    var qtyInput = tr.find('input.pos_quantity');
    var unitPriceInput = tr.find('input.pos_unit_price_inc_tax, input.pos_unit_price');

    if (unitPriceInput.length > 0 && unitPriceInput.data('cost-price')) {
        var quantity = __read_number(qtyInput);
        var unitPrice = __read_number(unitPriceInput);
        var costPrice = parseFloat(unitPriceInput.data('cost-price'));

        // Calculate total price for this line
        var totalPrice = quantity * unitPrice;
        var totalCostPrice = quantity * costPrice;

        // If total price is less than total cost price, show warning
        if (totalPrice < totalCostPrice) {
            showCostPriceWarning(tr, totalPrice, totalCostPrice);
        } else {
            // Remove any existing warnings
            tr.find('.cost_price_warning').remove();
        }
    }
}

// Function to handle MSP validation rules based on enable_msp setting
function handleMSPValidationRules() {
    if ($('#enable_msp_enabled').length > 0) {
        // If MSP is enabled, remove min-value validation rules and clear any existing errors
        $('.pos_unit_price_inc_tax, .pos_unit_price').each(function () {
            // Remove the min-value validation rule
            $(this).removeAttr('data-rule-min-value');
            $(this).removeAttr('data-msg-min-value');

            // Clear any existing validation errors from all possible locations
            var tr = $(this).closest('tr');

            // Clear errors from pos_line_total_text td
            var error_msg_td = tr.find('.pos_line_total_text').closest('td');
            error_msg_td.find('label.error').remove();

            // Clear errors from the input's own td
            var input_td = $(this).closest('td');
            input_td.find('label.error').remove();

            // Remove any jQuery validation errors
            $(this).removeClass('error');
            $(this).siblings('label.error').remove();

            // Clear any errors that might be in the row
            tr.find('label.error').remove();
            tr.find('.error').removeClass('error');
        });

        // Also clear any existing validation errors that might be present
        $('label.error').remove();
        $('.error').removeClass('error');
    } else {
        // If MSP is disabled, restore min-value validation rules
        $('.pos_unit_price_inc_tax, .pos_unit_price').each(function () {
            if ($(this).data('cost-price')) {
                $(this).attr('data-rule-min-value', $(this).data('cost-price'));
                $(this).attr(
                    'data-msg-min-value',
                    LANG.cost_price_validation_error.replace(
                        ':cost_price',
                        $(this).data('cost-price')
                    )
                );
            }
        });
    }
}

// Compute total quantity in cart for a given variation id limited to a location
function getCartQtyForVariation(variationId, forLocationId) {
    var total = 0;
    try {
        var activeLocId =
            typeof forLocationId !== 'undefined' && forLocationId !== null
                ? String(forLocationId)
                : String($('input#location_id').val());
        $('table#pos_table tbody')
            .find('tr')
            .each(function () {
                var vId = $(this).find('.row_variation_id').val();
                var rowLocId = $(this).find('input.line_location_id').val();
                if (
                    vId &&
                    vId.toString() === variationId.toString() &&
                    String(rowLocId) === activeLocId
                ) {
                    total += __read_number($(this).find('input.pos_quantity'));
                }
            });
    } catch (e) {}
    return total;
}

// Update a single product card's quantity badge using current cart counts
function updateProductQtyBadge(variationId) {
    var box = $('.product_box[data-variation_id="' + variationId + '"]');
    if (box.length === 0) return;
    var enableStock = box.data('enable-stock') == 1 || box.data('enable-stock') === '1';
    var initialQty = parseFloat(box.attr('data-initial-qty'));
    var badge = box.find('.qty-badge');
    if (!enableStock) {
        if (badge.length) {
            badge.text('--');
        }
        return;
    }
    if (isNaN(initialQty)) return;
    var soldQty = getCartQtyForVariation(variationId, $('input#location_id').val());
    var remaining = initialQty - soldQty;
    if (badge.length) {
        badge.text(__number_f(remaining, false));
        if (remaining <= 0) {
            badge.removeClass('text-green').addClass('text-danger');
        } else {
            badge.removeClass('text-danger').addClass('text-green');
        }
    }
}

// Update all product card badges
function updateAllProductQtyBadges() {
    $('.product_box[data-variation_id]').each(function () {
        var vId = $(this).data('variation-id') || $(this).attr('data-variation_id');
        if (vId) {
            updateProductQtyBadge(vId);
        }
    });
}

// Update selected qty badge (the black pill near product name)
function updateSelectedQtyBadge(variationId) {
    var selected = getCartQtyForVariation(variationId, $('input#location_id').val());
    var box = $('.product_box[data-variation_id="' + variationId + '"]');
    if (box.length === 0) return;
    var pill = box.find('.selected-qty-badge');
    if (pill.length === 0) return;
    if (selected && selected > 0) {
        pill.text(__number_f(selected, false));
        // Ensure flex to center content when visible
        pill.css('display', 'flex');
    } else {
        pill.text('0');
        pill.css('display', 'none');
    }
}

function updateAllSelectedQtyBadges() {
    $('.product_box[data-variation_id]').each(function () {
        var vId = $(this).data('variation-id') || $(this).attr('data-variation_id');
        if (vId) {
            updateSelectedQtyBadge(vId);
        }
    });
}
// Function to show cost price warning (only if enable_msp exists and value is 1)
function showCostPriceWarning(tr, totalPrice, totalCostPrice) {
    // Remove existing warnings
    tr.find('.cost_price_warning').remove();

    // Create warning message
    var warningHtml = `
        <div class="cost_price_warning alert alert-warning" style="margin: 5px 0; padding: 5px; font-size: 12px;">
            <i class="fa fa-exclamation-triangle"></i> 
            ${LANG.cost_price_warning_message}
        </div>
    `;

    // Add warning below the quantity input
    var qtyCell = tr.find('input.pos_quantity').closest('td');
    qtyCell.append(warningHtml);
}

$(document).ready(function () {
    customer_set = false;
    //Prevent enter key function except texarea
    $('form').on('keyup keypress', function (e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13 && e.target.tagName != 'TEXTAREA') {
            e.preventDefault();
            return false;
        }
    });

    //For edit pos form
    if ($('form#edit_pos_sell_form').length > 0) {
        pos_form_obj = $('form#edit_pos_sell_form');
        pos_total_row();
    } else {
        pos_form_obj = $('form#add_pos_sell_form');
    }
    if ($('form#edit_pos_sell_form').length > 0 || $('form#add_pos_sell_form').length > 0) {
        initialize_printer();

        // Handle MSP validation rules on page load
        handleMSPValidationRules();

        // Also handle MSP validation rules after a short delay to ensure all elements are loaded
        setTimeout(function () {
            handleMSPValidationRules();
        }, 500);

        // Periodically check and clear validation errors if MSP is enabled
        setInterval(function () {
            if ($('#enable_msp_enabled').length > 0) {
                $('label.error').remove();
                $('.error').removeClass('error');
            }
        }, 1000);

        // Initial sync of selected qty badges after page load
        try {
            updateAllSelectedQtyBadges();
        } catch (e) {}

        // Load form data and cart from session only if not in edit mode
        if ($('form#add_pos_sell_form').length > 0) {
            loadPosFormDataFromSession();

            if ($('#pos_table tbody tr.product_row').length === 0) {
                loadCartFromSession();
            }
        }

        // Save form data when fields change
        $(document).on('change', '#customer_id, #price_group, #commission_agent, #discount_type, #discount_amount, #tax_rate_id, #shipping_charges, #shipping_details, #shipping_address, #sale_note, #invoice_scheme_id, #types_of_service_id, #invoice_layout_id', function() {
            if ($('form#add_pos_sell_form').length > 0) {
                savePosFormDataToSession();
            }
        });
    }

    // Debounced search for product suggestions
    var __pos_search_timer = null;
    $(document).on('input', '#search_product', function () {
        clearTimeout(__pos_search_timer);
        __pos_search_timer = setTimeout(function () {
            $('input#suggestion_page').val(1);
            var location_id = $('input#location_id').val();
            get_product_suggestion_list(global_p_category_id, global_brand_id, location_id);
        }, 300);
    });

    // Updated: select#select_location_id change handler
    $('select#select_location_id').change(function () {
        // Always set the hidden input early to avoid empty suggestion params
        $('input#location_id').val($(this).val());

        var isEditMode = $('form#edit_pos_sell_form').length > 0;

        if (isEditMode) {
            // Save form data to local storage (for draft persistence if needed)
            saveFormDataToLocalStorage();

            // In edit mode: No reset/restore needed (preserve loaded data). Just update defaults and refetch.
            var originalCartLength = $('table#pos_table tbody tr.product_row').length; // Log/verify cart size

            var default_price_group = String($(this).find(':selected').data('default_price_group'));

            if (default_price_group && default_price_group !== '0') {
                var $pg = $('#price_group');
                var $opt = $pg.find("option[value='" + default_price_group + "']");

                if ($opt.length === 0) {
                    // Create new Option and append
                    var optionText = 'Price Group ' + default_price_group; // Placeholder; fetch real name via AJAX if needed
                    var newOption = new Option(optionText, default_price_group, false, false);
                    $pg.append(newOption);
                    $pg.trigger('change'); // Refresh any immediate listeners
                }

                // Defer selection to next tick to avoid reset from event chains
                setTimeout(function () {
                    $pg.val(default_price_group);
                    $pg.trigger('change'); // Trigger refresh (suggestions reload, etc.)
                }, 0);
            } else {
                console.log('Skipping set: default_price_group is falsy or 0');
            }

            // Set default invoice scheme for location
            if ($('#invoice_scheme_id').length) {
                if ($('input[name="is_direct_sale"]').length > 0) {
                    // default scheme for sale screen
                    var invoice_scheme_id = $(this)
                        .find(':selected')
                        .data('default_sale_invoice_scheme_id');
                } else {
                    var invoice_scheme_id = $(this)
                        .find(':selected')
                        .data('default_invoice_scheme_id');
                }
                $('#invoice_scheme_id').val(invoice_scheme_id).change();
            }

            // Set default invoice layout for location
            if ($('#invoice_layout_id').length) {
                let invoice_layout_id = $(this).find(':selected').data('default_invoice_layout_id');
                $('#invoice_layout_id').val(invoice_layout_id).change();
            }

            // Set default price group
            if ($('#default_price_group').length) {
                var dpg = default_price_group ? default_price_group : 0;
                $('#default_price_group').val(dpg);
            }

            set_payment_type_dropdown();

            // if ($('#types_of_service_id').length && $('#types_of_service_id').val()) {
            //     $('#types_of_service_id').change();
            // }

            // Refetch product suggestions with new location (page 1 to refresh)
            $('input#suggestion_page').val(1);
            get_product_suggestion_list(
                global_p_category_id,
                global_brand_id,
                $('input#location_id').val(),
                null
            );
            get_featured_products();

            pos_total_row();
            // Optional debug: Verify cart preserved
            var newCartLength = $('table#pos_table tbody tr.product_row').length;

            if (originalCartLength !== newCartLength) {
                console.error(
                    'Cart cleared unexpectedly in edit mode! Check types_of_service or other triggers.'
                );
            }
            return; // Skip the rest (no reset/restore in edit)
        }
        // Original add-mode logic: Capture cart, reset, restore with original line locations
        // If the selected location is the same as the current, do nothing
        // var currentLocId = $("input#location_id").val();
        // var newLocId = $(this).val();
        //      console.log(newLocId, currentLocId);
        // if (String(currentLocId) === String(newLocId)) {
        //   return; // avoid unnecessary reset/reloads when re-selecting the same branch
        // }
        // capture current cart before resetting, including per-line location
        var prevCartItems = [];
        var saved_customer_id = $('select#customer_id').val(); // Save current customer
        // capture previously active price group so restored items use the same pricing
        var prevActivePriceGroup = '';
        if ($('#price_group').length > 0) {
            prevActivePriceGroup = $('#price_group').val();
        } else if ($('#default_price_group').length > 0) {
            prevActivePriceGroup = $('#default_price_group').val();
        }
        $('table#pos_table tbody tr.product_row').each(function () {
            var variationId = $(this).find('.row_variation_id').val();
            var qty = __read_number($(this).find('.pos_quantity')) || 0;
            var lineLoc = $(this).find('input.line_location_id').val();
            var subUnitId = $(this).find('select.sub_unit').val() || null;
            var rowUnitPriceIncTax =
                __read_number($(this).find('input.pos_unit_price_inc_tax')) || '';
            var rowUnitPriceBase = __read_number($(this).find('input.pos_unit_price')) || '';
            var rowLineTotal = __read_number($(this).find('input.pos_line_total')) || '';
            var rowPriceGroupId = prevActivePriceGroup; // use active group at time of capture
            if (variationId && qty > 0) {
                prevCartItems.push({
                    variationId: variationId,
                    qty: qty,
                    locationId: lineLoc,
                    subUnitId: subUnitId,
                    unitPriceIncTax: rowUnitPriceIncTax,
                    unitPriceBase: rowUnitPriceBase,
                    lineTotal: rowLineTotal,
                    priceGroupId: rowPriceGroupId,
                });
            }
        });

        // reset form (switches branch and clears cart)
        // reset_pos_form();

        // Restore customer selection
        if (saved_customer_id) {
            $('select#customer_id').val(saved_customer_id).trigger('change');
        }

        // After reset, restore items using their original line location
        // if (prevCartItems.length > 0) {
        //   // restore sequentially to preserve row order
        //   (async function restoreCartSequentially(items) {
        //     for (var i = 0; i < items.length; i++) {
        //       var item = items[i];
        //       try {
        //         var product_row = $("input#product_row_count").val();
        //         var customer_id = $("select#customer_id").val();
        //         var is_direct_sell =
        //           $('input[name="is_direct_sale"]').length > 0 &&
        //           $('input[name="is_direct_sale"]').val() == 1;
        //         var is_serial_no =
        //           $('input[name="is_serial_no"]').length > 0 &&
        //           $('input[name="is_serial_no"]').val() == 1;
        //         // Use stored row price group if available; fallback to previously active
        //         var price_group =
        //           typeof item.priceGroupId !== "undefined" &&
        //           item.priceGroupId !== null
        //             ? item.priceGroupId
        //             : prevActivePriceGroup !== undefined
        //             ? prevActivePriceGroup
        //             : "";
        //         var is_sales_order =
        //           $("#sale_type").length && $("#sale_type").val() == "sales_order"
        //             ? true
        //             : false;
        //         var disable_qty_alert = $("#disable_qty_alert").length
        //           ? true
        //           : false;
        //         var is_draft =
        //           $("#status") &&
        //           ($("#status").val() == "quotation" ||
        //             $("#status").val() == "draft")
        //             ? true
        //             : false;

        //         // Use original line location to fetch the row
        //         var restoreLocationId =
        //           item.locationId ||
        //           $("input#location_id").val() ||
        //           $("select#select_location_id").val();

        //         // preview the product row to read available qty at original line location
        //         const preview = await $.ajax({
        //           method: "GET",
        //           url:
        //             "/sells/pos/get_product_row/" +
        //             item.variationId +
        //             "/" +
        //             restoreLocationId,
        //           data: {
        //             product_row: product_row,
        //             customer_id: customer_id,
        //             is_direct_sell: is_direct_sell,
        //             is_serial_no: is_serial_no,
        //             price_group: price_group,
        //             quantity: item.qty,
        //             is_sales_order: is_sales_order,
        //             disable_qty_alert: disable_qty_alert,
        //             is_draft: is_draft,
        //           },
        //           dataType: "json",
        //         });

        //         if (preview && preview.success && preview.html_content) {
        //           var $tmp = $("<tbody>" + preview.html_content + "</tbody>");
        //           var qtyAttr = $tmp
        //             .find("input.pos_quantity")
        //             .attr("data-qty_available");
        //           var enableStock = $tmp
        //             .find("input.pos_quantity")
        //             .is("[data-qty_available]");
        //           var qtyAvailable = enableStock
        //             ? parseFloat(qtyAttr || "0")
        //             : Number.POSITIVE_INFINITY;
        //           if (qtyAvailable >= item.qty) {
        //             // Merge by same variation and same original line location; else append
        //             var $tbody = $("table#pos_table tbody");
        //             var $existingRow = $tbody
        //               .find("tr.product_row")
        //               .filter(function () {
        //                 var vId = $(this).find(".row_variation_id").val();
        //                 var lineLoc = $(this).find("input.line_location_id").val();
        //                 var existingSubUnitId =
        //                   $(this).find("select.sub_unit").val() || null;
        //                 var sameSubUnit =
        //                   String(existingSubUnitId || "") ===
        //                   String(item.subUnitId || "");
        //                 return (
        //                   String(vId) === String(item.variationId) &&
        //                   String(lineLoc) === String(restoreLocationId) &&
        //                   sameSubUnit
        //                 );
        //               })
        //               .first();

        //             if ($existingRow.length) {
        //               var prevQty =
        //                 __read_number($existingRow.find("input.pos_quantity")) || 0;
        //               __write_number(
        //                 $existingRow.find("input.pos_quantity"),
        //                 prevQty + __number_uf(item.qty)
        //               );
        //               pos_each_row($existingRow);
        //               pos_total_row();
        //             } else {
        //               $tbody
        //                 .append(preview.html_content)
        //                 .find("input.pos_quantity");
        //               $("input#product_row_count").val(parseInt(product_row) + 1);
        //               var this_row = $("table#pos_table tbody").find("tr").last();
        //               this_row
        //                 .find("input.line_location_id")
        //                 .val(restoreLocationId);
        //               // Re-apply the previously captured unit/base prices and line total to preserve pricing
        //               var hadInc =
        //                 item.unitPriceIncTax !== "" && !isNaN(item.unitPriceIncTax);
        //               var hadBase =
        //                 item.unitPriceBase !== "" && !isNaN(item.unitPriceBase);
        //               if (hadInc) {
        //                 __write_number(
        //                   this_row.find("input.pos_unit_price_inc_tax"),
        //                   item.unitPriceIncTax
        //                 );
        //               }
        //               if (hadBase) {
        //                 __write_number(
        //                   this_row.find("input.pos_unit_price"),
        //                   item.unitPriceBase
        //                 );
        //               }
        //               var qty_for_line =
        //                 __read_number(this_row.find("input.pos_quantity")) ||
        //                 item.qty ||
        //                 1;
        //               var line_total_preserved =
        //                 item.lineTotal !== "" && !isNaN(item.lineTotal)
        //                   ? item.lineTotal
        //                   : hadInc
        //                   ? item.unitPriceIncTax * qty_for_line
        //                   : __read_number(this_row.find("input.pos_line_total"));
        //               __write_number(
        //                 this_row.find("input.pos_line_total"),
        //                 line_total_preserved,
        //                 false
        //               );
        //               this_row
        //                 .find("span.pos_line_total_text")
        //                 .text(__currency_trans_from_en(line_total_preserved, true));
        //               // Restore previously selected sub-unit, if any
        //               if (item.subUnitId) {
        //                 var $subSel = this_row.find("select.sub_unit");
        //                 if ($subSel.length) {
        //                   $subSel.val(String(item.subUnitId)).trigger("change");
        //                 }
        //               } else if (__getUnitMultiplier(this_row) > 1) {
        //                 // fallback to existing behavior if multiplier indicates non-base unit
        //                 this_row.find("select.sub_unit").trigger("change");
        //               }
        //               pos_each_row(this_row);
        //               var line_total = __read_number(
        //                 this_row.find("input.pos_line_total")
        //               );
        //               this_row.find("span.pos_line_total_text").text(line_total);
        //               pos_total_row();
        //               if (preview.enable_sr_no == "1") {
        //                 this_row
        //                   .find(".row_edit_product_price_model")
        //                   .modal("show");
        //               }
        //               round_row_to_iraqi_dinnar(this_row);
        //               __currency_convert_recursively(this_row);
        //             }
        //           } // else: skip to effectively remove from cart
        //         }
        //       } catch (e) {
        //         // silently skip item on error
        //       }
        //     }
        //     // recalc totals after restore
        //     pos_total_row();
        //     // After restoring rows for the newly selected location, refresh selection badges
        //     try {
        //       updateAllSelectedQtyBadges();
        //     } catch (e) {}
        //   })(prevCartItems);
        // }

        get_product_suggestion_list(
            global_p_category_id,
            global_brand_id,
            $('input#location_id').val(),
            null,
            is_enabled_stock,
            device_model_id
        );
        // Ensure hidden location id reflects the selection before any downstream calls
        $('input#location_id').val($(this).val());

        var default_price_group = $(this).find(':selected').data('default_price_group');

        if (default_price_group) {
            if ($("#price_group option[value='" + default_price_group + "']").length > 0) {
                $('#price_group').val(default_price_group);
            }
        } else {
            // If no default for this branch, fallback to 0 (default price) when available
            if ($("#price_group option[value='0']").length > 0) {
                $('#price_group').val('0');
            }
        }

        // Always trigger change so UI and product grid refresh with the active value
        $('#price_group').change();

        // Do not manually refresh suggestions here; the price group change event
        // will clear and reload suggestions with the correct price group and location.

        // Refresh all product selection badges to reflect the active location
        try {
            updateAllSelectedQtyBadges();
        } catch (e) {}
        // In case product grid re-renders asynchronously due to price group/location
        // changes, do one more refresh shortly after
        setTimeout(function () {
            try {
                updateAllSelectedQtyBadges();
            } catch (e) {}
        }, 400);

        //Set default invoice scheme for location
        if ($('#invoice_scheme_id').length) {
            if ($('input[name="is_direct_sale"]').length > 0) {
                //default scheme for sale screen
                var invoice_scheme_id = $(this)
                    .find(':selected')
                    .data('default_sale_invoice_scheme_id');
            } else {
                var invoice_scheme_id = $(this).find(':selected').data('default_invoice_scheme_id');
            }

            $('#invoice_scheme_id').val(invoice_scheme_id).change();
        }

        //Set default invoice layout for location
        if ($('#invoice_layout_id').length) {
            let invoice_layout_id = $(this).find(':selected').data('default_invoice_layout_id');
            $('#invoice_layout_id').val(invoice_layout_id).change();
        }

        //Set default price group
        if ($('#default_price_group').length) {
            var dpg = default_price_group ? default_price_group : 0;
            $('#default_price_group').val(dpg);
        }

        set_payment_type_dropdown();

        if ($('#types_of_service_id').length && $('#types_of_service_id').val()) {
            $('#types_of_service_id').change();
        }

        // Refetch suggestions (page 1 in add mode too)
        $('input#suggestion_page').val(1);
        get_product_suggestion_list(
            global_p_category_id,
            global_brand_id,
            $('input#location_id').val(),
            null
        );
        get_featured_products();
    });

    //get customer
    $('select#customer_id').select2({
        ajax: {
            url: '/contacts/customers',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                };
            },
            processResults: function (data) {
                return {
                    results: data,
                };
            },
        },
        templateResult: function (data) {
            var template = '';
            if (data.supplier_business_name) {
                template += data.supplier_business_name + '<br>';
            }
            template += data.text + '<br>' + LANG.mobile + ': ' + data.mobile;

            if (typeof data.total_rp != 'undefined') {
                var rp = data.total_rp ? data.total_rp : 0;
                template += "<br><i class='fa fa-gift text-success'></i> " + rp;
            }

            return template;
        },
        minimumInputLength: 1,
        language: {
            inputTooShort: function (args) {
                return LANG.please_enter + args.minimum + LANG.or_more_characters;
            },
            noResults: function () {
                var name = $('#customer_id').data('select2').dropdown.$search.val();
                return (
                    '<button type="button" data-name="' +
                    name +
                    '" class="btn btn-link add_new_customer"><i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i>&nbsp; ' +
                    __translate('add_name_as_new_customer', { name: name }) +
                    '</button>'
                );
            },
        },
        escapeMarkup: function (markup) {
            return markup;
        },
    });
    $('#customer_id').on('select2:select', function (e) {
        var data = e.params.data;
        if (data.pay_term_number) {
            $('input#pay_term_number').val(data.pay_term_number);
        } else {
            $('input#pay_term_number').val('');
        }

        if (data.pay_term_type) {
            $('#add_sell_form select[name="pay_term_type"]').val(data.pay_term_type);
            $('#edit_sell_form select[name="pay_term_type"]').val(data.pay_term_type);
        } else {
            $('#add_sell_form select[name="pay_term_type"]').val('');
            $('#edit_sell_form select[name="pay_term_type"]').val('');
        }

        update_shipping_address(data);
        $('#advance_balance_text').text(__currency_trans_from_en(data.balance), true);
        $('#advance_balance').val(data.balance);

        if (data.price_calculation_type == 'selling_price_group') {
            $('#price_group').val(data.selling_price_group_id);
            $('#price_group').change();
        }
        //  else {
        //     $('#price_group').val(0);
        //     $('#price_group').change();
        // }
        if ($('.contact_due_text').length) {
            get_contact_due(data.id);
            // store on customer change
            saveFormDataToLocalStorage();
        }
    });

    set_default_customer();

    if ($('#search_product').length) {
        //Add Product
        $('#search_product')
            .autocomplete({
                delay: 1000,
                source: function (request, response) {
                    var price_group = '';
                    var search_fields = [];
                    $('.search_fields:checked').each(function (i) {
                        search_fields[i] = $(this).val();
                    });

                    if ($('#price_group').length > 0) {
                        price_group = $('#price_group').val();
                    }
                    $.getJSON(
                        '/products/list',
                        {
                            price_group: price_group,
                            location_id: $('input#location_id').val(),
                            term: request.term,
                            not_for_selling: 0,
                            search_fields: search_fields,
                        },
                        response
                    );
                },
                minLength: 2,
                response: function (event, ui) {
                    if (ui.content.length == 1) {
                        ui.item = ui.content[0];

                        var is_overselling_allowed = false;
                        if ($('input#is_overselling_allowed').length) {
                            is_overselling_allowed = true;
                        }
                        var for_so = false;
                        if ($('#sale_type').length && $('#sale_type').val() == 'sales_order') {
                            for_so = true;
                        }

                        if (
                            (ui.item.enable_stock == 1 && ui.item.qty_available > 0) ||
                            ui.item.enable_stock == 0 ||
                            is_overselling_allowed ||
                            for_so
                        ) {
                            $(this)
                                .data('ui-autocomplete')
                                ._trigger('select', 'autocompleteselect', ui);
                            $(this).autocomplete('close');
                        }
                    } else if (ui.content.length == 0) {
                        toastr.error(LANG.no_products_found);
                        $('input#search_product').select();
                    }
                },
                focus: function (event, ui) {
                    if (ui.item.qty_available <= 0) {
                        return false;
                    }
                },
                select: function (event, ui) {
                    var searched_term = $(this).val();
                    var is_overselling_allowed = false;
                    if ($('input#is_overselling_allowed').length) {
                        is_overselling_allowed = true;
                    }
                    var for_so = false;
                    if ($('#sale_type').length && $('#sale_type').val() == 'sales_order') {
                        for_so = true;
                    }

                    var is_draft = false;
                    if (
                        $('#status') &&
                        ($('#status').val() == 'quotation' || $('#status').val() == 'draft')
                    ) {
                        var is_draft = true;
                    }

                    if (
                        ui.item.enable_stock != 1 ||
                        ui.item.qty_available > 0 ||
                        is_overselling_allowed ||
                        for_so ||
                        is_draft
                    ) {
                        $(this).val(null);

                        get_product_suggestion_list(
                            global_p_category_id,
                            global_brand_id,
                            $('input#location_id').val(),
                            null
                        );

                        //Pre select lot number only if the searched term is same as the lot number
                        var purchase_line_id =
                            ui.item.purchase_line_id && searched_term == ui.item.lot_number
                                ? ui.item.purchase_line_id
                                : null;
                        pos_product_row(ui.item.variation_id, purchase_line_id);
                        updateAllSelectedQtyBadges();
                    } else {
                        alert(LANG.out_of_stock);
                    }
                },
            })
            .autocomplete('instance')._renderItem = function (ul, item) {
            var is_overselling_allowed = false;
            if ($('input#is_overselling_allowed').length) {
                is_overselling_allowed = true;
            }

            var for_so = false;
            if ($('#sale_type').length && $('#sale_type').val() == 'sales_order') {
                for_so = true;
            }
            var is_draft = false;

            if (
                $('#status') &&
                ($('#status').val() == 'quotation' || $('#status').val() == 'draft')
            ) {
                var is_draft = true;
            }

            if (
                item.enable_stock == 1 &&
                item.qty_available <= 0 &&
                !is_overselling_allowed &&
                !for_so &&
                !is_draft
            ) {
                var string = '<li class="ui-state-disabled">' + item.name;
                if (item.type == 'variable') {
                    string += '-' + item.variation;
                }
                var selling_price = item.selling_price;
                if (item.variation_group_price) {
                    selling_price = item.variation_group_price;
                }
                string +=
                    ' (' +
                    item.sub_sku +
                    ')' +
                    '<br> Price: ' +
                    __currency_trans_from_en(
                        selling_price,
                        false,
                        false,
                        __currency_precision,
                        true
                    ) +
                    ' (Out of stock) </li>';
                return $(string).appendTo(ul);
            } else {
                var string = '<div>' + item.name;
                if (item.type == 'variable') {
                    string += '-' + item.variation;
                }

                var selling_price = item.selling_price;
                if (item.variation_group_price) {
                    selling_price = item.variation_group_price;
                }

                string +=
                    ' (' +
                    item.sub_sku +
                    ')' +
                    '<br> Price: ' +
                    __currency_trans_from_en(
                        selling_price,
                        false,
                        false,
                        __currency_precision,
                        true
                    );
                if (item.enable_stock == 1) {
                    var qty_available = __currency_trans_from_en(
                        item.qty_available,
                        false,
                        false,
                        __currency_precision,
                        true
                    );
                    string += ' - ' + qty_available + item.unit;
                }
                string += '</div>';

                return $('<li>').append(string).appendTo(ul);
            }
        };
    }

    //Update line total and check for quantity not greater than max quantity
    $('table#pos_table tbody').on('change', 'input.pos_quantity', function () {
        // comment line becouse it validate form at increment and decrement item
        // if (sell_form_validator) {
        //     sell_form.valid();
        // }
        if (pos_form_validator) {
            pos_form_validator.element($(this));
        }
        // var max_qty = parseFloat($(this).data('rule-max'));
        var entered_qty = __read_number($(this));

        var tr = $(this).parents('tr');

        var unit_price_inc_tax = __read_number(tr.find('input.pos_unit_price_inc_tax'));
        var line_total = entered_qty * unit_price_inc_tax;

        __write_number(tr.find('input.pos_line_total'), line_total, false);
        tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));

        //Change modifier quantity
        tr.find('.modifier_qty_text').each(function () {
            $(this).text(__currency_trans_from_en(entered_qty, false));
        });
        tr.find('.modifiers_quantity').each(function () {
            $(this).val(entered_qty);
        });

        pos_total_row();

        adjustComboQty(tr);

        // Check if enable_msp exists and value is 1, then validate cost price
        if ($('#enable_msp_enabled').length > 0) {
            checkCostPriceValidation(tr);
        }

        // Update product quantity badge for this variation in product grid
        try {
            var variationIdForRow = tr.find('.row_variation_id').val();
            if (typeof updateProductQtyBadge === 'function' && variationIdForRow) {
                updateProductQtyBadge(variationIdForRow);
            }

            if (variationIdForRow) {
                updateSelectedQtyBadge(variationIdForRow);
            }
        } catch (e) {}
    });

    // Add event handler for quantity up/down buttons
    $('table#pos_table tbody').on('click', '.quantity-up, .quantity-down', function () {
        var tr = $(this).closest('tr');
        var qtyInput = tr.find('input.pos_quantity');

        // Wait a bit for the quantity to update, then check validation (if enable_msp exists and value is 1)
        setTimeout(function () {
            if ($('#enable_msp_enabled').length > 0) {
                checkCostPriceValidation(tr);
            }
            // Also refresh badge for this product variation
            try {
                var variationIdForRow = tr.find('.row_variation_id').val();
                if (typeof updateProductQtyBadge === 'function' && variationIdForRow) {
                    updateProductQtyBadge(variationIdForRow);
                }
                if (variationIdForRow) {
                    updateSelectedQtyBadge(variationIdForRow);
                }
            } catch (e) {}
        }, 100);
    });

    // Track when user is actively typing in the price including tax field (Precio)
    $('table#pos_table tbody').on('focusin', 'input.pos_unit_price_inc_tax', function () {
        $(this).data('editing-price', true);
    });
    $('table#pos_table tbody').on('focusout', 'input.pos_unit_price_inc_tax', function () {
        $(this).data('editing-price', false);
    });

    //If change in unit price update price including tax and line total
    $('table#pos_table tbody').on('change', 'input.pos_unit_price', function () {
        var tr = $(this).parents('tr');

        //calculate discounted unit price
        var discounted_unit_price = calculate_discounted_unit_price(tr);

        var tax_rate = tr.find('select.tax_id').find(':selected').data('rate');

        var quantity = __read_number(tr.find('input.pos_quantity'));

        var unit_price_inc_tax = __add_percent(discounted_unit_price, tax_rate);
        var line_total = quantity * unit_price_inc_tax;

        // Do not overwrite Precio field if user is editing it
        var priceIncTaxInput = tr.find('input.pos_unit_price_inc_tax');
        if (!priceIncTaxInput.data('editing-price')) {
            __write_number(priceIncTaxInput, unit_price_inc_tax);
            // Update all inputs with the same name in this row to keep them synchronized
            tr.find('input.pos_unit_price_inc_tax').val(priceIncTaxInput.val());
        }
        __write_number(tr.find('input.pos_line_total'), line_total);
        tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));
        pos_each_row(tr);
        pos_total_row();
        round_row_to_iraqi_dinnar(tr);

        // Check if enable_msp exists and value is 1, then validate cost price
        if ($('#enable_msp_enabled').length > 0) {
            checkCostPriceValidation(tr);
        }
    });

    //If change in unit price inc tax update line total
    $('table#pos_table tbody').on('change', 'input.pos_unit_price_inc_tax', function () {
        var tr = $(this).parents('tr');
        var unit_price_inc_tax = __read_number($(this));

        // Update all inputs with the same name in this row to keep them synchronized
        tr.find('input.pos_unit_price_inc_tax').not(this).val($(this).val());

        var quantity = __read_number(tr.find('input.pos_quantity'));
        var line_total = quantity * unit_price_inc_tax;

        __write_number(tr.find('input.pos_line_total'), line_total);
        tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));
        pos_total_row();

        // Check if enable_msp exists and value is 1, then validate cost price
        if ($('#enable_msp_enabled').length > 0) {
            checkCostPriceValidation(tr);
        }
    });

    //If change in tax rate then update unit price according to it.
    $('table#pos_table tbody').on('change', 'select.tax_id', function () {
        var tr = $(this).parents('tr');

        var tax_rate = tr.find('select.tax_id').find(':selected').data('rate');
        var unit_price_inc_tax = __read_number(tr.find('input.pos_unit_price_inc_tax'));

        var discounted_unit_price = __get_principle(unit_price_inc_tax, tax_rate);
        var unit_price = get_unit_price_from_discounted_unit_price(tr, discounted_unit_price);
        __write_number(tr.find('input.pos_unit_price'), unit_price);
        pos_each_row(tr);
    });

    //If change in unit price including tax, update Base Price and totals
    $('table#pos_table tbody').on('keyup', 'input.pos_unit_price_inc_tax', function () {
        var unit_price_inc_tax = __read_number($(this));
        // Format the Precio field only (thousand separators/decimals), do not recalculate it
        __write_number($(this), unit_price_inc_tax, false);

        // Update all inputs with the same name in this row to keep them synchronized
        var tr = $(this).parents('tr');
        tr.find('input.pos_unit_price_inc_tax').not(this).val($(this).val());

        console.log(__read_number($(this)));
        var tax_rate = tr.find('select.tax_id').find(':selected').data('rate');
        var quantity = __read_number(tr.find('input.pos_quantity'));

        var line_total = quantity * unit_price_inc_tax;
        var discounted_unit_price = __get_principle(unit_price_inc_tax, tax_rate);
        var unit_price = get_unit_price_from_discounted_unit_price(tr, discounted_unit_price);

        __write_number(tr.find('input.pos_unit_price'), unit_price);
        __write_number(tr.find('input.pos_line_total'), line_total, false);
        tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));

        pos_each_row(tr);
        pos_total_row();
    });

    //Change max quantity rule if lot number changes
    $('table#pos_table tbody').on('change', 'select.lot_number', function () {
        var qty_element = $(this).closest('tr').find('input.pos_quantity');

        var tr = $(this).closest('tr');
        var multiplier = 1;
        var unit_name = '';
        var sub_unit_length = tr.find('select.sub_unit').length;
        if (sub_unit_length > 0) {
            var select = tr.find('select.sub_unit');
            multiplier = parseFloat(select.find(':selected').data('multiplier'));
            unit_name = select.find(':selected').data('unit_name');
        }
        var allow_overselling = qty_element.data('allow-overselling');
        if ($(this).val() && !allow_overselling) {
            var lot_qty = $('option:selected', $(this)).data('qty_available');
            var max_err_msg = $('option:selected', $(this)).data('msg-max');

            if (sub_unit_length > 0) {
                lot_qty = lot_qty / multiplier;
                var lot_qty_formated = __number_f(lot_qty, false);
                max_err_msg = __translate('lot_max_qty_error', {
                    max_val: lot_qty_formated,
                    unit_name: unit_name,
                });
            }

            qty_element.attr('data-rule-max-value', lot_qty);
            qty_element.attr('data-msg-max-value', max_err_msg);

            qty_element.rules('add', {
                'max-value': lot_qty,
                messages: {
                    'max-value': max_err_msg,
                },
            });
        } else {
            var default_qty = qty_element.data('qty_available');
            var default_err_msg = qty_element.data('msg_max_default');
            if (sub_unit_length > 0) {
                default_qty = default_qty / multiplier;
                var lot_qty_formated = __number_f(default_qty, false);
                default_err_msg = __translate('pos_max_qty_error', {
                    max_val: lot_qty_formated,
                    unit_name: unit_name,
                });
            }

            qty_element.attr('data-rule-max-value', default_qty);
            qty_element.attr('data-msg-max-value', default_err_msg);

            qty_element.rules('add', {
                'max-value': default_qty,
                messages: {
                    'max-value': default_err_msg,
                },
            });
        }
        qty_element.trigger('change');
    });

    //Change in row discount type or discount amount
    $('table#pos_table tbody').on(
        'change',
        'select.row_discount_type, input.row_discount_amount',
        function () {
            var tr = $(this).parents('tr');

            //calculate discounted unit price
            var discounted_unit_price = calculate_discounted_unit_price(tr);

            var tax_rate = tr.find('select.tax_id').find(':selected').data('rate');
            var quantity = __read_number(tr.find('input.pos_quantity'));

            var unit_price_inc_tax = __add_percent(discounted_unit_price, tax_rate);
            var line_total = quantity * unit_price_inc_tax;

            // Do not overwrite Precio field if user is editing it
            var priceIncTaxInput2 = tr.find('input.pos_unit_price_inc_tax');
            if (!priceIncTaxInput2.data('editing-price')) {
                __write_number(priceIncTaxInput2, unit_price_inc_tax);
                // Update all inputs with the same name in this row to keep them synchronized
                tr.find('input.pos_unit_price_inc_tax').val(priceIncTaxInput2.val());
            }
            __write_number(tr.find('input.pos_line_total'), line_total, false);
            tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));
            pos_each_row(tr);
            pos_total_row();
            round_row_to_iraqi_dinnar(tr);
        }
    );

    //Remove row on click on remove row
    $('table#pos_table tbody').on('click', 'i.pos_remove_row', function () {
        var tr = $(this).parents('tr');
        var variationIdForRow = tr.find('.row_variation_id').val();
        tr.remove();
        pos_total_row();
        // Update product badge after removal
        try {
            if (typeof updateProductQtyBadge === 'function' && variationIdForRow) {
                updateProductQtyBadge(variationIdForRow);
            }
            if (variationIdForRow) {
                updateSelectedQtyBadge(variationIdForRow);
                // Remove badge if no more rows for this variation
                var stillExists =
                    $('table#pos_table tbody tr.product_row').filter(function () {
                        return $(this).find('.row_variation_id').val() == variationIdForRow;
                    }).length > 0;
                if (!stillExists) {
                    $(
                        ".selected-qty-badge[data-variation-id='" + variationIdForRow + "']"
                    ).remove();
                }
            }
        } catch (e) {}

        // Save cart to session after removing product
        saveCartToSession();
    });

    // Function to remove all qty badges (used after cart reset or sale complete)
    function removeAllSelectedQtyBadges() {
        $('.selected-qty-badge').css('display', 'none');
    }

    //Cancel the invoice
    $('button#pos-cancel').click(function () {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((confirm) => {
            if (confirm) {
                reset_pos_form();
                removeAllSelectedQtyBadges();
            }
        });
    });

    //Save invoice as draft
    $('button#pos-draft').click(function () {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            removeAllSelectedQtyBadges();
            return false;
        }

        var is_valid = isValidPosForm();
        if (is_valid != true) {
            return;
        }

        var data = pos_form_obj.serialize();
        data = data + '&status=draft';
        var url = pos_form_obj.attr('action');

        disable_pos_form_actions();
        $.ajax({
            method: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: function (result) {
                enable_pos_form_actions();
                if (result.success == 1) {
                    reset_pos_form();
                    removeAllSelectedQtyBadges();
                    toastr.success(result.msg);
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });

    //Save invoice as Quotation
    $('button#pos-quotation').click(function () {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            removeAllSelectedQtyBadges();
            return false;
        }

        var is_valid = isValidPosForm();
        if (is_valid != true) {
            return;
        }

        var data = pos_form_obj.serialize();
        data = data + '&status=quotation';
        var url = pos_form_obj.attr('action');

        disable_pos_form_actions();
        $.ajax({
            method: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: function (result) {
                enable_pos_form_actions();
                if (result.success == 1) {
                    reset_pos_form();
                    removeAllSelectedQtyBadges();
                    toastr.success(result.msg);

                    //Check if enabled or not
                    if (result.receipt.is_enabled) {
                        pos_print(result.receipt);
                    }
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });

    //Finalize invoice, open payment modal
    $('button#pos-finalize').click(function () {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            removeAllSelectedQtyBadges();
            return false;
        }

        if ($('#reward_point_enabled').length) {
            var validate_rp = isValidatRewardPoint();
            if (!validate_rp['is_valid']) {
                toastr.error(validate_rp['msg']);
                return false;
            }
        }

        $('#modal_payment').modal('show');
    });

    $('#modal_payment').one('shown.bs.modal', function () {
        $('#modal_payment').find('input').filter(':visible:first').focus().select();
        if ($('form#edit_pos_sell_form').length == 0) {
            $(this).find('#method_0').change();
        }
    });

    //Finalize without showing payment options
    $('button.pos-express-finalize').click(function () {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            removeAllSelectedQtyBadges();
            return false;
        }

        if ($('#reward_point_enabled').length) {
            var validate_rp = isValidatRewardPoint();
            if (!validate_rp['is_valid']) {
                toastr.error(validate_rp['msg']);
                return false;
            }
        }

        var pay_method = $(this).data('pay_method');

        //If pay method is credit sale submit form
        if (pay_method == 'credit_sale') {
            $('#is_credit_sale').val(1);
            pos_form_obj.submit();
            removeAllSelectedQtyBadges();
            return true;
        } else {
            if ($('#is_credit_sale').length) {
                $('#is_credit_sale').val(0);
            }
        }

        //Check for remaining balance & add it in 1st payment row
        var total_payable = __read_number($('input#final_total_input'));
        var total_paying = __read_number($('input#total_paying_input'));
        if (total_payable > total_paying) {
            var bal_due = total_payable - total_paying;

            var first_row = $('#payment_rows_div').find('.payment-amount').first();
            var first_row_val = __read_number(first_row);
            first_row_val = first_row_val + bal_due;
            __write_number(first_row, first_row_val);
            first_row.trigger('change');
        }

        //Change payment method.
        var payment_method_dropdown = $('#payment_rows_div')
            .find('.payment_types_dropdown')
            .first();

        payment_method_dropdown.val(pay_method);
        payment_method_dropdown.change();
        if (pay_method == 'card') {
            $('div#card_details_modal').modal('show');
        } else if (pay_method == 'suspend') {
            $('div#confirmSuspendModal').modal('show');
        } else {
            pos_form_obj.submit();
            removeAllSelectedQtyBadges();
        }
    });

    $('div#card_details_modal').on('shown.bs.modal', function (e) {
        $('input#card_number').focus();
    });

    $('div#confirmSuspendModal').on('shown.bs.modal', function (e) {
        $(this).find('textarea').focus();
    });

    //on save card details
    $('button#pos-save-card').click(function () {
        $('input#card_number_0').val($('#card_number').val());
        $('input#card_holder_name_0').val($('#card_holder_name').val());
        $('input#card_transaction_number_0').val($('#card_transaction_number').val());
        $('select#card_type_0').val($('#card_type').val());
        $('input#card_month_0').val($('#card_month').val());
        $('input#card_year_0').val($('#card_year').val());
        $('input#card_security_0').val($('#card_security').val());

        $('div#card_details_modal').modal('hide');
        pos_form_obj.submit();
    });

    $('button#pos-suspend').click(function () {
        $('input#is_suspend').val(1);
        $('div#confirmSuspendModal').modal('hide');
        pos_form_obj.submit();
        removeAllSelectedQtyBadges();
        $('input#is_suspend').val(0);
    });

    //fix select2 input issue on modal
    $('#modal_payment')
        .find('.select2')
        .each(function () {
            $(this).select2({
                dropdownParent: $('#modal_payment'),
            });
        });

    $('button#add-payment-row').click(function () {
        var row_index = $('#payment_row_index').val();
        var location_id = $('input#location_id').val();
        $.ajax({
            method: 'POST',
            url: '/sells/pos/get_payment_row',
            data: { row_index: row_index, location_id: location_id },
            dataType: 'html',
            success: function (result) {
                if (result) {
                    var appended = $('#payment_rows_div').append(result);

                    var total_payable = __read_number($('input#final_total_input'));
                    var total_paying = __read_number($('input#total_paying_input'));
                    var b_due = total_payable - total_paying;
                    $(appended).find('input.payment-amount').focus();
                    $(appended)
                        .find('input.payment-amount')
                        .last()
                        .val(__currency_trans_from_en(b_due, false))
                        .change()
                        .select();
                    __select2($(appended).find('.select2'));
                    $(appended)
                        .find('#method_' + row_index)
                        .change();
                    $('#payment_row_index').val(parseInt(row_index) + 1);
                }
            },
        });
    });

    $(document).on('click', '.remove_payment_row', function () {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $(this).closest('.payment_row').remove();
                calculate_balance_due();
            }
        });
    });

    pos_form_validator = pos_form_obj.validate({
        submitHandler: function (form) {
            // var total_payble = __read_number($('input#final_total_input'));
            // var total_paying = __read_number($('input#total_paying_input'));
            var cnf = true;

            //Ignore if the difference is less than 0.5
            if ($('input#in_balance_due').val() >= 0.5) {
                cnf = confirm(LANG.paid_amount_is_less_than_payable);
            }

            var total_advance_payments = 0;
            $('#payment_rows_div')
                .find('select.payment_types_dropdown')
                .each(function () {
                    if ($(this).val() == 'advance') {
                        total_advance_payments++;
                    }
                });

            if (total_advance_payments > 1) {
                alert(LANG.advance_payment_cannot_be_more_than_once);
                return false;
            }

            // Helper to actually submit the POS form via AJAX
            function finalizePosSubmission() {
                if (!cnf) {
                    return false;
                }
                disable_pos_form_actions();

                var data = $(form).serialize();
                data = data + '&status=final';
                var url = $(form).attr('action');
                $.ajax({
                    method: 'POST',
                    url: url,
                    data: data,
                    dataType: 'json',
                    success: function (result) {
                        if (result.success == 1) {
                            if (result.whatsapp_link) {
                                window.open(result.whatsapp_link);
                            }
                            $('#modal_payment').modal('hide');
                            toastr.success(result.msg);

                            reset_pos_form();

                            //Check if enabled or not
                            if (result.receipt.is_enabled) {
                                pos_print(result.receipt);
                            }
                        } else {
                            toastr.error(result.msg);
                        }

                        enable_pos_form_actions();
                    },
                });
            }

            var is_msp_valid = true;
            var has_cost_price_violation = false;
            var cost_price_violation_rows = [];

            // Clear all existing validation errors before checking
            $('label.error').remove();
            $('.error').removeClass('error');

            // Validate minimum selling price based on MSP setting
            if ($('#enable_msp_enabled').length > 0) {
                // When MSP is enabled, allow below-cost but require confirmation
                $('.pos_unit_price_inc_tax, .pos_unit_price').each(function () {
                    if ($(this).data('cost-price')) {
                        var tr = $(this).closest('tr');
                        var val = __read_number($(this));
                        var cost_price = parseFloat($(this).data('cost-price'));

                        if (val < cost_price) {
                            has_cost_price_violation = true;
                            cost_price_violation_rows.push(tr);
                        }
                        // Clear any inline errors
                        var error_msg_td = tr.find('.pos_line_total_text').closest('td');
                        error_msg_td.find('label.error').remove();
                    }
                });

                if (has_cost_price_violation) {
                    // Use SweetAlert confirmation for consistent UI
                    swal({
                        title: LANG.cost_price_warning_title,
                        text: LANG.cost_price_warning_message,
                        icon: 'warning',
                        dangerMode: true,
                        buttons: {
                            cancel: LANG.cost_price_warning_cancel,
                            confirm: {
                                text: LANG.cost_price_warning_accept,
                                closeModal: true,
                            },
                        },
                    }).then(function (willAccept) {
                        if (willAccept) {
                            finalizePosSubmission();
                        }
                    });

                    return false; // pause submission until user decides
                }
            } else {
                // If MSP is disabled, enforce cost price validation
                $('.pos_unit_price_inc_tax, .pos_unit_price').each(function () {
                    if ($(this).data('cost-price')) {
                        var tr = $(this).closest('tr');
                        var val = __read_number($(this));
                        var cost_price = parseFloat($(this).data('cost-price'));

                        if (val < cost_price) {
                            is_msp_valid = false;
                            cost_price_violation_rows.push(tr);
                            var error_msg_td = tr.find('.pos_line_total_text').closest('td');
                            error_msg_td.find('label.error').remove();
                            error_msg_td.append(
                                '<label class="error">' + $(this).data('msg-min-value') + '</label>'
                            );
                        } else {
                            var error_msg_td = tr.find('.pos_line_total_text').closest('td');
                            error_msg_td.find('label.error').remove();
                        }
                    }
                });

                if (!is_msp_valid) {
                    return false;
                }
            }

            // No violations or user accepted – continue
            finalizePosSubmission();
            return false;
        },
    });

    $(document).on('keyup', '.payment-amount', function () {
        calculate_balance_due();
        __write_number($(this), $(this).val());
    });
    $(document).on('keyup', '#expense_final_total', function () {
        __write_number($(this), $(this).val());
    });

    //Update discount
    $('button#posEditDiscountModalUpdate').click(function () {
        //if discount amount is not valid return false
        if (!$('#discount_amount_modal').valid()) {
            return false;
        }
        //Close modal
        $('div#posEditDiscountModal').modal('hide');

        //Update values
        $('input#discount_type').val($('select#discount_type_modal').val());
        __write_number($('input#discount_amount'), __read_number($('input#discount_amount_modal')));

        if ($('#reward_point_enabled').length) {
            var reward_validation = isValidatRewardPoint();
            if (!reward_validation['is_valid']) {
                toastr.error(reward_validation['msg']);
                $('#rp_redeemed_modal').val(0);
                $('#rp_redeemed_modal').change();
            }
            updateRedeemedAmount();
        }

        pos_total_row();
    });

    //Shipping
    $('button#posShippingModalUpdate').click(function () {
        //Close modal
        $('div#posShippingModal').modal('hide');

        //update shipping details
        $('input#shipping_details').val($('#shipping_details_modal').val());

        $('input#shipping_address').val($('#shipping_address_modal').val());
        $('input#shipping_status').val($('#shipping_status_modal').val());
        $('input#delivered_to').val($('#delivered_to_modal').val());
        $('input#delivery_person').val($('#delivery_person_modal').val());

        //Update shipping charges
        __write_number(
            $('input#shipping_charges'),
            __read_number($('input#shipping_charges_modal'))
        );

        //$('input#shipping_charges').val(__read_number($('input#shipping_charges_modal')));

        pos_total_row();
    });

    $('#posShippingModal').on('shown.bs.modal', function () {
        $('#posShippingModal')
            .find('#shipping_details_modal')
            .filter(':visible:first')
            .focus()
            .select();
        // $('.select2-selection__rendered').css('padding-right', '150px');
    });

    $(document).on('shown.bs.modal', '.row_edit_product_price_model', function () {
        $('.row_edit_product_price_model').find('input').filter(':visible:first').focus().select();
    });

    //Update Order tax
    $('button#posEditOrderTaxModalUpdate').click(function () {
        //Close modal
        $('div#posEditOrderTaxModal').modal('hide');

        var tax_obj = $('select#order_tax_modal');
        var tax_id = tax_obj.val();
        var tax_rate = tax_obj.find(':selected').data('rate');

        $('input#tax_rate_id').val(tax_id);

        __write_number($('input#tax_calculation_amount'), tax_rate);
        pos_total_row();
    });

    $(document).on('click', '.add_new_customer', function () {
        $('#customer_id').select2('close');
        var name = $(this).data('name');
        $('.contact_modal').find('input#name').val(name);
        $('.contact_modal')
            .find('select#contact_type')
            .val('customer')
            .closest('div.contact_type_div')
            .addClass('hide');
        $('.contact_modal').modal('show');
    });
    $('form#quick_add_contact')
        .submit(function (e) {
            e.preventDefault();
        })
        .validate({
            rules: {
                contact_id: {
                    remote: {
                        url: '/contacts/check-contacts-id',
                        type: 'post',
                        data: {
                            contact_id: function () {
                                return $('#contact_id').val();
                            },
                            hidden_id: function () {
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
            submitHandler: function (form) {
                $.ajax({
                    method: 'POST',
                    url: base_path + '/check-mobile',
                    dataType: 'json',
                    data: {
                        contact_id: function () {
                            return $('#hidden_id').val();
                        },
                        mobile_number: function () {
                            return $('#mobile').val();
                        },
                    },
                    beforeSend: function (xhr) {
                        __disable_submit_button($(form).find('button[type="submit"]'));
                    },
                    success: function (result) {
                        if (result.is_mobile_exists == true) {
                            swal({
                                title: LANG.sure,
                                text: result.msg,
                                icon: 'warning',
                                buttons: true,
                                dangerMode: true,
                            }).then((willContinue) => {
                                if (willContinue) {
                                    submitQuickContactForm(form);
                                } else {
                                    $('#mobile').select();
                                }
                            });
                        } else {
                            submitQuickContactForm(form);
                        }
                    },
                });
            },
        });
    $('.contact_modal').on('hidden.bs.modal', function () {
        $('form#quick_add_contact').find('button[type="submit"]').removeAttr('disabled');
        $('form#quick_add_contact')[0].reset();
    });

    //Updates for add sell
    $(
        'select#discount_type, input#discount_amount, input#shipping_charges, \
        input#rp_redeemed_amount'
    ).change(function () {
        pos_total_row();
    });
    $('select#tax_rate_id').change(function () {
        var tax_rate = $(this).find(':selected').data('rate');
        __write_number($('input#tax_calculation_amount'), tax_rate);
        pos_total_row();
    });
    //Datetime picker
    $('#transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });

    //Direct sell submit
    sell_form = $('form#add_sell_form');
    if ($('form#edit_sell_form').length) {
        sell_form = $('form#edit_sell_form');
        pos_total_row();
    }
    sell_form_validator = sell_form.validate();

    $('button#submit-sell, button#save-and-print').click(function (e) {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        var is_msp_valid = true;
        var has_cost_price_violation = false;
        var cost_price_violation_rows = [];

        //Validate minimum selling price if enable_msp exists and value is 1
        if ($('#enable_msp_enabled').length > 0) {
            $('.pos_unit_price_inc_tax, .pos_unit_price').each(function () {
                if ($(this).data('cost-price')) {
                    var val = __read_number($(this));
                    var cost_price = parseFloat($(this).data('cost-price'));

                    if (val < cost_price) {
                        has_cost_price_violation = true;
                        cost_price_violation_rows.push($(this).closest('tr'));

                        // Remove validation error on the input if present (modal will handle)
                        var error_msg_td = $(this)
                            .closest('tr')
                            .find('.pos_line_total_text')
                            .closest('td');
                        error_msg_td.find('label.error').remove();
                    } else {
                        // Remove error message if valid
                        var error_msg_td = $(this)
                            .closest('tr')
                            .find('.pos_line_total_text')
                            .closest('td');
                        error_msg_td.find('label.error').remove();
                    }
                }
            });

            if (has_cost_price_violation) {
                // SweetAlert confirmation
                swal({
                    title: LANG.cost_price_warning_title,
                    text: LANG.cost_price_warning_message,
                    icon: 'warning',
                    dangerMode: true,
                    buttons: {
                        cancel: LANG.cost_price_warning_cancel,
                        confirm: {
                            text: LANG.cost_price_warning_accept,
                            closeModal: true,
                        },
                    },
                }).then(function (willAccept) {
                    if (willAccept) {
                        // Clear any inline errors (if any)
                        $('.pos_unit_price_inc_tax, .pos_unit_price').each(function () {
                            var error_msg_td = $(this)
                                .closest('tr')
                                .find('.pos_line_total_text')
                                .closest('td');
                            error_msg_td.find('label.error').remove();
                        });
                        submitPosFormFromValidator();
                    }
                });
                return false;
            }
        } else {
            // If enable_msp doesn't exist or value is empty, show validation errors and prevent submission
            $('.pos_unit_price_inc_tax, .pos_unit_price').each(function () {
                if ($(this).data('cost-price')) {
                    var val = __read_number($(this));
                    var cost_price = parseFloat($(this).data('cost-price'));

                    if (val < cost_price) {
                        is_msp_valid = false;
                        var error_msg_td = $(this)
                            .closest('tr')
                            .find('.pos_line_total_text')
                            .closest('td');
                        error_msg_td.find('label.error').remove();
                        error_msg_td.append(
                            '<label class="error">' + $(this).data('msg-min-value') + '</label>'
                        );
                    } else {
                        var error_msg_td = $(this)
                            .closest('tr')
                            .find('.pos_line_total_text')
                            .closest('td');
                        error_msg_td.find('label.error').remove();
                    }
                }
            });

            if (!is_msp_valid) {
                toastr.error(LANG.cost_price_validation_error);
                return false;
            }
        }

        if ($(this).attr('id') == 'save-and-print') {
            $('#is_save_and_print').val(1);
        } else {
            $('#is_save_and_print').val(0);
        }

        if ($('#reward_point_enabled').length) {
            var validate_rp = isValidatRewardPoint();
            if (!validate_rp['is_valid']) {
                toastr.error(validate_rp['msg']);
                return false;
            }
        }

        if ($('.enable_cash_denomination_for_payment_methods').length) {
            var payment_row = $('.enable_cash_denomination_for_payment_methods').closest(
                '.payment_row'
            );
            var is_valid = true;
            var payment_type = payment_row.find('.payment_types_dropdown').val();
            var denomination_for_payment_types = JSON.parse(
                $('.enable_cash_denomination_for_payment_methods').val()
            );
            if (
                denomination_for_payment_types.includes(payment_type) &&
                payment_row.find('.is_strict').length &&
                payment_row.find('.is_strict').val() === '1'
            ) {
                var payment_amount = __read_number(payment_row.find('.payment-amount'));
                var total_denomination = payment_row.find('input.denomination_total_amount').val();
                if (payment_amount != total_denomination) {
                    is_valid = false;
                }
            }

            if (!is_valid) {
                payment_row.find('.cash_denomination_error').removeClass('hide');
                toastr.error(payment_row.find('.cash_denomination_error').text());
                e.preventDefault();
                return false;
            } else {
                payment_row.find('.cash_denomination_error').removeClass('hide');
            }
        }

        if (sell_form.valid()) {
            window.onbeforeunload = null;
            $(this).attr('disabled', true);
            sell_form.submit();
        }
    });

    // Function to submit POS form after confirmation
    function submitPosForm() {
        if (sell_form.valid()) {
            window.onbeforeunload = null;
            $('button#submit-sell, button#save-and-print').attr('disabled', true);
            sell_form.submit();
        }
    }

    // Function to submit POS form after confirmation from validator
    function submitPosFormFromValidator() {
        var cnf = true;

        //Ignore if the difference is less than 0.5
        if ($('input#in_balance_due').val() >= 0.5) {
            cnf = confirm(LANG.paid_amount_is_less_than_payable);
        }

        if (cnf) {
            disable_pos_form_actions();

            // Clear all validation errors before submitting
            $('.pos_unit_price_inc_tax, .pos_unit_price').each(function () {
                var error_msg_td = $(this).closest('tr').find('.pos_line_total_text').closest('td');
                error_msg_td.find('label.error').remove();
            });

            // Submit the form directly without validation
            var form = $('#add_pos_sell_form')[0];
            form.submit();
        }
    }

    //REPAIR MODULE:check if repair module field is present send data to filter product
    var is_enabled_stock = null;
    if ($('#is_enabled_stock').length) {
        is_enabled_stock = $('#is_enabled_stock').val();
    }

    var device_model_id = null;
    if ($('#repair_model_id').length) {
        device_model_id = $('#repair_model_id').val();
    }

    //Show product list.
    get_product_suggestion_list(
        global_p_category_id,
        global_brand_id,
        $('input#location_id').val(),
        null,
        is_enabled_stock,
        device_model_id
    );

    $('select#select_location_id').on('change', function (e) {
        $('input#suggestion_page').val(1);
        // product suggestions will be reloaded by #price_group change; avoid duplicate calls here

        get_featured_products();
    });

    // on click sub category in category drawer
    $('.product_category').on('click', function (e) {
        global_p_category_id = $(this).data('value');
        $('input#suggestion_page').val(1);
        var location_id = $('input#location_id').val();
        if (location_id != '' || location_id != undefined) {
            get_product_suggestion_list(
                global_p_category_id,
                global_brand_id,
                $('input#location_id').val(),
                null
            );
        }
        get_featured_products();
        $('.overlay-category').trigger('click');
    });

    //  function for show sub category
    $('.main-category').on('click', function () {
        global_p_category_id = $(this).data('value');
        parent = $(this).data('parent');

        if (parent == 0) {
            get_product_suggestion_list(
                global_p_category_id,
                global_brand_id,
                $('input#location_id').val(),
                null
            );
            get_featured_products();
            $('.overlay-category').trigger('click');
        } else {
            var main_category = $(this).data('value');

            $('.main-category-div').hide();
            $('.' + main_category).fadeIn();
            $('.category_heading').text('Sub Category ' + $(this).data('name'));
            $('.category-back').fadeIn();
        }
    });

    // function for back button in category
    $('.category-back').on('click', function () {
        $('.main-category-div').fadeIn();
        $('.main-category-all').fadeIn();
        $('.all-sub-category').hide();
        $('.category-back').hide();
        $('.category_heading').text('Category');
    });

    // on click brand in brand drawer
    $('.product_brand').on('click', function (e) {
        global_brand_id = $(this).data('value');
        $('input#suggestion_page').val(1);
        var location_id = $('input#location_id').val();

        if (location_id != '' || location_id != undefined) {
            get_product_suggestion_list(
                global_p_category_id,
                global_brand_id,
                $('input#location_id').val(),
                null
            );
        }
        get_featured_products();
        $('.overlay-brand').trigger('click');
    });

    // close side bar

    $('.close-side-bar-category').on('click', function () {
        $('.overlay-category').trigger('click');
    });

    $('.close-side-bar-brand').on('click', function () {
        $('.overlay-brand').trigger('click');
    });

    // When clicking a product card in POS, add the row at the TOP of the cart
    // Use a transient flag to limit behavior only to this interaction
    $(document).on('click', 'div.product_box', function () {
        //Check if location is not set then show error message.
        if ($('input#location_id').val() == '') {
            toastr.warning(LANG.select_location);
        } else {
            should_prepend_pos_row = true;
            pos_product_row($(this).data('variation_id'));
            try {
                var vIdClicked = $(this).data('variation_id');
                if (vIdClicked) {
                    updateSelectedQtyBadge(vIdClicked);
                }
            } catch (e) {}
        }
    });

    $(document).on('shown.bs.modal', '.row_description_modal', function () {
        $(this).find('textarea').first().focus();
    });

    //Press enter on search product to jump into last quantty and vice-versa
    $('#search_product').keydown(function (e) {
        var key = e.which;
        if (key == 9) {
            // the tab key code
            e.preventDefault();
            if ($('#pos_table tbody tr').length > 0) {
                $('#pos_table tbody tr:last').find('input.pos_quantity').focus().select();
            }
        }
    });
    $('#pos_table').on('keypress', 'input.pos_quantity', function (e) {
        var key = e.which;
        if (key == 13) {
            // the enter key code
            $('#search_product').focus();
        }
    });

    $('#exchange_rate').change(function () {
        var curr_exchange_rate = 1;
        if ($(this).val()) {
            curr_exchange_rate = __read_number($(this));
        }
        var total_payable = __read_number($('input#final_total_input'));
        var shown_total = total_payable * curr_exchange_rate;
        $('span#total_payable').text(__currency_trans_from_en(shown_total, false));
    });

    $('select#price_group').change(function () {
        $('input#hidden_price_group').val($(this).val());

        // Refresh product grid with new price group
        if ($('div#product_list_body').length > 0) {
            // Clear the product grid first
            $('div#product_list_body').html('');
            $('input#suggestion_page').val(1);
            get_product_suggestion_list(
                global_p_category_id,
                global_brand_id,
                $('input#location_id').val()
            );
        }
    });

    //Quick add product
    $(document).on('click', '.pos_add_quick_product', function () {
        var url = $(this).data('href');
        var container = $(this).data('container');
        $.ajax({
            url: url + '?product_for=pos',
            dataType: 'html',
            success: function (result) {
                $(container).html(result).modal('show');
                $('.os_exp_date').datepicker({
                    autoclose: true,
                    format: 'dd-mm-yyyy',
                    clearBtn: true,
                });
            },
        });
    });

    $(document).on('change', 'form#quick_add_product_form input#single_dpp', function () {
        var unit_price = __read_number($(this));
        $('table#quick_product_opening_stock_table tbody tr').each(function () {
            var input = $(this).find('input.unit_price');
            __write_number(input, unit_price);
            input.change();
        });
    });

    $(document).on('quickProductAdded', function (e) {
        //Check if location is not set then show error message.
        if ($('input#location_id').val() == '') {
            toastr.warning(LANG.select_location);
        } else {
            pos_product_row(e.variation.id);
        }
    });

    $('div.view_modal').on('show.bs.modal', function () {
        __currency_convert_recursively($(this));
    });

    $('table#pos_table').on('change', 'select.sub_unit', function () {
        var tr = $(this).closest('tr');
        var base_unit_selling_price = tr.find('input.hidden_base_unit_sell_price').val();
        var base_unit_selling_price_inc_tax = tr
            .find('input.hidden_base_unit_sell_price_inc_tax')
            .val();

        var selected_option = $(this).find(':selected');

        var multiplier = parseFloat(selected_option.data('multiplier'));
        if (!selected_option.val()) {
            // If placeholder selected, revert to base unit behavior and restore original price
            multiplier = 1;

            // Restore original base unit selling price
            var sp_element = tr.find('input.pos_unit_price');
            __write_number(sp_element, base_unit_selling_price);
            sp_element.change();

            // Restore cost price validation rules for base unit
            var cost_price = tr.find('input.pos_unit_price').data('cost-price');
            if (cost_price) {
                tr.find('input.pos_unit_price, input.pos_unit_price_inc_tax').each(function () {
                    $(this).attr('data-rule-min-value', cost_price);
                    $(this).attr(
                        'data-msg-min-value',
                        LANG.cost_price_validation_error.replace(':cost_price', cost_price)
                    );
                });
            }

            // Update cost price data attributes to use base unit cost price
            updateCostPriceDataAttributes(tr, selected_option);

            // Remove any cost price warnings
            tr.find('.cost_price_warning').remove();

            return;
        }

        var allow_decimal = parseInt(selected_option.data('allow_decimal')) || 1;

        tr.find('input.base_unit_multiplier').val(multiplier);

        // If specific price is provided for the selected unit, use it; otherwise fallback to the current product amount in POS (keep user price),
        // and if that's empty then use base * multiplier
        var opt_price = selected_option.data('price');
        var parsed_opt = parseFloat(opt_price);
        var current_price = __read_number(tr.find('input.pos_unit_price'));

        // Debug logging
        console.log('Selected option:', selected_option.val());
        console.log('Option price data:', opt_price);
        console.log('Parsed price:', parsed_opt);
        console.log('Current price:', current_price);
        console.log('Base unit selling price:', base_unit_selling_price);
        console.log('Multiplier:', multiplier);

        var unit_sp =
            !isNaN(parsed_opt) && parsed_opt > 0
                ? parsed_opt
                : current_price || base_unit_selling_price * multiplier;

        console.log('Final unit selling price:', unit_sp);

        var sp_element = tr.find('input.pos_unit_price');
        var price_inc_tax_input = tr.find('input.pos_unit_price_inc_tax');

        // If sub-unit defines its own price, treat it as tax-inclusive and use directly
        if (!isNaN(parsed_opt) && parsed_opt > 0) {
            // Write tax-inclusive price directly to the Precio field
            __write_number(price_inc_tax_input, unit_sp, false);
            // Sync any duplicate inputs in the row
            tr.find('input.pos_unit_price_inc_tax').val(price_inc_tax_input.val());

            // Back-calculate base price from tax-inclusive using current tax rate
            var tax_rate = tr.find('select.tax_id').find(':selected').data('rate');
            var discounted_unit_price = __get_principle(unit_sp, tax_rate);
            var unit_price = get_unit_price_from_discounted_unit_price(tr, discounted_unit_price);
            __write_number(sp_element, unit_price);

            // Update line total and text
            var quantity = __read_number(tr.find('input.pos_quantity'));
            var line_total = quantity * unit_sp;
            __write_number(tr.find('input.pos_line_total'), line_total, false);
            tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));

            // Recalculate row taxes/discounts and grand totals
            pos_each_row(tr);
            pos_total_row();
        } else {
            // Fallback to existing behavior (base price path)
            __write_number(sp_element, unit_sp);
            sp_element.change();
        }

        // Check if sub-unit has a specific cost price
        var sub_unit_cost_price = selected_option.data('cost-price');
        if (sub_unit_cost_price && sub_unit_cost_price !== '') {
            // If sub-unit has a cost price, use it for validation (unless enable_msp is enabled)
            if ($('#enable_msp_enabled').length === 0) {
                var cost_price = parseFloat(sub_unit_cost_price);
                tr.find('input.pos_unit_price, input.pos_unit_price_inc_tax').each(function () {
                    $(this).attr('data-rule-min-value', cost_price);
                    $(this).attr(
                        'data-msg-min-value',
                        LANG.cost_price_validation_error.replace(':cost_price', cost_price)
                    );
                });
            } else {
                // If enable_msp is enabled, remove validation rules to allow any price
                tr.find('input.pos_unit_price, input.pos_unit_price_inc_tax').each(function () {
                    $(this).removeAttr('data-rule-min-value');
                    $(this).removeAttr('data-msg-min-value');
                });
            }
        } else {
            // When sub-unit is selected but has no specific cost price
            // Only remove validation if enable_msp is enabled; otherwise, keep base unit cost price validation
            if ($('#enable_msp_enabled').length > 0) {
                // If enable_msp is enabled, remove validation rules to allow any price
                tr.find('input.pos_unit_price, input.pos_unit_price_inc_tax').each(function () {
                    $(this).removeAttr('data-rule-min-value');
                    $(this).removeAttr('data-msg-min-value');
                });
            } else {
                // If enable_msp is NOT enabled, keep the base unit cost price validation
                var base_cost_price = tr.find('input.pos_unit_price').data('cost-price');
                if (base_cost_price) {
                    // Calculate the sub-unit cost price based on multiplier
                    var sub_unit_calculated_cost = base_cost_price * multiplier;
                    tr.find('input.pos_unit_price, input.pos_unit_price_inc_tax').each(function () {
                        $(this).attr('data-rule-min-value', sub_unit_calculated_cost);
                        $(this).attr(
                            'data-msg-min-value',
                            LANG.cost_price_validation_error.replace(
                                ':cost_price',
                                sub_unit_calculated_cost
                            )
                        );
                    });
                }
            }
        }

        // Update cost price data attributes
        updateCostPriceDataAttributes(tr, selected_option);

        // Only clear validation errors if enable_msp is enabled
        if ($('#enable_msp_enabled').length > 0) {
            // Remove any existing validation errors
            tr.find('label.error').remove();
            tr.find('.error').removeClass('error');

            // Remove any cost price warnings
            tr.find('.cost_price_warning').remove();

            // Clear any validation errors in the line total area
            var error_msg_td = tr.find('.pos_line_total_text').closest('td');
            error_msg_td.find('label.error').remove();
        }

        var qty_element = tr.find('input.pos_quantity');
        var base_max_avlbl = qty_element.data('qty_available');
        var error_msg_line = 'pos_max_qty_error';

        if (tr.find('select.lot_number').length > 0) {
            var lot_select = tr.find('select.lot_number');
            if (lot_select.val()) {
                base_max_avlbl = lot_select.find(':selected').data('qty_available');
                error_msg_line = 'lot_max_qty_error';
            }
        }

        qty_element.attr('data-decimal', allow_decimal);
        var abs_digit = true;
        if (allow_decimal) {
            abs_digit = false;
        }
        qty_element.rules('add', {
            abs_digit: abs_digit,
        });

        if (base_max_avlbl) {
            var max_avlbl = parseFloat(base_max_avlbl) / multiplier;
            var formated_max_avlbl = __number_f(max_avlbl);
            var unit_name = selected_option.data('unit_name');
            var max_err_msg = __translate(error_msg_line, {
                max_val: formated_max_avlbl,
                unit_name: unit_name,
            });
            qty_element.attr('data-rule-max-value', max_avlbl);
            qty_element.attr('data-msg-max-value', max_err_msg);
            qty_element.rules('add', {
                'max-value': max_avlbl,
                messages: {
                    'max-value': max_err_msg,
                },
            });
            qty_element.trigger('change');
        }
        adjustComboQty(tr);
    });

    //Confirmation before page load.
    window.onbeforeunload = function () {
        console.log('onbeforeunload called');
        // if ($('form#edit_pos_sell_form').length == 0) {
        //     if ($('table#pos_table tbody tr').length > 0) {
        //         return LANG.sure;
        //     } else {
        //         return null;
        //     }
        // }
    };
    $(window).resize(function () {
        var win_height = $(window).height();
        div_height = __calculate_amount('percentage', 63, win_height);
        // $('div.pos_product_div').css('min-height', div_height + 'px');
        // $('div.pos_product_div').css('max-height', div_height + 'px');
    });

    //Used for weighing scale barcode
    $('#weighing_scale_modal').on('shown.bs.modal', function (e) {
        //Attach the scan event
        onScan.attachTo(document, {
            suffixKeyCodes: [13], // enter-key expected at the end of a scan
            reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
            onScan: function (sCode, iQty) {
                console.log('Scanned: ' + iQty + 'x ' + sCode);
                $('input#weighing_scale_barcode').val(sCode);
                $('button#weighing_scale_submit').trigger('click');
            },
            onScanError: function (oDebug) {
                console.log(oDebug);
            },
            minLength: 2,
            // onKeyDetect: function(iKeyCode){ // output all potentially relevant key events - great for debugging!
            //     console.log('Pressed: ' + iKeyCode);
            // }
        });

        $('input#weighing_scale_barcode').focus();
    });

    $('#weighing_scale_modal').on('hide.bs.modal', function (e) {
        //Detach from the document once modal is closed.
        onScan.detachFrom(document);
    });

    $('button#weighing_scale_submit').click(function () {
        var price_group = '';
        if ($('#price_group').length > 0) {
            price_group = $('#price_group').val();
        }

        if ($('#weighing_scale_barcode').val().length > 0) {
            pos_product_row(null, null, $('#weighing_scale_barcode').val());
            $('#weighing_scale_modal').modal('hide');
            $('input#weighing_scale_barcode').val('');
        } else {
            $('input#weighing_scale_barcode').focus();
        }
    });

    $('#show_featured_products').click(function () {
        if (!$('#featured_products_box').is(':visible')) {
            $('#featured_products_box').fadeIn();
        } else {
            $('#featured_products_box').fadeOut();
        }
    });
    validate_discount_field();
    set_payment_type_dropdown();
    if ($('#__is_mobile').length) {
        $('.pos_form_totals').css('margin-bottom', $('.pos-form-actions').height() - 30);
    }

    setInterval(function () {
        if ($('span.curr_datetime').length) {
            $('span.curr_datetime').html(__current_datetime());
        }
    }, 60000);

    set_search_fields();

    // Initialize cost price validation for existing rows when page loads (if enable_msp exists and value is 1)
    if ($('#enable_msp_enabled').length > 0) {
        setTimeout(function () {
            $('table#pos_table tbody tr.product_row').each(function () {
                checkCostPriceValidation($(this));
            });
        }, 500); // Wait a bit for the page to fully load
    }
});

function set_payment_type_dropdown() {
    var payment_settings = $('#location_id').data('default_payment_accounts');
    payment_settings = payment_settings ? payment_settings : [];
    enabled_payment_types = [];
    for (var key in payment_settings) {
        if (payment_settings[key] && payment_settings[key]['is_enabled']) {
            enabled_payment_types.push(key);
        }
    }
    if (enabled_payment_types.length) {
        $('.payment_types_dropdown > option').each(function () {
            //skip if advance
            if ($(this).val() && $(this).val() != 'advance') {
                if (enabled_payment_types.indexOf($(this).val()) != -1) {
                    $(this).removeClass('hide');
                } else {
                    $(this).addClass('hide');
                }
            }
        });
    }
}

function get_featured_products() {
    var location_id = $('#location_id').val();
    if (location_id && $('#featured_products_box').length > 0) {
        $.ajax({
            method: 'GET',
            url: '/sells/pos/get-featured-products/' + location_id,
            dataType: 'html',
            success: function (result) {
                if (result) {
                    $('#feature_product_div').removeClass('hide');
                    $('#featured_products_box').html(result);
                } else {
                    $('#feature_product_div').addClass('hide');
                    $('#featured_products_box').html('');
                }
            },
        });
    } else {
        $('#feature_product_div').addClass('hide');
        $('#featured_products_box').html('');
    }
}

var __pos_suggestion_xhr = null;

function get_product_suggestion_list(
    category_id,
    brand_id,
    location_id,
    url = null,
    is_enabled_stock = null,
    repair_model_id = null
) {
    if ($('div#product_list_body').length == 0) {
        return false;
    }

    if (url == null) {
        url = '/sells/pos/get-product-suggestion';
    }
    $('#suggestion_page_loader').fadeIn(700);
    var page = $('input#suggestion_page').val();
    if (page == 1) {
        $('div#product_list_body').html('');
    }
    if ($('div#product_list_body').find('input#no_products_found').length > 0) {
        $('#suggestion_page_loader').fadeOut(700);
        return false;
    }

    // determine price group consistent with cart logic
    // var price_group = '';
    // if ($('#price_group').length > 0) {
    //     price_group = parseInt($('#price_group').val());
    // }
    // if ($('#default_price_group').length > 0 && (price_group === '' || isNaN(price_group))) {
    //     price_group = $('#default_price_group').val();
    // }
    // if ($('#types_of_service_price_group').length > 0 && $('#types_of_service_price_group').val()) {
    //     price_group = $('#types_of_service_price_group').val();
    // }

    var customer_id = $('select#customer_id').val();

    // console.log('Sending product suggestion request with:', {
    //     location_id: location_id,
    //     customer_id: customer_id,
    //     price_group: price_group
    // });

    var price_group = '';
    if ($('#price_group').length > 0) {
        price_group = $('#price_group').val();
    }

    var ajax_data = {
        category_id: category_id,
        brand_id: brand_id,
        location_id: location_id,
        page: page,
        is_enabled_stock: is_enabled_stock,
        repair_model_id: repair_model_id,
        // // new params to compute effective suggestion prices
        customer_id: customer_id,
        // price_group: price_group,
        term: $('#search_product').length ? $('#search_product').val() : '',
    };

    // Always send price_group explicitly (including '0') to avoid mixed caches
    if (typeof price_group !== 'undefined') {
        ajax_data.price_group = price_group;
    }

    // Abort any in-flight suggestions request before starting a new one
    try {
        if (__pos_suggestion_xhr && __pos_suggestion_xhr.readyState !== 4) {
            __pos_suggestion_xhr.abort();
        }
    } catch (e) {}

    __pos_suggestion_xhr = $.ajax({
        method: 'GET',
        url: url,
        data: ajax_data,
        dataType: 'html',
        success: function (result) {
            $('div#product_list_body').append(result);
            $('.create-product-button').not(':first').remove();
            $('#suggestion_page_loader').fadeOut(700);
            // After rendering suggestions, update all badges to reflect current cart quantities
            try {
                if (typeof updateAllProductQtyBadges === 'function') {
                    updateAllProductQtyBadges();
                }
            } catch (e) {}
            updateAllSelectedQtyBadges();
        },
        complete: function () {
            __pos_suggestion_xhr = null;
        },
        error: function (xhr, status, error) {
            console.error('Product suggestion error:', error);
            $('#suggestion_page_loader').fadeOut(700);
        },
    });
}

//Get recent transactions
function get_recent_transactions(status, element_obj) {
    if (element_obj.length == 0) {
        return false;
    }
    var transaction_sub_type = $('#transaction_sub_type').val();
    $.ajax({
        method: 'GET',
        url: '/sells/pos/get-recent-transactions',
        data: { status: status, transaction_sub_type: transaction_sub_type },
        dataType: 'html',
        success: function (result) {
            element_obj.html(result);
            __currency_convert_recursively(element_obj);
        },
    });
}

//variation_id is null when weighing_scale_barcode is used.
function pos_product_row(
    variation_id = null,
    purchase_line_id = null,
    weighing_scale_barcode = null,
    quantity = 1
) {
    //Get item addition method
    var item_addtn_method = 1; // Default to 1 (increment existing) instead of 0 (always add new)
    var add_via_ajax = true;

    if (variation_id != null && $('#item_addition_method').length) {
        item_addtn_method = $('#item_addition_method').val();
    }

    // Force increment mode for product clicks (ignore database setting)
    if (variation_id != null) {
        item_addtn_method = 1;
    }

    if (item_addtn_method == 0) {
        add_via_ajax = true;
    } else {
        var is_added = false;
        var currentLocationId = $('#location_id').val();

        //Search for variation id in each row of pos table
        $('#pos_table tbody')
            .find('tr')
            .each(function () {
                var row_v_id = $(this).find('.row_variation_id').val();
                var enable_sr_no = $(this).find('.enable_sr_no').val();
                var modifiers_exist = false;
                if ($(this).find('input.modifiers_exist').length > 0) {
                    modifiers_exist = true;
                }

                var row_line_loc = $(this).find('input.line_location_id').val();
                if (
                    row_v_id == variation_id &&
                    String(row_line_loc) === String(currentLocationId) &&
                    enable_sr_no !== '1' &&
                    !modifiers_exist &&
                    !is_added
                ) {
                    add_via_ajax = false;
                    is_added = true;

                    //Increment product quantity
                    qty_element = $(this).find('.pos_quantity');
                    var qty = __read_number(qty_element);

                    var new_qty = qty + 1;
                    __write_number(qty_element, new_qty);

                    // Manually trigger the line total calculation
                    var tr = $(this);
                    var unit_price_inc_tax = __read_number(tr.find('input.pos_unit_price_inc_tax'));
                    var line_total = new_qty * unit_price_inc_tax;

                    console.log(
                        'Debug - Qty:',
                        new_qty,
                        'Unit Price:',
                        unit_price_inc_tax,
                        'Line Total:',
                        line_total
                    );

                    // Update line total without triggering keyup event
                    tr.find('input.pos_line_total').val(line_total);
                    tr.find('span.pos_line_total_text').text(
                        __currency_trans_from_en(line_total, true)
                    );

                    qty_element.change();

                    // Recalculate totals after quantity change
                    pos_total_row();

                    round_row_to_iraqi_dinnar($(this));

                    // Check if enable_msp exists and value is 1, then validate cost price for new product row
                    if ($('#enable_msp_enabled').length > 0) {
                        var currentRow = $(this);
                        setTimeout(function () {
                            checkCostPriceValidation(currentRow);
                        }, 100);
                    }

                    // Handle MSP validation rules for existing product row
                    handleMSPValidationRules();

                    $('input#search_product').focus().select();

                    // Update product badge for this variation
                    try {
                        if (typeof updateProductQtyBadge === 'function') {
                            updateProductQtyBadge(row_v_id);
                        }
                        updateSelectedQtyBadge(row_v_id);
                    } catch (e) {}
                }
            });
    }

    if (add_via_ajax) {
        var product_row = $('input#product_row_count').val();
        var location_id = $('input#location_id').val();
        var customer_id = $('select#customer_id').val();
        var is_direct_sell = false;
        if (
            $('input[name="is_direct_sale"]').length > 0 &&
            $('input[name="is_direct_sale"]').val() == 1
        ) {
            is_direct_sell = true;
        }

        var disable_qty_alert = false;

        if ($('#disable_qty_alert').length) {
            disable_qty_alert = true;
        }

        var is_sales_order =
            $('#sale_type').length && $('#sale_type').val() == 'sales_order' ? true : false;

        var price_group = '';
        if ($('#price_group').length > 0) {
            price_group = parseInt($('#price_group').val());
        }

        //If default price group present
        if ($('#default_price_group').length > 0 && price_group === '') {
            price_group = $('#default_price_group').val();
        }

        //If types of service selected give more priority
        if (
            $('#types_of_service_price_group').length > 0 &&
            $('#types_of_service_price_group').val()
        ) {
            price_group = $('#types_of_service_price_group').val();
        }

        var is_draft = false;
        if ($('#status') && ($('#status').val() == 'quotation' || $('#status').val() == 'draft')) {
            is_draft = true;
        }

        var is_serial_no = false;

        if (
            $('input[name="is_serial_no"]').length > 0 &&
            $('input[name="is_serial_no"]').val() == 1
        ) {
            is_serial_no = true;
        }

        // Capture insertion intent for this specific request and reset the global flag
        var insertAtTop = typeof should_prepend_pos_row !== 'undefined' && should_prepend_pos_row;
        should_prepend_pos_row = true;

        // FIXED: Detect edit mode for merging logic
        var isEditMode = $('form#edit_pos_sell_form').length > 0;

        $.ajax({
            method: 'GET',
            url: '/sells/pos/get_product_row/' + variation_id + '/' + location_id,
            async: false,
            data: {
                product_row: product_row,
                customer_id: customer_id,
                is_direct_sell: is_direct_sell,
                is_serial_no: is_serial_no,
                price_group: price_group,
                purchase_line_id: purchase_line_id,
                weighing_scale_barcode: weighing_scale_barcode,
                quantity: quantity,
                is_sales_order: is_sales_order,
                disable_qty_alert: disable_qty_alert,
                is_draft: is_draft,
            },
            dataType: 'json',
            success: function (result) {
                if (result.success) {
                    var currentLocationId = $('#select_location_id').val();
                    var $tbody = $('table#pos_table tbody');

                    // Original add-mode logic: Check same variation AND same location
                    var $existingRow = $tbody
                        .find('tr.product_row')
                        .filter(function () {
                            var vId = $(this).find('.row_variation_id').val();
                            var lineLoc = $(this).find('input.line_location_id').val();
                            return (
                                String(vId) === String(variation_id) &&
                                String(lineLoc) === String(currentLocationId)
                            );
                        })
                        .first();

                    if ($existingRow.length) {
                        // Increase quantity instead of adding a new row
                        var prevQty = __read_number($existingRow.find('input.pos_quantity')) || 0;
                        var addQty = __number_uf(quantity);
                        __write_number($existingRow.find('input.pos_quantity'), prevQty + addQty);

                        // In edit mode, ensure line_location_id is current (for consistency, but merge anyway)
                        if (isEditMode) {
                            $existingRow.find('input.line_location_id').val(currentLocationId);
                        }

                        pos_each_row($existingRow);
                        pos_total_row();
                        $('input#search_product').focus().select();
                        try {
                            updateSelectedQtyBadge(variation_id);
                        } catch (e) {}
                        return; // skip appending new row
                    }

                    // Append as a new row when no existing row with same location
                    if (insertAtTop) {
                        $tbody.prepend(result.html_content).find('input.pos_quantity');
                    } else {
                        $tbody.append(result.html_content).find('input.pos_quantity');
                    }
                    //increment row count
                    $('input#product_row_count').val(parseInt(product_row) + 1);
                    var this_row = $('table#pos_table tbody')
                        .find('tr')
                        [insertAtTop ? 'first' : 'last']();

                    // FIXED: Always set line_location_id to current for new rows (ensures future merges)
                    this_row.find('input.line_location_id').val(currentLocationId);

                    pos_each_row(this_row);

                    //For initial discount if present
                    var line_total = __read_number(this_row.find('input.pos_line_total'));
                    this_row.find('span.pos_line_total_text').text(line_total);

                    pos_total_row();

                    //Check if multipler is present then multiply it when a new row is added.
                    if (__getUnitMultiplier(this_row) > 1) {
                        this_row.find('select.sub_unit').trigger('change');
                    }

                    if (result.enable_sr_no == '1') {
                        var new_row = $('table#pos_table tbody')
                            .find('tr')
                            [insertAtTop ? 'first' : 'last']();
                        new_row.find('.row_edit_product_price_model').modal('show');
                    }

                    round_row_to_iraqi_dinnar(this_row);
                    __currency_convert_recursively(this_row);

                    // Check if enable_msp exists and value is 1, then validate cost price for new product row
                    if ($('#enable_msp_enabled').length > 0) {
                        setTimeout(function () {
                            checkCostPriceValidation(this_row);
                        }, 100);
                    }

                    // Handle MSP validation rules for new product row
                    handleMSPValidationRules();

                    $('input#search_product').focus().select();

                    //Used in restaurant module
                    if (result.html_modifier) {
                        $('table#pos_table tbody')
                            .find('tr')
                            [insertAtTop ? 'first' : 'last']()
                            .find('td:first')
                            .append(result.html_modifier);
                    }

                    // Scroll cart list: top if we prepended, else bottom
                    if (insertAtTop) {
                        $('.pos_product_div').animate({ scrollTop: 0 }, 300);
                    } else {
                        $('.pos_product_div').animate(
                            { scrollTop: $('.pos_product_div').prop('scrollHeight') },
                            1000
                        );
                    }

                    // Update product badge for this variation
                    try {
                        if (typeof updateProductQtyBadge === 'function') {
                            updateProductQtyBadge(variation_id);
                        }
                    } catch (e) {}

                    // Save cart to session after adding product
                    saveCartToSession();
                } else {
                    toastr.error(result.msg);
                    $('input#search_product').focus().select();
                }
            },
        });
    }

    // Save cart to session after incrementing quantity
    if (!add_via_ajax && is_added) {
        saveCartToSession();
    }
}

//Update values for each row
function pos_each_row(row_obj) {
    var unit_price = __read_number(row_obj.find('input.pos_unit_price'));

    var discounted_unit_price = calculate_discounted_unit_price(row_obj);
    var tax_rate = row_obj.find('select.tax_id').find(':selected').data('rate');

    var unit_price_inc_tax =
        discounted_unit_price + __calculate_amount('percentage', tax_rate, discounted_unit_price);
    // Respect user editing of Precio: never overwrite while input is focused/being edited
    // var priceIncTaxInputRow = row_obj.find('input.pos_unit_price_inc_tax');
    // if (!priceIncTaxInputRow.data('editing-price')) {
    //     __write_number(priceIncTaxInputRow, unit_price_inc_tax);
    // }

    var discount = __read_number(row_obj.find('input.row_discount_amount'));

    if (discount > 0) {
        var qty = __read_number(row_obj.find('input.pos_quantity'));
        var line_total = qty * unit_price_inc_tax;
        __write_number(row_obj.find('input.pos_line_total'), line_total);
    }

    //var unit_price_inc_tax = __read_number(row_obj.find('input.pos_unit_price_inc_tax'));

    __write_number(row_obj.find('input.item_tax'), unit_price_inc_tax - discounted_unit_price);

    // Location indicator (A..Z): assign letter per unique location order
    try {
        var allLocs = [];
        $('table#pos_table tbody tr').each(function () {
            var locVal = $(this).find('input.line_location_id').val();
            if (locVal) allLocs.push(String(locVal));
        });
        var uniqueLocs = Array.from(
            new Set(
                allLocs.map(function (v) {
                    return String(v);
                })
            )
        );
        // sort by ascending numeric location id for stable mapping
        uniqueLocs.sort(function (a, b) {
            return Number(a) - Number(b);
        });
        var hasMulti = uniqueLocs.length > 1;
        var thisLoc = String(row_obj.find('input.line_location_id').val() || '');
        var indicatorSpan = row_obj.find('span.pos-location-indicator');
        if (indicatorSpan.length === 0) {
            // Backfill in case DOM changed
            var nameContainer = row_obj.find('td').first();
            nameContainer.prepend('<span class="pos-location-indicator"></span>');
            indicatorSpan = row_obj.find('span.pos-location-indicator');
        }
        // if (hasMulti && thisLoc) {
        // Look up printable code from the location select (data-location-code) if available
        var code = '';
        var $sel = $('#select_location_id');
        // if ($sel.length) {
        var opt = $sel.find('option[value="' + thisLoc + '"]');
        code = opt.data('location-code') || '';
        // }
        if (code) {
            indicatorSpan.text('(' + code + ') ');
        } else {
            //     // If no code, show nothing per requirement
            indicatorSpan.text('');
        }
        // } else {
        //     indicatorSpan.text('');
        // }
    } catch (e) {
        // fail silently
    }
}

function pos_total_row() {
    var total_quantity = 0;
    var price_total = get_subtotal();
    $('table#pos_table tbody tr').each(function () {
        total_quantity = total_quantity + __read_number($(this).find('input.pos_quantity'));
    });

    //updating shipping charges
    $('span#shipping_charges_amount').text(
        __currency_trans_from_en(__read_number($('input#shipping_charges_modal')), false)
    );

    $('span.total_quantity').each(function () {
        $(this).html(__number_f(total_quantity));
    });

    //$('span.unit_price_total').html(unit_price_total);
    $('span.price_total').html(__currency_trans_from_en(price_total, false));
    calculate_billing_details(price_total);

    if ($('input[name="is_serial_no"]').length > 0 && $('input[name="is_serial_no"]').val() == 1) {
        update_serial_no();
    }
    // store on any update
    saveFormDataToLocalStorage();

    // Refresh location indicators across all rows whenever totals recompute
    $('table#pos_table tbody tr').each(function () {
        pos_each_row($(this));
    });
}

function get_subtotal() {
    var price_total = 0;

    $('table#pos_table tbody tr').each(function () {
        var line_total = __read_number($(this).find('input.pos_line_total'));
        price_total = price_total + line_total;
    });

    //Go through the modifier prices.
    $('input.modifiers_price').each(function () {
        var modifier_price = __read_number($(this));
        var modifier_quantity = $(this)
            .closest('.product_modifier')
            .find('.modifiers_quantity')
            .val();
        var modifier_subtotal = modifier_price * modifier_quantity;
        price_total = price_total + modifier_subtotal;
    });
    var discount = pos_discount(price_total);
    price_total = price_total - discount;
    return price_total;
}

function calculate_billing_details(price_total) {
    var discount = pos_discount(price_total);
    if ($('#reward_point_enabled').length) {
        total_customer_reward = $('#rp_redeemed_amount').val();
        discount = parseFloat(discount) + parseFloat(total_customer_reward);

        if ($('input[name="is_direct_sale"]').length <= 0) {
            $('span#total_discount').text(__currency_trans_from_en(discount, false));
        }
    }

    var order_tax = pos_order_tax(price_total, discount);

    //Add shipping charges.
    var shipping_charges = __read_number($('input#shipping_charges'));

    var additional_expense = 0;
    //calculate additional expenses
    if ($('input#additional_expense_value_1').length > 0) {
        additional_expense += __read_number($('input#additional_expense_value_1'));
    }
    if ($('input#additional_expense_value_2').length > 0) {
        additional_expense += __read_number($('input#additional_expense_value_2'));
    }
    if ($('input#additional_expense_value_3').length > 0) {
        additional_expense += __read_number($('input#additional_expense_value_3'));
    }
    if ($('input#additional_expense_value_4').length > 0) {
        additional_expense += __read_number($('input#additional_expense_value_4'));
    }

    //Add packaging charge
    var packing_charge = 0;
    if ($('#types_of_service_id').length > 0 && $('#types_of_service_id').val()) {
        packing_charge = __calculate_amount(
            $('#packing_charge_type').val(),
            __read_number($('input#packing_charge')),
            price_total
        );

        $('#packing_charge_text').text(__currency_trans_from_en(packing_charge, false));
    }

    var total_payable =
        price_total + order_tax + shipping_charges + packing_charge + additional_expense;

    var rounding_multiple = $('#amount_rounding_method').val()
        ? parseFloat($('#amount_rounding_method').val())
        : 0;
    var round_off_data = __round(total_payable, rounding_multiple);
    var total_payable_rounded = round_off_data.number;

    var round_off_amount = round_off_data.diff;
    if (round_off_amount != 0) {
        $('span#round_off_text').text(__currency_trans_from_en(round_off_amount, false));
    } else {
        $('span#round_off_text').text(0);
    }
    $('input#round_off_amount').val(round_off_amount);

    __write_number($('input#final_total_input'), total_payable_rounded);
    var curr_exchange_rate = 1;
    if ($('#exchange_rate').length > 0 && $('#exchange_rate').val()) {
        curr_exchange_rate = __read_number($('#exchange_rate'));
    }
    var shown_total = total_payable_rounded * curr_exchange_rate;
    $('span#total_payable').text(__currency_trans_from_en(shown_total, false));

    $('span.total_payable_span').text(__currency_trans_from_en(total_payable_rounded, true));

    //Check if edit form then don't update price.
    if ($('form#edit_pos_sell_form').length == 0 && $('form#edit_sell_form').length == 0) {
        __write_number($('.payment-amount'), total_payable_rounded);
    }

    $(document).trigger('invoice_total_calculated');

    calculate_balance_due();
}

function pos_discount(total_amount) {
    var calculation_type = $('#discount_type').val();
    var calculation_amount = __read_number($('#discount_amount'));

    var discount = __calculate_amount(calculation_type, calculation_amount, total_amount);

    $('span#total_discount').text(__currency_trans_from_en(discount, false));

    return discount;
}

function pos_order_tax(price_total, discount) {
    var tax_rate_id = $('#tax_rate_id').val();
    var calculation_type = 'percentage';
    var calculation_amount = __read_number($('#tax_calculation_amount'));
    var total_amount = price_total - discount;

    if (tax_rate_id) {
        var order_tax = __calculate_amount(calculation_type, calculation_amount, total_amount);
    } else {
        var order_tax = 0;
    }

    $('span#order_tax').text(__currency_trans_from_en(order_tax, false));

    return order_tax;
}

function calculate_balance_due() {
    var total_payable = __read_number($('#final_total_input'));
    var total_paying = 0;
    $('#payment_rows_div')
        .find('.payment-amount')
        .each(function () {
            if (parseFloat($(this).val())) {
                total_paying += __read_number($(this));
            }
        });
    var bal_due = total_payable - total_paying;
    var change_return = 0;

    //change_return
    if (bal_due < 0 || Math.abs(bal_due) < 0.05) {
        __write_number($('input#change_return'), bal_due * -1);
        $('span.change_return_span').text(__currency_trans_from_en(bal_due * -1, true));
        change_return = bal_due * -1;
        bal_due = 0;
    } else {
        __write_number($('input#change_return'), 0);
        $('span.change_return_span').text(__currency_trans_from_en(0, true));
        change_return = 0;
    }

    if (change_return !== 0) {
        $('#change_return_payment_data').removeClass('hide');
    } else {
        $('#change_return_payment_data').addClass('hide');
    }

    __write_number($('input#total_paying_input'), total_paying);
    $('span.total_paying').text(__currency_trans_from_en(total_paying, true));

    __write_number($('input#in_balance_due'), bal_due);
    $('span.balance_due').text(__currency_trans_from_en(bal_due, true));

    __highlight(bal_due * -1, $('span.balance_due'));
    __highlight(change_return * -1, $('span.change_return_span'));
    // store payment details
    saveFormDataToLocalStorage();
}

function isValidPosForm() {
    flag = true;
    $('span.error').remove();

    if ($('select#customer_id').val() == null) {
        flag = false;
        error = '<span class="error">' + LANG.required + '</span>';
        $(error).insertAfter($('select#customer_id').parent('div'));
    }

    if ($('tr.product_row').length == 0) {
        flag = false;
        error = '<span class="error">' + LANG.no_products + '</span>';
        $(error).insertAfter($('input#search_product').parent('div'));
    }

    return flag;
}

function reset_pos_form() {
    //If on edit page then redirect to Add POS page
    if ($('form#edit_pos_sell_form').length > 0) {
        setTimeout(function () {
            window.location = $('input#pos_redirect_url').val();
        }, 4000);
        return true;
    }

    //reset all repair defects tags
    if ($('#repair_defects').length > 0) {
        tagify_repair_defects.removeAllTags();
    }

    if (pos_form_obj[0]) {
        pos_form_obj[0].reset();
    }
    if (sell_form[0]) {
        sell_form[0].reset();
    }
    set_default_customer();
    set_location();

    $('tr.product_row').remove();
    $(
        'span.total_quantity, span.price_total, span#total_discount, span#order_tax, span#total_payable, span#shipping_charges_amount'
    ).text(0);
    $('span.total_payable_span', 'span.total_paying', 'span.balance_due').text(0);

    // Clear cart and form data from session
    clearCartSession();

    // Also clear form data session
    $.ajax({
        method: 'POST',
        url: '/sells/pos/clear-form-session',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        async: true
    });

    $('#modal_payment')
        .find('.remove_payment_row')
        .each(function () {
            $(this).closest('.payment_row').remove();
        });

    if ($('#is_credit_sale').length) {
        $('#is_credit_sale').val(0);
    }

    //Reset discount
    __write_number($('input#discount_amount'), $('input#discount_amount').data('default'));
    $('input#discount_type').val($('input#discount_type').data('default'));

    //Reset tax rate
    $('input#tax_rate_id').val($('input#tax_rate_id').data('default'));
    __write_number(
        $('input#tax_calculation_amount'),
        $('input#tax_calculation_amount').data('default')
    );

    $('select.payment_types_dropdown').val('cash').trigger('change');
    $('#price_group').trigger('change');

    //Reset shipping
    __write_number($('input#shipping_charges'), $('input#shipping_charges').data('default'));
    $('input#shipping_details').val($('input#shipping_details').data('default'));
    $('input#shipping_address, input#shipping_status, input#delivered_to').val('');
    if ($('input#is_recurring').length > 0) {
        $('input#is_recurring').iCheck('update');
    }
    if ($('input#is_kitchen_order').length > 0) {
        $('input#is_kitchen_order').iCheck('update');
    }
    if ($('#invoice_layout_id').length > 0) {
        $('#invoice_layout_id').trigger('change');
    }
    $('span#round_off_text').text(0);

    //repair module extra  fields reset
    if ($('#repair_device_id').length > 0) {
        $('#repair_device_id').val('').trigger('change');
    }

    //Status is hidden in sales order
    if ($('#status').length > 0 && $('#status').is(':visible')) {
        $('#status').val('').trigger('change');
    }
    if ($('#transaction_date').length > 0) {
        $('#transaction_date').data('DateTimePicker').date(moment());
    }
    if ($('.paid_on').length > 0) {
        $('.paid_on').data('DateTimePicker').date(moment());
    }
    if ($('#commission_agent').length > 0) {
        var default_commission_agent_id = $('#default_commission_agent_id').val();
        $('#commission_agent').val(default_commission_agent_id).trigger('change');
    }

    //reset contact due
    $('.contact_due_text').find('span').text('');
    $('.contact_due_text').addClass('hide');

    $(document).trigger('sell_form_reset');

    // Set global_is_clear_local_storage to true to clear local storage
    global_is_clear_local_storage = true;
    saveFormDataToLocalStorage();
}

function set_default_customer() {
    var default_customer_id = $('#default_customer_id').val();
    var default_customer_name = $('#default_customer_name').val();
    var default_customer_balance = $('#default_customer_balance').val();
    var default_customer_address = $('#default_customer_address').val();
    var exists = default_customer_id
        ? $('select#customer_id option[value=' + default_customer_id + ']').length
        : 0;
    if (exists == 0 && default_customer_id) {
        $('select#customer_id').append(
            $('<option>', { value: default_customer_id, text: default_customer_name })
        );
    }
    $('#advance_balance_text').text(__currency_trans_from_en(default_customer_balance), true);
    $('#advance_balance').val(default_customer_balance);
    $('#shipping_address_modal').val(default_customer_address);
    if (default_customer_address) {
        $('#shipping_address').val(default_customer_address);
    }
    $('select#customer_id').val(default_customer_id).trigger('change');

    if ($('#default_selling_price_group').length) {
        $('#price_group').val($('#default_selling_price_group').val());
        $('#price_group').change();
    }

    //initialize tags input (tagify)
    if ($('textarea#repair_defects').length > 0 && !customer_set) {
        let suggestions = [];
        if (
            $('input#pos_repair_defects_suggestion').length > 0 &&
            $('input#pos_repair_defects_suggestion').val().length > 2
        ) {
            suggestions = JSON.parse($('input#pos_repair_defects_suggestion').val());
        }
        let repair_defects = document.querySelector('textarea#repair_defects');
        tagify_repair_defects = new Tagify(repair_defects, {
            whitelist: suggestions,
            maxTags: 100,
            dropdown: {
                maxItems: 100, // <- mixumum allowed rendered suggestions
                classname: 'tags-look', // <- custom classname for this dropdown, so it could be targeted
                enabled: 0, // <- show suggestions on focus
                closeOnSelect: false, // <- do not hide the suggestions dropdown once an item has been selected
            },
        });
    }

    customer_set = true;
}

//Set the location and initialize printer
function set_location() {
    if ($('select#select_location_id').length == 1) {
        $('input#location_id').val($('select#select_location_id').val());
        $('input#location_id').data(
            'receipt_printer_type',
            $('select#select_location_id').find(':selected').data('receipt_printer_type')
        );
        $('input#location_id').data(
            'default_payment_accounts',
            $('select#select_location_id').find(':selected').data('default_payment_accounts')
        );

        $('input#location_id').attr(
            'data-default_price_group',
            $('select#select_location_id').find(':selected').data('default_price_group')
        );
    }

    if ($('input#location_id').val()) {
        $('input#search_product').prop('disabled', false).focus();
    } else {
        $('input#search_product').prop('disabled', true);
    }

    initialize_printer();
}

function initialize_printer() {
    if ($('input#location_id').data('receipt_printer_type') == 'printer') {
        initializeSocket();
    }
}

$('body').on('click', 'label', function (e) {
    var field_id = $(this).attr('for');
    if (field_id) {
        if ($('#' + field_id).hasClass('select2')) {
            $('#' + field_id).select2('open');
            return false;
        }
    }
});

$('body').on('focus', 'select', function (e) {
    var field_id = $(this).attr('id');
    if (field_id) {
        if ($('#' + field_id).hasClass('select2')) {
            $('#' + field_id).select2('open');
            return false;
        }
    }
});

function round_row_to_iraqi_dinnar(row) {
    if (iraqi_selling_price_adjustment) {
        var element = row.find('input.pos_unit_price_inc_tax');
        // Do not modify Precio while the user is editing
        if (!element.data('editing-price')) {
            var unit_price = round_to_iraqi_dinnar(__read_number(element));
            __write_number(element, unit_price);
            element.change();
        }
    }
}

function pos_print(receipt) {
    //If printer type then connect with websocket
    if (receipt.print_type == 'printer') {
        var content = receipt;
        content.type = 'print-receipt';

        //Check if ready or not, then print.
        if (socket != null && socket.readyState == 1) {
            socket.send(JSON.stringify(content));
        } else {
            initializeSocket();
            setTimeout(function () {
                socket.send(JSON.stringify(content));
            }, 700);
        }
    } else if (receipt.html_content != '') {
        var title = document.title;
        if (typeof receipt.print_title != 'undefined') {
            document.title = receipt.print_title;
        }

        //If printer type browser then print content
        $('#receipt_section').html(receipt.html_content);
        __currency_convert_recursively($('#receipt_section'));
        __print_receipt('receipt_section');

        setTimeout(function () {
            document.title = title;
        }, 1200);
    }
}

function calculate_discounted_unit_price(row) {
    var this_unit_price = __read_number(row.find('input.pos_unit_price'));
    var row_discounted_unit_price = this_unit_price;
    var row_discount_type = row.find('select.row_discount_type').val();
    var row_discount_amount = __read_number(row.find('input.row_discount_amount'));
    if (row_discount_amount) {
        if (row_discount_type == 'fixed') {
            row_discounted_unit_price = this_unit_price - row_discount_amount;
        } else {
            row_discounted_unit_price = __substract_percent(this_unit_price, row_discount_amount);
        }
    }

    return row_discounted_unit_price;
}

function get_unit_price_from_discounted_unit_price(row, discounted_unit_price) {
    var this_unit_price = discounted_unit_price;
    var row_discount_type = row.find('select.row_discount_type').val();
    var row_discount_amount = __read_number(row.find('input.row_discount_amount'));
    if (row_discount_amount) {
        if (row_discount_type == 'fixed') {
            this_unit_price = discounted_unit_price + row_discount_amount;
        } else {
            this_unit_price = __get_principle(discounted_unit_price, row_discount_amount, true);
        }
    }

    return this_unit_price;
}

//Update quantity if line subtotal changes
$('table#pos_table tbody').on('keyup', 'input.pos_line_total', function () {
    var subtotal = __read_number($(this));
    var tr = $(this).parents('tr');
    var quantity_element = tr.find('input.pos_quantity');
    var unit_price_inc_tax = __read_number(tr.find('input.pos_unit_price_inc_tax'));
    var quantity = subtotal / unit_price_inc_tax;
    __write_number(quantity_element, quantity);

    __write_number($(this), subtotal, false);

    if (sell_form_validator) {
        sell_form_validator.element(quantity_element);
    }
    if (pos_form_validator) {
        pos_form_validator.element(quantity_element);
    }
    tr.find('span.pos_line_total_text').text(__currency_trans_from_en(subtotal, true));

    pos_total_row();

    // Check if enable_msp exists and value is 1, then validate cost price
    if ($('#enable_msp_enabled').length > 0) {
        checkCostPriceValidation(tr);
    }
});

$('div#product_list_body').on('scroll', function () {
    if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
        var page = parseInt($('#suggestion_page').val());
        page += 1;
        $('#suggestion_page').val(page);
        var location_id = $('input#location_id').val();
        var category_id = global_p_category_id;
        var brand_id = global_brand_id;

        var is_enabled_stock = null;
        if ($('#is_enabled_stock').length) {
            is_enabled_stock = $('#is_enabled_stock').val();
        }

        var device_model_id = null;
        if ($('#repair_model_id').length) {
            device_model_id = $('#repair_model_id').val();
        }

        get_product_suggestion_list(
            category_id,
            brand_id,
            location_id,
            null,
            is_enabled_stock,
            device_model_id
        );
    }
});

$(document).on('ifChecked', '#is_recurring', function () {
    $('#recurringInvoiceModal').modal('show');
});

$(document).on('shown.bs.modal', '#recurringInvoiceModal', function () {
    $('input#recur_interval').focus();
});

$(document).on('click', '#select_all_service_staff', function () {
    var val = $('#res_waiter_id').val();
    $('#pos_table tbody')
        .find('select.order_line_service_staff')
        .each(function () {
            $(this).val(val).change();
        });
});

$(document).on('click', '.print-invoice-link', function (e) {
    e.preventDefault();
    $.ajax({
        url: $(this).attr('href') + '?check_location=true',
        dataType: 'json',
        success: function (result) {
            if (result.success == 1) {
                //Check if enabled or not
                if (result.receipt.is_enabled) {
                    pos_print(result.receipt);
                }
            } else {
                toastr.error(result.msg);
            }
        },
    });
});

function getCustomerRewardPoints() {
    if ($('#reward_point_enabled').length <= 0) {
        return false;
    }
    var is_edit =
        $('form#edit_sell_form').length || $('form#edit_pos_sell_form').length ? true : false;
    if (is_edit && !customer_set) {
        return false;
    }

    var customer_id = $('#customer_id').val();

    $.ajax({
        method: 'POST',
        url: '/sells/pos/get-reward-details',
        data: {
            customer_id: customer_id,
        },
        dataType: 'json',
        success: function (result) {
            $('#available_rp').text(result.points);
            $('#rp_redeemed_modal').data('max_points', result.points);
            updateRedeemedAmount();
            $('#rp_redeemed_amount').change();
        },
    });
}

function updateRedeemedAmount(argument) {
    var points = $('#rp_redeemed_modal').val().trim();
    points = points == '' ? 0 : parseInt(points);
    var amount_per_unit_point = parseFloat($('#rp_redeemed_modal').data('amount_per_unit_point'));
    var redeemed_amount = points * amount_per_unit_point;
    $('#rp_redeemed_amount_text').text(__currency_trans_from_en(redeemed_amount, true));
    $('#rp_redeemed').val(points);
    $('#rp_redeemed_amount').val(redeemed_amount);
}

$(document).on('change', 'select#customer_id', function () {
    var default_customer_id = $('#default_customer_id').val();
    if ($(this).val() == default_customer_id) {
        //Disable reward points for walkin customers
        if ($('#rp_redeemed_modal').length) {
            $('#rp_redeemed_modal').val('');
            $('#rp_redeemed_modal').change();
            $('#rp_redeemed_modal').attr('disabled', true);
            $('#available_rp').text('');
            updateRedeemedAmount();
            pos_total_row();
        }
    } else {
        if ($('#rp_redeemed_modal').length) {
            $('#rp_redeemed_modal').removeAttr('disabled');
        }
        getCustomerRewardPoints();
    }
    //  $('input#suggestion_page').val(1);
    //     var location_id = $('input#location_id').val();
    //     if (location_id != '' || location_id != undefined) {
    //         get_product_suggestion_list(
    //             global_p_category_id,
    //             global_brand_id,
    //             $('input#location_id').val(),
    //             null
    //         );
    //     }
    //     get_featured_products();

    get_sales_orders();
});

$(document).on('change', '#rp_redeemed_modal', function () {
    var points = $(this).val().trim();
    points = points == '' ? 0 : parseInt(points);
    var amount_per_unit_point = parseFloat($(this).data('amount_per_unit_point'));
    var redeemed_amount = points * amount_per_unit_point;
    $('#rp_redeemed_amount_text').text(__currency_trans_from_en(redeemed_amount, true));
    var reward_validation = isValidatRewardPoint();
    if (!reward_validation['is_valid']) {
        toastr.error(reward_validation['msg']);
        $('#rp_redeemed_modal').select();
    }
});

$(document).on('change', '.direct_sell_rp_input', function () {
    updateRedeemedAmount();
    pos_total_row();
});

function isValidatRewardPoint() {
    var element = $('#rp_redeemed_modal');
    var points = element.val().trim();
    points = points == '' ? 0 : parseInt(points);

    var max_points = parseInt(element.data('max_points'));
    var is_valid = true;
    var msg = '';

    if (points == 0) {
        return {
            is_valid: is_valid,
            msg: msg,
        };
    }

    var rp_name = $('input#rp_name').val();
    if (points > max_points) {
        is_valid = false;
        msg = __translate('max_rp_reached_error', {
            max_points: max_points,
            rp_name: rp_name,
        });
    }

    var min_order_total_required = parseFloat(element.data('min_order_total'));

    var order_total = __read_number($('#final_total_input'));

    if (order_total < min_order_total_required) {
        is_valid = false;
        msg = __translate('min_order_total_error', {
            min_order: __currency_trans_from_en(min_order_total_required, true),
            rp_name: rp_name,
        });
    }

    var output = {
        is_valid: is_valid,
        msg: msg,
    };

    return output;
}

function adjustComboQty(tr) {
    if (tr.find('input.product_type').val() == 'combo') {
        var qty = __read_number(tr.find('input.pos_quantity'));
        var multiplier = __getUnitMultiplier(tr);

        tr.find('input.combo_product_qty').each(function () {
            $(this).val($(this).data('unit_quantity') * qty * multiplier);
        });
    }
}

$(document).on('change', '#types_of_service_id', function () {
    var types_of_service_id = $(this).val();
    var location_id = $('#location_id').val();

    if (types_of_service_id) {
        $.ajax({
            method: 'POST',
            url: '/sells/pos/get-types-of-service-details',
            data: {
                types_of_service_id: types_of_service_id,
                location_id: location_id,
            },
            dataType: 'json',
            success: function (result) {
                //reset form if price group is changed
                var prev_price_group = $('#types_of_service_price_group').val();
                if (result.price_group_id) {
                    $('#types_of_service_price_group').val(result.price_group_id);
                    $('#price_group_text').removeClass('hide');
                    $('#price_group_text span').text(result.price_group_name);
                } else {
                    $('#types_of_service_price_group').val('');
                    $('#price_group_text').addClass('hide');
                    $('#price_group_text span').text('');
                }
                $('#types_of_service_id').val(types_of_service_id);
                $('.types_of_service_modal').html(result.modal_html);

                if (prev_price_group != result.price_group_id) {
                    if ($('form#edit_pos_sell_form').length > 0) {
                        $('table#pos_table tbody').html('');
                        pos_total_row();
                    } else {
                        reset_pos_form();
                    }
                } else {
                    pos_total_row();
                }

                $('.types_of_service_modal').modal('show');
            },
        });
    } else {
        $('.types_of_service_modal').html('');
        $('#types_of_service_price_group').val('');
        $('#price_group_text').addClass('hide');
        $('#price_group_text span').text('');
        $('#packing_charge_text').text('');
        if ($('form#edit_pos_sell_form').length > 0) {
            $('table#pos_table tbody').html('');
            pos_total_row();
        } else {
            reset_pos_form();
        }
    }
});

$(document).on(
    'change',
    'input#packing_charge, #additional_expense_value_1, #additional_expense_value_2, \
        #additional_expense_value_3, #additional_expense_value_4',
    function () {
        pos_total_row();
    }
);

$(document).on('click', '.service_modal_btn', function (e) {
    if ($('#types_of_service_id').val()) {
        $('.types_of_service_modal').modal('show');
    }
});

$(document).on('change', '.payment_types_dropdown', function (e) {
    var default_accounts = $('select#select_location_id').length
        ? $('select#select_location_id').find(':selected').data('default_payment_accounts')
        : $('#location_id').data('default_payment_accounts');
    var payment_type = $(this).val();
    var payment_row = $(this).closest('.payment_row');
    if (payment_type && payment_type != 'advance') {
        console.log(payment_type, '<<<<<<<<<<------------------->>>>>>>>', default_accounts);
        var default_account =
            default_accounts &&
            default_accounts[payment_type] &&
            default_accounts[payment_type]['account']
                ? default_accounts[payment_type]['account']
                : '';
        var row_index = payment_row.find('.payment_row_index').val();

        var account_dropdown = payment_row.find('select#account_' + row_index);
        if (account_dropdown.length && default_accounts) {
            account_dropdown.val(default_account);
            account_dropdown.change();
        }
    }

    //Validate max amount and disable account if advance
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
            account_dropdown.closest('.form-group').addClass('hide');
        }
    } else {
        amount_element.rules('remove', 'max-value');
        if (account_dropdown) {
            account_dropdown.prop('disabled', false);
            account_dropdown.closest('.form-group').removeClass('hide');
        }
    }
});

$(document).on('show.bs.modal', '#recent_transactions_modal', function () {
    get_recent_transactions('final', $('div#tab_final'));
});
$(document).on('shown.bs.tab', 'a[href="#tab_quotation"]', function () {
    get_recent_transactions('quotation', $('div#tab_quotation'));
});
$(document).on('shown.bs.tab', 'a[href="#tab_draft"]', function () {
    get_recent_transactions('draft', $('div#tab_draft'));
});

function disable_pos_form_actions() {
    if (!window.navigator.onLine) {
        return false;
    }

    $('div.pos-processing').show();
    $('#pos-save').attr('disabled', 'true');
    $('div.pos-form-actions').find('button').attr('disabled', 'true');
}

function enable_pos_form_actions() {
    $('div.pos-processing').hide();
    $('#pos-save').removeAttr('disabled');
    $('div.pos-form-actions').find('button').removeAttr('disabled');
}

$(document).on('change', '#recur_interval_type', function () {
    if ($(this).val() == 'months') {
        $('.subscription_repeat_on_div').removeClass('hide');
    } else {
        $('.subscription_repeat_on_div').addClass('hide');
    }
});

function validate_discount_field() {
    discount_element = $('#discount_amount_modal');
    discount_type_element = $('#discount_type_modal');

    if ($('#add_sell_form').length || $('#edit_sell_form').length) {
        discount_element = $('#discount_amount');
        discount_type_element = $('#discount_type');
    }
    var max_value = parseFloat(discount_element.data('max-discount'));
    if (discount_element.val() != '' && !isNaN(max_value)) {
        if (discount_type_element.val() == 'fixed') {
            var subtotal = get_subtotal();
            //get max discount amount
            max_value = __calculate_amount('percentage', max_value, subtotal);
        }

        discount_element.rules('add', {
            'max-value': max_value,
            messages: {
                'max-value': discount_element.data('max-discount-error_msg'),
            },
        });
    } else {
        discount_element.rules('remove', 'max-value');
    }
    discount_element.trigger('change');
}

$(document).on('change', '#discount_type_modal, #discount_type', function () {
    validate_discount_field();
});

function update_shipping_address(data) {
    if ($('#shipping_address_div').length) {
        var shipping_address = '';
        if (data.supplier_business_name) {
            shipping_address += data.supplier_business_name;
        }
        if (data.name) {
            shipping_address += ',<br>' + data.name;
        }
        if (data.text) {
            shipping_address += ',<br>' + data.text;
        }
        shipping_address += ',<br>' + data.shipping_address;
        $('#shipping_address_div').html(shipping_address);
    }
    if ($('#billing_address_div').length) {
        var address = [];
        if (data.supplier_business_name) {
            address.push(data.supplier_business_name);
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
        var billing_address = address.join(', ');
        $('#billing_address_div').html(billing_address);
    }

    if ($('#shipping_custom_field_1').length) {
        let shipping_custom_field_1 =
            data.shipping_custom_field_details != null
                ? data.shipping_custom_field_details.shipping_custom_field_1
                : '';
        $('#shipping_custom_field_1').val(shipping_custom_field_1);
    }

    if ($('#shipping_custom_field_2').length) {
        let shipping_custom_field_2 =
            data.shipping_custom_field_details != null
                ? data.shipping_custom_field_details.shipping_custom_field_2
                : '';
        $('#shipping_custom_field_2').val(shipping_custom_field_2);
    }

    if ($('#shipping_custom_field_3').length) {
        let shipping_custom_field_3 =
            data.shipping_custom_field_details != null
                ? data.shipping_custom_field_details.shipping_custom_field_3
                : '';
        $('#shipping_custom_field_3').val(shipping_custom_field_3);
    }

    if ($('#shipping_custom_field_4').length) {
        let shipping_custom_field_4 =
            data.shipping_custom_field_details != null
                ? data.shipping_custom_field_details.shipping_custom_field_4
                : '';
        $('#shipping_custom_field_4').val(shipping_custom_field_4);
    }

    if ($('#shipping_custom_field_5').length) {
        let shipping_custom_field_5 =
            data.shipping_custom_field_details != null
                ? data.shipping_custom_field_details.shipping_custom_field_5
                : '';
        $('#shipping_custom_field_5').val(shipping_custom_field_5);
    }

    //update export fields
    if (data.is_export) {
        $('#is_export').prop('checked', true);
        $('div.export_div').show();
        if ($('#export_custom_field_1').length) {
            $('#export_custom_field_1').val(data.export_custom_field_1);
        }
        if ($('#export_custom_field_2').length) {
            $('#export_custom_field_2').val(data.export_custom_field_2);
        }
        if ($('#export_custom_field_3').length) {
            $('#export_custom_field_3').val(data.export_custom_field_3);
        }
        if ($('#export_custom_field_4').length) {
            $('#export_custom_field_4').val(data.export_custom_field_4);
        }
        if ($('#export_custom_field_5').length) {
            $('#export_custom_field_5').val(data.export_custom_field_5);
        }
        if ($('#export_custom_field_6').length) {
            $('#export_custom_field_6').val(data.export_custom_field_6);
        }
    } else {
        $(
            '#export_custom_field_1, #export_custom_field_2, #export_custom_field_3, #export_custom_field_4, #export_custom_field_5, #export_custom_field_6'
        ).val('');
        $('#is_export').prop('checked', false);
        $('div.export_div').hide();
    }

    $('#shipping_address_modal').val(data.shipping_address);
    $('#shipping_address').val(data.shipping_address);
}

function get_sales_orders() {
    if ($('#sales_order_ids').length) {
        if ($('#sales_order_ids').hasClass('not_loaded')) {
            $('#sales_order_ids').removeClass('not_loaded');
            return false;
        }
        var customer_id = $('select#customer_id').val();
        var location_id = $('input#location_id').val();
        $.ajax({
            url: '/get-sales-orders/' + customer_id + '?location_id=' + location_id,
            dataType: 'json',
            success: function (data) {
                $('#sales_order_ids').select2('destroy').empty().select2({ data: data });
                $('table#pos_table tbody')
                    .find('tr')
                    .each(function () {
                        if (typeof $(this).data('so_id') !== 'undefined') {
                            $(this).remove();
                        }
                    });
                pos_total_row();
            },
        });
    }
}

$('#sales_order_ids').on('select2:select', function (e) {
    var sales_order_id = e.params.data.id;
    var product_row = $('input#product_row_count').val();
    var location_id = $('input#location_id').val();
    $.ajax({
        method: 'GET',
        url: '/get-sales-order-lines',
        async: false,
        data: {
            product_row: product_row,
            sales_order_id: sales_order_id,
        },
        dataType: 'json',
        success: function (result) {
            if (result.html) {
                var html = result.html;
                $(html)
                    .find('tr')
                    .each(function () {
                        $('table#pos_table tbody').append($(this)).find('input.pos_quantity');

                        var this_row = $('table#pos_table tbody').find('tr').last();
                        pos_each_row(this_row);

                        product_row = parseInt(product_row) + 1;

                        //For initial discount if present
                        var line_total = __read_number(this_row.find('input.pos_line_total'));
                        this_row.find('span.pos_line_total_text').text(line_total);

                        //Check if multipler is present then multiply it when a new row is added.
                        if (__getUnitMultiplier(this_row) > 1) {
                            this_row.find('select.sub_unit').trigger('change');
                        }

                        round_row_to_iraqi_dinnar(this_row);
                        __currency_convert_recursively(this_row);
                    });

                set_so_values(result.sales_order);

                //increment row count
                $('input#product_row_count').val(product_row);

                pos_total_row();
            } else {
                toastr.error(result.msg);
                $('input#search_product').focus().select();
            }
        },
    });
});

function set_so_values(so) {
    $('textarea[name="sale_note"]').val(so.additional_notes);
    if ($('#shipping_details').is(':visible')) {
        $('#shipping_details').val(so.shipping_details);
    }
    $('#shipping_address').val(so.shipping_address);
    $('#delivered_to').val(so.delivered_to);
    $('#shipping_charges').val(__number_f(so.shipping_charges));
    $('#shipping_status').val(so.shipping_status);
    if ($('#shipping_custom_field_1').length) {
        $('#shipping_custom_field_1').val(so.shipping_custom_field_1);
    }
    if ($('#shipping_custom_field_2').length) {
        $('#shipping_custom_field_2').val(so.shipping_custom_field_2);
    }
    if ($('#shipping_custom_field_3').length) {
        $('#shipping_custom_field_3').val(so.shipping_custom_field_3);
    }
    if ($('#shipping_custom_field_4').length) {
        $('#shipping_custom_field_4').val(so.shipping_custom_field_4);
    }
    if ($('#shipping_custom_field_5').length) {
        $('#shipping_custom_field_5').val(so.shipping_custom_field_5);
    }
}

$('#sales_order_ids').on('select2:unselect', function (e) {
    var sales_order_id = e.params.data.id;
    $('table#pos_table tbody')
        .find('tr')
        .each(function () {
            if (
                typeof $(this).data('so_id') !== 'undefined' &&
                $(this).data('so_id') == sales_order_id
            ) {
                $(this).remove();
                pos_total_row();
            }
        });
});

$(document).on('click', '#add_expense', function () {
    $.ajax({
        url: '/expenses/create',
        data: {
            location_id: $('#select_location_id').val(),
        },
        dataType: 'html',
        success: function (result) {
            $('#expense_modal').html(result);
            $('#expense_modal').modal('show');
        },
    });
});

$(document).on('shown.bs.modal', '#expense_modal', function () {
    $('#expense_transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });
    $('#expense_modal .paid_on').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });
    $(this).find('.select2').select2();
    $('#add_expense_modal_form').validate();
});

$(document).on('hidden.bs.modal', '#expense_modal', function () {
    $(this).html('');
});

$(document).on('submit', 'form#add_expense_modal_form', function (e) {
    e.preventDefault();
    var data = $(this).serialize();

    $.ajax({
        method: 'POST',
        url: $(this).attr('action'),
        dataType: 'json',
        data: data,
        success: function (result) {
            if (result.success == true) {
                $('#expense_modal').modal('hide');
                toastr.success(result.msg);
            } else {
                toastr.error(result.msg);
            }
        },
    });
});

function get_contact_due(id) {
    $.ajax({
        method: 'get',
        url: /get-contact-due/ + id,
        dataType: 'text',
        success: function (result) {
            if (result != '') {
                $('.contact_due_text').find('span').text(result);
                $('.contact_due_text').removeClass('hide');
            } else {
                $('.contact_due_text').find('span').text('');
                $('.contact_due_text').addClass('hide');
            }
        },
    });
}

function submitQuickContactForm(form) {
    var data = $(form).serialize();
    $.ajax({
        method: 'POST',
        url: $(form).attr('action'),
        dataType: 'json',
        data: data,
        beforeSend: function (xhr) {
            __disable_submit_button($(form).find('button[type="submit"]'));
        },
        success: function (result) {
            if (result.success == true) {
                var name = result.data.name;

                if (result.data.supplier_business_name) {
                    name += result.data.supplier_business_name;
                }

                $('select#customer_id').append(
                    $('<option>', { value: result.data.id, text: name })
                );
                $('select#customer_id').val(result.data.id).trigger('change');
                $('div.contact_modal').modal('hide');
                update_shipping_address(result.data);
                toastr.success(result.msg);
            } else {
                toastr.error(result.msg);
            }
        },
    });
}

$(document).on('click', '#send_for_sell_return', function (e) {
    var invoice_no = $('#send_for_sell_return_invoice_no').val();

    if (invoice_no) {
        $.ajax({
            method: 'get',
            url: /validate-invoice-to-return/ + encodeURI(invoice_no),
            dataType: 'json',
            success: function (result) {
                if (result.success == true) {
                    window.location = result.redirect_url;
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    }
});

$(document).on('click', '#send_for_sercice_staff_replacement', function (e) {
    var invoice_no = $('#send_for_sell_service_staff_invoice_no').val();

    if (invoice_no) {
        $.ajax({
            method: 'get',
            url: /validate-invoice-to-service-staff-replacement/ + encodeURI(invoice_no),
            dataType: 'json',
            success: function (result) {
                if (result.success == true) {
                    $('#service_staff_replacement').popover('hide');
                    $('#service_staff_modal').html(result.msg);
                    $('#service_staff_modal').modal('show');
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    }
});

$(document).on('shown.bs.modal', '#service_staff_modal', function () {
    $('#change_service_staff').validate();
});

$(document).on('submit', 'form#change_service_staff', function (e) {
    e.preventDefault();
    var data = $(this).serialize();

    $.ajax({
        method: 'POST',
        url: $(this).attr('action'),
        dataType: 'json',
        data: data,
        success: function (result) {
            if (result.success == true) {
                $('#service_staff_modal').modal('hide');
                toastr.success(result.msg);
            } else {
                toastr.error(result.msg);
            }
        },
    });
});

$(document).on('ifChanged', 'input[name="search_fields[]"]', function (event) {
    var search_fields = [];
    $('input[name="search_fields[]"]:checked').each(function () {
        search_fields.push($(this).val());
    });

    localStorage.setItem('pos_search_fields', search_fields);
});

function set_search_fields() {
    if ($('input[name="search_fields[]"]').length == 0) {
        return false;
    }

    var pos_search_fields = localStorage.getItem('pos_search_fields');

    if (pos_search_fields === null) {
        pos_search_fields = ['name', 'sku', 'lot'];
    }

    $('input[name="search_fields[]"]').each(function () {
        if (pos_search_fields.indexOf($(this).val()) >= 0) {
            $(this).iCheck('check');
        } else {
            $(this).iCheck('uncheck');
        }
    });
}

$(document).on('click', '#show_service_staff_availability', function () {
    loadServiceStaffAvailability();
});
$(document).on('click', '#refresh_service_staff_availability_status', function () {
    loadServiceStaffAvailability(false);
});
$(document).on('click', 'button.pause_resume_timer', function (e) {
    $('.view_modal').find('.overlay').removeClass('hide');
    $.ajax({
        method: 'get',
        url: $(this).attr('data-href'),
        dataType: 'json',
        success: function (result) {
            loadServiceStaffAvailability(false);
        },
    });
});

$(document).on('click', '.mark_as_available', function (e) {
    e.preventDefault();
    $('.view_modal').find('.overlay').removeClass('hide');
    $.ajax({
        method: 'get',
        url: $(this).attr('href'),
        dataType: 'json',
        success: function (result) {
            loadServiceStaffAvailability(false);
        },
    });
});
var service_staff_availability_interval = null;

function loadServiceStaffAvailability(show = true) {
    var location_id = $('[name="location_id"]').val();
    $.ajax({
        method: 'get',
        url: $('#show_service_staff_availability').attr('data-href'),
        dataType: 'html',
        data: { location_id: location_id },
        success: function (result) {
            $('.view_modal').html(result);
            if (show) {
                $('.view_modal').modal('show');

                //auto refresh service staff availabilty if modal is open
                service_staff_availability_interval = setInterval(function () {
                    loadServiceStaffAvailability(false);
                }, 60000);
            }
        },
    });
}

$(document).on('hidden.bs.modal', '.view_modal', function () {
    if (service_staff_availability_interval !== null) {
        clearInterval(service_staff_availability_interval);
    }
    service_staff_availability_interval = null;
});

$(document).on('change', '#res_waiter_id', function (e) {
    var is_enable = $(this).find('option:selected').data('is_enable');

    if (is_enable) {
        swal({
            text: LANG.enter_pin_here,
            buttons: true,
            dangerMode: true,
            content: {
                element: 'input',
                attributes: {
                    placeholder: LANG.enter_pin_here,
                    type: 'password',
                },
            },
        }).then((inputValue) => {
            if (inputValue !== null) {
                $.ajax({
                    method: 'get',
                    url: '/modules/data/check-staff-pin',
                    dataType: 'json',
                    data: {
                        service_staff_pin: inputValue,
                        user_id: $('#res_waiter_id').val(),
                    },
                    success: (result) => {
                        if (result == false) {
                            toastr.error(LANG.authentication_failed);
                            $('#res_waiter_id').val('');
                        } else {
                            // AJAX request succeeded, resolve
                            toastr.success(LANG.authentication_successfull);
                        }
                    },
                });
            } else {
                // Handle the "Cancel" action
                $('#res_waiter_id').val('');
            }
        });
    }
});

// update serial number of product item
function update_serial_no() {
    $('.product_row').each(function (index) {
        // Add the serial number to the first <td> of each row (index + 1 to start from 1)
        if ($(this).find('td:first').hasClass('serial_no')) {
            $(this)
                .find('td:first')
                .text(index + 1);
        }
    });
}

/**
 * Saves the serialized form data from #add_pos_sell_form into LocalStorage.
 */

// Updated: Use pos_form_obj for both add/edit modes
function saveFormDataToLocalStorage() {
    // Safety: If pos_form_obj is undefined or empty, detect it dynamically
    pos_form_obj =
        $('form#edit_pos_sell_form').length > 0
            ? $('form#edit_pos_sell_form')
            : $('form#add_pos_sell_form');

    let form = pos_form_obj;
    // Check if the form exists in the DOM
    if (form.length === 0) {
        console.error('Error: POS form (pos_form_obj) not found.');
        return;
    }
    // Serialize form data into an array of objects: [{name: 'input_name', value: 'input_value'}, ...]
    let formArray = form.serializeArray();

    // Find if "price_total" already exists in the array
    let priceIndex = formArray.findIndex((item) => item.name === 'price_total');
    if (priceIndex !== -1) {
        // If exists, update the value
        formArray[priceIndex].value = get_subtotal();
    } else {
        // If not exists, push new entry
        formArray.push({ name: 'price_total', value: get_subtotal() });
    }

    // Find if "order_tax" already exists in the array
    let taxIndex = formArray.findIndex((item) => item.name === 'order_tax');
    if (taxIndex !== -1) {
        // If exists, update the value
        formArray[taxIndex].value = $('#order_tax').text().trim();
    } else {
        // If not exists, push new entry
        formArray.push({ name: 'order_tax', value: $('#order_tax').text().trim() });
    }

    // Find if "shipping_charges_amount" already exists in the array
    let shippingIndex = formArray.findIndex((item) => item.name === 'shipping_charges_amount');
    if (shippingIndex !== -1) {
        // If exists, update the value
        formArray[shippingIndex].value = $('#shipping_charges_amount').text().trim();
    } else {
        // If not exists, push new entry
        formArray.push({
            name: 'shipping_charges_amount',
            value: $('#shipping_charges_amount').text().trim(),
        });
    }

    // Find if "total_paying_input" already exists in the array
    let totalPayingIndex = formArray.findIndex((item) => item.name === 'total_paying_input');
    if (totalPayingIndex !== -1) {
        // If exists, update the value
        formArray[totalPayingIndex].value = $('#total_paying_input').val();
    } else {
        // If not exists, push new entry
        formArray.push({
            name: 'total_paying_input',
            value: $('#total_paying_input').val(),
        });
    }

    // Find if "change_return" already exists in the array
    let changeReturnIndex = formArray.findIndex((item) => item.name === 'change_return');
    if (changeReturnIndex !== -1) {
        // If exists, update the value
        formArray[changeReturnIndex].value = $('#change_return').val();
    } else {
        // If not exists, push new entry
        formArray.push({ name: 'change_return', value: $('#change_return').val() });
    }

    // Find if "in_balance_due" already exists in the array
    let balanceDueIndex = formArray.findIndex((item) => item.name === 'in_balance_due');
    if (balanceDueIndex !== -1) {
        // If exists, update the value
        formArray[balanceDueIndex].value = $('#in_balance_due').val();
    } else {
        // If not exists, push new entry
        formArray.push({
            name: 'in_balance_due',
            value: $('#in_balance_due').val(),
        });
    }

    // Store serialized data in LocalStorage as a JSON string
    localStorage.setItem('pos_form_data_array', JSON.stringify(formArray));
}

// Function to update cost price data attributes based on selected sub-unit
function updateCostPriceDataAttributes(tr, selected_option) {
    var sub_unit_cost_price = selected_option.data('cost-price');
    var base_cost_price = tr.find('input.pos_unit_price').data('cost-price');

    if (sub_unit_cost_price && sub_unit_cost_price !== '') {
        // Use sub-unit specific cost price
        var cost_price = parseFloat(sub_unit_cost_price);
        tr.find('input.pos_unit_price, input.pos_unit_price_inc_tax').each(function () {
            $(this).attr('data-cost-price', cost_price);
        });
    } else if (base_cost_price) {
        // Calculate cost price based on base unit cost price and multiplier
        var calculated_cost_price = multiplier > 0 ? base_cost_price * multiplier : base_cost_price;
        tr.find('input.pos_unit_price, input.pos_unit_price_inc_tax').each(function () {
            $(this).attr('data-cost-price', calculated_cost_price);
        });
    }
}

$(document).on('keyup', 'input#shipping_charges_modal', function () {
    $(this).val(__currency_trans_from_en($(this).val(), false, false));
});
