<div class="btn-group">
    <button type="button" class="btn btn-xs btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        @lang('messages.actions') <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-left" role="menu">
        <li>
            <a href="{{ $edit_url }}"><i class="glyphicon glyphicon-edit"></i> @lang('messages.edit')</a>
        </li>
        <li>
            <a href="#" data-href="{{ $delete_url }}" class="delete_cash_withdrawal"><i class="glyphicon glyphicon-trash"></i> @lang('messages.delete')</a>
        </li>
    </ul>
</div>


