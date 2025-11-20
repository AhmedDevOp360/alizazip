@extends('layouts.app')

@section('title', __('report.register_report'))

@section('content')
<section class="content-header">
    <h1>@lang('report.register_report') <small>@lang('lang_v1.non_pos')</small></h1>
</section>

<section class="content no-print">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>@lang('report.date_range'):</label>
                                <input type="text" id="np_date_range" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>@lang('business.location'):</label>
                                {!! Form::select('location_id', $locations, null, ['class' => 'form-control', 'id' => 'np_location_id']) !!}
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <button class="btn btn-primary" id="np_filter">@lang('lang_v1.report_filter')</button>
                            <button class="btn btn-default" id="np_print">@lang('messages.print')</button>
                            <button class="btn btn-success" id="np_export">@lang('lang_v1.report_export_to_excel')</button>
                            <button class="btn btn-info" id="np_open_general">@lang('lang_v1.open_general_box')</button>
                            <button class="btn btn-danger" id="np_close_general">@lang('lang_v1.close_general_box')</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="small-box bg-aqua">
                                <div class="inner">
                                    <h3 id="np_opening">0</h3>
                                    <p>@lang('lang_v1.opening_balance') - Caja Menor</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h3 id="np_ingresos">0</h3>
                                    <p>@lang('lang_v1.income')</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-red">
                                <div class="inner">
                                    <h3 id="np_egresos">0</h3>
                                    <p>@lang('expense.expenses')</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-yellow">
                                <div class="inner">
                                    <h3 id="np_final">0</h3>
                                    <p>@lang('lang_v1.closing_balance') - @lang('lang_v1.small_cash')</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel-group" id="np_accordion">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#np_accordion" href="#np_caja_menor">Caja Menor</a>
                                </h4>
                            </div>
                            <div id="np_caja_menor" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <table class="table table-striped" id="np_tbl_caja">
                                        <thead>
                                        <tr>
                                            <th>@lang('messages.date')</th>
                                            <th>@lang('contact.contact')</th>
                                            <th>@lang('lang_v1.description')</th>
                                            <th class="text-right">@lang('sale.amount')</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#np_accordion" href="#np_ingresos_panel">Ingresos (Ventas, Abonos)</a>
                                </h4>
                            </div>
                            <div id="np_ingresos_panel" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <table class="table table-striped" id="np_tbl_ingresos">
                                        <thead>
                                        <tr>
                                            <th>@lang('messages.date')</th>
                                            <th>@lang('contact.contact')</th>
                                            <th>@lang('lang_v1.description')</th>
                                            <th class="text-right">@lang('sale.amount')</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#np_accordion" href="#np_egresos_panel">Egresos (Gastos, Anticipos, Pagos)</a>
                                </h4>
                            </div>
                            <div id="np_egresos_panel" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <table class="table table-striped" id="np_tbl_egresos">
                                        <thead>
                                        <tr>
                                            <th>@lang('messages.date')</th>
                                            <th>@lang('contact.contact')</th>
                                            <th>@lang('lang_v1.description')</th>
                                            <th class="text-right">@lang('sale.amount')</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="content print-section hide">
    <div id="np_print_container"></div>
</section>

@endsection

@section('javascript')
<script>
$(function () {
    function __date_format(dateStr) {
        try {
            return moment(dateStr).format(moment_date_format + ' ' + moment_time_format);
        } catch (e) {
            return dateStr;
        }
    }
    $('#np_date_range').daterangepicker(
        $.extend({}, dateRangeSettings, {
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
                'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
            },
            showCustomRangeLabel: true
        }),
        function (start, end) {
            $('#np_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
        }
    );
    $('#np_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
    // Set default to today ~ today
    (function(){
        var today = moment();
        var drp = $('#np_date_range').data('daterangepicker');
        if (drp) {
            drp.setStartDate(today);
            drp.setEndDate(today);
            $('#np_date_range').val(today.format(moment_date_format) + ' ~ ' + today.format(moment_date_format));
        }
    })();

    function loadData() {
        var drp = $('#np_date_range').data('daterangepicker');
        var start = drp ? drp.startDate.format('YYYY-MM-DD') : moment().format('YYYY-MM-DD');
        var end = drp ? drp.endDate.format('YYYY-MM-DD') : start;
        var location_id = $('#np_location_id').val();

        $.getJSON('/reports/non-pos-closing/data', { start_date: start, end_date: end, location_id: location_id }, function (resp) {
            $('#np_opening').text(__currency_trans_from_en(resp.opening.caja_menor, true));
            $('#np_ingresos').text(__currency_trans_from_en(resp.totals.ingresos, true));
            $('#np_egresos').text(__currency_trans_from_en(resp.totals.egresos, true));
            $('#np_final').text(__currency_trans_from_en(resp.totals.final_caja_menor, true));

            // Petty cash: show cash-method movements (income positive, expense negative)
            var $caja = $('#np_tbl_caja tbody').empty();
            var cajaRows = [];
            (resp.ingresos.details || []).forEach(function (r) {
                if ((r.method || '').toLowerCase() === 'cash') {
                    cajaRows.push({date: r.date, party: r.party, description: r.description, amount: r.amount});
                }
            });
            (resp.egresos.details || []).forEach(function (r) {
                if ((r.method || '').toLowerCase() === 'cash') {
                    cajaRows.push({date: r.date, party: r.party, description: r.description, amount: -1 * Math.abs(Number(r.amount || 0))});
                }
            });
            cajaRows.sort(function(a,b){ return new Date(a.date) - new Date(b.date); });
            cajaRows.forEach(function (r) {
                $caja.append('<tr>'+
                    '<td>'+__date_format(r.date)+'</td>'+
                    '<td>'+(r.party||'')+'</td>'+
                    '<td>'+(r.description||'')+'</td>'+
                    '<td class="text-right">'+__currency_trans_from_en(r.amount, true)+'</td>'+
                '</tr>');
            });

            var $ing = $('#np_tbl_ingresos tbody').empty();
            (resp.ingresos.details || []).forEach(function (r) {
                $ing.append('<tr>'+
                    '<td>'+__date_format(r.date)+'</td>'+
                    '<td>'+(r.party||'')+'</td>'+
                    '<td>'+(r.description||'')+'</td>'+
                    '<td class="text-right">'+__currency_trans_from_en(r.amount, true)+'</td>'+
                '</tr>');
            });

            var $egr = $('#np_tbl_egresos tbody').empty();
            (resp.egresos.details || []).forEach(function (r) {
                $egr.append('<tr>'+
                    '<td>'+__date_format(r.date)+'</td>'+
                    '<td>'+(r.party||'')+'</td>'+
                    '<td>'+(r.description||'')+'</td>'+
                    '<td class="text-right">'+__currency_trans_from_en(r.amount, true)+'</td>'+
                '</tr>');
            });
        });
    }

    $('#np_filter').on('click', loadData);
    $('#np_print').on('click', function () {
        window.print();
    });
    $('#np_export').on('click', function () {
        var drp = $('#np_date_range').data('daterangepicker');
        var start = drp ? drp.startDate.format('YYYY-MM-DD') : moment().format('YYYY-MM-DD');
        var end = drp ? drp.endDate.format('YYYY-MM-DD') : start;
        var location_id = $('#np_location_id').val();
        var qs = $.param({ start_date: start, end_date: end, location_id: location_id, export: 'xlsx' });
        window.location = '/reports/non-pos-closing/data?' + qs;
    });

    // Open general box: create a general closing record for today, then set today's date and reload
    $('#np_open_general').on('click', function () {
        var today = moment();
        var location_id = $('#np_location_id').val();
        $.post('/reports/non-pos-closing/open', { location_id: location_id }, function () {
            var drp = $('#np_date_range').data('daterangepicker');
            if (drp) {
                drp.setStartDate(today);
                drp.setEndDate(today);
            }
            $('#np_date_range').val(today.format(moment_date_format) + ' ~ ' + today.format(moment_date_format));
            loadData();
        });
    });

    // Close general box: close record and trigger export
    $('#np_close_general').on('click', function () {
        var drp = $('#np_date_range').data('daterangepicker');
        var start = drp ? drp.startDate.format('YYYY-MM-DD') : moment().format('YYYY-MM-DD');
        var end = drp ? drp.endDate.format('YYYY-MM-DD') : start;
        var location_id = $('#np_location_id').val();
        $.post('/reports/non-pos-closing/close', { start_date: start, end_date: end, location_id: location_id }, function (resp) {
            if (resp && resp.export_url) {
                window.location = resp.export_url;
            }
        });
    });

    loadData();
});
</script>
@endsection


