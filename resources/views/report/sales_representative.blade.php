@extends('layouts.app')
@section('title', __('report.sales_representative'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">{{ __('report.sales_representative')}}
    
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


  <button class="btn-youtube" id="openModalBtn">Ver Video</button>

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
    const youtubeURL = ""; // reemplaza con tu video

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
       
       
    

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => action([\App\Http\Controllers\ReportController::class, 'getStockReport']), 'method' => 'get', 'id' => 'sales_representative_filter_form' ]) !!}
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('sr_id',  __('report.user') . ':') !!}
                        {!! Form::select('sr_id', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('report.all_users')]); !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('sr_business_id',  __('business.business_location') . ':') !!}
                        {!! Form::select('sr_business_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">

                        {!! Form::label('sr_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'sr_date_filter', 'readonly']); !!}
                    </div>
                </div>

                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>

    <!-- Summary -->
    <div class="row">
        <div class="col-sm-12">
            @component('components.widget', ['title' => __('report.summary')])
                <h3 class="text-muted">
                    {{ __('report.total_sell') }} - {{ __('lang_v1.total_sales_return') }}: 
                    <span id="sr_total_sales">
                        <i class="fas fa-sync fa-spin fa-fw"></i>
                    </span>
                    -
                    <span id="sr_total_sales_return">
                        <i class="fas fa-sync fa-spin fa-fw"></i>
                    </span>
                    =
                    <span id="sr_total_sales_final">
                        <i class="fas fa-sync fa-spin fa-fw"></i>
                    </span>
                </h3>
                <div class="hide" id="total_payment_with_commsn_div">
                    <h3 class="text-muted">
                        {{ __('lang_v1.total_payment_with_commsn') }}: 
                        <span id="total_payment_with_commsn">
                            <i class="fas fa-sync fa-spin fa-fw"></i>
                        </span>
                    </h3>
                </div>
                <div class="hide" id="total_commission_div">
                    <h3 class="text-muted">
                        {{ __('lang_v1.total_sale_commission') }}: 
                        <span id="sr_total_commission">
                            <i class="fas fa-sync fa-spin fa-fw"></i>
                        </span>
                    </h3>
                </div>
                <h3 class="text-muted">
                    {{ __('report.total_expense') }}: 
                    <span id="sr_total_expenses">
                        <i class="fas fa-sync fa-spin fa-fw"></i>
                    </span>
                </h3>
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <!-- Custom Tabs -->
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#sr_sales_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cog" aria-hidden="true"></i> @lang('lang_v1.sales_added')</a>
                    </li>

                    <li>
                        <a href="#sr_commission_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cog" aria-hidden="true"></i> @lang('lang_v1.sales_with_commission')</a>
                    </li>

                    <li>
                        <a href="#sr_expenses_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cog" aria-hidden="true"></i> @lang('expense.expenses')</a>
                    </li>

                    @if(!empty($pos_settings['cmmsn_calculation_type']) && $pos_settings['cmmsn_calculation_type'] == 'payment_received')
                        <li>
                            <a href="#sr_payments_with_cmmsn_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cog" aria-hidden="true"></i> @lang('lang_v1.payments_with_cmmsn')</a>
                        </li>
                    @endif
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="sr_sales_tab">
                        @include('report.partials.sales_representative_sales')
                    </div>

                    <div class="tab-pane" id="sr_commission_tab">
                        @include('report.partials.sales_representative_commission')
                    </div>

                    <div class="tab-pane" id="sr_expenses_tab">
                        @include('report.partials.sales_representative_expenses')
                    </div>

                    @if(!empty($pos_settings['cmmsn_calculation_type']) && $pos_settings['cmmsn_calculation_type'] == 'payment_received')
                        <div class="tab-pane" id="sr_payments_with_cmmsn_tab">
                            @include('report.partials.sales_representative_payments_with_cmmsn')
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

</section>
<!-- /.content -->
<div class="modal fade view_register" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

@endsection

@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection