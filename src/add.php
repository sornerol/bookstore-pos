<!DOCTYPE html>
<?php
include 'config.php';
include 'functions.php';
$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$input = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
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

                        <h2>Add inventory</h2>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            Search for ISBN: <input type="text" name="isbn" value="<?php echo htmlspecialchars(ifsetor($input['isbn'])); ?>" maxlength="13" size="13">
                            <input type="submit" name="action" value="Lookup">
                            <br/><hr/><br/>

                            <?php
                            switch ($input['action']) {
                                case 'Lookup':
                                    /*
                                     * Query the database to find out if the product exists. If not, check Google Books to see
                                     * if it has the ISBN on record. If not, require the user to enter the information manually
                                     */
                                    if ($input['isbn']) {
                                        $query = 'SELECT * FROM tblCatalog '
                                                . 'WHERE ISBN_13="' . htmlspecialchars($input['isbn']) .
                                                '"OR ISBN_10="' . htmlspecialchars($input['isbn']) . '" LIMIT 1;';

                                        $dbSearchResult = mysqli_query($con, $query);
                                        if (mysqli_num_rows($dbSearchResult)):
                                            ?>
                                            <div class="success">Found matching ISBN in Database.</div><br/>
                                            <?php
                                            $row = mysqli_fetch_array($dbSearchResult);
                                            $populate = array('ISBN_10' => $row['ISBN_10'],
                                                'ISBN_13' => $row['ISBN_13'],
                                                'Title' => $row['Title'],
                                                'Author' => $row['Author'],
                                                'Subject' => $row['Subject'],
                                                'ID' => $row['ID'],
                                                'Action' => 'Update',);

                                        else:
                                            $googleSearchResult = googleapi_search(htmlspecialchars($input['isbn']));
                                            if ($googleSearchResult):
                                                ?>
                                                <div class="success">Found matching ISBN via Google Books API</div><br/>
                                                <?php
                                                $populate = array('ISBN_10' => googleapi_getisbn10($googleSearchResult),
                                                    'ISBN_13' => googleapi_getisbn13($googleSearchResult),
                                                    'Title' => $googleSearchResult['items'][0]['volumeInfo']['title'],
                                                    'Author' => $googleSearchResult['items'][0]['volumeInfo']['authors'][0],
                                                    'Subject' => $googleSearchResult['items'][0]['volumeInfo']['categories'][0],
                                                    'Action' => 'Add',);

                                            else:
                                                ?>
                                                <div class="warning">Could not find ISBN. Double check the
                                                    number, or enter information manually
                                                </div><br/>
                                            <?php
                                            endif;
                                        endif;
                                    }
                                    ?>
                                    <?php if (isset($populate['ID'])): ?>
                                        <input type="hidden" name="catID" value="<?php echo $populate['ID']; ?>">
                                    <?php endif; ?>
                                    <table>
                                        <tr>
                                            <td>ISBN-13:</td>
                                            <td><input type="text" name="ISBN_13" value="<?php echo ifsetor($populate['ISBN_13']); ?>" maxlength="13" size="13"></td>
                                        </tr>
                                        <tr>
                                            <td>ISBN-10:</td>
                                            <td><input type="text" name="ISBN_10" value="<?php echo ifsetor($populate['ISBN_10']); ?>" maxlength="10" size="10"></td>
                                        </tr>
                                        <tr>
                                            <td>Title:</td>
                                            <td><input type="text" name="title" value="<?php echo ifsetor($populate['Title']); ?>" maxlength="255" size="25"></td>
                                        </tr>
                                        <tr>
                                            <td>Author:</td>
                                            <td><input type="text" name="author" value="<?php echo ifsetor($populate['Author']); ?>" maxlength="255" size="25"></td>
                                        </tr>
                                        <tr>
                                            <td>Subject:</td>
                                            <td><input type="text" name="subject" value="<?php echo ifsetor($populate['Subject']); ?>" maxlength="25" size="25"></td>
                                        </tr>
                                    </table>
                                    <input type="submit" name="action" value="<?php echo ifsetor($populate['Action'], 'Add'); ?>">
                                    <?php if (isset($populate['ID'])): ?><input type="submit" name="action" value="Continue">
                                        <?php
                                    endif;
                                    break;
                                case 'Update':
                                    /*
                                     * Run an update query on the catalog, then display the Continue button.
                                     */
                                    $query = "UPDATE tblCatalog SET ISBN_13='" . $input['ISBN_13'] .
                                            "', ISBN_10='" . $input['ISBN_10'] .
                                            "', Title='" . $input['title'] .
                                            "', Author='" . $input['author'] .
                                            "', Subject='" . $input['subject'] .
                                            "' WHERE ID=" . $input['catID'];
                                    mysqli_query($con, $query);
                                    ?>
                                    <div class="success">Updated <?php echo $input['title']; ?> (catalog id = <?php echo $input['catID']; ?>)</div>
                                    <input type="hidden" name="catID" value="<?php echo $input['catID']; ?>">
                                    <script type="text/javascript">
                                        setTimeout(function()
                                        {
                                            document.getElementById('continue').click();
                                        }, 500);
                                    </script>
                                    <input type="submit" id="continue" name="action" value="Continue">

                                    <?php
                                    break;

                                case 'Add':
                                    /*
                                     * Add the product to the catalog, then get the new catalog ID number
                                     */
                                    $query = "INSERT INTO tblCatalog (ISBN_13, ISBN_10, Title, Author, Subject)"
                                            . "VALUES ('" . $input['ISBN_13'] . "','" .
                                            $input['ISBN_10'] . "','" .
                                            $input['title'] . "','" .
                                            $input['author'] . "','" .
                                            $input['subject'] . "')";
                                    mysqli_query($con, $query);
                                    $result = mysqli_query($con, "SELECT ID FROM tblCatalog WHERE ISBN_10='" . $input['ISBN_10'] .
                                            "'AND ISBN_13='" . $input['ISBN_13'] .
                                            "'AND Title='" . $input['title'] .
                                            "'AND Author='" . $input['author'] . "' LIMIT 1");
                                    if (mysqli_num_rows($result)):
                                        $row = mysqli_fetch_array($result);
                                        ?>
                                        <div class="success">Added <?php echo $input['title']; ?> as Catalog ID <?php echo $row['ID']; ?></div>
                                        <input type="hidden" name="catID" value="<?php echo $row['ID']; ?>">
                                        <script type="text/javascript">
                                            setTimeout(function()
                                            {
                                                document.getElementById('continue').click();
                                            }, 500);

                                        </script>
                                        <input type="submit" id="continue" name="action" value="Continue">

                                    <?php else: ?>
                                        <div class="error">Could not add <?php echo $input['title']; ?></div>
                                    <?php
                                    endif;
                                    break;

                                case 'Continue':
                                    /*
                                     * display form to enter individual product specifics
                                     */

                                    $result = mysqli_query($con, 'SELECT * FROM tblCatalog WHERE ID=' . $input['catID'] . ' LIMIT 1');
                                    if (mysqli_num_rows($result)):
                                        $row = mysqli_fetch_array($result);
                                        ?>
                                        <!--Display catalog information-->
                                        <input type="hidden" name="catID" value="<?php echo $input['catID']; ?>">
                                        <table class="information">
                                            <tr>
                                                <td>Title:</td>
                                                <td><?php echo $row['Title']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Author:</td>
                                                <td><?php echo $row['Author']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Subject:</td>
                                                <td><?php echo $row['Subject']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>ISBN-13:</td>
                                                <td><?php echo $row['ISBN_13']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>ISBN-10:</td>
                                                <td><?php echo $row['ISBN_10']; ?></td>
                                            </tr>
                                        </table>
                                        <hr>
                                        <h3>Enter item specifics</h3>
                                        <table>
                                            <tr>
                                                <td>Cost (NOT sale price)</td>
                                                <td><input type="number" name="cost" step="any" min="0" value="0.00"></td>
                                            </tr>
                                            <tr>
                                                <td>Condition</td>
                                                <td>
                                                    <select name="condition">
                                                        <option value="LikeNew">LikeNew</option>
                                                        <option value="VeryGood">VeryGood</option>
                                                        <option value="Good">Good</option>
                                                        <option value="Acceptable">Acceptable</option>
                                                        <option value="Poor">Poor</option>
                                                        <option value="New">New</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Condition notes</td>
                                                <td><textarea name="conditionNotes" rows="3" maxlength="2000"></textarea></td>
                                            </tr>
                                            
                                        </table>
                                        <input type="submit" name="action" value="Done">
                                    <?php else: ?>
                                        <div class="error">Could not get info for catalog ID <?php echo $input['catID']; ?></div>
                                    <?php
                                    endif;
                                    break;
                                case 'Done':
                                    
                                    $query = "INSERT INTO tblInventory (catalogID, BookCondition, ConditionNotes, Cost)"
                                            . "VALUES (" . $input['catID'] . ",'" .
                                            $input['condition'] . "','" .
                                            $input['conditionNotes'] . "'," .
                                            $input['cost'] . ")";
                                    mysqli_query($con, $query);
                                    $titleQuery = 'SELECT Title FROM tblCatalog WHERE ID=' . $input['catID'] . ' LIMIT 1';
                                    $titleResult = mysqli_query($con, $titleQuery);
                                    $titleRow = mysqli_fetch_array($titleResult);
                                    ?>
                                    <div class="success">Added product to inventory</div>
                                    <h3>Current Holdings for <?php echo $titleRow['Title']; ?></h3>
                                    <table class="inventory">
                                        <tr>
                                            <th>ID</th>
                                            <th>Date Listed</th>
                                            <th>Condition</th>
                                            <th>Condition Notes</th>
                                            <th>Cost</th>
                                        </tr>
                                        <?php
                                        $invQuery = "SELECT * FROM tblInventory WHERE catalogID=" . $input['catID'] . ' AND SoldOn IS NULL';
                                        $invResult = mysqli_query($con, $invQuery);
                                        while ($row = mysqli_fetch_array($invResult)) {
                                            $newRow = '<tr class="invlist">'
                                                    . '<td>'
                                                    . $row['ID']
                                                    . '</td><td>'
                                                    . $row['EnteredOn']
                                                    . '</td><td>'
                                                    . $row['BookCondition']
                                                    . '</td><td>'
                                                    . $row['ConditionNotes']
                                                    . '</td><td>'
                                                    . $row['Cost']
                                                    . '</td>'
                                                    . '</tr>';
                                            echo $newRow;
                                        }
                                        ?>
                                    </table>
                                    <input type="submit" name="action" value="Start Over">

                                    <?php
                                    break;
                                default:
                                    echo 'Enter an ISBN, or press "Lookup" if no ISBN exists.';
                                    break;
                            }
                            ?>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <!--Footer-->
        <?php include 'footer.php'; ?>
    </body>
</html>
