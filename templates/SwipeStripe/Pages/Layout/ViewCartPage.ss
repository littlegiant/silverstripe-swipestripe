{$CartForm}

<% with $SwipeStripe.ActiveCart %>
    <table>
        <tr>
            <td><strong>Subtotal</strong></td>
            <td><strong>{$SubTotal.Nice}</strong></td>
        </tr>
        <% loop $OrderAddOns %>
            <tr>
                <td>{$Title}</td>
                <td>{$Amount.Nice}</td>
            </tr>
        <% end_loop %>
        <tr>
            <td><strong>Total</strong></td>
            <td><strong>{$Total.Nice}</strong></td>
        </tr>
    </table>
<% end_with %>
