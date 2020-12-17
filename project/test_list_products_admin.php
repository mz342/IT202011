<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php

$results = [];
$low = 0;
$db = getDB();



if (has_role("Admin")) {
  if (isset($_POST["stock"])) {
    $stmt = $db->prepare("SELECT id,name, price, description, quantity FROM Products WHERE quantity = 0  ORDER BY price LIMIT 10");
    $r = $stmt->execute();
  }
  elseif (isset($_POST["sort"])) {
    $stmt = $db->prepare("SELECT AVG(Ratings.rating) as rating,Products.id as id, Products.name, Products.description, Products.quantity,Products.price  FROM Ratings JOIN Products on Products.id = Ratings.product_id GROUP BY product_id");
    $r = $stmt->execute();


  }
  elseif (isset($_POST["quantity"])) {
    $low = $_POST["quantity"];
    $stmt = $db->prepare("SELECT AVG(rating), product_id FROM `Ratings` GROUP BY product_id");
    $r = $stmt->execute( [":q" => $low]);
  }else{
    $stmt = $db->prepare("SELECT id,name, price, description, quantity FROM Products  LIMIT 10");
    $r = $stmt->execute();
  }
}

if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    flash("There was a problem fetching the products " . var_export($stmt->errorInfo(), true));
}



?>

  <form method="POST">
      <label for="input">quantity</label>
      <input type="input" name="quantity" class="form-control" id="quantity" aria-describedby="emailHelp" required>
    <button id="submit" type="submit" class="btn btn-primary" name="quantity" value="quantity">Find</button>
  </form>
  <form method="POST">
    <button id="submit" type="submit" class="btn btn-primary" name="stock" value="stock">View Stock</button>
    <button id="submit" type="submit" class="btn btn-primary" name="sort" value="sort">Sort Rating</button>
  </form>




<h1>View Products</h1>
<div class="row" style= "margin-left: 2em;">
<?php if (count($results) > 0): ?>
    <?php foreach ($results as $r): ?>
      <div   class="card" style="width: 20rem; margin: 1em;">
        <div class="card-body">
          <a href = "test_view_products.php?id=<?php safer_echo($r['id']); ?>" <h5 class="card-title"><?php safer_echo($r["name"]); ?></h5></a>
          <h6 class="card-title"><?php safer_echo($r["price"]); ?></h6>
          <p class="card-text"><?php safer_echo($r["description"]); ?></p>
          <p class="card-text"> quantity = <?php safer_echo($r["quantity"]); ?></p>
          <?php if (isset($_POST["sort"])): ?>
          <p class="card-text"> rating: <?php safer_echo($r["rating"]); ?></p>
        <?php endif?>
          <?php if (has_role("Admin")): ?>
            <a href="test_edit_products.php?id=<?php safer_echo($r['id']); ?>" class="btn btn-primary">Edit</a>
          <?php endif; ?>
          </div>
        </div>
<?php endforeach; ?>
<?php endif; ?>
</div>
<?php require_once(__DIR__ . "/partials/flash.php"); ?>
