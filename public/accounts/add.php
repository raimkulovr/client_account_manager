<?php
include '../../header.php';

class OperationResult
{
    function __construct(public bool $success, public string $message = "") {}
}

if ($_POST) {
    $result = null;
    $first_name = $_POST['first_name'] ?? null;
    $last_name = $_POST['last_name'] ?? null;
    $email = $_POST['email'] ?? null;
    $company_id = $_POST['company_id'] ?? null;
    $company_name = $_POST['company_name'] ?? null;
    $position = $_POST['position'] ?? null;
    $phone_number1 = $_POST['phone_number1'] ?? null;
    $phone_number2 = $_POST['phone_number2'] ?? null;
    $phone_number3 = $_POST['phone_number3'] ?? null;

    if ($first_name  && $last_name && $email) {
        if($company_id === "new" && $company_name){
            $sql = "INSERT INTO companies (name) VALUES (:name)";
            $stmt = $pdo->prepare($sql); 
            try{
                $stmt->execute([
                    ':name' => htmlspecialchars($company_name, ENT_QUOTES, "UTF-8"),
                ]);
                $company_id = $pdo->lastInsertId();
            } catch (Throwable $e) {
                $company_id = null;
            }
        }

        $sql = "INSERT INTO clients (first_name, last_name, email, company_id, position, phone_number1, phone_number2, phone_number3) 
        VALUES (:first_name, :last_name, :email, :company_id, :position, :phone_number1, :phone_number2, :phone_number3)";
        $stmt = $pdo->prepare($sql);
        try {
            $company_id = (int) $company_id;
            $company_id = ($company_id > 0) ? $company_id : null;

            $stmt->execute([
                ':first_name' => htmlspecialchars($first_name, ENT_QUOTES, "UTF-8"),
                ':last_name' => htmlspecialchars($last_name, ENT_QUOTES, "UTF-8"),
                ':email' => htmlspecialchars($email, ENT_QUOTES, "UTF-8"),
                ':company_id' => $company_id,
                ':position' => htmlspecialchars($position, ENT_QUOTES, "UTF-8"),
                ':phone_number1' => htmlspecialchars($phone_number1, ENT_QUOTES, "UTF-8"),
                ':phone_number2' => htmlspecialchars($phone_number2, ENT_QUOTES, "UTF-8"),
                ':phone_number3' => htmlspecialchars($phone_number3, ENT_QUOTES, "UTF-8"),
            ]);
            $result = new OperationResult($success = true);
        } catch (Throwable $e) {
            if ($e->getCode() == '23000' && strpos($e->getMessage(), '1062') !== false) {
                $result = new OperationResult($success = false, "Запись с таким Email уже существует.");
            } else {
                $result = new OperationResult($success = false, "Что-то пошло не так.");
            }
        }
    }
    $_POST = [];
}

$stmt = $pdo->query(<<<SQL
SELECT *
FROM companies
SQL);

$companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

function renderCompaniesOptions()
{
    global $companies;
    $result = "";

    foreach ($companies as $raw_company) {
        $company = array_map(fn($entry) => htmlspecialchars((string)$entry, ENT_QUOTES, 'UTF-8'), $raw_company);
        $result .= "<option ";
        $result .= "value=\"" . $company['id'] . "\">";
        $result .= $company['name'];
        $result .= "</option>";
    }

    echo $result;
}

?>

<script>
    function handleSelection(select) {
        const value = select.value;
        const newInputDiv = document.getElementById("company_name_div");
        const newInput = document.getElementById("company_name");

        if (value === "new") {
            newInputDiv.style.display = "block";
            newInput.focus();
        } else {
            newInputDiv.style.display = "none";
        }
    }
</script>
<div class="container flex-grow-1">
    <h1 class="display-6 py-3">Добавить аккаунт</h1>
    <p class="text-secondary">Поля отмечанные "*" обязательны к заполнению</p>
    <?php
        if(isset($result->success)){
            $success = $result->success;
            $message = $result->message;
            if ($success) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Готово!</strong> Запись успешно сохранена.
                </div>';
            } else {
                echo "<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
                    <strong>Ошибка!</strong> $message
                </div>";
            }
        }
    ?>
    <form class="row g-3" method="post">
        <div class="col-md-6">
            <label for="first_name" class="form-label">Имя*</label>
            <input id="first_name" type="text" class="form-control" name="first_name" placeholder="Имя" maxlength=100 required>
        </div>
        <div class="col-md-6">
            <label for="last_name" class="form-label">Фамилия*</label>
            <input id="last_name" type="text" class="form-control" name="last_name" placeholder="Фамилия" maxlength=100 required>
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">Email*</label>
            <input id="email" type="email" class="form-control" name="email" placeholder="test@example.com" maxlength=255 required>
        </div>
        <div class="col-md-6">
            <label for="position" class="form-label">Должность</label>
            <input id="position" type="text" class="form-control" name="position" placeholder="Менеджер" maxlength=255>
        </div>
        <div class="col-md-4">
            <label for="company_id" class="form-label">Компания</label>
            <select id="company_id" name="company_id" class="form-select" onchange="handleSelection(this)">
                <option value="">-- Выберите компанию --</option>
                <?php renderCompaniesOptions(); ?>
                <option value="new">+ Добавить</option>
            </select>
        </div>
        <div class="col-md-8" id="company_name_div" style="display: none;">
            <label for="company_name" class="form-label">Новая компания</label>
            <input id="company_name" type="text" class="form-control" name="company_name" placeholder="Введите название компании">
        </div>

        <div class="row mt-3">
            <div class="col-md-4">
                <label for="phone1" class="form-label">Телефон 1</label>
                <input id="phone1" type="text" class="form-control" name="phone_number1" maxlength=20>
            </div>
            <div class="col-md-4">
                <label for="phone2" class="form-label">Телефон 2</label>
                <input id="phone2" type="text" class="form-control" name="phone_number2" maxlength=20>
            </div>
            <div class="col-md-4">
                <label for="phone3" class="form-label">Телефон 3</label>
                <input id="phone3" type="text" class="form-control" name="phone_number3" maxlength=20>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </form>
</div>

<?php include '../../footer.php'; ?>