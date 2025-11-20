@extends('layouts.app')
@section('title', __('lang_v1.lot_report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">{{ __('lang_v1.lot_report')}}
    
    
      &nbsp; <button style='font-size:36px;color:red'><i class='fab fa-youtube id='modal-video-tutorial' data-toggle="modal" data-target="#stack"></i></button>
					

	    </h4>
       
       
    <div data-width="500" tabindex="-1" class="modal fade" id="stack" style="display: none;">
     <div class="modal-dialog">
        <div class="modal-content" style="padding-bottom: 40px">
               <div class="modal-header">
                  <button type="button" id='close-modal' class="close" data-dismiss="modal" rel=0;aria-hidden="true"></button>
                <div id="title-tutorial">
                Modulo Reportes
                </div>
        </div>
            <div class="modal-body">
                <div id="video-tutorial">
                    
                <iframe width="560" height="315" src="https://www.youtube.com/embed/HBV8Mn4lCyk?si=kYkTV6AbRGAX_cZd" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>                
                </div>
                <p id="description-tutorial">XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX</p>

                
            </div>
        </div>
      </div>

    
    
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => action([\App\Http\Controllers\ReportController::class, 'getStockReport']), 'method' => 'get', 'id' => 'stock_report_filter_form' ]) !!}
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}
                        {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('category_id', __('category.category') . ':') !!}
                        {!! Form::select('category', $categories, null, ['placeholder' => __('messages.all'), 'class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'category_id']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('sub_category_id', __('product.sub_category') . ':') !!}
                        {!! Form::select('sub_category', array(), null, ['placeholder' => __('messages.all'), 'class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'sub_category_id']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('brand', __('product.brand') . ':') !!}
                        {!! Form::select('brand', $brands, null, ['placeholder' => __('messages.all'), 'class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('unit',__('product.unit') . ':') !!}
                        {!! Form::select('unit', $units, null, ['placeholder' => __('messages.all'), 'class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>
                @if(Module::has('Manufacturing'))
                    <div class="col-md-3">
                        <div class="form-group">
                            <br>
                            <div class="checkbox">
                                <label>
                                  {!! Form::checkbox('only_mfg', 1, false, 
                                  [ 'class' => 'input-icheck', 'id' => 'only_mfg_products']); !!} {{ __('manufacturing::lang.only_mfg_products') }}
                                </label>
                            </div>
                        </div>
                    </div>
                @endif
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="lot_report">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>@lang('business.product')</th>
                            <th>@lang('lang_v1.lot_number')</th>
                            <th>@lang('product.exp_date')</th>
                            <th>@lang('report.current_stock')</th>
                            <th>@lang('report.total_unit_sold')</th>
                            <th>@lang('lang_v1.total_unit_adjusted')</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-17 text-center footer-total">
                            <td colspan="4"><strong>@lang('sale.total'):</strong></td>
                            <td id="footer_total_stock"></td>
                            <td id="footer_total_sold"></td>
                            <td id="footer_total_adjusted"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->

@endsection

@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
@endsection