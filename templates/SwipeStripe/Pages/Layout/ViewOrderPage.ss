<div class="container">
    <div class="row">
        <section class="col-md-10 col-md-offset-1">
            <div class="page-header">
                {$Breadcrumbs}
                <h1>{$Title} - Order #{$Order.ID}</h1>
            </div>
        </section>
    </div>

    <div class="row">
        <% with $Order %>
            <div class="col-md-4">
                <h3>Details</h3>
                <p>{$ConfirmationTime.Nice}</p>
                <p>{$CustomerName}</p>
                <p>{$CustomerEmail}</p>
            </div>
            <div class="col-md-8">
                <h3>Billing Address</h3>
                <address>{$BillingAddress.Nice}</address>
            </div>
        <% end_with %>
    </div>

    <div class="row">
        <div class="col-md-8">
            <% include SwipeStripe/Order/CheckoutSummary Cart=$Order %>
        </div>

        <div class="col-md-4">
            <% include SwipeStripe/Order/CartTotalSummary Cart=$Order %>

            <% include SwipeStripe/Order/PaymentSummary %>
        </div>
    </div>
</div>
