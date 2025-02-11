<?php
const DROPDOWN_TABS = [
	["accounts/index.php", "Список", [["accounts/index.php", "клиентов"],]],
	["accounts/write.php", "Добавить", [["accounts/write.php", "клиента"],]],
];

require_once 'config.php';
require_once 'db_config.php';

function renderDropdownNavbarItemList(): string
{
	$result = "";

	foreach (DROPDOWN_TABS as $tab) {
		$href = $tab[0];
		$name = $tab[1];
		$menu_items = $tab[2];
		
		$result .= "<li class=\"nav-item dropdown\"><a role=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\" class=\"nav-link dropdown-toggle\"";
		$result .= "href=\"" . $href . "\">$name</a>";
		$result .= "<ul class=\"dropdown-menu\">";
			foreach($menu_items as $item) {
				$item_href = $item[0];
				$item_name = $item[1];
				$result .= "<li><a ";
				$result .= "href=\"" . $item_href . "\" ";
				$result .= "class=\"dropdown-item\"";
				$result .= ">$item_name</a></li>";
			}
		$result .= "</ul></li>";
	}
	return $result;
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="utf-8">
	<meta name="description" content="Simple CRUD demonstrator." />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<base href="<?php echo BASE_URL ?>">
	<title>Client Account Manager</title>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<style>
		.navbar-brand {
			padding-top: 0;
		}
	</style>
</head>

<body class="d-flex flex-column min-vh-100">
	<header>
		<nav class="navbar navbar-expand-lg bg-body-tertiary">
			<div class="container">
				<a class="navbar-brand" href="#">Zimalab Accounts</a>
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav me-auto mb-2 mb-lg-0">
						<?php echo renderDropdownNavbarItemList(); ?>
					</ul>
					<a href="https://github.com/raimkulovr/client_account_manager" target="_blank"><i class="bi bi-github"></i></a>
				</div>
			</div>
		</nav>
	</header>