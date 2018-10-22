<div class="container">
    <div class="row">
        <div class="page-header col-md-12">
            <h1>{$Title} <small>Receipt</small></h1>
        </div>
    </div>

    <div class="row">
        <% include SwipeStripe/Order/OrderDetailsSummary_Receipt %>
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
