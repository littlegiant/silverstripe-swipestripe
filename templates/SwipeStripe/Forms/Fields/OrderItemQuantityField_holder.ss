<div id="$HolderID" class="field<% if $extraClass %> $extraClass<% end_if %>">
    <% with $OrderItem %>
        <div>
            <h3>{$Title} - {$SubTotal.Nice} [{$Price.Nice} x{$Quantity}]</h3>
            <p>{$Description}</p>
        </div>
    <% end_with %>

    <div class="middleColumn">
        <% if $Title %><label class="left" for="$ID">$Title</label><% end_if %>
        $Field
    </div>
    <% if $RightTitle %><label class="right" for="$ID">$RightTitle</label><% end_if %>
    <% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
    <% if $Description %><span class="description">$Description</span><% end_if %>
</div>
