
@if(empty($is_admin))
    <h3>@lang('business.business')</h3>
@endif
{!! Form::hidden('language', request()->lang); !!}

<fieldset>
<legend class="text-teal">@lang('business.business_details'):</legend>
<div class="col-md-5">
    <div class="form-group">
        {!! Form::label('name', __('business.business_name') . ':*' ) !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-suitcase"></i>
            </span>
            {!! Form::text('name', null, ['class' => 'form-control','placeholder' => __('business.business_name'), 'required']); !!}
        </div>
    </div>
</div>
<div class="col-sm-3">
    <div class="form-group">
        {!! Form::label('type_document_identification_id', __('Tipo de documento') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fas fa-money-bill-alt"></i>
            </span>
            {!! Form::select('type_document_identification_id', $type_document_identifications, null, ['class' => 'form-control','placeholder' => 'Tipo de documento', 'required']); !!}
        </div>
    </div>
</div>  
<div class="col-sm-3">
    <div class="form-group">
        <label for="nit">Numero de Identificacion:*</label>
        <i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
            data-container="body" data-toggle="popover" data-placement="auto bottom" 
            data-content="Digite el Numero de Identificacion, Persona natural o Juridica" data-html="true" data-trigger="hover"></i>                <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-card"></i>
            </span>
            {!! Form::text('nit', null, ['class' => 'form-control','id'=>'nit']); !!}
        </div>
    </div>
</div>
<div class="col-sm-1">
    <div class="form-group">
        <label for="dv">DV</label>
        <div class="input-group">
            {!! Form::text('dv', null, ['class' => 'form-control','id'=>'dv']); !!}
        </div>
    </div>
</div>
<div class="col-sm-2">
    <div class="form-group">
        <label for="merchant_registration">Registro Mercantil</label>
        <div class="input-group">
            {!! Form::text('merchant_registration', null, ['class' => 'form-control']); !!}
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('type_organization_id', __('Tipo de organización') . ':') !!}
        <div class="input-group">
            
            {!! Form::select('type_organization_id', $type_organizations, null, ['class' => 'form-control','placeholder' => 'Tipo de documento']); !!}
        </div>
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('type_regime_id', __('Régimenes') . ':') !!}
        <div class="input-group">
            
            {!! Form::select('type_regime_id', $type_regimes, null, ['class' => 'form-control']); !!}
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('type_liability_id', __('Tipo de responsabilidad') . ':') !!}
        <div class="input-group">
            
            {!! Form::select('type_liability_id', $type_liabilities, null, ['class' => 'form-control']); !!}
        </div>
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('department_id', __('Departamentos') . ':') !!}
        <div class="input-group">
            
            {!! Form::select('department_id', $departments, null, ['class' => 'form-control','id'=>'department_id']); !!}
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('municipality_id', __('Municipio') . ':') !!}
        {!! Form::select('municipality_id', $municipalities, null, ['class' => 'form-control']); !!}
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
    {!! Form::label('start_date', __('business.start_date') . ':') !!}
    <div class="input-group">
        <span class="input-group-addon">
            <i class="fa fa-calendar"></i>
        </span>
        {!! Form::text('start_date', null, ['class' => 'form-control start-date-picker','placeholder' => __('business.start_date'), 'readonly']); !!}
    </div>
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
    {!! Form::label('currency_id', __('business.currency') . ':*') !!}
    <div class="input-group">
        <span class="input-group-addon">
            <i class="fas fa-money-bill-alt"></i>
        </span>
        {!! Form::select('currency_id', $currencies, '', ['class' => 'form-control select2_register','placeholder' => __('business.currency_placeholder'), 'required']); !!}
    </div>
    </div>
</div>
<div class="clearfix"></div>
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('business_logo', __('business.upload_logo') . ':') !!}
        {!! Form::file('business_logo', ['accept' => 'image/*']); !!}
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('website', __('lang_v1.website') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-globe"></i>
            </span>
            {!! Form::text('website', null, ['class' => 'form-control','placeholder' => __('lang_v1.website')]); !!}
        </div>
    </div>
</div>
{{-- <div class="clearfix"></div> --}}
<div class="col-md-3">
    <div class="form-group">
    {!! Form::label('mobile', __('lang_v1.business_telephone') . ':') !!}
    <div class="input-group">
        <span class="input-group-addon">
            <i class="fa fa-phone"></i>
        </span>
        {!! Form::text('mobile', null, ['class' => 'form-control','placeholder' => __('lang_v1.business_telephone')]); !!}
    </div>
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('alternate_number', __('business.alternate_number') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-phone"></i>
            </span>
            {!! Form::text('alternate_number', null, ['class' => 'form-control','placeholder' => __('business.alternate_number')]); !!}
        </div>
    </div>
</div>

<div class="clearfix"></div>

{{-- <div class="col-md-4">
    <div class="form-group">
    {!! Form::label('country', __('business.country') . ':*') !!}
    <div class="input-group">
        <span class="input-group-addon">
            <i class="fa fa-globe"></i>
        </span>
        {!! Form::text('country', null, ['class' => 'form-control','placeholder' => __('business.country'), 'required']); !!}
    </div>
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
    {!! Form::label('state',__('business.state') . ':*') !!}
    <div class="input-group">
        <span class="input-group-addon">
            <i class="fa fa-map-marker"></i>
        </span>
        {!! Form::text('state', null, ['class' => 'form-control','placeholder' => __('business.state'), 'required']); !!}
    </div>
    </div>
</div>
<div class="clearfix"></div>
<div class="col-md-6">
    <div class="form-group">
    {!! Form::label('city',__('business.city'). ':*') !!}
    <div class="input-group">
        <span class="input-group-addon">
            <i class="fa fa-map-marker"></i>
        </span>
        {!! Form::text('city', null, ['class' => 'form-control','placeholder' => __('business.city'), 'required']); !!}
    </div>
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
    {!! Form::label('zip_code', __('business.zip_code') . ':*') !!}
    <div class="input-group">
        <span class="input-group-addon">
            <i class="fa fa-map-marker"></i>
        </span>
        {!! Form::text('zip_code', null, ['class' => 'form-control','placeholder' => __('business.zip_code_placeholder'), 'required']); !!}
    </div>
    </div>
</div> --}}
<div class="clearfix"></div>
<div class="col-md-4">
    <div class="form-group">
    {!! Form::label('landmark', __('business.landmark') . ':*') !!}
    <div class="input-group">
        <span class="input-group-addon">
            <i class="fa fa-map-marker"></i>
        </span>
        {!! Form::text('landmark', null, ['class' => 'form-control','placeholder' => __('business.landmark'), 'required']); !!}
    </div>
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('time_zone', __('business.time_zone') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fas fa-clock"></i>
            </span>
            {!! Form::select('time_zone', $timezone_list, config('app.timezone'), ['class' => 'form-control select2_register','placeholder' => __('business.time_zone'), 'required']); !!}
        </div>
    </div>
</div>
</fieldset>

<!-- tax details -->
@if(empty($is_admin))
    <h3>@lang('business.business_settings')</h3>

    <fieldset>
    <legend class="text-teal">@lang('business.business_settings'):</legend>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('tax_label_1', __('business.tax_1_name') . ':') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('tax_label_1', null, ['class' => 'form-control','placeholder' => __('business.tax_1_placeholder')]); !!}
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('tax_number_1', __('business.tax_1_no') . ':') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('tax_number_1', null, ['class' => 'form-control']); !!}
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('tax_label_2',__('business.tax_2_name') . ':') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('tax_label_2', null, ['class' => 'form-control','placeholder' => __('business.tax_1_placeholder')]); !!}
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('tax_number_2',__('business.tax_2_no') . ':') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('tax_number_2', null, ['class' => 'form-control',]); !!}
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('fy_start_month', __('business.fy_start_month') . ':*') !!} @show_tooltip(__('tooltip.fy_start_month'))
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                </span>
                {!! Form::select('fy_start_month', $months, null, ['class' => 'form-control select2_register', 'required', 'style' => 'width:100%;']); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('accounting_method', __('business.accounting_method') . ':*') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-calculator"></i>
                </span>
                {!! Form::select('accounting_method', $accounting_methods, null, ['class' => 'form-control select2_register', 'required', 'style' => 'width:100%;']); !!}
            </div>
        </div>
    </div>
    </fieldset>
@endif

<!-- Owner Information -->
@if(empty($is_admin))
    <h3>@lang('business.owner')</h3>
@endif

<fieldset>
<legend class="text-teal">@lang('business.owner_info')</legend>
{{-- <div class="col-md-4">
    <div class="form-group">
        {!! Form::label('surname', __('business.prefix') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-info"></i>
            </span>
            {!! Form::text('surname', null, ['class' => 'form-control','placeholder' => __('business.prefix_placeholder')]); !!}
        </div>
    </div>
</div> --}}
{!! Form::hidden('surname', null, ['class' => 'form-control','placeholder' => __('business.prefix_placeholder')]); !!}
<div class="col-md-4">
    <div class="form-group">
        {!! Form::label('first_name', __('business.first_name') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-info"></i>
            </span>
            {!! Form::text('first_name', null, ['class' => 'form-control','placeholder' => __('business.first_name'), 'required']); !!}
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        {!! Form::label('last_name', __('business.last_name') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-info"></i>
            </span>
            {!! Form::text('last_name', null, ['class' => 'form-control','placeholder' =>  __('business.last_name')]); !!}
        </div>
    </div>
</div>
{{-- <div class="clearfix"></div> --}}
<div class="col-md-4">
    <div class="form-group">
        {!! Form::label('username', __('business.username') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-user"></i>
            </span>
            {!! Form::text('username', null, ['class' => 'form-control','placeholder' => __('business.username'), 'required']); !!}
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        {!! Form::label('email', __('business.email') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-envelope"></i>
            </span>
            {!! Form::text('email', null, ['class' => 'form-control','placeholder' => __('business.email'), 'required']); !!}
        </div>
    </div>
</div>
{{-- <div class="clearfix"></div> --}}
<div class="col-md-4">
    <div class="form-group">
        {!! Form::label('password', __('business.password') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-lock"></i>
            </span>
            {!! Form::password('password', ['class' => 'form-control','placeholder' => __('business.password'), 'required']); !!}
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        {!! Form::label('confirm_password', __('business.confirm_password') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-lock"></i>
            </span>
            {!! Form::password('confirm_password', ['class' => 'form-control','placeholder' => __('business.confirm_password'), 'required']); !!}
        </div>
    </div>
</div>
<div class="clearfix"></div>
<div class="col-md-6">
    @if(!empty($system_settings['superadmin_enable_register_tc']))
        <div class="form-group">
            <label>
                {!! Form::checkbox('accept_tc', 0, false, ['required', 'class' => 'input-icheck']); !!}
                <u><a class="terms_condition cursor-pointer" data-toggle="modal" data-target="#tc_modal">
                    @lang('lang_v1.accept_terms_and_conditions') <i></i>
                </a></u>
            </label>
        </div>
        @include('business.partials.terms_conditions')
    @endif
</div>
<div class="clearfix"></div>
</fieldset>