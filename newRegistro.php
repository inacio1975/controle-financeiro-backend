<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
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

// DATA FORM REQUEST
$data = json_decode(file_get_contents("php://input"));

$returnData = [];

if ($_SERVER["REQUEST_METHOD"] != "POST") :
    $returnData = msg(0, 404, 'Page Not Found!');
elseif (
    !isset($data->id)
    || !isset($data->descricao)
    || !isset($data->valor)
    || empty(trim($data->esaida))
) :

    $fields = ['fields' => ['id', 'descricao', 'valor', 'esaida'], 'extra' => json_decode(file_get_contents("php://input"))];
    $returnData = msg(0, 422, 'Por favor, Indique todos os campos obrigatórios!', $fields);

// IF THERE ARE NO EMPTY FIELDS THEN
else :

    $id = trim($data->id);
    $descricao = trim($data->descricao);
    $valor = trim($data->valor);
    $esaida = trim($data->esaida);

    $responseCode = 0;
    try {

        $registro = "SELECT `id` FROM `registros` WHERE `id`=:id";
        $registro_stmt = $conn->prepare($registro);
        $registro_stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $registro_stmt->execute();

        if ($registro_stmt->rowCount()) :
            $returnData = msg(0, 409, 'Foi encontrado um registro igual na base de dados!');
            $responseCode = 409;
        else :
            $insert_query = "INSERT INTO `registros` (`id`, `descricao`,`valor`,`esaida`) VALUES(:id,:descricao,:valor,:esaida)";

            $insert_stmt = $conn->prepare($insert_query);

            // DATA BINDING
            $insert_stmt->bindValue(':id', htmlspecialchars(strip_tags($id)), PDO::PARAM_STR);
            $insert_stmt->bindValue(':descricao', htmlspecialchars(strip_tags($descricao)), PDO::PARAM_STR);
            $insert_stmt->bindValue(':valor', htmlspecialchars(strip_tags($valor)), PDO::PARAM_STR);
            $insert_stmt->bindValue(':esaida', htmlspecialchars(strip_tags($esaida)), PDO::PARAM_INT);

            $insert_stmt->execute();

            $returnData = msg(1, 201, 'Registro inserido com sucesso na base de dados');
            $responseCode = 201;

        endif;
    } catch (PDOException $e) {
        $returnData = msg(0, 500, $e->getMessage());
        $responseCode = 500;
    }
    http_response_code($responseCode);
endif;
echo json_encode($returnData);
