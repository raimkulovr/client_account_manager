<?php
const TABS = [
	["index.php", "Список"],
	["add_account.php", "Добавить"],
];

require_once 'db-config.php';

function isActivePage($page)
{
	return basename($_SERVER['PHP_SELF']) == $page ? 'active' : '';
}

function renderNavigationTabList(): string
{
	$result = "";
	foreach (TABS as $tab) {
		$href = $tab[0];
		$name = $tab[1];

		// open tags
		$result .= "<li class=\"nav-item\"><a ";

		// apply <a> tag attributes
		$result .= "href=\"" . $href . "\" ";
		$result .= "class=\"nav-link " . isActivePage($href) . "\" ";
		$result .= isActivePage($href) ? "aria-current=\"page\"" : "";

		// add <a> tag text
		$result .= ">$name";

		// close tags
		$result .= "</a></li>";
	}
	return $result;
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="utf-8">
	<meta name="description" content="A short description." />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Client Account Manager</title>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

	<style>
		.navbar-brand {
			padding-top: 0;
		}
	</style>
</head>

<body>
	<header>
		<nav class="navbar navbar-expand-lg bg-body-tertiary">
			<div class="container">
				<a class="navbar-brand" href="#">Zimalab Accounts</a>
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav me-auto mb-2 mb-lg-0">
						<?php echo renderNavigationTabList(); ?>
					</ul>
					<form class="d-flex mb-2 mb-lg-0" role="search">
						<input class="form-control me-2" type="search" placeholder="Поиск" aria-label="Поиск">
					</form>
					<button class="btn btn-primary" type="submit">Войти</button>
				</div>
			</div>
		</nav>
	</header>