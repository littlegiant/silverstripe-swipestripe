<% include SwipeStripe/CartTotalSummary Cart=$SwipeStripe.ActiveCart %>

<% with $CheckoutForm %>
    <% if $PaymentError %>
        <h3>{$PaymentError}</h3>
    <% end_if %>

    {$Me}
<% end_with %>

<% with $SwipeStripe.ActiveCart %>
    <a href="{$Link}"><%t SwipeStripe\\Forms\\CheckoutForm.CANCEL_CHECKOUT 'Return to cart' %></a>
<% end_with %>
