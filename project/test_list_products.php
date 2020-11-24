  
<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
$query = "";
$results = [];
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
if (isset($_POST["search"]) && !empty($query)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id,name,price,category,visibility, user_id from Products WHERE name like :q LIMIT 10");
    $r = $stmt->execute([":q" => "%$query%"]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
}
?>
<form method="POST">
    <input name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
    <input type="submit" value="Search" name="search"/>
</form>
<div class="results">
    <?php if (count($results) > 0): ?>
        <div class="list-group">
            <?php foreach ($results as $r): ?>
                <?php if ($r["visibility"] == "1"): ?> 
                    <div class="list-group-item">
                        <div>
                            <div>Name:</div>
                            <div><?php safer_echo($r["name"]); ?></div>
                        </div>
                        <div>
                            <div>Price:</div>
                            <div><?php safer_echo($r["price"]); ?></div>
                        </div>
                        <div>
                            <div>Category:</div>
                            <div><?php safer_echo($r["category"]); ?></div>
                        </div>
                        <div>
                            <div>Owner Id:</div>
                            <div><?php safer_echo($r["user_id"]); ?></div>
                        </div>
                        <div>
                            <a type="button" href="view_products.php?id=<?php safer_echo($r['id']); ?>">View</a>
                        </div>
                    </div>
                <?php elseif (count($results) == 1): ?>
                    <p>No results</p>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
