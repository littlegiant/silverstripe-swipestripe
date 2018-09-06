<div id="$HolderID" class="field<% if $extraClass %> $extraClass<% end_if %>">
    <% with $OrderItem %>
        <div>
            <h3>{$Title} - {$Total.Nice}</h3>
            <p>{$Description}</p>

            <% if $OrderItemAddOns %>
                <h4>Base Price: {$SubTotal.Nice} [{$Price.Nice} x{$Quantity}]</h4>

                <h4>Add-ons:</h4>
                <ul>
                    <% loop $OrderItemAddOns %>
                        <li>{$Title} (<strong>{$Amount.Nice}</strong>)</li>
                    <% end_loop %>
                </ul>
            <% else %>
                <h4>Price: {$SubTotal.Nice} [{$Price.Nice} x{$Quantity}]</h4>
            <% end_if %>
        </div>
    <% end_with %>

    <div class="middleColumn">
        <% if $Title %><label class="left" for="$ID">$Title</label><% end_if %>
        $Field
    </div>
    <% if $RightTitle %><label class="right" for="$ID">$RightTitle</label><% end_if %>
    <% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
    <% if $Description %><span class="description">$Description</span><% end_if %>

    <% if $RemoveAction && not $RemoveAction.IsDisabled %>
        {$RemoveAction}
    <% end_if %>
</div>
