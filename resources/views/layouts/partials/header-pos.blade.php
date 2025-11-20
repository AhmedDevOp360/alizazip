<!-- default value -->
@php
    $go_back_url = action([\App\Http\Controllers\SellPosController::class, 'index']);
    $transaction_sub_type = '';
    $view_suspended_sell_url = action([\App\Http\Controllers\SellController::class, 'index']) . '?suspended=1';
    $pos_redirect_url = action([\App\Http\Controllers\SellPosController::class, 'create']);
@endphp

@if (!empty($pos_module_data))
    @foreach ($pos_module_data as $key => $value)
        @php
            if (!empty($value['go_back_url'])) {
                $go_back_url = $value['go_back_url'];
            }

            if (!empty($value['transaction_sub_type'])) {
                $transaction_sub_type = $value['transaction_sub_type'];
                $view_suspended_sell_url .= '&transaction_sub_type=' . $transaction_sub_type;
                $pos_redirect_url .= '?sub_type=' . $transaction_sub_type;
            }
        @endphp
    @endforeach
@endif
<input type="hidden" name="transaction_sub_type" id="transaction_sub_type" value="{{ $transaction_sub_type }}">
@inject('request', 'Illuminate\Http\Request')
<div class="row" style="display:flex; margin-top: 20px">
    <div class="col-md-3 no-print pos-header" style="margin-left: 20px">
        <input type="hidden" id="pos_redirect_url" value="{{ $pos_redirect_url }}">
        <div class="tw-flex tw-flex-col md:tw-flex-row tw-items-center tw-justify-between tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white tw-rounded-xl tw-mx-0 tw-mt-1 tw-mb-0 md:tw-mb-0 tw-p-4" 
                 style="overflow: hidden; flex-shrink: 1;min-width: 0; margin-left: 6px;">
            <div class="tw-w-full" >
                
                <div class="tw-flex tw-items-center tw-gap-1">
                    <p style="height: 20px; width: auto; font-size:10px"><strong>@lang('sale.location'): &nbsp;</strong></p>

                    <div>
                        @if (count($business_locations) > 1)
                            @php
                                $locOptions = [];
                                foreach($business_locations as $id => $name){
                                    $location = optional(\App\BusinessLocation::find($id));
                                    $locOptions[$id] = ['value' => $id, 'label' => $name, 'code' => $location->location_id, 'pgId' => $location->selling_price_group_id];
                               }
                            @endphp
                            <select name="select_location_id" id="select_location_id" class="control input-sm" required autofocus style="background:white; border:1px solid black; width: 120px;">
                                @foreach($locOptions as $id => $opt)
                                    <option value="{{$opt['value']}}" data-location-code="{{$opt['code']}}" data-default_price_group="{{ $opt['pgId'] }}" @if(($default_location->id ?? null) == $opt['value']) selected @endif>{{$opt['label']}}</option>
                                @endforeach
                            </select>
                        @else
                            {{ isset($business_locations[0])? $business_locations[0]->name : '' }}
                        @endif
                    </div>  
                    
                    <div class="tw-flex tw-w-full tw-gap-1 tw-items-end">
                        <div style="width: 10px; max-width: 20px"></div>
                        <!-- Botón rojo -->
                        {{-- <button type="button" class="btn btn-danger" style="align-content: flex-end">
                            <i class="bi bi-pause"></i>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pause" viewBox="0 0 16 16">
                            <path d="M6 3.5a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-1 0V4a.5.5 0 0 1 .5-.5m4 0a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-1 0V4a.5.5 0 0 1 .5-.5"/>
                            </svg>
                        </button> --}}
                        <!-- Botón rojo Nuevo -->
                        @if (!Gate::check('disable_suspend_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                            @if (empty($pos_settings['disable_suspend']))
                                <button type="button"
                                    class="btn btn-danger no-print pos-express-finalize" style="align-content: flex-end"
                                    data-pay_method="suspend"
                                    @if (!empty($only_payment)) disabled @endif>
                                    <i class="bi bi-pause"></i>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pause" viewBox="0 0 16 16">
                                        <path d="M6 3.5a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-1 0V4a.5.5 0 0 1 .5-.5m4 0a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-1 0V4a.5.5 0 0 1 .5-.5"/>
                                    </svg>
                                </button>
                            @endif
                        @endif
                        <!-- Botón verde -->
                        {{-- <button type="button" class="btn btn-success">
                            <i class="bi bi-play-fill"></i>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-play-fill" viewBox="0 0 16 16">
                            <path d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393"/>
                            </svg>
                        </button> --}}
                        {{-- Boton Verde Nuevo --}}
                        @if (!Gate::check('disable_draft') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                            {{-- <button type="button" class="btn btn-success  @if ($pos_settings['disable_draft'] != 0) hide @endif"
                                id="pos-draft" @if (!empty($only_payment)) disabled @endif>
                                <i class="bi bi-play-fill"></i> 
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-play-fill" viewBox="0 0 16 16">
                                <path d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393"/>
                                </svg>
                            </button> --}}
                            <button type="button" id="view_suspended_sales" title="{{ __('lang_v1.  view_suspended_sales') }}"
                                    class="btn btn-success btn-modal pull-right"
                                    data-container=".view_modal" data-href="{{ $view_suspended_sell_url }}">
                          
                                <i class="bi bi-play-fill"></i> 
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-play-fill" viewBox="0 0 16 16">
                                <path d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393"/>
                                </svg>
                           
                            </button>
                         @endif

                        
                    </div>
                
                    
                    
                    
                    <div
                        class="">
                         {{--<span
                            class="curr_datetime text-black tw-font-semibold">{{ @format_datetime('now') }}</span>--}}
                        
                    </div>&nbsp;&nbsp;
                    @if (empty($pos_settings['hide_product_suggestion']))
                        <button type="button" title="{{ __('lang_v1.view_products') }}" data-placement="bottom"
                            class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white hover:tw-bg-white/60 tw-cursor-pointer tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-md tw-w-8 tw-h-8 tw-text-gray-600 btn-modal pull-right tw-block md:tw-hidden"
                            data-toggle="modal" data-target="#mobile_product_suggestion_modal">
                            <strong><i class="fa fa-cubes fa-lg tw-text-!tw-text-sm" style="font-size:24px ;color:purple"></i></strong>
                        </button>
                    @endif
                    <span class="tw-block md:tw-hidden">
                        <i class="fas hamburger fa-bars tw-mx-5"
                            onclick="document.getElementById('pos_header_more_options').classList.toggle('tw-hidden')"></i>
                    </span>
                   
                </div>
            </div>
             
            <div class="tw-w-full md:tw-w-2/3 !tw-p-0 tw-flex tw-items-center tw-justify-between tw-gap-4 tw-flex-col md:tw-flex-row tw-hidden md:tw-flex"
                id="pos_header_more_options">
                <a href="{{ $go_back_url }}" title="{{ __('lang_v1.go_back') }}"
                    class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white hover:tw-bg-white/60 tw-cursor-pointer tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-md md:tw-w-8 tw-w-auto tw-h-8 tw-text-gray-600 pull-right">
                    <strong class="!tw-m-3">
                        <i class="fa fa-backward fa-lg fa fa-backward" style=" font-size:25px ;color:black"></i>
                        <span class="tw-inline md:tw-hidden">{{ __('lang_v1.go_back') }}</span>
                    </strong>
                </a>

                @if (!empty($pos_settings['customer_display_screen']))
                    <a href="{{route('pos_display')}}" id="customer_display_screen"  onclick="window.open(this.href, 'customer_display', 'width='+screen.width+',height='+screen.height+',top=0,left=0'); return false;"   title="{{ __('lang_v1.customer_display_screen') }}"
                        class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white hover:tw-bg-white/60 tw-cursor-pointer tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-md md:tw-w-8 tw-w-auto tw-h-8 tw-text-gray-600 pull-right">
                        <strong class="!tw-m-3">
                            <i class="fa fa-tv fa-lg tw-text-[#646EE4] !tw-text-sm"></i>
                            <span class="tw-inline md:tw-hidden">{{ __('lang_v1.customer_display_screen') }}</span>
                        </strong>
                    </a>
                @endif
                
            </div>
                {{--
                @if (!isset($pos_settings['hide_recent_trans']) || $pos_settings['hide_recent_trans'] == 0)
                    <button type="button"
                        class="md:tw-hidden tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white hover:tw-bg-white/60 tw-cursor-pointer tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-md md:tw-w-8 tw-w-auto tw-h-8 tw-text-gray-600 pull-right"
                        data-toggle="modal" data-target="#recent_transactions_modal" id="recent-transactions">
                            <strong class="!tw-m-3">
                                <i class="fa fa-clock fa-lg tw-text-[#646EE4] !tw-text-sm"></i>
                                <span class="tw-inline md:tw-hidden">{{ __('lang_v1.recent_transactions') }}</span>
                            </strong>
                    </button>
                @endif

                @if (!empty($pos_settings['inline_service_staff']))
                    <button type="button" id="show_service_staff_availability"
                        title="{{ __('lang_v1.service_staff_availability') }}"
                        class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white hover:tw-bg-white/60 tw-cursor-pointer tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-md md:tw-w-8 tw-w-auto tw-h-8 tw-text-gray-600 pull-right"
                        data-container=".view_modal"
                        data-href="{{ action([\App\Http\Controllers\SellPosController::class, 'showServiceStaffAvailibility']) }}">
                        <strong class="!tw-m-3">
                            <i class="fa fa-users fa-lg tw-text-[#646EE4] !tw-text-sm"></i>
                            <span class="tw-inline md:tw-hidden">{{ __('lang_v1.service_staff_availability') }}</span>
                        </strong>
                    </button>
                @endif

                @can('close_cash_register')
                    <button type="button" id="close_register" title="{{ __('cash_register.close_register') }}"
                        class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white hover:tw-bg-white/60 tw-cursor-pointer tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-md md:tw-w-8 tw-w-auto tw-h-8 tw-text-gray-600 btn-modal pull-right"
                        data-container=".close_register_modal"
                        data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getCloseRegister']) }}">
                        <strong class="!tw-m-3">
                            <i class="fa fa-window-close fa-lg tw-text-[#EF4B53] !tw-text-sm"></i>
                            <span class="tw-inline md:tw-hidden">{{ __('cash_register.close_register') }}</span>
                        </strong>
                    </button>
                @endcan

                @if (
                    !empty($pos_settings['inline_service_staff']) ||
                        (in_array('tables', $enabled_modules) || in_array('service_staff', $enabled_modules)))
                    <button type="button"
                        class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white hover:tw-bg-white/60 tw-cursor-pointer tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-md md:tw-w-8 tw-w-auto tw-h-8 tw-text-gray-600 pull-right popover-default"
                        id="service_staff_replacement" title="{{ __('restaurant.service_staff_replacement') }}"
                        data-toggle="popover" data-trigger="click"
                        data-content='<div class="m-8"><input type="text" class="form-control" placeholder="@lang('sale.invoice_no')" id="send_for_sell_service_staff_invoice_no"></div><div class="w-100 text-center"><button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error" id="send_for_sercice_staff_replacement">@lang('lang_v1.send')</button></div>'
                        data-html="true" data-placement="bottom">

                        <strong class="!tw-m-3">
                            <i class="fa fa-user-plus fa-lg tw-text-[#646EE4] !tw-text-sm"></i>
                            <span class="tw-inline md:tw-hidden">{{ __('restaurant.service_staff_replacement') }}</span>
                        </strong>
                    </button>
                @endif

                @can('view_cash_register')
                    <button type="button" id="register_details" title="{{ __('cash_register.register_details') }}"
                        class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white hover:tw-bg-white/60 tw-cursor-pointer tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-md md:tw-w-8 tw-w-auto tw-h-8 tw-text-gray-600 btn-modal pull-right"
                        data-container=".register_details_modal"
                        data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getRegisterDetails']) }}">

                        <strong class="!tw-m-3">
                            <i class="fa fa-briefcase tw-fa-lg tw-text-[#00935F] !tw-text-sm" aria-hidden="true"></i>
                            <span class="tw-inline md:tw-hidden">{{ __('cash_register.register_details') }}</span>
                        </strong>
                    </button>
                @endcan

                <button title="@lang('lang_v1.calculator')" id="btnCalculator" type="button"
                    class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white hover:tw-bg-white/60 tw-cursor-pointer tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-md md:tw-w-8 tw-w-auto tw-h-8 tw-text-gray-600 pull-right popover-default"
                    data-toggle="popover" data-trigger="click" data-content='@include('layouts.partials.calculator')' data-html="true"
                    data-placement="bottom">


                    <strong class="!tw-m-3">
                        <i class="fa fa-calculator fa-lg tw-text-[#00935F] !tw-text-sm" aria-hidden="true"></i>
                        <span class="tw-inline md:tw-hidden">{{ __('lang_v1.calculator') }}</span>
                    </strong>
                </button>

                <button type="button"
                    class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white hover:tw-bg-white/60 tw-cursor-pointer tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-md md:tw-w-8 tw-w-auto tw-h-8 tw-text-gray-600 pull-right pull-right popover-default"
                    id="return_sale" title="@lang('lang_v1.sell_return')" data-toggle="popover" data-trigger="click"
                    data-content='<div class="m-8"><input type="text" class="form-control" placeholder="@lang('sale.invoice_no')" id="send_for_sell_return_invoice_no"></div><div class="w-100 text-center"><button type="button" class="tw-dw-btn tw-dw-btn-error tw-text-white tw-dw-btn-sm" id="send_for_sell_return">@lang('lang_v1.send')</button></div>'
                    data-html="true" data-placement="bottom">
                    <strong class="!tw-m-3">
                        <i class="fas fa-undo fa-lg tw-text-[#EF4B53] !tw-text-sm"></i>
                        <span class="tw-inline md:tw-hidden">{{ __('lang_v1.sell_return') }}</span>
                    </strong>
                </button>


                <button type="button" title="{{ __('lang_v1.full_screen') }}"
                    class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white hover:tw-bg-white/60 tw-cursor-pointer tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-md md:tw-w-8 tw-w-auto tw-h-8 tw-text-gray-600 pull-right"
                    id="full_screen">
                    <strong class="!tw-m-3">
                        <i class="fa fa-window-maximize fa-lg tw-text-[#646EE4] !tw-text-sm"></i>
                        <span class="tw-inline md:tw-hidden">Full Screen</span>
                    </strong>
                </button>      

                <button type="button" id="view_suspended_sales" title="{{ __('lang_v1.view_suspended_sales') }}"
                    class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white hover:tw-bg-white/60 tw-cursor-pointer tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-md md:tw-w-8 tw-w-auto tw-h-8 tw-text-gray-600 btn-modal pull-right"
                    data-container=".view_modal" data-href="{{ $view_suspended_sell_url }}">
                    <strong class="!tw-m-3">
                        <i class="fa fa-pause-circle fa-lg tw-text-[#A5ADBB] !tw-text-sm"></i>
                        <span class="tw-inline md:tw-hidden">{{ __('lang_v1.view_suspended_sales') }}</span>
                    </strong>
                </button>
                

                @if (Module::has('Repair') && $transaction_sub_type != 'repair')
                    @include('repair::layouts.partials.pos_header')
                @endif
                
            </div> --}}
            
        </div>
    </div>
    
                            <div class="col-md-1/3  tw-w-auto tw-cursor-pointer  main-category-div main-category no-print"
                                data-value="all" data-parent="0">
                                <div class="tw-dw-card tw-w-25 tw-bg-base-100 tw-shadow-sm tw-h-auto tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white hover:tw-bg-white/60 tw-cursor-pointer !tw-text-xs md:!tw-text-sm tw-font-semibold tw-text-center tw-border-2">
                                    <div class="tw-dw-card-body">
                                        <h4 class="tw-flex tw-items-center tw-justify-center" style="align-text: center; font-size: inherit; font-weight: inherit; margin-bottom: -20px; margin-top: 0px">@lang('lang_v1.all_categories')</h4>
                                    </div>
                                </div>
                            </div>


    <div class="col-md-7" >
        <div class="d-flex justify-content-center mt-3 tw-h-auto" style="margin:0px 20px 0px 19px">
            <div  id="categories-container" style="display:flex;gap:10px; overflow-y: auto; padding-bottom: 10px;wid">
                @foreach ($categories as $index => $category)

                <button type="button" class="col-md-1/3 tw-dw-btn btn-secondary tw-dw-btn-sm main-category" style="margin-top: 10px; ; height:5vh; font-size: 15px; background-color: white; border: none" data-value="{{ $category['id'] }}" data-parent="0">
                    <div class=" col-xs-12 tw-mb-7 tw-w-auto tw-cursor-pointer main-category-div  no-print" style="margin-bottom: 0px"
                        data-value="{{ $category['id'] }}" data-name="{{ $category['name'] }}" data-parent="1">
                        <h4 style="align-text: center; font-size: inherit; font-weight: inherit; margin-bottom: 0px; margin-top: 0px">
                            {{ $category['name'] }}</h4>
                        <div class="tw-dw-card-actions tw-justify-center">
                        </div>
                    </div>
                </button>
                @endforeach

                @if (in_array('pos_sale', $enabled_modules) && !empty($transaction_sub_type))
                    @can('sell.create')
                        <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}"
                            title="@lang('sale.pos_sale')"
                            class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white hover:tw-bg-white/60 tw-cursor-pointer tw-border-2 tw-w-auto tw-h-auto tw-py-1 tw-px-4 tw-rounded-md  ">
                            <strong><i class="fa fa-th-large tw-text-[#00935F] !tw-text-sm"></i> &nbsp;
                                @lang('sale.pos_sale')</strong>
                        </a>
                    @endcan
                @endif
            </div>

            {{-- <div class="d-flex justify-content-center mt-3">
                <button class="btn btn-secondary me-2" id="prev-page">Anterior</button>
                <button class="btn btn-secondary" id="next-page">Siguiente</button>
            </div> --}}
        </div>

    </div>
    
    
    
    <div class="col-md-2">
        <div class="d-flex justify-content-center mt-3 tw-h-auto m-0 no-print">
            <div  id="categories-container" style="display:flex;gap:10px; overflow-y: auto; padding-bottom: 10px;wid">
                @can('expense.add')
                    <button type="button" title="{{ __('expense.add_expense') }}" data-placement="bottom"
                        class="tw-bg-white tw-dw-btn tw-cursor-pointer btn-modal"
                        style="margin-top: 10px; height: 5vh; font-size: 15px" id="add_expense">
                        <strong><i class="fa fas fa-minus-circle" style=" font-size:14px ;color:red"></i> @lang('expense.add_expense')</strong>
                    </button>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="service_staff_modal" tabindex="-1" role="dialog"
    aria-labelledby="gridSystemModalLabel">
</div>
<script>
    $(document).ready(function () {
        const itemsPerPage = 6;
        let currentPage = 1;

        const $items = $('.category-item');
        const totalItems = $items.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        function showPage(page) {
            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;

            $items.hide();
            $items.slice(start, end).show();

            $('#prev-page').prop('disabled', page === 1);
            $('#next-page').prop('disabled', page === totalPages);
        }

        $('#prev-page').click(function () {
            if (currentPage > 1) {
                currentPage--;
                showPage(currentPage);
            }
        });

        $('#next-page').click(function () {
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
            }
        });

        showPage(currentPage); // Inicializar
    });
</script>
