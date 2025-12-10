@extends('layouts.app')

@section('title', __('cash_register.cash_withdrawals'))

@section('content')
<section class="content-header">
    <h1>@lang('cash_register.cash_withdrawals')</h1>
</section>

<section class="content">
    <div class="box box-solid">
        <div class="box-body">
            {!! Form::model($withdrawal, ['url' => action([\App\Http\Controllers\CashWithdrawalController::class, 'update'], [$withdrawal->id]), 'method' => 'put', 'id' => 'cash_withdrawal_form']) !!}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('location_id',  __('business.business_locations') . ':*') !!}
                            {!! Form::select('location_id', $locations, $withdrawal->location_id, ['class' => 'form-control', 'required']) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('amount',  __('sale.amount') . ':*') !!}
                            {!! Form::text('amount', $withdrawal->amount, ['class' => 'form-control input_number', 'id' => 'withdrawal-amount', 'required']) !!}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('note',  __('sale.note') . ':') !!}
                            {!! Form::textarea('note', $withdrawal->note, ['class' => 'form-control', 'rows' => 3]) !!}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="button" id="submit-withdrawal-form" class="btn btn-primary">@lang('messages.update')</button>
                        <a href="{{ action([\App\Http\Controllers\CashWithdrawalController::class, 'index']) }}" class="btn btn-default">@lang('messages.cancel')</a>
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</section>
@endsection


