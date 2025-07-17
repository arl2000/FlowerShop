<?php
/* ────────────────  BOOTSTRAP  ──────────────── */
session_start();
include 'navi.php';
include 'db_connection.php';   //  $conn is created here

function fetchRows(mysqli $con,string $sql):array {
    $out=[]; $res=$con->query($sql);
    if($res && $res->num_rows) while($row=$res->fetch_assoc()) $out[]=$row;
    return $out;
}

/* Flowers & add‑ons have image_path */
$flowers = fetchRows($conn,"SELECT id,name,price,image_path FROM flowers");
$addons  = fetchRows($conn,"SELECT id,name,price,image_path FROM add_ons");
/* Ribbons, wrappers & leaves: no image_path column */
$ribbons = fetchRows($conn,"SELECT id,name,price FROM ribbon_colors");
$wrappers= fetchRows($conn,"SELECT id,color AS name,price FROM wrappers");
$leaves  = fetchRows($conn,"SELECT id,name,price FROM leaves");

/* ───────────  HANDLE FORM SUBMIT  ─────────── */
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>document.addEventListener('DOMContentLoaded',()=>{document.getElementById('loginModal').style.display='flex';});</script>";
        exit;
    }

    $userId        = $_SESSION['user_id'];
    $totalPrice    = (float)$_POST['total_price'];
    $selectedItems = json_decode($_POST['selected_items'],true);   // [{id,group,qty},…]
    $itemPositions = json_decode($_POST['item_positions'],true);   // reserved
    $customerMsg   = $_POST['customer_message'] ?? null;

    if ($totalPrice<=0 || empty($selectedItems)) {
        $_SESSION['cart_message']="Please select at least one item.";
        echo "<script>window.location.href = '".$_SERVER['HTTP_REFERER']."';</script>";
        exit;
    }

    $productName="Custom Bouquet";

    /* 1️⃣  create product shell */
    $stmt=$conn->prepare("INSERT INTO products (product_name,price,product_price,product_description,product_image,category_id)
                          VALUES (?,?,?,'Customized product','default.jpg',8)");
    $stmt->bind_param("sdd",$productName,$totalPrice,$totalPrice);
    $stmt->execute();
    $productId=$conn->insert_id;
    $stmt->close();

    /* 2️⃣  Group items by type */
    $groupedItems = [
        'flowers' => [],
        'ribbons' => [],
        'wrappers' => [],
        'addons' => [],
        'leaves' => []
    ];
    
    foreach($selectedItems as $item) {
        $type = $item['group'];
        if ($type === 'flower') $type = 'flowers';
        if ($type === 'ribbon') $type = 'ribbons';
        if ($type === 'wrapper') $type = 'wrappers';
        if ($type === 'addon') $type = 'addons';
        if ($type === 'leaf') $type = 'leaves';
        
        // Get item details from original arrays to ensure we have name and price
        $sourceArray = ${$type};
        $itemDetails = array_filter($sourceArray, fn($x) => $x['id'] == $item['id']);
        $itemDetails = reset($itemDetails);
        
        $groupedItems[$type][] = [
            'id' => $item['id'],
            'qty' => $item['qty'],
            'name' => $itemDetails['name'] ?? '',
            'price' => $itemDetails['price'] ?? 0
        ];
    }
    
    $addonsJson = json_encode($groupedItems);
    
    $stmt=$conn->prepare("INSERT INTO customized_products
            (product_id,product_name,product_price,product_description,add_ons,message,category_id)
            VALUES (?,?,?,'Custom Bouquet',?, ?,8)");
    $stmt->bind_param("isdss",$productId,$productName,$totalPrice,$addonsJson,$customerMsg);
    $stmt->execute(); $stmt->close();

    /* 3️⃣  add to cart */
    $stmt=$conn->prepare("INSERT INTO cart
            (user_id,product_id,product_name,product_price,quantity,is_customized,customer_message,addons)
            VALUES (?,?,?, ?,1,1,?,?)");
    $stmt->bind_param("iisdss",$userId,$productId,$productName,$totalPrice,$customerMsg,$addonsJson);
    $stmt->execute(); $stmt->close();

    echo "<script>window.location.href = 'cart.php';</script>";
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Customize Bouquet</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
/* Modern font stack */
body {
    font-family: 'Raleway', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    margin: 0;
    background-color: #f8f9fa;
    color: #333;
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    margin-bottom: 2rem;
    color: #333;
    text-align: center;
    font-weight: 600;
}

h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    margin: 2rem 0 1.5rem;
    color: #333;
    border-bottom: 2px solid #eee;
    padding-bottom: 0.5rem;
}

h4 {
    font-size: 1.4rem;
    margin: 1.5rem 0 1rem;
    color: #333;
}

.catalog {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.item-card {
    border: 极速赛车开奖结果
    transform: translateY(-3px);
    border-color: #ddd;
}

.item-card.selected {
    outline: 3px solid #28a745;
    border-color: transparent;
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
}

.item-card img {
    width: 100%;
    height: 140px;
    object-fit: cover;
    border-radius: 6px;
    margin-bottom: 0.8rem;
}

.item-card strong {
    font-weight: 600;
    margin-top: auto;
    font-size: 0.95rem;
    display: block;
    padding: 0.3rem 0;
}

#preview {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 1.5rem;
}

#preview ul {
    padding: 0;
    margin: 0;
}

#preview li {
    list-style: none;
    margin: 极速赛车开奖结果
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(40, 167, 69, 0.4);
}

button[type="submit"]:disabled {
    background: #888;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.message {
    background: #ffdede;
    border: 1px solid #f5c2c2;
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-radius: 6px;
    font-weight: 500;
}

hr {
    border: 0;
    border-top: 1px solid #eee;
    margin: 2.5rem 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .catalog {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 1rem;
    }
    
    .item-card img {
        height: 120px;
    }
    
    h2 {
        font-size: 2rem;
    }
    
    h3 {
        font-size: 1.5rem;
    }
    
    #preview li {
        flex-wrap: wrap;
    }
    
    .qty-controls {
        margin-left: 0;
        width: 100%;
        justify-content: flex-end;
    }
}
</style>
</head>
<body>
<div class="container">
<h2>Create Your Custom Bouquet</h2>
<p style="text-align:center;max-width:800px;margin:0 auto 2rem;color:#666;font-size:1.1rem;">
    Select your favorite flowers, ribbons, wrappers, and add-ons to create a personalized bouquet
</p>
<?php if(isset($_SESSION['cart_message'])){ ?>
  <div class="message"><?=htmlspecialchars($_SESSION['cart_message']); unset($_SESSION['cart_message']);?></div>
<?php } ?>

<form method="post">
<?php
function catalogSection(string $title,array $items,string $group){
    echo "<h3>".htmlspecialchars($title)."</h3><div class='catalog'>";
    foreach($items as $it){
        $img = !empty($it['image_path']) ? $it['image_path'] : 'placeholder.jpg';
        echo "<div class='item-card' ".
             "data-id='{$it['id']}' ".
             "data-group='{$group}' ".
             "data-name=\"".htmlspecialchars($it['name'])."\" ".
             "data-price='{$it['price']}' ".
             "data-img='".htmlspecialchars($img)."'>".
             "<img src='".htmlspecialchars($img)."' alt=''>".
             "<strong>".htmlspecialchars($it['name'])."</strong><br>".
             "₱".number_format($it['price'],2).
             "</div>";
    }
    echo "</div>";
}
catalogSection('Flowers',       $flowers ,'flower');
catalogSection('Ribbon Colors', $ribbons ,'ribbon');
catalogSection('Wrappers',      $wrappers,'wrapper');
catalogSection('Leaves',        $leaves  ,'leaf');
catalogSection('Chocolates & Add‑ons',$addons,'addon');
?>
<hr>
<h4>Your Selection (<span id="selCount">0</span> items)</h4>
<ul id="preview"></ul>

<div class="total-display">
    Total: ₱<span id="total">0.00</span>
</div>

<label>Message for recipient (optional):
<textarea name="customer_message" rows="3" style="width:100%;resize:vertical"></textarea></label><br>

<input type="hidden" name="total_price"    id="total_price">
<input type="hidden" name="selected_items" id="selected_items">
<input type="hidden" name="item_positions" id="item_positions">

<button type="submit" id="addBtn" disabled>Add to Cart</button>
</form>
</div>

<script>
/* ---------- minimal vanilla‑JS bouquet builder --------------------------- */
const state=new Map();           // key -> {id,group,name,price,img,qty}
function keyOf(d){return d.group+'-'+d.id;}

document.querySelectorAll('.item-card').forEach(card=>{
  card.addEventListener('click',()=>{
      const data=card.dataset; const k=keyOf(data);
      if(state.has(k)){          // toggle off
         state.delete(k); card.classList.remove('selected');
      }else{                     // add with qty 1
         state.set(k,{id:data.id,group:data.group,name:data.name,
                      price:+data.price,img:data.img,qty:1});
         card.classList.add('selected');
      }
      render();
  });
});

document.getElementById('preview').addEventListener('click',e=>{
   const btn=e.target.closest('button'); if(!btn) return;
   const li=btn.closest('li'); const k=li.dataset.key; if(!state.has(k)) return;
   if(btn.classList.contains('plus')){ state.get(k).qty++; }
   else if(btn.classList.contains('minus')){
       const it=state.get(k); it.qty>1? it.qty-- : state.delete(k);
   }
   render();
});

function render(){
  const pv=document.getElementById('preview'); pv.innerHTML='';
  let total=0; state.forEach(i=>{ total+=i.price*i.qty;
      pv.insertAdjacentHTML('beforeend',`
        <li data-key="${keyOf(i)}">
          <img src="${i.img}">
          <button class="qty-btn minus">−</button>
          <span>${i.qty}</span>
          <button class="qty-btn plus">+</button>
          ${i.name} – ₱${(i.price*i.qty).toFixed(2)}
        </li>`); });

  document.getElementById('selCount').textContent = state.size;
  document.getElementById('total')    .textContent = total.toFixed(2);
  document.getElementById('total_price').value     = total.toFixed(2);
  document.getElementById('selected_items').value  = JSON.stringify(
       [...state.values()].map(i=>({id:i.id,group:i.group,qty:i.qty})));
  document.getElementById('item_positions').value  = '[]';
  document.getElementById('addBtn').disabled       = (state.size===0);
}
render();
</script>
</body>
</html>
