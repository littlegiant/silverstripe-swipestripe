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
        <div class="col-md-8">
            <% include SwipeStripe/CheckoutSummary Cart=$Order %>
        </div>

        <div class="col-md-4">
            <% include SwipeStripe/CartTotalSummary Cart=$Order %>

            <h4>Payments</h4>

            <% loop $Order.Payments %>
                <% if $SwipeStripe.DisplayStatus($Status) %>
                    <div>
                        <h5>
                            {$SwipeStripe.DisplayGateway($Gateway)}
                            <small>&mdash; {$SwipeStripe.DisplayStatus($Status)}</small>
                        </h5>
                        <div class="row">
                            <div class="col-sm-8">{$Created.Nice}</div>
                            <div class="col-sm-4">{$Money.Nice}</div>
                        </div>
                        <hr>
                    </div>
                <% end_if %>
            <% end_loop %>
        </div>
    </div>
</div>
