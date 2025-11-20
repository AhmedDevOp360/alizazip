@extends('layouts.app')

@section('title', __('sale.pos_sale'))

@section('content')
<section class="content no-print">
	<input type="hidden" id="amount_rounding_method" value="{{$pos_settings['amount_rounding_method'] ?? ''}}">
	@if(!empty($pos_settings['allow_overselling']))
		<input type="hidden" id="is_overselling_allowed">
	@endif
	@if(session('business.enable_rp') == 1)
        <input type="hidden" id="reward_point_enabled">
    @endif
    @php
		$is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
		$is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
	@endphp
	{!! Form::open(['url' => action([\App\Http\Controllers\SellPosController::class, 'update'], [$transaction->id]), 'method' => 'post', 'id' => 'edit_pos_sell_form' ]) !!}
	{{ method_field('PUT') }}
	<div class="row mb-12">
		<div class="col-md-12 tw-pt-0">
			<div class="row tw-flex lg:tw-flex-row md:tw-flex-col sm:tw-flex-col tw-flex-col tw-items-start md:tw-gap-4">
				{{-- <div class=" @if(empty($pos_settings['hide_product_suggestion']))   @else lg:tw-w-[100%] @endif"> --}}
				<div class="tw-px-3 tw-w-full  lg:tw-px-0 lg:tw-pr-0 col-lg-3 col-sm-3" style="overflow: hidden">

					<div class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-rounded-2xl tw-bg-white tw-p-2">
						<div class="box-body pb-0">
							{!! Form::hidden('location_id', $transaction->location_id, [
								'id' => 'location_id', 
								'data-receipt_printer_type' => !empty
								($location_printer_type) ? $location_printer_type 
									: 'browser', 
									'data-default_payment_accounts' => $transaction->location->default_payment_accounts]); 
							!!}
							<!-- sub_type -->
							{!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
							<input type="hidden" id="item_addition_method" value="{{$business_details->item_addition_method}}">
								@include('sale_pos.partials.pos_form_edit')

								@include('sale_pos.partials.pos_form_totals', ['edit' => true])

								@include('sale_pos.partials.payment_modal')

								@if(empty($pos_settings['disable_suspend']))
									@include('sale_pos.partials.suspend_note_modal')
								@endif

								@if(empty($pos_settings['disable_recurring_invoice']))
									@include('sale_pos.partials.recurring_invoice_modal')
								@endif
							</div>
							
							
							<div style="display: flex; ">
                                @if (!Gate::check('disable_pay_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                                <button type="button" style="flex: 1; margin-left: 12px;  font-size: 15px" 
                                    class="tw-hidden md:tw-flex md:tw-flex-row md:tw-items-center md:tw-justify-center md:tw-gap-1 tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-sm tw-bg-[#001F3E] btn btn-success tw-rounded-md tw-p-2 tw-w-[8.5rem] @if (!isMobile())  @endif no-print @if ($pos_settings['disable_pay_checkout'] != 0) hide @endif"
                                    id="pos-finalize" title="@lang('COBRAR')"><i class=""
                                        aria-hidden="true"></i> @lang('COBRAR') </button>
                                @endif
                                <div style="width: 10px"></div>
                                {{-- @if (!Gate::check('disable_express_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                                    <button type="button" style="flex: 1; margin-right: 12px"
                                        class="tw-font-bold tw-text-white tw-cursor-pointer tw-text-xs md:tw-text-sm tw-bg-[rgb(40,183,123)] tw-p-2 tw-rounded-md tw-w-[8.5rem] tw-hidden md:tw-flex lg:tw-flex lg:tw-flex-row btn btn-warning lg:tw-items-center lg:tw-justify-center lg:tw-gap-1 @if (!isMobile())  @endif no-print @if ($pos_settings['disable_express_checkout'] != 0 || !array_key_exists('cash', $payment_types)) hide @endif pos-express-finalize"
                                        data-pay_method="cash" title="@lang('tooltip.express_checkout')"> <i class=""
                                            aria-hidden="true"></i> @lang('lang_v1.express_checkout_cash')</button>
                                @endif  --}}

                                {{-- @if (!Gate::check('disable_credit_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                                    @if (empty($pos_settings['disable_credit_sale_button']))
                                        <input type="hidden" name="is_credit_sale" value="0" id="is_credit_sale">
                                        <button type="button" style="flex: 1; margin-right: 12px; font-size: 15px"
                                            class="  tw-text-gray-700 tw-cursor-pointer tw-text-xs md:tw-text-sm tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-1 no-print pos-express-finalize btn-danger @if ($is_mobile) col-xs-6 @endif"
                                            data-pay_method="credit_sale" title="@lang('lang_v1.tooltip_credit_sale')"
                                            @if (!empty($only_payment)) disabled @endif>
                                            Crédito
                                        </button>
                                    @endif
                                @endif --}}
								 @if (!Gate::check('disable_credit_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                                    @if (empty($pos_settings['disable_credit_sale_button']))
                                        <input type="hidden" name="is_credit_sale" value="0" id="is_credit_sale">
                                        <button type="button" style="flex: 1; margin-right: 12px; font-size: 15px"
                                            class="  tw-text-gray-700 tw-cursor-pointer tw-text-xs md:tw-text-sm tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-1 no-print pos-express-finalize btn-danger col-xs-6"
                                            data-pay_method="credit_sale" title="@lang('lang_v1.tooltip_credit_sale')"
                                            @if (!empty($only_payment)) disabled @endif>
                                            @lang('lang_v1.credit_sale')
                                        </button>
                                    @endif
                                @endif
                            </div>
                        
							
							@if(!empty($only_payment))
								<div class="overlay"></div>
							@endif
						</div>
					</div>
				@if(empty($pos_settings['hide_product_suggestion'])  && !isMobile() && empty($only_payment))
					<div class="col-lg-9" style="height: 65%;">
						@include('sale_pos.partials.pos_sidebar')
					</div>
				@endif
			</div>
		</div>
	</div>
	@include('sale_pos.partials.pos_form_actions', ['edit' => true])
	{!! Form::close() !!}
</section>

<!-- This will be printed -->
<section class="invoice print_section" id="receipt_section">
</section>
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div>
@if(empty($pos_settings['hide_product_suggestion']) && isMobile())
	@include('sale_pos.partials.mobile_product_suggestions')
@endif
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

@include('sale_pos.partials.configure_search_modal')

@include('sale_pos.partials.recent_transactions_modal')

@include('sale_pos.partials.weighing_scale_modal')

@stop

@section('javascript')
	<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
	@include('sale_pos.partials.keyboard_shortcuts')

	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
    	<script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif

    <!-- include module js -->
    @if(!empty($pos_module_data))
	    @foreach($pos_module_data as $key => $value)
            @if(!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
	    @endforeach
	@endif
	
@endsection

@section('css')
	<style type="text/css">
		/*CSS to print receipts*/
		.print_section{
		    display: none;
		}
		@media print{
		    .print_section{
		        display: block !important;
		    }
		}
		@page {
		    size: 3.1in auto;/* width height */
		    height: auto !important;
		    margin-top: 0mm;
		    margin-bottom: 0mm;
		}
		.overlay {
			background: rgba(255,255,255,0) !important;
			cursor: not-allowed;
		}
	</style>
	<!-- include module css -->
    @if(!empty($pos_module_data))
        @foreach($pos_module_data as $key => $value)
            @if(!empty($value['module_css_path']))
                @includeIf($value['module_css_path'])
            @endif
        @endforeach
    @endif
@endsection
