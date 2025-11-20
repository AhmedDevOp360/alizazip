<?php

namespace App\Http\Controllers;

use App\Contact;
use App\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $loans = DB::table('loans')->get()->paginate(10);
        return response()->json([
            'status' => true,
            'loans' => $loans
        ]);
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $contact = Contact::find($request->contact_id);
        $credits = DB::table('loans')
                    ->where('contacts_id', $request->contact_id)
                    ->orderBy('created_at', 'desc')
                    ->limit(1)
                    ->value('balance');

        $limit = $request->amount + $credits;
        if ($request->type == 1) {
            if ($limit > intval($contact->credit_limit)) {
                $output = ['error' => true,
                    'msg' => 'Limite de credito superado. Disponilidad de  $'.($contact->credit_limit - $credits),
                ];
                return redirect()->back()->with(['status' => $output]);
            }
        }

        $balance = ($request->type == 1) ? $request->amount + $credits :  $credits - $request->amount;
        $credit = Loan::create([
            'contacts_id' => $contact->id,
            'users_id' => Auth::user()->id,
            'amount' => $request->amount,
            'observation' => $request->note,
            'type' => $request->type,
            'balance' => $balance
        ]);
        
        $output = [
            'success' => true,
            'msg' => 'credito creado exitosamente',
        ];
        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $contact = Contact::find($id);
        $debt = DB::table('loans')
                    ->where('contacts_id', $id)
                    ->orderBy('created_at', 'desc')
                    ->limit(1)
                    ->value('balance');
        $availability =$contact->credit_limit - $debt;
        $availability = number_format($availability, 0, '.', ',');
        $debt = number_format($debt, 0, '.', ',');
        $loans = DB::table('loans')
                    ->join('users', 'loans.users_id', '=', 'users.id')
                    ->where('contacts_id', '=', $id)
                    ->where('type', '=', 1)
                    ->select('loans.*', 'users.first_name', 'users.last_name')
                    ->get();
        foreach ($loans as $loan){
            $loan->amount = number_format($loan->amount, 0, '.', ',');
        }
        return response()->json([
            'status' => true,
            'loans' => $loans,
            'debt' => $debt,
            'availability' => $availability
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $loan = DB::table('loans')->where('contacts_id', '=', $id)->get()->paginate(10);
        return response()->json([
            'status' => true,
            'loans' => $loan
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Loan $loan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Loan $loan)
    {
        //
    }
}
