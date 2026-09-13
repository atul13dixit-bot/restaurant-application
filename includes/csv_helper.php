<?php
// includes/csv_helper.php

if (!defined('DATA_DIR')) {
    define('DATA_DIR', __DIR__ . '/../data/');
}

// Robust read method that drops blank rows and prevents null pointer bugs
function readCSV($filename) {
    $filePath = DATA_DIR . $filename;
    if (!file_exists($filePath)) {
        return [];
    }
    
    $rows = [];
    if (($handle = fopen($filePath, 'r')) !== FALSE) {
        $headers = fgetcsv($handle);
        if ($headers !== FALSE) {
            // Clean headers safely
            $headers = array_map(function($h) { return trim((string)$h); }, $headers);
            
            while (($data = fgetcsv($handle)) !== FALSE) {
                // Ignore perfectly empty or corrupt trailing lines
                if (empty($data) || $data === [null] || count(array_filter($data)) === 0) {
                    continue;
                }
                
                // Convert values to string before trimming to prevent PHP 8.1+ deprecation errors
                $data = array_map(function($d) { return trim((string)$d); }, $data);
                
                // Standardize layout matrix matching
                if (count($headers) !== count($data)) {
                    if (count($data) < count($headers)) {
                        $data = array_pad($data, count($headers), '');
                    } else {
                        $data = array_slice($data, 0, count($headers));
                    }
                }
                
                $rows[] = array_combine($headers, $data);
            }
        }
        fclose($handle);
    }
    return $rows;
}

// Safely append new rows avoiding spacing issues
function writeCSV($filename, $data, $headers) {
    $filePath = DATA_DIR . $filename;
    $fileExists = file_exists($filePath);
    
    $data = array_map(function($v) { return trim((string)$v); }, $data);
    $headers = array_map(function($h) { return trim((string)$h); }, $headers);
    
    $handle = fopen($filePath, 'a');
    if ($handle !== FALSE) {
        flock($handle, LOCK_EX);
        
        if (!$fileExists || filesize($filePath) === 0) {
            fputcsv($handle, $headers);
        }
        
        fputcsv($handle, $data);
        
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

// Re-write data arrays precisely
function rewriteFullCSV($filename, $allRows, $headers) {
    $filePath = DATA_DIR . $filename;
    $headers = array_map(function($h) { return trim((string)$h); }, $headers);
    
    $handle = fopen($filePath, 'w');
    if ($handle !== FALSE) {
        flock($handle, LOCK_EX);
        
        fputcsv($handle, $headers);
        foreach ($allRows as $row) {
            $cleanRow = array_map(function($v) { return trim((string)$v); }, array_values($row));
            fputcsv($handle, $cleanRow);
        }
        
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

// Locate incremental identifiers safely
function getNextID($filename) {
    $rows = readCSV($filename);
    if (empty($rows)) return 1;
    $lastRow = end($rows);
    return isset($lastRow['id']) ? (int)$lastRow['id'] + 1 : 1;
}
