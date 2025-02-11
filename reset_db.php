<?php
require_once 'db_config.php';

// Script for scheduled data reset and filling. Mostly written by ChatGPT.
try {
    $pdo = new PDO($dsn, $username, $password, $options);
    $pdo->exec("TRUNCATE TABLE clients;");
    $pdo->exec("DELETE FROM companies;");


    // Sample company names
    $companyNames = [
        "TechCorp", "InnoSoft", "Global Dynamics", "SkyNet Systems", "Cloud Nexus",
        "BlueWave Tech", "Quantum Solutions", "NextGen Innovations", "CyberCore", "DataForge"
    ];

    // Insert 50 random companies
    $stmt = $pdo->prepare("INSERT INTO companies (name, description) VALUES (:name, :description)");

    for ($i = 1; $i <= 50; $i++) {
        $companyName = $companyNames[array_rand($companyNames)] . " " . rand(100, 999);
        $companyDesc = "A leading company in " . strtolower($companyNames[array_rand($companyNames)]) . " sector.";

        $stmt->execute([
            'name' => $companyName,
            'description' => $companyDesc
        ]);
    }

    // Retrieve all company IDs for assigning clients
    $companyIds = $pdo->query("SELECT id FROM companies")->fetchAll(PDO::FETCH_COLUMN);

    // Sample first and last names
    $firstNames = ["John", "Jane", "Michael", "Emily", "David", "Sarah", "Robert", "Jessica", "Daniel", "Laura"];
    $lastNames = ["Smith", "Johnson", "Brown", "Williams", "Jones", "Garcia", "Miller", "Davis", "Rodriguez", "Martinez"];
    $positions = ["Manager", "Engineer", "Developer", "Designer", "Consultant", "Analyst", "Director"];

    // Insert 50 random clients
    $stmt = $pdo->prepare("INSERT INTO clients (first_name, last_name, email, company_id, position, phone_number1, phone_number2, phone_number3) 
                            VALUES (:first_name, :last_name, :email, :company_id, :position, :phone1, :phone2, :phone3)");

    for ($i = 1; $i <= 50; $i++) {
        $firstName = $firstNames[array_rand($firstNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        $email = strtolower($firstName . "." . $lastName . rand(100, 999) . "@example.com");
        $companyId = $companyIds[array_rand($companyIds)];
        $position = $positions[array_rand($positions)];
        $phone1 = "+1-" . rand(100, 999) . "-" . rand(1000, 9999);
        $phone2 = rand(0, 1) ? "+1-" . rand(100, 999) . "-" . rand(1000, 9999) : null;
        $phone3 = rand(0, 1) ? "+1-" . rand(100, 999) . "-" . rand(1000, 9999) : null;

        $stmt->execute([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'company_id' => $companyId,
            'position' => $position,
            'phone1' => $phone1,
            'phone2' => $phone2,
            'phone3' => $phone3
        ]);
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}