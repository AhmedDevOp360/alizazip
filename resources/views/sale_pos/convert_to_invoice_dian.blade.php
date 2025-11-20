@extends('layouts.app')



@section('title', 'Convertir a Factura Electrónica')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Convertir a Factura Electrónica</h1>
</section>
<!-- Main content -->
<section class="content no-print">
<input type="hidden" id="amount_rounding_method" value="{{$pos_settings['amount_rounding_method'] ?? ''}}">
@if(!empty($pos_settings['allow_overselling']))
	<input type="hidden" id="is_overselling_allowed">
@endif
@if(session('business.enable_rp') == 1)
    <input type="hidden" id="reward_point_enabled">
@endif


@php
	$custom_labels = json_decode(session('business.custom_labels'), true);
	$common_settings = session()->get('business.common_settings');
@endphp

	{!! Form::open(['url' => action([\App\Http\Controllers\SellPosController::class, 'ConvertToInvoiceDianStore']), 'method' => 'post', 'id' => 'convert_sell_to_invoice_dian_form']) !!}
        @component('components.widget', ['class' => 'box-solid'])

        <table class="table">
            <thead>
              <tr>
				  <th scope="col">NIT</th>
				  <th scope="col">Cliente</th>
				  <th scope="col">Número de Factura</th>
                <th scope="col">Fecha de Factura</th>
                <th scope="col">Total</th>
                <th scope="col">Estado</th>
                <th scope="col">Método de Pago</th>
            </tr>
            </thead>
            <tbody>
              <tr>
                <td>{{$sell->contact->contact_id}}-{{$sell->contact->dv}}</td>
                <td>{{$sell->contact->name}}</td>
                <td>{{$sell->invoice_no}}</td>
                <td>{{$sell->transaction_date}}</td>
                <td>{{number_format($sell->final_total, 1, '.', ',')}}</td>
                <td>{{$sell->status}}</td>
                <td>{{$sell->payment_lines[0]->method ?? ''}}</td>
              </tr>
            </tbody>
        </table>

        @endcomponent

        @component('components.widget', ['class' => 'box-solid'])

        <table class="table">
            <thead>
              <tr>
                <th scope="col">Producto</th>
                <th scope="col">Cantidad</th>
                <th scope="col">Precio</th>
                <th scope="col">Total</th>
            </tr>
            </thead>
            <tbody>
                <tr>
                    @foreach($sell->sell_lines as $sell_line)
                    <td>{{$sell_line->product->name}}</td>
                    <td>{{$sell_line->quantity}}</td>
                    <td>{{number_format($sell_line->unit_price, 1, '.', ',')}}</td>
                    <td>{{number_format($sell_line->unit_price*$sell_line->quantity, 1, '.', ',')}}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
		<div class="row">
			<div class="col-sm-4">
				<div class="form-group">
					{!! Form::label('invoice_scheme_id', __('Resolución de Factura') . ':*') !!}
					{!! Form::select('invoice_scheme_id', $invoice_schemes, $default_invoice_scheme->id, ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select')]); !!}
				</div>
			</div>
		</div>
		<input type="hidden" name="sell_id" value="{{$sell->id}}">


		@if(session('success'))
		<div class="alert alert-info" role="alert">
			{{ session('success') }}
		  </div>
		@endif
		  

        <button type="submit" class="tw-dw-btn bg-info tw-text-white">Convertir a Electrónica</button>
        @endcomponent

    {!! Form::close() !!}
    @stop

@section('javascript')
	<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>

	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
    	<script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <script type="text/javascript">
    	$(document).ready( function() {
    		$('#status').change(function(){
    			if ($(this).val() == 'final') {
    				$('#payment_rows_div').removeClass('hide');
    			} else {
    				$('#payment_rows_div').addClass('hide');
    			}
    		});
    		$('.paid_on').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });

            $('#shipping_documents').fileinput({
		        showUpload: false,
		        showPreview: false,
		        browseLabel: LANG.file_browse_label,
		        removeLabel: LANG.remove,
		    });

		    $(document).on('change', '#prefer_payment_method', function(e) {
			    var default_accounts = $('select#select_location_id').length ? 
			                $('select#select_location_id')
			                .find(':selected')
			                .data('default_payment_accounts') : $('#location_id').data('default_payment_accounts');
			    var payment_type = $(this).val();
			    if (payment_type) {
			        var default_account = default_accounts && default_accounts[payment_type]['account'] ? 
			            default_accounts[payment_type]['account'] : '';
			        var account_dropdown = $('select#prefer_payment_account');
			        if (account_dropdown.length && default_accounts) {
			            account_dropdown.val(default_account);
			            account_dropdown.change();
			        }
			    }
			});

		    function setPreferredPaymentMethodDropdown() {
			    var payment_settings = $('#location_id').data('default_payment_accounts');
			    payment_settings = payment_settings ? payment_settings : [];
			    enabled_payment_types = [];
			    for (var key in payment_settings) {
			        if (payment_settings[key] && payment_settings[key]['is_enabled']) {
			            enabled_payment_types.push(key);
			        }
			    }
			    if (enabled_payment_types.length) {
			        $("#prefer_payment_method > option").each(function() {
		                if (enabled_payment_types.indexOf($(this).val()) != -1) {
		                    $(this).removeClass('hide');
		                } else {
		                    $(this).addClass('hide');
		                }
			        });
			    }
			}
			
			setPreferredPaymentMethodDropdown();

			$('#is_export').on('change', function () {
	            if ($(this).is(':checked')) {
	                $('div.export_div').show();
	            } else {
	                $('div.export_div').hide();
	            }
	        });

			if($('.payment_types_dropdown').length){
				$('.payment_types_dropdown').change();
			}

    	});
    </script>
@endsection
