@extends('layouts.app')
@section('title', __('lang_v1.sales_commission_agents'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'lang_v1.sales_commission_agents' )
    
    
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
    const youtubeURL = "https://www.youtube.com/embed/iFzoxFROEx4?si=vKbt1XS8Yhh3_v5a"; // reemplaza con tu video

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
       
       
    <div data-width="500" tabindex="-1" class="modal fade" id="stack" style="display: none;">
     <div class="modal-dialog">
        <div class="modal-content" style="padding-bottom: 40px">
               <div class="modal-header">
                  <button type="button" id='close-modal' class="close" data-dismiss="modal" rel=0;aria-hidden="true"></button>
                <div id="title-tutorial">
                Modulo Comision de Vendedores           
                </div>
        </div>
            <div class="modal-body">
                <div id="video-tutorial">
                    
                <iframe width="560" height="315" src="https://www.youtube.com/embed/HBV8Mn4lCyk?si=kYkTV6AbRGAX_cZd" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>                
                </div>
                <p id="description-tutorial">Asigne comision de ventas para su personal</p>

                
            </div>
        </div>
      </div>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @can('user.create')
            @slot('tool')
                <div class="box-tools">                
                        <a class="tw-dw-btn tw-bg--to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-black tw-border-none tw-rounded-full btn-modal pull-right"
                        data-href="{{action([\App\Http\Controllers\SalesCommissionAgentController::class, 'create'])}}" data-container=".commission_agent_modal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" 
                                        viewBox="0 0 20 20" fill="none" stroke="teal" stroke-width="3" stroke-linecap="round"
                                            stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 5l0 14" />
                                            <path d="M5 12l14 0" />
                                        </svg> @lang('Crear Vendedor')
                        </a>
                    </div>
            @endslot
        @endcan
        @can('user.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="sales_commission_agent_table">
                    <thead>
                        <tr>
                            <th>@lang( 'user.name' )</th>
                            <th>@lang( 'business.email' )</th>
                            <th>@lang( 'lang_v1.contact_no' )</th>
                            <th>@lang( 'business.address' )</th>
                            <th>@lang( 'lang_v1.cmmsn_percent' )</th>
                            <th>@lang( 'messages.action' )</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade commission_agent_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
