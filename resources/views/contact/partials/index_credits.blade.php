<div class="modal fade" id="credit_index_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Abonos de Creditos</h4>
            </div>
            <div class="modal-body">
                <table class="table table-light">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Cantidad</th>
                            <th scope="col">Fecha de aprobacion</th>
                        </tr>
                    </thead>
                    <tbody id="loanListPayment">
                        <tr id="preLoanPayment" class="hide">
                            <td><label class="id_loan"></label></td>
                            <td><label class="amount_loan"></label></td>
                            <td><label class="date_loan"></label></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang( 'messages.close' )</button>
            </div>
            {!! Form::close() !!}
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->   
</div>