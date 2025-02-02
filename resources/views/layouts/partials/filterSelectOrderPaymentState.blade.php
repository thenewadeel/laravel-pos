<select name="payment_state" id="payment_state" class="form-control p-0 m-0 w-auto">
    <option value="">{{ __('order.Payment_Status') }}</option>
    <option {{ request('payment_state') == 'open' ? 'selected' : '' }} value="open">
        {{ __('order.Open') }}</option>
    <option {{ request('payment_state') == 'closed' ? 'selected' : '' }} value="closed">
        {{ __('order.Closed') }}</option>
    <option {{ request('payment_state') == 'paid' ? 'selected' : '' }} value="paid">
        {{ __('order.Paid') }}</option>
    <option {{ request('payment_state') == 'chit' ? 'selected' : '' }} value="chit">
        {{ __('order.Chit') }}</option>
    <option {{ request('payment_state') == 'part-chit' ? 'selected' : '' }} value="part-chit">
        {{ __('order.Part_Chit') }}</option>
</select>
