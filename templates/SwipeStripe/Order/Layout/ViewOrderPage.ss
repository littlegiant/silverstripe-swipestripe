<div class="container">
    <div class="row">
        <section class="col-md-10 col-md-offset-1">
            <div class="page-header">
                {$Breadcrumbs}
                <h1>{$Title} - {$Order.Title}</h1>
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
            <div class="col-md-4">
                <h3>Billing Address</h3>
                <address>{$BillingAddress.Nice}</address>
            </div>
        <% end_with %>

        <div class="col-md-4">
            <h3>Receipt & Invoice</h3>
            <a href="{$Link($Order.ID)}/receipt">Download receipt (PDF)</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <% if $Order.CustomerVisibleOrderStatusUpdates %>
                <h3>Updates</h3>

                <table class="table">
                    <thead>
                    <tr>
                        <td>Time</td>
                        <td>Status</td>
                        <td>Notes</td>
                    </tr>

                    <tr>

                    </tr>
                    </thead>

                    <% loop $Order.CustomerVisibleOrderStatusUpdates %>
                        <tr>
                            <td>{$Created.Nice}</td>
                            <td>{$Status}</td>
                            <td>{$Message}</td>
                        </tr>
                    <% end_loop %>
                </table>
            <% end_if %>
        </div>
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
