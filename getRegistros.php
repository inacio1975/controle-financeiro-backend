<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__ . '/configuration/Database.php';
$db_connection = new Database();
$conn = $db_connection->dbConnection();

function msg($success, $status, $message, $extra = [])
{
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ], $extra);
}

$returnData = [];

if ($_SERVER["REQUEST_METHOD"] != "GET") :
    $returnData = msg(0, 404, 'Page Not Found!');
else :
    $responseCode = 0;
        try {

            $check_conv = "SELECT * FROM `registros`";
            $check_conv_stmt = $conn->prepare($check_conv);
            $check_conv_stmt->execute();

            if ($check_conv_stmt->rowCount()) :
                $returnData = ['success' => 1, 'elementos' => $check_conv_stmt->fetchAll(PDO::FETCH_ASSOC)];//msg(1, 302,"Guest Founded", $check_conv_stmt->fetchObject());
                $responseCode = 200;
            else :
                $returnData = msg(0, 404, 'Guest not founded');
                $responseCode = 404;

            endif;
        } catch (PDOException $e) {
            $returnData = msg(0, 500, $e->getMessage());
            $responseCode = 500;
        }
        http_response_code($responseCode);
    //endif;
endif;
echo json_encode($returnData);
