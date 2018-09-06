<% if $SwipeStripe.ActiveCart.Empty %>
    <h3>Your cart is empty.</h3>
<% else %>
    {$CartForm}
<% end_if %>

<% include SwipeStripe/CartTotalSummary Cart=$SwipeStripe.ActiveCart %>

<% if not $SwipeStripe.ActiveCart.Empty %>
    <a href="{$Top.CheckoutLink}">Checkout</a>
<% end_if %>
