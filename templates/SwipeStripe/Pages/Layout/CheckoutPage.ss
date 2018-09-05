<% include SwipeStripe/CartTotalSummary Cart=$SwipeStripe.ActiveCart %>

<% with $CheckoutForm %>
    <% if $HasPaymentError %>
        <h3><%t SwipeStripe\\Forms\\CheckoutForm.PAYMENT_ERROR 'Sorry, it looks like there was an error processing your payment.' %></h3>
    <% end_if %>

    {$Me}
<% end_with %>

<% with $SwipeStripe.ActiveCart %>
    <a href="{$Link}"><%t SwipeStripe\\Forms\\CheckoutForm.CANCEL_CHECKOUT 'Return to cart' %></a>
<% end_with %>
