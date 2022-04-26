<?php
// Start the session
if (!isset($_SESSION)) {
    session_start();
}
?>
<html>

<head>
    <style>
        body {
            font-family: Arial Narrow, Arial, sans-serif;
            font-size: 16px;
        }

        .menu {
            width: 600px;
            margin: auto;
            display: flex;
            flex-wrap: nowrap;
            flex-direction: column;
            gap: 0.5em;
            margin-bottom: 0.5em;
        }

        .menu-item,
        .menu-item a {
            height: 2em;
            background-color: #800080;
            color: #fafafa;
            text-decoration: none;
            text-align: center;
            line-height: 2em;
        }
    </style>

</head>

<body>
    <div style='width:600px;margin:auto;'>
    <h2>SurveySYS</h2>
    <div class='menu'>
        <a class='menu-item' href="createSurvey.php">Create survey</a>
        <a class='menu-item' href="editSurvey.php">Edit survey</a>
    </div>
    <footer style='text-align:right'><small><code><a href="https://github.com/HGustavs/SurveySYS">https://github.com/HGustavs/SurveySYS</a></code></small></footer>
    </div>
</body>

</html>