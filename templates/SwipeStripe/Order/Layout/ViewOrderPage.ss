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
        <% include SwipeStripe/Order/OrderDetailsSummary %>
    </div>

    <div class="row">
        <div class="col-md-12">
            <% if $Order.CustomerVisibleOrderStatusUpdates %>
                <h3><%t SwipeStripe\\Order\\Layout\\ViewOrderPage.UPDATES 'Updates' %></h3>

                <table class="table">
                    <thead>
                    <tr>
                        <td><%t SwipeStripe\\Order\\Layout\\ViewOrderPage.TIME 'Time' %></td>
                        <td><%t SwipeStripe\\Order\\Layout\\ViewOrderPage.STATUS 'Status' %></td>
                        <td><%t SwipeStripe\\Order\\Layout\\ViewOrderPage.NOTES 'Notes' %>/td>
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
