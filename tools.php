<?php
require_once './database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Origin: *');

$conn = connectDB();

// Determinar el método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];
$path = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$id = isset($path[0]) ? intval($path[0]) : null;

// Manejo de solicitudes
switch ($method) {
    case 'GET':
        if ($id) {
            // Obtener una herramienta por ID
            $stmt = $conn->prepare("SELECT * FROM tools WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($result ?: ['message' => 'Tool not found']);
        } else {
            // Obtener todas las herramientas
            $stmt = $conn->query("SELECT * FROM tools");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        }
        break;

    case 'POST':
        // Crear una nueva herramienta
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("INSERT INTO tools (name, price, stock) VALUES (:name, :price, :stock)");
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':stock', $data['stock']);
        if ($stmt->execute()) {
            echo json_encode(['message' => 'Tool created successfully']);
        } else {
            echo json_encode(['message' => 'Failed to create tool']);
        }
        break;

    case 'PUT':
        if ($id) {
            // Actualizar una herramienta existente
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $conn->prepare("UPDATE tools SET name = :name, price = :price, stock = :stock WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':stock', $data['stock']);
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Tool updated successfully']);
            } else {
                echo json_encode(['message' => 'Failed to update tool']);
            }
        } else {
            echo json_encode(['message' => 'Tool ID is required']);
        }
        break;

    case 'DELETE':
        if ($id) {
            // Eliminar una herramienta
            $stmt = $conn->prepare("DELETE FROM tools WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Tool deleted successfully']);
            } else {
                echo json_encode(['message' => 'Failed to delete tool']);
            }
        } else {
            echo json_encode(['message' => 'Tool ID is required']);
        }
        break;

    default:
        echo json_encode(['message' => 'Method not supported']);
        break;
}
