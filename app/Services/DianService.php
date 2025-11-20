<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use App\Business;
use App\CashRegister;
use App\CashRegisterInformation;
use App\InvoiceScheme;
use App\Contact;
use App\Product;
use App\TaxRate;
use App\Transaction;
use App\TransactionPayment;
use Carbon\Carbon;
// use App\Utils\TransactionUtil;
// use App\Utils\ProductUtil;
use Illuminate\Support\Facades\Http;


class DianService
{
    /**
     * Leer un archivo JSON desde una ruta específica
     *
     * @param string $path
     * @return array|null
     */
    public static function convert_numeric($number)
    {
        return  str_replace(',', '', $number);
    }

    public static function num_uf($input_number, $currency_details = null)
    {
        $thousand_separator = '.';
        $decimal_separator = '';

        if (! empty($currency_details)) {
            $thousand_separator = $currency_details->thousand_separator;
            $decimal_separator = $currency_details->decimal_separator;
        } else {
            $thousand_separator = session()->has('currency') ? session('currency')['thousand_separator'] : '';
            $decimal_separator = session()->has('currency') ? session('currency')['decimal_separator'] : '';
        }

        $num = str_replace($thousand_separator, '', $input_number);
        $num = str_replace($decimal_separator, '.', $num);

        return (float) $num;
    }

    public static function tax($number,$percent)
    {
        $base = 1000;
        // $porcentaje = 19;
        $factor = 1 + ($percent / 100);

        $resultado = $number * $factor;

        return  $resultado - $number;
    }

    public static function round_number($valor) {
        
        return round($valor,2);
    }

    public static function separarLetrasYNumeros($input) {
        $letras = preg_replace('/[^a-zA-Z]/', '', $input);
        $numeros = preg_replace('/[^0-9]/', '', $input);
        return array($letras, $numeros);
    }

    public function toggleAllowanceCharges($enableAllowance, $discount_id, $charge_indicator, $allowance_charge_reason, $amount, $base_amount)
    {

        if ($enableAllowance) {
            // Agregar la propiedad allowance_charges si no existe
            $invoice['allowance_charges'] = [
                [
                    "discount_id" => $discount_id,
                    "charge_indicator" => $charge_indicator,
                    "allowance_charge_reason" =>$allowance_charge_reason,
                    "amount" => $amount,
                    "base_amount" => $base_amount
                ]
            ];
        }
    }

    public static function read_xml_invoice($base64_invoice)
    {
        $payableAmount = 0;
        $LineExtensionAmount = 0;

        $invoice_xml = base64_decode($base64_invoice);
        $xml = simplexml_load_string($invoice_xml);
        $xml->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xml->registerXPathNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        // Leer las líneas de factura
        $invoiceLines = $xml->xpath('//cac:InvoiceLine');
        foreach ($invoiceLines as $line) {
            $quantity = $line->xpath('cbc:InvoicedQuantity')[0];  
            $price = $line->xpath('cac:Price/cbc:PriceAmount')[0];
            $description = $line->xpath('cac:Item/cbc:Description')[0];

            // echo "Descripción: " . $description . "\n";
            // echo "Cantidad: " . $quantity . "\n";
            // echo "Precio Unitario: " . $price . "\n";
            // echo "---------------------\n";
        }

        // Leer los totales de la factura
        $totals = $xml->xpath('//cac:LegalMonetaryTotal');
        foreach ($totals as $total) {
            $payableAmount = $total->xpath('cbc:PayableAmount')[0];
            $lineExtensionAmount = $total->xpath('cbc:LineExtensionAmount')[0];
            // echo "Total a Pagar: " . $payableAmount . "\n";
        }

        return [
            'payable_amount' => (double)$payableAmount,
            'line_extension_amount' => (double)$lineExtensionAmount,
        ];
    }
    
    public static function resend_invoice($transaction_before, $business_id, $contact_id, $input,$prefix, $number, $resolution)
    {
        $customer_data = Contact::findOrFail($contact_id);
        $business_data = Business::find($business_id);

        //validamos si se va a enviar factua electronica o no
        if($transaction_before->is_valid != 1 && $transaction_before->e_invoice == 'si' && $transaction_before->status == "final" && $transaction_before->is_suspend == "0")//final
        // if($transaction_before->is_valid == 1  && $transaction_before->status == "final" && $transaction_before->is_suspend == "0")//final
        {

            
            $actual_date = Carbon::now('America/Bogota')->format('Y-m-d');
            $actual_hous = Carbon::now('America/Bogota')->format('H:m:s');


            $total_tax_products = 0;
            $total_not_tax_products = 0;
            // $total_not_tax_products = 0;
            $line_extension_amount = 0;
            $tax_inclusive_amount = 0;
            $tax_exclusive_amount = 0;
            // $taxes = [];//impuesto por linea de producto
            $payable_amount = 0;
            $payable_amount_inc_discount = 0;
            

            $invoiceLines = array();

            $tax_totals_map = [];//array para agregar y sumar los impuestos a nivel de factura

            $tax_total_invoice = [];


            if(isset($input))
            {
                foreach ($input as $product){
                    $tax_totals = [];//impuestos totales de la factura
                    // $total_product = 0;
                    // $tax_total_product = 0;

                    $product_db = Product::findOrFail($product['product_id']);

                    $unit_price = self::convert_numeric($product['unit_price']);

                    //calculo de los campos de cada linea convert_numeric($number)
                    $line_extension_amount_product = (floatval($product['quantity']) * floatval($unit_price));

                    //CONSTRUCCIÓN DEL JSON DE IMPUESTOS
                    if(isset($product['tax_id'])){
                        $tax = TaxRate::find($product['tax_id']);

                        $tax_amount_product = self::tax($line_extension_amount_product,floatval($tax['amount']));
                        // $tax_total_product = $tax_amount_product + $line_extension_amount_product;

                        $tax_totals[] = [
                            // "tax_id" =>intval($product['tax_id']),//ERROR AQUI
                            "tax_id" =>$tax->code,
                            "tax_amount" => self::round_number($tax_amount_product),
                            "taxable_amount" => self::round_number($line_extension_amount_product),//valor del producto sin impuesto
                            "percent" => intval($tax['amount'])
                        ];

                        // Sumar el impuesto al total de impuestos de la factura
                        $tax_key = intval($tax['code']) . '_' . floatval($tax['amount']);
                        if (isset($tax_totals_map[$tax_key])) {
                            // $tax_totals_map[$tax_key]['tax_id'] = intval($tax['tax_id']);
                            $tax_totals_map[$tax_key]['tax_amount'] += self::round_number($tax_amount_product);
                            $tax_totals_map[$tax_key]['taxable_amount'] += self::round_number($line_extension_amount_product);
                        } else {
                            $tax_totals_map[$tax_key] = [
                                "tax_id" => $tax->code,
                                "tax_amount" => self::round_number($tax_amount_product),
                                "taxable_amount" => self::round_number($line_extension_amount_product),
                                "percent" => floatval($tax['amount'])
                            ];
                        }


                        //sumamos el total de las bases productos con impuestos
                        $tax_exclusive_amount +=  $line_extension_amount_product;

                        //sumamos el excedente del impuesto al total de la linea
                        $line_extension_amount_product += $tax_amount_product;


                        
                    }
                    $total_line = floatval($product['quantity']) * $unit_price;
                    
                    if(isset($product['tax_id'])){
                        $invoiceLines[] = [
                            "unit_measure_id" => 70,
                            "invoiced_quantity" => floatval($product['quantity']),
                            "line_extension_amount" => self::round_number($total_line),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            // "line_extension_amount" => $this->round_number($line_extension_amount_product),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            "free_of_charge_indicator" => false,
                            
                            "description" => $product_db->name,
                            "code" => $product_db->sku,
                            "type_item_identification_id" => 4,
                            "price_amount" => self::round_number($line_extension_amount_product),//Valor de la linea de la factura
                            "base_quantity" =>floatval($product['quantity']),
                            "tax_totals" => $tax_totals

                        ];
                        unset($tax_totals);
                    }else{
                        $invoiceLines[] = [
                            "unit_measure_id" => 70,
                            "invoiced_quantity" => floatval($product['quantity']),
                            "line_extension_amount" => self::round_number($total_line),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            "free_of_charge_indicator" => false,
                            
                            "description" => $product_db->name,
                            "code" => $product_db->sku,
                            "type_item_identification_id" => 4,
                            "price_amount" => self::round_number($line_extension_amount_product),//Valor de la linea de la factura
                            "base_quantity" =>floatval($product['quantity']),

                        ];
                    }


                    $tax_inclusive_amount += $line_extension_amount_product;
                }

            }

            // Convertir el mapa de impuestos a un array de totales de impuestos
            $tax_total_invoice = array_values($tax_totals_map);

            //calcular los invoice line
            if(isset($invoiceLines)){
                foreach($invoiceLines as $invoice_product)
                {
                    if(isset($invoice_product['tax_id']))
                    {
                        $total_tax_products += floatval($invoice_product['line_extension_amount']);
                    }else{
                        $total_not_tax_products +=  floatval($invoice_product['line_extension_amount']);
                    }
                    $line_extension_amount += floatval($invoice_product['line_extension_amount']);

                    //calculñar el total de la factura
                    $payable_amount = $payable_amount + $invoice_product['price_amount'];
                }
            }


            //ENVIO DE FE zposs.co

            $date = $actual_date;
            $time = $actual_hous;
            $sendmail = true;
            
            if($customer_data->contact_id == 222222222222)
            {
                $customer = array(
                    "identification_number" => "222222222222",
                    "name" => "Consumidor Final",
                    "merchant_registration" => "0000000-00",
                );
            }else{
                $contact_type = 0;
                $name = '';
                if($customer_data->contact_type == 'individual')
                {
                    $contact_type = 2;
                    $name = $customer_data->name;
                }else{
                    $contact_type = 1;
                    $name = $customer_data->supplier_business_name;
                }
                $customer = array(
                    "identification_number" => $customer_data->contact_id,
                    "dv" => $customer_data->dv,
                    "name" => $name,
                    "type_organization_id" => $contact_type,
                    "phone" => $customer_data->mobile,
                    "merchant_registration" => ($customer_data->merchant_registration)?$customer_data->merchant_registration : "0000000",
                    "type_document_identification_id" => ($customer_data->type_document_identification_id)?$customer_data->type_document_identification_id : "",
                    "type_regime_id" => ($customer_data->type_regime_id)?$customer_data->type_regime_id : "",
                    "municipality_id" => ($customer_data->municipality_id)?$customer_data->municipality_id : "",
                    "email" => $customer_data->email,
                    "address" => ($customer_data->address_line_1)? $customer_data->address_line_1: 'no'
                );
                $sendmail = true;
            }
            // dd($transaction_before);
            $transaction_payment = TransactionPayment::where('business_id', $business_id)->where('transaction_id',$transaction_before->id)->first();
            // dd($transaction_payment);
            //leer los metodos y formas de pago
            $payment_form = 0;
            $duration_measure = 0;
            $payment_method = 0;

            if(!empty($transaction_payment->method))
            {
                if($transaction_payment->method == 'cash')
                {
                    $payment_method = 10;
                }else if($transaction_payment->method == 'cheque')
                {
                    $payment_method = 20;
                }else if($transaction_payment->method == 'custom_pay_1' || $transaction_payment->method == 'custom_pay_2' || $transaction_payment->method == 'custom_pay_3'  || $transaction_payment->method == 'custom_pay_4')
                {
                    $payment_method = 1;
                }else if($transaction_payment->method == 'bank_transfer')
                {
                    $payment_method = 47;
                }else if($transaction_payment->method == 'other')
                {
                    $payment_method = 1;
                
                }else{
                    $payment_method = $transaction_payment->method;
                }
            }else{
                if($transaction_before->payment_method == 'cash')
                {
                    $payment_method = 10;
                }else if($transaction_before->payment_method == 'cheque')
                {
                    $payment_method = 20;
                }else if($transaction_before->payment_method == 'custom_pay_1' || $transaction_before->payment_method == 'custom_pay_2' || $transaction_before->payment_method == 'custom_pay_3'  || $transaction_before->payment_method == 'custom_pay_4')
                {
                    $payment_method = 1;
                }else if($transaction_before->payment_method == 'bank_transfer')
                {
                    $payment_method = 47;
                }else if($transaction_before->payment_method == 'other')
                {
                    $payment_method = 1;
                
                }else{
                    $payment_method = $transaction_before->payment_method;
                }
                
            }
            if($transaction_before->payment_status == 'due' || $transaction_before->payment_status == 'partial')
            {
                if($transaction_before->pay_term_number == '' || $transaction_before->pay_term_number == 0)
                {
                    $duration_measure = 30;
                }else{
                    $duration_measure = $transaction_before->pay_term_number;
                }

                $payment_form = 2;//credito
                
                
            }elseif($transaction_before->payment_status == 'paid'){
                $payment_form = 1;//debido
            }
            //si is_credit_sale	= 1 es venta a credito
            $paymentForm = array(
                "duration_measure" => $duration_measure,//dias de plazo para pago
                "payment_form_id" => $payment_form,//1 contado 2 credito
                "payment_method_id" => $payment_method,
                "payment_due_date" => $actual_date
            );

            $discount = 0;
            
            if($transaction_before->discount_amount > 0)
            {
                if($transaction_before->discount_type == "percentage")
                {
                    $discount = ($transaction_before->discount_amount / 100) * $payable_amount;
                    $payable_amount_inc_discount = $tax_inclusive_amount - $discount;

                }else{

                    $discount = $transaction_before->discount_amount;
                    $payable_amount_inc_discount = $tax_inclusive_amount - $discount;
                }
                // dd()
            }else{
                $payable_amount_inc_discount = $tax_inclusive_amount;
            }

            $legalMonetaryTotals = array(
                "line_extension_amount" => self::round_number($line_extension_amount),
                "tax_exclusive_amount" => self::round_number($tax_exclusive_amount),//
                "tax_inclusive_amount" => self::round_number($tax_inclusive_amount),
                "charge_total_amount" => "0.00",
                // "payable_amount" => $this->round_number($final_total),
                "allowance_total_amount" => self::round_number($discount),
                "payable_amount" => self::round_number($payable_amount_inc_discount),
            );

            // Construcción del JSON dinámicamente
            $data = array(
                "number" => $number,
                "prefix" => $prefix,
                "type_document_id" => 1,
                "date" => $date,
                "time" => $time,
                "notes" => $transaction_before->additional_notes,
                "foot_note" => $transaction_before->staff_note,
                "sendmail" => $sendmail,
                "resolution_number" => $resolution,
                "customer" => $customer,
                "payment_form" => $paymentForm,
                // "previous_balance" => $previousBalance,
                "legal_monetary_totals" => $legalMonetaryTotals,
                // "allowance_charges" => $allowanceCharges,
            );

            if (!empty($invoiceLines)) {
                $data["invoice_lines"] = $invoiceLines;
            }

            if (!empty($tax_total_invoice)) {
                $data["tax_totals"] = $tax_total_invoice;
            }

            if ($transaction_before->discount_amount > 0) {
                $data["allowance_charges"] = [[
                    "discount_id" => 1,
                    "charge_indicator" => false,
                    "allowance_charge_reason" =>"DESCUENTO GENERAL",
                    "amount" => $discount,
                    "base_amount" => self::round_number($line_extension_amount)
                ]];
            }

            // return $data;

            $jsonData = json_encode($data);
            // return $jsonData;
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => env('APP_API_FE').'/api/ubl2.1/invoice',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer '.$business_data->dian_token
            ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            $resp_curl_code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

            curl_close($curl);



            switch ($resp_curl_code) {
                case 200:
                    $respuesta = json_decode($response);
                    if($respuesta->success){

                        if(isset($respuesta->ResponseDian))
                        {
                            $response_dian =  $respuesta->ResponseDian;
                            $IsValid = $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->IsValid;
                            $StatusCode = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode : '';
                            $ErrorRules = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage->string : '';
                            $cufe = $respuesta->cufe;
                            $QRStr = $respuesta->QRStr;
                            // $ErrorRules = '';
                            $message = '';
                            $code = '';
        
                            if($IsValid == "true" && $StatusCode == 00)
                            {
                                //guardamos el cufe de la factura y cambiamos estado de la facturA en el sistema
                                $transaction = Transaction::find($transaction_before->id);
                                $transaction->cufe = $cufe;
                                // $transaction->e_invoice = 'si';
                                $transaction->is_valid = true;
                                $transaction->qrstr = $QRStr;
                                $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
                                $transaction->status_code = $StatusCode;
                                $transaction->save();
        
                                $message = 'Factura aceptada por la DIAN';
                                $code = 1;
                            }else if($IsValid == "false"){
                                
                                $StatusCode = $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode;
                                $message = $ErrorRules;
                                $code = 0;
                                $transaction = Transaction::find($transaction_before->id);
                                if($StatusCode == 99)//factura procesada anteriormente
                                {
                                    $res_invoice = self::read_xml_invoice($respuesta->invoicexml);
                                    $res_invoice['payable_amount'];
                                    if(self::round_number($res_invoice['payable_amount']) == self::round_number($payable_amount_inc_discount))
                                    {//se verifica que los valores de la factura sean los mismos, el de la dian con la del sistema
                                        // dd($respuesta->ResponseDian);
                                        $transaction->cufe = $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->XmlDocumentKey;
                                        $transaction->is_valid = true;
                                        $transaction->qrstr = $QRStr;
                                        $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
                                        $transaction->status_code = $StatusCode;
                                    }
                                    
                                }else{
                                    $transaction->is_valid = false;
                                    $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
                                    $transaction->status_code = $StatusCode;
                                }
                                $transaction->save();
                            }
        
                            $output = [
                                'success' =>$code, 
                                'msg' => $message, 
                                'input_curl'=> $data, 
                                'input_factura'=> $input, 
                                'response' => ($respuesta) ? $respuesta : '',  
                                'cufe' => ($cufe) ? $cufe : '',
                                'IsValid' => ($IsValid) ? $IsValid : '',
                                'QRStr' => ($QRStr) ? $QRStr : '',
                                'ErrorMessage' => $ErrorRules
                            ];
                            return $output;
                        }else{
        
                            //respuesta almacenada
                            $transaction = Transaction::find($transaction_before->id);
                            $transaction->is_valid = false;
                            $transaction->save();
                            //fin de respuesta
                            $output = [
                                'success' => 0, 
                                // 'msg' => $respuesta->error[0], 
                                'msg' => $respuesta, 
                                'input_curl'=> $data, 
                            ];
                            return $output;
                        }
                    }
                    break;
                case 422:
                    $respuesta = json_decode($response);
                    $error_msg = '';
                    foreach ($respuesta->errors as $campo => $mensajes) {
                        $error_msg = $mensajes[0] . "\n";
                    }

                    $message = $respuesta->message.' - '.$error_msg;
                    $errors = $respuesta->errors;
                    //respuesta almacenada
                    $transaction = Transaction::find($transaction_before->id);
                    $transaction->is_valid = false;
                    $transaction->save();
                    //fin de respuesta
                    $output = [
                        'success' =>0, 
                        'msg' => $message, 
                        'input_curl'=> $data, 
                        'input_factura'=> $input, 
                        'response' => ($respuesta) ? $respuesta : '',  
                        'cufe' => '',
                        'IsValid' =>  false,
                        'QRStr' =>  '',
                        'ErrorMessage' => $errors
                    ];
                    return $output;
                    break;
                case 404:
                    $respuesta = json_decode($response);
                    $message = $respuesta->message;
                    //respuesta almacenada
                    $transaction = Transaction::find($transaction_before->id);
                    $transaction->is_valid = false;
                    $transaction->save();
                    //fin de respuesta
                    $output = [
                        'success' =>0, 
                        'msg' => "error 404 Url incorrecta", 
                        'input_curl'=> $data, 
                        'input_factura'=> $input, 
                        'response' => ($respuesta) ? $respuesta : '',  
                        'cufe' => '',
                        'IsValid' =>  false,
                        'QRStr' =>  '',
                        'ErrorMessage' => 'La url a la que intenta hacer la petición no existe'
                    ];
                    return $output;
                    break;
                default:
                    $respuesta = json_decode($response);
                    $message = $respuesta->message;
                    //respuesta almacenada
                    $transaction = Transaction::find($transaction_before->id);
                    $transaction->is_valid = false;
                    $transaction->save();
                    //fin de respuesta
                    $output = [
                        'success' =>0, 
                        'msg' => "error 404 Url incorrecta", 
                        'input_curl'=> $data, 
                        'input_factura'=> $input, 
                        'response' => ($respuesta) ? $respuesta : '',  
                        'cufe' => '',
                        'IsValid' =>  false,
                        'QRStr' =>  '',
                        'ErrorMessage' => $message
                    ];
                    return $output;
            }

            
        }else{
            $output = [
                'success' => 1, 
                'msg' => 'No es una factura electrónica o ya fue enviada anteriormente', 
            ];
            return $output;
        }
    }
    
    public static function send_invoice($invoice_scheme_id, $business_id, $contact_id, $input, $transaction)
    {
        $i_echeme = '';
        if(!empty($invoice_scheme_id))
        {
            //consultar datos de la empresa 
            $business_data = Business::find($business_id);

            //consultar el tipo de factura
            $invoice_scheme = InvoiceScheme::findOrFail($invoice_scheme_id);
            $i_echeme = $invoice_scheme->is_fe;
            //consultar los datos del cliente
            $customer_data = Contact::findOrFail($contact_id);
        }else{
            $invoice_scheme = InvoiceScheme::where('business_id',$business_id)->where('is_default',1)->get();
            $i_echeme == $invoice_scheme->is_fe;
        }
        

        //validamos si se va a enviar factua electronica o no
        if($i_echeme == 'si' && $input['status'] == "final" && $input['is_suspend'] == "0")//final
        {

            
            $actual_date = Carbon::now('America/Bogota')->format('Y-m-d');
            $actual_hous = Carbon::now('America/Bogota')->format('H:m:s');

            $invoice_number = intval($invoice_scheme->start_number) + intval($invoice_scheme->invoice_count) -1;

            $total_tax_products = 0;
            $total_not_tax_products = 0;
            // $total_not_tax_products = 0;
            $line_extension_amount = 0;
            $tax_inclusive_amount = 0;
            $tax_exclusive_amount = 0;
            $payable_amount = 0;
            // $taxes = [];//impuesto por linea de producto
            $payable_amount = 0;
            

            $invoiceLines = array();

            $tax_totals_map = [];//array para agregar y sumar los impuestos a nivel de factura

            $tax_total_invoice = [];

            
            // $final_total = $this->convert_numeric($input['final_total']);



            if(isset($input['products']))
            {
                foreach ($input['products'] as $product){
                    $tax_totals = [];//impuestos totales de la factura
                    // $total_product = 0;
                    // $tax_total_product = 0;

                    $product_db = Product::findOrFail($product['product_id']);

                    $unit_price = self::convert_numeric($product['unit_price']);

                    //calculo de los campos de cada linea convert_numeric($number)
                    $line_extension_amount_product = (floatval($product['quantity']) * floatval($unit_price));

                    //CONSTRUCCIÓN DEL JSON DE IMPUESTOS
                    if(isset($product['tax_id'])){
                        $tax = TaxRate::find($product['tax_id']);

                        $tax_amount_product = self::tax($line_extension_amount_product,floatval($tax['amount']));
                        // $tax_total_product = $tax_amount_product + $line_extension_amount_product;

                        $tax_totals[] = [
                            // "tax_id" =>intval($product['tax_id']),//ERROR AQUI
                            "tax_id" =>$tax->code,
                            "tax_amount" => self::round_number($tax_amount_product),
                            "taxable_amount" => self::round_number($line_extension_amount_product),//valor del producto sin impuesto
                            "percent" => intval($tax['amount'])
                        ];

                        // Sumar el impuesto al total de impuestos de la factura
                        $tax_key = intval($tax['code']) . '_' . floatval($tax['amount']);
                        if (isset($tax_totals_map[$tax_key])) {
                            // $tax_totals_map[$tax_key]['tax_id'] = intval($tax['tax_id']);
                            $tax_totals_map[$tax_key]['tax_amount'] += self::round_number($tax_amount_product);
                            $tax_totals_map[$tax_key]['taxable_amount'] += self::round_number($line_extension_amount_product);
                        } else {
                            $tax_totals_map[$tax_key] = [
                                "tax_id" => $tax->code,
                                "tax_amount" => self::round_number($tax_amount_product),
                                "taxable_amount" => self::round_number($line_extension_amount_product),
                                "percent" => floatval($tax['amount'])
                            ];
                        }


                        //sumamos el total de las bases productos con impuestos
                        $tax_exclusive_amount +=  $line_extension_amount_product;

                        //sumamos el excedente del impuesto al total de la linea
                        $line_extension_amount_product += $tax_amount_product;


                        
                    }
                    $total_line = floatval($product['quantity']) * $unit_price;
                    
                    if(isset($product['tax_id'])){
                        $invoiceLines[] = [
                            "unit_measure_id" => 70,
                            "invoiced_quantity" => floatval($product['quantity']),
                            "line_extension_amount" => self::round_number($total_line),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            // "line_extension_amount" => $this->round_number($line_extension_amount_product),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            "free_of_charge_indicator" => false,
                            
                            "description" => $product_db->name,
                            "code" => $product_db->sku,
                            "type_item_identification_id" => 4,
                            "price_amount" => self::round_number($line_extension_amount_product),//Valor de la linea de la factura
                            "base_quantity" =>floatval($product['quantity']),
                            "tax_totals" => $tax_totals

                        ];
                        unset($tax_totals);
                    }else{
                        $invoiceLines[] = [
                            "unit_measure_id" => 70,
                            "invoiced_quantity" => floatval($product['quantity']),
                            "line_extension_amount" => self::round_number($total_line),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            "free_of_charge_indicator" => false,
                            
                            "description" => $product_db->name,
                            "code" => $product_db->sku,
                            "type_item_identification_id" => 4,
                            "price_amount" => self::round_number($line_extension_amount_product),//Valor de la linea de la factura
                            "base_quantity" =>floatval($product['quantity']),

                        ];
                    }


                    $tax_inclusive_amount += $line_extension_amount_product;
                }

            }

            // Convertir el mapa de impuestos a un array de totales de impuestos
            $tax_total_invoice = array_values($tax_totals_map);

            //calcular los invoice line
            if(isset($invoiceLines)){
                foreach($invoiceLines as $invoice_product)
                {
                    if(isset($invoice_product['tax_id']))
                    {
                        $total_tax_products += floatval($invoice_product['line_extension_amount']);
                    }else{
                        $total_not_tax_products +=  floatval($invoice_product['line_extension_amount']);
                    }
                    $line_extension_amount += floatval($invoice_product['line_extension_amount']);

                    //calculñar el total de la factura
                    $payable_amount = $payable_amount + $invoice_product['price_amount'];
                }
            }


            //ENVIO DE FE zposs.co
        

            // Parámetros dinámicos
            $invoiceNumber = $invoice_number;
            // $invoiceNumber = 3;
            $prefix = $invoice_scheme->prefix;
            $typeDocumentId = 1;
            $date = $actual_date;
            $time = $actual_hous;
            $sendmail = false;
            $resolutionNumber = $invoice_scheme->resolution;
            if($customer_data->contact_id == 222222222222)
            {
                $customer = array(
                    "identification_number" => "222222222222",
                    "name" => "Consumidor Final",
                    "merchant_registration" => "0000000-00",
                );
            }else{
                $contact_type = 0;
                $name = '';
                if($customer_data->contact_type == 'individual')
                {
                    $contact_type = 2;
                    $name = $customer_data->name;
                }else{
                    $contact_type = 1;
                    $name = $customer_data->supplier_business_name;
                }
                $customer = array(
                    "identification_number" => $customer_data->contact_id,
                    "dv" => $customer_data->dv,
                    "name" => $name,
                    "type_organization_id" => $contact_type,
                    "phone" => $customer_data->mobile,
                    "merchant_registration" => ($customer_data->merchant_registration)?$customer_data->merchant_registration : "0000000-00",
                    "type_document_identification_id" => ($customer_data->type_document_identification_id)?$customer_data->type_document_identification_id : "",
                    "type_regime_id" => ($customer_data->type_regime_id)?$customer_data->type_regime_id : "",
                    "type_liability_id" => ($customer_data->liability_id)?$customer_data->liability_id : "",
                    "municipality_id" => ($customer_data->municipality_id)?$customer_data->municipality_id : "",
                    "email" => $customer_data->email,
                    "address" => ($customer_data->address_line_1)? $customer_data->address_line_1: 'no'
                );
                $sendmail = true;
            }
            //leer los metodos y formas de pago
            $payment_form = 0;
            $duration_measure = 0;
            $payment_method = 0;

            if($input['payment'][0]['method'] == 'cash')
            {
                $payment_method = 10;
            }else if($input['payment'][0]['method'] == 'cheque')
            {
                $payment_method = 20;
            }else if($input['payment'][0]['method'] == 'custom_pay_1' || $input['payment'][0]['method'] == 'custom_pay_2' || $input['payment'][0]['method'] == 'custom_pay_3'  || $input['payment'][0]['method'] == 'custom_pay_4')
            {
                $payment_method = 1;
            }else if($input['payment'][0]['method'] == 'bank_transfer')
            {
                $payment_method = 47;
            }else if($input['payment'][0]['method'] == 'other')
            {
                $payment_method = 1;
            
            }else{
                $payment_method = $input['payment'][0]['method'];
            }

            if((isset($input['is_credit_sale']) && $input['is_credit_sale'] == '1') || $input['payment'][0]['amount'] == '0')
            {
                if($input['pay_term_number'] == '' || $input['pay_term_number'] == 0)
                {
                    $duration_measure = 30;
                }else{
                    $duration_measure = $input['pay_term_number'];
                }

                $payment_form = 2;//credito
                
            }elseif(!isset($input['is_credit_sale']) || $input['is_credit_sale'] == '0'){
                $payment_form = 1;//debito
            }
            //si is_credit_sale	= 1 es venta a credito
            $paymentForm = array(
                "duration_measure" => $duration_measure,//dias de plazo para pago
                "payment_form_id" => $payment_form,//1 contado 2 credito
                "payment_method_id" => $payment_method,
                "payment_due_date" => $actual_date
            );

            $discount = 0;
            
            if(self::num_uf($input['discount_amount']) > 0)
            {
                if($input['discount_type'] == "percentage")
                {
                    $discount = (self::num_uf($input['discount_amount']) / 100) * $line_extension_amount;
                    $payable_amount = $tax_inclusive_amount - $discount;

                }else{

                    $discount = self::num_uf($input['discount_amount']);
                    $payable_amount = $tax_inclusive_amount - $discount;
                }
                // dd()
            }

            $legalMonetaryTotals = array(
                "line_extension_amount" => self::round_number($line_extension_amount),
                "tax_exclusive_amount" => self::round_number($tax_exclusive_amount),//
                "tax_inclusive_amount" => self::round_number($tax_inclusive_amount),
                "charge_total_amount" => "0.00",
                // "payable_amount" => $this->round_number($final_total),
                "allowance_total_amount" => self::round_number($discount),
                "payable_amount" => self::round_number($payable_amount),
            );


            // Construcción del JSON dinámicamente
            $data = array(
                "number" => $invoiceNumber,
                // "number" => 990000547,
                "prefix" => $prefix,
                "notes" => (isset($input['sale_note'])) ? $input['sale_note'] : '',
                "foot_note" => (isset($input['staff_note'])) ? $input['staff_note'] : '',
                "type_document_id" => $typeDocumentId,
                "date" => $date,
                "time" => $time,
                "sendmail" => $sendmail,
                "resolution_number" => $resolutionNumber,
                "customer" => $customer,
                "payment_form" => $paymentForm,
                // "previous_balance" => $previousBalance,
                "legal_monetary_totals" => $legalMonetaryTotals,
                // "allowance_charges" => $allowanceCharges,
            );

            if (!empty($invoiceLines)) {
                $data["invoice_lines"] = $invoiceLines;
            }

            if (!empty($tax_total_invoice)) {
                $data["tax_totals"] = $tax_total_invoice;
            }
            
            if (self::num_uf($input['discount_amount']) > 0) {
                $data["allowance_charges"] = [[
                    "discount_id" => 1,
                    "charge_indicator" => false,
                    "allowance_charge_reason" =>"DESCUENTO GENERAL",
                    "amount" => self::num_uf($discount),
                    "base_amount" => self::round_number($line_extension_amount)
                ]];
            }

            // dd($data);

            $jsonData = json_encode($data);
            // return $jsonData;
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => env('APP_API_FE').'/api/ubl2.1/invoice',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer '.$business_data->dian_token
            ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            $resp_curl_code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);


            // dd($resp_curl_code);
            //200 cuando la respuesta es ok
            //422 cuando retorna error de validación por falta de un campo en el json
            curl_close($curl);


            switch ($resp_curl_code) {
                case 200:
                    $respuesta = json_decode($response);
                    // dd($respuesta);
                    if($respuesta->success){

                        if(isset($respuesta->ResponseDian))
                        {
                            $message = '';
                            $code = '';
                            $response_dian =  $respuesta->ResponseDian;
                            $StatusDescription = $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusDescription;
                            $IsValid = $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->IsValid;
                            $StatusCode = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode : '';
                            if($StatusCode == "500")
                            {
                                $message = $StatusDescription;
                                $transaction = Transaction::find($transaction->id);
                                    // $transaction->cufe = $cufe;
                                    // $transaction->e_invoice = 'si';
                                    $transaction->is_valid = false;
                                    // $transaction->qrstr = $QRStr;
                                    $transaction->rules = $StatusDescription;
                                    $transaction->status_code = $StatusCode;
                                    $transaction->save();
            
                                    // $message = 'Factura aceptada por la DIAN';
                                    $code = 0;
                            }else{
                             
                                
                                
                                $ErrorRules = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage->string : '';
                                $cufe = $respuesta->cufe;
                                $QRStr = $respuesta->QRStr;
                                // $ErrorRules = '';
                                
            
                                if($IsValid == "true" && $StatusCode == 00)
                                {
                                    //guardamos el cufe de la factura y cambiamos estado de la facturA en el sistema
                                    $transaction = Transaction::find($transaction->id);
                                    $transaction->cufe = $cufe;
                                    $transaction->e_invoice = 'si';
                                    $transaction->is_valid = true;
                                    $transaction->qrstr = $QRStr;
                                    $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
                                    $transaction->status_code = $StatusCode;
                                    $transaction->save();
            
                                    $message = 'Factura aceptada por la DIAN';
                                    $code = 1;
                                }else if($IsValid == "false"){
                                    
                                    $StatusCode = $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode;
                                    $message = $ErrorRules;
                                    $code = 0;
                                    $transaction = Transaction::find($transaction->id);
                                    if($StatusCode == 99)
                                    {
                                        $transaction->cufe = $cufe;
                                        $transaction->is_valid = false;
                                        $transaction->qrstr = $QRStr;
                                        $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
                                        $transaction->status_code = $StatusCode;
                                    }else{
                                        $transaction->is_valid = false;
                                        $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
                                        $transaction->status_code = $StatusCode;
                                    }
                                    $transaction->save();
                                }
                            }
                                $output = [
                                    'success' =>$code, 
                                    'msg' => $message, 
                                    'input_curl'=> $data, 
                                    'input_factura'=> $input, 
                                    'response' => ($respuesta) ? $respuesta : '',  
                                    'cufe' => (isset($cufe)) ? $cufe : '',
                                    'IsValid' => ($IsValid) ? $IsValid : '',
                                    'QRStr' => (isset($QRStr)) ? $QRStr : '',
                                    'ErrorMessage' =>  (isset($ErrorRules)) ? $ErrorRules : ''
                                ];
                                return $output;
                            
                        }else{
        
                            //respuesta almacenada
                            $transaction = Transaction::find($transaction->id);
                            $transaction->is_valid = false;
                            $transaction->save();
                            //fin de respuesta
                            $output = [
                                'success' => 0, 
                                // 'msg' => $respuesta->error[0], 
                                'msg' => $respuesta, 
                                'input_curl'=> $data, 
                            ];
                            return $output;
                        }
                    }
                    break;
                case 422:
                    $respuesta = json_decode($response);
                    $message = (is_object($respuesta) && isset($respuesta->message)) ? $respuesta->message : 'Error 422 from DIAN service';
                    $errors = (is_object($respuesta) && isset($respuesta->errors)) ? $respuesta->errors : $respuesta;
                    //respuesta almacenada
                    $transaction = Transaction::find($transaction->id);
                    $transaction->is_valid = false;
                    $transaction->save();
                    //fin de respuesta
                    $output = [
                        'success' =>0, 
                        'msg' => $message, 
                        'input_curl'=> $data, 
                        'input_factura'=> $input, 
                        'response' => ($respuesta) ? $respuesta : '',  
                        'cufe' => '',
                        'IsValid' =>  false,
                        'QRStr' =>  '',
                        'ErrorMessage' => $errors
                    ];
                    return $output;
                    break;
                case 404:
                    $respuesta = json_decode($response);
                    $message = (is_object($respuesta) && isset($respuesta->message)) ? $respuesta->message : 'error 404 Url incorrecta';
                    //respuesta almacenada
                    $transaction = Transaction::find($transaction->id);
                    $transaction->is_valid = false;
                    $transaction->save();
                    //fin de respuesta
                    $output = [
                        'success' =>0, 
                        'msg' => "error 404 Url incorrecta", 
                        'input_curl'=> $data, 
                        'input_factura'=> $input, 
                        'response' => ($respuesta) ? $respuesta : '',  
                        'cufe' => '',
                        'IsValid' =>  false,
                        'QRStr' =>  '',
                        'ErrorMessage' => 'La url a la que intenta hacer la petición no existe'
                    ];
                    return $output;
                    break;
                default:
                    $respuesta = json_decode($response);
                    $message = (is_object($respuesta) && isset($respuesta->message)) ? $respuesta->message : 'Unknown error from DIAN service';
                    //respuesta almacenada
                    $transaction = Transaction::find($transaction->id);
                    $transaction->is_valid = false;
                    $transaction->save();
                    //fin de respuesta
                    $output = [
                        'success' =>0, 
                        'msg' => $message, 
                        'input_curl'=> $data, 
                        'input_factura'=> $input, 
                        'response' => ($respuesta) ? $respuesta : '',  
                        'cufe' => '',
                        'IsValid' =>  false,
                        'QRStr' =>  '',
                        'ErrorMessage' => $message
                    ];
                    return $output;
            }

        }else{


            $output = [
                'success' => 1, 
                'msg' => 'No es una factura electrónica', 
                // 'receipt' => $receipt
            ];
            return $output;
        }

    }

    public static function send_eqpos($invoice_scheme_id, $business_id, $contact_id, $input, $transaction)
    {
        $i_echeme = '';
        if(!empty($invoice_scheme_id))
        {
            $business_data = Business::find($business_id);

            $invoice_scheme = InvoiceScheme::findOrFail($invoice_scheme_id);
            $i_echeme = $invoice_scheme->is_fe;
            //consultar los datos del cliente
            $customer_data = Contact::findOrFail($contact_id);
        }else{
            $invoice_scheme = InvoiceScheme::where('business_id',$business_id)->where('is_default',1)->get();
            $i_echeme == $invoice_scheme->is_fe;
        }

        if($i_echeme == 'si' && $input['status'] == "final" && $input['is_suspend'] == "0")//final
        {
            $actual_date = Carbon::now('America/Bogota')->format('Y-m-d');
            $actual_hous = Carbon::now('America/Bogota')->format('H:m:s');

            $invoice_number = intval($invoice_scheme->start_number) + intval($invoice_scheme->invoice_count) -1;

            $total_tax_products = 0;
            $total_not_tax_products = 0;
            // $total_not_tax_products = 0;
            $line_extension_amount = 0;
            $tax_inclusive_amount = 0;
            $tax_exclusive_amount = 0;
            $payable_amount = 0;
            // $taxes = [];//impuesto por linea de producto
            $payable_amount = 0;
            

            $invoiceLines = array();

            $tax_totals_map = [];//array para agregar y sumar los impuestos a nivel de factura

            $tax_total_invoice = [];

            if(isset($input['products']))
            {
                foreach ($input['products'] as $product){
                    $tax_totals = [];//impuestos totales de la factura

                    $product_db = Product::findOrFail($product['product_id']);

                    $unit_price = self::convert_numeric($product['unit_price']);

                    //calculo de los campos de cada linea convert_numeric($number)
                    $line_extension_amount_product = (floatval($product['quantity']) * floatval($unit_price));

                    //CONSTRUCCIÓN DEL JSON DE IMPUESTOS
                    if(isset($product['tax_id'])){
                        $tax = TaxRate::find($product['tax_id']);

                        $tax_amount_product = self::tax($line_extension_amount_product,floatval($tax['amount']));
                        // $tax_total_product = $tax_amount_product + $line_extension_amount_product;

                        $tax_totals[] = [
                            // "tax_id" =>intval($product['tax_id']),//ERROR AQUI
                            "tax_id" =>$tax->code,
                            "tax_amount" => self::round_number($tax_amount_product),
                            "taxable_amount" => self::round_number($line_extension_amount_product),//valor del producto sin impuesto
                            "percent" => intval($tax['amount'])
                        ];

                        // Sumar el impuesto al total de impuestos de la factura
                        $tax_key = intval($tax['code']) . '_' . floatval($tax['amount']);
                        if (isset($tax_totals_map[$tax_key])) {
                            // $tax_totals_map[$tax_key]['tax_id'] = intval($tax['tax_id']);
                            $tax_totals_map[$tax_key]['tax_amount'] += self::round_number($tax_amount_product);
                            $tax_totals_map[$tax_key]['taxable_amount'] += self::round_number($line_extension_amount_product);
                        } else {
                            $tax_totals_map[$tax_key] = [
                                "tax_id" => $tax->code,
                                "tax_amount" => self::round_number($tax_amount_product),
                                "taxable_amount" => self::round_number($line_extension_amount_product),
                                "percent" => floatval($tax['amount'])
                            ];
                        }


                        //sumamos el total de las bases productos con impuestos
                        $tax_exclusive_amount +=  $line_extension_amount_product;

                        //sumamos el excedente del impuesto al total de la linea
                        $line_extension_amount_product += $tax_amount_product;
 
                    }
                    $total_line = floatval($product['quantity']) * $unit_price;
                    
                    if(isset($product['tax_id'])){
                        $invoiceLines[] = [
                            "unit_measure_id" => 70,
                            "invoiced_quantity" => floatval($product['quantity']),
                            "line_extension_amount" => self::round_number($total_line),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            // "line_extension_amount" => $this->round_number($line_extension_amount_product),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            "free_of_charge_indicator" => false,
                            
                            "description" => $product_db->name,
                            "code" => $product_db->sku,
                            "type_item_identification_id" => 4,
                            "price_amount" => self::round_number($line_extension_amount_product),//Valor de la linea de la factura
                            "base_quantity" =>floatval($product['quantity']),
                            "tax_totals" => $tax_totals

                        ];
                        unset($tax_totals);
                    }else{
                        $invoiceLines[] = [
                            "unit_measure_id" => 70,
                            "invoiced_quantity" => floatval($product['quantity']),
                            "line_extension_amount" => self::round_number($total_line),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            "free_of_charge_indicator" => false,
                            
                            "description" => $product_db->name,
                            "code" => $product_db->sku,
                            "type_item_identification_id" => 4,
                            "price_amount" => self::round_number($line_extension_amount_product),//Valor de la linea de la factura
                            "base_quantity" =>floatval($product['quantity']),

                        ];
                    }

                    $tax_inclusive_amount += $line_extension_amount_product;
                }

            }

            // Convertir el mapa de impuestos a un array de totales de impuestos
            $tax_total_invoice = array_values($tax_totals_map);

            //calcular los invoice line
            if(isset($invoiceLines)){
                foreach($invoiceLines as $invoice_product)
                {
                    if(isset($invoice_product['tax_id']))
                    {
                        $total_tax_products += floatval($invoice_product['line_extension_amount']);
                    }else{
                        $total_not_tax_products +=  floatval($invoice_product['line_extension_amount']);
                    }
                    $line_extension_amount += floatval($invoice_product['line_extension_amount']);

                    //calculñar el total de la factura
                    $payable_amount = $payable_amount + $invoice_product['price_amount'];
                }
            }

            // Parámetros dinámicos
            $invoiceNumber = $invoice_number;
            // $invoiceNumber = 3;
            $prefix = $invoice_scheme->prefix;
            $typeDocumentId = 15;
            $date = $actual_date;
            $time = $actual_hous;
            $sendmail = false;
            $resolutionNumber = $invoice_scheme->resolution;
            $name = '';
            if($customer_data->contact_id == 222222222222)
            {
                $customer = array(
                    "identification_number" => "222222222222",
                    "name" => "Consumidor Final",
                    "merchant_registration" => "0000000-00",
                );
            }else{
                $contact_type = 0;
                
                if($customer_data->contact_type == 'individual')
                {
                    $contact_type = 2;
                    $name = $customer_data->name;
                }else{
                    $contact_type = 1;
                    $name = $customer_data->supplier_business_name;
                }
                $customer = array(
                    "identification_number" => $customer_data->contact_id,
                    "dv" => $customer_data->dv,
                    "name" => $name,
                    "type_organization_id" => $contact_type,
                    "phone" => $customer_data->mobile,
                    "merchant_registration" => ($customer_data->merchant_registration)?$customer_data->merchant_registration : "0000000-00",
                    "type_document_identification_id" => ($customer_data->type_document_identification_id)?$customer_data->type_document_identification_id : "",
                    "type_regime_id" => ($customer_data->type_regime_id)?$customer_data->type_regime_id : "",
                    "type_liability_id" => ($customer_data->liability_id)?$customer_data->liability_id : "",
                    "municipality_id" => ($customer_data->municipality_id)?$customer_data->municipality_id : "",
                    "email" => $customer_data->email,
                    "address" => ($customer_data->address_line_1)? $customer_data->address_line_1: 'no'
                );
                $sendmail = true;
            }
            //leer los metodos y formas de pago
            $payment_form = 0;
            $duration_measure = 0;
            $payment_method = 0;

            if($input['payment'][0]['method'] == 'cash')
            {
                $payment_method = 10;
            }else if($input['payment'][0]['method'] == 'cheque')
            {
                $payment_method = 20;
            }else if($input['payment'][0]['method'] == 'custom_pay_1' || $input['payment'][0]['method'] == 'custom_pay_2' || $input['payment'][0]['method'] == 'custom_pay_3'  || $input['payment'][0]['method'] == 'custom_pay_4')
            {
                $payment_method = 1;
            }else if($input['payment'][0]['method'] == 'bank_transfer')
            {
                $payment_method = 47;
            }else if($input['payment'][0]['method'] == 'other')
            {
                $payment_method = 1;
            
            }else{
                $payment_method = $input['payment'][0]['method'];
            }

            if($input['is_credit_sale'] == '1' || $input['payment'][0]['amount'] == '0')
            {
                if($input['pay_term_number'] == '' || $input['pay_term_number'] == 0)
                {
                    $duration_measure = 30;
                }else{
                    $duration_measure = $input['pay_term_number'];
                }

                $payment_form = 2;//credito
                
            }elseif($input['is_credit_sale'] == '0'){
                $payment_form = 1;//debido
            }
            //si is_credit_sale	= 1 es venta a credito
            $paymentForm = array(
                "duration_measure" => $duration_measure,//dias de plazo para pago
                "payment_form_id" => $payment_form,//1 contado 2 credito
                "payment_method_id" => $payment_method,
                "payment_due_date" => $actual_date
            );

            $discount = 0;
            
            if(self::num_uf($input['discount_amount']) > 0)
            {
                if($input['discount_type'] == "percentage")
                {
                    $discount = (self::num_uf($input['discount_amount']) / 100) * $line_extension_amount;
                    $payable_amount = $tax_inclusive_amount - $discount;

                }else{

                    $discount = self::num_uf($input['discount_amount']);
                    $payable_amount = $tax_inclusive_amount - $discount;
                }
                // dd()
            }

            $legalMonetaryTotals = array(
                "line_extension_amount" => self::round_number($line_extension_amount),
                "tax_exclusive_amount" => self::round_number($tax_exclusive_amount),//
                "tax_inclusive_amount" => self::round_number($tax_inclusive_amount),
                "charge_total_amount" => "0.00",
                // "payable_amount" => $this->round_number($final_total),
                "allowance_total_amount" => self::round_number($discount),
                "payable_amount" => self::round_number($payable_amount),
            );

            $softwareManufacturer = array(
                "name" => "Delio Ospino Barros",
                "business_name" => $business_data->name,
                "software_name" => "ZISCO"
            );

            // dd($name);
            
            $buyerBenefits = array(
                "name" => ($customer_data->contact_type == 'individual') ? $customer_data->name : $customer_data->supplier_business_name,
                "code" => $customer_data->contact_id,
                "points" => "0"
            );

            //obtener información de la caja abierta
            $cash_register_information = CashRegister::where('business_id',session()->get('user.business_id'))->where('user_id',session()->get('user.id'))->where('status','open')->first();
        
            $cashInformation = array(
                "plate_number" => $cash_register_information->cash_register_information->plate_number,//numero de matricula
                "location" => $customer_data->contact_id,
                "cashier" => session()->get('user.first_name').' '.session()->get('user.last_name'),//vendedor
                "cash_type" => $cash_register_information->cash_register_information->cash_type,//tipo de caja
                "sales_code" => $cash_register_information->cash_register_information->sales_code,//codigo de ventas
                "subtotal" => self::round_number($line_extension_amount),//subtotal
            );


            // Construcción del JSON dinámicamente
            $data = array(
                "number" => $invoiceNumber,
                "postal_zone_code" => "200001",
                "software_manufacturer"=> $softwareManufacturer,
                "buyer_benefits"=> $buyerBenefits,
                "cash_information"=> $cashInformation,
                "prefix" => $prefix,
                "notes" => $input['sale_note'],
                "foot_note" => $input['staff_note'],
                "type_document_id" => $typeDocumentId,
                "date" => $date,
                "time" => $time,
                "sendmail" => $sendmail,
                "resolution_number" => $resolutionNumber,
                "customer" => $customer,
                "payment_form" => $paymentForm,
                // "previous_balance" => $previousBalance,
                "legal_monetary_totals" => $legalMonetaryTotals,
                // "allowance_charges" => $allowanceCharges,
            );

            if (!empty($invoiceLines)) {
                $data["invoice_lines"] = $invoiceLines;
            }

            if (!empty($tax_total_invoice)) {
                $data["tax_totals"] = $tax_total_invoice;
            }
            
            if (self::num_uf($input['discount_amount']) > 0) {
                $data["allowance_charges"] = [[
                    "discount_id" => 1,
                    "charge_indicator" => false,
                    "allowance_charge_reason" =>"DESCUENTO GENERAL",
                    "amount" => self::num_uf($discount),
                    "base_amount" => self::round_number($line_extension_amount)
                ]];
            }

            // dd($data);

            $jsonData = json_encode($data);
            // return $jsonData;
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => env('APP_API_FE').'/api/ubl2.1/eqdoc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer '.$business_data->dian_token
            ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            $resp_curl_code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);


            // dd($resp_curl_code);
            //200 cuando la respuesta es ok
            //422 cuando retorna error de validación por falta de un campo en el json
            curl_close($curl);


            switch ($resp_curl_code) {
                case 200:
                    $respuesta = json_decode($response);
                    // dd($respuesta);
                    // if($respuesta->success){

                        if(isset($respuesta->ResponseDian))
                        {
                            $message = '';
                            $code = '';
                            $response_dian =  $respuesta->ResponseDian;
                            $StatusDescription = $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusDescription;
                            $IsValid = $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->IsValid;
                            $StatusCode = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode : '';
                            if($StatusCode == "500")
                            {
                                $message = $StatusDescription;
                                $transaction = Transaction::find($transaction->id);
                                    // $transaction->cufe = $cufe;
                                    // $transaction->e_invoice = 'si';
                                    $transaction->is_valid = false;
                                    // $transaction->qrstr = $QRStr;
                                    $transaction->rules = $StatusDescription;
                                    $transaction->status_code = $StatusCode;
                                    $transaction->save();
            
                                    // $message = 'Factura aceptada por la DIAN';
                                    $code = 0;
                            }else{
                             
                                
                                
                                $ErrorRules = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage->string)
                                ? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage->string 
                                : '';

                                $cufe = $respuesta->cufe;
                                $QRStr = $respuesta->QRStr;
                                // $ErrorRules = '';
                                
            
                                if($IsValid == "true" && $StatusCode == 00)
                                {
                                    //guardamos el cufe de la factura y cambiamos estado de la facturA en el sistema
                                    $transaction = Transaction::find($transaction->id);
                                    $transaction->cufe = $cufe;
                                    $transaction->e_invoice = 'si';
                                    $transaction->is_valid = true;
                                    $transaction->qrstr = $QRStr;
                                    $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
                                    $transaction->status_code = $StatusCode;
                                    $transaction->save();
            
                                    $message = 'Factura aceptada por la DIAN';
                                    $code = 1;
                                }else if($IsValid == "false"){
                                    
                                    $StatusCode = $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode;
                                    $message = $ErrorRules;
                                    $code = 0;
                                    $transaction = Transaction::find($transaction->id);
                                    if($StatusCode == 99)
                                    {
                                        $transaction->cufe = $cufe;
                                        $transaction->is_valid = false;
                                        $transaction->qrstr = $QRStr;
                                        $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
                                        $transaction->status_code = $StatusCode;
                                    }else{
                                        $transaction->is_valid = false;
                                        $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
                                        $transaction->status_code = $StatusCode;
                                    }
                                    $transaction->save();
                                }
                            }
                                $output = [
                                    'success' =>$code, 
                                    'msg' => $message, 
                                    'input_curl'=> $data, 
                                    'input_factura'=> $input, 
                                    'response' => ($respuesta) ? $respuesta : '',  
                                    'cufe' => (isset($cufe)) ? $cufe : '',
                                    'IsValid' => ($IsValid) ? $IsValid : '',
                                    'QRStr' => (isset($QRStr)) ? $QRStr : '',
                                    'ErrorMessage' =>  (isset($ErrorRules)) ? $ErrorRules : ''
                                ];
                                return $output;
                            
                        }else{
        
                            //respuesta almacenada
                            $transaction = Transaction::find($transaction->id);
                            $transaction->is_valid = false;
                            $transaction->save();
                            //fin de respuesta
                            $output = [
                                'success' => 0, 
                                // 'msg' => $respuesta->error[0], 
                                'msg' => $respuesta, 
                                'input_curl'=> $data, 
                            ];
                            return $output;
                        }
                    // }
                    break;
                case 422:
                    $respuesta = json_decode($response);
                    $message = $respuesta->message;
                    $errors = $respuesta->errors;
                    //respuesta almacenada
                    $transaction = Transaction::find($transaction->id);
                    $transaction->is_valid = false;
                    $transaction->save();
                    //fin de respuesta
                    $output = [
                        'success' =>0, 
                        'msg' => $message, 
                        'input_curl'=> $data, 
                        'input_factura'=> $input, 
                        'response' => ($respuesta) ? $respuesta : '',  
                        'cufe' => '',
                        'IsValid' =>  false,
                        'QRStr' =>  '',
                        'ErrorMessage' => $errors
                    ];
                    return $output;
                    break;
                case 404:
                    $respuesta = json_decode($response);
                    $message = $respuesta->message;
                    //respuesta almacenada
                    $transaction = Transaction::find($transaction->id);
                    $transaction->is_valid = false;
                    $transaction->save();
                    //fin de respuesta
                    $output = [
                        'success' =>0, 
                        'msg' => "error 404 Url incorrecta", 
                        'input_curl'=> $data, 
                        'input_factura'=> $input, 
                        'response' => ($respuesta) ? $respuesta : '',  
                        'cufe' => '',
                        'IsValid' =>  false,
                        'QRStr' =>  '',
                        'ErrorMessage' => 'La url a la que intenta hacer la petición no existe'
                    ];
                    return $output;
                    break;
                default:
                    $respuesta = json_decode($response);
                    $message = $respuesta->message;
                    //respuesta almacenada
                    $transaction = Transaction::find($transaction->id);
                    $transaction->is_valid = false;
                    $transaction->save();
                    //fin de respuesta
                    $output = [
                        'success' =>0, 
                        'msg' => "error 404 Url incorrecta", 
                        'input_curl'=> $data, 
                        'input_factura'=> $input, 
                        'response' => ($respuesta) ? $respuesta : '',  
                        'cufe' => '',
                        'IsValid' =>  false,
                        'QRStr' =>  '',
                        'ErrorMessage' => $message
                    ];
                    return $output;
            }

        }else{


            $output = [
                'success' => 1, 
                'msg' => 'No es una factura electrónica', 
                // 'receipt' => $receipt
            ];
            return $output;
        }

    }

    public static function resend_eqpos($transaction_before, $business_id, $contact_id, $input,$prefix, $number, $resolution)
    {
        $customer_data = Contact::findOrFail($contact_id);
        $business_data = Business::find($business_id);

        //validamos si se va a enviar factua electronica o no
        if($transaction_before->is_valid != 1 && $transaction_before->e_invoice == 'si' && $transaction_before->status == "final" && $transaction_before->is_suspend == "0")//final
        // if($transaction_before->is_valid == 1  && $transaction_before->status == "final" && $transaction_before->is_suspend == "0")//final
        {

            
            $actual_date = Carbon::now('America/Bogota')->format('Y-m-d');
            $actual_hous = Carbon::now('America/Bogota')->format('H:m:s');


            $total_tax_products = 0;
            $total_not_tax_products = 0;
            // $total_not_tax_products = 0;
            $line_extension_amount = 0;
            $tax_inclusive_amount = 0;
            $tax_exclusive_amount = 0;
            $payable_amount = 0;
            $payable_amount_inc_discount = 0;
            

            $invoiceLines = array();

            $tax_totals_map = [];//array para agregar y sumar los impuestos a nivel de factura

            $tax_total_invoice = [];


            if(isset($input))
            {
                foreach ($input as $product){
                    $tax_totals = [];//impuestos totales de la factura

                    $product_db = Product::findOrFail($product['product_id']);

                    $unit_price = self::convert_numeric($product['unit_price']);

                    //calculo de los campos de cada linea convert_numeric($number)
                    $line_extension_amount_product = (floatval($product['quantity']) * floatval($unit_price));

                    //CONSTRUCCIÓN DEL JSON DE IMPUESTOS
                    if(isset($product['tax_id'])){
                        $tax = TaxRate::find($product['tax_id']);

                        $tax_amount_product = self::tax($line_extension_amount_product,floatval($tax['amount']));
                        // $tax_total_product = $tax_amount_product + $line_extension_amount_product;

                        $tax_totals[] = [
                            // "tax_id" =>intval($product['tax_id']),//ERROR AQUI
                            "tax_id" =>$tax->code,
                            "tax_amount" => self::round_number($tax_amount_product),
                            "taxable_amount" => self::round_number($line_extension_amount_product),//valor del producto sin impuesto
                            "percent" => intval($tax['amount'])
                        ];

                        // Sumar el impuesto al total de impuestos de la factura
                        $tax_key = intval($tax['code']) . '_' . floatval($tax['amount']);
                        if (isset($tax_totals_map[$tax_key])) {
                            // $tax_totals_map[$tax_key]['tax_id'] = intval($tax['tax_id']);
                            $tax_totals_map[$tax_key]['tax_amount'] += self::round_number($tax_amount_product);
                            $tax_totals_map[$tax_key]['taxable_amount'] += self::round_number($line_extension_amount_product);
                        } else {
                            $tax_totals_map[$tax_key] = [
                                "tax_id" => $tax->code,
                                "tax_amount" => self::round_number($tax_amount_product),
                                "taxable_amount" => self::round_number($line_extension_amount_product),
                                "percent" => floatval($tax['amount'])
                            ];
                        }


                        //sumamos el total de las bases productos con impuestos
                        $tax_exclusive_amount +=  $line_extension_amount_product;

                        //sumamos el excedente del impuesto al total de la linea
                        $line_extension_amount_product += $tax_amount_product;


                        
                    }
                    $total_line = floatval($product['quantity']) * $unit_price;
                    
                    if(isset($product['tax_id'])){
                        $invoiceLines[] = [
                            "unit_measure_id" => 70,
                            "invoiced_quantity" => floatval($product['quantity']),
                            "line_extension_amount" => self::round_number($total_line),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            // "line_extension_amount" => $this->round_number($line_extension_amount_product),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            "free_of_charge_indicator" => false,
                            
                            "description" => $product_db->name,
                            "code" => $product_db->sku,
                            "type_item_identification_id" => 4,
                            "price_amount" => self::round_number($line_extension_amount_product),//Valor de la linea de la factura
                            "base_quantity" =>floatval($product['quantity']),
                            "tax_totals" => $tax_totals

                        ];
                        unset($tax_totals);
                    }else{
                        $invoiceLines[] = [
                            "unit_measure_id" => 70,
                            "invoiced_quantity" => floatval($product['quantity']),
                            "line_extension_amount" => self::round_number($total_line),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            "free_of_charge_indicator" => false,
                            
                            "description" => $product_db->name,
                            "code" => $product_db->sku,
                            "type_item_identification_id" => 4,
                            "price_amount" => self::round_number($line_extension_amount_product),//Valor de la linea de la factura
                            "base_quantity" =>floatval($product['quantity']),

                        ];
                    }


                    $tax_inclusive_amount += $line_extension_amount_product;
                }

            }

            // Convertir el mapa de impuestos a un array de totales de impuestos
            $tax_total_invoice = array_values($tax_totals_map);

            //calcular los invoice line
            if(isset($invoiceLines)){
                foreach($invoiceLines as $invoice_product)
                {
                    if(isset($invoice_product['tax_id']))
                    {
                        $total_tax_products += floatval($invoice_product['line_extension_amount']);
                    }else{
                        $total_not_tax_products +=  floatval($invoice_product['line_extension_amount']);
                    }
                    $line_extension_amount += floatval($invoice_product['line_extension_amount']);

                    //calculñar el total de la factura
                    $payable_amount = $payable_amount + $invoice_product['price_amount'];
                }
            }

            $date = $actual_date;
            $time = $actual_hous;
            $sendmail = true;
            
            if($customer_data->contact_id == 222222222222)
            {
                $customer = array(
                    "identification_number" => "222222222222",
                    "name" => "Consumidor Final",
                    "merchant_registration" => "0000000-00",
                );
            }else{
                $contact_type = 0;
                $name = '';
                if($customer_data->contact_type == 'individual')
                {
                    $contact_type = 2;
                    $name = $customer_data->name;
                }else{
                    $contact_type = 1;
                    $name = $customer_data->supplier_business_name;
                }
                $customer = array(
                    "identification_number" => $customer_data->contact_id,
                    "dv" => $customer_data->dv,
                    "name" => $name,
                    "type_organization_id" => $contact_type,
                    "phone" => $customer_data->mobile,
                    "merchant_registration" => ($customer_data->merchant_registration)?$customer_data->merchant_registration : "0000000",
                    "type_document_identification_id" => ($customer_data->type_document_identification_id)?$customer_data->type_document_identification_id : "",
                    "type_regime_id" => ($customer_data->type_regime_id)?$customer_data->type_regime_id : "",
                    "municipality_id" => ($customer_data->municipality_id)?$customer_data->municipality_id : "",
                    "email" => $customer_data->email,
                    "address" => ($customer_data->address_line_1)? $customer_data->address_line_1: 'no'
                );
                $sendmail = true;
            }
            // dd($transaction_before);
            $transaction_payment = TransactionPayment::where('business_id', $business_id)->where('transaction_id',$transaction_before->id)->first();
            // dd($transaction_payment);
            //leer los metodos y formas de pago
            $payment_form = 0;
            $duration_measure = 0;
            $payment_method = 0;

            if(!empty($transaction_payment->method))
            {
                if($transaction_payment->method == 'cash')
                {
                    $payment_method = 10;
                }else if($transaction_payment->method == 'cheque')
                {
                    $payment_method = 20;
                }else if($transaction_payment->method == 'custom_pay_1' || $transaction_payment->method == 'custom_pay_2' || $transaction_payment->method == 'custom_pay_3'  || $transaction_payment->method == 'custom_pay_4')
                {
                    $payment_method = 1;
                }else if($transaction_payment->method == 'bank_transfer')
                {
                    $payment_method = 47;
                }else if($transaction_payment->method == 'other')
                {
                    $payment_method = 1;
                
                }else{
                    $payment_method = $transaction_payment->method;
                }
            }else{
                if($transaction_before->payment_method == 'cash')
                {
                    $payment_method = 10;
                }else if($transaction_before->payment_method == 'cheque')
                {
                    $payment_method = 20;
                }else if($transaction_before->payment_method == 'custom_pay_1' || $transaction_before->payment_method == 'custom_pay_2' || $transaction_before->payment_method == 'custom_pay_3'  || $transaction_before->payment_method == 'custom_pay_4')
                {
                    $payment_method = 1;
                }else if($transaction_before->payment_method == 'bank_transfer')
                {
                    $payment_method = 47;
                }else if($transaction_before->payment_method == 'other')
                {
                    $payment_method = 1;
                
                }else{
                    $payment_method = $transaction_before->payment_method;
                }
                
            }
            if($transaction_before->payment_status == 'due' || $transaction_before->payment_status == 'partial')
            {
                if($transaction_before->pay_term_number == '' || $transaction_before->pay_term_number == 0)
                {
                    $duration_measure = 30;
                }else{
                    $duration_measure = $transaction_before->pay_term_number;
                }

                $payment_form = 2;//credito
                
                
            }elseif($transaction_before->payment_status == 'paid'){
                $payment_form = 1;//debido
            }
            //si is_credit_sale	= 1 es venta a credito
            $paymentForm = array(
                "duration_measure" => $duration_measure,//dias de plazo para pago
                "payment_form_id" => $payment_form,//1 contado 2 credito
                "payment_method_id" => $payment_method,
                "payment_due_date" => $actual_date
            );

            $discount = 0;
            
            if($transaction_before->discount_amount > 0)
            {
                if($transaction_before->discount_type == "percentage")
                {
                    $discount = ($transaction_before->discount_amount / 100) * $payable_amount;
                    $payable_amount_inc_discount = $tax_inclusive_amount - $discount;

                }else{

                    $discount = $transaction_before->discount_amount;
                    $payable_amount_inc_discount = $tax_inclusive_amount - $discount;
                }
                // dd()
            }else{
                $payable_amount_inc_discount = $tax_inclusive_amount;
            }

            $softwareManufacturer = array(
                "name" => "Delio Ospino Barros",
                "business_name" => $business_data->name,
                "software_name" => "ZISCO"
            );

            $legalMonetaryTotals = array(
                "line_extension_amount" => self::round_number($line_extension_amount),
                "tax_exclusive_amount" => self::round_number($tax_exclusive_amount),//
                "tax_inclusive_amount" => self::round_number($tax_inclusive_amount),
                "charge_total_amount" => "0.00",
                // "payable_amount" => $this->round_number($final_total),
                "allowance_total_amount" => self::round_number($discount),
                "payable_amount" => self::round_number($payable_amount_inc_discount),
            );

            $buyerBenefits = array(
                "name" => ($customer_data->contact_type == 'individual') ? $customer_data->name : $customer_data->supplier_business_name,
                "code" => $customer_data->contact_id,
                "points" => "0"
            );

            //obtener información de la caja abierta
            $cash_register_information = CashRegister::where('business_id',session()->get('user.business_id'))->where('user_id',session()->get('user.id'))->where('status','open')->first();
        
            $cashInformation = array(
                "plate_number" => $cash_register_information->cash_register_information->plate_number,//numero de matricula
                "location" => $customer_data->contact_id,
                "cashier" => session()->get('user.first_name').' '.session()->get('user.last_name'),//vendedor
                "cash_type" => $cash_register_information->cash_register_information->cash_type,//tipo de caja
                "sales_code" => $cash_register_information->cash_register_information->sales_code,//codigo de ventas
                "subtotal" => self::round_number($line_extension_amount),//subtotal
            );

            // Construcción del JSON dinámicamente
            $data = array(
                "number" => $number,
                "prefix" => $prefix,
                "postal_zone_code" => "200001",
                "software_manufacturer"=> $softwareManufacturer,
                "buyer_benefits"=> $buyerBenefits,
                "cash_information"=> $cashInformation,
                "type_document_id" => 15,
                "date" => $date,
                "time" => $time,
                "notes" => $transaction_before->additional_notes,
                "foot_note" => $transaction_before->staff_note,
                "sendmail" => $sendmail,
                "resolution_number" => $resolution,
                "customer" => $customer,
                "payment_form" => $paymentForm,
                // "previous_balance" => $previousBalance,
                "legal_monetary_totals" => $legalMonetaryTotals,
                // "allowance_charges" => $allowanceCharges,
            );

            if (!empty($invoiceLines)) {
                $data["invoice_lines"] = $invoiceLines;
            }

            if (!empty($tax_total_invoice)) {
                $data["tax_totals"] = $tax_total_invoice;
            }

            if ($transaction_before->discount_amount > 0) {
                $data["allowance_charges"] = [[
                    "discount_id" => 1,
                    "charge_indicator" => false,
                    "allowance_charge_reason" =>"DESCUENTO GENERAL",
                    "amount" => $discount,
                    "base_amount" => self::round_number($line_extension_amount)
                ]];
            }

            // return $data;

            $jsonData = json_encode($data);
            // return $jsonData;
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => env('APP_API_FE').'/api/ubl2.1/eqdoc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer '.$business_data->dian_token
            ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            $resp_curl_code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

            curl_close($curl);



            switch ($resp_curl_code) {
                case 200:
                    $respuesta = json_decode($response);
                    // if($respuesta->success){

                        if(isset($respuesta->ResponseDian))
                        {
                            $response_dian =  $respuesta->ResponseDian;
                            $IsValid = $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->IsValid;
                            $StatusCode = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode : '';
                            $ErrorRules = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage->string : '';
                            $cufe = $respuesta->cufe;
                            $QRStr = $respuesta->QRStr;
                            // $ErrorRules = '';
                            $message = '';
                            $code = '';
        
                            if($IsValid == "true" && $StatusCode == 00)
                            {
                                //guardamos el cufe de la factura y cambiamos estado de la facturA en el sistema
                                $transaction = Transaction::find($transaction_before->id);
                                $transaction->cufe = $cufe;
                                // $transaction->e_invoice = 'si';
                                $transaction->is_valid = true;
                                $transaction->qrstr = $QRStr;
                                $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
                                $transaction->status_code = $StatusCode;
                                $transaction->save();
        
                                $message = 'Factura aceptada por la DIAN';
                                $code = 1;
                            }else if($IsValid == "false"){
                                
                                $StatusCode = $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode;
                                $message = $ErrorRules;
                                $code = 0;
                                $transaction = Transaction::find($transaction_before->id);
                                if($StatusCode == 99)//factura procesada anteriormente
                                {
                                    $res_invoice = self::read_xml_invoice($respuesta->invoicexml);
                                    $res_invoice['payable_amount'];
                                    if(self::round_number($res_invoice['payable_amount']) == self::round_number($payable_amount_inc_discount))
                                    {//se verifica que los valores de la factura sean los mismos, el de la dian con la del sistema
                                        // dd($respuesta->ResponseDian);
                                        $transaction->cufe = $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->XmlDocumentKey;
                                        $transaction->is_valid = true;
                                        $transaction->qrstr = $QRStr;
                                        $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
                                        $transaction->status_code = $StatusCode;
                                    }
                                    
                                }else{
                                    $transaction->is_valid = false;
                                    $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
                                    $transaction->status_code = $StatusCode;
                                }
                                $transaction->save();
                            }
        
                            $output = [
                                'success' =>$code, 
                                'msg' => $message, 
                                'input_curl'=> $data, 
                                'input_factura'=> $input, 
                                'response' => ($respuesta) ? $respuesta : '',  
                                'cufe' => ($cufe) ? $cufe : '',
                                'IsValid' => ($IsValid) ? $IsValid : '',
                                'QRStr' => ($QRStr) ? $QRStr : '',
                                'ErrorMessage' => $ErrorRules
                            ];
                            return $output;
                        }else{
        
                            //respuesta almacenada
                            $transaction = Transaction::find($transaction_before->id);
                            $transaction->is_valid = false;
                            $transaction->save();
                            //fin de respuesta
                            $output = [
                                'success' => 0, 
                                // 'msg' => $respuesta->error[0], 
                                'msg' => $respuesta, 
                                'input_curl'=> $data, 
                            ];
                            return $output;
                        }
                    // }
                    break;
                case 422:
                    $respuesta = json_decode($response);
                    $error_msg = '';
                    foreach ($respuesta->errors as $campo => $mensajes) {
                        $error_msg = $mensajes[0] . "\n";
                    }

                    $message = $respuesta->message.' - '.$error_msg;
                    $errors = $respuesta->errors;
                    //respuesta almacenada
                    $transaction = Transaction::find($transaction_before->id);
                    $transaction->is_valid = false;
                    $transaction->save();
                    //fin de respuesta
                    $output = [
                        'success' =>0, 
                        'msg' => $message, 
                        'input_curl'=> $data, 
                        'input_factura'=> $input, 
                        'response' => ($respuesta) ? $respuesta : '',  
                        'cufe' => '',
                        'IsValid' =>  false,
                        'QRStr' =>  '',
                        'ErrorMessage' => $errors
                    ];
                    return $output;
                    break;
                case 404:
                    $respuesta = json_decode($response);
                    $message = $respuesta->message;
                    //respuesta almacenada
                    $transaction = Transaction::find($transaction_before->id);
                    $transaction->is_valid = false;
                    $transaction->save();
                    //fin de respuesta
                    $output = [
                        'success' =>0, 
                        'msg' => "error 404 Url incorrecta", 
                        'input_curl'=> $data, 
                        'input_factura'=> $input, 
                        'response' => ($respuesta) ? $respuesta : '',  
                        'cufe' => '',
                        'IsValid' =>  false,
                        'QRStr' =>  '',
                        'ErrorMessage' => 'La url a la que intenta hacer la petición no existe'
                    ];
                    return $output;
                    break;
                default:
                    $respuesta = json_decode($response);
                    $message = $respuesta->message;
                    //respuesta almacenada
                    $transaction = Transaction::find($transaction_before->id);
                    $transaction->is_valid = false;
                    $transaction->save();
                    //fin de respuesta
                    $output = [
                        'success' =>0, 
                        'msg' => "error 404 Url incorrecta", 
                        'input_curl'=> $data, 
                        'input_factura'=> $input, 
                        'response' => ($respuesta) ? $respuesta : '',  
                        'cufe' => '',
                        'IsValid' =>  false,
                        'QRStr' =>  '',
                        'ErrorMessage' => $message
                    ];
                    return $output;
            }

            
        }else{
            $output = [
                'success' => 1, 
                'msg' => 'No es una factura electrónica o ya fue enviada anteriormente', 
            ];
            return $output;
        }
    }


    public static function send_credit_note($business_id, $contact_id, $input,$sell_return)
    {

        $i_echeme = '';

        $invoice_scheme = InvoiceScheme::where('business_id',$business_id)->where('type_document_id',4)->first();
        $i_echeme = $invoice_scheme->type_document_id;

        $business_data = Business::find($business_id);

        //validamos si se va a enviar factua electronica o no
        if($i_echeme == '4' && $input['status'] == "final" && $input['type'] == "sell")//final
        {

            
            $actual_date = Carbon::now('America/Bogota')->format('Y-m-d');
            $actual_hous = Carbon::now('America/Bogota')->format('H:m:s');

            $invoice_number = intval($invoice_scheme->start_number) + intval($invoice_scheme->invoice_count) -1;

            $total_tax_products = 0;
            $total_not_tax_products = 0;
            // $total_not_tax_products = 0;
            $line_extension_amount = 0;
            $tax_inclusive_amount = 0;
            $tax_exclusive_amount = 0;
            // $taxes = [];//impuesto por linea de producto
            $payable_amount = 0;
            

            $invoiceLines = array();

            $tax_totals_map = [];//array para agregar y sumar los impuestos a nivel de factura

            $tax_total_invoice = [];

            if(isset($input['sell_lines']))
            {
                foreach ($input['sell_lines'] as $product){
                    $tax_totals = [];//impuestos totales de la factura
                    // $total_product = 0;
                    // $tax_total_product = 0;

                    $product_db = Product::findOrFail($product['product_id']);

                    $unit_price = self::convert_numeric($product['unit_price']);

                    //calculo de los campos de cada linea convert_numeric($number)
                    $line_extension_amount_product = (floatval($product['quantity_returned']) * floatval($unit_price));

                    //CONSTRUCCIÓN DEL JSON DE IMPUESTOS
                    if(isset($product['tax_id'])){
                        $tax = TaxRate::find($product['tax_id']);

                        $tax_amount_product = self::tax($line_extension_amount_product,floatval($tax['amount']));
                        // $tax_total_product = $tax_amount_product + $line_extension_amount_product;

                        $tax_totals[] = [
                            // "tax_id" =>intval($product['tax_id']),//ERROR AQUI
                            "tax_id" =>$tax->code,
                            "tax_amount" => self::round_number($tax_amount_product),
                            "taxable_amount" => self::round_number($line_extension_amount_product),//valor del producto sin impuesto
                            "percent" => intval($tax['amount'])
                        ];

                        // Sumar el impuesto al total de impuestos de la factura
                        $tax_key = intval($tax['code']) . '_' . floatval($tax['amount']);
                        if (isset($tax_totals_map[$tax_key])) {
                            // $tax_totals_map[$tax_key]['tax_id'] = intval($tax['tax_id']);
                            $tax_totals_map[$tax_key]['tax_amount'] += self::round_number($tax_amount_product);
                            $tax_totals_map[$tax_key]['taxable_amount'] += self::round_number($line_extension_amount_product);
                        } else {
                            $tax_totals_map[$tax_key] = [
                                "tax_id" => $tax->code,
                                "tax_amount" => self::round_number($tax_amount_product),
                                "taxable_amount" => self::round_number($line_extension_amount_product),
                                "percent" => floatval($tax['amount'])
                            ];
                        }


                        //sumamos el total de las bases productos con impuestos
                        $tax_exclusive_amount +=  $line_extension_amount_product;

                        //sumamos el excedente del impuesto al total de la linea
                        $line_extension_amount_product += $tax_amount_product;


                        
                    }
                    $total_line = floatval($product['quantity_returned']) * $unit_price;
                    
                    if(isset($product['tax_id'])){
                        $invoiceLines[] = [
                            "unit_measure_id" => 70,
                            "invoiced_quantity" => floatval($product['quantity_returned']),
                            "line_extension_amount" => self::round_number($total_line),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            // "line_extension_amount" => $this->round_number($line_extension_amount_product),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            "free_of_charge_indicator" => false,
                            
                            "description" => $product_db->name,
                            "code" => $product_db->sku,
                            "type_item_identification_id" => 4,
                            "price_amount" => self::round_number($line_extension_amount_product),//Valor de la linea de la factura
                            "base_quantity" =>floatval($product['quantity_returned']),
                            "tax_totals" => $tax_totals

                        ];
                        unset($tax_totals);
                    }else{
                        $invoiceLines[] = [
                            "unit_measure_id" => 70,
                            "invoiced_quantity" => floatval($product['quantity_returned']),
                            "line_extension_amount" => self::round_number($total_line),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            "free_of_charge_indicator" => false,
                            
                            "description" => $product_db->name,
                            "code" => $product_db->sku,
                            "type_item_identification_id" => 4,
                            "price_amount" => self::round_number($line_extension_amount_product),//Valor de la linea de la factura
                            "base_quantity" =>floatval($product['quantity_returned']),

                        ];
                    }


                    $tax_inclusive_amount += $line_extension_amount_product;
                }

            }

            // Convertir el mapa de impuestos a un array de totales de impuestos
            $tax_total_invoice = array_values($tax_totals_map);

            //calcular los invoice line
            if(isset($invoiceLines)){
                foreach($invoiceLines as $invoice_product)
                {
                    if(isset($invoice_product['tax_id']))
                    {
                        $total_tax_products += floatval($invoice_product['line_extension_amount']);
                    }else{
                        $total_not_tax_products +=  floatval($invoice_product['line_extension_amount']);
                    }
                    $line_extension_amount += floatval($invoice_product['line_extension_amount']);

                    //calculñar el total de la factura
                    $payable_amount = $payable_amount + $invoice_product['price_amount'];
                }
            }


            // Parámetros dinámicos para notas credito
            $datetime = Carbon::parse($input->transaction_date);
            $fecha = $datetime->toDateString();

            $billing_reference = array(
                "number" => $input->invoice_no,//numero de la factura
                "uuid" => $input->cufe,//cufe de la factura
                // "issue_date" => "2024-01-17",//fecha de la factura
                "issue_date" => $fecha,//fecha de la factura
            );
            $customer_data = Contact::findOrFail($sell_return->contact_id);

            $invoiceNumber = $invoice_number;
            $discrepancyResponseDescription = "descripcion del motivo de la nota credito";//codigo motivo de la nota credito
            $discrepancyResponseCode = 2;
            
            $prefix = $invoice_scheme->prefix;
            $typeDocumentId = 4;
            $date = $actual_date;
            $time = $actual_hous;
            $sendmail = false;
            // $resolutionNumber = $invoice_scheme->resolution;
            if($customer_data->contact_id == 222222222222)
            {
                $customer = array(
                    "identification_number" => "222222222222",
                    "name" => "Consumidor Final",
                    "merchant_registration" => "0000000-00",
                );
            }else{
                $contact_type = 0;
                $name = '';
                if($customer_data->contact_type == 'individual')
                {
                    $contact_type = 2;
                    $name = $customer_data->name;
                }else{
                    $contact_type = 1;
                    $name = $customer_data->supplier_business_name;
                }
                $customer = array(
                    "identification_number" => $customer_data->contact_id,
                    "dv" => $customer_data->dv,
                    "name" => $name,
                    "type_organization_id" => $contact_type,
                    "phone" => $customer_data->mobile,
                    "merchant_registration" => ($customer_data->merchant_registration)?$customer_data->merchant_registration : "0000000-00",
                    "type_document_identification_id" => ($customer_data->type_document_identification_id)?$customer_data->type_document_identification_id : "",
                    "type_regime_id" => ($customer_data->type_regime_id)?$customer_data->type_regime_id : "",
                    "type_liability_id" => ($customer_data->liability_id)?$customer_data->liability_id : "",
                    "municipality_id" => ($customer_data->municipality_id)?$customer_data->municipality_id : "",
                    "email" => $customer_data->email,
                    "address" => ($customer_data->address_line_1)? $customer_data->address_line_1: 'no'
                );
                $sendmail = true;
            }

            
            $transaction_payment = TransactionPayment::where('business_id', $business_id)->where('transaction_id',$input->id)->first();
            // dd($transaction_payment);
            //leer los metodos y formas de pago
            $payment_form = 0;
            $duration_measure = 0;
            $payment_method = 0;

            if($transaction_payment->method == 'cash')
            {
                $payment_method = 10;
            }else if($transaction_payment->method == 'cheque')
            {
                $payment_method = 20;
            }else if($transaction_payment->method == 'other')
            {
                $payment_method = 1;
            
            }else{
                $payment_method = $transaction_payment->method;
            }

            if($input->payment_status == 'due' || $input->payment_status == 'partial')
            {
                if($input->pay_term_number == '' || $input->pay_term_number == 0)
                {
                    $duration_measure = 30;
                }else{
                    $duration_measure = $input->pay_term_number;
                }

                $payment_form = 2;//credito
                
            }elseif($input->payment_status == 'paid'){
                $payment_form = 1;//debido
            }
            //si is_credit_sale	= 1 es venta a credito
            $paymentForm = array(
                "duration_measure" => $duration_measure,//dias de plazo para pago
                "payment_form_id" => $payment_form,//1 contado 2 credito
                "payment_method_id" => $payment_method,
                "payment_due_date" => $actual_date
            );
            // $previousBalance = "0";
            $legalMonetaryTotals = array(
                "line_extension_amount" => self::round_number($line_extension_amount),
                "tax_exclusive_amount" => self::round_number($tax_exclusive_amount),//
                "tax_inclusive_amount" => self::round_number($tax_inclusive_amount),
                "charge_total_amount" => "0.00",
                // "payable_amount" => $this->round_number($final_total),
                "payable_amount" => self::round_number($tax_inclusive_amount),
                "allowance_total_amount" => "0.00"
            );

        


            // Construcción del JSON dinámicamente
            $data = array(
                "number" => $invoiceNumber,
                "prefix" => $prefix,
                "type_document_id" => $typeDocumentId,
                "discrepancyresponsecode" => $discrepancyResponseCode,
                "discrepancyresponsedescription" => $discrepancyResponseDescription,
                "date" => $date,
                "time" => $time,
                "sendmail" => $sendmail,
                "billing_reference" => $billing_reference,
                // "resolution_number" => $resolutionNumber,
                "customer" => $customer,
                "payment_form" => $paymentForm,
                // "previous_balance" => $previousBalance,
                "legal_monetary_totals" => $legalMonetaryTotals,
                // "allowance_charges" => $allowanceCharges,
            );

            if (!empty($invoiceLines)) {
                $data["credit_note_lines"] = $invoiceLines;
            }

            if (!empty($tax_total_invoice)) {
                $data["tax_totals"] = $tax_total_invoice;
            }

            // return $data;

            $jsonData = json_encode($data);
            // return $jsonData;
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => env('APP_API_FE').'/api/ubl2.1/credit-note',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer '.$business_data->dian_token
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $respuesta = json_decode($response);
            // dd($respuesta);
            $ErrorRules = '';
            $StatusCode = '';
            $MgsResponse = '';

            if(!isset($respuesta->success) || $respuesta->success){
                if(isset($respuesta->ResponseDian))
                {
                    

                    $response_dian =  $respuesta->ResponseDian;
                    $IsValid = $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->IsValid;
                    $ErrorRules = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage->string : '';
                    $cufe = $respuesta->cude;
                    $QRStr = $respuesta->QRStr;
                    $StatusCode = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode : '';
                    

                    if($IsValid == "true")
                    {
                        //guardamos el cufe de la factura y cambiamos estado de la facturA en el sistema
                        $transaction = Transaction::find($sell_return->id);
                        $transaction->cufe = $cufe;
                        $transaction->is_valid = true;
                        $transaction->qrstr = $QRStr;
                        $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
                        $transaction->status_code = $StatusCode;
                        $transaction->save();

                        $MgsResponse = 'Nota Crédito aceptada por la DIAN';                   
                    }else{
                        $ErrorRules = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage->string : '';
                        $StatusCode = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode : '';
                        if($StatusCode == '99')
                        {
                            $transaction = Transaction::find($sell_return->id);
                            $transaction->cufe = $cufe;
                            $transaction->is_valid = false;
                            $transaction->qrstr = $QRStr;
                            $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
                            $transaction->status_code = $StatusCode;
                            $transaction->save();
                            $MgsResponse = 'Nota Crédito procesada anteriormente';  
                        }
                    }

                    $output = [
                        'success' => 1, 
                        'msg' => $MgsResponse, 
                        'input_curl'=> $data, 
                        'input_factura'=> $input, 
                        'response' => ($respuesta) ? $respuesta : '',  
                        'cufe' => ($cufe) ? $cufe : '',
                        'IsValid' => ($IsValid) ? $IsValid : '',
                        'QRStr' => ($QRStr) ? $QRStr : '',
                        'ErrorMessage' => $ErrorRules
                    ];
                    return $output;
                }else{

                    
                    $output = [
                        'success' => 0, 
                        'msg' => $respuesta->error[0], 
                        'input_curl'=> $data, 
                    ];
                    return $output;
                }
            }else{
                $output = [
                    'success' => 1, 
                    'msg' => $respuesta->message, 
                ];
                return $output;
            }

        }else{


            $output = [
                'success' => 1, 
                'msg' => 'No es una factura electrónica', 
                // 'receipt' => $receipt
            ];
            return $output;
        }

    }

    public static function send_credit_note_eqdocs($business_id, $contact_id, $input,$sell_return)
    {

        $i_echeme = '';

        $invoice_scheme = InvoiceScheme::where('business_id',$business_id)->where('type_document_id',26)->first();
        $i_echeme = $invoice_scheme->type_document_id;

        $business_data = Business::find($business_id);

        //validamos si se va a enviar factua electronica o no
        if($i_echeme == '26' && $input['status'] == "final" && $input['type'] == "sell")//final
        {

            
            $actual_date = Carbon::now('America/Bogota')->format('Y-m-d');
            $actual_hous = Carbon::now('America/Bogota')->format('H:m:s');

            $invoice_number = intval($invoice_scheme->start_number) + intval($invoice_scheme->invoice_count) -1;

            $total_tax_products = 0;
            $total_not_tax_products = 0;
            // $total_not_tax_products = 0;
            $line_extension_amount = 0;
            $tax_inclusive_amount = 0;
            $tax_exclusive_amount = 0;
            // $taxes = [];//impuesto por linea de producto
            $payable_amount = 0;
            

            $invoiceLines = array();

            $tax_totals_map = [];//array para agregar y sumar los impuestos a nivel de factura

            $tax_total_invoice = [];

            if(isset($input['sell_lines']))
            {
                foreach ($input['sell_lines'] as $product){
                    $tax_totals = [];//impuestos totales de la factura
                    // $total_product = 0;
                    // $tax_total_product = 0;

                    $product_db = Product::findOrFail($product['product_id']);

                    $unit_price = self::convert_numeric($product['unit_price']);

                    //calculo de los campos de cada linea convert_numeric($number)
                    $line_extension_amount_product = (floatval($product['quantity_returned']) * floatval($unit_price));

                    //CONSTRUCCIÓN DEL JSON DE IMPUESTOS
                    if(isset($product['tax_id'])){
                        $tax = TaxRate::find($product['tax_id']);

                        $tax_amount_product = self::tax($line_extension_amount_product,floatval($tax['amount']));
                        // $tax_total_product = $tax_amount_product + $line_extension_amount_product;

                        $tax_totals[] = [
                            // "tax_id" =>intval($product['tax_id']),//ERROR AQUI
                            "tax_id" =>$tax->code,
                            "tax_amount" => self::round_number($tax_amount_product),
                            "taxable_amount" => self::round_number($line_extension_amount_product),//valor del producto sin impuesto
                            "percent" => intval($tax['amount'])
                        ];

                        // Sumar el impuesto al total de impuestos de la factura
                        $tax_key = intval($tax['code']) . '_' . floatval($tax['amount']);
                        if (isset($tax_totals_map[$tax_key])) {
                            // $tax_totals_map[$tax_key]['tax_id'] = intval($tax['tax_id']);
                            $tax_totals_map[$tax_key]['tax_amount'] += self::round_number($tax_amount_product);
                            $tax_totals_map[$tax_key]['taxable_amount'] += self::round_number($line_extension_amount_product);
                        } else {
                            $tax_totals_map[$tax_key] = [
                                "tax_id" => $tax->code,
                                "tax_amount" => self::round_number($tax_amount_product),
                                "taxable_amount" => self::round_number($line_extension_amount_product),
                                "percent" => floatval($tax['amount'])
                            ];
                        }


                        //sumamos el total de las bases productos con impuestos
                        $tax_exclusive_amount +=  $line_extension_amount_product;

                        //sumamos el excedente del impuesto al total de la linea
                        $line_extension_amount_product += $tax_amount_product;


                        
                    }
                    $total_line = floatval($product['quantity_returned']) * $unit_price;
                    
                    if(isset($product['tax_id'])){
                        $invoiceLines[] = [
                            "unit_measure_id" => 70,
                            "invoiced_quantity" => floatval($product['quantity_returned']),
                            "line_extension_amount" => self::round_number($total_line),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            // "line_extension_amount" => $this->round_number($line_extension_amount_product),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            "free_of_charge_indicator" => false,
                            
                            "description" => $product_db->name,
                            "code" => $product_db->sku,
                            "type_item_identification_id" => 4,
                            "price_amount" => self::round_number($line_extension_amount_product),//Valor de la linea de la factura
                            "base_quantity" =>floatval($product['quantity_returned']),
                            "tax_totals" => $tax_totals

                        ];
                        unset($tax_totals);
                    }else{
                        $invoiceLines[] = [
                            "unit_measure_id" => 70,
                            "invoiced_quantity" => floatval($product['quantity_returned']),
                            "line_extension_amount" => self::round_number($total_line),//Valor total de la línea. Cantidad x Precio Unidad menos descuentos más recargos que apliquen para la línea.
                            "free_of_charge_indicator" => false,
                            
                            "description" => $product_db->name,
                            "code" => $product_db->sku,
                            "type_item_identification_id" => 4,
                            "price_amount" => self::round_number($line_extension_amount_product),//Valor de la linea de la factura
                            "base_quantity" =>floatval($product['quantity_returned']),

                        ];
                    }


                    $tax_inclusive_amount += $line_extension_amount_product;
                }

            }

            // Convertir el mapa de impuestos a un array de totales de impuestos
            $tax_total_invoice = array_values($tax_totals_map);

            //calcular los invoice line
            if(isset($invoiceLines)){
                foreach($invoiceLines as $invoice_product)
                {
                    if(isset($invoice_product['tax_id']))
                    {
                        $total_tax_products += floatval($invoice_product['line_extension_amount']);
                    }else{
                        $total_not_tax_products +=  floatval($invoice_product['line_extension_amount']);
                    }
                    $line_extension_amount += floatval($invoice_product['line_extension_amount']);

                    //calculñar el total de la factura
                    $payable_amount = $payable_amount + $invoice_product['price_amount'];
                }
            }


            // Parámetros dinámicos para notas credito
            $datetime = Carbon::parse($input->transaction_date);
            $fecha = $datetime->toDateString();

            $billing_reference = array(
                "number" => $input->invoice_no,//numero de la factura
                "uuid" => $input->cufe,//cufe de la factura
                // "issue_date" => "2024-01-17",//fecha de la factura
                "issue_date" => $fecha,//fecha de la factura
                "type_document_id" => 15,//fecha de la factura
            );
            $customer_data = Contact::findOrFail($sell_return->contact_id);

            $invoiceNumber = $invoice_number;
            $discrepancyResponseDescription = "descripcion del motivo de la nota credito";//codigo motivo de la nota credito
            $discrepancyResponseCode = 2;
            
            $prefix = $invoice_scheme->prefix;
            $typeDocumentId = 26;
            $date = $actual_date;
            $time = $actual_hous;
            $sendmail = false;
            // $resolutionNumber = $invoice_scheme->resolution;
            if($customer_data->contact_id == 222222222222)
            {
                $customer = array(
                    "identification_number" => "222222222222",
                    "name" => "Consumidor Final",
                    "merchant_registration" => "0000000-00",
                );
            }else{
                $contact_type = 0;
                $name = '';
                if($customer_data->contact_type == 'individual')
                {
                    $contact_type = 2;
                    $name = $customer_data->name;
                }else{
                    $contact_type = 1;
                    $name = $customer_data->supplier_business_name;
                }
                $customer = array(
                    "identification_number" => $customer_data->contact_id,
                    "dv" => $customer_data->dv,
                    "name" => $name,
                    "type_organization_id" => $contact_type,
                    "phone" => $customer_data->mobile,
                    "merchant_registration" => ($customer_data->merchant_registration)?$customer_data->merchant_registration : "0000000-00",
                    "type_document_identification_id" => ($customer_data->type_document_identification_id)?$customer_data->type_document_identification_id : "",
                    "type_regime_id" => ($customer_data->type_regime_id)?$customer_data->type_regime_id : "",
                    "type_liability_id" => ($customer_data->liability_id)?$customer_data->liability_id : "",
                    "municipality_id" => ($customer_data->municipality_id)?$customer_data->municipality_id : "",
                    "email" => $customer_data->email,
                    "address" => ($customer_data->address_line_1)? $customer_data->address_line_1: 'no'
                );
                $sendmail = true;
            }

            
            $transaction_payment = TransactionPayment::where('business_id', $business_id)->where('transaction_id',$input->id)->first();
            // dd($transaction_payment);
            //leer los metodos y formas de pago
            $payment_form = 0;
            $duration_measure = 0;
            $payment_method = 0;

            if($transaction_payment->method == 'cash')
            {
                $payment_method = 10;
            }else if($transaction_payment->method == 'cheque')
            {
                $payment_method = 20;
            }else if($transaction_payment->method == 'other')
            {
                $payment_method = 1;
            
            }else{
                $payment_method = $transaction_payment->method;
            }

            if($input->payment_status == 'due' || $input->payment_status == 'partial')
            {
                if($input->pay_term_number == '' || $input->pay_term_number == 0)
                {
                    $duration_measure = 30;
                }else{
                    $duration_measure = $input->pay_term_number;
                }

                $payment_form = 2;//credito
                
            }elseif($input->payment_status == 'paid'){
                $payment_form = 1;//debido
            }
            //si is_credit_sale	= 1 es venta a credito
            $paymentForm = array(
                "duration_measure" => $duration_measure,//dias de plazo para pago
                "payment_form_id" => $payment_form,//1 contado 2 credito
                "payment_method_id" => $payment_method,
                "payment_due_date" => $actual_date
            );
            // $previousBalance = "0";
            $legalMonetaryTotals = array(
                "line_extension_amount" => self::round_number($line_extension_amount),
                "tax_exclusive_amount" => self::round_number($tax_exclusive_amount),//
                "tax_inclusive_amount" => self::round_number($tax_inclusive_amount),
                "charge_total_amount" => "0.00",
                // "payable_amount" => $this->round_number($final_total),
                "payable_amount" => self::round_number($tax_inclusive_amount),
                "allowance_total_amount" => "0.00"
            );

        


            // Construcción del JSON dinámicamente
            $data = array(
                "number" => $invoiceNumber,
                "prefix" => $prefix,
                "is_eqdoc" => true,
                // "type_operation_id" => 26,
                "type_document_id" => $typeDocumentId,
                "discrepancyresponsecode" => $discrepancyResponseCode,
                "discrepancyresponsedescription" => $discrepancyResponseDescription,
                "date" => $date,
                "time" => $time,
                "sendmail" => $sendmail,
                "billing_reference" => $billing_reference,
                // "resolution_number" => $resolutionNumber,
                "customer" => $customer,
                "payment_form" => $paymentForm,
                // "previous_balance" => $previousBalance,
                "legal_monetary_totals" => $legalMonetaryTotals,
                // "allowance_charges" => $allowanceCharges,
            );

            if (!empty($invoiceLines)) {
                $data["credit_note_lines"] = $invoiceLines;
            }

            if (!empty($tax_total_invoice)) {
                $data["tax_totals"] = $tax_total_invoice;
            }

            // return $data;

            $jsonData = json_encode($data);
            // dd($jsonData);
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => env('APP_API_FE').'/api/ubl2.1/credit-note',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer '.$business_data->dian_token
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            dd($response);
            $respuesta = json_decode($response);
            $ErrorRules = '';
            $StatusCode = '';
            $MgsResponse = '';

            if(!isset($respuesta->success) || $respuesta->success){
                if(isset($respuesta->ResponseDian))
                {
                    

                    $response_dian =  $respuesta->ResponseDian;
                    $IsValid = $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->IsValid;
                    $ErrorRules = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage->string : '';
                    $cufe = $respuesta->cude;
                    $QRStr = $respuesta->QRStr;
                    $StatusCode = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode : '';
                    

                    if($IsValid == "true")
                    {
                        //guardamos el cufe de la factura y cambiamos estado de la facturA en el sistema
                        $transaction = Transaction::find($sell_return->id);
                        $transaction->cufe = $cufe;
                        $transaction->is_valid = true;
                        $transaction->qrstr = $QRStr;
                        $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
                        $transaction->status_code = $StatusCode;
                        $transaction->save();

                        $MgsResponse = 'Nota Crédito aceptada por la DIAN';                   
                    }else{
                        $ErrorRules = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage->string : '';
                        $StatusCode = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode : '';
                        if($StatusCode == '99')
                        {
                            $transaction = Transaction::find($sell_return->id);
                            $transaction->cufe = $cufe;
                            $transaction->is_valid = false;
                            $transaction->qrstr = $QRStr;
                            $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
                            $transaction->status_code = $StatusCode;
                            $transaction->save();
                            $MgsResponse = 'Nota Crédito procesada anteriormente';  
                        }
                    }

                    $output = [
                        'success' => 1, 
                        'msg' => $MgsResponse, 
                        'input_curl'=> $data, 
                        'input_factura'=> $input, 
                        'response' => ($respuesta) ? $respuesta : '',  
                        'cufe' => ($cufe) ? $cufe : '',
                        'IsValid' => ($IsValid) ? $IsValid : '',
                        'QRStr' => ($QRStr) ? $QRStr : '',
                        'ErrorMessage' => $ErrorRules
                    ];
                    return $output;
                }else{

                    
                    $output = [
                        'success' => 0, 
                        'msg' => $respuesta->error[0], 
                        'input_curl'=> $data, 
                    ];
                    return $output;
                }
            }else{
                $output = [
                    'success' => 1, 
                    'msg' => $respuesta->message, 
                ];
                return $output;
            }

        }else{


            $output = [
                'success' => 1, 
                'msg' => 'No es una factura electrónica', 
                // 'receipt' => $receipt
            ];
            return $output;
        }

    }

    // public static function send_radian($business_id, $cufe, $event_id)
    // {
    //     $output = [];
    //     $isValid = false;

    //     $business_data = Business::find($business_id);
    //     //validamos cuantos eventos se han enviado
    //     $response = Http::withHeaders([
    //         'Content-Type: application/json',
    //         'Accept: application/json',
    //         'Authorization: Bearer '.$business_data->dian_token
        
    //     ])->post(env('APP_API_FE').'/api/ubl2.1/status/events-document/'.$cufe, [
    //         // 'event_id' => $event_id,
    //         // 'billing_reference' => array(
    //         //     'cufe' => $cufe
    //         // )
    //     ]);

    //     $responseData = $response->json();
    //     $isValid = $responseData['ResponseDian']['Envelope']['Body']['GetStatusEventResponse']['GetStatusEventResult']['IsValid'];
    //     $isValidBool = ($isValid === "true"); 

    //     $isValid = false;

    //     if (isset($responseData['ResponseDian']['Envelope']['Body']['GetStatusEventResponse']['GetStatusEventResult']['IsValid'])) {
    //         $isValid = $responseData['ResponseDian']['Envelope']['Body']['GetStatusEventResponse']['GetStatusEventResult']['IsValid'] === "true";
    //     }

    //     if ($isValid) {
    //         // La factura tiene eventos válidos
    //         // Procesar los eventos...
    //     } else {
    //         // No hay eventos válidos o hubo un error
    //         $statusCode = $responseData['ResponseDian']['Envelope']['Body']['GetStatusEventResponse']['GetStatusEventResult']['StatusCode'];
    //         $statusDesc = $responseData['ResponseDian']['Envelope']['Body']['GetStatusEventResponse']['GetStatusEventResult']['StatusDescription'];
    //         $output = [
    //             'success' => 0, 
    //             'msg' => $statusDesc, 
    //         ];
    //         return $output;
    //         // Log::warning("Factura sin eventos válidos. Código: $statusCode, Descripción: $statusDesc");
    //     }





    //     $i_echeme = '';

    //     $invoice_scheme = InvoiceScheme::where('business_id',$business_id)->where('type_document_id',4)->first();
    //     $i_echeme = $invoice_scheme->type_document_id;

        

    //     //validamos si se va a enviar factua electronica o no
    //     if($i_echeme == '4' && $input['status'] == "final" && $input['type'] == "sell")//final
    //     {

            

    //             $data = array(
    //                 "event_id" => $event_id,
    //                 "billing_reference" => array(
    //                     "cufe" => $cufe
    //                 ),
                    
    //             );
            
    //         // return $data;

    //         $jsonData = json_encode($data);
    //         // return $jsonData;
    //         $curl = curl_init();

    //         curl_setopt_array($curl, array(
    //         CURLOPT_URL => env('APP_API_FE').'/api/ubl2.1/send-event-data',
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS => $jsonData,
    //         CURLOPT_HTTPHEADER => array(
    //             'Content-Type: application/json',
    //             'Accept: application/json',
    //             'Authorization: Bearer '.$business_data->dian_token
    //         ),
    //         ));

    //         $response = curl_exec($curl);

    //         curl_close($curl);

    //         $respuesta = json_decode($response);
    //         // dd($respuesta);
    //         $ErrorRules = '';
    //         $StatusCode = '';
    //         $MgsResponse = '';

    //         if(!isset($respuesta->success) || $respuesta->success){
    //             if(isset($respuesta->ResponseDian))
    //             {
                    

    //                 $response_dian =  $respuesta->ResponseDian;
    //                 $IsValid = $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->IsValid;
    //                 $ErrorRules = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage->string : '';
    //                 $cufe = $respuesta->cude;
    //                 $QRStr = $respuesta->QRStr;
    //                 $StatusCode = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode : '';
                    

    //                 if($IsValid == "true")
    //                 {
    //                     //guardamos el cufe de la factura y cambiamos estado de la facturA en el sistema
    //                     $transaction = Transaction::find($sell_return->id);
    //                     $transaction->cufe = $cufe;
    //                     $transaction->is_valid = true;
    //                     $transaction->qrstr = $QRStr;
    //                     $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
    //                     $transaction->status_code = $StatusCode;
    //                     $transaction->save();

    //                     $MgsResponse = 'Nota Crédito aceptada por la DIAN';                   
    //                 }else{
    //                     $ErrorRules = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->ErrorMessage->string : '';
    //                     $StatusCode = isset($response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode)? $response_dian->Envelope->Body->SendBillSyncResponse->SendBillSyncResult->StatusCode : '';
    //                     if($StatusCode == '99')
    //                     {
    //                         $transaction = Transaction::find($sell_return->id);
    //                         $transaction->cufe = $cufe;
    //                         $transaction->is_valid = false;
    //                         $transaction->qrstr = $QRStr;
    //                         $transaction->rules = (is_array($ErrorRules)) ? json_encode($ErrorRules, true) : $ErrorRules;
    //                         $transaction->status_code = $StatusCode;
    //                         $transaction->save();
    //                         $MgsResponse = 'Nota Crédito procesada anteriormente';  
    //                     }
    //                 }

    //                 $output = [
    //                     'success' => 1, 
    //                     'msg' => $MgsResponse, 
    //                     'input_curl'=> $data, 
    //                     'input_factura'=> $input, 
    //                     'response' => ($respuesta) ? $respuesta : '',  
    //                     'cufe' => ($cufe) ? $cufe : '',
    //                     'IsValid' => ($IsValid) ? $IsValid : '',
    //                     'QRStr' => ($QRStr) ? $QRStr : '',
    //                     'ErrorMessage' => $ErrorRules
    //                 ];
    //                 return $output;
    //             }else{

                    
    //                 $output = [
    //                     'success' => 0, 
    //                     'msg' => $respuesta->error[0], 
    //                     'input_curl'=> $data, 
    //                 ];
    //                 return $output;
    //             }
    //         }else{
    //             $output = [
    //                 'success' => 1, 
    //                 'msg' => $respuesta->message, 
    //             ];
    //             return $output;
    //         }

    //     }else{


    //         $output = [
    //             'success' => 1, 
    //             'msg' => 'No es una factura electrónica', 
    //             // 'receipt' => $receipt
    //         ];
    //         return $output;
    //     }

    // }
}
