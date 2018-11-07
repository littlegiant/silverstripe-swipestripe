<% with $OrderStatusUpdate %>
    <p><%t SwipeStripe\\Order\\Status\\Layout\\OrderStatusUpdateEmail.GREETING 'Hey {name},' name=$Order.CustomerName %></p>

    <p>
        <%t SwipeStripe\\Order\\Status\\Layout\\OrderStatusUpdateEmail.ORDER_UPDATED "Your {site} order {order}'s status has been updated to <strong>{status}</strong>" site=$SiteConfig.Title order=$Order.Title status=$Status %>
    </p>

    <% if $Message %>
        <div>
            <h3><%t SwipeStripe\\Order\\Status\\Layout\\OrderStatusUpdateEmail.NOTES 'Notes' %></h3>
            <blockquote>{$Message}</blockquote>
        </div>
    <% end_if %>

    <p><%t SwipeStripe\\Order\\Status\\Layout\\OrderStatusUpdateEmail.ORDER_LINK 'View your order here - <a href="{link}">{link}</a>' link=$Order.AbsoluteLink %></p>
<% end_with %>
