<?php Header("Location: " . $url, true, 303) ?>
<html>
<head>
<title>See Other</title>
<meta http-equiv="refresh" content="0;url=<?php echo htmlspecialchars($url) ?>" />
</head>
<body>
<h1>See Other</h1>
<p>Please see <a href="<?php echo htmlspecialchars($url) ?>"><?php echo htmlspecialchars($url) ?></a> for the results of this action.</p>
</body>
</html>
