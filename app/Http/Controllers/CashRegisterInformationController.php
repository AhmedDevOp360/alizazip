<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BusinessLocation;
use App\CashRegisterInformation;
use App\InvoiceLayout;
use App\InvoiceScheme;
use App\SellingPriceGroup;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class CashRegisterInformationController extends Controller
{
    protected $moduleUtil;

    protected $commonUtil;

    /**
     * Constructor
     *
     * @param  ModuleUtil  $moduleUtil
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil, Util $commonUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }
        $cash_infos = [];
        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $cash_infos = CashRegisterInformation::where('cash_register_information.business_id', $business_id)
            ->leftjoin(
                'business as bus',
                'cash_register_information.business_id',
                '=',
                'bus.id'
            )
            ->leftjoin(
                'business_locations as loc',
                'cash_register_information.location_id',
                '=',
                'loc.id'
            )
            ->select([
                'cash_register_information.id',
                'cash_register_information.sales_code',
                'cash_register_information.cash_type',
                'cash_register_information.plate_number',
                
                'bus.name as business_name',
                'loc.name as location_name',
                'cash_register_information.created_at',
            ]);

            // $permitted_locations = auth()->user()->permitted_locations();
            // if ($permitted_locations != 'all') {
            //     $locations->whereIn('business_locations.id', $permitted_locations);
            // }
            // dd($cash_infos);

            return Datatables::of($cash_infos)
                ->addColumn(
                    'action',
                    '<button type="button" data-href="{{action(\'App\Http\Controllers\CashRegisterInformationController@edit\', [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary btn-modal" data-container=".cash_register_information_edit_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>'
                )
                ->removeColumn('id')
                // ->removeColumn('is_active')
                ->rawColumns([6])
                ->make(false);
        }
        // dd($cash_infos);
        return view('cash_register_information.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for location quota
        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (! $this->moduleUtil->isQuotaAvailable('locations', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('locations', $business_id);
        }

        $business_location = BusinessLocation::where('business_id', $business_id)
                            ->get()
                            ->pluck('name', 'id');

        return view('cash_register_information.create')
                    ->with(compact(
                        'business_location'
                    ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            //Check if subscribed or not, then check for location quota
            // if (! $this->moduleUtil->isSubscribed($business_id)) {
            //     return $this->moduleUtil->expiredResponse();
            // } elseif (! $this->moduleUtil->isQuotaAvailable('locations', $business_id)) {
            //     return $this->moduleUtil->quotaExpiredResponse('locations', $business_id);
            // }

            $input = $request->only(['cash_type', 'sales_code', 'location_id', 'plate_number', 'business_id']);

            $input['business_id'] = $business_id;

            

            $cashRegisterInformation = CashRegisterInformation::create($input);

            //Create a new permission related to the created location
            Permission::create(['name' => 'cash_register_information.'.$cashRegisterInformation->id]);

            $output = ['success' => true,
                'msg' => 'Caja Registradora creada con exito',
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => 'Error al intentar crear la Caja Registradora',
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\StoreFront  $storeFront
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\StoreFront  $storeFront
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $cash_register_information = CashRegisterInformation::where('business_id', $business_id)
                                    ->find($id);
        $business_location = BusinessLocation::where('business_id', $business_id)
        ->get()
        ->pluck('name', 'id');

       

        return view('cash_register_information.edit')
                ->with(compact(
                    'business_location',
                    'cash_register_information'
                ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\StoreFront  $storeFront
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['cash_type', 'sales_code', 'location_id', 'plate_number', 'business_id']);

            $business_id = $request->session()->get('user.business_id');

            $input['business_id'] = $business_id;

            CashRegisterInformation::where('business_id', $business_id)
                            ->where('id', $id)
                            ->update($input);

            $output = ['success' => true,
                'msg' => 'Caja registradora actualizada con exito',
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => 'Error al actualizar la caja registradora',
            ];
        }

        return $output;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\StoreFront  $storeFront
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Checks if the given location id already exist for the current business.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function checkLocationId(Request $request)
    // {
    //     $location_id = $request->input('location_id');

    //     $valid = 'true';
    //     if (! empty($location_id)) {
    //         $business_id = $request->session()->get('user.business_id');
    //         $hidden_id = $request->input('hidden_id');

    //         $query = BusinessLocation::where('business_id', $business_id)
    //                         ->where('location_id', $location_id);
    //         if (! empty($hidden_id)) {
    //             $query->where('id', '!=', $hidden_id);
    //         }
    //         $count = $query->count();
    //         if ($count > 0) {
    //             $valid = 'false';
    //         }
    //     }
    //     echo $valid;
    //     exit;
    // }

    /**
     * Function to activate or deactivate a location.
     *
     * @param  int  $location_id
     * @return json
     */
    // public function activateDeactivateLocation($location_id)
    // {
    //     if (! auth()->user()->can('business_settings.access')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     try {
    //         $business_id = request()->session()->get('user.business_id');

    //         $business_location = BusinessLocation::where('business_id', $business_id)
    //                         ->findOrFail($location_id);

    //         $business_location->is_active = ! $business_location->is_active;
    //         $business_location->save();

    //         $msg = $business_location->is_active ? __('lang_v1.business_location_activated_successfully') : __('lang_v1.business_location_deactivated_successfully');

    //         $output = ['success' => true,
    //             'msg' => $msg,
    //         ];
    //     } catch (\Exception $e) {
    //         \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

    //         $output = ['success' => false,
    //             'msg' => __('messages.something_went_wrong'),
    //         ];
    //     }

    //     return $output;
    // }
}
