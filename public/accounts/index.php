<?php
include_once '../../header.php';

$current_url = BASE_URL . "accounts/index.php";

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = $page < 1 ? 1 : $page;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare(<<<SQL
SELECT 
    c.*, 
    comp.name AS company_name 
FROM clients AS c 
LEFT JOIN companies AS comp ON c.company_id = comp.id 
LIMIT :limit OFFSET :offset 
SQL);

$stmt->execute([
    ':limit' => $limit,
    ':offset' => $offset,
]);

$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM clients");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$clients_total = $row['total'];
$max_page = ceil($clients_total / $limit);
if ($page > $max_page) {
    header("Location: " . $current_url . "?page=$max_page");
    exit();
}

function calculateNumber(int $index)
{
    global $page, $limit;
    return ($page - 1) * $limit + $index + 1;
}

function renderPageButton(int $page_int)
{
    global $page, $current_url;
    echo "<li class=\"page-item\"><a class=\"page-link " . ($page == $page_int ? "active" : "") . "\" href=\"" . $current_url . "?page=$page_int" . "\">$page_int</a></li>";
}
?>

<style>
    table td {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
<div class="container flex-grow-1">
    <h1 class="display-6 py-3">Список аккаунтов</h1>
    <div class="d-flex justify-content-between align-items-center my-3">
        <p class="text-secondary mb-0">Показ <?php echo calculateNumber(0) ?>-<?php echo calculateNumber(array_key_last($clients) ?? 0) ?> из <?php echo $clients_total ?> аккаунтов</p>
        <nav aria-label="Page navigation example">
            <ul class="pagination mb-0">
                <li class="page-item <?php if ($page == 1) echo "disabled"; ?>">
                    <a class="page-link" href="<?php echo $current_url . "?page=" . $page-1?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php renderPageButton(1) ?>
                <?php
                $range_start = max(2, $page - 3);
                $range_end = min($max_page - 1, $page + 3);

                for ($i = $range_start; $i <= $range_end; $i++) {
                    renderPageButton($i);
                }
                ?>
                <?php if ($max_page > 1) renderPageButton($max_page) ?>
                <li class="page-item <?php if ($page == $max_page) echo "disabled"; ?>">
                    <a class="page-link" href="<?php echo $current_url . "?page=" . $page+1?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <table class="table table-striped table-hover">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>ФИО</th>
                <th>Email</th>
                <th>Должность</th>
                <th>Компания</th>
                <th>Телефон</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $url = BASE_URL . "accounts/";
            $addButtons = fn(int $id) => <<< HTML
                <td>
                    <a href="{$url}write.php?edit_id=$id" class="text-warning me-2"><i class="bi bi-pencil-fill"></i></a>
                    <a href="{$url}delete.php?delete_id=$id" class="text-danger" onclick="return confirm('Удалить аккаунт?');"><i class="bi bi-trash-fill"></i></a>
                </td>
                HTML;

            foreach ($clients as $key => $client) {

                $phoneNumbers = array_filter([
                    $client['phone_number1'] ?? null,
                    $client['phone_number2'] ?? null,
                    $client['phone_number3'] ?? null
                ]);

                $phoneNumbers = array_map(
                    fn($phoneNumber)
                    => '<a href="tel:' . $phoneNumber . '">' . $phoneNumber . '</a>',
                    $phoneNumbers
                );

                $tr = "<tr>";
                $tr .= "<td>" . calculateNumber($key) . "</td>";
                $tr .= "<td>" . $client['last_name'] . " " . $client['first_name'] . "</td>";
                $tr .= "<td>" . "<a href=\"mailto:" . $client['email'] . "\">" . $client['email'] . "</a></td>";
                $tr .= "<td>" . substr($client['position'], 0, 40) . "</td>";
                $tr .= "<td>" . $client['company_name'] . "</td>";
                $tr .= "<td>" . implode('<br>', $phoneNumbers) . "</td>";
                $tr .= $addButtons($client['id']);
                $tr .= "</tr>";
                echo $tr;
            }
            ?>
        </tbody>
    </table>
</div>

<?php include_once '../../footer.php'; ?>