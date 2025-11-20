<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\CashRegister;
use App\CashRegisterInformation;
use App\TransactionPayment;
use App\Utils\CashRegisterUtil;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $cashRegisterUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  CashRegisterUtil  $cashRegisterUtil
     * @return void
     */
    public function __construct(CashRegisterUtil $cashRegisterUtil, ModuleUtil $moduleUtil)
    {
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('cash_register.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //like:repair
        $sub_type = request()->get('sub_type');

        //Check if there is a open register, if yes then redirect to POS screen.
        if ($this->cashRegisterUtil->countOpenedRegister() != 0) {
            return redirect()->action([\App\Http\Controllers\SellPosController::class, 'create'], ['sub_type' => $sub_type]);
        }
        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id);
        $cash_register_info = CashRegisterInformation::where('business_id',$business_id)->get()
        ->pluck('cash_type', 'id');

        return view('cash_register.create')->with(compact('business_locations', 'sub_type','cash_register_info'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //like:repair
        $sub_type = request()->get('sub_type');

        try {
            $initial_amount = 0;
            if (! empty($request->input('amount'))) {
                $initial_amount = $this->cashRegisterUtil->num_uf($request->input('amount'));
            }
            $user_id = $request->session()->get('user.id');
            $business_id = $request->session()->get('user.business_id');

            $register = CashRegister::create([
                'business_id' => $business_id,
                'user_id' => $user_id,
                'status' => 'open',
                'location_id' => $request->input('location_id'),
                'cash_register_information_id' => $request->input('cash_register_information_id'),
                'created_at' => \Carbon::now()->format('Y-m-d H:i:00'),
            ]);
            if (! empty($initial_amount)) {
                $register->cash_register_transactions()->create([
                    'amount' => $initial_amount,
                    'pay_method' => 'cash',
                    'type' => 'credit',
                    'transaction_type' => 'initial',
                ]);
            }
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
        }

        return redirect()->action([\App\Http\Controllers\SellPosController::class, 'create'], ['sub_type' => $sub_type]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CashRegister  $cashRegister
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (! auth()->user()->can('view_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $register_details = $this->cashRegisterUtil->getRegisterDetails($id);
        
        if (!$register_details) {
            abort(404, 'Cash register not found.');
        }
        
        $user_id = $register_details->user_id;
        $open_time = $register_details->open_time;
        $close_time = ! empty($register_details->closed_at) ? $register_details->closed_at : \Carbon::now()->toDateTimeString();
        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time);

        $payment_types = $this->cashRegisterUtil->payment_types(null, false, $business_id);
        $payment_types = $this->cashRegisterUtil->payment_types(null, false, $business_id);
        
        $backendPaymentAmount = TransactionPayment::where('method', 'cash')
            ->where('created_by', $user_id)
            ->whereNull('parent_id')
            ->whereBetween('created_at', [$open_time, $close_time])
            ->where(function ($q) use($id) {
                // Include sell transaction payments OR customer payments
                $q->where(function ($query) {
                    $query->whereHas('transaction', function ($q) {
                        $q->where('type', 'sell');
                    })
                    ->orWhereNotNull('payment_for');
                })
                // Exclude payments already recorded in cash register
                ->where(function ($query) use($id) {
                    $query->whereDoesntHave('transaction', function ($q) use($id) {
                        $q->whereHas('cash_register_payments', function ($q) use($id) {
                            $q->where('cash_register_id', $id);
                        });
                    })
                    ->orWhereNull('transaction_id');
                });
            })
            ->sum('amount');
        
        // Calculate sell return refund amount from modal (all methods) to show in section b)
        $modalSellReturnRefundTotal = TransactionPayment::where('created_by', $user_id)
            ->whereBetween('created_at', [$open_time, $close_time])
            ->whereHas('transaction', function ($q) {
                $q->where('type', 'sell_return');
            })
            ->whereDoesntHave('transaction.cash_register_payments', function ($q) use($id) {
                $q->where('cash_register_id', $id);
            })
            ->sum('amount');

        // Cash-only portion to deduct from cash balance
        $modalCashSellReturnRefund = TransactionPayment::where('method', 'cash')
            ->where('created_by', $user_id)
            ->whereBetween('created_at', [$open_time, $close_time])
            ->whereHas('transaction', function ($q) {
                $q->where('type', 'sell_return');
            })
            ->whereDoesntHave('transaction.cash_register_payments', function ($q) use($id) {
                $q->where('cash_register_id', $id);
            })
            ->sum('amount');
        
        // Refunds by method done from modal (not recorded to register)
        $modalRefundsByMethod = TransactionPayment::selectRaw('method, SUM(amount) as total')
            ->where('created_by', $user_id)
            ->whereBetween('created_at', [$open_time, $close_time])
            ->whereHas('transaction', function ($q) {
                $q->where('type', 'sell_return');
            })
            ->whereDoesntHave('transaction.cash_register_payments', function ($q) use($id) {
                $q->where('cash_register_id', $id);
            })
            ->groupBy('method')
            ->pluck('total', 'method')
            ->toArray();
            
        return view('cash_register.register_details')
                    ->with(compact('register_details', 'details', 'payment_types', 'close_time', 'backendPaymentAmount', 'modalSellReturnRefundTotal', 'modalCashSellReturnRefund', 'modalRefundsByMethod'));
    }

    /**
     * Shows register details modal.
     *
     * @param  void
     * @return \Illuminate\Http\Response
     */
    public function getRegisterDetails()
    {
        if (! auth()->user()->can('view_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $register_details = $this->cashRegisterUtil->getRegisterDetails();
        
        if (!$register_details) {
            abort(404, 'No open cash register found.');
        }

        $user_id = auth()->user()->id;
        $open_time = $register_details->open_time;
        $close_time = \Carbon::now()->toDateTimeString();

        $is_types_of_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time, $is_types_of_service_enabled);

        $payment_types = $this->cashRegisterUtil->payment_types($register_details->location_id, true, $business_id);

        // Calculate sell return refund amount from modal (all methods) to show in section b)
        $modalSellReturnRefundTotal = TransactionPayment::where('created_by', $user_id)
            ->whereBetween('created_at', [$open_time, $close_time])
            ->whereHas('transaction', function ($q) {
                $q->where('type', 'sell_return');
            })
            ->whereDoesntHave('transaction.cash_register_payments', function ($q) use($register_details) {
                $q->where('cash_register_id', $register_details->id);
            })
            ->sum('amount');

        // Cash-only portion to deduct from cash balance
        $modalCashSellReturnRefund = TransactionPayment::where('method', 'cash')
            ->where('created_by', $user_id)
            ->whereBetween('created_at', [$open_time, $close_time])
            ->whereHas('transaction', function ($q) {
                $q->where('type', 'sell_return');
            })
            ->whereDoesntHave('transaction.cash_register_payments', function ($q) use($register_details) {
                $q->where('cash_register_id', $register_details->id);
            })
            ->sum('amount');

        // Refunds by method done from modal (not recorded to register)
        $modalRefundsByMethod = TransactionPayment::selectRaw('method, SUM(amount) as total')
            ->where('created_by', $user_id)
            ->whereBetween('created_at', [$open_time, $close_time])
            ->whereHas('transaction', function ($q) {
                $q->where('type', 'sell_return');
            })
            ->whereDoesntHave('transaction.cash_register_payments', function ($q) use($register_details) {
                $q->where('cash_register_id', $register_details->id);
            })
            ->groupBy('method')
            ->pluck('total', 'method')
            ->toArray();

        return view('cash_register.register_details')
                ->with(compact('register_details', 'details', 'payment_types', 'close_time', 'modalSellReturnRefundTotal', 'modalCashSellReturnRefund', 'modalRefundsByMethod'));
    }

    /**
     * Shows close register form.
     *
     * @param  void
     * @return \Illuminate\Http\Response
     */
    public function getCloseRegister($id = null)
    {
        if (! auth()->user()->can('close_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $register_details = $this->cashRegisterUtil->getRegisterDetails($id);
        
        if (!$register_details) {
            abort(404, 'Cash register not found.');
        }

        $user_id = $register_details->user_id;
        $open_time = $register_details->open_time;
        $close_time = \Carbon::now()->toDateTimeString();

        $is_types_of_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time, $is_types_of_service_enabled);

        $payment_types = $this->cashRegisterUtil->payment_types($register_details->location_id, true, $business_id);

        $pos_settings = ! empty(request()->session()->get('business.pos_settings')) ? json_decode(request()->session()->get('business.pos_settings'), true) : [];
        
        $backendPaymentAmount = TransactionPayment::where('method', 'cash')
            ->where('created_by', $user_id)
            ->whereNull('parent_id')
            ->whereBetween('created_at', [$open_time, $close_time])
            ->where(function ($q) use($id) {
                // Include sell transaction payments OR customer payments
                $q->where(function ($query) {
                    $query->whereHas('transaction', function ($q) {
                        $q->where('type', 'sell');
                    })
                    ->orWhereNotNull('payment_for');
                })
                // Exclude payments already recorded in cash register
                ->where(function ($query) use($id) {
                    $query->whereDoesntHave('transaction', function ($q) use($id) {
                        $q->whereHas('cash_register_payments', function ($q) use($id) {
                            $q->where('cash_register_id', $id);
                        });
                    })
                    ->orWhereNull('transaction_id');
                });
            })
            ->sum('amount');
        
        // Calculate sell return refund amount from modal (all methods) to show in section b)
        $modalSellReturnRefundTotal = TransactionPayment::where('created_by', $user_id)
            ->whereBetween('created_at', [$open_time, $close_time])
            ->whereHas('transaction', function ($q) {
                $q->where('type', 'sell_return');
            })
            ->whereDoesntHave('transaction.cash_register_payments', function ($q) use($id) {
                $q->where('cash_register_id', $id);
            })
            ->sum('amount');

        // Cash-only portion to deduct from cash balance
        $modalCashSellReturnRefund = TransactionPayment::where('method', 'cash')
            ->where('created_by', $user_id)
            ->whereBetween('created_at', [$open_time, $close_time])
            ->whereHas('transaction', function ($q) {
                $q->where('type', 'sell_return');
            })
            ->whereDoesntHave('transaction.cash_register_payments', function ($q) use($id) {
                $q->where('cash_register_id', $id);
            })
            ->sum('amount');
        
        // Refunds by method done from modal (not recorded to register)
        $modalRefundsByMethod = TransactionPayment::selectRaw('method, SUM(amount) as total')
            ->where('created_by', $user_id)
            ->whereBetween('created_at', [$open_time, $close_time])
            ->whereHas('transaction', function ($q) {
                $q->where('type', 'sell_return');
            })
            ->whereDoesntHave('transaction.cash_register_payments', function ($q) use($id) {
                $q->where('cash_register_id', $id);
            })
            ->groupBy('method')
            ->pluck('total', 'method')
            ->toArray();
            
        return view('cash_register.close_register_modal')
                    ->with(compact('register_details', 'details', 'payment_types', 'pos_settings', 'backendPaymentAmount', 'modalSellReturnRefundTotal', 'modalCashSellReturnRefund', 'modalRefundsByMethod'));
    }

    /**
     * Closes currently opened register.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postCloseRegister(Request $request)
    {
        if (! auth()->user()->can('close_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            //Disable in demo
            if (config('app.env') == 'demo') {
                $output = ['success' => 0,
                    'msg' => 'Feature disabled in demo!!',
                ];

                return redirect()->action([\App\Http\Controllers\HomeController::class, 'index'])->with('status', $output);
            }

            $input = $request->only(['closing_amount', 'total_card_slips', 'total_cheques', 'closing_note']);
            $input['closing_amount'] = $this->cashRegisterUtil->num_uf($input['closing_amount']);
            $user_id = $request->input('user_id');
            $input['closed_at'] = \Carbon::now()->format('Y-m-d H:i:s');
            $input['status'] = 'close';
            $input['denominations'] = ! empty(request()->input('denominations')) ? json_encode(request()->input('denominations')) : null;

            CashRegister::where('user_id', $user_id)
                                ->where('status', 'open')
                                ->update($input);
            $output = ['success' => 1,
                'msg' => __('cash_register.close_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with('status', $output);
    }
}
