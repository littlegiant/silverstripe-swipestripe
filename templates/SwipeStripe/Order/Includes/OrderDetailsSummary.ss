<% with $Order %>
    <div class="col-md-4">
        <h3><%t SwipeStripe\\Order\\Includes\\OrderDetailsSummary.DETAILS 'Details' %></h3>
        <p>{$ConfirmationTime.Nice}</p>
        <p>{$CustomerName}</p>
        <p>{$CustomerEmail}</p>
    </div>
    <div class="col-md-4">
        <h3><%t SwipeStripe\\Order\\Includes\\OrderDetailsSummary.BILLING_ADDRESS 'Billing Address' %></h3>
        <address>{$BillingAddress.Nice}</address>
    </div>
<% end_with %>

<div class="col-md-4">
    <h3><%t SwipeStripe\\Order\\Includes\\OrderDetailsSummary.RECEIPT_AND_INVOICE 'Receipt & Invoice' %></h3>
    <a href="{$Link($Order.ID)}/receipt"><%t SwipeStripe\\Order\\Includes\\OrderDetailsSummary.DOWNLOAD_RECEIPT_PDF 'Download receipt (PDF)' %></a>
</div>
