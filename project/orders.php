<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>

<?php
$db = getDB();
if(!has_role("Admin")) {
    $stmt = $db->prepare("SELECT count(*) as total from Orders where user_id=:id");
}elseif(has_role("Admin")){
    $stmt = $db->prepare("SELECT count(*) as total from Orders");
}
$stmt->execute([":id"=>get_user_id()]);
$orderResult = $stmt->fetch(PDO::FETCH_ASSOC);
if(!has_role("Admin")){
    $userID = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("SELECT price,created,Address FROM Orders where user_id=:id ORDER by created DESC LIMIT 10");
    $stmt->bindValue(":id", $userID);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}elseif(has_role("Admin")){
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Orders ORDER by created DESC LIMIT 10");
    $stmt->execute();
    $adminOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
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
        foreach ($adminOrders as $order):?>
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
    <?php endif; ?>
</div>
<?php require(__DIR__ . "/partials/flash.php");
