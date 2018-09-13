<% with $Cart %>
    <% loop $OrderItems %>
        <div class="row">
            <div class="col-md-10">
                <h3><a href="{$Purchasable.Link}">{$Title}</a>
                    <small>{$Price.Nice}</small>
                </h3>
                <h5>Quantity: {$Quantity}</h5>
                <p>{$Description}</p>

                <% if $OrderItemAddOns %>
                    <table class="table">
                        <% loop $OrderItemAddOns %>
                            <tr>
                                <td>{$Title}</td>
                                <td>{$Amount.Nice}</td>
                            </tr>
                        <% end_loop %>
                    </table>
                <% end_if %>
            </div>

            <div class="col-md-2">
                <h4>{$Total.Nice}</h4>
            </div>
        </div>
        <hr>
    <% end_loop %>
<% end_with %>
