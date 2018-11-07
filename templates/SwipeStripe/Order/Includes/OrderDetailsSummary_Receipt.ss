<div class="col-md-6">
    <h3><%t SwipeStripe\\Order\\Includes\\OrderDetailsSummary_Receipt.DETAILS 'Details' %></h3>
    <p>{$ConfirmationTime.Nice}</p>
    <p>{$CustomerName}</p>
    <p>{$CustomerEmail}</p>
</div>
<div class="col-md-6">
    <h3><%t SwipeStripe\\Order\\Includes\\OrderDetailsSummary_Receipt.BILLING_ADDRESS 'Billing Address' %></h3>
    <address>{$BillingAddress.Nice}</address>
</div>
