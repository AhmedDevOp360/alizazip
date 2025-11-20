@extends('layouts.app')

@section('title', __('sale.pos_sale'))

@section('content')
    <section class="content no-print">
        <input type="hidden" id="amount_rounding_method" value="{{ $pos_settings['amount_rounding_method'] ?? '' }}">
        @if (!empty($pos_settings['allow_overselling']))
            <input type="hidden" id="is_overselling_allowed">
        @endif
        @if (!empty($pos_settings['enable_msp']) && $pos_settings['enable_msp'] == 1)
            <input type="hidden" id="enable_msp_enabled">
        @endif
        @if (session('business.enable_rp') == 1)
            <input type="hidden" id="reward_point_enabled">
        @endif
        @php
            $is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
            $is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
        @endphp
        {!! Form::open([
            'url' => action([\App\Http\Controllers\SellPosController::class, 'store']),
            'method' => 'post',
            'id' => 'add_pos_sell_form',
        ]) !!}
        <div class="row mb-12">
            <div class="col-md-12 tw-pt-0">
                <div class="row tw-flex lg:tw-flex-row md:tw-flex-col sm:tw-flex-col tw-flex-col tw-items-start md:tw-gap-4">
                    {{-- <div class="@if (empty($pos_settings['hide_product_suggestion'])) col-md-7 @else col-md-10 col-md-offset-1 @endif no-padding pr-12"> --}}
                    <div class="tw-px-3 tw-w-full  lg:tw-px-0 lg:tw-pr-0 col-lg-3 col-sm-3" style="overflow: hidden">
                        
                        <div class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-rounded-2xl tw-bg-white tw-p-2">
                            {{-- <div class="box box-solid mb-12 @if (!isMobile()) mb-40 @endif"> --}}
                                <div class="box-body pb-0">
                                    <!-- Productos-->
                                    {!! Form::hidden('location_id', $default_location->id ?? null, [
                                        'id' => 'location_id',
                                        'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
                                            ? $default_location->receipt_printer_type
                                            : 'browser',
                                        'data-default_payment_accounts' => $default_location->default_payment_accounts ?? '',
                                    ]) !!}
                                    <!-- sub_type -->
                                    {!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
                                    <input type="hidden" id="item_addition_method"
                                        value="{{ $business_details->item_addition_method }}">
                                    <!-- Fin Productos-->
                                    
                                    @include('sale_pos.partials.pos_form') <!-- titulos de prodcuto, cantidad y total -->

                                    @include('sale_pos.partials.pos_form_totals')

                                    @include('sale_pos.partials.payment_modal')

                                    @if (empty($pos_settings['disable_suspend']))
                                        @include('sale_pos.partials.suspend_note_modal')
                                    @endif

                                    @if (empty($pos_settings['disable_recurring_invoice']))
                                        @include('sale_pos.partials.recurring_invoice_modal')
                                    @endif
                                </div>
                            {{-- </div> --}}
                            <!-- BOTONES  a VERDE Y AMARILLO COBRAR y A CREDITO-->
                            <div style="display: flex; " id="buttons-container">
                                @if (!Gate::check('disable_pay_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                                <button type="button" style="flex: 1; margin-left: 12px;  font-size: 15px" 
                                    class="tw-hidden md:tw-flex md:tw-flex-row md:tw-items-center md:tw-justify-center md:tw-gap-1 tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-sm tw-bg-[#001F3E] btn btn-success tw-rounded-md tw-p-2 tw-w-[8.5rem] @if (!isMobile())  @endif no-print @if ($pos_settings['disable_pay_checkout'] != 0) hide @endif"
                                    id="pos-finalize" title="@lang('lang_v1.tooltip_checkout_multi_pay')"><i class=""
                                        aria-hidden="true"></i> COBRAR </button>
                                @endif
                                <div style="width: 10px"></div>
                                @if (!Gate::check('disable_express_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                                    <button type="button" style="flex: 1; margin-right: 12px"
                                        class="tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-sm tw-bg-[rgb(40,183,123)] tw-p-2 tw-rounded-md tw-w-[8.5rem] tw-hidden md:tw-flex lg:tw-flex lg:tw-flex-row btn btn-warning lg:tw-items-center lg:tw-justify-center lg:tw-gap-1 @if (!isMobile())  @endif no-print @if ($pos_settings['disable_express_checkout'] != 0 || !array_key_exists('cash', $payment_types)) hide @endif pos-express-finalize"
                                        data-pay_method="cash" title="@lang('tooltip.express_checkout')"> <i class=""
                                            aria-hidden="true"></i> @lang('lang_v1.express_checkout_cash')</button>
                                @endif 

                                @if (!Gate::check('disable_credit_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                                    @if (empty($pos_settings['disable_credit_sale_button']))
                                        <input type="hidden" name="is_credit_sale" value="0" id="is_credit_sale">
                                        <button type="button" style="flex: 1; margin-right: 12px; font-size: 15px"
                                            class="  tw-text-gray-700 tw-cursor-pointer tw-text-xs md:tw-text-sm tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-1 no-print pos-express-finalize btn-danger col-xs-6"
                                            data-pay_method="credit_sale" title="@lang('lang_v1.tooltip_credit_sale')"
                                            @if (!empty($only_payment)) disabled @endif>
                                            Crédito
                                        </button>
                                    @endif
                                @endif
                            </div>
                            
                            <div  style="display: none;" class="tw-flex tw-flex-row tw-items-center tw-justify-center tw-gap-1" id="loading-gif">
                                <img src="{{ asset('img/login.gif') }}" alt="Loading" class="tw-w-10 tw-h-10">
                            </div>
                        </div>

                         

                    
                    </div>
                    
                    @if (empty($pos_settings['hide_product_suggestion']) && !isMobile())
                        <div class="col-lg-9" style="height: calc(100vh - 200px); min-height: 60vh;">
                            @include('sale_pos.partials.pos_sidebar')
                        </div>
                    @endif
                    
                </div>
            </div>
        </div>
        @include('sale_pos.partials.pos_form_actions')
        {!! Form::close() !!}
    </section>

    <!-- This will be printed -->
    <section class="invoice print_section" id="receipt_section">
    </section>
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>
    @if (empty($pos_settings['hide_product_suggestion']) && isMobile())
        @include('sale_pos.partials.mobile_product_suggestions')
    @endif
    <!-- /.content -->
    <div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <!-- quick product modal -->
    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

    <div class="modal fade" id="expense_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    @include('sale_pos.partials.configure_search_modal')

    @include('sale_pos.partials.recent_transactions_modal')

    @include('sale_pos.partials.weighing_scale_modal')

@stop
@section('css')
    <!-- include module css -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_css_path']))
                @includeIf($value['module_css_path'])
            @endif
        @endforeach
    @endif
@stop
@section('javascript')
    <script src="{{ asset('js/pos.js?v='.time() . $asset_v) }}"></script>
    <script src="{{ asset('js/printer.js?v={{ time()' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v={{ time()' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v={{ time()' . $asset_v) }}"></script>

    @include('sale_pos.partials.keyboard_shortcuts')

    <!-- Call restaurant module if defined -->
    @if (in_array('tables', $enabled_modules) ||
            in_array('modifiers', $enabled_modules) ||
            in_array('service_staff', $enabled_modules))
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <!-- include module js -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
        @endforeach
    @endif
    
    
    <script type="text/javascript">
        $(document).ready(function() {
            // Set default commission agent
            $('#set_default_commission_agent').click(function() {
                var agent_id = $('#commission_agent').val();

                if (!agent_id) {
                    toastr.error("@lang('lang_v1.please_select_commission_agent')");
                    return;
                }

                swal({
                    title: LANG.sure,
                    text: "@lang('lang_v1.confirm_set_default_commission_agent')",
                    icon: "warning",
                    buttons: true,
                    dangerMode: false,
                }).then((confirmed) => {
                    if (confirmed) {
                        $.ajax({
                            url: '/sales-commission-agents/set-default/' + agent_id,
                            method: 'POST',
                            dataType: 'json',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(result) {
                                if (result.success) {
                                    toastr.success(result.msg);
                                    // Update button appearance
                                    $('#set_default_commission_agent').addClass('btn-warning');
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });

            // Update button appearance based on selected agent
            $('#commission_agent').change(function() {
                var selected_agent = $(this).val();
                var default_agent = '{{ $default_commission_agent_id ?? "" }}';

                if (selected_agent == default_agent) {
                    $('#set_default_commission_agent').addClass('btn-warning');
                } else {
                    $('#set_default_commission_agent').removeClass('btn-warning');
                }
            });

            // Initialize button appearance
            var default_agent = '{{ $default_commission_agent_id ?? "" }}';
            if (default_agent && $('#commission_agent').val() == default_agent) {
                $('#set_default_commission_agent').addClass('btn-warning');
            }
        });
    </script>
@endsection
