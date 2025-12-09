@extends('layouts.app')

@section('title', __('cash_register.cash_withdrawals'))

@section('content')
    <section class="content-header">
        <h1>@lang('cash_register.cash_withdrawals')</h1>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header">
                <div class="box-tools">
                    <a href="{{ action([\App\Http\Controllers\CashWithdrawalController::class, 'create']) }}"
                        class="btn btn-primary">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('location_id', __('business.business_locations') . ':') !!}
                            {!! Form::select('location_id', $locations, null, [
                                'class' => 'form-control',
                                'id' => 'cash_withdrawal_location_filter',
                                'placeholder' => __('messages.all'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('cash_withdrawal_date_range', __('report.date_range') . ':') !!}
                            {!! Form::text('cash_withdrawal_date_range', null, [
                                'class' => 'form-control',
                                'readonly',
                                'id' => 'cash_withdrawal_date_range',
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="cash_withdrawals_table">
                        <thead>
                            <tr>
                                <th>@lang('messages.action')</th>
                                <th>@lang('lang_v1.date')</th>
                                <th>@lang('business.business_location')</th>
                                <th>@lang('cash_register.created_by')</th>
                                <th>@lang('sale.amount')</th>
                                <th>@lang('lang_v1.suspend_note')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            if ($('#cash_withdrawals_table').length) {
                // Date range picker
                $('#cash_withdrawal_date_range').daterangepicker(
                    dateRangeSettings,
                    function(start, end) {
                        $('#cash_withdrawal_date_range').val(start.format(moment_date_format) + ' ~ ' + end
                            .format(moment_date_format));
                        cash_withdrawals_table.ajax.reload();
                    }
                );
                $('#cash_withdrawal_date_range').on('cancel.daterangepicker', function(ev, picker) {
                    $('#cash_withdrawal_date_range').val('');
                    cash_withdrawals_table.ajax.reload();
                });

                window.cash_withdrawals_table = $('#cash_withdrawals_table').DataTable({
                    processing: true,
                    serverSide: true,
                    aaSorting: [
                        [1, 'desc']
                    ],
                    ajax: {
                        url: '{{ action([\App\Http\Controllers\CashWithdrawalController::class, 'index']) }}',
                        data: function(d) {
                            d.location_id = $('#cash_withdrawal_location_filter').val();
                            var start = '';
                            var end = '';
                            if ($('#cash_withdrawal_date_range').val()) {
                                var drp = $('#cash_withdrawal_date_range').data('daterangepicker');
                                start = drp.startDate.format('YYYY-MM-DD');
                                end = drp.endDate.format('YYYY-MM-DD');
                            }
                            d.start_date = start;
                            d.end_date = end;
                        }
                    },
                    columns: [{
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'created_at',
                            name: 'cash_withdrawals.created_at'
                        },
                        {
                            data: 'location',
                            name: 'business_locations.name'
                        },
                        {
                            data: 'user',
                            name: 'users.username'
                        },
                        {
                            data: 'amount',
                            name: 'cash_withdrawals.amount'
                        },
                        {
                            data: 'note',
                            name: 'cash_withdrawals.note'
                        },
                    ]
                });

                $('#cash_withdrawal_location_filter').change(function() {
                    cash_withdrawals_table.ajax.reload();
                });
            }
        });
    </script>
@endsection
