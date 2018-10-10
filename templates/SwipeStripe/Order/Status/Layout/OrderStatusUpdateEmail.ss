<% with $OrderStatusUpdate %>
    <p>Hey {$Order.CustomerName},</p>

    <p>Your {$SiteConfig.Title} order {$Order.Title}'s status has been updated to <strong>{$Status}</strong>.</p>

    <% if $Message %>
        <div>
            <h3>Notes</h3>
            <blockquote>{$Message}</blockquote>
        </div>
    <% end_if %>

    <p>View your order here - <a href="{$Order.AbsoluteLink}">{$Order.AbsoluteLink}</a>.</p>
<% end_with %>
