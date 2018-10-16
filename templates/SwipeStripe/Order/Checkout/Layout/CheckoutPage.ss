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
        <% if $CheckoutForm.PaymentError %>
            <div class="alert alert-warning" role="alert">{$CheckoutForm.PaymentError}</div>
        <% end_if %>

        <div class="col-md-8">
            <% include SwipeStripe/Order/CheckoutSummary Cart=$SwipeStripe.ActiveCart %>
        </div>

        <div class="col-md-4">
            <% include SwipeStripe/Order/CartTotalSummary Cart=$SwipeStripe.ActiveCart %>

            {$CheckoutForm}

            <% with $SwipeStripe.ActiveCart %>
                <a href="{$Link}"><%t SwipeStripe\\Forms\\CheckoutForm.CANCEL_CHECKOUT 'Return to cart' %></a>
            <% end_with %>
        </div>
    </div>
</div>
