<div class="container">
    <div class="row">
        <section class="col-md-10 col-md-offset-1">
            <div class="page-header">
                {$Breadcrumbs}
                <h1>{$Title}</h1>
            </div>
        </section>
    </div>
    <div class="row">
        <% with $CheckoutForm %>
            <% if $PaymentError %>
                <h3>{$PaymentError}</h3>
            <% end_if %>

            {$Me}
        <% end_with %>

        <% with $SwipeStripe.ActiveCart %>
            <a href="{$Link}"><%t SwipeStripe\\Forms\\CheckoutForm.CANCEL_CHECKOUT 'Return to cart' %></a>
        <% end_with %>
    </div>
</div>
