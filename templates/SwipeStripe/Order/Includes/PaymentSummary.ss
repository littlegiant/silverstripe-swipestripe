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
                <div class="col-sm-4"><strong>{$Money.Nice}</strong></div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <strong>Ref:</strong> {$TransactionReference}
                </div>
            </div>
            <hr>
        </div>
    <% end_if %>
<% end_loop %>
