<% with $Order %>
    <p>Hey {$CustomerName},</p>

    <p>We have received your order and payment for {$Title}.</p>

    <p>View your order here - <a href="{$AbsoluteLink}">{$AbsoluteLink}</a>.</p>

    <!-- TODO - items, payment details, etc. -->
<% end_with %>
