<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
    <% with $Order %>
        <p>Hey {$CustomerName},</p>

        <p>We have received your order and payment for order #{$ID}.</p>

        <p>View your order here - <a href="{$AbsoluteLink}">{$AbsoluteLink}</a>.</p>

        <!-- TODO - items, payment details, etc. -->
    <% end_with %>
</body>
</html>
