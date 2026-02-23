<?php
// ========================
// Marathon Tracker
// ========================

// File to store historical race data
$data_file = "race_data.txt";

// Initialize historical data array
$historical_data = [];

// Read existing historical data
if(file_exists($data_file)){
    $lines = file($data_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach($lines as $line){
        $parts = explode(",", $line);
        $historical_data[] = [
            'runner_name' => $parts[0],
            'total_distance' => floatval($parts[1]),
            'distance_covered' => floatval($parts[2]),
            'elapsed_time' => floatval($parts[3]),
            'target_time' => floatval($parts[4]),
            'current_speed' => floatval($parts[5]),
            'required_speed' => floatval($parts[6])
        ];
    }
}

// Initialize variables
$message = "";
$runner_name = $total_distance = $distance_covered = $elapsed_time = $target_time = "";

// ========================
// Functions
// ========================

// Calculate current speed (km/h)
function calculateCurrentSpeed($distance, $time){
    if($time <= 0) return 0;
    return $distance / $time;
}

// Calculate required speed to finish within target time
function calculateRequiredSpeed($distance_total, $distance_covered, $target_time, $elapsed_time){
    $remaining_distance = $distance_total - $distance_covered;
    $remaining_time = $target_time - $elapsed_time;
    if($remaining_time <= 0) return 0;
    return $remaining_distance / $remaining_time;
}

// Format speed to 2 decimal places
function formatSpeed($speed){
    return number_format($speed, 2) . " km/h";
}

// Save new runner data
function saveRunnerData($file, $runner){
    $line = implode(",", $runner) . PHP_EOL;
    file_put_contents($file, $line, FILE_APPEND);
}

// ========================
// Handle form submission
// ========================
if(isset($_POST['submit'])){
    $runner_name = trim($_POST['runner_name']);
    $total_distance = floatval($_POST['total_distance']);
    $distance_covered = floatval($_POST['distance_covered']);
    $elapsed_time = floatval($_POST['elapsed_time']);
    $target_time = floatval($_POST['target_time']);

    // Validation
    if($runner_name == "" || $total_distance <= 0 || $distance_covered < 0 || $elapsed_time < 0 || $target_time <= 0){
        $message = "Please fill all fields correctly.";
    } elseif($distance_covered > $total_distance){
        $message = "Distance covered cannot exceed total distance.";
    } elseif($elapsed_time > $target_time){
        $message = "Elapsed time cannot exceed target time.";
    } else {
        // Calculations
        $current_speed = calculateCurrentSpeed($distance_covered, $elapsed_time);
        $required_speed = calculateRequiredSpeed($total_distance, $distance_covered, $target_time, $elapsed_time);

        // Prepare runner data
        $runner = [
            $runner_name,
            $total_distance,
            $distance_covered,
            $elapsed_time,
            $target_time,
            $current_speed,
            $required_speed
        ];

        // Save to historical data array
        $historical_data[] = [
            'runner_name' => $runner_name,
            'total_distance' => $total_distance,
            'distance_covered' => $distance_covered,
            'elapsed_time' => $elapsed_time,
            'target_time' => $target_time,
            'current_speed' => $current_speed,
            'required_speed' => $required_speed
        ];

        // Save to file
        saveRunnerData($data_file, $runner);

        $message = "Runner data saved successfully.";
        //Reset  form fileds
        $runner_name= $total_distance = $distance_covered =$elapsed_time = $target_time ="";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Marathon Progress Tracker</title>
    <style>
        body{font-family: Arial; margin: 20px;}
        form{padding: 15px; border:1px solid #ccc; border-radius:8px; max-width:500px; margin-bottom:20px;}
        label{display:block; margin-top:10px; font-weight:bold;}
        input{width:100%; padding:6px; margin-top:5px; border:1px solid #999; border-radius:4px;}
        button{margin-top:12px; padding:6px 15px; border:none; border-radius:4px; background-color:#4CAF50; color:white; cursor:pointer;}
        button:hover{background-color:#45a049;}
        table{border-collapse:collapse; width:100%; margin-top:20px;}
        th, td{border:1px solid #ccc; padding:6px; text-align:left;}
        th{background-color:#f2f2f2;}
        .message{color:green; font-weight:bold;}
    </style>
</head>
<body>
<h1>Marathon Progress Tracker</h1>

<?php if($message != "") echo "<p class='message'>$message</p>"; ?>

<!-- Runner Form -->
<form method="POST">
    <label>Runner Name:</label>
    <input type="text" name="runner_name" value="<?php echo htmlspecialchars($runner_name); ?>" required>
    <label>Total Distance (km):</label>
    <input type="number" step="0.01" name="total_distance" value="<?php echo htmlspecialchars($total_distance); ?>" required>
    <label>Distance Covered (km):</label>
    <input type="number" step="0.01" name="distance_covered" value="<?php echo htmlspecialchars($distance_covered); ?>" required>
    <label>Elapsed Time (hours):</label>
    <input type="number" step="0.01" name="elapsed_time" value="<?php echo htmlspecialchars($elapsed_time); ?>" required>
    <label>Target Time (hours):</label>
    <input type="number" step="0.01" name="target_time" value="<?php echo htmlspecialchars($target_time); ?>" required>
    <button type="submit" name="submit">Track Progress</button>
</form>

<!-- Historical Data Table -->
<h2>Historical Runner Data</h2>
<?php if(!empty($historical_data)){ ?>
<table>
    <tr>
        <th>Runner Name</th>
        <th>Total Distance (km)</th>
        <th>Distance Covered (km)</th>
        <th>Elapsed Time (h)</th>
        <th>Target Time (h)</th>
        <th>Current Speed (km/h)</th>
        <th>Required Speed (km/h)</th>
    </tr>
    <?php foreach($historical_data as $runner){ ?>
        <tr>
            <td><?php echo htmlspecialchars($runner['runner_name']); ?></td>
            <td><?php echo $runner['total_distance']; ?></td>
            <td><?php echo $runner['distance_covered']; ?></td>
            <td><?php echo $runner['elapsed_time']; ?></td>
            <td><?php echo $runner['target_time']; ?></td>
            <td><?php echo formatSpeed($runner['current_speed']); ?></td>
            <td><?php echo formatSpeed($runner['required_speed']); ?></td>
        </tr>
    <?php } ?>
</table>
<?php } else { echo "<p>No historical data available yet.</p>"; } ?>
</body>
</html>