<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SourceCode - Premium Code Templates & Projects</title>
    <meta name="description" content="Download premium source code, templates, and complete projects for your web development needs.">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        .bg-primary {
            background-color: #1e40af;
        }
        .text-primary {
            color: #1e40af;
        }
        .border-primary {
            border-color: #1e40af;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-gray-50">
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-grow">
