<table class="table table-responsive table-hover table-bordered">
    <tbody>
    <tr>
        <td>Product price</td>
        <td>{{ $item['currency']}} {{{ $item['price'] }}}</td>
    </tr>
    <td>Shipping price</td>
    <td>{{ $item['currency']}} {{{ $item['ship_price'] }}}</td>
    <tr>
        <td><strong>Total</strong></td>
        <td><strong>{{ $item['currency'] }} {{ floatval($item['price']) + floatval($item['ship_price']) }}</strong></td>
    </tr>

    </tbody>
</table>