<div class="row mini_print">
  <div class="col-sm-12">
    <table class="table table-condensed">
      <tr>
        <th>@lang('lang_v1.payment_method')</th>
        <th>@lang('sale.sale')</th>
        <th>@lang('lang_v1.expense')</th>
      </tr>
      <tr>
        <td>
          @lang('cash_register.cash_in_hand'):
        </td>
        <td>
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->cash_in_hand }}</span>
        </td>
        <td>--</td>
      </tr>
      <tr>
        <td>
          @lang('cash_register.cash_payment'):
        </th>
        <td>
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cash }}</span>
        </td>
        <td>
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cash_expense }}</span>
        </td>
      </tr>
       <tr>
        <td>
          @lang('cash_register.checque_payment'):
        </td>
        <td>
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cheque }}</span>
        </td>
        <td>
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cheque_expense }}</span>
        </td>
      </tr>
      <tr> 
        <td>
          @lang('cash_register.card_payment'):
        </td>
        <td>
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_card }}</span>
        </td>
        <td>
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_card_expense }}</span>
        </td>
      </tr>
        <tr>
        <td>
          @lang('cash_register.bank_transfer'):
        </td>
        <td>
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_bank_transfer }}</span>
        </td>
        <td>
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_bank_transfer_expense }}</span>
        </td>
      </tr> 
      <tr>
        <td>
          @lang('lang_v1.advance_payment'):
        </td>
        <td>
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_advance }}</span>
        </td>
        <td>
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_advance_expense }}</span>
        </td>
      </tr>
      @if(array_key_exists('custom_pay_1', $payment_types))
        <tr>
          <td>
            {{$payment_types['custom_pay_1']}}:
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_1 }}</span>
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_1_expense }}</span>
          </td>
        </tr>
      @endif
      @if(array_key_exists('custom_pay_2', $payment_types))
        <tr>
          <td>
            {{$payment_types['custom_pay_2']}}:
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_2 }}</span>
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_2_expense }}</span>
          </td>
        </tr>
      @endif
      @if(array_key_exists('custom_pay_3', $payment_types))
        <tr>
          <td>
            {{$payment_types['custom_pay_3']}}:
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_3 }}</span>
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_3_expense }}</span>
          </td>
        </tr>
      @endif
      @if(array_key_exists('custom_pay_4', $payment_types))
        <tr>
          <td>
            {{$payment_types['custom_pay_4']}}:
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_4 }}</span>
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_4_expense }}</span>
          </td>
        </tr>
      @endif
      @if(array_key_exists('custom_pay_5', $payment_types))
        <tr>
          <td>
            {{$payment_types['custom_pay_5']}}:
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_5 }}</span>
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_5_expense }}</span>
          </td>
        </tr>
      @endif
      @if(array_key_exists('custom_pay_6', $payment_types))
        <tr>
          <td>
            {{$payment_types['custom_pay_6']}}:
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_6 }}</span>
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_6_expense }}</span>
          </td>
        </tr>
      @endif
      @if(array_key_exists('custom_pay_7', $payment_types))
        <tr>
          <td>
            {{$payment_types['custom_pay_7']}}:
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_7 }}</span>
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_7_expense }}</span>
          </td>
        </tr>
      @endif
    <tr>
        <td>
          @lang('cash_register.other_payments'):
        </td>
        <td>
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_other }}</span>
        </td>
        <td>
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_other_expense }}</span>
        </td>
      </tr>
    </table>
    <hr>
    <table class="table table-condensed">
      <tr>
        <td>
          a) @lang('cash_register.total_sales') - Devoluciones:
        </td>
        <td>
          <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_sale }}</span>
        </td>
      </tr>
             <tr class="">
         <th>
           b) @lang('cash_register.total_refund'):
         </th>
         <td>
           <b><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_refund + ($modalSellReturnRefundTotal ?? 0) }}</span></b>
         </td>
       </tr>
       <tr>
         <td></td>
         <td>
           <small>
           @php($cash_refund_total = $register_details->total_cash_refund + ($modalRefundsByMethod['cash'] ?? 0))
           @if($cash_refund_total != 0)
             Efectivo: <span class="display_currency" data-currency_symbol="true">{{ $cash_refund_total }}</span><br>
           @endif
           @php($cheque_refund_total = $register_details->total_cheque_refund + ($modalRefundsByMethod['cheque'] ?? 0))
           @if($cheque_refund_total != 0) 
             Mequi: <span class="display_currency" data-currency_symbol="true">{{ $cheque_refund_total }}</span><br>
           @endif
           @php($card_refund_total = $register_details->total_card_refund + ($modalRefundsByMethod['card'] ?? 0))
           @if($card_refund_total != 0) 
             Tarjetas: <span class="display_currency" data-currency_symbol="true">{{ $card_refund_total }}</span><br> 
           @endif
           @php($bt_refund_total = $register_details->total_bank_transfer_refund + ($modalRefundsByMethod['bank_transfer'] ?? 0))
           @if($bt_refund_total != 0)
             Tranferencia B: <span class="display_currency" data-currency_symbol="true">{{ $bt_refund_total }}</span><br>
           @endif
           @php($cp1_refund_total = $register_details->total_custom_pay_1_refund + ($modalRefundsByMethod['custom_pay_1'] ?? 0))
           @if(array_key_exists('custom_pay_1', $payment_types) && $cp1_refund_total != 0)
               {{$payment_types['custom_pay_1']}}: <span class="display_currency" data-currency_symbol="true">{{ $cp1_refund_total }}</span><br>
           @endif
           @php($cp2_refund_total = $register_details->total_custom_pay_2_refund + ($modalRefundsByMethod['custom_pay_2'] ?? 0))
           @if(array_key_exists('custom_pay_2', $payment_types) && $cp2_refund_total != 0)
               {{$payment_types['custom_pay_2']}}: <span class="display_currency" data-currency_symbol="true">{{ $cp2_refund_total }}</span><br>
           @endif
           @php($cp3_refund_total = $register_details->total_custom_pay_3_refund + ($modalRefundsByMethod['custom_pay_3'] ?? 0))
           @if(array_key_exists('custom_pay_3', $payment_types) && $cp3_refund_total != 0)
               {{$payment_types['custom_pay_3']}}: <span class="display_currency" data-currency_symbol="true">{{ $cp3_refund_total }}</span><br>
           @endif
           @php($cp4_refund_total = $register_details->total_custom_pay_4_refund + ($modalRefundsByMethod['custom_pay_4'] ?? 0))
           @if(array_key_exists('custom_pay_4', $payment_types) && $cp4_refund_total != 0)
               {{$payment_types['custom_pay_4']}}: <span class="display_currency" data-currency_symbol="true">{{ $cp4_refund_total }}</span><br>
           @endif
           @php($cp5_refund_total = $register_details->total_custom_pay_5_refund + ($modalRefundsByMethod['custom_pay_5'] ?? 0))
           @if(array_key_exists('custom_pay_5', $payment_types) && $cp5_refund_total != 0)
               {{$payment_types['custom_pay_5']}}: <span class="display_currency" data-currency_symbol="true">{{ $cp5_refund_total }}</span><br>
           @endif
           @php($cp6_refund_total = $register_details->total_custom_pay_6_refund + ($modalRefundsByMethod['custom_pay_6'] ?? 0))
           @if(array_key_exists('custom_pay_6', $payment_types) && $cp6_refund_total != 0)
               {{$payment_types['custom_pay_6']}}: <span class="display_currency" data-currency_symbol="true">{{ $cp6_refund_total }}</span><br>
           @endif
           @php($cp7_refund_total = $register_details->total_custom_pay_7_refund + ($modalRefundsByMethod['custom_pay_7'] ?? 0))
           @if(array_key_exists('custom_pay_7', $payment_types) && $cp7_refund_total != 0)
               {{$payment_types['custom_pay_7']}}: <span class="display_currency" data-currency_symbol="true">{{ $cp7_refund_total }}</span><br>
           @endif
           @php($other_refund_total = $register_details->total_other_refund + ($modalRefundsByMethod['other'] ?? 0))
           @if($other_refund_total != 0)
             Other: <span class="display_currency" data-currency_symbol="true">{{ $other_refund_total }}</span>
           @endif
           </small>
         </td>
       </tr>
      <tr class="">
        <th>
          c) @lang('lang_v1.total_payment'):
        </th>
        <td>
          <b><span class="display_currency" data-currency_symbol="true">{{ $register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund - ($modalCashSellReturnRefund ?? 0) }}</span></b>
        </td>
      </tr>
      <tr class="">
        <th>
          d) @lang('lang_v1.credit_sales'):
        </th>
        <td>
          <b><span class="display_currency" data-currency_symbol="true">{{ $details['transaction_details']->total_sales - $register_details->total_sale }}</span></b>
        </td>
      </tr>
      <tr class="">
        <th>
          e) @lang('cash_register.total_sales')= (a+d):
        </th>
        <td>
          <b><span class="display_currency" data-currency_symbol="true">{{ $details['transaction_details']->total_sales }}</span></b>
        </td>
      </tr>
      <tr class="">
        <th>
          f) @lang('report.total_expense'):
        </th>
        <td>
          <b><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_expense }}</span></b>
        </td>
      </tr>
      <tr class="">
        <th>
          g) @lang('cash_register.total_backend_payment') (@lang('cash_register.cash_payment')):
        </th>
        <td>
          <b><span class="display_currency" data-currency_symbol="true">{{ $backendPaymentAmount }}</span></b>
        </td>
      </tr>
    </table>
  </div>
</div>

@include('cash_register.register_product_details')
