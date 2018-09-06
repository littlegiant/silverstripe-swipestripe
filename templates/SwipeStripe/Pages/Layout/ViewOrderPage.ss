<%-- TODO --%>
<% with $Order %>
<table>
    <thead>
    <tr>
        <td><strong>Item</strong></td>
        <td><strong>Unit Price</strong></td>
        <td><strong>Quantity</strong></td>
        <td><strong>Price</strong></td>
    </tr>
    </thead>

    <% loop $OrderItems %>
        <tr>
            <td>{$Purchasable.Title}</td>
            <td>{$Price.Nice}</td>
            <td>{$Quantity}</td>
            <td>{$SubTotal.Nice}</td>
        </tr>

        <% if $OrderItemAddOns %>
            <% loop $OrderItemAddOns %>
                <tr>
                    <td></td>
                    <td></td>
                    <td>{$Title}</td>
                    <td>{$Amount.Nice}</td>
                </tr>
            <% end_loop %>

            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>{$Total.Nice}</td>
            </tr>
        <% end_if %>
    <% end_loop %>

    <tfoot>
    <tr>
        <td><strong>Subtotal</strong></td>
        <td></td>
        <td></td>
        <td>{$SubTotal.Nice}</td>
    </tr>

    <% loop $OrderAddOns %>
        <td></td>
        <td></td>
        <td>{$Title}</td>
        <td>{$Amount.Nice}</td>
    <% end_loop %>

    <tr>
        <td><strong>Total</strong></td>
        <td></td>
        <td></td>
        <td>{$Total.Nice}</td>
    </tr>
    </tfoot>
</table>
<% end_with %>
