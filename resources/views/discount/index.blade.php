@extends('layouts.app')
@section('title', __('sale.discount'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('sale.discount')
        
        
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
    const youtubeURL = "https://www.youtube.com/embed/m6lUxuzLO0I?si=MmLEWxxttefHTmEH"; // reemplaza con tu video

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
                Modulo Descuentos           
                </div>
        </div>
            <div class="modal-body">
                <div id="video-tutorial">
                    
                <iframe width="560" height="315" src="https://www.youtube.com/embed/HBV8Mn4lCyk?si=kYkTV6AbRGAX_cZd" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>                
                </div>
                <p id="description-tutorial">Cree Descuentos por marca, productos o categorias</p>

                
            </div>
        </div>
      </div>
        
        
        </h1>

    </section>

    <!-- Main content -->
    <section class="content">
        <div
            class=" tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw-translate-y-0.5 tw-ring-gray-200">
            <div class="tw-p-4 sm:tw-p-5">
                <div class="tw-flex tw-gap-2.5 tw-justify-end">
                    @can('brand.create')
                            <a class="tw-dw-btn tw-bg--to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-black tw-border-none tw-rounded-full btn-modal pull-right"
                                data-href="{{ action([\App\Http\Controllers\DiscountController::class, 'create']) }}"
                                data-container=".discount_modal">
                                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" 
                                        viewBox="0 0 20 20" fill="none" stroke="teal" stroke-width="3" stroke-linecap="round"
                                            stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 5l0 14" />
                                            <path d="M5 12l14 0" />
                                        </svg> @lang('Crear Descuento')
                            </a>
                    @endcan
                </div>
                <div class="tw-flow-root tw-mt-5 tw-border-b tw-border-gray-200">
                    <div class="tw-mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                            @can('brand.view')
                                <table class="table table-bordered table-striped" id="discounts_table">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all-row" data-table-id="discounts_table"></th>
                                            <th>@lang('unit.name')</th>
                                            <th>@lang('lang_v1.starts_at')</th>
                                            <th>@lang('lang_v1.ends_at')</th>
                                            <th>@lang('sale.discount_amount')</th>
                                            <th>@lang('lang_v1.priority')</th>
                                            <th>@lang('product.brand')</th>
                                            <th>@lang('product.category')</th>
                                            <th>@lang('report.products')</th>
                                            <th>@lang('sale.location')</th>
                                            <th>@lang('messages.action')</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <td colspan="11">
                                                <div style="display: flex; width: 100%;">
                                                    {!! Form::open([
                                                        'url' => action([\App\Http\Controllers\DiscountController::class, 'massDeactivate']),
                                                        'method' => 'post',
                                                        'id' => 'mass_deactivate_form',
                                                    ]) !!}
                                                    {!! Form::hidden('selected_discounts', null, ['id' => 'selected_discounts']) !!}
                                                    {!! Form::submit(__('lang_v1.deactivate_selected'), [
                                                        'class' => 'tw-dw-btn tw-dw-btn-warning tw-text-white tw-dw-btn-xs',
                                                        'id' => 'deactivate-selected',
                                                    ]) !!}
                                                    {!! Form::close() !!}
                                                </div>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade discount_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->
@stop
@section('javascript')
    <script type="text/javascript">
        $(document).on('click', '#deactivate-selected', function(e) {
            e.preventDefault();
            var selected_rows = [];
            var i = 0;
            $('.row-select:checked').each(function() {
                selected_rows[i++] = $(this).val();
            });

            if (selected_rows.length > 0) {
                $('input#selected_discounts').val(selected_rows);
                swal({
                    title: LANG.sure,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $('form#mass_deactivate_form').submit();
                    }
                });
            } else {
                $('input#selected_discounts').val('');
                swal('@lang('lang_v1.no_row_selected')');
            }
        });

        $(document).on('click', '.activate-discount', function(e) {
            e.preventDefault();
            var href = $(this).data('href');
            $.ajax({
                method: "get",
                url: href,
                dataType: "json",
                success: function(result) {
                    if (result.success == true) {
                        toastr.success(result.msg);
                        discounts_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });

        $(document).on('shown.bs.modal', '.discount_modal', function() {
            $('#variation_ids').select2({
                ajax: {
                    url: '/purchases/get_products?check_enable_stock=false&only_variations=true',
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        var results = [];
                        for (var item in data) {
                            results.push({
                                id: data[item].variation_id,
                                text: data[item].text,
                            });
                        }
                        return {
                            results: results,
                        };
                    },
                },
                minimumInputLength: 1,
                closeOnSelect: false
            });
        });

        $(document).on('change', '#variation_ids', function() {
            if ($(this).val().length) {
                $('#brand_input').addClass('hide');
                $('#category_input').addClass('hide');
            } else {
                $('#brand_input').removeClass('hide');
                $('#category_input').removeClass('hide');
            }
        });

        $(document).on('hidden.bs.modal', '.discount_modal', function() {
            $("#variation_ids").select2('destroy');
        });
    </script>
@endsection
