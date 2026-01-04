<?php
$csvFile = 'cv_list.csv';
$uploadsDir = 'uploads/';

// Make sure uploads folder exists
if(!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}

// ------------------ HANDLE FORM SUBMISSION ------------------
if(isset($_POST['submit'])) {
    $name = $_POST['name'];
    $rank = $_POST['rank'];
    $email = $_POST['email'];
    $number = $_POST['number'];
    $cvDate = $_POST['cvDate'];
    $remarks = $_POST['remarks'];

    // Handle file upload
    $fileName = '';
    if(isset($_FILES['cvUpload']) && $_FILES['cvUpload']['error'] == 0){
        $originalName = $_FILES['cvUpload']['name'];
        $fileName = time().'_'.preg_replace("/[^a-zA-Z0-9\.]/", "_", $originalName);
        move_uploaded_file($_FILES['cvUpload']['tmp_name'], $uploadsDir.$fileName);
    }

    // Append to CSV
    $entry = [$name, $rank, $email, $number, $cvDate, $remarks, $fileName];
    $file = fopen($csvFile, 'a');
    fputcsv($file, $entry);
    fclose($file);

    header("Location: index.php");
    exit();
}

// ------------------ HANDLE DELETE ------------------
if(isset($_GET['delete'])) {
    $deleteIndex = intval($_GET['delete']);
    $rows = [];
    if(file_exists($csvFile)){
        $file = fopen($csvFile, 'r');
        while(($data = fgetcsv($file)) !== false){
            $rows[] = $data;
        }
        fclose($file);
    }

    if(isset($rows[$deleteIndex])){
        // Delete the uploaded CV file
        if(!empty($rows[$deleteIndex][6]) && file_exists($uploadsDir.$rows[$deleteIndex][6])){
            unlink($uploadsDir.$rows[$deleteIndex][6]);
        }
        unset($rows[$deleteIndex]);
        $rows = array_values($rows); // reindex
        $file = fopen($csvFile, 'w');
        foreach($rows as $row){
            fputcsv($file, $row);
        }
        fclose($file);
    }

    header("Location: index.php");
    exit();
}

// ------------------ LOAD CSV ------------------
$cvList = [];
if(file_exists($csvFile)){
    $file = fopen($csvFile, 'r');
    while(($data = fgetcsv($file)) !== false){
        $cvList[] = $data;
    }
    fclose($file);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ozellar Marine CV Tracker</title>
    <style>
        body { font-family: Arial; margin: 30px; background-color: #f9f9f9; }
        h1 { color: orange; text-align: center; }
        label { display: block; margin-top: 15px; }
        input, select { width: 300px; padding: 5px; margin-top: 5px; }
        button { margin-top: 20px; padding: 5px 10px; cursor: pointer; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .deleteBtn { background-color: red; color: white; border: none; padding: 3px 8px; cursor: pointer; }
    </style>
</head>
<body>

<h1>Ozellar Marine</h1>
<h2>CV Tracker</h2>

<form method="post" enctype="multipart/form-data">
    <label>Name: <input type="text" name="name" required></label>
    <label>Rank: 
        <select name="rank" required>
            <option value="">--Select Rank--</option>
            <option>Master</option>
            <option>Chief Officer</option>
            <option>Second Officer</option>
            <option>Third Officer</option>
            <option>Deck Cadet</option>
            <option>Other</option>
        </select>
    </label>
    <label>Email: <input type="text" name="email"></label>
    <label>Contact Number: <input type="text" name="number" required></label>
    <label>CV Collecting Date: <input type="date" name="cvDate"></label>
    <label>Remarks: <input type="text" name="remarks"></label>
    <label>Upload CV: <input type="file" name="cvUpload" accept=".pdf,.doc,.docx,.eml"></label>
    <button type="submit" name="submit">Submit</button>
</form>

<h3>Total CVs: <?= count($cvList) ?></h3>

<table>
    <thead>
        <tr>
            <th>Sr.No</th>
            <th>Name</th>
            <th>Rank</th>
            <th>Email</th>
            <th>Contact Number</th>
            <th>CV Date</th>
            <th>Remarks</th>
            <th>CV / Mail</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($cvList as $i => $cv): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($cv[0]) ?></td>
            <td><?= htmlspecialchars($cv[1]) ?></td>
            <td><?= htmlspecialchars($cv[2]) ?></td>
            <td><?= htmlspecialchars($cv[3]) ?></td>
            <td><?= htmlspecialchars($cv[4]) ?></td>
            <td><?= htmlspecialchars($cv[5]) ?></td>
            <td>
                <?php if(!empty($cv[6])): ?>
                    <a href="<?= $uploadsDir.$cv[6] ?>" download>Download</a>
                <?php else: ?>
                    No File
                <?php endif; ?>
            </td>
            <td>
                <a class="deleteBtn" href="?delete=<?= $i ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
