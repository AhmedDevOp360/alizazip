@extends('layouts.app')
@section('title', __( 'Transacciones'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('Transacciones')
     
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
    const youtubeURL = "https://www.youtube.com/embed/YQLbutF2teM?si=qLBfVyq57bJCmcHR"; // reemplaza con tu video

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
    
    
     <p class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap"> Esto es lo que pasa en tu tienda hoy</p>
      </h4>
      
      
			
			
		   <div class="tw-grid tw-grid-cols-1 tw-gap-4 tw-mt-6 sm:tw-grid-cols-2 xl:tw-grid-cols-4 sm:tw-gap-5">
                            
                                <div
                                     class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                    <div class="tw-p-4 sm:tw-p-5">
                                        <div class="tw-flex tw-items-center tw-gap-4">
                                            <div
                                                class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-green-500 tw-bg-green-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 tw-shrink-0">
                                                <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                    <path
                                                        d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2">
                                                    </path>
                                                    <path
                                                        d="M14.8 8a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1">
                                                    </path>
                                                    <path d="M12 6v10"></path>
                                                </svg>
                                            </div>

                                            <div class="tw-flex-1 tw-min-w-0">
                                                <p
                                                    class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                    {{ __('Ingresos') }}
                                                </p>
                                                
                                                {{-- <span class="display_currency" data-currency_symbol="true">{{$data['net_profit']}}</span> --}}
                                                
                                                
                                                <p
                                                    class="total_sell tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
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
                                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-red-500 tw-bg-red-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path
                                                    d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2">
                                                </path>
                                                <path
                                                    d="M14.8 8a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1">
                                                </path>
                                                <path d="M12 6v10"></path>
                                            </svg>
                                        </div>

                                        <div class="tw-flex-1 tw-min-w-0">
                                            <p
                                                class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                {{ __('lang_v1.expense') }}
                                            </p>
                                            
                                            {{-- <span class="display_currency" data-currency_symbol="true">{{$data['net_profit']}}</span> --}}
                                            
                                            <p
                                                class="total_expense tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">

                                            </p>
                                        </div>
                                        <a href="https://sistema.ziscoplus.com/expenses">Ver Más</a>
                                    </div>
                                </div>
                            </div>
        
                            
                            
                            
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
                                                <p>
                                            <p
                                                class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                {{ __('lang_v1.potential_profit') }}
                                                
                                            </p>
                                            {{-- <span class="display_currency" data-currency_symbol="true">{{$data['net_profit']}}</span> --}}
                                            <p
                                                class="gross_profit tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">

                                            </p>
                                        </div>
                                        <a href="https://sistema.ziscoplus.com/reports/profit-loss">Ver Más</a>
                                    </div>
                                </div>
                            </div>  
                            
                            
                            
                            
                            
                          <div
                                 class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                    <div class="tw-p-4 sm:tw-p-5">
                                        <div class="tw-flex tw-items-center tw-gap-4">
                                            <div
                                                class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-yellow-500 tw-bg-yellow-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                                <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                    <path
                                                        d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                    <path d="M9 7l1 0" />
                                                    <path d="M9 13l6 0" />
                                                    <path d="M13 17l2 0" />
                                                </svg>
                                            </div>

                                            <div class="tw-flex-1 tw-min-w-0">
                                            <p
                                                class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                                {{ __('lang_v1.my_inventary') }}
                                                
                                            </p>
                                            {{-- <span class="display_currency" data-currency_symbol="true">{{$data['net_profit']}}</span> --}}
                                            <p id="closing_stock_by_pp"
                                                class="closing_stock_by_pp tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">

                                            </p>
                                        </div>
                                        <a href="https://sistema.ziscoplus.com/reports/stock-report">Ver Más</a>
                                    </div>
                                </div>
                            </div>  
                            	
			
    
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        @include('sell.partials.sell_list_filters')
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __( '')])
        @can('sell.create')
            @slot('tool')
                <div class="box-tools">
                        <a class="tw-dw-btn tw-bg--to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-black tw-border-none tw-rounded-full pull-right"
                            href="{{ action([\App\Http\Controllers\SellController::class, 'create']) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" 
                                        viewBox="0 0 20 20" fill="none" stroke="teal" stroke-width="3" stroke-linecap="round"
                                            stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 5l0 14" />
                                            <path d="M5 12l14 0" />
                                        </svg> @lang('Crear Factura Electrónica')
                        </a>
                      
                        

                                    <a class="tw-dw-btn tw-bg--to-r tw-from-600 tw-to-blue-500 tw-font-bold tw-text-black tw-border-none tw-full pull-right tw-m-2"
                                        href="{{ action([\App\Http\Controllers\ReportController::class, 'getproductSellReport']) }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-dash-circle-fill" viewBox="0 0 16 16">
                                      <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M4.5 7.5a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1z"/>
                                    </svg> @lang('Ver Salidas ')
                                    </a>
                                    
                                    
                                    <a class="tw-dw-btn tw-bg--to-r tw-from-600 tw-to-blue-500 tw-font-bold tw-text-black tw-border-none tw-full pull-right tw-m-2"
                                        href="{{ action([\App\Http\Controllers\ProductController::class, 'index']) }}">
                                        
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-box-seam-fill" viewBox="0 0 16 16">
                                          <path fill-rule="evenodd" d="M15.528 2.973a.75.75 0 0 1 .472.696v8.662a.75.75 0 0 1-.472.696l-7.25 2.9a.75.75 0 0 1-.557 0l-7.25-2.9A.75.75 0 0 1 0 12.331V3.669a.75.75 0 0 1 .471-.696L7.443.184l.01-.003.268-.108a.75.75 0 0 1 .558 0l.269.108.01.003zM10.404 2 4.25 4.461 1.846 3.5 1 3.839v.4l6.5 2.6v7.922l.5.2.5-.2V6.84l6.5-2.6v-.4l-.846-.339L8 5.961 5.596 5l6.154-2.461z"/>
                                        </svg> @lang('Ver Productos')
                                    </a>
                                    
                              
                        
                </div>
                      
            @endslot
        @endcan
        
        
        @can('sell.view')
            <input type="hidden" name="is_direct_sale" id="is_direct_sale" value="0">
            @include('sale_pos.partials.sales_table')
        @endcan
    @endcomponent
</section>
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade register_details_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<!-- This will be printed -->
<section class="invoice print_section" id="receipt_section">
</section> 


@stop

@section('javascript')
@include('sale_pos.partials.sale_table_javascript')
<script src="{{ asset('js/payment.js?v=' . $asset_v . time()) }}"></script>
@endsection
