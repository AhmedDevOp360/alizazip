<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\CashRegister;
use App\CashWithdrawal;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CashWithdrawalController extends Controller
{
    protected $transactionUtil;

    public function __construct(TransactionUtil $transactionUtil)
    {
        $this->transactionUtil = $transactionUtil;
    }
    public function index(Request $request)
    {
        if (!auth()->user()->can('close_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            $business_id = $request->session()->get('user.business_id');

            $withdrawals = CashWithdrawal::leftJoin('business_locations', 'cash_withdrawals.location_id', '=', 'business_locations.id')
                ->leftJoin('users', 'cash_withdrawals.user_id', '=', 'users.id')
                ->select(
                    'cash_withdrawals.*',
                    'business_locations.name as location_name',
                    'users.username as user_name'
                )
                ->where('cash_withdrawals.business_id', $business_id);

            if ($request->filled('location_id')) {
                $withdrawals->where('location_id', $request->location_id);
            }

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $withdrawals->whereDate('cash_withdrawals.created_at', '>=', $request->start_date)
                    ->whereDate('cash_withdrawals.created_at', '<=', $request->end_date);
            }
            $withdrawals->orderBy('cash_withdrawals.created_at', 'desc');


            return DataTables::of($withdrawals)
                ->addColumn('action', function ($row) {

                    $html = '<div class="btn-group">
                                <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info tw-w-max dropdown-toggle" 
                                    data-toggle="dropdown" aria-expanded="false">
                                    Actions
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-left" role="menu">
                            ';

                    // 👉 EDIT option
                    $html .= '<li><a href="' . action([\App\Http\Controllers\CashWithdrawalController::class, 'edit'], [$row->id]) . '">
                                    <i class="fas fa-edit" style="font-size:20px;color:chocolate"></i> &nbsp; Edit
                                </a></li>';

                    // 👉 DELETE option
                    $html .= '<li><a href="' . action([\App\Http\Controllers\CashWithdrawalController::class, 'destroy'], [$row->id]) . '"
                                    class="delete-cashwithdrawal">
                                    <i class="fas fa-trash" style="font-size:20px;color:red"></i> &nbsp; Delete
                                </a></li>';

                    $html .= '</ul></div>';

                    return $html;
                })

                ->editColumn('amount', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true">' . $this->transactionUtil->num_f($row->amount) . '</span>';
                })
                ->editColumn('created_at', '{{ @format_datetime($created_at) }}')
                ->addColumn('location', function ($row) {
                    return $row->location_name;
                })
                ->addColumn('user', function ($row) {
                    return $row->user_name;
                })
                ->rawColumns(['amount', 'action'])
                ->make(true);
        }

        $business_id = $request->session()->get('user.business_id');
        $locations = BusinessLocation::forDropdown($business_id);

        return view('cash_withdrawal.index')->with(compact('locations'));
    }

    public function create(Request $request)
    {
        if (!auth()->user()->can('close_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $locations = BusinessLocation::forDropdown($business_id);

        if (request()->ajax()) {
            return view('cash_withdrawal.create_modal')->with(compact('locations'));
        }

        return view('cash_withdrawal.create')->with(compact('locations'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('close_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'location_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string',
        ]);

        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $cashRegister = CashRegister::where('user_id', $user_id)->where('status', 'open')->whereNull('closed_at')->latest()->first();
        CashWithdrawal::create([
            'business_id' => $business_id,
            'location_id' => $request->location_id,
            'user_id' => $user_id,
            'cash_register_id' => $cashRegister->id ?? null,
            'amount' => $request->amount,
            'note' => $request->note,
        ]);

        $output = [
            'success' => 1,
            'msg' => __('lang_v1.added_success'),
        ];
        
        if (request()->ajax()) {
            return $output;
        }
        return redirect()->action([self::class, 'index'])->with('status', $output);
    }

    public function edit($id)
    {
        if (!auth()->user()->can('close_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $withdrawal = CashWithdrawal::where('business_id', $business_id)->findOrFail($id);
        $locations = BusinessLocation::forDropdown($business_id);

        return view('cash_withdrawal.edit')->with(compact('withdrawal', 'locations'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('close_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'location_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string',
        ]);

        $business_id = $request->session()->get('user.business_id');

        $withdrawal = CashWithdrawal::where('business_id', $business_id)->findOrFail($id);

        $withdrawal->update([
            'location_id' => $request->location_id,
            'amount' => $request->amount,
            'note' => $request->note,
        ]);

        $output = [
            'success' => 1,
            'msg' => __('lang_v1.updated_success'),
        ];

        return redirect()->action([self::class, 'index'])->with('status', $output);
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('close_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        if (!request()->ajax()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $withdrawal = CashWithdrawal::where('business_id', $business_id)->findOrFail($id);
            $withdrawal->delete();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
