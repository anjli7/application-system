<?php
session_start();
require_once '../php/auth.php';
require_once '../php/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Check if article ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid article ID';
    header('Location: manage_articles.php');
    exit();
}

$article_id = (int)$_GET['id'];

// Get the image filename before deleting the article
$query = "SELECT image FROM articles WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();

if ($article) {
    // Delete the article
    $query = "DELETE FROM articles WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $article_id);
    
    if ($stmt->execute()) {
        // Delete the associated image file
        if (!empty($article['image']) && file_exists("../uploads/" . $article['image'])) {
            unlink("../uploads/" . $article['image']);
        }
        $_SESSION['success'] = 'Article deleted successfully';
    } else {
        $_SESSION['error'] = 'Error deleting article';
    }
} else {
    $_SESSION['error'] = 'Article not found';
}

// Close statement
$stmt->close();

// Redirect back to manage articles page
header('Location: manage_articles.php');
exit();
?>
