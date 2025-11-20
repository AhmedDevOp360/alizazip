@extends('layouts.app')
@section('title', "Cajas registradoras")

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Cajas Registradoras
        <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">Administrar sus multiples cajas</small>
    </h1>

</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'Listado de Cajas Registradoras' )])
        @slot('tool')
            <div class="box-tools">
               
                <button class="tw-dw-btn tw-bg--to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-black tw-border-none tw-rounded-full pull-right tw-mb-2 btn-modal"
                    data-href="{{action([\App\Http\Controllers\CashRegisterInformationController::class, 'create'])}}" 
                    data-container=".cash_register_information_add_modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" 
                                        viewBox="0 0 20 20" fill="none" stroke="teal" stroke-width="3" stroke-linecap="round"
                                            stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 5l0 14" />
                                            <path d="M5 12l14 0" />
                                        </svg> Crear Caja Registradora
                </button>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="cash_information_table">
                <thead>
                    <tr>
                        <th>Serial</th>
                        <th>Tipo de Caja</th>
                        <th>Código de ventas</th>
                        <th>Empresa</th>
                        <th>Sucursal</th>
                        <th>Creado</th>
                        <th>@lang( 'messages.action' )</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <div class="modal fade cash_register_information_add_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade cash_register_information_edit_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
