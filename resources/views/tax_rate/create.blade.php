<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\App\Http\Controllers\TaxRateController::class, 'store']), 'method' => 'post', 'id' => 'tax_rate_add_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'tax_rate.add_tax_rate' )</h4>
    </div>

    <div class="modal-body">
      <div class="row">
        <div class="col-md-12">
          <div class="form-group">
            {!! Form::label('name', __( 'tax_rate.name' ) . ':*') !!}
              {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'tax_rate.name' )]); !!}
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('code', __( 'Tipo de Impuesto' ) . ':*') !!}
              {!! Form::select('code', $taxes, null, ['class' => 'form-control', 'required', 'placeholder' => __( 'Seleccione el tipo de impuesto' )]); !!}

          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('amount', __( 'tax_rate.rate' ) . ':*') !!} @show_tooltip(__('lang_v1.tax_exempt_help'))
              {!! Form::text('amount', null, ['class' => 'form-control input_number', 'required']); !!}
          </div>
        </div>
        
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('base', __( 'tax_rate.base' ) . ':') !!}
              {!! Form::text('base', null, ['class' => 'form-control', 'placeholder' => __( 'tax_rate.base' )]); !!}
          </div>
        </div>
        
      </div>
      

      

      <div class="form-group">
        <div class="checkbox">
          <label>
             {!! Form::checkbox('for_tax_group', 1, false, [ 'class' => 'input_icheck']); !!} @lang( 'lang_v1.for_tax_group_only' )
          </label> @show_tooltip(__('lang_v1.for_tax_group_only_help'))
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="submit" class="tw-dw-btn bg-info tw-text-white">@lang( 'Crear Impuesto' )</button>
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
