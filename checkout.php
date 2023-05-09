<script src="https://www.mercadopago.com/v2/checkout.js"></script>
<?php 
$total = 0;
$qry = $conn->query("SELECT c.*,p.name,p.price,p.id as pid from `cart` c inner join `inventory` i on i.id=c.inventory_id inner join products p on p.id = i.product_id where c.client_id = ".$_settings->userdata('id'));
while($row= $qry->fetch_assoc()):
    $total += $row['price'] * $row['quantity'];
endwhile;
?>
<section class="py-5">
    <div class="container">
        <div class="card rounded-0">
            <div class="card-body"></div>
            <h3 class="text-center"><b>Checkout</b></h3>
            <hr class="border-dark">
            <form action="" id="place_order">
                <input type="hidden" name="amount" value="<?php echo $total ?>">
                <input type="hidden" name="payment_method" value="cod">
                <input type="hidden" name="paid" value="0">
                <div class="row row-col-1 justify-content-center">
                    <div class="col-6">
                    <div class="form-group col mb-0">
                    <label for="" class="control-label">Order Type</label>
                    </div>
                    <div class="form-group d-flex pl-2">
                        <div class="custom-control custom-radio">
                          <input class="custom-control-input custom-control-input-primary" type="radio" id="customRadio4" name="order_type" value="1" checked="">
                          <label for="customRadio4" class="custom-control-label">For Delivery</label>
                        </div>
                        <div class="custom-control custom-radio ml-3">
                          <input class="custom-control-input custom-control-input-primary custom-control-input-outline" type="radio" id="customRadio5" name="order_type" value="2">
                          <label for="customRadio5" class="custom-control-label">For Pick up</label>
                        </div>
                      </div>
                        <div class="form-group col address-holder">
                            <label for="" class="control-label">Delivery Address</label>
                            <textarea id="" cols="30" rows="3" name="delivery_address" class="form-control" style="resize:none"><?php echo $_settings->userdata('default_delivery_address') ?></textarea>
                        </div>
                        <div class="col">
                            <span><h4><b>Total:</b> <?php echo number_format($total) ?></h4></span>
                        </div>
                        <hr>
                        <div class="col my-3">
                        <h4 class="text-muted">Payment Method</h4>
                            <div class="d-flex w-100 justify-content-between">
                                <button class="btn btn-flat btn-dark" id="normal-pay-btn">Cash on Delivery</button>
                                <button class="btn btn-flat btn-primary" id="mercadopago-button">Mercado Pago</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<script>
function payment_online() {
    $('[name="payment_method"]').val("Online Payment");
    $('[name="paid"]').val(1);
    $('#place_order').submit();
}

$(function() {
    $('[name="order_type"]').change(function(){
        if ($(this).val() == 2) {
    $('.address-holder').hide('slow');
    $('#normal-pay-btn').text('Cash on Pickup');
} else {
    $('.address-holder').show('slow');
    $('#normal-pay-btn').text('Cash on Delivery');
}
});

$('#place_order').submit(function(e) {
    e.preventDefault();
    start_loader();
    $.ajax({
        url: 'classes/Master.php?f=place_order',
        method: 'POST',
        data: $(this).serialize(),
        dataType: "json",
        error: err => {
            console.log(err);
            alert_toast("An error occurred", "error");
            end_loader();
        },
        success: function(resp) {
            if (!!resp.status && resp.status == 'success') {
                alert_toast("Order successfully placed.", "success");
                setTimeout(function() {
                    location.replace('./');
                }, 2000);
            } else {
                console.log(resp);
                alert_toast("An error occurred", "error");
                end_loader();
            }
        }
    });
});

// Mercado Pago integration
$('#mercadopago-button').click(function() {
    var amount = <?php echo $total; ?>;

    // Initialize Mercado Pago Checkout
    var checkout = new MercadoPago.Checkout({
        publicKey: 'TEST-7072701300813384-050913-9789e0ad5c1ba6d6a3b0402bfcbd4a75-1158040355' // Replace with your Mercado Pago Public Key
    });

    // Create the preference object
    var preference = {
        items: [{
            title: 'Order',
            quantity: 1,
            currency_id: 'PHP',
            unit_price: amount
        }],
        back_urls: {
            success: 'https://yourdomain.com/success',
            failure: 'https://yourdomain.com/failure',
            pending: 'https://yourdomain.com/pending'
        }
    };

    // Open the Mercado Pago Checkout
    checkout.open(preference);
});
});
</script>
