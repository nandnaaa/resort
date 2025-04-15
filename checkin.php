<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) header("Location: login.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $persons = $_POST['persons'];
    $room_count = $_POST['room_count'];
    $phone = $_POST['phone'];
    $vehicle = $_POST['vehicle'];
    $id_proof = $_FILES['id_proof']['name'];

    // Upload ID Proof
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir);
    $target_file = $target_dir . basename($id_proof);
    move_uploaded_file($_FILES["id_proof"]["tmp_name"], $target_file);

    // Save each room entry
    for ($i = 0; $i < $room_count; $i++) {
        $room_type = $_POST["room_type_$i"];
        $room_number = $_POST["room_number_$i"];

        $stmt = $conn->prepare("INSERT INTO checkins (name, room_type, room_number, id_proof, phone, vehicle, persons, checkin_time) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssssi", $name, $room_type, $room_number, $id_proof, $phone, $vehicle, $persons);
        $stmt->execute();
    }

    echo "<script>alert('Customer Checked In Successfully!'); window.location.href='dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Check-In</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(to right, #89f7fe, #66a6ff);
            display: flex;
            justify-content: center;
            align-items: start;
            padding: 40px;
            min-height: 100vh;
            margin: 0;
        }

        .checkin-form {
            background: #fff;
            padding: 30px 40px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            width: 600px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        input, select {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 14px;
            border: none;
            width: 100%;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        label {
            font-weight: bold;
            color: #333;
        }

        .room-block {
            background-color: #f7f7f7;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="checkin-form">
    <h2>Customer Check-In</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Customer Name</label>
        <input type="text" name="name" required>

        <label>Number of Persons</label>
        <input type="number" name="persons" min="1" required>

        <label>Number of Rooms</label>
        <input type="number" name="room_count" id="room_count" min="1" max="5" onchange="renderRoomFields()" required>

        <div id="rooms_container"></div>

        <label>Upload ID Proof</label>
        <input type="file" name="id_proof" accept="image/*" required>

        <label>Phone Number</label>
        <input type="tel" name="phone" pattern="[0-9]{10}" required>

        <label>Vehicle Number</label>
        <input type="text" name="vehicle" required>

        <button type="submit">Check In</button>
    </form>
</div>

<script>
    const rooms = {
        "deluxe": ["103", "105", "203", "205", "303", "305", "403"],
        "super deluxe": ["101", "201", "301", "404", "401"],
        "suite with balcony": ["102", "202", "302", "204", "104", "402"],
        "honeymoon suite": ["304"],
        "presidential": ["501"],
        "tree top": ["T.H 1", "T.H 2", "T.H 3"],
        "family suite": ["602"],
        "standard room": ["601"]
    };

    async function fetchUnavailableRooms() {
        const response = await fetch('get_unavailable_rooms.php');
        return await response.json();
    }

    async function renderRoomFields() {
        const count = document.getElementById("room_count").value;
        const container = document.getElementById("rooms_container");
        container.innerHTML = "";
        const unavailableRooms = await fetchUnavailableRooms();

        for (let i = 0; i < count; i++) {
            let block = document.createElement("div");
            block.classList.add("room-block");

            block.innerHTML = `
                <label>Room Type ${i + 1}</label>
                <select name="room_type_${i}" id="room_type_${i}" required onchange="updateRoomNumbers(${i})">
                    <option value="">Select Room Type</option>
                    ${Object.keys(rooms).map(type => `<option value="${type}">${type.charAt(0).toUpperCase() + type.slice(1)}</option>`).join('')}
                </select>

                <label>Room Number ${i + 1}</label>
                <select name="room_number_${i}" id="room_number_${i}" required>
                    <option value="">Select Room Number</option>
                </select>
            `;
            container.appendChild(block);
        }
    }

    async function updateRoomNumbers(index) {
        const typeSelect = document.getElementById(`room_type_${index}`);
        const numberSelect = document.getElementById(`room_number_${index}`);
        const selectedType = typeSelect.value;

        const unavailableRooms = await fetchUnavailableRooms();

        numberSelect.innerHTML = `<option value="">Select Room Number</option>`;
        if (rooms[selectedType]) {
            rooms[selectedType].forEach(room => {
                const isUnavailable = unavailableRooms.includes(room);
                const option = document.createElement('option');
                option.value = room;
                option.textContent = room + (isUnavailable ? " (Unavailable)" : "");
                if (isUnavailable) option.disabled = true;
                numberSelect.appendChild(option);
            });
        }
    }
</script>
</body>
</html>
