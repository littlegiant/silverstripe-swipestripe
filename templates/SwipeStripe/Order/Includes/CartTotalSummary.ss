<% with $Cart %>
    <table class="table">
        <tfoot>
        <tr>
            <td><strong>Subtotal</strong></td>
            <td><strong>{$SubTotal.Nice}</strong></td>
        </tr>
            <% loop $OrderAddOns %>
                <% if $IsActive %>
                <tr>
                    <td>{$Title}</td>
                    <td>{$Amount.Nice}</td>
                </tr>
                <% end_if %>
            <% end_loop %>
        <tr>
            <td><strong>Total</strong></td>
            <td><strong>{$Total.Nice}</strong></td>
        </tr>
        </tfoot>
    </table>
<% end_with %>
