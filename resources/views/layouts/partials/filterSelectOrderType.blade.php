<select name="type" id="type" class="form-control p-0 m-0 w-auto">
    <option value="">{{ __('order.Type') }}</option>
    <option {{ request('type') == 'dine-in' ? 'selected' : '' }} value="dine-in">
        {{ __('order.Dine_In') }}</option>
    <option {{ request('type') == 'take-away' ? 'selected' : '' }} value="take-away">
        {{ __('order.Take_Away') }}</option>
    <option {{ request('type') == 'delivery' ? 'selected' : '' }} value="delivery">
        {{ __('order.Delivery') }}</option>
</select>
