<?php session_start(); ?>
<!DOCTYPE html>
<?php
include 'config.php';
include 'functions.php';
$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$input = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

?>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="style.css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script>
            $(document).ready(function()
            {
                $(".success").fadeIn(1000);
                setTimeout(function()
                {
                    $(".success").fadeOut(1000);
                }, 2000);
            });
        </script>
        <title>Time and Again Bookstore: Inventory Management</title>
    </head>
    <body>
        <!--Header-->
        <?php include 'header.php'; ?>
        <table class="main">
            <tr>
                <td id="topmenu"><?php include 'topmenu.php'; ?></td>
            </tr>
            <tr>
                <td id="content">
                    <?php if (mysqli_connect_errno($con)): //check to make sure the MySQL connection succeeded?>
                        <div class="error">ERROR: Could not connect to MySQL database.</div>
                                            
                    <?php else: ?>
                        <!--Start page content-->

                        <h2>Sell</h2>
                        
                        <?php $checkQuery = 'SELECT ID from tblInventory WHERE ID = '.$input['pid'];
                        
                        switch ($input['action'])
                        {
                            /*
                             * add: add the selected product ID to the cart
                             */
                            case 'add':
                                $checkResult = mysqli_query($con, $checkQuery);
                                if (mysqli_num_rows($checkResult)):
                                    $_SESSION['cart'][$input['pid']]=0.0;
                                    echo '<div class="success">Added Product ID '.$input['pid'].' to cart.</div>';
                                else:
                                    echo '<div class="error">ERROR: Could not find a product with ID '.$input['pid'].'</div>';
                                endif;
                                break;
                            
                            /*
                             * update: edit sale prices for items in the cart
                             */
                            case 'Update':
                                foreach ($_SESSION['cart'] as $productID => $price)
                                {
                                    $_SESSION['cart'][$productID]=$input['price'.$productID];
                                }
                                $_SESSION['saleSource']=$input['saleSource'];
                                echo '<div class="success">Updated prices for items in cart.</div>';
                                break;
                                                                          
                            /*
                             * remove: remove the selected product ID from the cart
                             */
                            case 'remove':
                                unset($_SESSION['cart'][$input['pid']]);
                                echo '<div class="success">Removed product ID '.$input['pid'].' from cart.</div>';
                                break;
                            
                            /*
                             * Sell: Finalize the transaction and commit sale to tblInventory
                             */
                            case 'Sell':
                                $_SESSION['saleSource']=$input['saleSource'];
                                foreach ($_SESSION['cart'] as $productID => $price)
                                {
                                    $_SESSION['cart'][$productID]=$input['price'.$productID];
                                }
                                foreach ($_SESSION['cart'] as $productID => $price)
                                {
                                    $sellQuery = 'UPDATE tblInventory '.
                                            'SET SalePrice = '.$price.
                                            ', SoldOn = current_timestamp()'.
                                            ', SaleSource="'.$_SESSION['saleSource'].'" WHERE ID = '.
                                            $productID;
                                    mysqli_query($con, $sellQuery);
                                }
                                unset($_SESSION['cart']);
                                unset($_SESSION['saleSource']);
                                echo '<div class="success">All items in cart were marked as sold.</div>';
                                break;
                            
                            /*
                             * empty: remove all items from the cart
                             */
                            case 'Empty':
                                unset($_SESSION['cart']);
                                echo '<div class="success">Removed all items from cart.</div>';
                                break;
                        }
                        
                        /*
                         * Now, display the current cart
                         */
                        if($_SESSION['cart']): ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
                            Sale source: <select name="saleSource">
                                <option value="InStore" <?php if($_SESSION['saleSource']=='InStore'){echo 'selected="selected"';}?>>In Store</option>
                                <option value="Amazon" <?php if($_SESSION['saleSource']=='Amazon') {echo 'selected="selected"';}?>>Amazon</option>
                                <option value="Ebay" <?php if($_SESSION['saleSource']=='Ebay') {echo 'selected="selected"';}?>>eBay</option>
                            </select>
                        <table class="inventory">
                            <tr>
                                <th>ISBN-13</th>
                                <th>ISBN-10</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Condition</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                            <?php
                             $grandTotal=0;
                             foreach ($_SESSION['cart'] as $productID => $price)
                             {
                                 $query = 'SELECT * FROM tblInventory JOIN tblCatalog '.
                                         'ON tblInventory.catalogID = tblCatalog.ID '.
                                         'WHERE tblInventory.ID = '.$productID;
                                 $result = mysqli_query($con, $query);
                                 if(mysqli_num_rows($result) > 0)
                                 {
                                     $row= mysqli_fetch_array($result);
                                     $grandTotal += $price;
                                     $actionLink = '<a href="'.htmlspecialchars($_SERVER["PHP_SELF"]).
                                             '?action=remove&pid='.$productID.
                                             '">Remove</a>';
                                     $tableRow = '<tr>'
                                             .'<td>'
                                             .$row['ISBN_13']
                                             .'</td><td>'
                                             .$row['ISBN_10']
                                             .'</td><td>'
                                             .$row['Title']
                                             .'</td><td>'
                                             .$row['Author']
                                             .'</td><td>'
                                             .$row['BookCondition']
                                             .'</td><td>'
                                             .'<input type="number" class="priceInput" name="price'.$productID.'" value="'.number_format($price,2).'" step="any" min="0">'
                                             .'</td><td>'
                                             .$actionLink
                                             .'</td></tr>';
                                     echo $tableRow;
                                 }
                             }
                             echo '<tr><td colspan="5" class="gtLabel">Grand Total</td><td class="gtSum">$'.number_format($grandTotal,2).'</td></tr>'
                                
                            ?>
                        </table>
                            <input type="submit" name="action" value="Update">
                            <input type="submit" name="action" value="Empty">
                            <input type="submit" name="action" value="Sell">
                        </form>
                        <?php else:
                            echo '<div class="warning">No items to sell. Use the search screen to add items to the cart.</div>';
                        endif; ?>           

                        <!--End page content-->

                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <!--Footer-->
        <?php include 'footer.php'; ?>
    </body>
</html>
