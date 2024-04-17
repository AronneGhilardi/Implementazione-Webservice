<?php
$servername = "localhost";
$username = "utente";
$password = "1234";
$dbname = "gestionalemusicale";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica della connessione
if ($conn->connect_error) 
{
    die("ERRORE! Connessione fallita: " . $conn->connect_error);
}

// Validazione dei dati in input
function validateData($data) 
{
    if 
    (
        !isset($data['Nome']) || !is_string($data['Nome']) ||
        !isset($data['Cognome']) || !is_string($data['Cognome']) ||
        !isset($data['Email']) || !filter_var($data['Email'], FILTER_VALIDATE_EMAIL) ||
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
        // Se è specificato un ID nella richiesta GET
        $id = $array[2];
        $sql = "SELECT * FROM dati WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) 
        {
            $row = $result->fetch_assoc();
            echo json_encode($row);
        } 
        else 
        {
            echo "ERRORE! Nessun risultato trovato con ID $id";
        }
    } 
    elseif (count($array) == 3 && $array[2] == '') 
    {
        // Se non è specificato un ID nella richiesta GET
        $sql = "SELECT * FROM dati";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) 
        {
            $rows = array();
            while ($row = $result->fetch_assoc()) 
            {
                $rows[] = $row;
            }
            echo json_encode($rows);
        } 
        else 
        {
            echo "ERRORE! Nessun risultato trovato nella tabella.";
        }
    } 
    else 
    {
        // Se il metodo HTTP non è GET
        http_response_code(405); // Metodo non consentito
        echo "ERRORE! Metodo non consentito";
    }
} 
elseif ($method == 'POST') 
{
    // Esegui l'inserimento dei dati
    $data = json_decode(file_get_contents("php://input"), true);

    // Verifica se i dati sono stati inviati correttamente
    if (!empty($data) && validateData($data)) 
    {
        // Esegui l'inserimento nel database
        $sql = "INSERT INTO dati (Nome, Cognome, Email, Anno, Ascolti) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssis", $data['Nome'], $data['Cognome'], $data['Email'], $data['Anno'], $data['Ascolti']);

        if ($stmt->execute()) 
        {
            echo "Dati inseriti con successo.";
        } 
        else 
        {
            echo "Errore durante l'inserimento dei dati.";
        }
    } 
    else 
    {
        echo "Dati non validi.";
    }
} 
elseif ($method == 'PUT') 
{
    // Esegui l'aggiornamento dei dati
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Verifica se l'ID è stato fornito
    if (count($array) == 3 && $array[2] != '') 
    {
        $id = $array[2];
        
        // Esegui l'aggiornamento nel database
        if (!empty($data) && validateData($data)) 
        {
            $sql = "UPDATE dati SET Nome=?, Cognome=?, Email=?, Anno=?, Ascolti=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $data['Nome'], $data['Cognome'], $data['Email'], $data['Anno'], $data['Ascolti'], $id);

            if ($stmt->execute()) 
            {
                echo "Dati aggiornati con successo.";
            } 
            else 
            {
                echo "Errore durante l'aggiornamento dei dati.";
            }
        } 
        else 
        {
            echo "ERRORE! Dati non validi.";
        }
    } 
    else 
    {
        echo "ERRORE! ID non specificato.";
    }
} 
elseif ($method == 'DELETE') 
{
    // Esegui la cancellazione dei dati
    if (count($array) == 3 && $array[2] != '') 
    {
        $id = $array[2];
        
        // Esegui la cancellazione nel database
        $sql = "DELETE FROM dati WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) 
        {
            echo "Dati cancellati con successo.";
        } else 
        {
            echo "Errore durante la cancellazione dei dati.";
        }
    } 
    else 
    {
        echo "ERRORE! ID non specificato.";
    }
} 
else 
{
    // Se il metodo HTTP non è supportato
    http_response_code(405); // Metodo non consentito
    echo "ERRORE! Metodo non consentito";
}

$conn->close();

?>
