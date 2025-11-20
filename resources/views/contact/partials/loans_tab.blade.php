@php
    $transaction_types = [];
    if (in_array($contact->type, ['both', 'supplier'])) {
        $transaction_types['purchase'] = __('lang_v1.purchase');
        $transaction_types['purchase_return'] = __('lang_v1.purchase_return');
    }

    if (in_array($contact->type, ['both', 'customer'])) {
        $transaction_types['sell'] = __('sale.sale');
        $transaction_types['sell_return'] = __('lang_v1.sell_return');
    }

    $transaction_types['opening_balance'] = __('lang_v1.opening_balance');
@endphp
<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-1 col-sm-offset-11">
                <a href="javascript:" data-target="#credit_create_modal" data-toggle="modal"
                    class="edit_contact_button btn btn-primary" id="credit_create_btn"
                    data-url="{{ route('loans.create', $contact->id) }}"><i
                        class="glyphicon glyphicon-edit"></i>Crear</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h3>Deuda: <span class="debt"></span></h3>
            </div>
            <div class="col-md-6">
                <h3>Disponilidad: <span class="availability"></span></h3>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="container">
                    <table class="table table-light table-hover table-striped">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Cantidad</th>
                                <th scope="col">Aprobado por</th>
                                <th scope="col">Fecha de aprobacion</th>
                            </tr>
                        </thead>
                        <tbody id="loan_list">
                            <tr id="pre-loan" class="hide">
                                <td><label class="id_loan"></label></td>
                                <td><label class="amount_loan"></label></td>
                                <td><label class="users_loan"></label></td>
                                <td><label class="date_loan"></label></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="contact_ledger_div"></div>
</div>
