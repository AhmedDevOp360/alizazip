<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

        {!! Form::open(['url' => action([\App\Http\Controllers\CashRegisterInformationController::class, 'update'], [$cash_register_information->id]), 'method' => 'PUT', 'id' => 'cash_register_information_add_form' ]) !!}

        {!! Form::hidden('hidden_id', $cash_register_information->id, ['id' => 'hidden_id']); !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Editar Caja Registradora</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('plate_number', 'Código de Caja:*') !!}
                        {!! Form::text('plate_number', $cash_register_information->plate_number, ['class' => 'form-control', 'required' ]); !!}
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('cash_type','Tipo de Caja:*') !!}
                        {!! Form::text('cash_type', $cash_register_information->cash_type, ['class' => 'form-control', 'required' ]); !!}
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('sales_code',  'Código de Ventas:*') !!}
                        {!! Form::text('sales_code', $cash_register_information->sales_code, ['class' => 'form-control', 'required']); !!}
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('location_id', 'Sucursal:*') !!}
                        {!! Form::select('location_id', $business_location, $cash_register_information->location_id, ['class' => 'form-control', 'required']); !!}
                    </div>
                </div>
                
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="tw-dw-btn tw-dw-btn-lg bg-info tw-text-white">@lang( 'Actualizar' )</button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->