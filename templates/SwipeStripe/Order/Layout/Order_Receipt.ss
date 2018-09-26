<div class="container">
    <div class="row">
        <div class="page-header col-md-12">
            <h1>Order #{$ID} <small>Receipt</small></h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <h3>Details</h3>
            <p>{$ConfirmationTime.Nice}</p>
            <p>{$CustomerName}</p>
            <p>{$CustomerEmail}</p>
        </div>
        <div class="col-md-6">
            <h3>Billing Address</h3>
            <address>{$BillingAddress.Nice}</address>
        </div>
    </div>
    <hr>

    <div class="row">
        <div class="col-md-8">
            <% include SwipeStripe/Order/CheckoutSummary Cart=$Me %>
        </div>

        <div class="col-md-4">
            <% include SwipeStripe/Order/CartTotalSummary Cart=$Me %>

            <% include SwipeStripe/Order/PaymentSummary Order=$Me %>
        </div>
    </div>
</div>
