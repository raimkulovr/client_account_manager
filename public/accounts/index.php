<?php
include_once '../../header.php';
$limit = 100;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare(<<<SQL
SELECT 
    c.*, 
    comp.name AS company_name 
FROM clients AS c 
LEFT JOIN companies AS comp ON c.company_id = comp.id 
LIMIT :limit OFFSET :offset 
SQL);

$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            $addButtons = fn(int $id) => <<< HTML
                <td>
                    <a href="edit.php?id=$id" class="text-warning me-2"><i class="bi bi-pencil-fill"></i></a>
                    <a href="delete.php?id=$id" class="text-danger" onclick="return confirm('Удалить аккаунт?');"><i class="bi bi-trash-fill"></i></a>
                </td>
                HTML;

            foreach ($clients as $client) {

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
                $tr .= "<td>" . $client['id'] . "</td>";
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