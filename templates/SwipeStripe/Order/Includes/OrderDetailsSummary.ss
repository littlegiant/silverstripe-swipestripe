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
