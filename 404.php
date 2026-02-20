<?php
// Optional: theme constant guard
if (!defined('THEAM')) {
    define('THEAM', 'default');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <style>
        /* Basic reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: #f8f8f8;
            color: #333;
        }

        .container {
            text-align: center;
        }

        h1 {
            font-size: 10rem;
            color: #ff4c4c;
        }

        h2 {
            font-size: 2rem;
            margin: 20px 0;
        }

        p {
            font-size: 1rem;
            margin-bottom: 30px;
            color: #666;
        }

        a {
            display: inline-block;
            padding: 12px 30px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s ease;
        }

        a:hover {
            background: #0056b3;
        }

        @media(max-width: 600px){
            h1 {
                font-size: 6rem;
            }
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>404</h1>
        <h2>Oops! Page not found</h2>
        <p>We can't seem to find the page you're looking for.</p>
        <a href="/">Go Back Home</a>
    </div>
</body>
</html>