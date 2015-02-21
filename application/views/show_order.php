<p class="lead">
    Order # {order_num} for {total}
</p>
{items}
<div class="row">
    {quantity}
    {code}
</div>
{/items}
<div class="row">
    <!--the original code was supposed to be able to disable the Proceed button but didn't, this is the fixed version-->
    <button onclick="location.href = '/order/commit/{order_num}';" class="btn btn-large btn-success" {okornot}>Proceed</button>
    <button onclick="location.href = '/order/display_menu/{order_num}';" class="btn btn-large btn-primary">Keep shopping</button>
    <button onclick="location.href = '/order/cancel/{order_num}';" class="btn btn-large btn-danger">Forget about it</button>
</div>