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
        <% if $SwipeStripe.ActiveCart.Empty %>
            <h3>Your cart is empty.</h3>
        <% else %>
            {$CartForm}
        <% end_if %>

        <% include SwipeStripe/CartTotalSummary Cart=$SwipeStripe.ActiveCart %>

        <% if not $SwipeStripe.ActiveCart.Empty %>
            <a href="{$Top.CheckoutLink}">Checkout</a>
        <% end_if %>
    </div>
</div>
