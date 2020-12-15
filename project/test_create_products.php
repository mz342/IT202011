 <?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<form method="POST">
	<label>Name</label>
	<input name="name" placeholder="Name"/>
	<label>Price</label>
	<input type="number" min="0" name="price"/>
	<label>Quantity</label>
	<input type="number" min="0" name="quantity"/>
	<label>Description</label>
	<input type="text" name="description"/>
	<label>Category</label>
	<input type="text" name="category"/>
	<label>Visibility</label>
	<input type="number" min="0" name="visibility"/>
	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$name = $_POST["name"];
	// $state = $_POST["state"];
	$pr = $_POST["price"];
	$quantity = $_POST["quantity"];
	$desc = $_POST["description"];
	//$nst = date('Y-m-d H:i:s');//calc
	$category = $_POST["category"];
	$visibility = $_POST["visibility"];
	$user = get_user_id();
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Products (name, price, quantity, description, category, visibility, user_id) VALUES(:name, :pr, :quantity, :desc, :category, :visibility, :user)");
	$r = $stmt->execute([
		":name"=>$name,
		":pr"=>$pr,
		":quantity"=>$quantity,
		":desc"=>$desc,
		//":nst"=>$nst,
		":category"=>$category,
		":visibility"=>$visibility,
		":user"=>$user
	]);
	if($r){
		flash("Created successfully with id: " . $db->lastInsertId());
	}
	else{
		$e = $stmt->errorInfo();
		flash("Error creating: " . var_export($e, true));
	}
}
?>
<?php require(__DIR__ . "/partials/flash.php");
