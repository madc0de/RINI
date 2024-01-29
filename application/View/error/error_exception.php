<?php
defined('APP') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Uncaught Exception was encountered</title>
	<style>
		.error {
			border:1px solid #990000;
			padding-left:20px;
			margin:10px;
		}

		li {
			margin:10px 15px
		}
	</style>
</head>
<body>
<div class="error">

<h2>An uncaught Exception was encountered</h2>

<p>Type: <?php echo get_class($exception); ?></p>
<p>Message: <strong><?php echo $message; ?></strong></p>
<p>Filename: <?php echo $exception->getFile(); ?></p>
<p>Line Number: <?php echo $exception->getLine(); ?></p>
<h3>Backtrace:</h3>

	<ul>
	<?php foreach ($exception->getTrace() as $error): ?>

		<?php if (isset($error['file'])): ?>

			<li>File: <?php echo str_replace(realpath(ROOT), '', $error['file']); ?></li>
			<ul>
				
				<li>Line: <?php echo $error['line']; ?></li>
				<li>Function: <strong><?php echo $error['function']; ?></strong></li>
			</ul>
		<?php endif ?>

	<?php endforeach ?>
	</ul>

</div>
</body>
</html>