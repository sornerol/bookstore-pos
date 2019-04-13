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
                        <h2>Edit Catalog Item Details</h2>
                        <?php if(!isset($input['catID'])):
                            echo '<div class="error">ERROR: You must specify a catalog item ID to edit.</div>';
                        else:
                            $catQuery='SELECT * FROM tblCatalog WHERE ID = '.$input['catID'].' LIMIT 1';
                            //echo 'DEBUG: '.$catQuery;
                            $catResult = mysqli_query($con, $catQuery);
                            if (!mysqli_num_rows($catResult))
                            {
                                echo '<div class="error">ERROR: Could not find catalog ID'.$input['catID'].'.</div>';
                            }
                            else
                            {
                                $catRow=  mysqli_fetch_array($catResult);
                            }
                            
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
<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

