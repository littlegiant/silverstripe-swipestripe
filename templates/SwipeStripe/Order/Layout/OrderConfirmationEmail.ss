<% with $Order %>
    <p><%t SwipeStripe\\Order\\Layout\\OrderConfirmationEmail.GREETING 'Hey {name},' name=$CustomerName %></p>

    <p><%t SwipeStripe\\Order\\Layout\\OrderConfirmationEmail.PAYMENT_RECEIVED 'We have received your order and payment for {title}' title=$Title %></p>

    <p><%t SwipeStripe\\Order\\Layout\\OrderConfirmationEmail.ORDER_LINK 'View your order here - <a href="{link}">{link}</a>.' link=$AbsoluteLink %></p>

    <!-- TODO - items, payment details, etc. -->
<% end_with %>
