<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>

<?php

//for the filter purchase history feature
if(isset($_POST["submit"])){
    if(isset($_POST["cat"])){
        $filter = "category";
        $cat = $_POST["cat"];
    }elseif(isset($_POST["date1"]) && isset($_POST["date2"])){
        $filter = "date";
        $date1 = $_POST["date1"];
        $date2 = $_POST["date2"];
    }
}
if(isset($_GET["filter"])) {
    $filter = $_GET["filter"];
}
if(isset($_GET["cat"])) {
    $cat = $_GET["cat"];
}elseif(isset($_GET["date1"]) && isset($_GET["date2"])) {
    $date1 = $_GET["date1"];
    $date2 = $_GET["date2"];
}
?>

<?php
$db = getDB();
if(!has_role("Admin")) {
    $stmt = $db->prepare("SELECT count(*) as total from Orders where user_id=:id");
}elseif(has_role("Admin")){
	if(empty($filter)){
   	      $stmt = $db->prepare("SELECT count(*) as total from Orders");
		$stmt->execute([":id"=>get_user_id()]);
		$orderResult = $stmt->fetch(PDO::FETCH_ASSOC);
}

	if(isset($filter)){
		 if ($filter == "category" && !empty($cat)) {
           		 $stmt = $db->prepare("SELECT count(*) as total from OrderItems JOIN Products on product_id=Products.id where Products.category=:cat");
           		 $stmt->execute([":cat" => $cat]);
           		 $orderResult = $stmt->fetch(PDO::FETCH_ASSOC);
              }	 elseif ($filter == "date" && !empty($date1) && !empty($date2)) {
           		 $stmt = $db->prepare("SELECT count(*) as total from Orders WHERE created BETWEEN :date1 and :date2");
           		 $stmt->execute([":date1" => $date1, ":date2" => $date2]);
           		 $orderResult = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}
$total = 0;
if(!empty($orderResult)){
	$total = (int)$orderResult["total"];
}



if(!has_role("Admin")){
    $userID = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("SELECT price,created,Address FROM Orders where user_id=:id ORDER by created DESC LIMIT 10");
    $stmt->bindvalue(":id", $userID);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}elseif(has_role("Admin")){
  if(!isset($filter)){
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Orders ORDER by created DESC LIMIT 10");
    $stmt->execute();
    $adminProcess = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
  }
  else{
            if($filter=="category" && isset($cat)){
            $stmt = $db->prepare("SELECT *,Orders.id as oid FROM Orders JOIN OrderItems JOIN Products on product_id=Products.id where Products.category=:cat LIMIT :offset, :count");
            $stmt->execute();
            $adminProcess = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }elseif($filter=="date"&&isset($date1)&&isset($date2)){
            $stmt = $db->prepare("SELECT *,Orders.id as oid FROM Orders WHERE created BETWEEN :date1 and :date2 LIMIT :offset, :count");
            $stmt->execute();
        }
     }
  
?>

<?php if(has_role("Admin")):?>
    <form method="POST">
        <h3>Filter</h3>
        <br>
        <label for="cat">Category:</label>
        <br>
        <select name="cat" id="cat">
            <option value="" disabled selected>Select a Category</option>
            <option value="Accessories">Accessories</option>
            <option value="Footwear">Footwear</option>
            <option value="Eyewear">Eyewear</option>
            <option value="Clothes">Clothes</option>
        </select>
        <br>
        <label>Date Range: (Y-M-D)</label>
        <br>
        <label>Date 1: </label>
        <br>
        <input type="text" name="date1"/>
        <br>
        <label>Date 2: </label>
        <br>
        <input type="text" name="date2"/>
        <br>
    <button id="submit" type="submit" class="btn btn-primary" name="submit" value="Submit">Submit</button>
        <br>
    </form>
<?php endif;?>





<div class="results">
    <div class="list-group">
        <div>
            <div><h3>Orders:</h3></div>
        </div>
        <div>
            <br>
        </div>
        <?php
        if(!has_role("Admin")):
        foreach ($orders as $order):?>
            <div class="list-group-item">
                <div>
                    <div>Order placed on: <?php safer_echo($order["created"]); ?></div>
                </div>
                <div>
                    <div>Address: <?php safer_echo($order["Address"]); ?></div>
                </div>
                <div>
                    <div>Subtotal: $<?php safer_echo($order["price"]); ?></div>
                </div>
                <div>
                    <div>Status: Received</div>
                </div>
                <div>
                    <br>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
    if(has_role("Admin")):
        $revenue = 0;
        foreach ($adminProcess as $order):?>
            <div class="list-group-item">
                <div>
                    <div>Order ID: <?php safer_echo($order["id"]); ?></div>
                </div>
                <div>
                    <div>Order Date: <?php safer_echo($order["created"]); ?></div>
                </div>
                <div>
                    <div>Address: <?php safer_echo($order["Address"]); ?></div>
                </div>
                <div>
                    <div>Payment Method: <?php safer_echo($order["PaymentMethod"]); ?></div>
                </div>
                <div>
                    <div>Subtotal: $<?php safer_echo($order["price"]); $revenue+=$order["price"];?></div>
                </div>
                <div>
                    <br>
                </div>
            </div>
        <?php endforeach; ?>
	<div><b>Total Price: $<?php safer_echo($revenue);?></b></div>
    <?php endif; ?>
</div>
<?php require(__DIR__ . "/partials/flash.php");
