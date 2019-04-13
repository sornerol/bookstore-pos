<!DOCTYPE html>
<?php
include 'config.php';
include 'functions.php';
$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$filterArray = array(
    'page' => FILTER_VALIDATE_INT,
    'query' => FILTER_SANITIZE_STRING,
    'outOfStock' => FILTER_VALIDATE_BOOLEAN,
    'amazonPricing' => FILTER_VALIDATE_BOOLEAN
);
$input = filter_input_array(INPUT_GET, $filterArray);
if (isset($input['page'])) {
    $page = $input['page'];
} else {
    $page = 1;
}
$startFrom = ($page - 1) * RECORS_PER_PAGE;
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
<?php if (mysqli_connect_errno($con)): //check to make sure the MySQL connection succeeded ?>
                        <div class="error">ERROR: Could not connect to MySQL database.</div>
                    <?php else: ?>

                        <!--Start page content-->

                        <h2>Search</h2>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
                            Search query: <input type="text" name="query" value="<?php echo htmlspecialchars(ifsetor($input['query'])); ?>"><br/>
                            Include out of stock: <input type="checkbox" name="outOfStock" <?php if ($input['outOfStock']) {
                            echo 'checked';
                        } ?>><br/>
                            Attempt to get Amazon Pricing (Slow): <input type="checkbox" name="amazonPricing" <?php if ($input['amazonPricing']) {
                            echo 'checked';
                        } ?>><br/>
                            <input type="submit" name="search" value="Search"><br/>
                        </form>
                        You may search for ISBN, Author Name, or Title.
                        <hr/>
                        <?php
                        if (isset($input['outOfStock'])) {
                            $leftJoin = 'LEFT ';
                        } else {
                            $soldOn = 'AND SoldOn IS NULL ';
                        }

                        $query = "SELECT tblCatalog.*,tblInventory.catalogID,SUM(IF(tblInventory.catalogID AND tblInventory.SoldOn IS NULL, 1, 0)) AS ItemCount FROM tblCatalog "
                                . ifsetor($leftJoin) . "JOIN tblInventory ON tblCatalog.ID = tblInventory.catalogID WHERE (ISBN_13 LIKE '%"
                                . $input['query'] . "%' OR ISBN_10 LIKE '%"
                                . $input['query'] . "%' OR Author LIKE '%"
                                . $input['query'] . "%' OR Title LIKE '%"
                                . $input['query'] . "%') " . ifsetor($soldOn) . "GROUP BY tblCatalog.ID LIMIT " . $startFrom . ", " . RECORS_PER_PAGE;
                        //echo 'DEBUG: '.$query;
                        $result = mysqli_query($con, $query);
                        ?>
                        <table class="inventory">
                            <tr>
                                <th>ISBN-13</th>
                                <th>ISBN-10</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Subject</th>
                                <th>Number of Copies</th>
                                <th>Amazon Price</th>
                            </tr>

    <?php
    while ($row = mysqli_fetch_array($result)) {
        $productLink = '<a href="catalogitem.php?catID=' . $row['ID'] . '">' . $row['Title'] . '</a>';
        if(isset($input['amazonPricing']))
        {
            if($row['ISBN_13'])
            {
                $price='$'. getAmazonPrice($row['ISBN_13']);
            }
            else if ($row['ISBN_10'])
            {
                $price='$'.getAmazonPrice($row['ISBN_10']);
            }
            else
            {
                $price = '-';
            }
        }
        else
        {
            $price = '(lookup not enabled)';
        }
        
        $rowOutput = '<tr class="invlist">'
                . '<td>'
                . $row['ISBN_13']
                . '</td><td>'
                . $row['ISBN_10']
                . '</td><td>'
                . $productLink
                . '</td><td>'
                . $row['Author']
                . '</td><td>'
                . $row['Subject']
                . '</td><td>'
                . $row['ItemCount']
                . '</td><td>'
                . $price
                . '</td></tr>';
        echo $rowOutput;
    }
    ?>

                        </table>
                            <?php
                            $countQuery = "SELECT COUNT(DISTINCT(tblCatalog.ID)) AS RecordCount FROM tblCatalog "
                                    . ifsetor($leftJoin) . "JOIN tblInventory ON tblCatalog.ID = tblInventory.catalogID WHERE (ISBN_13 LIKE '%"
                                    . $input['query'] . "%' OR ISBN_10 LIKE '%"
                                    . $input['query'] . "%' OR Author LIKE '%"
                                    . $input['query'] . "%' OR Title LIKE '%"
                                    . $input['query'] . "%') " . ifsetor($soldOn);
                            //echo 'DEBUG: '.$countQuery;
                            $countResult = mysqli_query($con, $countQuery);
                            $countRow = mysqli_fetch_array($countResult);
                            $numRecords = $countRow['RecordCount'];
                            $numPages = ceil($numRecords / RECORS_PER_PAGE);
                            echo 'Total results: ' . $numRecords . '<br>';
                            for ($i = 1; $i <= $numPages; $i++) {
                                if ($i != $page) {
                                    if(isset($input['outOfStock']))
                                    {
                                        $outOfStock = '&outOfStock=on';
                                    }
                                    if(isset($input['amazonPricing']))
                                    {
                                        $amazonPricing = '&amazonPricing=on';
                                    }
                                    if(isset($input['query']))
                                    {
                                        $searchQuery = '&query=' . $input['query'];
                                    }
                                    echo '<a href="' . htmlspecialchars($_SERVER["PHP_SELF"]) .
                                    '?page=' . $i .
                                    ifsetor($searchQuery).
                                     ifsetor($outOfStock).
                                     ifsetor($amazonPricing).'">' . $i . '</a> | ';
                                } else {
                                    echo $i . ' | ';
                                }
                            }
                            ?>
                        <!--End page content-->

                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <!--Footer-->
                    <?php include 'footer.php'; ?>
    </body>
</html>
