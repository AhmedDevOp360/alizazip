@extends('layouts.app')
@section('title', __('lang_v1.customer_groups_report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">{{ __('lang_v1.customer_groups_report')}}
    
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

              {!! Form::open(['url' => action([\App\Http\Controllers\ReportController::class, 'getCustomerGroup']), 'method' => 'get', 'id' => 'cg_report_filter_form' ]) !!}
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('cg_customer_group_id', __( 'lang_v1.customer_group_name' ) . ':') !!}
                        {!! Form::select('cg_customer_group_id', $customer_group, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'cg_customer_group_id']); !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('cg_location_id',  __('purchase.business_location') . ':') !!}
                        {!! Form::select('cg_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('cg_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'cg_date_range', 'readonly']); !!}
                    </div>
                </div>

                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="cg_report_table">
                    <thead>
                        <tr>
                            <th>@lang('lang_v1.customer_group')</th>
                            <th>@lang('report.total_sell')</th>
                        </tr>
                    </thead>
                </table>
            </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->

@endsection

@section('javascript')
    
    <script type="text/javascript">
        $(document).ready(function(){
            if($('#cg_date_range').length == 1){
                $('#cg_date_range').daterangepicker(
                    dateRangeSettings,
                    function (start, end) {
                        $('#cg_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                        cg_report_table.ajax.reload();
                    }
                );

                $('#cg_date_range').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    cg_report_table.ajax.reload();
                });
            }

            cg_report_table = $('#cg_report_table').DataTable({
                            processing: true,
                            serverSide: true,
                            fixedHeader:false,
                            "ajax": {
                                "url": "/reports/customer-group",
                                "data": function ( d ) {
                                    d.location_id = $('#cg_location_id').val();
                                    d.customer_group_id = $('#cg_customer_group_id').val();
                                    d.start_date = $('#cg_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                                    d.end_date = $('#cg_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                                }
                            },
                            columns: [
                                {data: 'name', name: 'CG.name'},
                                {data: 'total_sell', name: 'total_sell', searchable: false}
                            ],
                            "fnDrawCallback": function (oSettings) {
                                __currency_convert_recursively($('#cg_report_table'));
                            }
                        });
            //Customer Group report filter
            $('select#cg_location_id, select#cg_customer_group_id, #cg_date_range').change( function(){
                cg_report_table.ajax.reload();
            });
        })
    </script>
@endsection