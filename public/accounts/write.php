<?php
include_once '../../header.php';
require_once '../../models/company.php';
require_once '../../models/account.php';
require_once '../../models/operation_result.php';

interface IAccountCreator
{
    public function createAccount();
}

interface IAccountEditor
{
    public function editAccount();
}

class AccountWriteFormController implements IAccountCreator, IAccountEditor
{
    protected array $companies = [];
    protected ?int $number_of_accounts = null;
    public OperationResult $operation_result;
    public readonly bool $is_edit;
    public readonly ?Account $account;

    function __construct(protected PDO $pdo, $edit_id) {
        if($edit_id!=null){
            $this->getAccountData($edit_id);
        } else {
            $this->is_edit = false;
            $this->account = null;
        }
    }

    private function getNumberOfAccounts(): int
    {
        if($this->number_of_accounts) return $this->number_of_accounts;

        $sql = "SELECT COUNT(*) AS total FROM clients";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch();
        $count = $result['total'];
        
        $this->number_of_accounts = $count;
        return $count;
    }

    public function canCreateAccount() : bool 
    {
        return $this->getNumberOfAccounts() < 1000;
    }
    
    public function canCreateCompany() : bool 
    {
        return count($this->getCompanies()) < 1000;
    }

    public function getCompanies(): array
    {
        if ($this->companies) return $this->companies;
        $companies = array();

        $stmt = $this->pdo->query(<<<SQL
        SELECT *
        FROM companies
        SQL);
        $raw_companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($raw_companies as $raw_company) {
            $companies[] = Company::fromArray($raw_company);
        }

        $this->companies = $companies;
        return $companies;
    }

    public function createCompany($company_id, $company_name) 
    {
        if(!$this->canCreateCompany()) return;

        $sql = "INSERT INTO companies (name) VALUES (:name)";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([
                ':name' => htmlspecialchars($company_name, ENT_QUOTES, "UTF-8"),
            ]);
            $company_id = $this->pdo->lastInsertId();
        } catch (Throwable $e) {
            $company_id = null;
        }

        return $company_id;
    }

    public static function getRawAccountFromPost() : Array {
        return [
            'first_name' => $_POST['first_name'] ?? null,
            'last_name' => $_POST['last_name'] ?? null,
            'email' => $_POST['email'] ?? null,
            'company_id' => $_POST['company_id'] ?? null,
            'company_name' => $_POST['company_name'] ?? null,
            'position' => $_POST['position'] ?? null,
            'phone_number1' => $_POST['phone_number1'] ?? null,
            'phone_number2' => $_POST['phone_number2'] ?? null,
            'phone_number3' => $_POST['phone_number3'] ?? null,
        ];
    }

    public function createAccount()
    {
        if(!$this->canCreateAccount()) return;
        $account = $this->getRawAccountFromPost();

        if ($account['first_name'] && $account['last_name'] && $account['email']) {
            if ($account['company_id'] === "new" && $account['company_name']) {
                $company_id = $this->createCompany($account['company_id'], $account['company_name']);
            }
            $company_id = (int) ($company_id ?? $account['company_id']);
            $company_id = ($company_id > 0) ? $company_id : null;

            $sql = "INSERT INTO clients (first_name, last_name, email, company_id, position, phone_number1, phone_number2, phone_number3) 
            VALUES (:first_name, :last_name, :email, :company_id, :position, :phone_number1, :phone_number2, :phone_number3)";
            $stmt = $this->pdo->prepare($sql);

            try {
                $stmt->execute([
                    ':first_name' => htmlspecialchars($account['first_name'], ENT_QUOTES, "UTF-8"),
                    ':last_name' => htmlspecialchars($account['last_name'], ENT_QUOTES, "UTF-8"),
                    ':email' => htmlspecialchars($account['email'], ENT_QUOTES, "UTF-8"),
                    ':company_id' => $company_id,
                    ':position' => htmlspecialchars($account['position'], ENT_QUOTES, "UTF-8"),
                    ':phone_number1' => htmlspecialchars($account['phone_number1'], ENT_QUOTES, "UTF-8"),
                    ':phone_number2' => htmlspecialchars($account['phone_number2'], ENT_QUOTES, "UTF-8"),
                    ':phone_number3' => htmlspecialchars($account['phone_number3'], ENT_QUOTES, "UTF-8"),
                ]);
                $this->operation_result = new OperationResult($success = true, "Запись успешно сохранена.");
            } catch (Throwable $e) {
                if ($e->getCode() == '23000' && strpos($e->getMessage(), '1062') !== false) {
                    $this->operation_result = new OperationResult($success = false, "Запись с таким Email уже существует.");
                } else {
                    $this->operation_result = new OperationResult($success = false, "Что-то пошло не так.");
                }
            }
        }
        $_POST = [];
    }

    private function getAccountData(int $id) {
        if (isset($account) && $this->account) return $this->account;

        $stmt = $this->pdo->prepare(<<<SQL
        SELECT *
        FROM clients
        WHERE id = :id
        SQL);
        $stmt->execute(['id' => $id]);
        $raw_account = $stmt->fetch(PDO::FETCH_ASSOC);

        if($raw_account){
            $this->is_edit = true;
            $account = Account::fromArray($raw_account);
            $this->account = $account;
            return $account;
        } else {
            $this->is_edit = false;
            $this->account = null;
        }

    }

    public function editAccount() {
        $account = $this->getRawAccountFromPost();

        if ($account['first_name'] && $account['last_name'] && $account['email']) {
            if ($account['company_id'] === "new" && $account['company_name']) {
                $company_id = $this->createCompany($account['company_id'], $account['company_name']);
            }
            $company_id = (int) ($company_id ?? $account['company_id']);
            $company_id = ($company_id > 0) ? $company_id : null;

            $sql = 'UPDATE clients 
                    SET 
                        first_name = :first_name, 
                        last_name = :last_name, 
                        email = :email, 
                        company_id = :company_id, 
                        position = :position, 
                        phone_number1 = :phone_number1, 
                        phone_number2 = :phone_number2, 
                        phone_number3 = :phone_number3 
                    WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);

            try {
                $stmt->execute([
                    ':id' => $this->account->id,
                    ':first_name' => htmlspecialchars($account['first_name'], ENT_QUOTES, "UTF-8"),
                    ':last_name' => htmlspecialchars($account['last_name'], ENT_QUOTES, "UTF-8"),
                    ':email' => htmlspecialchars($account['email'], ENT_QUOTES, "UTF-8"),
                    ':company_id' => $company_id,
                    ':position' => htmlspecialchars($account['position'], ENT_QUOTES, "UTF-8"),
                    ':phone_number1' => htmlspecialchars($account['phone_number1'], ENT_QUOTES, "UTF-8"),
                    ':phone_number2' => htmlspecialchars($account['phone_number2'], ENT_QUOTES, "UTF-8"),
                    ':phone_number3' => htmlspecialchars($account['phone_number3'], ENT_QUOTES, "UTF-8"),
                ]);
                $this->operation_result = new OperationResult($success = true, "Аккаунт успешно обновлен.");
            } catch (Throwable $e) {
                if ($e->getCode() == '23000' && strpos($e->getMessage(), '1062') !== false) {
                    $this->operation_result = new OperationResult($success = false, "Запись с таким Email уже существует.");
                } else {
                    $this->operation_result = new OperationResult($success = false, "Что-то пошло не так.");
                }
            }
        }
        $_POST = [];
    }
}

$controller = new AccountWriteFormController($pdo, $_GET['edit_id'] ?? null);

if ($_POST) {
    if($controller->is_edit){
        $controller->editAccount();
    } else {
        $controller->createAccount();
    }
}

function renderCompaniesOptions()
{
    global $controller;
    $result = "";

    foreach ($controller->getCompanies() as $company) {
        $result .= "<option ";
        $result .= "value=\"" . $company->id . "\" ";
        if($controller->account->company_id == $company->id) $result .= "selected";
        $result .= ">";
        $result .= $company->name . " ";
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
    <h1 class="display-6 py-3"><?php echo $controller->is_edit ? 'Редактировать' : 'Добавить'?> аккаунт</h1>
    <p class="text-secondary">Поля отмечанные "*" обязательны к заполнению</p>
    <?php
    if (isset($controller->operation_result)) {
        $success = $controller->operation_result->success;
        $message = $controller->operation_result->message;
        if ($success) {
            echo "<div class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">
                    <strong>Готово!</strong> $message
                </div>";
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
            <input id="first_name" type="text" class="form-control" name="first_name" placeholder="Имя" maxlength=100 value="<?php echo $controller->account->first_name ?? ''?>" required>
        </div>
        <div class="col-md-6">
            <label for="last_name" class="form-label">Фамилия*</label>
            <input id="last_name" type="text" class="form-control" name="last_name" placeholder="Фамилия" maxlength=100 value="<?php echo $controller->account->last_name ?? ''?>" required>
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">Email*</label>
            <input id="email" type="email" class="form-control" name="email" placeholder="test@example.com" maxlength=255 value="<?php echo $controller->account->email ?? ''?>" required>
        </div>
        <div class="col-md-6">
            <label for="position" class="form-label">Должность</label>
            <input id="position" type="text" class="form-control" name="position" placeholder="Менеджер" value="<?php echo $controller->account->position ?? ''?>" maxlength=255>
        </div>
        <div class="col-md-4">
            <label for="company_id" class="form-label">Компания</label>
            <select id="company_id" name="company_id" class="form-select" onchange="handleSelection(this)">
                <option value="">-- Выберите компанию --</option>
                <?php if($controller->canCreateCompany()) echo '<option value="new">+ Добавить</option>' ?>
                <?php renderCompaniesOptions(); ?>
            </select>
        </div>
        <div class="col-md-8" id="company_name_div" style="display: none;">
            <label for="company_name" class="form-label">Новая компания</label>
            <input id="company_name" type="text" class="form-control" name="company_name" placeholder="Введите название компании">
        </div>

        <div class="row mt-3">
            <div class="col-md-4">
                <label for="phone1" class="form-label">Телефон 1</label>
                <input id="phone1" type="text" class="form-control" name="phone_number1" maxlength=20 value="<?php echo $controller->account->phone_number1 ?? ''?>">
            </div>
            <div class="col-md-4">
                <label for="phone2" class="form-label">Телефон 2</label>
                <input id="phone2" type="text" class="form-control" name="phone_number2" maxlength=20 value="<?php echo $controller->account->phone_number2 ?? ''?>">
            </div>
            <div class="col-md-4">
                <label for="phone3" class="form-label">Телефон 3</label>
                <input id="phone3" type="text" class="form-control" name="phone_number3" maxlength=20 value="<?php echo $controller->account->phone_number3 ?? ''?>">
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary" <?php if(!$controller->canCreateAccount()) echo 'disabled' ?>>Сохранить</button>
        </div>
    </form>
</div>

<?php include '../../footer.php'; ?>