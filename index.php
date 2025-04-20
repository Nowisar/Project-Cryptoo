<?php
// Cipher functions in PHP
function caesar_cipher($text, $shift, $encrypt = true) {
    $result = "";
    $shift = $encrypt ? $shift : -$shift;
    foreach (str_split($text) as $char) {
        if (ctype_alpha($char)) {
            $base = ctype_upper($char) ? ord('A') : ord('a');
            $new_pos = (ord($char) - $base + $shift) % 26;
            if ($new_pos < 0) $new_pos += 26;
            $result .= chr($base + $new_pos);
        } else {
            $result .= $char;
        }
    }
    return $result;
}

function vigenere_cipher($text, $key, $encrypt = true) {
    $result = "";
    $key = strtoupper($key);
    $key_index = 0;
    foreach (str_split($text) as $char) {
        if (ctype_alpha($char)) {
            $base = ctype_upper($char) ? ord('A') : ord('a');
            $key_shift = ord($key[$key_index % strlen($key)]) - ord('A');
            $shift = $encrypt ? $key_shift : -$key_shift;
            $new_pos = (ord($char) - $base + $shift) % 26;
            if ($new_pos < 0) $new_pos += 26;
            $result .= chr($base + $new_pos);
            $key_index++;
        } else {
            $result .= $char;
        }
    }
    return $result;
}

function playfair_cipher($text, $key, $encrypt = true) {
    // Prepare key and create matrix
    $key = strtoupper(str_replace('J', 'I', $key));
    $matrix = array_unique(str_split($key . 'ABCDEFGHIKLMNOPQRSTUVWXYZ'));
    $matrix = array_values(array_filter($matrix, function($c) { return $c !== 'J'; }));
    
    // Prepare text
    $text = strtoupper(str_replace('J', 'I', $text));
    $text = preg_replace('/[^A-Z]/', '', $text);
    if (strlen($text) % 2 != 0) $text .= 'X';
    
    // Process digraphs
    $result = '';
    for ($i = 0; $i < strlen($text); $i += 2) {
        $a = $text[$i];
        $b = $text[$i+1];
        $a_pos = array_search($a, $matrix);
        $b_pos = array_search($b, $matrix);
        $a_row = intval($a_pos / 5);
        $a_col = $a_pos % 5;
        $b_row = intval($b_pos / 5);
        $b_col = $b_pos % 5;
        
        if ($a_row == $b_row) { // Same row
            $a_col = ($a_col + ($encrypt ? 1 : -1) + 5) % 5;
            $b_col = ($b_col + ($encrypt ? 1 : -1) + 5) % 5;
        } elseif ($a_col == $b_col) { // Same column
            $a_row = ($a_row + ($encrypt ? 1 : -1) + 5) % 5;
            $b_row = ($b_row + ($encrypt ? 1 : -1) + 5) % 5;
        } else { // Rectangle
            $temp = $a_col;
            $a_col = $b_col;
            $b_col = $temp;
        }
        
        $result .= $matrix[$a_row * 5 + $a_col] . $matrix[$b_row * 5 + $b_col] . ' ';
    }
    return trim($result);
}

// Process form if submitted
$result = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cipher = $_POST['cipher'] ?? 'caesar';
    $operation = $_POST['operation'] ?? 'encrypt';
    $key = $_POST['key'] ?? '';
    $message = $_POST['message'] ?? '';
    
    try {
        switch ($cipher) {
            case 'caesar':
                if (!is_numeric($key)) throw new Exception("Shift must be a number");
                $result = caesar_cipher($message, (int)$key, $operation === 'encrypt');
                break;
            case 'vigenere':
                if (empty($key)) throw new Exception("Please enter a keyword");
                $result = vigenere_cipher($message, $key, $operation === 'encrypt');
                break;
            case 'playfair':
                if (empty($key)) throw new Exception("Please enter a keyword");
                $result = playfair_cipher($message, $key, $operation === 'encrypt');
                break;
        }
    } catch (Exception $e) {
        $result = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptoSecure Pro | Web Version</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <h1><i class="fas fa-lock"></i> CryptoSecure Pro</h1>
            <div class="theme-switch">
                <button id="themeToggle"><i class="fas fa-moon"></i> Dark Mode</button>
            </div>
        </header>
        
        <main class="app-main">
            <form id="cipherForm" method="POST">
                <div class="settings-panel">
                    <div class="form-group">
                        <label for="cipher">Cipher Type:</label>
                        <select id="cipher" name="cipher">
                            <option value="caesar">Caesar Cipher</option>
                            <option value="vigenere">Vigenère Cipher</option>
                            <option value="playfair">Playfair Cipher</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Operation:</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="operation" value="encrypt" checked>
                                <span>Encrypt</span>
                            </label>
                            <label>
                                <input type="radio" name="operation" value="decrypt">
                                <span>Decrypt</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="key" id="keyLabel">Shift Number:</label>
                        <input type="text" id="key" name="key" required>
                    </div>
                </div>
                
                <div class="io-panels">
                    <div class="panel">
                        <label for="message">Input Message:</label>
                        <textarea id="message" name="message" rows="8" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="panel">
                        <label for="result">Output Result:</label>
                        <textarea id="result" rows="8" readonly><?= htmlspecialchars($result) ?></textarea>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="button" id="clearBtn"><i class="fas fa-eraser"></i> Clear</button>
                    <button type="button" id="copyBtn"><i class="fas fa-copy"></i> Copy Result</button>
                    <button type="submit" class="primary"><i class="fas fa-lock"></i> Process</button>
                </div>
            </form>
        </main>
        
        <footer class="app-footer">
            <p>CryptoSecure Pro v2.0 &copy; <?= date('Y') ?> | SecureSoft Technologies</p>
        </footer>
    </div>

    <script src="script.js"></script>
</body>
</html>