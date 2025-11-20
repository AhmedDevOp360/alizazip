@extends('layouts.app')
@section('title', __('purchase.purchases'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('purchase.purchases')
        
             
			
			
			     <div class="tw-grid tw-grid-cols-1 tw-gap-4 tw-mt-6 sm:tw-grid-cols-2 xl:tw-grid-cols-4 sm:tw-gap-5">
                            
                                <div
                                    class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw-translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                    <div class="tw-p-4 sm:tw-p-5">
                                        <div class="tw-flex tw-items-center tw-gap-4">
                                            <div
                                                class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full sm:tw-w-12 sm:tw-h-12 tw-shrink-0 tw-bg-sky-100 tw-text-sky-500">
                                                <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                    <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                    <path d="M17 17h-11v-14h-2" />
                                                    <path d="M6 5l14 1l-1 7h-13" />
                                                </svg>
                                            </div>

                                            <div class="tw-flex-1 tw-min-w-0">
                                                <p
                                                class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                {{ __('home.total_purchase') }}
                                            </p>
                                            
                                            {{-- <span class="display_currency" data-currency_symbol="true"></span> --}}
                                                <p
                                                    class="total_purchase tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

				
				
				<div
                                class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div
                                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-yellow-500 tw-bg-yellow-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M12 9v4" />
                                                <path
                                                    d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
                                                <path d="M12 16h.01" />
                                            </svg>
                                        </div>

                                        <div>
                                            <p class="tw-text-sm tw-font-medium tw-text-gray-500">
                                                {{ __('home.purchase_due') }}
                                            </p>
                                            
                                            {{-- <span class="display_currency" data-currency_symbol="true"></span> --}}
                                            
                                            <p
                                                class="purchase_due tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">

                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
				
			
			
			
					

	    
       
       
     &nbsp;
     <title>Modal YouTube</title>
  <style>
     {
      font-family: Arial, sans-serif;
      text-align: center;
      padding: 50px;
      background: #f2f2f2;
    }

    /* Botón */
    .btn-youtube {
      background-color: #DB2323;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 12px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .btn-youtube:hover {
      background-color: #2BB3B0;
    }


    /* Contenido del modal */
    .modal-content {
      position: relative;
      background-color: #fff;
      margin: 10% auto;
      padding: 0;
      border-radius: 8px;
      width: 80%;
      max-width: 720px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    /* Botón cerrar */
    .close {
      color: #aaa;
      position: absolute;
      top: 10px;
      right: 20px;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }

    .close:hover {
      color: #000;
    }

    /* Video */
    iframe {
      width: 120%;
      height: 605px;
      border: none;
      border-radius: 0 0 8px 8px;
    }
  </style>

<body>


  <button class="btn-youtube" id="openModalBtn">Ver Video Crear Compra</button>

  <!-- Modal -->
  <div id="youtubeModal" class="modal">
    <div class="modal-content">
      <span class="close" id="closeModalBtn">&times;</span>
      <iframe id="youtubeVideo" src="" allowfullscreen></iframe>
    </div>
  </div>

  <script>
    const modal = document.getElementById("youtubeModal");
    const openBtn = document.getElementById("openModalBtn");
    const closeBtn = document.getElementById("closeModalBtn");
    const video = document.getElementById("youtubeVideo");

    // URL del video
    const youtubeURL = "https://www.youtube.com/embed/9o_BFlXOOe0?si=JIiskfVMazljDJzu"; // reemplaza con tu video

    openBtn.onclick = () => {
      modal.style.display = "block";
      video.src = youtubeURL + "?autoplay=1";
    }

    closeBtn.onclick = () => {
      modal.style.display = "none";
      video.src = ""; // Detener el video al cerrar
    }

    // Cerrar al hacer clic fuera del modal
    window.onclick = (e) => {
      if (e.target === modal) {
        modal.style.display = "none";
        video.src = "";
      }
    }
  </script>

</body>


	    </h4>

    
    </h1>
</section>


    <!-- Main content -->
    <section class="content no-print">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('purchase_list_filter_location_id', __('purchase.business_location') . ':') !!}
                    {!! Form::select('purchase_list_filter_location_id', $business_locations, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('purchase_list_filter_supplier_id', __('purchase.supplier') . ':') !!}
                    {!! Form::select('purchase_list_filter_supplier_id', $suppliers, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('purchase_list_filter_status', __('purchase.purchase_status') . ':') !!}
                    {!! Form::select('purchase_list_filter_status', $orderStatuses, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('purchase_list_filter_payment_status', __('purchase.payment_status') . ':') !!}
                    {!! Form::select(
                        'purchase_list_filter_payment_status',
                        [
                            'paid' => __('lang_v1.paid'),
                            'due' => __('lang_v1.due'),
                            'partial' => __('lang_v1.partial'),
                            'overdue' => __('lang_v1.overdue'),
                        ],
                        null,
                        ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')],
                    ) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('purchase_list_filter_date_range', __('report.date_range') . ':') !!}
                    {!! Form::text('purchase_list_filter_date_range', null, [
                        'placeholder' => __('lang_v1.select_a_date_range'),
                        'class' => 'form-control',
                        'readonly',
                    ]) !!}
                </div>
            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-primary', 'title' => __('purchase.all_purchases')])
            @can('purchase.create')
                @slot('tool')
                    <div class="box-tools">
                        {{-- <a class="btn btn-block btn-primary" href="{{action([\App\Http\Controllers\PurchaseController::class, 'create'])}}">
                    <i class="fa fa-plus"></i> @lang('messages.add')</a> --}}
                        <a class="tw-dw-btn tw-bg--to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-black tw-border-none tw-rounded-full pull-right"
                            href="{{action([\App\Http\Controllers\PurchaseController::class, 'create'])}}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" 
                                        viewBox="0 0 20 20" fill="none" stroke="teal" stroke-width="3" stroke-linecap="round"
                                            stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 5l0 14" />
                                            <path d="M5 12l14 0" />
                                        </svg> @lang('Crear Compra')
                        </a>
                    </div>
                @endslot
            @endcan
            @include('purchase.partials.purchase_table')
        @endcomponent

        <div class="modal fade product_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

        @include('purchase.partials.update_purchase_status_modal')

    </section>

    <section id="receipt_section" class="print_section"></section>

    <!-- /.content -->
@stop
@section('javascript')
    <script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    <script>
        //Date range as a button
        $('#purchase_list_filter_date_range').daterangepicker(
            dateRangeSettings,
            function(start, end) {
                $('#purchase_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                    moment_date_format));
                purchase_table.ajax.reload();
            }
        );
        $('#purchase_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#purchase_list_filter_date_range').val('');
            purchase_table.ajax.reload();
        });

        $(document).on('click', '.update_status', function(e) {
            e.preventDefault();
            $('#update_purchase_status_form').find('#status').val($(this).data('status'));
            $('#update_purchase_status_form').find('#purchase_id').val($(this).data('purchase_id'));
            $('#update_purchase_status_modal').modal('show');
        });

        $(document).on('submit', '#update_purchase_status_form', function(e) {
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
                        $('#update_purchase_status_modal').modal('hide');
                        toastr.success(result.msg);
                        purchase_table.ajax.reload();
                        $('#update_purchase_status_form')
                            .find('button[type="submit"]')
                            .attr('disabled', false);
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });

        $(document).ready(function() {
            update_statistics();
        });

        function update_statistics() {
        // console.log('------------------------------>', start, end)
        var location_id = '';
        if ($('#dashboard_location').length > 0) {
            location_id = $('#dashboard_location').val();
        }
        var data = { location_id: location_id };
        //get purchase details
        var loader = '<i class="fas fa-sync fa-spin fa-fw margin-bottom"></i>';
        $('.total_purchase').html(loader);
        $('.purchase_due').html(loader);
            $.ajax({
            method: 'get',
            url: '/home/get-totals',
            dataType: 'json',
            data: data,
            success: function (data) {
                //purchase details
                $('.total_purchase').html(__currency_trans_from_en(data.total_purchase, true));
                $('.purchase_due').html(__currency_trans_from_en(data.purchase_due, true));

            },
        });
    }
    
    </script>

@endsection
