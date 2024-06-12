<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_meal'])) {
        $meal_name = $_POST['meal_name'];
        $meal_type = $_POST['meal_type'];
        $description = $_POST['description'];

        $stmt = $conn->prepare("INSERT INTO meals (meal_name, meal_type, description, user_id) VALUES (:meal_name, :meal_type, :description, :user_id)");
        $stmt->bindParam(':meal_name', $meal_name);
        $stmt->bindParam(':meal_type', $meal_type);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            $message = "Meal added successfully!";
        } else {
            $message = "Error: " . $stmt->errorInfo()[2];
        }
    } elseif (isset($_POST['update_meal'])) {
        $meal_id = $_POST['meal_id'];
        $meal_name = $_POST['meal_name'];
        $meal_type = $_POST['meal_type'];
        $description = $_POST['description'];

        $stmt = $conn->prepare("UPDATE meals SET meal_name = :meal_name, meal_type = :meal_type, description = :description WHERE meal_id = :meal_id AND user_id = :user_id");
        $stmt->bindParam(':meal_name', $meal_name);
        $stmt->bindParam(':meal_type', $meal_type);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':meal_id', $meal_id);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            $message = "Meal updated successfully!";
        } else {
            $message = "Error: " . $stmt->errorInfo()[2];
        }
    } elseif (isset($_POST['delete_meal'])) {
        $meal_id = $_POST['meal_id'];

        $stmt = $conn->prepare("DELETE FROM meals WHERE meal_id = :meal_id AND user_id = :user_id");
        $stmt->bindParam(':meal_id', $meal_id);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            $message = "Meal deleted successfully!";
        } else {
            $message = "Error: " . $stmt->errorInfo()[2];
        }
    }
}

$meals = $conn->prepare("SELECT * FROM meals WHERE user_id = :user_id");
$meals->bindParam(':user_id', $user_id);
$meals->execute();
$meal_list = $meals->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Meal Planner</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Meal Planner</h2>
        <?php if (isset($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="post" action="meal_planner.php">
            <div class="form-group">
                <label>Meal Name:</label>
                <input type="text" name="meal_name" required>
            </div>
            <div class="form-group">
                <label>Meal Type:</label>
                <input type="text" name="meal_type" required>
            </div>
            <div class="form-group">
                <label>Description:</label>
                <textarea name="description"></textarea>
            </div>
            <button type="submit" name="add_meal">Add Meal</button>
        </form>

        <h3>Your Meals</h3>
        <ul>
            <?php foreach ($meal_list as $meal): ?>
                <li>
                    <strong><?php echo htmlspecialchars($meal['meal_name']); ?></strong>
                    (<?php echo htmlspecialchars($meal['meal_type']); ?>) - 
                    <?php echo htmlspecialchars($meal['description']); ?>
                    <form method="post" action="meal_planner.php" style="display:inline;">
                        <input type="hidden" name="meal_id" value="<?php echo $meal['meal_id']; ?>">
                        <button type="submit" name="edit_meal">Edit</button>
                        <button type="submit" name="delete_meal">Delete</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (isset($_POST['edit_meal'])): 
            $meal_id = $_POST['meal_id'];
            $stmt = $conn->prepare("SELECT * FROM meals WHERE meal_id = :meal_id AND user_id = :user_id");
            $stmt->bindParam(':meal_id', $meal_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $meal = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
            <h3>Edit Meal</h3>
            <form method="post" action="meal_planner.php">
                <input type="hidden" name="meal_id" value="<?php echo $meal['meal_id']; ?>">
                <div class="form-group">
                    <label>Meal Name:</label>
                    <input type="text" name="meal_name" value="<?php echo htmlspecialchars($meal['meal_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Meal Type:</label>
                    <input type="text" name="meal_type" value="<?php echo htmlspecialchars($meal['meal_type']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <textarea name="description"><?php echo htmlspecialchars($meal['description']); ?></textarea>
                </div>
                <button type="submit" name="update_meal">Update Meal</button>
            </form>
        <?php endif; ?>

        <form action="logout.php" method="post">
            <button type="submit">Logout</button>
        </form>
    </div>
</body>
</html>
