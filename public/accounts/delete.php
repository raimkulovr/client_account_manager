<?php
include_once '../../header.php';
require_once '../../models/operation_result.php';

if(isset($_GET['delete_id'])){
    $sql = "DELETE FROM clients WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([
            ':id' => $_GET['delete_id'],
        ]);
        $deletedRows = $stmt->rowCount();

        if ($deletedRows > 0) {
            $operation_result = new OperationResult(true, "Запись успешно удалена.");
        } else {
            $operation_result = new OperationResult(false, "Запись не найдена или уже удалена.");
        }

    } catch (Throwable $e) {
        $operation_result = new OperationResult($success = false, "Что-то пошло не так.");
    }
}
?>

<div class="container flex-grow-1">
    <h1 class="display-6 py-3">Удаление аккаунта</h1>
    <?php 
        if (isset($_SERVER['HTTP_REFERER'])) {
            $previousPage = $_SERVER['HTTP_REFERER'];
            echo "<p class=\"text-secondary\">Нажмите <a href=\"$previousPage\">здесь</a> что-бы вернуться</p>";
        }
    ?>
    <?php
    if (isset($operation_result)) {
        $success = $operation_result->success;
        $message = $operation_result->message;
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

</div>