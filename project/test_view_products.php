<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) { // product_id
    $product_id = $_GET["id"];
}
?>
<?php



$result = [];
if (isset($product_id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Products WHERE id = :id");
    $r = $stmt->execute([":id" => $product_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}


?>
<script>
    //php will exec first so just the value will be visible on js side
    function addToCart(product_id){
        //https://www.w3schools.com/xml/ajax_xmlhttprequest_send.asp
        let xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                let json = JSON.parse(this.responseText);
                if (json) {
                    if (json.status == 200) {
                        alert(json.message);
                    } else {
                        alert(json.error);
                    }
                }
            }
        };
        xhttp.open("POST", "<?php echo "api/add_to_cart.php";?>", true);
        //this is required for post ajax calls to submit it as a form
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        //map any key/value data similar to query params
        xhttp.send("itemId="+product_id);
    }
</script>

    <h3>View product details</h3>


    

    <?php if (isset($result) && !empty($result)): ?>
      <div class="row row-cols-1 row-cols-md-2">
        <div class="col mb-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title"> <?php safer_echo($result["name"]); ?></h5>
              <p class="card-text">$<?php safer_echo($result["price"])?></p>
              <p class="card-text"><?php safer_echo($result["description"])?></p>
              <p class="card-text"><?php if($result["quantity"]>0){echo "In Stock";}else{echo "Out Of Stock";}?></p>
              <p class="card-text"><small class="text-muted">added on <?php safer_echo($result["modified"])?></small></p>




            <form method="POST" >
              <select class="form-control" id="quantity" name="quantity" style= "width: 60;">
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
              </select>
              <button type="button" onclick="addToCart(<?php echo $product_id;?>);" class="btn btn-primary btn-lg">Add to Cart</button>
            </form>

              <div>
            </div>
          </div>
        </div>
      <?php else: ?>
        <p>Error looking up id...</p>
      <?php endif; ?>

<?php require(__DIR__ . "/partials/flash.php"); ?>
