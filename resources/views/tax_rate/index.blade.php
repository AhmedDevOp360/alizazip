@extends('layouts.app')
@section('title', __( 'tax_rate.tax_rates' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'tax_rate.tax_rates' )
    
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

    
    
    
       
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'tax_rate.all_your_tax_rates' )])
        @can('tax_rate.create')
            @slot('tool')
                <div class="box-tools">
                    <button class="tw-dw-btn tw-bg--to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-black tw-border-none tw-rounded-full btn-modal pull-right"
                        data-href="{{action([\App\Http\Controllers\TaxRateController::class, 'create'])}}" 
                        data-container=".tax_rate_modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" 
                                        viewBox="0 0 20 20" fill="none" stroke="teal" stroke-width="3" stroke-linecap="round"
                                            stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 5l0 14" />
                                            <path d="M5 12l14 0" />
                                        </svg> @lang('Crear Impuesto')
                    </button>
                </div>
            @endslot
        @endcan
        @can('tax_rate.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tax_rates_table">
                    <thead>
                        <tr>
                            <th>@lang( 'tax_rate.name' )</th>
                            <th>@lang( 'tax_rate.rate' )</th>
                            <th>@lang( 'Código' )</th>
                            <th>@lang( 'messages.action' )</th>
                        </tr>
                    </thead>
                </table>
            </div>
            
            
            
            
        @endcan
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])
        @slot('title')
            @lang( 'tax_rate.tax_groups' ) ( @lang('lang_v1.combination_of_taxes') ) @show_tooltip(__('tooltip.tax_groups'))
            
            
        @endslot
        @can('tax_rate.create')
            @slot('tool')
                <div class="box-tools">
                    {{-- <button type="button" class="btn btn-block btn-primary btn-modal" 
                    data-href="{{action([\App\Http\Controllers\GroupTaxController::class, 'create'])}}" 
                    data-container=".tax_group_modal">
                    <i class="fa fa-plus"></i> @lang( 'messages.add' )</button> --}}
                    <button class="tw-dw-btn tw-bg--to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-black tw-border-none tw-rounded-full btn-modal pull-right"
                        data-href="{{action([\App\Http\Controllers\GroupTaxController::class, 'create'])}}" 
                        data-container=".tax_group_modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" 
                                        viewBox="0 0 20 20" fill="none" stroke="red" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 5l0 14" />
                                            <path d="M5 12l14 0" />
                                        </svg> @lang('Crear Grupo')
                </button>
                </div>
            @endslot
        @endcan
        @can('tax_rate.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tax_groups_table">
                    <thead>
                        <tr>
                            <th>@lang( 'tax_rate.name' )</th>
                            <th>@lang( 'tax_rate.rate' )</th>
                            <th>@lang( 'tax_rate.sub_taxes' )</th>
                            <th>@lang( 'messages.action' )</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent
    
    <div class="modal fade tax_rate_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade tax_group_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
@endsection
