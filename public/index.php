<?php
if($_SERVER['REQUEST_URI'] == '/'){
    header("Location: accounts/index.php");
    exit();
}
