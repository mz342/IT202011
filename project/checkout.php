<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
$user_id = get_user_id();

?>

<?php
$userID = get_user_id();
$db = getDB();
$stmt = $db->prepare("SELECT c.id,c.product_id,c.quantity,c.price, Product.name as product FROM Cart as c JOIN Users on c.user_id = Users.id LEFT JOIN Products Product on Product.id = c.product_id where c.user_id = :id ORDER by product");
$r = $stmt->execute([":id" => $userID]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<form method = "POST">
  <div class="form-group1">

<h1 > Payment Method</h1>

  </div>
  <div class="form-group2">
    <label for="card type">Card Type</label>
    <select class="form-control" name="payment">
        <option value="discover">Discover</option>
        <option value="paypal">Paypal</option>
        <option value="visa">Visa</option>    </select> 
<br>
  </div>
<h2> Shipping Information</h2>
 </div>
  <div class="form-group3" style="width 40;">
        <input type="text" name="address" class="form-control" placeholder="Address Line"
  </div>
  <div class="form-group3" style="width 40;">
        <input type="text" name="city" class="form-control" placeholder="City"
 </div>
  <div class="form-group3" style="width 40;">
	<input type="text" name="state" class="form-control" placeholder="State/Province/Region"
 </div>
  <div class="form-group3" style="width 40;">
	<input type="text" name="zip" class="form-control" placeholder="Zip"
  </div>

<br>

    <div class="results">
        <div class="list-group">
            <div>
                <div><h3> Review Products</h3></div>
            </div>
            <div>
                <br>
            </div>
            <?php
            $total = 0;
            foreach ($results as $product):?>
                <div class="list-group-item">
                    <div>
                        <div><?php safer_echo($product["product"]); ?></div>
                    </div>
                    <div>
                        <div>Quantity: <?php safer_echo($product["quantity"]); ?></div>
                    </div>
                    <div>
                        <div>Price: $<?php safer_echo($product["price"]); ?></div>
                    </div>
                    <div>
                        <div>Subtotal: $<?php safer_echo($product["price"]*$product["quantity"]); $total+=$product["price"]*$product["quantity"]; ?></div>
                    </div>
                    <div>
                        <br>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div>
            <div><b> Order Total: $<?php safer_echo($total); ?></b></div>
        </div>
        <div>
            <div><br></div>
        </div>
    </div>


<br>
<br>
<br>
<br>

</form>

  <a style= "margin: 1em; float: left;" type="submit" class="btn btn-primary" href="orders.php" name="placeorder">Place Order</a>

<?php


if(isset($_POST["submit"])) {
    $adr = null;
    $payment = null;
    $price = $total;
    $created = date('Y-m-d H:i:s');
    $id = $user_id;

    if(isset($_POST["payment"])){
        $payment = $_POST["payment"];
        if($payment==-1){
            flash("Please select a payment method.");
        }
    }
    $streetAdr = $_POST["address"];
    $words = explode(" ", $streetAdr);
    if (gettype($words[0] == "integer") && (sizeof($words) >= 3) && (is_string($_POST["city"])) && (is_string($_POST["state"]))) {
        $adr = $_POST["address"] . ", " . $_POST["city"] . ", " .$_POST["state"]."  ".$_POST["zip"];
    } else {
        flash("Please enter an  address.");
    }
    $db = getDB();
    $stmt = $db->prepare("SELECT Cart.product_id,Cart.quantity,Products.name,Products.quantity as inventory FROM Cart Join Products on Cart.product_id = Products.id JOIN Users on Cart.user_id = Users.id where Cart.user_id=:id");
    $r = $stmt->execute([":id" => $id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $validOrder = true;
    foreach($items as $item):
        if($item["quantity"]>$item["inventory"]){
            flash("Sorry, there are only ".$item["inventory"]." ".$item["name"]." left in stock, update your cart please.");
            $validOrder = false;
        }elseif($item["inventory"]==0){
            flash("Sorry, ".$item["name"]." is out of stock.");
            $validOrder = false;
        }
    endforeach;

    if ($adr && ($payment!="-1") && $validOrder) {

        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Orders (user_id,total_price,created,address,payment_method) VALUES(:user,:price,:cr,:adr,:pay)");
        $r = $stmt->execute([
            ":user"=>$id,
            ":price"=>$price,
            ":cr"=>$created,
            ":adr"=>$adr,
            ":pay"=>$payment
        ]);
        if(!$r){
            $e = $stmt->errorInfo();
            flash("Error placing order: " . var_export($e, true));
        }

        $db = getDB();
        $stmt = $db->prepare("SELECT id from Orders where user_id = :id ORDER by created DESC LIMIT 1");
        $r = $stmt->execute([":id"=>$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        $order_id = $order["id"];
        $db = getDB();
        $stmt = $db->prepare("SELECT c.product_id,c.quantity,c.price FROM Cart as c JOIN Users on c.user_id = Users.id LEFT JOIN Products Product on Product.id = c.product_id where c.user_id = :id ORDER by Product.id");
        $r = $stmt->execute([":id" => $id]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($orderItems as $orderItem):
            $pid = $orderItem["product_id"];
            $itemQuantity = $orderItem["quantity"];
            $unitPrice = $orderItem["price"];

            $db = getDB();
            $stmt = $db->prepare("INSERT INTO OrderItems (order_id,product_id,quantity,unit_price) VALUES(:order,:pid,:quant,:up)");
            $r = $stmt->execute(["order"=>$order_id,":pid"=>$pid,":quant"=>$itemQuantity,":up"=>$unitPrice]);

            $db = getDB();
            $stmt = $db->prepare("UPDATE Products SET quantity=quantity-:itemQuantity where id=:pid");
            $r = $stmt->execute([":pid"=>$pid,":itemQuantity"=>$itemQuantity]);
        endforeach;

        $userID = get_user_id();
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM Cart where user_id=:id");
        $r = $stmt->execute([":id" => $userID]);

        flash("Thank you for your order!");
        die(header("Location: orders.php"));
    }
}

?>








<?php require_once(__DIR__ . "/partials/flash.php"); ?>
