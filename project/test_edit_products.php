<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if(isset($_GET["id"])){
	$id = $_GET["id"];
}
?>
<?php
//saving
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$name = $_POST["name"];
	$quan = $_POST["quantity"];
	$price = $_POST["price"];
	$desc = $_POST["description"];
	$category = $_POST["category"];
	$visibility = $_POST["visibility"];
	if (isset($_POST["visibility"]) && $_POST["visibility"] == 'on') {
		$vis = true;
	}
	$db = getDB();
	if(isset($id)){
		$stmt = $db->prepare("UPDATE Products set name=:name, quantity=:quan, price=:price, description=:desc, category=:category, visibility=:visibility  where id=:id");
		//$stmt = $db->prepare("INSERT INTO Products (name, quantity, price, description, modified, created, user_id) VALUES(:name, :quan, :price, :desc, :mod, :crtd, :user)");
		$r = $stmt->execute([
			":name"=>$name,
			":quan"=>$quan,
			":price"=>$price,
			":desc"=>$desc,
			":category"=>$category,
			":visibility"=>$visibility,
			":id"=>$id
		]);
		if($r){
			flash("Updated successfully with id: " . $id);
		}
		else{
			$e = $stmt->errorInfo();
			flash("Error updating: " . var_export($e, true));
		}
	}
	else{
		flash("ID isn't set, we need an ID in order to update");
	}
}
?>
<?php
//fetching
$result = [];
if(isset($id)){
	$id = $_GET["id"];
	$db = getDB();
	$stmt = $db->prepare("SELECT * FROM Products where id = :id");
	$r = $stmt->execute([":id"=>$id]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<form method="POST">
	<label>Name</label>
	<input name="name" placeholder="Name" value="<?php echo $result["name"];?>"/>
	<label>Quantity</label>
	<input type="number" min="1" name="quantity" value="<?php echo $result["quantity"];?>"=/>
	<label>Price</label>
	<input type="number" step="0.01" min="0.00" name="price" value="<?php echo $result["price"];?>"/>
	<label>Description</label>
	<input name="description" placeholder="Description" value="<?php echo $result["description"];?>"/>
	<label>Category</label>
	<input name="category" placeholder="Category" value="<?php echo $result["category"];?>"/>
	<label>Visibility</label>
	<input type ="number" min="0" name="visibility"/>
	<input type="submit" name="save" value="Update"/>
</form>


<?php require(__DIR__ . "/partials/flash.php");
