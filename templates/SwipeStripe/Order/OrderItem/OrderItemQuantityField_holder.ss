<div id="$HolderID" class="row field<% if $extraClass %> $extraClass<% end_if %>">
    <% with $OrderItem %>
        <div class="col-md-10">
            <h3><a href="{$Purchasable.Link}">{$Title}</a> <small>{$Price.Nice}</small></h3>
            <div>{$Description}</div>

            <% if $OrderItemAddOns %>
                <div class="col-md-5">
                    <table class="table">
                        <% loop $OrderItemAddOns %>
                            <% if $IsActive %>
                                <tr>
                                    <td>{$Title}</td>
                                    <td>{$Amount.Nice}</td>
                                </tr>
                            <% end_if %>
                        <% end_loop %>
                    </table>
                </div>
            <% end_if %>
        </div>
    <% end_with %>

    <div class="col-md-2">
        <h4>{$OrderItem.Total.Nice}</h4>

        <p>
            {$Title}
            {$Field}
        </p>

        <% if $RemoveAction && not $RemoveAction.IsDisabled %>
            {$RemoveAction}
        <% end_if %>
    </div>

    <% if $RightTitle %><label class="right" for="$ID">{$RightTitle}</label><% end_if %>
    <% if $Message %><span class="message $MessageType">{$Message}</span><% end_if %>
    <% if $Description %><span class="description">{$Description}</span><% end_if %>
</div>
<hr>
