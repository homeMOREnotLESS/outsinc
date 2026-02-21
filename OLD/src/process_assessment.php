<?php
header('Content-Type: application/json');
$pdo = include __DIR__ . '/db.php';

// Very small stub: accept POST with client_id and up to q1..q60
$input = $_POST;
if (empty($input['client_id'])) {
    echo json_encode(['error' => 'client_id required']);
    exit;
}
$client_id = (int)$input['client_id'];

$fields = [];
$params = [':client_id' => $client_id];
for ($i = 1; $i <= 60; $i++) {
    $k = 'q' . $i;
    if (isset($input[$k])) {
        $fields[] = "$k = :$k";
        $params[":$k"] = $input[$k];
    }
}

if (empty($fields)) {
    // nothing to save, create an empty row with timestamp
    $stmt = $pdo->prepare('INSERT INTO intake_responses (client_id) VALUES (:client_id)');
    $stmt->execute([':client_id' => $client_id]);
    echo json_encode(['ok' => true, 'message' => 'created empty intake response']);
    exit;
}

$set = implode(', ', $fields);
$sql = "INSERT INTO intake_responses (client_id, $set) VALUES (:client_id, " . implode(', ', array_map(function($n){ return ':' . $n; }, array_map(function($f){ return explode(' = ', $f)[0]; }, $fields))) . ")";
// The above building is a bit complex; simpler approach: use explicit columns
$columns = ['client_id'];
$placeholders = [':client_id'];
foreach ($fields as $f) {
    $col = explode(' = ', $f)[0];
    $columns[] = $col;
    $placeholders[] = ':' . $col;
}
$sql = 'INSERT INTO intake_responses (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode(['ok' => true, 'response_id' => $pdo->lastInsertId()]);
