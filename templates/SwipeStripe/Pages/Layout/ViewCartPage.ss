{$CartForm}

<% include SwipeStripe/CartTotalSummary Cart=$SwipeStripe.ActiveCart %>

<% if not $SwipeStripe.ActiveCart.Empty %>
    <a href="{$Top.CheckoutLink}">Checkout</a>
<% end_if %>
