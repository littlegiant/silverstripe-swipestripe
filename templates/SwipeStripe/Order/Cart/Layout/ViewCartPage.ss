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
            <h3><%t SwipeStripe\\Order\\Cart\\Layout\\ViewCartPage.CART_EMPTY 'Your cart is empty.' %></h3>
        <% else %>
            {$CartForm}
            <a href="{$Top.CheckoutLink}"><%t SwipeStripe\\Order\\Cart\\Layout\\ViewCartPage.CHECKOUT 'Checkout' %></a>
        <% end_if %>
    </div>
</div>
