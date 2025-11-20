<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

        {!! Form::open(['url' => action([\App\Http\Controllers\CashRegisterInformationController::class, 'store']), 'method' => 'post', 'id' => 'cash_register_information_add_form' ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Crear nueva Caja Registradora</h4>
        </div>

        <div class="modal-body">
            <div class="row">

                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('plate_number', 'Código de la Caja:') !!}
                        {!! Form::text('plate_number', null, ['class' => 'form-control' ]); !!}
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('cash_type', 'Tipo de Caja:') !!}
                        {!! Form::text('cash_type', null, ['class' => 'form-control' ]); !!}
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('sales_code', 'Código de Venta:*') !!}
                        {!! Form::text('sales_code', null, ['class' => 'form-control', 'required' ]); !!}
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('location_id', 'Sucursal:*') !!} @show_tooltip('Sucursal donde se creará la caja registradora')
                        {!! Form::select('location_id', $business_location, null, ['class' => 'form-control', 'required'
                        ]); !!}
                    </div>
                </div>

            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="tw-dw-btn tw-dw-btn-lg bg-info tw-text-white">@lang( 'Crear Caja' )</button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->