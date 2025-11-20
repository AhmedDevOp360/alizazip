<?php

namespace App\Http\Controllers;

use App\Loan;
use App\LoanPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Contact  $contact_id
     * @return $loanPayments abonos de creditos asocionados a este contacto
     */
    public function show($id)
    {
        $loans = DB::table('loans')
                    ->join('users', 'loans.users_id', '=', 'users.id')
                    ->where('contacts_id', '=', $id)
                    ->where('type', '=', 2)
                    ->select('loans.*', 'users.first_name', 'users.last_name')
                    ->get();
        foreach ($loans as $loan){
            $loan->amount = number_format($loan->amount, 0, '.', ',');
        }
        return response()->json([
            'status' => true,
            'loans' => $loans
        ]);
    }

    public function sum($contact_id)
    {
        $loanPayment = DB::table('loans')
        ->where('contacts_id', $contact_id)
        ->orderBy('created_at', 'desc')
        ->limit(1)
        ->value('balance');
        return response()->json([
            'status' => true,
            'sum' => $loanPayment
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LoanPayment  $loanPayment
     * @return \Illuminate\Http\Response
     */
    public function edit(LoanPayment $loanPayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LoanPayment  $loanPayment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LoanPayment $loanPayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LoanPayment  $loanPayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(LoanPayment $loanPayment)
    {
        //
    }
}
