<?php

/**
 * Prepare and execute a PDO statement with bound parameters.
 */
function dbStatement(PDO $pdo, string $sql, array $params = []): PDOStatement
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Run a SELECT query and return all matching rows.
 */
function dbFetchAll(PDO $pdo, string $sql, array $params = []): array
{
    return dbStatement($pdo, $sql, $params)->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Run a SELECT query and return the first row (or null if none).
 */
function dbFetchOne(PDO $pdo, string $sql, array $params = []): ?array
{
    $row = dbStatement($pdo, $sql, $params)->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * Run an INSERT, UPDATE, or DELETE query and return whether it succeeded.
 */
function dbExecute(PDO $pdo, string $sql, array $params = []): bool
{
    return dbStatement($pdo, $sql, $params) instanceof PDOStatement;
}
