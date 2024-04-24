<?php
$servername = "localhost";
$username = "utente";
$password = "1234";
$dbname = "gestionalemusicale";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) 
{
    http_response_code(500);
    die("Connessione fallita: " . $conn->connect_error);
}

function validateData($data) 
{
    if (
        !isset($data['Nome']) || !is_string($data['Nome']) ||
        !isset($data['Cognome']) || !is_string($data['Cognome']) ||
        !isset($data['Email']) || !filter_var($data['Email']) ||
        !isset($data['Anno']) || !is_numeric($data['Anno']) ||
        !isset($data['Ascolti']) || !strtotime($data['Ascolti'])
    ) 
    {
        return false;
    }
    return true;
}

$array = explode('/', $_SERVER['REQUEST_URI']);
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') 
{
    if (count($array) == 3 && $array[2] != '') 
    {
        $id = $array[2];
        $sql = "SELECT * FROM dati WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) 
        {
            $row = $result->fetch_assoc();
            http_response_code(200);
            echo json_encode($row);
        } 
        else 
        {
            http_response_code(404);
        }
    } 
    elseif (count($array) == 3 && $array[2] == '') 
    {
        $sql = "SELECT * FROM dati";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) 
        {
            $rows = array();
            while ($row = $result->fetch_assoc()) 
            {
                $rows[] = $row;
            }
            http_response_code(200);
            echo json_encode($rows);
        } 
        else 
        {
            http_response_code(404);
        }
    } 
    else 
    {
        http_response_code(405);
    }
} 
elseif ($method == 'POST') 
{
    $data = json_decode(file_get_contents("php://input"), true);
    if (!empty($data) && validateData($data)) 
    {
        $sql = "INSERT INTO dati (Nome, Cognome, Email, Anno, Ascolti) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssis", $data['Nome'], $data['Cognome'], $data['Email'], $data['Anno'], $data['Ascolti']);

        if ($stmt->execute()) 
        {
            if ($stmt->affected_rows > 0) 
            {
                http_response_code(200);
            } 
            else 
            {
                http_response_code(404);
            }
        } 
        else 
        {
            http_response_code(500);
        }
    } 
    else 
    {
        http_response_code(400);
    }
} 
elseif ($method == 'PUT') 
{
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (count($array) == 3 && $array[2] != '') 
    {
        $id = $array[2];
        
        if (!empty($data) && validateData($data)) 
        {
            $sql = "UPDATE dati SET Nome=?, Cognome=?, Email=?, Anno=?, Ascolti=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $data['Nome'], $data['Cognome'], $data['Email'], $data['Anno'], $data['Ascolti'], $id);

            if ($stmt->execute()) 
            {
                if ($stmt->affected_rows > 0) 
                {
                    http_response_code(200);
                } 
                else 
                {
                    http_response_code(404);
                }
            } 
            else 
            {
                http_response_code(500);
            }
        } 
        else 
        {
            http_response_code(400);
        }
    } 
    else 
    {
        http_response_code(400);
    }
} 
elseif ($method == 'DELETE') 
{
    if (count($array) == 3 && $array[2] != '') 
    {
        $id = $array[2];
        
        $sql = "DELETE FROM dati WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) 
        {
            if ($stmt->affected_rows > 0) 
            {
                http_response_code(200);
            } 
            else 
            {
                http_response_code(404);
            }
        } 
        else 
        {
            http_response_code(500);
        }
    } 
    else 
    {
        http_response_code(400);
    }
} 
elseif ($method == 'OPTIONS') 
{
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    
    http_response_code(200);
} 
else 
{
    http_response_code(405);
}

$conn->close();
?>
