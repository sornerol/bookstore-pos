<!DOCTYPE html>
<?php
include 'config.php';
$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$input = filter_input_array(INPUT_GET, FILTER_VALIDATE_INT);
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
                        
                        <h2>Item Details</h2>

                        <?php if(!isset($input['catID'])): //double check that a catalog ID has been supplied?>
                            <div class="error">ERROR: Missing catalog ID of item to display.</div>
                        <?php else:
                            $catQuery = 'SELECT * FROM tblCatalog WHERE ID = '.$input['catID'] . ' LIMIT 1';
                            $catResult = mysqli_query($con, $catQuery);
                            if (mysqli_num_rows($catResult)):
                                $catRow = mysqli_fetch_array($catResult); ?>
                                <table class="information">
                                    <tr>
                                        <td>Title:</td>
                                        <td><?php echo $catRow['Title']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Author:</td>
                                        <td><?php echo $catRow['Author']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Subject:</td>
                                        <td><?php echo $catRow['Subject']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>ISBN-13:</td>
                                        <td><?php echo $catRow['ISBN_13']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>ISBN-10:</td>
                                        <td><?php echo $catRow['ISBN_10']; ?></td>
                                    </tr>
                                </table>
                                <hr>
                                <h3>Current Holdings for <?php echo $catRow['Title']; ?></h3>
                                <table class="inventory">
                                    <tr>
                                        <th>Date Listed</th>
                                        <th>Condition</th>
                                        <th>Condition Notes</th>
                                        <th>Cost</th>
                                        <th>List Online</th>
                                        <th>Action</th>
                                    </tr>
                                <?php
                                    $invQuery = 'SELECT * FROM tblInventory WHERE catalogID=' . $input['catID'] . ' AND SoldOn IS NULL';
                                    $invResult = mysqli_query($con, $invQuery);
                                    while ($row = mysqli_fetch_array($invResult))
                                    {
                                        if ($row['ListOnline'])
                                        {
                                            $listOnline = 'YES';
                                        }
                                        else
                                        {
                                            $listOnline = 'NO';
                                        }
                                        $sellLink = '<a href="sell.php?action=add&pid='.$row['ID'].'">Sell</a>';
                                        $newRow = '<tr class="invlist">'
                                                    . '<td>'
                                                    . $row['EnteredOn']
                                                    . '</td><td>'
                                                    . $row['BookCondition']
                                                    . '</td><td>'
                                                    . $row['ConditionNotes']
                                                    . '</td><td>'
                                                    . $row['Cost']
                                                    . '</td><td>'
                                                    . $listOnline
                                                    . '</td><td>'
                                                    . $sellLink
                                                    . '</td>'
                                                    . '</tr>';
                                            echo $newRow;
                                        }
                                ?>
                                </table>
                            <?php else: ?>
                                <div class="error">ERROR: Could not find a product with Catalog ID <?php echo $input['catID']; ?></div>
                            <?php endif;?>
                        
                        <?php endif;?>
                        <!--End page content-->
                        
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <!--Footer-->
        <?php include 'footer.php'; ?>
    </body>
</html>
