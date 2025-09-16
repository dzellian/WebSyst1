<?php

$rows = isset($_POST['rows']) ? (int)$_POST['rows'] : 10;
$cols = isset($_POST['cols']) ? (int)$_POST['cols'] : 10;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Multiplication Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 30px;
        }
        h2 {
            margin-bottom: 15px;
        }
        form {
            margin-bottom: 20px;
        }
        table {
            border-collapse: collapse;
            background: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #333;
            padding: 10px 15px;
            text-align: center;
        }
        th {
            background-color: #e0e0e0;
        }
        .odd {
            background-color: yellow;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>Multiplication Table Generator</h2>

   
    <form method="post">
        <label>Rows: 
            <input type="number" name="rows" value="<?php echo $rows; ?>" min="1">
        </label>
        <label>Columns: 
            <input type="number" name="cols" value="<?php echo $cols; ?>" min="1">
        </label>
        <button type="submit">Generate</button>
    </form>

 
    <table>
        <tr>
            <th>X</th>
            <?php for ($c = 1; $c <= $cols; $c++): ?>
                <th><?php echo $c; ?></th>
            <?php endfor; ?>
        </tr>

        <?php for ($r = 1; $r <= $rows; $r++): ?>
            <tr>
                <th><?php echo $r; ?></th>
                <?php for ($c = 1; $c <= $cols; $c++): 
                    $val = $r * $c;
                    $class = ($val % 2 == 1) ? "odd" : "";
                ?>
                    <td class="<?php echo $class; ?>"><?php echo $val; ?></td>
                <?php endfor; ?>
            </tr>
        <?php endfor; ?>
    </table>
</body>
</html>
