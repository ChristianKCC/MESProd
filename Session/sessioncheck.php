<?php
session_start();
if (!isset($_SESSION["autentica"]) || $_SESSION["autentica"] != "SIP") {
    echo json_encode(["status" => "expired"]);
} else {
    echo json_encode(["status" => "active"]);
}
?>