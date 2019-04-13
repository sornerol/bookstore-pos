<!DOCTYPE html>
<?php
include 'config.php';
include 'functions.php';
$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

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
                <!--Main page menu-->
                <td id="topmenu"><?php include 'topmenu.php'; ?></td>
            </tr>
            <tr>
                <td id="content">
                    <?php if (mysqli_connect_errno($con)): //check to make sure the MySQL connection succeeded?>
                        <div class="error">ERROR: Could not connect to MySQL database.</div>
                    <?php else: ?>
                        <!--Start page content-->

                    

                        <!--End page content-->
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <!--Footer-->
        <?php include 'footer.php'; ?>
    </body>
</html>
