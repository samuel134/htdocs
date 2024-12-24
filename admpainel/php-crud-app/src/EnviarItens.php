<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $itemID = $_POST['item_id'];
    $senderUID = $_POST['sender_uid'];
    $receiverUID = $_POST['receiver_uid'];

    // Validate input
    if (!empty($itemID) && !empty($senderUID) && !empty($receiverUID)) {
        // Prepare SQL statement to transfer item
        $sql = "INSERT INTO ItemTransfers (ItemID, SenderUID, ReceiverUID) VALUES (?, ?, ?)";
        $stmt = $conn1->prepare($sql);
        
        if ($stmt->execute([$itemID, $senderUID, $receiverUID])) {
            echo "Item sent successfully!";
        } else {
            echo "Error sending item.";
        }
    } else {
        echo "All fields are required.";
    }
}

// Fetch items for the form
$sql = "SELECT * FROM Items";
$stmt = $conn1->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Itens</title>
</head>
<body>
    <h1>Enviar Itens</h1>
    <form method="POST" action="">
        <label for="item_id">Select Item:</label>
        <select name="item_id" id="item_id" required>
            <?php foreach ($items as $item): ?>
                <option value="<?= $item['ID'] ?>"><?= $item['Name'] ?></option>
            <?php endforeach; ?>
        </select>
        <br>

        <label for="sender_uid">Sender User UID:</label>
        <input type="text" name="sender_uid" id="sender_uid" required>
        <br>

        <label for="receiver_uid">Receiver User UID:</label>
        <input type="text" name="receiver_uid" id="receiver_uid" required>
        <br>

        <input type="submit" value="Send Item">
    </form>
</body>
</html>